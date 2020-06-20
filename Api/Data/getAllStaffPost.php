<?php

include __DIR__."/../ApiCore.php";
include BASE_PATH.'/Model/dbBusiness/StaffPost.php';

use Model\Business\StaffPost;

$api = new ApiCore();

if( $api->SC->isAdmin() ){
  
  $staff = new StaffPost();
  
  $result = $staff->read(array('id','name','type','orderby','enable'),null,"order by orderby desc, FIELD(type,'管理職','行政職','專業職','其他')")->data;
  //成功結果
  $api->setArray($result);
  
}else{
  $api->denied();
}

print $api->getJSON();

?>