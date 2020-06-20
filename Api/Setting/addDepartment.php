<?php

include __DIR__."/../ApiCore.php";
include BASE_PATH.'/Model/dbBusiness/Department.php';
include BASE_PATH.'/Model/dbBusiness/ConfigCyclical.php';


$api = new ApiCore($_POST);

use Model\Business\Department;
use Model\Business\ConfigCyclical;
  
if( $api->checkPost(array('lv','unit_id','name','upper_id')) && $api->isAdmin() ){
  
  //當月評分
  $webconfig = new ConfigCyclical();
  $year = date("Y");
  $month = date("m");
  $config = $webconfig->getConfigWithDate($year,$month);
  if( $api->isPast($config['RangeEnd']) ){
    
  }else{
    //當月已經起動
    if($config['monthly_launched']==1){ $api->denied('Can Not Modify Staff When Monthly Launch.'); }
  }
  
  $result=null;
  $codi = $api->getPOST();
  if( preg_match('/^[A-Z]{1}[\d]{2}$/i',$codi['unit_id']) ){
    $team = new Department();
    $team_map = $team->map();
    
    if( empty($team_map[ $codi['upper_id'] ]) ){
      $api->denied('Not Found Upper Department.');
    }
    $see = $team->search(array('unit_id'=>$codi['unit_id']));
    if( count($see)>0){
      $api->denied('Double unit_id.'); 
    }
    
    $upper_team = $team_map[ $codi['upper_id'] ];
    $super = ($upper_team['manager_staff_id']>0)?$upper_team['manager_staff_id']:$upper_team['supervisor_staff_id'];
    $codi['duty_shift'] = (isset( $codi['duty_shift'] )) ? 1 : 0;
    $codi['supervisor_staff_id'] = $super;
    
    // LG($codi);
    $id = $team->create($codi);
    $result = $team->select($id);
    
  }else{
    $api->denied('Unit Id Wrong.');
  }
  
  //成功結果
  if($result){
    $api->setArray($result);
    //紀錄
    $self_id = $api->SC->getId();
    $record_data = $result[0];
    unset($record_data['update_date']);
    $record = new \Model\Business\RecordAdmin( $self_id );
    $record->type($record::TYPE_SETTING)->add( $record_data );
  }else{
    $api->denied('Param Wrong.');
  }
  
}else{
  // var_dump($_POST);
}

print $api->getJSON();

?>