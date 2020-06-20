
<?php
ini_set('max_execution_time', 300);

include __DIR__.'/../../ApiCore.php';
include_once RP('/Model/dbBusiness/Attendance.php');
include_once RP('/Model/dbBusiness/Staff.php');

$api = new ApiCore();

$attendance = new Model\Business\Attendance();
$staff = new Model\Business\Staff();


$files = $api->getFiles();
if(count($files)==0){$api->denied('沒有檔案.');}


$staff_map = $staff->read(['id','staff_no'],[])->cmap('staff_no',true);

$time_start = microtime(true);
$loop_time = 0;
$insert_time = 0;
$update_time = 0;
require_once RP('/Model/PHPExcel.php');
require_once RP('/Model/PHPExcel/IOFactory.php');

$all = array();
$staff_id_array = array();
$date_array = array();
$wrongMsgArray = array();

$str_map = [
  0=>'date',
  2=>'checkin_hours',
  3=>'checkout_hours',
  4=>'work_hours_total',
  5=>'late',
  6=>'early',
  8=>'nocard',
  9=>'remark',
  10=>'overtime_hours',
  11=>'vocation_hours',
  13=>'{stamp}_from',
  14=>'{stamp}_to',
  // 14=>'vocation_from',
  // 16=>'vocation_to'
];
function getDataBySheet(&$s, $page, $limit_row){
  Global $staff_map,$str_map;
  //取得ID
  $staff_data_row = 4;
  $staffId = 0;
  while ($staff_data_row < 8) {
    $staff_data_row += 1;
    $staff = $s->getCell("B$staff_data_row")->getValue();
    $staff_strs = explode(" ",$staff);
    if (empty($staff_strs[0]) || count($staff_strs) != 3) {
      continue;
    }

    $staffNo = $staff_strs[0];

    if (isset($staff_map[$staffNo])) {
      $staffId = $staff_map[ $staffNo ]['id'];
      break;
    } else {
      return sError("第 $page 頁: 沒有員工[$staffNo]。");
    }
  }

  if ($staffId == 0) {
    return sError("第 $page 頁: 員工代號 資料欄位錯誤。");
  }
  
  

  //取得年份
  $sheetYear = '';
  $year_data_row = 1;
  while ($year_data_row < 5) {
    $year_data_row+= 1;
    $year_title = $s->getCell("A$year_data_row")->getValue();
    $year_strs = explode("年", $year_title);

    if (count($year_strs) != 2) {
      continue;
    }

    $year_str = trim($year_strs[0]);

    if(strlen($year_str) == 4 ){
      $sheetYear = $year_str;
      break;
    }
  }

  if(empty($sheetYear)){
      // 若該頁年份格是錯誤或員工編號有誤，則不進行後續處理
    return sError("第 $page 頁: 年份格式錯誤，請檢查檔案。");
  }
  


  $result = [];
  
  $str_ary = array();					// 存放每行資料
  $str_ary['staff_id'] = $staffId;		// 每行資料存入前必須先放員工資料庫ID($staffId)
  // 預設
  $allColumn = 15;
  $timeFormat = "H:i:s";
  $mergeCells = "mergeCells";         // 合併儲存格

  $overtime_hours_idx = array_search('overtime_hours', $str_map);
  $vocation_hours_idx = array_search('vocation_hours', $str_map);
          
  // 拆解/重組資料陣列
  for ($row = $staff_data_row + 3; $row <= $limit_row-2; $row++) {

      $row_date = $s->getCellByColumnAndRow(0, $row)->getValue();

      if($row_date == null){  // :加班時數 :請假時數
          $vocation_hours = $s->getCellByColumnAndRow($vocation_hours_idx, $row)->getValue();
          $overtime_hours = $s->getCellByColumnAndRow($overtime_hours_idx, $row)->getValue();
          if($vocation_hours > 0 || $overtime_hours > 0){
              $row_date = $mergeCells;
          } else {
              continue;
          }
      } else {

          if (!preg_match('/^[\d\s\/]+$/', $row_date)) {
              continue;
          }
          // 日期欄位文字處理 YYYY-MM-DD
          $valstr = explode("/", $row_date);
          
          if( empty($valstr[1]) ){ return sError("第 $page 頁 [$row] : 日期資料錯誤。"); }
          
          $valstr2 = strtotime($sheetYear.'-'.(int)$valstr[0].'-'.(int)$valstr[1]);
          $row_date = date("Y-m-d", $valstr2);
      }

      $str_ary['date'] = $row_date;

      for ($col = 2; $col <= $allColumn; $col++) {

          if( !isset($str_map[$col]) ){ continue; }
          $col_name = $str_map[$col];

          // 取得該欄位內容
          $val = $s->getCellByColumnAndRow($col, $row)->getValue();
          
          switch($col_name){
            case 'checkin_hours':case 'checkout_hours':  // 處理欄位為時間格式的 2:上班 3:下班
              if($val != null){
                $timestr = strtotime($val);
                $val = date($timeFormat, $timestr);
              }else{
                $val = 'NULL';
              }
              // $str_ary[ $str_map[$col] ] = $val;
            break;
            case 'work_hours_total':case 'overtime_hours':case 'vocation_hours': // 處理欄位格式為時數 4:工時 10:加班時數 12:請假時數
              if($val == null){
                  // 次數欄位為空值
                  $val = 0.00;
              }
              $val = (float)$val;
            break;
            case 'late':case 'early':case 'nocard':   // 處理欄位格式為次數 5:遲到 6:早退 8:忘卡
              if($val === null){
                  // 次數欄位為空值
                  $val = 0;
              }
              $val = (int)$val;
            break;
            case 'remark':   // 9: 假別(加班別)
              if($val==null){ $val=''; }
            break;
            case '{stamp}_from':case '{stamp}_to': // 13:開始時間 15:結束時間
              
              if($val != null){
                $timestr = strtotime($val);
                $val = date($timeFormat, $timestr);
                
                if(preg_match('/_from$/', $col_name)){
                  $str_ary['remark'] .= '('.$val;
                } else if(preg_match('/_to$/', $col_name)) {
                  $str_ary['remark'] .= '-'.$val.')、';
                }
              }else{
                $val = '';
              }
              
              $vocation_hours = $str_ary['vocation_hours'];
              $overtime_hours = $str_ary['overtime_hours'];

              $vocation_col_name = str_replace('{stamp}', 'vocation', $col_name);
              $str_ary[$vocation_col_name] = $vocation_hours > 0 ? $val : '';

              $overtime_col_name = str_replace('{stamp}', 'overtime', $col_name);
              $str_ary[$overtime_col_name] = $overtime_hours > 0 ? $val : '';

              if ($vocation_hours == 0 && $overtime_hours == 0) {
                $str_ary['remark'] = '';
              }
              
            break ;
            default:break;
          } //..switch
          
          if (preg_match('/^[a-z\_]+$/', $col_name)) {
            $str_ary[ $col_name ] = $val;
          }
         
      }
      //合併儲存格
      if($str_ary['date']==$mergeCells){
        if($str_ary['overtime_hours']>0 || $str_ary['vocation_hours']>0){
          //有效的 合併
          $final_date = end($result)['date'];
          $result[$final_date]['remark'] .= $str_ary['remark'];
          $result[$final_date]['overtime_hours'] += $str_ary['overtime_hours'];
          $result[$final_date]['vocation_hours'] += $str_ary['vocation_hours'];
        }
        
      }else{
        $result[$str_ary['date']] = $str_ary;
      }
  }
  // dd($result);
  return $result;
}

