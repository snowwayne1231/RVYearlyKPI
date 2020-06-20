<?php
include __DIR__."/../../ApiCore.php";
include BASE_PATH.'/Model/dbBusiness/Multiple/YearlyAssessment.php';

$api = new ApiCore($_POST);
use Model\Business\Multiple\YearlyAssessment;

  if( $api->checkPost(array('division_id','year')) && $api->SC->isAdmin() ){
    
    $year = $api->post('year');
    $division_id = $api->post('division_id');
    
      $ya = new YearlyAssessment( $year );
      // $reason = $api->post('reason');
      $self_id = $api->SC->getId();
      $result = $ya->rejectDivisionZone( $division_id, $self_id, true );
      
      
      $api->setArray($result);
      
  }else{
      $api->denied('You Have Not Promised.');
  }

print $api->getJSON();
?>