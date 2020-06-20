<?php
include __DIR__."/../../ApiCore.php";
include BASE_PATH.'/Model/dbBusiness/Multiple/YearlyAssessment.php';

$api = new ApiCore($_POST);

use Model\Business\Multiple\YearlyAssessment;

  
if($api->checkPost(['year']) && $api->SC->isLogin() && ($api->SC->isTopLeader() || $api->SC->isCEO())){
  
  $year = $api->post('year');
  
  $ydr = new YearlyAssessment($year);
  
  $result = $ydr->getDistributionRate();
  
  //結果
  $api->setArray($result);
  
  
}else{
  $api->denied('You Have Not Promised.');
}

print $api->getJSON();

?>