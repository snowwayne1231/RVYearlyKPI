<?php
include __DIR__."/../../ApiCore.php";
$api = new ApiCore($_POST);

use Model\Business\RecordYearPerformanceQuestions;
// use Model\Business\YearPerformanceFeedbackQuestions;
use Model\Business\Staff;
use Model\Business\YearPerformanceConfigCyclical;

$question_id = $api->post('question_id');
$target_staff_id = $api->post('target_staff_id');
$year = $api->post('year');


if( $year && $question_id && $target_staff_id && $api->isAdmin() ){
  
  $q = new RecordYearPerformanceQuestions();
  $config = new YearPerformanceConfigCyclical( $year );
  // $st_q = new YearPerformanceFeedbackQuestions();
  $staff = new Staff();

  if ($config->isFinished()) {

    $api->denied('This Year Is Already Finished.');

  } else {

    $this_q = $q->read(['id'],$question_id)->check('Not Found.');
  
    if($target_staff_id=="delete"){
      
      $q->delete($question_id);
      
      $api->setArray('Delete Done.');
      
    }else{
      
      if( !is_numeric($target_staff_id)){ $api->denied('Staff Id Is Not Number.');}
      
      // $staff->read(['id'],['id'=>$target_staff_id,'is_leader'=>1])->check('Staff Is Not Leader.');
      $staff->read(['id'],$target_staff_id)->check('Staff Is Not Found.');
      
      $q->update(['target_staff_id'=>$target_staff_id],$question_id);
      
      $result = $q->sql("select a.id, a.target_staff_id, a.content, a.create_date, c.name, c.name_en from {table} as a left join ".
      $staff->table_name." as c on a.target_staff_id = c.id ".
      "where a.id=$question_id ")->data[0];
      
    }
    
    
    $api->setArray($result);

  }
  
} else {
  $api->denied('You Have Not Promised.');
}



print $api->getJSON();