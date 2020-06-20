<?php
ini_set('max_execution_time', 300);

include __DIR__.'/../ApiCore.php';

$api = new ApiCore($_REQUEST);

use \Model\Business\Staff;
use \Model\Business\MonthlyReportLeader;
use \Model\Business\MonthlyReport;
use \Model\Business\Department;
// $staff = new Model\Business\Staff();

if(!$api->SC->isAdmin()){ $api->denied('You Have Not Promised.'); } //管理者

$files = $api->getFiles();
if(count($files)==0){$api->denied('沒有檔案.');}

$staff = new Staff();
$team = new Department();
$general = new MonthlyReport();
$leader = new MonthlyReportLeader();
$staff_map = $staff->read(['id','staff_no'],[])->cmap('staff_no');
$team_map = $team->read(['id','unit_id'],[])->cmap('unit_id');

$time_start = microtime(true);
$loop_time = 0;
// $insert_time = 0;
// $update_time = 0;
require_once RP('/Model/PHPExcel.php');
require_once RP('/Model/PHPExcel/IOFactory.php');
include __DIR__."/common_function.php";   // 快選 excel 儲存格

$col_map = [1=>'department_code',2=>'department_name',3=>'staff_no',4=>'name',5=>'first_day',6=>'total'];
//取得資料
function getDataWithSheet(&$s){
  Global $col_map,$staff_map,$month,$api;
  $res = [];
  $final_row = $s->getHighestRow(); 		// 取得該頁總列數
  $total_col = 6;
  
  for($row=3;$row<=$final_row;$row++){
    $tmp = [];
    for($col=1;$col<=$total_col;$col++){
      $col_name = $col_map[$col];
      $tmp[$col_name] = $s->getCell(p($col,$row))->getValue();
      
    }
    if( isset($staff_map[ $tmp['staff_no'] ]) ){
      $staff = $staff_map[ $tmp['staff_no'] ];
      $id = $staff['id'];
      $tmp['id'] = $id;
      $team_id = isset($team_map[ $tmp['department_code'] ]) ? $team_map[ $tmp['department_code'] ]['id'] : 0;
      $tmp['department_id'] = $team_id;
      if( empty($tmp['total']) ){continue;}
      if( (int)$tmp['total'] > 200){$api->denied('Score Wrong With[('.$tmp['name'].') Score='.$tmp['total'].']');}
      $res[$id] = $tmp;
    }
  }
  
  return $res;
}


//開始更新
function updateByData($data){
  Global $general, $leader, $skill_map;
    $year = 2017;
    $month_ary = [4];
    foreach($data as $m=>$v){
      $month_ary[]=$m;
    }
    // dd($data);
    $update_data = [];  $insert_data = [];
    
    //本來有的績效資料
    $history_data = [];  $staff_position=[];
    $common_col = ['id','staff_id','month','year','total','post_id','title_id','owner_department_id','owner_staff_id'];  $common_where = ['year'=>$year,'month'=>'in('.join(',',$month_ary).')'];
    $leader_data = $leader->select($common_col ,$common_where);
    $general_data = $general->select($common_col ,$common_where);
    foreach($leader_data as $ld){
      if($ld['month']==4){
        $staff_position[$ld['staff_id']]='leader';
      }
      $history_data[$ld['month']]['leader'][$ld['staff_id']]=$ld;
    }
    foreach($general_data as $gd){
      if($gd['month']==4){
        $staff_position[$gd['staff_id']]='general';
      }
      $history_data[$gd['month']]['general'][$gd['staff_id']]=$gd;
    }
    // dd($staff_position);
    
    // unset($history_data[4]);dd($history_data);
    // dd($data);
    //塞選
    foreach($data as $month => $val){
      $this_month_history = isset($history_data[$month])?$history_data[$month]:[];
      
      foreach($val as $staff_id => $staff_data){
        if(empty($staff_position[$staff_id])){continue;}      //該員工 不在4月考評中
        $position = $staff_position[$staff_id];
        $type = $position=='leader'?1:2;
        $history_report = isset($this_month_history[$position])?$this_month_history[$position]:[];
        if( isset($history_report[$staff_id]) ){    //存在
          $report = $history_report[$staff_id];
          if($staff_data['total']!=$report['total']){
            $updatee = ['total'=>$staff_data['total']];
            
            addtitionStaffData($updatee, $type);
            $update_data[$position][$report['id']] = $updatee;
          }
        }else{
          $staff_data['year'] = $year;
          $staff_data['month'] = $month;
          $monthlt_4_data = $history_data[4][$position][$staff_id];
          $staff_data['post_id'] = $monthlt_4_data['post_id'];
          $staff_data['title_id'] = $monthlt_4_data['title_id'];
          $staff_data['owner_department_id'] = $monthlt_4_data['owner_department_id'];
          $staff_data['owner_staff_id'] = $monthlt_4_data['owner_staff_id'];
          
          addtitionStaffData($staff_data, $type);
          $insert_data[$position][$month][$staff_id] = $staff_data;
        }
      }
      
    }
    
    // dd($update_data);
    // dd($insert_data);
    foreach($update_data as $position_key => $u_data){
      foreach($u_data as $u_report_id=>$uu_data){
        if($position_key=='leader'){
          $leader->update($uu_data, $u_report_id);
        }else{
          $general->update($uu_data, $u_report_id);
        }
      }
    }
    
    foreach($insert_data as $position_key=>$i_data){
      foreach($i_data as $i_month=>$ii_data){
        foreach($ii_data as $i_staff_id=>$iii_data){
          if($position_key=='leader'){
            $ireport = &$leader;
            $skills = $skill_map[1];
          }else{
            $ireport = &$general;
            $skills = $skill_map[2];
          }
          $i_insert_data = [
            'title_id'=>$iii_data['title_id'],
            'staff_id'=>$i_staff_id,
            'post_id'=>$iii_data['post_id'],
            'year' =>$iii_data['year'],
            'month' =>$iii_data['month'],
            'total' =>$iii_data['total'],
            'releaseFlag' =>'Y',
            'processing_id' =>-1,
            'owner_staff_id' =>$iii_data['owner_staff_id'],
            'owner_department_id'=>$iii_data['owner_department_id']
          ];
          foreach($skills as $ss){
            $i_insert_data[$ss]=$iii_data[$ss];
          }
          $ireport->addStorage($i_insert_data);
        }
      }
    }

    $c = $leader->addRelease();
    $c += $general->addRelease();
    
    return [ count($update_data), $c ];
}

$skill_map=[
1=>['target','quality','method','error','backtrack','planning','execute','decision','resilience','attendance','attendance_members'],
2=>['quality','completeness','responsibility','cooperation','attendance']
];
function addtitionStaffData(&$o,$type){
  Global $skill_map;
  foreach($skill_map[$type] as $v){
    $o[$v]=0;
  }
}


$meta_data = [];
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
      $sheet = $objPHPExcel->getSheet($sheetRow);
      $month = (int)$sheet->getCell(p(6,1))->getValue();
      // dd($month);
      $meta_data[$month] = getDataWithSheet($sheet);
      
    }
}

$new_data = updateByData($meta_data);



$api->setArray([
  'update_count'=>$new_data[0],
  'insert_count'=>$new_data[1],
  'status'=>'OK.'
]);

print $api->getJSON();
?>