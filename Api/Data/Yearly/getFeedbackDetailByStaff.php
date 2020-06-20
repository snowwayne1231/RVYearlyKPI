<?php
include __DIR__."/../../ApiCore.php";
use Model\Business\Multiple\YearlyFeedback;

$api = new ApiCore($_POST);

if( $api->checkPost(array('staff_id','year')) && $api->SC->isLogin() ){
  
  $feedback = new YearlyFeedback;
  
  $staff_id = $api->post('staff_id');
  $year = $api->post('year');
  
  $data = $feedback->getFeedbackDetailByStaff($staff_id,$year);
  
  $api->setArray($data);
  
}else{
  $api->denied('You Have Not Promised.');
}

print $api->getJSON();

?>