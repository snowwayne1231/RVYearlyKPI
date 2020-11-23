<?php
include __DIR__."/../ApiCore.php";

$api = new ApiCore($_REQUEST);
// use Model\Business\Multiple\YearlyAssessment;
// use Model\Business\Multiple\YearlyQuickly;
use Model\Business\YearPerformanceReport;
use Model\Business\YearPerformanceReportTopic;
use Model\Business\YearPerformanceDivisions;

$api->isAdmin();
 
$year = $api->post('year');

if ($year) {

  $REPORT = new YearPerformanceReport();
  $TOPIC = new YearPerformanceReportTopic();
  $topic_map = $TOPIC->map();
  $all_report = $REPORT->select([],['year'=>$year,'enable'=>1,'self_contribution'=>'', 'staff_id'=>'>1']);
  // dd($all_report);
  foreach($all_report as $val){
    $time =  time();
    $aj = &$val['assessment_json'];
    $sign_json = ['s'=>[$val['staff_id'],$time],'c'=>[2,$time],'f'=>[1,$time]];
    foreach($val['path_lv_leaders'] as $plv=> $pv){
      if( isset($sign_json[ $plv ]) ){continue;}
      if( $plv==1 && $val['processing_lv'] != 1){continue;}
      foreach ($pv as $_staff_id) {
        if( $_staff_id==$val['staff_id'] ){continue;}
        if (isset($sign_json[$plv])) {
          $sign_json[$plv] = array_merge($sign_json[$plv], [$_staff_id,$time]);
        } else {
          $sign_json[$plv] = [$_staff_id,$time];
        }
      }
    }
    $total = 0;
    $bonus = rand(0,3);
    foreach($aj as $k=>&$v){
      
      if(!empty($v['score'])){
        $v['total'] = 0;
        foreach($v['score'] as $tid=>&$tv){
          $topic = $topic_map[$tid];
          $max = $val['staff_is_leader']==1 ? $topic['score_leader'] : $topic['score'];
          $min = min($max, round( ($max / 3)+$bonus ));
          $tv = rand($min, $max);
          $v['total']+= $tv;
        }
      }
      
      $total += $v['total'] * (int)$v['percent'] / 100;
    }
    $total = round($total);
    if($total >= 91){
      $level = 'A';
    }else if($total >= 81){
      $level = 'B';
    }else if($total >= 71){
      $level = 'C';
    }else if($total >= 61){
      $level = 'D';
    }else{
      $level = 'E';
    }
    // $total = number_format($total,2);
    $self_contribution = 'contribution | '.MD5(rand(10,500)).' 國國國 self';
    $self_improve = 'improve | '.MD5(rand(10,500)).' 國國國';
    
    foreach( $val['upper_comment'] as $upc_lv => &$ucv ){
      foreach ($ucv['staff_id'] as $_uidx => $_ustaff_id) {
        $ucv['content'][$_uidx] = MD5(rand(10,500)).' 上司上';
      }
    }

    $next_aej = $val['assessment_evaluating_json'];
    if (isset($next_aej)) {
      foreach ($next_aej as $_aej_lv => &$_aej_val) {
        foreach ($_aej_val['leaders'] as $_idx => $_leader_id) {
          foreach ($_aej_val['scores'][$_idx] as &$_score) {
            $_score = rand(1,5);
          }
          $_aej_val['commited'][$_idx] = true;
        }
      }
    }
    
    $update_data = [
      'processing_lv'=>0, 
      'owner_staff_id'=>1, 
      'assessment_json'=>$aj,
      'assessment_evaluating_json'=>$next_aej,
      'assessment_total'=>$total,
      'level'=>$level,
      'self_contribution'=>$self_contribution,
      'self_improve'=>$self_improve,
      'upper_comment'=> json_encode($val['upper_comment'],JSON_UNESCAPED_UNICODE) ,
      'sign_json'=>$sign_json
    ];
    // dd($update_data);
    $REPORT->update($update_data, $val['id']);
  }
  
  
  $DIVI = new YearPerformanceDivisions();
  $DIVI->update(['status'=>5],['year'=>$year,'division'=>'>1']);

  $api->setArray('OK');
  
} else {
  $api->denied('No Year Input.');
}

// $qq = new YearlyQuickly( $year );
// $qq->doneReport();


print $api->getJSON();
?>