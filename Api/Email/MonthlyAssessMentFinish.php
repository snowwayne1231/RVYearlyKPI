<?php
include __DIR__.'/../ApiCore.php';

include_once BASE_PATH.'/Model/dbBusiness/Multiple/ProcessRouting.php';



if( empty($_SERVER['HTTP_HOST']) ){
  //被命令呼叫
  $api = new ApiCore($argv);
  
}else{
  //被網頁呼叫
  $api = new ApiCore($_REQUEST); 
}
//只催該月的
$year = ($api->post('year')) ? $api->post('year') : '';
$month = ($api->post('month')) ? $api->post('month') : '';

$process = new Model\Business\Multiple\ProcessRouting('');
$result  = $process->notifyAdminAssessMentFinish($year, $month);
$api->setArray($result); 
echo $api->getJSON();
?>

