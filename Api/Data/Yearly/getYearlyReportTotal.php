<?php
include __DIR__."/../../ApiCore.php";

$api = new ApiCore($_POST);

use Model\Business\Multiple\YearlyAssessment;
use Model\Business\Multiple\StaffHistory;


if( $api->checkPost(array('year','department_level'))  && $api->SC->isLogin() ){

  $year = $api->post('year');
  $api->SC->set('year', $year);
  $is_admin = $api->SC->isAdmin();

  $department_level = $api->post('department_level');
  if( isset($department_level) && (!is_numeric($department_level) || (int)$department_level > 5)){ $api->denied('Param Wrong.'); }

  $with_assignment = empty($api->post('with_assignment')) ? false : true;
  $is_over = empty($api->post('is_over')) ? false : true;

  $yfb = new YearlyAssessment( $year );
  $sh = new StaffHistory();
  $self_id = $api->SC->getId();

  //員工只能看該看的
  if ($api->SC->isLeader()) {
    $report_columns = [];
  } else {
    $report_columns = ['attendance_json', 'before_level', 'department_id', 'division_id', 'level', 'monthly_average', 'self_contribution', 'self_improve', 'staff_id', 'staff_post', 'staff_title', 'upper_comment', 'enable'];
  }

  $result = $yfb->getPerfomanceList( $self_id , $year, $department_level, $with_assignment, $is_over, true, $report_columns );
  // dd($result);
  //員工只能看自評
  if ($api->SC->isLeader() && !$is_admin) {
    
    $is_not_division_leader = !$api->SC->getMember()['_is_division_leader'];

    foreach ($result['assessment'] as $duty => &$reports) {
      foreach ($reports as &$report) {
        if ($is_not_division_leader) {
          unset($report['assessment_evaluating_json']);
          unset($report['assessment_total']);
          unset($report['assessment_total_ceo_change']);
          unset($report['assessment_total_division_change']);
          unset($report['assessment_total_final']);
        }
      }
    }
  }

  foreach ($result['assessment'] as $duty => &$reports) {
    foreach ($reports as &$report) {
      if ($resSH = $sh->getStayWithStaff($report['staff_id'])) {
        $report['staff_stay'] = $resSH;
      } else {
        $report['staff_stay'] = array();
      }
    }
  }

  //結果
  if( isset($result['error']) ){
    $api->denied($result['error']);
  }else{
    $api->setArray($result);
  }


}else{
  $api->denied('You Have Not Promised.');
}

print $api->getJSON();

?>