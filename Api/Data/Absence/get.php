<?php
include __DIR__.'/../../ApiCore.php';
include BASE_PATH.'/Model/dbBusiness/ConfigCyclical.php';
include BASE_PATH.'/Model/dbBusiness/Multiple/DepartmentAttendance.php';

$api = new ApiCore($_REQUEST);

if($api->checkPost(array("year","month"))){
  
    //年/月
    $year =  $api->post('year');
    $month = $api->post('month');
    $team_id = $api->post('team_id');
    $staff_id = $api->post('staff_id');
    
    
    //取得該月的設定值
    $webconfig = new Model\Business\ConfigCyclical();
    $config = $webconfig->getConfigWithDate($year, $month);
    $DateRangeStart = $config['RangeStart'];
    $DateRangeEnd = $config['RangeEnd'];
    
    $attendance = new Model\Business\Multiple\DepartmentAttendance();

    $self_id = $api->SC->getId();
    $seld_data = $api->SC->getMember();
    $is_CEO = $api->SC->isCEO();
    $is_admin = $api->SC->isAdmin();
    $is_leader = $api->SC->isLeader();
    
    $result = array();
    
    if( $team_id ){

      $team_id = preg_replace('/[\[\]\)\(]+/','',$team_id);

      if ($is_CEO || $is_admin) {
        
        $result = $attendance->getWithDate($DateRangeStart, $DateRangeEnd, $team_id);

      } else if ($is_leader) {

        $result = $attendance->getWithDateWithLeader($seld_data, $DateRangeStart, $DateRangeEnd, $team_id);
        
      } else {

        $api->denied('General Staff Can Not Search By Team Id.');

      }

    }else if( $staff_id ){
      
      $staff_id = preg_replace('/[\s\r\n!@#%$\[\]]+/i','',$staff_id);
      
      $result = $attendance->getWithDate($DateRangeStart, $DateRangeEnd, null, $staff_id );

    }else{
      
      $result = $attendance->getWithDate($DateRangeStart, $DateRangeEnd);

    }
    
    // $api->setArray($result);
    $newResult = $attendance->bTree();
    
    $api->setArray($newResult);
}

print $api->getJSON();
?>