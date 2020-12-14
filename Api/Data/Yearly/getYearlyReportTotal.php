<?php
include __DIR__."/../../ApiCore.php";

$api = new ApiCore($_POST);

use Model\Business\YearPerformanceReport;
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
  $config_data = $yfb->getConfigData();
  
  $sh = new StaffHistory();
  $self_id = $api->SC->getId();
  $is_leader_or_admin = $api->SC->isLeader() || $is_admin;
  $is_yearly_finished = $yfb->isYearlyFinished();

  $is_should_promotion_c_to_b = $config_data['promotion_c_to_b'] == 1 && $is_yearly_finished && !$is_admin;

  //員工只能看該看的
  if ($is_leader_or_admin) {
    $report_columns = [];
  } else {
    $report_columns = ['id', 'attendance_json', 'before_level', 'department_id', 'division_id', 'level', 'monthly_average', 'self_contribution', 'self_improve', 'staff_id', 'staff_post', 'staff_title', 'enable', 'processing_lv'];
  }

  $result = $yfb->getPerfomanceList( $self_id , $year, $department_level, $with_assignment, $is_over, true, $report_columns );
  $staff_map = $yfb->getStaffMap();
  $all_in_report_ids = [];
  // dd($result);
  // dd($staff_map);

  $level_map_cache = [
    'A' => 0,
    'B' => 0,
    'C' => 0,
    'D' => 0,
    'E' => 0,
  ];

  //員工只能看自評
  if ($api->SC->isLeader() && !$is_admin && !$api->SC->isCEO()) {
    
    $is_not_division_leader = !$api->SC->getMember()['_is_division_leader'];

    foreach ($result['assessment'] as $duty => &$reports) {
      foreach ($reports as &$report) {
        unset($report['assessment_evaluating_json']);
        unset($report['assessment_total_ceo_change']);
        unset($report['assessment_total_final']);

        if ($is_not_division_leader) {
          unset($report['assessment_total']);
          unset($report['assessment_total_division_change']);
        }
      }
    }
  }

  foreach ($result['assessment'] as $duty => &$reports) {
    foreach ($reports as &$report) {
      // $all_in_report_ids[] = $report['staff_id'];
      $lv = &$report['level'];
      if ($resSH = $sh->getStayWithStaff($report['staff_id'])) {
        $report['staff_stay'] = $resSH;
      } else {
        $report['staff_stay'] = array();
      }

      if (isset($report['upper_comment'])) {
        unset($report['upper_comment']);
      }

      if ($report['staff_id'] == $self_id && !$is_yearly_finished) {
        if (isset($report['assessment_json'])) {
          unset($report['assessment_json']);
        }
        if (isset($report['assessment_evaluating_json'])) {
          unset($report['assessment_evaluating_json']);
          unset($report['assessment_total']);
          unset($report['assessment_total_ceo_change']);
          unset($report['assessment_total_division_change']);
          unset($report['assessment_total_final']);
        }
        $lv = '-';
      }

      if ($is_should_promotion_c_to_b) {
        $lv = YearPerformanceReport::PromotionBtoC($lv);
      }

      if ($lv != '-') {
        if (isset($level_map_cache[$lv])) {
          $level_map_cache[$lv] += 1;
        } else {
          $level_map_cache[$lv] = 1;
        }
      }
    }
  }

  if ($is_should_promotion_c_to_b) {
    foreach ($result['distribution'] as &$dis) {
      if (isset($level_map_cache[$dis['name']])) {
        $dis['count'] = $level_map_cache[$dis['name']];
      }
    }
  }

  // $staff_for_show_map = [];
  // foreach ($staff_map as $staff_data) {
  //   $staff_for_show_map[$staff_data['id']] = [
  //     'name' => $staff_data['name'],
  //     'name_en' => $staff_data['name_en'],
  //     'staff_no' => $staff_data['staff_no'],
  //   ];
  // }
  // $result['staff_map'] = $staff_for_show_map;

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