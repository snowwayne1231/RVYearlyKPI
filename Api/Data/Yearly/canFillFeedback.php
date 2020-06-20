<?php

include __DIR__."/../../ApiCore.php";


use Model\Business\Multiple\YearlyFeedback;

$api = new ApiCore($_REQUEST);
try {
  if( $api->SC->isLogin() ){
    $year = $api->post('year');
    $year = ($year) ? $year : date("Y");
    $yfb = new YearlyFeedback();
    $result = $yfb->canFillFeedback($year);
    $api->setArray($result);
  }
} catch (\Exception $ex) {
  $api->denied($ex);
}


print $api->getJSON();