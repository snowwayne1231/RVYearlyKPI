<?php

include __DIR__."/../ApiCore.php";
include BASE_PATH.'/Model/dbBusiness/Multiple/CommentCenter.php';

use Model\Business\Multiple\CommentCenter;
use Model\Business\Multiple\Leadership;

$api = new ApiCore($_REQUEST);

if( $api->SC->isLogin() && ( $api->checkPost(array('report_id','report_type')) || $api->checkPost(array('staff_id','year','month')) ) ){
  
  $target_staff_id = $api->post('staff_id');
  $self_id = $api->SC->getId();
  $leadership = new Leadership($self_id);

  if ($target_staff_id && !$leadership->isMyUnderStaff($target_staff_id)) {
    if (!$api->SC->isAdmin() && $target_staff_id != $self_id) {
      $api->denied('Is Not My Subordinate.');
    }
  }

  $param = array(
    'mode' => ($target_staff_id) ? 1 : 2,
    'self_id' => $self_id,
    'target_staff_id' => $target_staff_id,
    'year' => $api->post('year'),
    'month' => $api->post('month'),
    'report_id' => $api->post('report_id'),
    'report_type' => $api->post('report_type')
  );
  
  $comment = new CommentCenter();
  
  $result = $comment->getComment( $param );
  
  $self_rank = $api->SC->getMember()['rank'];
  
  // foreach($result['comments'] as &$v){
    // if($v['_created_staff_rank'] > $self_rank){ $v['content'] = '你沒有權限觀看此則評論.'; }
  // }
  
  
  if($result){
    // LG($result);
    //成功結果
    $api->setArray($result);
  }else{
    $api->denied('Not Found Report.');
  }
  
}else{
  // $api->denied();
}

print $api->getJSON();

?>