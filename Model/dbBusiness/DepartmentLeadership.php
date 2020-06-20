<?php
namespace Model\Business;

include_once __DIR__.'/Common/DBPropertyObject.php';

class DepartmentLeadership extends DBPropertyObject{
  
  //實體表 :: 單表
  public $table_name = "rv_department_leaderships";
  
  //欄位
  public $tables_column = Array(
    'id',
    'department_id',
    'staff_id',
    'status',
  );
  
  public function __construct(){
    parent::__construct();
  }
  
}
?>
