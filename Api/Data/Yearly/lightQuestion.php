<?php
include __DIR__."/../../ApiCore.php";


$api = new ApiCore($_POST);

use Model\Business\RecordYearPerformanceQuestions;

  
if( $api->checkPost(array('id','highlight')) && $api->SC->isSuperUser() ){
  $rypq = new RecordYearPerformanceQuestions();
  $id = $api->post('id');
  $highlight = $api->post('highlight');
  $hightlightAry = [RecordYearPerformanceQuestions::HIGHTLIGHT_YES, RecordYearPerformanceQuestions::HIGHTLIGHT_NO];
  if (!in_array($highlight, $hightlightAry)) {
    return  $api->denied('You Must Input Correct Highlight.');
  }
  $c = $rypq->update(array('highlight'=> $highlight ), ['id' => $api->post('id')]);
  if($c>0){
    $result = [
      'status'    => 'OK.',
      'highlight' => $highlight,
      'id'        => $id
    ];
  }else{
    $api->denied('Nothing Chnaged.');
  }
  
  $api->setArray($result);
  
} else{
  $api->denied('You Have Not Promised.');
}

print $api->getJSON();
