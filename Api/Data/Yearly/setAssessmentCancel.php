<?php

include __DIR__."/../../ApiCore.php";


use Model\Business\Multiple\YearlyAssessment;

$api = new ApiCore($_REQUEST);
try {
  if( $api->checkPost(['assessment_id']) && $api->SC->isAdmin() ){
    $year = $api->SC->get('year');
     $yearly_assessment = new YearlyAssessment($year);
     $assessment_id = $api->post('assessment_id');
     $enable = empty($api->post('enable')) ? 0 : 1;
     $self_id = $api->SC->getId();
     $result = $yearly_assessment->setAssessmentCancel($assessment_id, $enable, $self_id);
     $api->setArray($result);
     
     //紀錄
     if($result['status']=='OK.'){
        $record_data = $api->getPost();
        $record = new \Model\Business\RecordAdmin( $self_id );
        $record->type($record::TYPE_YEAR_REPORT)->update( $record_data );
     }
    
      
  } else {
    $api->denied('You Have Not Promised.');
  } 
} catch (\Exception $ex) {
    $api->denied($ex);
}


print $api->getJSON();