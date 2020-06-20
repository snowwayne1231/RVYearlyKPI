<?php

include __DIR__."/../../ApiCore.php";
include BASE_PATH.'/Model/dbBusiness/Multiple/MonthlyReport.php';

$api = new ApiCore($_REQUEST);

use Model\Business\Multiple\MonthlyReport;
use Model\Business\Multiple\MonthlyReportLeadershipEvaluating;
use Model\Business\Multiple\MonthlyProcessingEva;

if($api->SC->isLogin()){
  
  $report = new MonthlyReport( );
  $eva = new MonthlyReportLeadershipEvaluating();
  
  $result = array();
  
  $self_id = $api->SC->getId();
  $department_id = $api->SC->getDepartmentId();
  
  $processing_id = $api->post('processing_id','array');
  $manager_id = $api->post('manager_id','int');
  $team_id = $api->post('department_id','int');
  $year = $api->post('year');
  $month = $api->post('month');
  
  if($processing_id){
    //用月考評單查  一人至多就2~3張
    // $processing_id    = explode(',',preg_replace('/[^(\d\,)]+/','',$processing_id));
    
  }else if($manager_id && $year && $month){
    //用主管查
    $pr_id = $report->process->select(array('id'),"where created_staff_id in ($manager_id) and year = $year and month = $month");
    $processing_id = array();
    foreach($pr_id as &$val){
      $processing_id[] = $val['id'];
    }
  }else if($team_id && $year && $month){
    $pr_id = $report->process->select(array('id'),"where created_department_id in ($team_id) and year = $year and month = $month");
    $processing_id = array();
    foreach($pr_id as &$val){
      $processing_id[] = $val['id'];
    }
  }else{
    return $api->denied();
  }
  
  if(count($processing_id)>0){
    $result = $report->getReportWithProcess( $processing_id, $self_id, $department_id );
    foreach ($result as $pid => &$process) {
      $_reports = &$process['_reports'];
      foreach ($_reports as &$report) {
        $report = $eva->replaceByReportData($report, $self_id);
      }
    }
  }
  
  $api->setArray($result);
  
}else{
  $api->denied();
}

print $api->getJSON();

?>