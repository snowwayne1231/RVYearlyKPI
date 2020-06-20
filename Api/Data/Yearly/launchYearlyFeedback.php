<?php
include __DIR__."/../../ApiCore.php";
include BASE_PATH.'/Model/dbBusiness/Multiple/YearlyFeedback.php';

$api = new ApiCore($_POST);

use Model\Business\Multiple\YearlyFeedback;

  
if( $api->checkPost(array('year')) && $api->SC->isAdmin() ){
  
  $yfb = new YearlyFeedback();
  $self_id = $api->SC->getId();
  $year = $api->post('year');
  
  
  $result = $yfb->launchFeedback( $year );
  
  //結果
  if( isset($result['error']) ){
    $api->denied($result['error']);
  }else{
    $process = $yfb->config->select(array('processing'),array('year'=>$year))[0]['processing'];
    $api->setArray(array('status'=>"OK.",'change'=>$result,'processing'=>$process));
    //紀錄
    // $self_id = $api->SC->getId();
    $record_data = $api->getPost();
    $record = new \Model\Business\RecordAdmin( $self_id );
    $record->type($record::TYPE_YEAR)->update( $record_data );
      
  }
  
  
}else{
  $api->denied('You Have Not Promised.');
}

print $api->getJSON();

?>