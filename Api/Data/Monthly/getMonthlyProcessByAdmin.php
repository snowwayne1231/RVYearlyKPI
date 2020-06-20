<?php
include __DIR__."/../../ApiCore.php";
include BASE_PATH.'/Model/dbBusiness/Multiple/StaffDepartment.php';
include BASE_PATH.'/Model/dbBusiness/MonthlyProcessing.php';

$api = new ApiCore($_REQUEST);

// use Model\Business\Multiple\ProcessReport;
use Model\Business\Multiple\StaffDepartment;
use Model\Business\MonthlyProcessing;
use Model\Business\Multiple\MonthlyReport;

if( $api->SC->isLogin() && $api->SC->isSuperUser() && $api->checkPost(array('year','month')) ){
  
  $year      = $api->post('year');
  $month     = $api->post('month');
  $status_code=$api->post('status_code');
  
  if($status_code && strpos($status_code,',')){
    $status_code = "in ($status_code)";
  }
  
  $condition = $api->condition(array(
    'status_code'=>$status_code,
    'year'=>$year,
    'month'=>$month
  ));
  
  
  // LG($condition);
  $process = new MonthlyProcessing();
  $monthlyReport = new MonthlyReport([
    'year' => $year,
    'month' => $month,
  ]);

  // $process_data = $process->select($condition);
  $process_data = $process->select(null,$condition,'order by type asc, month asc, created_department_id asc');
  // LG($process_data);
  $sd = new StaffDepartment();
  
  $sd->collect();
  $sd_map = $sd->map();
  
  $team_map = $sd->team->map();
  
  $result = array();
  // stamp();
  if( count($process_data) > 0){

    $report_data = $monthlyReport->getReport();
    $report_map = [];
    foreach($report_data as $r_data) {
      $r_data['staff'] = $sd_map[ $r_data['staff_id'] ];
      $report_map[$r_data['processing_id']][] =$r_data;
    }

    // dd($report_map);
    
    foreach($process_data as $key=>&$val){
      
      // if((int)$val['status_code']==5){continue;}
      
      $owner = $sd_map[ $val['owner_staff_id'] ];
      $created_staff = $sd_map[ $val['created_staff_id'] ];
      
      $val['created_unit_name'] = $team_map[ $val['created_department_id'] ][ 'unit_name' ];
      $val['created_unit_id'] = $team_map[ $val['created_department_id'] ][ 'unit_id' ];
      $val['created_name'] = $created_staff['name'];
      $val['created_name_en'] = $created_staff['name_en'];
      $val['owner_name'] = $owner['name'];
      $val['owner_name_en'] = $owner['name_en'];
      $val['owner_department_name'] = $team_map[ $val['owner_department_id'] ][ 'unit_name' ];
      $val['owner_department_unit_id'] = $team_map[ $val['owner_department_id'] ][ 'unit_id' ];
      $val['staff_count'] = $sd->getStaffCounts( $val['created_department_id'] );
      $val['reports'] = $report_map[$val['id']];
      // stamp_log(__FILE__.' * LINE = '.__LINE__);
      $result[ $val['created_unit_id'] ][ $val['type'] ] = $val;
      
    }
    
  }
  
  ksort($result);
  
  $api->setArray( $result );
}else{
  $api->denied();
}

print $api->getJSON();

?>