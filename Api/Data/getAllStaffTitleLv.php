<?php

include __DIR__."/../ApiCore.php";
include BASE_PATH.'/Model/dbBusiness/StaffTitleLv.php';

use Model\Business\StaffTitleLv;

$api = new ApiCore();

if( $api->SC->isAdmin() ){
  
  $staff = new StaffTitleLv();
  
  $result = $staff->read(array('id','name','lv','enable'),null,'order by lv asc')->data;
  //成功結果
  $api->setArray($result);
  
}else{
  $api->denied();
}

print $api->getJSON();

?>