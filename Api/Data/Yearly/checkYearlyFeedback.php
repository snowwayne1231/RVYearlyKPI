<?php
include __DIR__."/../../ApiCore.php";

$api = new ApiCore($_POST);

use Model\Business\Multiple\YearlyFeedback;

  
if( $api->checkPost(array('year')) && $api->SC->isAdmin() ){
  
  $year = $api->post('year');
  $yfb = new YearlyFeedback( $year );
  
  $reset = !!$api->post('reset');
  if($reset){
    $result = $yfb->deleteFeedback( );
  }else{
    $result = $yfb->checkFeedback( );
  }
    
  
  //結果
  if( isset($result['error']) ){
    $api->denied($result['error']);
  }else{
    $process = $yfb->config->select(array('processing'),array('year'=>$year))[0]['processing'];
    $api->setArray(array('status'=>"OK.",'change'=>$result,'processing'=>$process));
    //紀錄
    $self_id = $api->SC->getId();
    $record_data = $api->getPost();
    $record = new \Model\Business\RecordAdmin( $self_id );
    if($reset){
      $record->type($record::TYPE_YEAR_REPORT)->delete( $record_data );
    }else{
      $record->type($record::TYPE_YEAR_REPORT)->add( $record_data );
    }
    
  }
  
  
}else{
  $api->denied('You Have Not Promised.');
}

print $api->getJSON();

?>