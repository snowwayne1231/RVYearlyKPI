<?php
include __DIR__."/../../ApiCore.php";
include BASE_PATH.'/Model/dbBusiness/Multiple/StaffDepartment.php';
include BASE_PATH.'/Model/dbBusiness/RecordMonthlyProcessing.php';
include BASE_PATH.'/Model/dbBusiness/RecordMonthlyReport.php';
include BASE_PATH.'/Model/dbBusiness/MonthlyProcessing.php';

$api = new ApiCore($_REQUEST);

// use Model\Business\Multiple\ProcessReport;
use Model\Business\Multiple\StaffDepartment;
use Model\Business\RecordMonthlyProcessing;
use Model\Business\RecordMonthlyReport;
use Model\Business\MonthlyProcessing;
use Model\Business\MonthlyProcessingEvaluating;

if( $api->SC->isLogin() && $api->checkPost(array('processing_id')) ){

  $processing_id = $api->post('processing_id');
  $condition = $api->condition(array(
    'processing_id'=>$processing_id
  ));

  $process = new MonthlyProcessing();
  $process_record = new RecordMonthlyProcessing();
  $report_record = new RecordMonthlyReport();
  $process_eva = new MonthlyProcessingEvaluating();

  $create_process = $process->select($processing_id);

  if(count($create_process)==0){$api->denied();}

  $create_process=$create_process[0];
  $process_data = $process_record->select($condition);
  // LG($create_process);
  $sd = new StaffDepartment();

  $sd->collect();
  $sd_map = $sd->map();

  $creator = $sd_map[ $create_process['created_staff_id'] ];

  $team_map = $sd->team->map();

  $result = array();
  //第一筆創立
  $result[] = array(
    'operating_staff_id' => $create_process['created_staff_id'],
    'target_staff_id' => $create_process['created_staff_id'],
    'action' => 'create',
    'reason' => '',
    'changed_json' => array() ,
    'update_date' => $create_process['create_date'],
    '_target_name' => $creator['name'],
    '_target_name_en' => $creator['name_en'],
    '_operating_name' => $creator['name'],
    '_operating_name_en' => $creator['name_en'],
    '_operating_unit_name' => $creator['unit_name'],
    '_operating_unit_id' => $creator['unit_id'],
  );
  // stamp();
  //修改資料
  if( count($process_data) > 0){

    $eva_processing_data = $process_eva->read(['processing_id', 'staff_department_id', 'staff_id'], ['processing_id'=> $processing_id])->map('staff_department_id', false, true);
    // dd($eva_processing_data);

    foreach($process_data as $key=>&$val){

      $operating = $sd_map[ $val['operating_staff_id'] ];
      $target = $sd_map[ $val['target_staff_id'] ];
      $opertating_department_id = $operating['department_id'];
      $target_department_id = $target['department_id'];

      if (isset($eva_processing_data[$opertating_department_id]) && count($eva_processing_data[$opertating_department_id]) > 1) {
        $val['_is_operating_multiple_leader'] = 1;
      } else {
        $val['_is_operating_multiple_leader'] = 0;
      }

      if (isset($eva_processing_data[$target_department_id]) && count($eva_processing_data[$target_department_id]) > 1) {
        $val['_is_target_multiple_leader'] = 1;
      } else {
        $val['_is_target_multiple_leader'] = 0;
      }

      $val['_target_name'] = $target['name'];
      $val['_target_name_en'] = $target['name_en'];
      $val['_target_unit_name'] = $target['unit_name'];
      $val['_target_unit_id'] = $target['unit_id'];
      $val['_operating_name'] = $operating['name'];
      $val['_operating_name_en'] = $operating['name_en'];
      $val['_operating_unit_name'] = $operating['unit_name'];
      $val['_operating_unit_id'] = $operating['unit_id'];

      $owner = $sd_map[ $val['changed_json']['owner_staff_id'] ];
      if($owner==$target){
        unset($val['changed_json']['owner_staff_id']);
        unset($val['changed_json']['owner_department_id']);
      }else{
        $team_id = $val['changed_json']['owner_department_id'];
        $team = $team_map[$team_id];
        $val['changed_json']['_owner_staff_name'] = $owner['name'];
        $val['changed_json']['_owner_staff_name_en'] = $owner['name_en'];
        $val['changed_json']['_owner_unit_name'] = $team['unit_name'];
        $val['changed_json']['_owner_unit_id'] = $team['unit_id'];
      }

      //report修改

      $val['_report_changed'] = $report_record->getChangedWithProcessingRecordId( $val['id'] );

      unset($val['id']);
      unset($val['processing_id']);
      $result[] = $val;

    }

  }

  $api->setArray( $result );
}else{

}

print $api->getJSON();

?>