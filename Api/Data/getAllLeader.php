<?php

include __DIR__."/../ApiCore.php";
include BASE_PATH.'/Model/dbBusiness/Staff.php';

use Model\Business\Staff;
use Model\Business\YearPerformanceConfigCyclical;

$api = new ApiCore($_POST);

if( $api->SC->isLogin() ){
  
  $staff = new Staff();
  
  $year = $api->post('year');
  
  $col = array('id','staff_no','title','post','name','name_en','rank');
  
  if($year){
    
    $year_config = new YearPerformanceConfigCyclical($year);
    $leader_array = $year_config->getAllLeader();
    if(count($leader_array)==0){ $api->denied('Not Found Any Leader.'); }
    $ids = join(',',$leader_array);
    $result = $staff->select( $col, array('id'=>"in($ids)"),'order by rank desc, staff_no' );
    
  }else{
    $result = $staff->select( $col, array('is_leader'=>1) );
  }
  
  //濾掉自己
  if($api->SC->isLeader()){
    $self = $api->SC->getId();
    foreach($result as $i => &$v){
      if($v['id']==$self){ array_splice($result,$i,1);break; }
    }
  }
  //成功結果
  $api->setArray($result);
  
}else{
  $api->denied();
}

print $api->getJSON();

?>