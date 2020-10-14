<?php

include __DIR__."/../../ApiCore.php";
include BASE_PATH.'/Model/dbBusiness/Multiple/ProcessRouting.php';
include_once BASE_PATH.'/Model/dbBusiness/Department.php';

$api = new ApiCore($_REQUEST);

use Model\Business\Multiple\ProcessRouting;
use Model\Business\Department;
use Model\Business\Multiple\Leadership;

$process_id = $api->post('processing_id');
$staff_id = $api->post('staff_id');
$turnback = $api->post('turnback');//取消核准
  
if( $process_id && ($staff_id || ($api->SC->isAdmin() && $turnback)) ){
  
  $self_id = $api->SC->getId();
  //理由非必填
  $reason = $api->post('reason');
  
  if(!$staff_id){
    //沒有給 staff_id 的話 就是送給 CEO 指給 最大的沒有 upper_id 的單位
    $team = new Department();
    $staff_id = $team->select(array('manager_staff_id'),array('upper_id'=>0))[0]['manager_staff_id'];
  }
  
  
  $routing = new ProcessRouting( $process_id, $self_id );
  
  //退回
  $ok = $routing->rejectToStaff( $staff_id, $reason, $self_id, $api->SC->isAdmin() );
  if(!$ok){$api->denied('Error Staff Id.');}

  // 找到同層主管
  $Leadership = new Leadership($staff_id);
  $leaders = $Leadership->getSameDepartmentLeaders(true);
  
  //20170807 改成 process 的單位名稱
  $team = $routing->getTeam();
  //mail
  require_once BASE_PATH.'/Model/MailCenter.php';

  $mail = new Model\MailCenter;
  // $mail->addAddress($staff_id);
  $mail->addAddressByStaffArray($leaders);
  $res = $mail->sendTemplate('monthly_return',array(
    'unit_name' => $team['name'],
    'unit_id' => $team['unit_id'],
    'year' => date('Y'),
    'month' => date('m')
  ));
  
  if($res===true){
    $api->setArray('ok');
  }else{
    $api->setArray($res);
  }
  
}else{
  $api->denied('You Have Not Promised.');
}

print $api->getJSON();

?>