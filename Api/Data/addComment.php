<?php

include __DIR__."/../ApiCore.php";
include BASE_PATH.'/Model/dbBusiness/Multiple/CommentCenter.php';

use Model\Business\Multiple\CommentCenter;

$api = new ApiCore($_REQUEST);

if( $api->SC->isLogin() && ( $api->checkPost(array('report_id','report_type','content')) || $api->checkPost(array('staff_id','year','month','content')) ) ){
  
  $target_staff_id = $api->post('staff_id');
  $param = array(
    'mode' => ($target_staff_id) ? 1 : 2,
    'self_id' => $api->SC->getId(),
    'target_staff_id' => $target_staff_id,
    'year' => $api->post('year'),
    'month' => $api->post('month'),
    'report_id' => $api->post('report_id'),
    'report_type' => $api->post('report_type'),
    'content' => $api->post('content')
  );
  
  $comment = new CommentCenter();
  // LG($param);
  $result = $comment->addComment( $param );
  
  
  if($result){
    //成功結果
    $api->setArray('ok');
  }else{
    $api->denied('Can Not Input.');
  }
  
  
}else{
  // $api->denied();
}

print $api->getJSON();

?>