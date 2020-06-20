<?php

include __DIR__."/../ApiCore.php";
include BASE_PATH.'/Model/dbBusiness/Staff.php';
include BASE_PATH.'/Model/dbBusiness/MonthlyReport.php';

$api = new ApiCore($_POST);
$api->denied('No Open This API.');

use Model\Staff;
use Model\MonthlyReport;
  
$id = $api->post('id');
  
if( $id ){
  
  $staff = new Staff();
  
  $st = $staff->select($id);
  
  if($st[0]['is_leader']==1){
    $api->denied();
    $api->setMsg('can not delect leader.');
  }else{
    
    $staff->delete($id);
    
    $report = new MonthlyReport();
    
    $year = date('Y');
    $month = date('m');
    
    $report->delete( array('staff_id'=>$id,'year'=>$year,'month'=>$month) );
    
    
    
    $api->setArray("ok");
    
  }
  
}else{
  // var_dump($_POST);
}

print $api->getJSON();

?>