<?php
include __DIR__."/../../ApiCore.php";

$api = new ApiCore($_POST);

use Model\Business\Multiple\YearlyAssessment;

if( $api->checkPost(array('year'))  && $api->SC->isLogin() ){
 
  $year = $api->post('year');
  
  $ya = new YearlyAssessment( $year );
  
  $self = $api->SC->getId();
  
  $result = $ya->getYearlyReportMyTranscripts($self);
  
  $api->setArray($result);
  
  
}else{
  $api->denied('You Have Not Promised.');
}

print $api->getJSON();

?>