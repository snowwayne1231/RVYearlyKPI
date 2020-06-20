<?php

include __DIR__."/../ApiCore.php";
include BASE_PATH.'/Model/dbBusiness/StaffStatus.php';

use Model\Business\StaffStatus;

$api = new ApiCore();

if( $api->SC->isAdmin() ){
  
  $staff = new StaffStatus();
  
  $result = $staff->read()->data;
  //成功結果
  $api->setArray($result);
  
}else{
  $api->denied();
}

print $api->getJSON();

?>