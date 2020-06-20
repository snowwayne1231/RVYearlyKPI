<?php
include __DIR__."/../ApiCore.php";
//table
use Model\Business\StaffTitleLv;
use Model\Business\Staff;


//basic api
$API = new ApiCore($_POST, ['title_id'], ApiCore::IS_ADMIN );

//get table id
$id = $API->post('title_id');
//get others param
$update_data = $API->getPost();


$title = new StaffTitleLv;
// filter column
$true_update = $title->trueColumn( $update_data );
//更新不能修改 LV 不然整體資料改動幅度太大
if(isset($true_update['lv'])){ unset($true_update['lv']); }

//確認有該筆資料後 更新
// $result = $title->read( $id )->check()->update( $true_update, $id );
$result = $title->update( $true_update, $id );

//沒有資料被更新
if($result==0){
  $API->denied('Nothing Change.');
}




//印出修改後的內容
$result = $title->select( ['id','name','lv'], $id )[0];
//紀錄
  $self_id = $API->SC->getId();
  $record_data = $true_update;
  $record_data['id']=$id;
  $record = new \Model\Business\RecordAdmin( $self_id );
  $record->type($record::TYPE_SETTING)->update( $record_data );
  
//更新員工 title
$staff = new Staff();
$title_table = $title->table_name;
$staff->sql("update {table} as a left join $title_table as b on a.title_id=b.id set a.title = b.name;");
  

$API->result($result);
?>