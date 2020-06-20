<?php
include __DIR__."/../../ApiCore.php";
include BASE_PATH.'/Model/dbBusiness/Multiple/StaffDepartment.php';
include BASE_PATH.'/Model/dbBusiness/MonthlyProcessing.php';

$api = new ApiCore($_REQUEST);

// use Model\Business\Multiple\ProcessReport;
use Model\Business\Multiple\StaffDepartment;
use Model\Business\MonthlyProcessing;
use Model\Business\Department;
use Model\Business\MonthlyProcessingEvaluating;

if( $api->SC->isLogin() ){

  $year      = $api->post('year');
  $month     = $api->post('month');
  //如有 staff_id 則取 沒有就是登入者自己
  if($api->post('staff_id')){
    $id = $api->post('staff_id');
  }else{
    $id = $api->SC->getId();
  }
  // //取得直屬上司

  $department_id = $api->SC->getDepartmentId();

  $condition = $api->condition(array(
    // 'owner_staff_id'=> $id,
    'owner_department_id'=> $department_id,
    'year'=>$year,
    'month'=>$month,
    'date_after'=>date("Y-m-d", time() - (60 * 60 * 24 * 30 * 3))
  ));
  $process = new MonthlyProcessing();
  
  $process_data = $process->getThisWithOwnerDepartmentId( $condition );

  $result = array();

  // api 整理資料需求
  if( count($process_data) > 0){

    //找出所有員工
    $sd = new StaffDepartment();
    $mpe = new MonthlyProcessingEvaluating();
    $sd->collect();
    $sd_map = $sd->map();
    $team_map = $sd->team->map();

    foreach($process_data as $key=>&$val){

      if((int)$val['status_code']==5) {continue;}
      
      // if($mpe->read(['status_code'], ['processing_id'=>$val['id'], 'staff_id'=>$id])->isSubmited()) {continue;}

      if ($val['owner_staff_id'] == $id ) {
        $owner = $sd_map[ $val['owner_staff_id'] ];
      } else {
        $owner = $sd_map[ $val['prev_owner_staff_id'] ];
      }

      $created_staff = $sd_map[ $val['created_staff_id'] ];
      $val['created_unit_name'] = $team_map[ $val['created_department_id'] ][ 'unit_name' ];
      $val['created_unit_id'] = $team_map[ $val['created_department_id'] ][ 'unit_id' ];
      $val['created_name'] = $created_staff['name'];
      $val['created_name_en'] = $created_staff['name_en'];
      $val['owner_name'] = $owner['name'];
      $val['owner_name_en'] = $owner['name_en'];
      $val['staff_count'] = $sd->getStaffCounts( $val['created_department_id'] );
      // stamp_log(__FILE__.' * LINE = '.__LINE__);
      $result[] = $val;

    }

  }

  $api->setArray( $result );

}else{

}

print $api->getJSON();

?>