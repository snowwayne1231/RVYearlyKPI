<?php
/**
 * 儲存月考評單
 */
include __DIR__."/../../ApiCore.php";
include BASE_PATH.'/Model/dbBusiness/Multiple/ProcessReport.php';

$api = new ApiCore($_POST);

// use Model\Business\Multiple\ProcessReport;
use Model\Business\MonthlyReportEvaluating;
use Model\Business\MonthlyProcessing;

$reports = $api->post('report');
$length_report = count($reports);

if( $reports && $length_report>0 && $api->SC->isLogin()){

  $count = 0;
  $mre = new MonthlyReportEvaluating();
  $mp = new MonthlyProcessing();

  $report_ids = array();
  $process_ids = array();
  foreach($reports as &$loc){
    $report_ids[] = $loc['id'];
    if (!in_array($loc['processing_id'], $process_ids)) {
      $process_ids[] = $loc['processing_id'];
    }
  }

  if (count($process_ids) != 1) {
    $api->denied('Wrong Process In Same Time.');
  }

  $p_id = $process_ids[0];
  $mp->read(['type'], $p_id)->check(0, 'Not Found This Process.');
  $report_type = $mp->data[0]['type'];

  //檢查是不是每張表都是自己的
  $self_staff_id = $api->SC->getId();
  $all_eva_reports = $mre->select(['id', 'report_id', 'json_data', 'submitted', 'should_count'], "where report_id in (".join(',',$report_ids).") and staff_id_evaluator = $self_staff_id and report_type = $report_type");
  // dd($all_eva_reports);
  if (count($all_eva_reports) != count($report_ids)) {
    $api->denied('Wrong Report Id.');
  }
  // $pr_process = $pr->process->read( array('id','owner_staff_id','created_staff_id','type','status_code'), 'where id in ('.join(',',$process_id).') and owner_staff_id = '.$staff )->map();
  // $pr_process = $pr->process->read( array('id','owner_staff_id','created_staff_id','type'), 'where id in ('.join(',',$process_id).') ' )->map();
  // LG($pr_process);

  $result = [];
  foreach ($all_eva_reports as $eva_report) {
    $eva_rid = $eva_report['id'];
    $rid = $eva_report['report_id'];
    $update_data = $reports[$rid];
    $next_data = [];
    // if (isset($update_data['submitted'])) {
    //   $next_data['submitted'] = intval($update_data['submitted']);
    // }
    if (isset($update_data['should_count'])) {
      $next_data['should_count'] = intval($update_data['should_count']);
    }
    $origin_json_data = $eva_report['json_data'];
    foreach ($origin_json_data as $jkey => &$jval) {
      if (isset($update_data[$jkey])) {
        $jval = intval($update_data[$jkey]);
      }
    }
    $next_data['json_data'] = $origin_json_data;
    $result[] = $mre->update($next_data, $eva_rid);
    // $report_item = $pr->updateReport( $loc, $pr_process[$pid], $staff );
    $count++;
    
  }
  //成功結果
  if($count == $length_report){
    $api->setArray($result);
  }else{
    $api->sqlError('No Complete Update.'.$count.'=='.$all);
  }

}else{
  $api->denied();
}

print $api->getJSON();

?>