function sError($str){
  return ['error'=>$str];
}




/**
 *  依上傳檔案數執行
 *  @modify 2017-10-23
 *  @
 *  
 */
foreach ($files as $fileInfo) {
    // 呼叫封裝好的 function
    $res = $api->uploadFile($fileInfo,array('xlsx', 'xls', 'csv'),2097152,false,RP('/Uploads'));
    
    if (empty($res['dest'])) { continue; }
    // 上傳成功，將實際儲存檔名存入 array（以便存入資料庫）
    $file = $res['dest'];

    $objPHPExcel = PHPExcel_IOFactory::load($file);
    $sheetNo = $objPHPExcel->getSheetCount(); //檔案sheet頁數
    // 撈每頁內容
    for($sheetRow=0; $sheetRow <= $sheetNo-1; $sheetRow++){

      // 讀取資料
      $sheet = $objPHPExcel->getSheet($sheetRow); // 讀取工作表(編號從 0 開始)
      $highestRow = $sheet->getHighestRow(); 		// 取得該頁總列數
      $page = $sheetRow+1;
        
      $data = getDataBySheet($sheet, $page, $highestRow);
      // dd($data);
      if(isset($data['error'])){ $wrongMsgArray[]=$data['error'];continue; }
      if(count($data)==0){ continue; }
      
      // 準備寫入資料庫的資料
      foreach($data as $dv){
        $staff_id = $dv['staff_id'];
        $date = $dv['date'];
        $all[$staff_id.'-'.$date] = $dv;
        if(empty($date_array[$date])){$date_array[$date] = $date;}
      }
      $staff_id_array[$staff_id] = $staff_id;
      // if($sheetRow>5){break;}    // for test..
    }
    
    
    $all_count = count($all);
        
    //檢查是否存在記錄
    $sids = join(',',$staff_id_array);
    $dates = "'".join("','",$date_array)."'";
    
    $exist = $attendance->read(['id','staff_id','date','checkin_hours','checkout_hours','work_hours_total','late','early','vocation_hours','overtime_hours']
    ," where staff_id in ($sids) and date in ($dates)")->map('staff_id,date',true);
    
    // dd($exist);
    // dd($all);
    // dd(microtime(true) - $time_start);
    
    $update = array();
    
    foreach($exist as $key => &$val){
      unset($val['_ORDER_POSITION']);
      $e_id = $val['id'];
      unset($val['id']);
      if( isset($all[$key]) ){
        foreach( $val as $kk => &$vv ){
          $invar=$all[$key][$kk];
          if($vv==$invar || (is_null($vv)&&$invar=='NULL')){continue;}
          if( strpos($vv,$invar)===false || strlen($vv)!=strlen($invar)){
            $update[$e_id]=$all[$key]; 
            // var_dump("$kk = o[$vv], new[$invar]");
            break; 
          }
        }
        unset($all[$key]); 
      }
    }
    
    $loop_time += microtime(true) - $time_start;
    $time_start = microtime(true);
    
    // dd($update);
    $insert_count = count($all);
    if( $insert_count>0){
      foreach($all as &$val){
        foreach($val as &$vv){$vv = "'$vv'";}
        $attendance->addStorage($val);
      }
      $insert_count = $attendance->addRelease();
    }
        
    $insert_time += microtime(true) - $time_start;
    $time_start = microtime(true);
        
    // 有空在試 虛擬表 update
    // CREATE TEMPORARY TABLE temp ... ENGINE = MEMORY;
    // INERT INTO temp ... VALUES ..., ...;
    // UPDATE target, temp SET target.name = temp.name WHERE target.id = temp.id;
    
    $update_count = count($update);
    if( $update_count>0){
      foreach($update as $uid => &$uval){
        $attendance->update($uval,$uid);
      }
      
    }
    $update_time += microtime(true) - $time_start;
          
    
}//for files


//紀錄
  $self_id = $api->SC->getId();
  $record = new \Model\Business\RecordAdmin( $self_id );
  if($insert_count>0){
    $record_data = ['insert_count'=>$insert_count, 'insert_time'=>$insert_time];
    $record->type($record::TYPE_EXCEL)->add( $record_data );
  }else if($update_count>0){
    $record_data = ['update_count'=>$update_count, 'update_time'=>$update_time];
    $record->type($record::TYPE_EXCEL)->update( $record_data );
  }
  
  
  
  


  $api->setArray(array(
   'insert_count' => $insert_count,
   'insert_time' => $insert_time,
   'update_count' => $update_count,
   'update_time' => $update_time,
   'all_count' => $all_count,
   'loop_time' => $loop_time,
   'error_record' => join(',',$wrongMsgArray)
  ));
  
print $api->getJSON();
?>
