<?php
include __DIR__."/../../ApiCore.php";


$api = new ApiCore($_POST);

use Model\Business\Multiple\YearlyAssessment;

try {
  if( $api->checkPost(array('assessment_id')) && $api->SC->isLogin() ){
    
    $year = $api->SC->get('year');
    
    if( empty($year) ){$api->denied('Not Setting Year.');}
    
    $yearlyAssessment = new YearlyAssessment($year);
    $assessment_id = $api->post('assessment_id');
    $res = $yearlyAssessment->getYearlyHistoryRecord( $api->SC->getId(), $assessment_id, false );
    $api->setArray($res);
  }
} catch (\Exception $ex) {
  $api->denied($ex);
}


print $api->getJSON(); 
