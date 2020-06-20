<?php
include __DIR__."/../../ApiCore.php";
$api = new ApiCore($_POST);

use Model\Business\Multiple\YearlyFeedback;

$year = $api->post('year');
try {
  if( $year && $api->SC->isAdmin() ){
    $feedback = new YearlyFeedback(); 
    $return = $feedback->getYearlyFeedBackStatistics($year);
    $api->setArray($return);
  }else{
    $api->denied('You Have Not Promised.');
  }
} catch (\Exception $ex) {
  $api->denied($ex);
}


print $api->getJSON();