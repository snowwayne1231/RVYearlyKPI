<?php
namespace Model\Business;

include_once __DIR__.'/Common/DBPropertyObject.php';

class StaffPost extends DBPropertyObject{
  
  //實體表 :: 單表
  public $table_name = "rv_staff_post";
  
  //欄位
  public $tables_column = Array(
    'id',
    'name',
    'type',
    'orderby',
    'enable'
  );
  
  public function __construct($db=null){
    parent::__construct($db);
  }
  
}
?>
