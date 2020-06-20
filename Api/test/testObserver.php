<?php

include __DIR__."/../ApiCore.php";

include_once BASE_PATH.'/Model/dbBusiness/MonthlyProcessing.php';

$api = new ApiCore($_REQUEST);

use Model\Business\MonthlyProcessing;

$monthlyProcessing = new \Model\Business\MonthlyProcessing();

if($api->checkPost( array('id') ) && $api->SC->isSuperUser() ){
 $id = $api->post('id');
 $now = date("Y-m-d H:i:s");
 echo $monthlyProcessing::STATUS_CODE_REVIEW;
 exit;
 //$monthlyProcessing->update(array('status'=> $now), $id);
 $api->setArray( true);
 print $api->getJSON();
}
// 
// Model\Business\Observer\DBPropertyObject, instance of Model\Business\Department given, c