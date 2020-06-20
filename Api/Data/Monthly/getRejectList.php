<?php
include __DIR__."/../../ApiCore.php";
include BASE_PATH.'/Model/dbBusiness/Multiple/MonthlyReport.php';

$api = new ApiCore($_POST);

use Model\Business\Multiple\MonthlyReport;

$process_id = $api->post('processing_id');
  
if( $process_id ){
  
  $mr = new MonthlyReport( );
  
  $pdata = $mr->process->select( $process_id );
  
  if(count($pdata)==0){ $api->denied("Not Found This Processing."); }
  
  $start = $pdata[0]['created_staff_id'];
  $end = $pdata[0]['owner_staff_id'];
  
  $array_manager = $mr->team->read()->getSuperArrayWithManager( $start, $end );
  
  // $array_manager = array_pop($array_manager);
  foreach($array_manager as $i => $v){
    if($v==$end){array_splice($array_manager, $i, 1);}
  }
  
  // $staff_map = $staff->read()->map();
  $result = $mr->getStaffWithPath($array_manager, true);
  
  //成功結果
  $api->setArray($result);
  
}else{
  $api->denied();
}

print $api->getJSON();

?>