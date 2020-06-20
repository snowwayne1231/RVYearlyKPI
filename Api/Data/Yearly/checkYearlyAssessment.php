<?php
include __DIR__."/../../ApiCore.php";
include BASE_PATH.'/Model/dbBusiness/Multiple/YearlyAssessment.php';

$api = new ApiCore($_POST);

use Model\Business\Multiple\YearlyAssessment;

  
if( $api->checkPost(array('year')) && $api->SC->isAdmin() ){
  
  $year = $api->post('year');
  
  $ya = new YearlyAssessment( $year );
  
  $reset = !empty($api->post('reset'));
  
  if($reset){
  	$result = $ya->deleteAssessment();
  }else{
  	$result = $ya->checkA();
  }
  
  //結果
  $api->setArray($result);
  
  //紀錄
  $self_id = $api->SC->getId();
  $record_data = $api->getPost();
  $record = new \Model\Business\RecordAdmin( $self_id );
  if($reset){
    $record->type($record::TYPE_YEAR_REPORT)->delete( $record_data );
  }else{
    $record->type($record::TYPE_YEAR_REPORT)->add( $record_data );
  }
  
  
}else{
  $api->denied('You Have Not Promised.');
}

print $api->getJSON();

?>