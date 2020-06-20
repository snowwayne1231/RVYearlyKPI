<?php
include __DIR__."/../../ApiCore.php";

$api = new ApiCore($_POST);
use Model\Business\Multiple\YearlyAssessment;

if( $api->checkPost(array('assessment_id')) && $api->SC->isLogin() ){
    
    $year = $api->SC->get('year');
    if(empty($year)){ $api->denied('SomeThings Wrong.');}
    
    $ya = new YearlyAssessment( $year );
    $self_id = $api->SC->getId();
    $result = $ya->commitYearlyAssessment( $api->post('assessment_id'), $self_id );
    
    $api->setArray($result);
    
    
}else{
    $api->denied('You Have Not Promised.');
}



print $api->getJSON();
?>