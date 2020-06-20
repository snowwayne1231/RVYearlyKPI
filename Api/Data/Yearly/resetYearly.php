<?php
include __DIR__."/../../ApiCore.php";
$api = new ApiCore($_POST);
use Model\Business\Multiple\YearlyFinally;

if( $api->checkPost(array('year')) &&  $api->SC->isAdmin() ){

    $year = $api->post('year');
    
    $YearlyFinally = new YearlyFinally( $year );
    
    $result = $YearlyFinally->deleteAllData();

    $api->setArray($result);
    
    //紀錄
    $self_id = $api->SC->getId();
    $record_data = $api->getPost();
    $record = new \Model\Business\RecordAdmin( $self_id );
    $record->type($record::TYPE_YEAR)->delete( $record_data );
    
    
}else{
    $api->denied('You Have Not Promised.');
}

print $api->getJSON();

?>