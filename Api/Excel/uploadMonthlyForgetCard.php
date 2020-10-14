<?php
ini_set('max_execution_time', 300);

include __DIR__.'/../ApiCore.php';

$api = new ApiCore($_REQUEST);

use \Model\Business\Staff;
use \Model\Business\Department;
use \Model\Business\AttendanceMonthlySpecial;
use \Model\Business\ConfigCyclical;

// if(!$api->SC->isAdmin()){ $api->denied('You Have Not Promised.'); } //管理者

$files = $api->getFiles();
if(count($files)==0){$api->denied('沒有檔案.');}

$staff = new Staff();
$team = new Department();
$attendance = new AttendanceMonthlySpecial();
$config = new ConfigCyclical();
// $leader = new MonthlyReportLeader();
$staff_map = $staff->read(['id','staff_no'],[])->cmap('staff_no');
$team_map = $team->read(['id','unit_id'],[])->cmap('unit_id');

$time_start = microtime(true);
$loop_time = 0;


require_once RP('/Model/PHPExcel.php');
require_once RP('/Model/PHPExcel/IOFactory.php');
include __DIR__."/common_function.php";

$all_sheet_datas = [];
$col_name_array = ['unit_id','unit_name','staff_no','name','outside_number', 'type', 'reason', 'status_char', 'date', 'time'];
//
foreach ($files as $fileInfo) {
    // 呼叫封裝好的 function
    $res = $api->uploadFile($fileInfo,array('xlsx', 'xls', 'csv'),2097152,false,RP('/Uploads'));
    
    if (empty($res['dest'])) { continue; }
    // 上傳成功，將實際儲存檔名存入 array（以便存入資料庫）
    $file = $res['dest'];

    $objPHPExcel = PHPExcel_IOFactory::load($file);
    
    $sheet = $objPHPExcel->getSheet(0);
    //取得 excel 資料
    $max_row = $sheet->getHighestRow();
    $zoonArray = [1,2, 10,$max_row];
    
    $sheet_data = getDataByTable($sheet, $zoonArray, $col_name_array);
    $all_sheet_datas[] = $sheet_data;
    
}
//解析資料
$meta_data=[];
$staff_ids=[];
$errors=[];
$min_date = time();
$max_date = 0;

foreach ($all_sheet_datas as $sht_data) {
  if (count($sht_data) == 0) continue;

  $first_data_date = $sht_data[0]['date'];
  $ym = $config->getWhichYearMonthWithDate($first_data_date);

  // dd($ym);
  
  $year = $ym[0];
  $month = $ym[1];
  
  foreach ($sht_data as $val){
    $err_msg = '';

    $staff_no = $val['staff_no'];
    $unit_id = $val['unit_id'];

    if (empty($staff_no)) {
      // $err_msg = printf('Unit ID: [ %s ] | Name: [ %s ] | Date: [ %s ]', $unit_id, $val['name'], $val['date']);
      continue;
    } else if (empty($unit_id)){
      $err_msg = sprintf('Staff: [ %s ] | Date: [ %s ]', $staff_no, $val['date']);
    } else if (empty($val['type'])) {
      $err_msg = sprintf('Staff: [ %s ] | Date: [ %s ]', $staff_no, $val['date']);
    } else if (empty($val['date'])) {
      $err_msg = sprintf('Staff: [ %s ] | Time: [ %s ]', $staff_no, $val['time']);
    } else if (empty($val['time'])) {
      $err_msg = sprintf('Staff: [ %s ] | Date: [ %s ]', $staff_no, $val['date']);
    }


    if (!array_key_exists($staff_no, $staff_map)) {
      $err_msg = sprintf('Staff Not Exist [ %s ]', $staff_no);
    }
    if (!isset($team_map[$unit_id])) {
      $err_msg = sprintf('Department Not Exist [ %s ]', $unit_id);
    }
    

    if (empty($err_msg)) {

      $staff_id = $staff_map[$staff_no]['id'];
      $department_id = $team_map[$unit_id]['id'];

      $replaced_date = str_replace('/', '-', $val['date']);

      $date_obj = strtotime($replaced_date);

      if ($date_obj < $min_date) {
        $min_date = $date_obj;
      } else if ($date_obj > $max_date) {
        $max_date = $date_obj;
      }

      $db_data_tmp = [
        'staff_id' => $staff_id,
        'department_id' => $department_id,
        'outside_number' => $val['outside_number'],
        'date' => $replaced_date,
        'time' => date("H:i:s", strtotime($val['time'])),
        'year' => $year,
        'month' => $month,
        'type' => (int) $val['type'],
        'reason' => trim($val['reason']),
        'remark' => trim($val['status_char']),
      ];

      if (!isset($meta_data[$staff_id])) {
        $meta_data[$staff_id] = [];
        $staff_ids[] = $staff_id;
      }

      $meta_data[$staff_id][] = $db_data_tmp;
      
    } else {
      $errors[] = $err_msg;
    }

  }
}


