<?php
include __DIR__."/../../ApiCore.php";

$api = new ApiCore($_POST);

use Model\Business\Multiple\YearlyFeedback;

  
if( $api->checkPost(array('year')) && $api->SC->isLogin() ){
  
  $year = $api->post('year');
  
  $api->SC->set('year',$year);
  
  $yfb = new YearlyFeedback( $year );
  // $mode = $api->post('mode');
  
  $result = $yfb->getFeedbackWithStaff( $api->SC->getId() );
  
  //結果
  $api->setArray($result);
  
  
}else{
  $api->denied('You Have Not Promised.');
}

print $api->getJSON();

?>