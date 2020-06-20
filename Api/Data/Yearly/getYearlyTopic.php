<?php
include __DIR__."/../../ApiCore.php";
include BASE_PATH.'/Model/dbBusiness/Multiple/YearlyAssessment.php';

$api = new ApiCore($_POST);

use Model\Business\Multiple\YearlyAssessment;

  
if( $api->checkPost(array('year')) && $api->SC->isLogin() ){
  
  $year = $api->post('year');
  
  $ya = new YearlyAssessment($year);
  
  $result = $ya->getFullTopic($year);
  //結果
  $api->setArray($result);
  
  
}else{
  $api->denied('You Have Not Promised.');
}

print $api->getJSON();

?>