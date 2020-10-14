<?php

include __DIR__."/../../ApiCore.php";
// include BASE_PATH.'/Model/dbBusiness/Multiple/ProcessRouting.php';

$api = new ApiCore($_REQUEST);

use Model\Business\Multiple\ProcessRouting;


$process_id = $api->post('processing_id');
$commit_by_admin = $api->post('admin');

if( $process_id ){

  $self_id = $api->SC->getId();

  $routing = new ProcessRouting( $process_id, $self_id );

  if(!$routing->process->isLaunch()){
    $api->denied("This Processing Not Yet Be Ready.");
  }

  if ($commit_by_admin) {
    if (!$api->SC->isAdmin()) {
      $api->denied('You Are Not Admin.');
    }
  } else {
    if(!$routing->checkOwnerSameDepartment()) {
      $api->denied('Wrong Department With Processing.');
    }
  }

  //不是擁有者 又不是管理者，沒有權限修改
  // if( $routing->owner != $self_id && !$api->SC->isAdmin() ){
  //   $api->denied("You Are Not Owner.");
  // }


  if($routing->isFinally()){//在最後的人手上

    $routing->done( $self_id );
    $api->setArray("Already Done.");//成功結果

  } else {

    //往上送
    // $ok = $routing->processToSupervisor( $self_id, $api->SC->isAdmin() );
    $is_next = $routing->commitToNext(!!$commit_by_admin);

    // if(!$ok){//沒有送審成功
    //   $api->denied('Commit Failed.');
    // }

    if ($is_next && !$commit_by_admin) {
      //取得上司
      $staff_id = $routing->supervisor;

      $Leadership = new Model\Business\Multiple\Leadership($staff_id);
      $leaders = $Leadership->getSameDepartmentLeaders(true);

      $team = $routing->getTeam();//20170807 改成取 processing 的部門名
      // $team = $routing->team->map('manager_staff_id')[$staff_id];

      //發送 E-mail
      require_once BASE_PATH.'/Model/MailCenter.php';
      $mail = new Model\MailCenter;
      $mail->addAddressByStaffArray($leaders);
      $res = $mail->sendTemplate('monthly_arrive',array(
        'unit_name' => $team['name'],
        'unit_id'   => $team['unit_id'],
        'year'      => date('Y'),
        'month'     => date('m')
      ));

      $api->setArray($is_next);

    } else {

      $api->denied();

    }
  }

}else{
  $api->denied();
}

print $api->getJSON();

?>