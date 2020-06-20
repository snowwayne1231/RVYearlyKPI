<?php
/**
 * 取得月考評所有評分紀錄
 */
include __DIR__."/../../ApiCore.php";

$api = new ApiCore($_REQUEST);

use Model\Business\MonthlyReportEvaluating;
use Model\Business\MonthlyReport;
use Model\Business\MonthlyReportLeader;
use Model\Business\MonthlyProcessingEvaluating;
use Model\Business\MonthlyProcessing;
use Model\Business\Staff;

$processing_id = $api->post('processing_id');

if ($processing_id) {

  $staff = new Staff();
  $staff_map = $staff->read(['id', 'lv', 'name', 'name_en', 'post', 'staff_no', 'title'], [])->map();
  $main_process = new MonthlyProcessing();
  $eva_process = new MonthlyProcessingEvaluating();
  $eva_report = new MonthlyReportEvaluating();

  $process_data = $main_process->read($processing_id)->check(0, 'Not Found This Id.')->data[0];
  if ($process_data['type'] == 1) {
    $main_report = new MonthlyReportLeader();
  } else {
    $main_report = new MonthlyReport();
  }

  $eva_process_data = $eva_process->select(['staff_id', 'status_code'], ['processing_id'=> $processing_id]);
  $_submited_staff = [];
  foreach ($eva_process_data as $eva_pdata) {
    if ($eva_pdata['status_code'] == MonthlyProcessingEvaluating::STATUS_CODE_SUBMITED) {
      $_submited_staff[] = $eva_pdata['staff_id'];
    }
  }
  

  $_reports = $main_report->select(['processing_id'=> $processing_id]);
  foreach ($_reports as &$report) {
    $rid = $report['id'];
    $eva_data = $eva_report->select(['json_data', 'should_count', 'staff_id_evaluator', 'status_code'], ['report_id'=> $rid, 'report_type'=> $process_data['type']]);
    foreach ($eva_data as &$report_eva) {
      $json = $report_eva['json_data'];
      foreach ($json as $jkey => $jvalue) {
        $report_eva[$jkey] = $jvalue;
      }
      unset($report_eva['json_data']);
      $report_eva['_evaluator_detail'] = $staff_map[$report_eva['staff_id_evaluator']];
    }
    
    $report['_evaluating'] = $eva_data;
    $report['_staff_detail'] = $staff_map[$report['staff_id']];
  }
  $process_data['_reports'] = $_reports;
  $process_data['_submited_staff'] = $_submited_staff;

  $api->setArray($process_data);

} else {
  $api->denied('Wrong Paramter.');
}

print $api->getJSON();

?>