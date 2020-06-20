<?php
include __DIR__."/../../ApiCore.php";
include BASE_PATH.'/Model/dbBusiness/Multiple/YearlyFeedback.php';

$api = new ApiCore($_POST);

use Model\Business\Multiple\YearlyFeedback;

if( $api->checkPost(array('feedback_id','multiple_choice')) && $api->SC->isLogin() ){

  $yfb = new YearlyFeedback();

  $fc = $api->post('multiple_choice');

  if(!is_array($fc)){ $api->denied('Multiple Choice Is Not Right Json.'); }
  if(count($fc)==0){ $api->denied('Multiple Choice Is Empty.'); }

  $result = $yfb->saveFeedbackMultipleChoice( $api->post('feedback_id'), $api->SC->getId(), $fc );

  //結果
  if( isset($result['error']) ){
    $api->denied($result['error']);
  }else{
    $api->setArray($result);
  }


}else{
  $api->denied('You Have Not Promised.');
}

print $api->getJSON();

?>