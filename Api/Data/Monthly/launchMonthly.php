<?php

include __DIR__."/../../ApiCore.php";
include BASE_PATH.'/Model/dbBusiness/MonthlyProcessing.php';
include BASE_PATH.'/Model/dbBusiness/ConfigCyclical.php';

$api = new ApiCore($_REQUEST);

use Model\Business\MonthlyProcessing;
use Model\Business\ConfigCyclical;

if( $api->checkPost(array('year','month')) && $api->SC->isAdmin() ){

  $year = $api->post('year');
  $month = $api->post('month');

  if( $year==0 || $month==0){$api->denied('Wrong Date.');}
  $YM = array('year'=>$year,'month'=>$month);

  $process = new MonthlyProcessing();
  
  $change = $process->update(array('status_code'=>2),array('year'=>$year,'month'=>$month,'status_code'=>'< 2'));

  $config = new ConfigCyclical($year,$month);
  $config_data = $config->data;

  $cutOffDate = $config_data['RangeEnd'];

  $addition = $config_data['day_cut_addition'];

  $cutOffDate = date('Y-m-d', strtotime($config_data['RangeEnd']. " + $addition days"));
  // LG($cutOffDate);
  $config->update(array('monthly_launched'=>1,'cut_off_date'=>$cutOffDate), $YM );

  $self_id = $api->SC->getId();


  //發送 email
  if ($change > 0) {
    $config_data['cut_off_date'] = str_replace('-','/',$cutOffDate);

    require_once BASE_PATH.'/Model/MailCenter.php';
    $mail = new Model\MailCenter;
    // $mail->addAddress('snow.jhung@rv88.tw', 'Snow');
    $mail->addAddressGroup('monthly_process');
    $res = $mail->sendTemplate('monthly_start',$config_data);
  } else {
    $res = true;
  }
  


  if($res===true){
    $api->setArray('ok');

  }else{
    $api->setArray($res);
  }


}else{
  $api->denied();
}

print $api->getJSON();

?>