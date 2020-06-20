<?php
include __DIR__."/../../ApiCore.php";
include BASE_PATH.'/Model/dbBusiness/Multiple/YearlyQuestionCenter.php';

$api = new ApiCore($_POST);

use Model\Business\Multiple\YearlyQuestionCenter;

  
if( $api->checkPost(array('year','staff_id')) && $api->SC->isLeader() ){
  
  $year = $api->post('year');
  $staff_id = $api->post('staff_id');
  $yqc = new YearlyQuestionCenter();
  
  $result = $yqc->getQuestionsWithStaff( $year, $staff_id );
  
  //結果
  $api->setArray($result);
  
  
}else{
  $api->denied('You Have Not Promised.');
}

print $api->getJSON();

?>