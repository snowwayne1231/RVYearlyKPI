<?php
include __DIR__."/../../ApiCore.php";


$api = new ApiCore($_POST);

use Model\Business\Multiple\YearlyAssessment;

try {
  if( $api->checkPost(array('assessment_id')) && $api->SC->isLogin() ){
    $year = $api->SC->get('year');
    $yearlyAssessment = new YearlyAssessment($year);
    $assessment_id = $api->post('assessment_id');
    $res = $yearlyAssessment->getYearlyAllReportWord( $api->SC->getId(), $assessment_id );
    $api->setArray($res);
  }
} catch (\Exception $ex) {
  $api->denied($ex);
}


print $api->getJSON(); 
