<?php
include __DIR__."/../../ApiCore.php";
$api = new ApiCore($_POST);

use Model\Business\Multiple\YearlyQuickly;

$year = $api->post('year');
if( $year && $api->SC->isAdmin() ){
  
  // $config = new YearPerformanceConfigCyclical();
  $config = new YearlyQuickly( $year );
  
  $reset = $api->post('reset');
  
  //年設定 組織
  $result = $config->getConstrust( $reset );
  
  
  //結果
  $api->setArray($result);
  //紀錄
  if(!empty($reset)){
    $self_id = $api->SC->getId();
    $record_data = $api->getPost();
    $record = new \Model\Business\RecordAdmin( $self_id );
    $record->type($record::TYPE_YEAR)->update( $record_data );
  }
  
  
}else{
  $api->denied('You Have Not Promised.');
}

print $api->getJSON();

?>