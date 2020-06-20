<?php

include __DIR__."/../ApiCore.php";
include BASE_PATH.'/Model/DatabaseCenter.php';

$api = new ApiCore($_POST);

use Model\DatabaseCenter;

if($api->checkPost( array('table','col','val') ) && $api->SC->isSuperUser() ){
  
  $db = new DatabaseCenter();
  $date = new DateTime();
  $time_1 = $date->getTimestamp();
  $table=$api->post('table');
  $col=$api->post('col');
  $val=$api->post('val');
  
  
  $sql = "insert $table ( $col ) values ";
  // $sql = 'insert test_2 (process_id) values ';
  $values_array = array();
  for($i = 1;$i <= 100000; $i++){
    $rand = "'".rand(0,100)."qqqwer國國國__YEAH!!!帥'";
    $full = str_replace('{rand}',$rand,"( $val )");
    array_push($values_array, $full );
    // array_push($values_array, "($i)" );
  }
  $values_string = join(',',$values_array);
  
  $sql .= $values_string;
  
  $db->doSQL($sql);
  // var_dump($sql);
  
  $date2 = new DateTime();
  $time_2 = $date2->getTimestamp();
  
  //成功結果
  $api->setArray( $time_2 - $time_1 );
  // var_dump($time_2 - $time_1);
  
  
  
}
  
  
  

print $api->getJSON();

?>