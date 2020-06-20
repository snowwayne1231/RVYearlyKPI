<?php
include __DIR__."/../../ApiCore.php";

$api = new ApiCore($_POST);

use Model\Business\Multiple\YearlyAssessment;
use Model\Business\Multiple\StaffHistory;


if( $api->checkPost(array('year','department_level'))  && $api->SC->isLogin() ){

  $year = $api->post('year');
  $api->SC->set('year',$year);

  $department_level = $api->post('department_level');
  if( isset($department_level) && (!is_numeric($department_level) || (int)$department_level > 5)){ $api->denied('Param Wrong.'); }

  $with_assignment = empty($api->post('with_assignment')) ? false : true;
  $is_over = empty($api->post('is_over')) ? false : true;

  $yfb = new YearlyAssessment( $year );
  $sh = new StaffHistory();
  $self_id = $api->SC->getId();


  $result = $yfb->getPerfomanceList( $self_id , $year, $department_level, $with_assignment, $is_over );
  //員工只能看自評
  if(!$api->SC->isLeader()){
    foreach($result['assessment']['staff'] as &$sv){
      foreach($sv['assessment_json'] as $k=>$v){
        if($k!='self'){ unset($sv['assessment_json'][$k]); }
      }
    }
  }

  foreach($result['assessment']['leader'] as &$sv){
    if($resSH = $sh->getStayWithStaff($sv['staff_id'])){
      $sv['staff_stay'] = $resSH;
    }else{
      $sv['staff_stay'] = array();
    }
  }

  foreach($result['assessment']['staff'] as &$sv){
    if($resSH = $sh->getStayWithStaff($sv['staff_id'])){
      $sv['staff_stay'] = $resSH;
    }else{
      $sv['staff_stay'] = array();
    }
  }

  //結果
  if( isset($result['error']) ){
    $api->denied($result['error']);
  }else{
    $api->setArray($result);
  }


}else{
  $api->denied('You Have Not Promised.');
}

print $api->getJSON();

?>