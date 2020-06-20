<?php
include __DIR__."/../../ApiCore.php";
$api = new ApiCore($_POST);

use Model\Business\RecordYearPerformanceQuestions;
use Model\Business\YearPerformanceFeedbackQuestions;
use Model\Business\Staff;

$year = $api->post('year');


if( $year && $api->isAdmin() ){
  
  $q = new RecordYearPerformanceQuestions();
  $st_q = new YearPerformanceFeedbackQuestions();
  $staff = new Staff();
  
  $result = $q->sql("select a.id, a.target_staff_id, a.content, a.create_date, c.name, c.name_en from {table} as a left join ".
  $st_q->table_name." as b on a.question_id = b.id left join ".
  $staff->table_name." as c on a.target_staff_id = c.id ".
  "where b.mode='others' and a.year = $year ")->data;
  
  $final = [];
  foreach($result as $r){
    if(empty($final[$r['target_staff_id']])){
      $final[$r['target_staff_id']]['name'] = $r['name'];
      $final[$r['target_staff_id']]['name_en'] = $r['name_en'];
    }
    $final[$r['target_staff_id']]['questions'][] = ['qid'=>$r['id'],'content'=>$r['content'],'create_date'=>$r['create_date']];
    
  };
  
  $api->setArray($final);
  
} else {
  $api->denied('You Have Not Promised.');
}



print $api->getJSON();