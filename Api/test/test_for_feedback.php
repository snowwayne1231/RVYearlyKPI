<?php
include __DIR__."/../ApiCore.php";

$api = new ApiCore($_REQUEST);
// use Model\Business\Multiple\YearlyAssessment;
// use Model\Business\Multiple\YearlyQuickly;

$api->isAdmin();
 
$fb = new Model\Business\YearPerformanceFeedback;
$rq = new Model\Business\RecordYearPerformanceQuestions;

$year = $api->post('year');

if (empty($year)) {
  $api->denied('No Year.');
}

$all_data = $fb->select(['id','multiple_choice_json','multiple_score','staff_id','target_staff_id'],['year'=>$year,'status'=>0]);
$i = 0;
$time = microtime(true);

foreach($all_data as &$val){
  $val['multiple_score'] = 0;
  foreach($val['multiple_choice_json'] as &$loc){
    $ans = rand(0,2);
    $loc['ans'] = $ans;
    $loc['score'] = 5- ($ans*2);
    $val['multiple_score']+= $loc['score'];
  }
  $fb->update(['multiple_choice_json'=>$val['multiple_choice_json'], 'multiple_score'=>$val['multiple_score'], 'status'=>1], $val['id']);
  
  $stamp = $i.'-'.$time.'-'.MD5($val['staff_id']);
  $content = '讓您忍不住想大力讚揚是什麼？ '.$stamp;
  $rq->addStorage(['question_id'=>1,'year'=>$year,'from_type'=>1,'target_staff_id'=>$val['target_staff_id'],'content'=>$content]);
  $content = '您覺得受評主管有哪些是可以改善 '.$stamp;
  $rq->addStorage(['question_id'=>2,'year'=>$year,'from_type'=>1,'target_staff_id'=>$val['target_staff_id'],'content'=>$content]);
  $content = '對於受評主管，是否有任何是您想提出建議的？ '.$stamp;
  $rq->addStorage(['question_id'=>4,'year'=>$year,'from_type'=>1,'target_staff_id'=>$val['target_staff_id'],'content'=>$content]);
  $content = '其它建議：（針對公司，是否還有其它您想特別說明/補充呢？） '.$stamp;
  $rq->addStorage(['question_id'=>6,'year'=>$year,'from_type'=>1,'target_staff_id'=>0,'content'=>$content]);
  
  $i++;
}

$rq->addRelease();

  
$api->setArray([
  'count'=> count($all_data)
]);

print $api->getJSON();
?>