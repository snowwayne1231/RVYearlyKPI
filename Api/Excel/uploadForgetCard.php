<?php
ini_set('max_execution_time', 300);

include __DIR__.'/../ApiCore.php';

$api = new ApiCore($_REQUEST);

use \Model\Business\Staff;
// use \Model\Business\Department;
use \Model\Business\AttendanceSpecial;

if(!$api->SC->isAdmin()){ $api->denied('You Have Not Promised.'); } //管理者

$files = $api->getFiles();
if(count($files)==0){$api->denied('沒有檔案.');}

$staff = new Staff();
// $team = new Department();
$atten = new AttendanceSpecial();
// $leader = new MonthlyReportLeader();
$staff_map = $staff->read(['id','staff_no'],[])->cmap('staff_no');
// $team_map = $team->read(['id','unit_id'],[])->cmap('unit_id');

$time_start = microtime(true);
$loop_time = 0;


require_once RP('/Model/PHPExcel.php');
require_once RP('/Model/PHPExcel/IOFactory.php');
include __DIR__."/common_function.php"; 


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
    $zoonArray = [1,3,6,$max_row];
    $col_name_array = ['unit_id','unit_name','staff_no','name','nocard','forgetcard'];
    $key_name = 'staff_no';
    $sheet_data = getDataByTable($sheet, $zoonArray, $col_name_array, $key_name);
    
    $year = $sheet->getCell(p(1,1))->getValue();
    
}
//解析資料
$meta_data=[];  $ids=[];
foreach($sheet_data as $no=>$val){
  if(empty($val['nocard']) && empty($val['forgetcard'])){continue;}
  if(empty($staff_map[$no])){continue;}
  $staff_id = $staff_map[$no]['id'];
  $meta_data[$staff_id] = $val;
  $ids[]=$staff_id;
}
//搜尋繼存資料
$all_atten_type = [$atten::TYPE_NOCARD, $atten::TYPE_FORGETCARD];
$map_atten_type = [$atten::TYPE_NOCARD=>'nocard', $atten::TYPE_FORGETCARD=>'forgetcard'];
$atten_data = $atten->select(['id','type','staff_id','value'],['staff_id'=>$ids,'type'=>$all_atten_type,'year'=>$year]);
$atten_map = [];
foreach($atten_data as $val){
  $atten_map[$val['staff_id']][$val['type']] = $val;
}

// dd($atten_map);
// dd($meta_data);
//過濾無修正
$insert_data=[]; $update_data=[];
foreach($meta_data as $staff_id => $val){ //所有有上傳的資料
  if( isset($atten_map[$staff_id]) ){
    $this_history_atten = $atten_map[$staff_id];
    foreach($all_atten_type as $type){    //歷遍兩種類型
      $new_val = $val[$map_atten_type[$type]];
      if($new_val<=0){continue;}
      if( isset($this_history_atten[$type]) ){
        $ts = $this_history_atten[$type];
        if($new_val==$ts['value']){continue;}
        $update_data[ $ts['id'] ] = ['value'=> $new_val ];
      }else{
        $insert_data[] = ['type'=>$type, 'value'=>$val[$map_atten_type[$type]], 'staff_id'=> $staff_id, 'year'=>$year];
      }
    }
  }else{
    if(!empty($val['nocard']) && $val['nocard']!=0){ $insert_data[] = ['type'=>$atten::TYPE_NOCARD, 'value'=>$val['nocard'], 'staff_id'=> $staff_id, 'year'=>$year]; }
    if(!empty($val['forgetcard']) && $val['forgetcard']!=0){ $insert_data[] = ['type'=>$atten::TYPE_FORGETCARD, 'value'=>$val['forgetcard'], 'staff_id'=> $staff_id, 'year'=>$year]; }
  }
}
// dd($update_data);
// dd($insert_data);

//插入
foreach($insert_data as $ival){
  $atten->addStorage($ival);
}
$c = $atten->addRelease();
//更新
foreach($update_data as $uid=>$uval){
  $atten->update($uval,$uid);
}

//紀錄
  $self_id = $api->SC->getId();
  $record_data = [];
  $record_data['table']=$atten->table_name;
  $record_data['year']=$year;
  $record = new \Model\Business\RecordAdmin( $self_id );
  if(!empty($update_data)){
    $tmp = []; 
    foreach($update_data as $id=>$v){
      $tmp[$id]=$v['value'];
    }
    $record_data['data']=$tmp;
    $record->type($record::TYPE_EXCEL)->update( $record_data );
  }
  if(!empty($insert_data)){
    $tmp = []; 
    foreach($insert_data as $v){
      $tmp[$v['staff_id']]=$v['value'];
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