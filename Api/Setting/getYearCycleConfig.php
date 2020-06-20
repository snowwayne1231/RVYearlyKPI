<?php

include __DIR__."/../ApiCore.php";

$api = new ApiCore($_POST);

use Model\Business\YearPerformanceConfigCyclical;
  
if( $api->checkPost(array('year')) ){
  
  $year = $api->post('year');
  
  $api->SC->set('year',$year);
  
  $config = new YearPerformanceConfigCyclical($year);
  
  $cdata = $config->data;
  
  unset($cdata['department_construct_json']);
  
  $api->setArray($cdata);
  
}else{
  
}

print $api->getJSON();

?>