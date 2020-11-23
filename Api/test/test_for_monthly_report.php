<?php
include __DIR__."/../ApiCore.php";

$api = new ApiCore($_REQUEST);


use Model\Business\Multiple\ProcessRouting;
// use Model\Business\Multiple\YearlyQuickly;

$api->isAdmin();
 
$year = $api->post('year');
$month = $api->post('month');

if ($year && $month) {
  $monthlyProcessing = new \Model\Business\MonthlyProcessing();

  $mothly_data = $monthlyProcessing->select(['id', 'status_code'], ['year' => $year, 'month' => $month, 'status_code' => '<5']);
  foreach ($mothly_data as $key => $val) {
    $procussRouting = new ProcessRouting($val['id'], 1);
    $procussRouting->done();
  }
  
  $api->setArray('ok');
} else {
  $api->denied('Wrong Inputs.');
}


print $api->getJSON();
?>