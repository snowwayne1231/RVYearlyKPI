<?php
include __DIR__."/../../ApiCore.php";
include BASE_PATH.'/Model/dbBusiness/RecordYearPerformanceQuestions.php';

$api = new ApiCore($_POST);

use Model\Business\RecordYearPerformanceQuestions;


if( $api->checkPost(array('year')) && $api->SC->isLogin() ){

  $rypq = new RecordYearPerformanceQuestions();
  // $from_type = $api->post('from_type');

  $year = $api->post('year');
  $result = $rypq->select( array('id','highlight','content','create_date'), array('year'=>$year,'target_staff_id'=>0) );


  //結果
  $api->setArray($result);


}else{
  $api->denied('You Have Not Promised.');
}

print $api->getJSON();

?>