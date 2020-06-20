<?php
include __DIR__.'/../ApiCore.php';
use Model\Business\Multiple\YearlyAssessment;
if( empty($_SERVER['HTTP_HOST']) ){
  //被命令呼叫
  $api = new ApiCore($argv);
}else{
  //被網頁呼叫
  $api = new ApiCore($_REQUEST); 
}
$year = ($api->post('year')) ? $api->post('year') : date('Y');
$now = ($api->post('now')) ? $api->post('now') : date("Y-m-d");
try {
  $yearly_assessment = new Model\Business\Multiple\YearlyAssessment('');
  $result  = $yearly_assessment->notifyOverdeadline($year, $now);
  $api->setArray($result); 
} catch (\Exception $ex) {
  $api->denied($ex);
}
echo $api->getJSON();
?>

