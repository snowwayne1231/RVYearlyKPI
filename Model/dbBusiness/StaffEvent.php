<?php
namespace Model\Business;

include_once __DIR__.'/Common/DBPropertyObject.php';

class StaffEvent extends DBPropertyObject{

  //實體表 :: 單表
  public $table_name = "rv_staff_event";

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
