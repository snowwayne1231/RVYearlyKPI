<?php
namespace Model\Business;

include_once __DIR__.'/Common/DBPropertyObject.php';

class RecordPersonalCommentChanged extends DBPropertyObject{
  
  //實體表 :: 單表
  public $table_name = "rv_record_personal_comment_changed";
  
  //欄位
  public $tables_column = Array(
    'id',
    'comment_id',
    'create_staff_id',
    'target_staff_id',
    'content',
    'change_time'
  );
  
  public function __construct($db=null){
    parent::__construct($db);
  }
  
}
?>
