<?php
include __DIR__."/../../ApiCore.php";
include BASE_PATH.'/Model/dbBusiness/Multiple/YearlyFeedback.php';

$api = new ApiCore($_POST);

use Model\Business\Multiple\YearlyFeedback;

  
if( $api->checkPost(array('year')) && $api->SC->isAdmin() ){
  
  $yfb = new YearlyFeedback();
  
  $result = $yfb->close( $api->post('year') );
  
  //結果
  if( isset($result['error']) ){
    $api->denied($result['error']);
  }else{
    $api->setArray($result);
    
    //紀錄
    $self_id = $api->SC->getId();
    $record_data = $api->getPost();
    $record = new \Model\Business\RecordAdmin( $self_id );
    $record->type($record::TYPE_YEAR)->update( $record_data );
    
    
  }
  
}else{
  $api->denied('You Have Not Promised.');
}

print $api->getJSON();

?>