<?php
namespace Model\Business;

include_once __DIR__.'/Common/DBPropertyObject.php';

class StaffStatus extends DBPropertyObject{
  
  //實體表 :: 單表
  public $table_name = "rv_staff_status";
  
  //欄位
  public $tables_column = Array(
    'id',
    'name'
  );
  
  public function __construct($db=null){
    parent::__construct($db);
  }
  
}
?>
