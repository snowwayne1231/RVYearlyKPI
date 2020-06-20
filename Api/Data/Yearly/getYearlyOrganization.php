<?php
include __DIR__."/../../ApiCore.php";
$api = new ApiCore($_POST);

use Model\Business\Multiple\YearlyFinally;

if( $api->checkPost(array('year')) && $api->SC->isLogin() ){
  
  $year = $api->post('year');
  
  $api->SC->set('year',$year);
  
  $yf = new YearlyFinally();
  
  $self = $api->SC->getMember();
  
  $result = $yf->getYearlyOrganization($year, $self);
  //結果
  $api->setArray($result);
  
  
}else{
  $api->denied('You Have Not Promised.');
}

print $api->getJSON();

?>