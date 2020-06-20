<?php
include __DIR__."/../ApiCore.php";

use Model\Business\StaffTitleLv;


$API = new ApiCore($_POST, ['lv','name'], ApiCore::IS_ADMIN );


$update_data = $API->getPost();


$title = new StaffTitleLv;


$true_update = $title->trueColumn( $update_data );
//有啟用的才檢查是否重複
$true_update['enable'] = 1;

$new_id = $title->read(['id'],$true_update)->check(1,'This Data Is Already Exist.')->create( $true_update );


if($new_id>0){
  $result = $title->select( ['id','name','lv'], $new_id )[0];
  //紀錄
  $self_id = $API->SC->getId();
  $record = new \Model\Business\RecordAdmin( $self_id );
  $record->type($record::TYPE_SETTING)->add( $result );
}else{
  $API->denied('Fail To Add Title.');
}


$API->result($result);
?>