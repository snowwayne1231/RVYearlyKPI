<?php
include __DIR__."/../../ApiCore.php";
include BASE_PATH.'/Model/dbBusiness/Multiple/YearlyAssessment.php';

$api = new ApiCore($_POST);
use Model\Business\Multiple\YearlyAssessment;

  if( $api->checkPost(array('assessment_id')) && $api->SC->isLogin() ){
    
    $year = $api->SC->get('year');
    
      $ya = new YearlyAssessment( $year );
      $reason = $api->post('reason');
      $self_id = $api->SC->getId();
      $result = $ya->rejectYearlyAssessment( $api->post('assessment_id'), $reason, $self_id, $api->SC->isAdmin() );
      
      
      $api->setArray($result);
      
  }else{
      $api->denied('You Have Not Promised.');
  }

print $api->getJSON();
?>