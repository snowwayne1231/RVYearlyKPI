<?php

include __DIR__."/../../ApiCore.php";

$api = new ApiCore($_REQUEST);

use Model\Business\Multiple\MonthlyReport;
use Model\Business\Department;
use Model\Business\Multiple\StaffHistory;

if($api->checkPost(array("year_start","year_end","month_start","month_end","staff_id")) && ($api->SC->isLeader() || $api->SC->isAdmin() ) ){
  // LG($api->getPOST());
  $report = new MonthlyReport();
  $sh = new StaffHistory();

  if( $api->SC->isAdmin() || $api->SC->isCEO() ){
    $team_id = false;
  }else{
    $team_id = $api->SC->getDepartmentId();
    // $api->denied('This Function Is In Maintaining.');
  }

  $year = [(int)$api->post('year_start'),(int)$api->post('year_end')];
  $month = [(int)$api->post('month_start'), (int)$api->post('month_end')];
  $staff_id = $api->post('staff_id');
  $seld_id = $api->SC->getId();
  $self_department_id = $api->SC->getDepartmentId();

  $result = $report->getDetailMonthlyByPerson($year, $month, $staff_id, $seld_id, $self_department_id, $api->SC->isAdmin() || !empty($api->post('api-token')) );
  if($resSH = $sh->getStayWithStaff($staff_id)){
    $result['staff_info']['staff_stay'] = $resSH;
  }else{
    $result['staff_info']['staff_stay'] = array();
  }

  $api->setArray($result);

}else{
  $api->denied('You Have Not Promised.');
}


print $api->getJSON();

?>