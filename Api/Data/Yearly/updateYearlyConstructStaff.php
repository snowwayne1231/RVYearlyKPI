<?php
include __DIR__."/../../ApiCore.php";

$api = new ApiCore($_POST);

use Model\Business\Multiple\YearlyQuickly;


if( $api->checkPost(array('year','staff_id')) && $api->SC->isAdmin() ){
  
  $year = $api->post('year');
  $staff_id = $api->post('staff_id');
  $department_id = $api->post('department_id');
  
  $feedback = $api->post('feedback');
  $assessment = $api->post('assessment');
  
  
  $yq = new YearlyQuickly( $year );
  
  $res = $yq->updateConstrust(array(
    'staff' => $staff_id,
    'department' => $department_id,
    'feedback' => $feedback,
    'assessment' => $assessment
  ));

  $api->setArray($res);
  
  //紀錄
  $self_id = $api->SC->getId();
  $record_data = $api->getPost();
  $record = new \Model\Business\RecordAdmin( $self_id );
  $record->type($record::TYPE_YEAR)->update( $record_data );
  
}else{
  $api->denied('You Have Not Promised.');
}

print $api->getJSON();

?>