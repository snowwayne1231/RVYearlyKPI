<?php

include __DIR__."/../../ApiCore.php";
include BASE_PATH.'/Model/dbBusiness/Multiple/ProcessRouting.php';
include_once BASE_PATH.'/Model/dbBusiness/Department.php';

$api = new ApiCore($_REQUEST);

use Model\Business\Multiple\ProcessRouting;
use Model\Business\Department;

$process_id = $api->post('processing_id');
try {
  if( $process_id && $api->SC->isLogin() ){
  
    $self_id = $api->SC->getId();
    //理由非必填
    $reason = $api->post('reason');
    
    $routing = new ProcessRouting( $process_id );
    
    //退回
    $ok = $routing->drawSingle($self_id, $api->SC->isAdmin(), $reason);
    if(!$ok){$api->denied('Error Staff Id.');}
    $api->setArray($ok);
  }else{
    $api->denied('You Have Not Promised.');
  }
} catch (\Exception $ex) {
  $api->denied($ex);
}


print $api->getJSON();

?>