<?php

include __DIR__."/../ApiCore.php";
include BASE_PATH.'/Model/DatabaseCenter.php';

$api = new ApiCore($_POST);

use Model\DatabaseCenter;

  $table = $_REQUEST['table'];
  
  $db = new DatabaseCenter();
  
  // $sql = "SELECT * FROM $table LIMIT 0";
  $sql = "DESCRIBE $table";
  $pdo = $db->getPDO();
  
  // $rs = $db->doSQL($sql);
  // var_dump($rs);
  // exit;
  
  $q = $pdo->prepare("DESCRIBE $table");
  $q->execute();
  $table_fields = $q->fetchAll(PDO::FETCH_COLUMN);
  
  // var_dump($table_fields);exit;
  $output = '';
  foreach($table_fields as $k => $v){
    $col = $v;
    // $type = $v['Type'];
    // $output.= "  '$col' => '$type' <br>";
    $output.= "  '$col',<br>";
  }
  echo $output;

// print $api->getJSON();

?>