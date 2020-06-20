<?php

include __DIR__."/../ApiCore.php";
include BASE_PATH.'/Model/dbBusiness/Multiple/CommentCenter.php';

use Model\Business\Multiple\CommentCenter;

$api = new ApiCore($_REQUEST);

if( $api->SC->isLogin() && $api->checkPost(array('comment_id','do')) ){
  
  $do = $api->post('do');
  
  $condition = $api->condition(array(
    'comment_id' => $api->post('comment_id'),
    'status' => 1,
    'do' => $do,
    'content' => $api->post('content')
  ));
  
  $comment = new CommentCenter();
  
  $self_id = $api->SC->getId();
  
  if( $do=="del"){
    
    $result = $comment->deleteComment( $condition['comment_id'], $self_id );
    
  }else if($do=="upd"){
    if( empty($condition['content']) ){ $api->denied('Has No Content.'); }
    
    $result = $comment->updateComment( $condition['comment_id'], $self_id, $condition['content'] );
    
  }else{
    $api->denied('Wrong Mode.');
  }
  
  if($result){
    //成功結果
    $api->setArray($result);
  }else{
    $api->denied('Not Work.');
  }
  
}else{
  $api->denied();
}

print $api->getJSON();

?>