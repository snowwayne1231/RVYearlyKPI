<?php
include __DIR__."/../../ApiCore.php";
include BASE_PATH.'/Model/dbBusiness/Multiple/StaffDepartment.php';
include BASE_PATH.'/Model/dbBusiness/MonthlyProcessing.php';

$api = new ApiCore($_REQUEST);

// use Model\Business\Multiple\ProcessReport;
use Model\Business\Multiple\StaffDepartment;
use Model\Business\MonthlyProcessing;
  
if( $api->SC->isLogin() ){
  
  $year      = $api->post('year');
  $month     = $api->post('month');
  if($api->post('staff_id')){
    $id = $api->post('staff_id');
  }else{
    $id = $api->SC->getId();
  }
  
  $condition = $api->condition(array(
    'created_staff_id'=>$id,
    'year'=>$year,
    'month'=>$month
  ));
  
  $process = new MonthlyProcessing();
  $process_data = $process->select(null,$condition,'order by month asc, type asc,created_department_id asc');
  
  $sd = new StaffDepartment();
  
  $sd->collect();
  $sd_map = $sd->map();
  
  $team_map = $sd->team->map();
  
  $result = array();
  // stamp();
  if( count($process_data) > 0){
    
    foreach($process_data as $key=>&$val){
      
      if((int)$val['status_code']==5){continue;}
      
      $owner = $sd_map[ $val['owner_staff_id'] ];
      $created_staff = $sd_map[ $val['created_staff_id'] ];
      
      $val['created_unit_name'] = $team_map[ $val['created_department_id'] ][ 'unit_name' ];
      $val['created_unit_id'] = $team_map[ $val['created_department_id'] ][ 'unit_id' ];
      $val['created_name'] = $created_staff['name'];
      $val['created_name_en'] = $created_staff['name_en'];
      $val['owner_name'] = $owner['name'];
      $val['owner_name_en'] = $owner['name_en'];
      $val['staff_count'] = $sd->getStaffCounts( $val['created_department_id'] );
      // stamp_log(__FILE__.' * LINE = '.__LINE__);
      $result[] = $val;
      
    }
    
  }
  
  $api->setArray( $result );
}else{
  
}

print $api->getJSON();

?>