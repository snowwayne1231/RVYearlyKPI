<?php
include __DIR__."/../../ApiCore.php";
include_once BASE_PATH.'/Model/dbBusiness/Multiple/YearlyFeedback.php';
include_once BASE_PATH.'/Model/ToolKit/utf8Chinese.php';


$api = new ApiCore($_POST);

use Model\Business\Multiple\YearlyFeedback;
use Model\ToolKit\utf8Chinese;


if( $api->checkPost(array('feedback_id','normal_questions')) && $api->SC->isLogin() ){

  $yfb = new YearlyFeedback();
  $utf8_chinese_str = new utf8Chinese();

  $self_id = $api->SC->getId();
  $feedback_id = $api->post('feedback_id');
  $questions = $api->post('normal_questions');

  foreach($questions as &$q){
    $q = $utf8_chinese_str->gb2312_big5($q);
  }

  $fc = $api->post('multiple_choice');


  $result = $yfb->saveFeedbackMultipleChoice( $feedback_id, $self_id, $fc, $questions, false );

  //結果
  if( isset($result['error']) ){
    $api->denied($result['error']);
  }else{
    $api->setArray('OK.');
  }


}else{
  $api->denied('You Have Not Promised.');
}

print $api->getJSON();

?>