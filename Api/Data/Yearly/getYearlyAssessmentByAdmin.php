<?php

include __DIR__."/../../ApiCore.php";

use Model\Business\Multiple\YearlyAssessment;

$api = new ApiCore($_REQUEST);

if( $api->SC->isAdmin() ){
  
    $year = $api->post('year');
    $year = ($year) ? $year : date("Y");
    
    $api->SC->set('year',$year);
    
    $self_id = $api->SC->getId();
    
    $yearly_assessment = new YearlyAssessment($year);
    
    $result = $yearly_assessment->getYearlyAssessmentByAdmin( $self_id);
    
    $api->setArray($result);
  
} else {
  $api->denied('You Have Not Promised.');
}



print $api->getJSON();