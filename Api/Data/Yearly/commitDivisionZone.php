<?php
include __DIR__."/../../ApiCore.php";
include BASE_PATH.'/Model/dbBusiness/Multiple/YearlyAssessment.php';

$api = new ApiCore($_POST);

use Model\Business\Multiple\YearlyAssessment;

$division_id = $api->post('division_id');

if( $division_id && $api->SC->isLogin() ){

  $year = $api->SC->get('year');
  
  $ya = new YearlyAssessment( $year );
  $self_id = $api->SC->getId();
  $result = $ya->commitDivisionZone( $division_id, $self_id );

  //結果
  if( isset($result['error']) ){
    $api->denied($result['error']);
  }else{
    $api->setArray($result);
  }


}else{
  $api->denied('You Have Not Promised.');
}

print $api->getJSON();



?>