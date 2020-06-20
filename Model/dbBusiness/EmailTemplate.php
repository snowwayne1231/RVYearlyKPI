<?php
namespace Model\Business;

include_once __DIR__.'/Common/DBPropertyObject.php';

class EmailTemplate extends DBPropertyObject{
  
  //實體表 :: 單表
  public $table_name = "rv_email_template";
  
  //欄位
  public $tables_column = Array(
    'id',
    'name',
    'title',
    'text',
    'update_operatinger_id',
    'update_date'
  );
  
  public function __construct($db=null){
    parent::__construct($db);
  }
  
}
?>
