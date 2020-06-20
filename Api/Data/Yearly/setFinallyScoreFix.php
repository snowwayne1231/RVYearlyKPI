<?php
include __DIR__."/../../ApiCore.php";
$api = new ApiCore($_POST);

use Model\Business\Multiple\YearlyAssessment;

$assessment_change = $api->post('assessment_change');
$division_id = $api->post('division_id');


if( $assessment_change && $division_id && $api->SC->isLogin() ){
  
  if( !is_array($assessment_change) ){ $this->error('Param [assessment_change] Is Wrong Format.'); }
  
  $year = $api->SC->get('year');
  if(empty($year)){$api->denied('Year Not Setting.');}
  
  $yearlyAssessment = new YearlyAssessment( $year ); 
  $result = $yearlyAssessment->setFinallyScoreFix($division_id, $assessment_change, $api->SC->getId() );
  
  $api->setArray($result);
  
} else {
  $api->denied('You Have Not Promised.');
}



print $api->getJSON();