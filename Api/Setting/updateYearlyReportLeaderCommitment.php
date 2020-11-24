<?php

include __DIR__."/../ApiCore.php";

$api = new ApiCore($_POST);

use Model\Business\YearPerformanceReport;
//'processing'
if( $api->checkPost(array('report_id', 'leader_id', 'commit')) && $api->SC->isAdmin() ){

  $report_id = (int) $api->post('report_id');
  $leader_id = (int) $api->post('leader_id');
  $commit = (int) $api->post('commit') == 1 ? true : false;

  $report = new YearPerformanceReport();
  $report_data = $report->select(['assessment_evaluating_json'], $report_id);

  if (count($report_data) != 1) {
    $api->denied('Not Found Report.');
  }

  $report_data = $report_data[0];
  $eva_json = $report_data['assessment_evaluating_json'];

  $found_leader = false;
  foreach ($eva_json as $lv => &$val) {
    $leaders = $val['leaders'];
    $commited = &$val['commited'];

    foreach ($leaders as $idx => $leader) {
      if ($leader == $leader_id) {
        $found_leader = true;
        $commited[$idx] = $commit;
      }
    }
  }
  

  if (!$found_leader) {
    $api->denied('Not Found Leader.');
  }

  $changed = $report->update(['assessment_evaluating_json' => $eva_json], $report_id);

  $res = [
    'changed' => $changed,
    'assessment_evaluating_json' => $eva_json,
  ];
  
  $api->setArray( $res );

}else{
  $api->denied();
}

print $api->getJSON();

?>