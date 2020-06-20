<?php
include __DIR__."/../ApiCore.php";
//table
use Model\Business\StaffPost;
use Model\Business\Staff;


//basic api
$API = new ApiCore($_POST, ['post_id'], ApiCore::IS_ADMIN );

//get table id
$id = $API->post('post_id');
//get others param
$update_data = $API->getPost();


$sPost = new StaffPost;
// filter column
$true_update = $sPost->trueColumn( $update_data );

//確認有該筆資料後 更新
// $result = $title->read( $id )->check()->update( $true_update, $id );
$result = $sPost->update( $true_update, $id );

//沒有資料被更新
if($result==0){
  $API->denied('Nothing Change.');
}




//印出修改後的內容
$result = $sPost->select( ['id','name','type','orderby','enable'], $id )[0];
//紀錄
$self_id = $API->SC->getId();
$record_data = $true_update;
$record_data['id']=$id;
$record = new \Model\Business\RecordAdmin( $self_id );
$record->type($record::TYPE_SETTING)->update( $record_data );

//更新員工 post
$staff = new Staff();
$post_table = $sPost->table_name;
$staff->sql("update {table} as a left join $post_table as b on a.post_id=b.id set a.post = b.name;");


$API->result($result);
?>