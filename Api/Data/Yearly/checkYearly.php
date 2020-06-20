<?php
include __DIR__."/../../ApiCore.php";
// include_once BASE_PATH.'/Model/dbBusiness/Attendance.php';
// include_once BASE_PATH.'/Model/dbBusiness/YearPerformanceConfigCyclical.php';
// include_once BASE_PATH.'/Model/dbBusiness/MonthlyProcessing.php';
// include_once BASE_PATH.'/Model/dbBusiness/YearPerformanceFeedback.php';

$api = new ApiCore($_POST);

// use Model\Business\Attendance;
// use Model\Business\YearPerformanceConfigCyclical;
// use Model\Business\MonthlyProcessing;
// use Model\Business\YearPerformanceFeedback;

 use Model\Business\Multiple\YearlyAssessment;
 
if( $api->checkPost(array('year')) && $api->SC->isAdmin() ){
  
  $year = $api->post('year');
  $year = intval($year);
  
  //全部統一寫在 YearlyAssessment裡面
  $ya = new YearlyAssessment( $year );
  //1.各月份出缺勤資料是否完整
  $config = $ya->data;
  $attendance = $ya->checkAttendAnce($config['date_start'],$config['date_end']);
  //2.各月份績效結果是否完整
  $monthly_report = $ya->checkMonthlyProcessing($config);
  //3.部屬回饋問卷是否已停止流程
  $feedback = $ya->checkFeedBackAvaiable($config['year']);
  
  //結果
  $api->setArray( array(
    'feedback' => $feedback['status'],
    'attendance' => $attendance['status'],
    'monthly' => $monthly_report['status'],
  ));
  
}else{
  $api->denied('You Have Not Promised.');
}

print $api->getJSON();

?>