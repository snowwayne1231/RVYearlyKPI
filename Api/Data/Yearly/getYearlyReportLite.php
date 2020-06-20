<?php
include __DIR__."/../../ApiCore.php";

$api = new ApiCore($_POST);

use Model\Business\Multiple\YearlyAssessment;

  
if( $api->checkPost(array('year','department_level'))  && $api->SC->isAdmin() ){
 
  $year = $api->post('year');
  $api->SC->set('year',$year);
 
  $yfb = new YearlyAssessment($year);
  $self_id = $api->SC->getId();
  
  $department_level = $api->post('department_level');
  $result = $yfb->getYearlyReportLite( $year, $department_level);
  
  $api->setArray($result);

  
}else{
  $api->denied('You Have Not Promised.');
}

print $api->getJSON();

?>