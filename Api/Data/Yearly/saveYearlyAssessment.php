<?php
include __DIR__."/../../ApiCore.php";
include BASE_PATH.'/Model/dbBusiness/Multiple/YearlyAssessment.php';

$api = new ApiCore($_POST);
use Model\Business\Multiple\YearlyAssessment;

if( $api->checkPost(array('assessment_id')) && $api->SC->isLogin() ){

    
    $assessment_json = $api->post('assessment_json');
    $self_contribution = $api->post('self_contribution');
    $self_improve = $api->post('self_improve');
    $comment = $api->post('comment');
    
    if(empty($assessment_json) && empty($self_contribution) && empty($self_improve) && empty($comment)){$api->denied('Nothing Changed.');}
    
    // if(!is_array($ar)){ $api->denied('Multiple Choice Is Not Right Json.'); }
    // if(count($ar) == 0){ $api->denied('Multiple Choice Is Empty.'); }
    
    $year = $api->SC->get('year');
    if(empty($year)){ $api->denied('SomeThings Wrong.');}
    $ya = new YearlyAssessment($year);
    
    $update_data = [];
    if( !empty($assessment_json) ){ $update_data['assessment_json'] = $assessment_json; }
    if( !empty($self_contribution) ){ $update_data['self_contribution'] = $self_contribution; }
    if( !empty($self_improve) ){ $update_data['self_improve'] = $self_improve; }
    if( !empty($comment) ){ $update_data['comment'] = $comment; }
    
    $result = $ya->saveYearlyAssessment( $api->post('assessment_id'), $api->SC->getMember(), $update_data );

    $api->setArray($result);

}else{
    $api->denied('You Have Not Promised.');
}

print $api->getJSON();
?>