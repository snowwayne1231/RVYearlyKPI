<?php
include __DIR__."/../../ApiCore.php";
include_once BASE_PATH.'/Model/dbBusiness/Multiple/YearlyFeedback.php';
include_once BASE_PATH.'/Model/ToolKit/utf8Chinese.php';

$api = new ApiCore($_POST);

use Model\Business\Multiple\YearlyFeedback;
use Model\ToolKit\utf8Chinese;


if( $api->checkPost(array('year')) && $api->SC->isLogin() ){

  $other = $api->post('others_questions');
  $company = $api->post('company_questions');
  $year = $api->post('year');

  $yf = new YearlyFeedback( $year );
  $utf8_chinese_str = new utf8Chinese();

  foreach($other as &$oval){
    foreach($oval as &$o){
      $o = $utf8_chinese_str->gb2312_big5($o);
    }
  }

  foreach($company as &$c){
    $c = $utf8_chinese_str->gb2312_big5($c);
  }

  if( is_array($other) || is_array($company)){
    $changed = $yf->collectQuestion( array('other'=>$other,'company'=>$company) );
    if($changed >0){
      $result = array('status'=>'OK.','change'=>$changed);
    }else{
      $result = array('status'=>'Nothing Change.','change'=>$changed);
    }
  }else{
    $result = array('error'=>'No Data Can Be Collect.');
  }


  //結果
  $api->setArray($result);


}else{
  $api->denied('You Have Not Promised.');
}

print $api->getJSON();

?>