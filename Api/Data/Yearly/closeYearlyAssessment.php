<?php
include __DIR__."/../../ApiCore.php";
include BASE_PATH.'/Model/dbBusiness/Multiple/YearlyAssessment.php';

$api = new ApiCore($_POST);

use Model\Business\Multiple\YearlyAssessment;

  
if( $api->checkPost(array('year')) && $api->SC->isAdmin() ){
  $year = $api->post('year');
  $ya = new YearlyAssessment( $year );
  
  $result = $ya->close();
  
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