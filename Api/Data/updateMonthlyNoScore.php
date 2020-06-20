<?php
include __DIR__."/../ApiCore.php";
$api = new ApiCore($_POST);

use Model\Business\Multiple\MonthlyReport;

if( $api->checkPost(['report_type', 'report_id', 'exception']) && $api->SC->isAdmin() ){
  
  $report_type = $api->post('report_type');
  $report_id = $api->post('report_id');
  $exception = $api->post('exception');
  $reason    = $api->post('reason');
  
  $monthly_report = new MonthlyReport;
  $res = $monthly_report->updateMonthlyNoScore($report_type, $report_id, $exception, $reason);
  $api->setArray($res);
  
  //紀錄
  $self_id = $api->SC->getId();
  $record_data = $api->getPost();
  $record = new \Model\Business\RecordAdmin( $self_id );
  $record->type($record::TYPE_MONTH_REPORT)->update( $record_data );
  
} else {
    $api->denied('You Have Not Promised.');
}

print $api->getJSON();