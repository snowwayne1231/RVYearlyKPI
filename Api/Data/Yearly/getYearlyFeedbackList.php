<?php
include __DIR__."/../../ApiCore.php";

$api = new ApiCore($_POST);

use Model\Business\Multiple\YearlyFeedback;

  
if( $api->checkPost(array('year'))  && $api->SC->isAdmin() ){
 
  $yfb = new YearlyFeedback();
  $choice = $yfb->choice;
  
  $result = [];
  
  $self_id = $api->SC->getId();
  $year = $api->post('year');
  $id = $api->post('feedback_id');
  $result['list'] = $yfb->getYearlyFeedbackList( $year, $id);
  
  $cids = [];
  foreach($result['list'][0]['multiple_choice_json'] as $mcjid=>$v){
    $cids[]=$mcjid;
  }
  $result['choice'] = $choice->select( ['id','title'], ['id'=>$cids],'order by sort');
  
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