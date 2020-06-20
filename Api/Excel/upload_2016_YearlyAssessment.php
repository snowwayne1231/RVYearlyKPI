<?php
ini_set('max_execution_time', 300);

include __DIR__.'/../ApiCore.php';

$api = new ApiCore();

use \Model\Business\Staff;
use \Model\Business\YearPerformanceReport;
use \Model\Business\YearPerformanceReportDistributionRate;
use \Model\Business\Department;
// $staff = new Model\Business\Staff();

if(!$api->SC->isAdmin()){ $api->denied('You Have Not Promised.'); } //管理者

$files = $api->getFiles();
if(count($files)==0){$api->denied('沒有檔案.');}

$staff = new Staff();
$team = new Department();
$staff_map = $staff->read(['id','staff_no'],[])->cmap('staff_no');
$team_map = $team->read(['id','unit_id'],[])->cmap('unit_id');

$time_start = microtime(true);
$loop_time = 0;
$insert_time = 0;
$update_time = 0;
require_once RP('/Model/PHPExcel.php');
require_once RP('/Model/PHPExcel/IOFactory.php');
include __DIR__."/common_function.php";   // 快選 excel 儲存格


$col_map = [1=>'department_code',2=>'department_name',3=>'staff_no',4=>'name',5=>'name_en',6=>'level'];
function getDataWithSheet(&$s){
  Global $col_map,$staff_map;
  $res = [];
  $final_row = $s->getHighestRow(); 		// 取得該頁總列數
  $level_col = 6;
  
  for($row=2;$row<=$final_row;$row++){
    $tmp = [];
    for($col=1;$col<=$level_col;$col++){
      $col_name = $col_map[$col];
      $tmp[$col_name] = $s->getCell(p($col,$row))->getValue();
      
    }
    if( isset($staff_map[ $tmp['staff_no'] ]) ){
      $id = $staff_map[ $tmp['staff_no'] ]['id'];
      $tmp['id'] = $id;
      $team_id = isset($team_map[ $tmp['department_code'] ]) ? $team_map[ $tmp['department_code'] ]['id'] : 0;
      $tmp['department_id'] = $team_id;
      if( empty($tmp['level']) ){continue;}
      $res[$id] = $tmp;
    }
  }
  
  return $res;
}



foreach ($files as $fileInfo) {
    // 呼叫封裝好的 function
    $res = $api->uploadFile($fileInfo,array('xlsx', 'xls', 'csv'),2097152,false,RP('/Uploads'));
    
    if (empty($res['dest'])) { continue; }
    // 上傳成功，將實際儲存檔名存入 array（以便存入資料庫）
    $file = $res['dest'];

    $objPHPExcel = PHPExcel_IOFactory::load($file);
    $sheetNo = $objPHPExcel->getSheetCount(); //檔案sheet頁數
    // 撈每頁內容
    // for($sheetRow=0; $sheetRow <= $sheetNo-1; $sheetRow++){
      $sheet = $objPHPExcel->getSheet(0);
      
      $data = getDataWithSheet($sheet);
      
      // dd($data);
    // }
}

$year = 2016;
$ypr = new YearPerformanceReport();
$yprdr = new YearPerformanceReportDistributionRate();

$update_data = [];

$report_map = $ypr->read(['id','staff_id','level'],['year'=>$year])->cmap('staff_id');
$rate_map = $yprdr->read(['id','name'],[])->cmap('name');
foreach($data as $id => $val){
  if(empty($rate_map[$val['level']])){ $api->denied('Error Level Name With.[ staff_no:'.$val['staff_no'].', level:'.$val['level'].' ]');break; }
  if( isset($report_map[$id]) ){
    $report = $report_map[$id];
    
    if($val['level']!=$report['level']){
      $update_data[$report['id']] = $val;
    }
    unset($data[$id]);
  }
}
// $data

$insert_data = $data;

// dd($update_data);
// dd($insert_data);

foreach($update_data as $u_report_id=>$u_data){
  $ypr->update(['level'=>$u_data['level']], $u_report_id);
}

foreach($insert_data as $i_id=>$i_data){
  $ypr->addStorage( [
    'year'=>$year,
    'staff_id'=>$i_id,
    'owner_staff_id'=>0,
    'department_id'=>$i_data['department_id'],
    'division_id'=>0,
    'staff_is_leader'=>0,
    'staff_lv'=>0,
    'staff_post'=>'',
    'staff_title'=>'',
    'staff_title_id'=>0,
    'enable'=>1,
    'processing_lv'=>-1, // -2 作廢 , -1 設為歷史狀態, 0 設為已核準 送到CEO審核的階段, 1 送到處長審核的階段, 2 送到部長審核的階段, 3 送到員工填考評的階段
    'path'=>'[]',
    'path_lv'=>'{}',
    'before_level'=>'-',
    'monthly_average'=>0,
    'attendance_json'=>'{}',    // {"late":0,"early":0,"nocard":0,"leave":0,"paysick":0,"physiology":0,"sick":0,"absent":0}
    'assessment_json'=>'{}',
    'sign_json'=>'{}',
    'assessment_total'=>0,
    'assessment_total_division_change'=>0,
    'assessment_total_ceo_change'=>0,
    'level'=>$i_data['level'],
    'self_contribution'=>'',
    'self_improve'=>'',
    'reason'=>'',
    'upper_comment'=>'{}'
  ] );
}

$c = $ypr->addRelease();

$api->setArray([
  'update_count'=>count($update_data),
  'insert_count'=>$c,
  'status'=>'OK.'
]);

print $api->getJSON();
?>