<?php

include __DIR__."/../../ApiCore.php";
include BASE_PATH.'/Model/dbBusiness/MonthlyProcessing.php';
include BASE_PATH.'/Model/dbBusiness/ConfigCyclical.php';

$api = new ApiCore($_REQUEST);

use Model\Business\MonthlyProcessing;
use Model\Business\ConfigCyclical;

if( $api->checkPost(array('year','month')) && $api->SC->isSuperUser() ){

  $year = $api->post('year');
  $month = $api->post('month');

  $process = new MonthlyProcessing();

  // $self_id = $api->SC->getId();
  $change = $process->update(array('status_code'=>1),array('year'=>$year,'month'=>$month,'status_code'=>'< 2'));

  $config = new ConfigCyclical();
  $config->update(array('monthly_launched'=>0), array('year'=>$year,'month'=>$month));

  if($change){

    //mail
    require_once BASE_PATH.'/Model/MailCenter.php';
    $mail = new Model\MailCenter;
    $mail->addAddressGroup('monthly_process');
    $res = $mail->sendTemplate('monthly_pause', array('year'=>$year,'month'=>$month) );
  }else{
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