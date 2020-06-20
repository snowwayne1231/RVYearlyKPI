<?php
include __DIR__."/../../ApiCore.php";


$api = new ApiCore($_POST);

use Model\Business\Multiple\YearlyAssessment;

try {
  if( $api->checkPost(array('year')) && ($api->SC->isAdmin() ||$api->SC->isLeader()) ){
    
    $year = $api->post('year');
    
    $yearlyAssessment = new YearlyAssessment($year);
    
    $res = $yearlyAssessment->getYearlySpecialStaff( $year );
    
    $api->setArray($res);
  }else{
    $api->denied('You Have Not Promised.');
  }  
} catch (\Exception $ex) {
  $api->denied($ex);
}


print $api->getJSON(); 