//
// $ddtest = [
//   'errors'=>$errors,
//   'meta_data'=>$meta_data,
//   'min_date'=>$min_date,
//   'max_date'=>$max_date,
//   'status'=>'OK.',
// ];

// dd($ddtest);


//搜尋繼存資料

$old_atten_data = $attendance->select(['id','type','staff_id','date','time', 'outside_number'],['staff_id'=>$staff_ids, 'date'=> ["BETWEEN", date('Y-m-d', $min_date), date('Y-m-d', $max_date)]]);

// dd($attendance->getSql());
$old_atten_map = [];
foreach($old_atten_data as $val){
  $old_atten_map[$val['staff_id']][$val['date']][$val['outside_number']] = $val;
}

// dd($atten_map);
// dd($meta_data);
//過濾無修正
$insert_data=[]; $update_data=[];

foreach ($meta_data as $staff_id => $val_ary){ //所有有上傳的資料
  if( isset($old_atten_map[$staff_id]) ){
    $history_atten_staff_map = $old_atten_map[$staff_id];

    foreach ($val_ary as $val) {
      $date = $val['date'];
      $has_double_date = isset($history_atten_staff_map[$date]);
      
      if ($has_double_date) {
        $outside_number = $val['outside_number'];
        $history_atten_staff_date_map = $history_atten_staff_map[$date];
        $has_double_outside_number = isset($history_atten_staff_date_map[$outside_number]);

        if ($has_double_outside_number) {
          $history_val = $history_atten_staff_date_map[$outside_number];

          $nothing_changed = $val['time'] == $history_val['time'] && $val['type'] == $history_val['type'];
          if ($nothing_changed) {
            // don't need do anything
          } else {
            $update_data[$history_val['id']] = $val;
            // $update_data[$history_val['id']]['old'] = $history_val;
          }
        } else {

          $insert_data[] = $val;

        }
      } else {

        $insert_data[] = $val;

      }
    }
  } else {
    $insert_data = array_merge($insert_data, $val_ary);
  }
}

// $ddtest = [
//   'insert_data' => $insert_data,
//   'update_data' => $update_data,
// ];
// dd($ddtest);


//插入
foreach($insert_data as $ival){
  $attendance->addStorage($ival);
}
$c = $attendance->addRelease();
//更新
foreach($update_data as $uid=>$uval){
  $attendance->update($uval,$uid);
}

//紀錄
$self_id = $api->SC->getId();
$record_data = [];
$record_data['table']=$attendance->table_name;
$record = new \Model\Business\RecordAdmin( $self_id );
if(!empty($update_data)){
  $tmp = []; 
  foreach($update_data as $id=>$v){
    $tmp[]=$id;
  }
  $record_data['data']=$tmp;
  $record->type($record::TYPE_EXCEL)->update( $record_data );
}

if(!empty($insert_data)){
  $tmp = []; 
  foreach($insert_data as $v){
    $tmp['staff_ids'] = $staff_ids;
  }
  $record_data['data']=$tmp;
  $record->type($record::TYPE_EXCEL)->add( $record_data );
}



//橘果
$api->setArray([
  'update_count'=>count($update_data),
  'insert_count'=>$c,
  'status'=>'OK.'
]);

print $api->getJSON();
?>