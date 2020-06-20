<?php

include __DIR__."/../ApiCore.php";
include BASE_PATH.'/Model/dbBusiness/Department.php';

use Model\Business\Department;

$api = new ApiCore();

if( $api->SC->isAdmin() ){
  
  $team = new Department( );
  
  $result = $team->read()->data;
  //成功結果
  $api->setArray($result);
  
}else{
  $api->denied();
}

print $api->getJSON();

?>