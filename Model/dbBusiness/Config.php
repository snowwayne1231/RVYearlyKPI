<?php
namespace Model\Business;

include_once __DIR__.'/Common/DBPropertyObject.php';

class Config extends DBPropertyObject{
  
  //實體表 :: 單表
  public $table_name = "rv_config";
  
  //欄位
  public $tables_column = Array(
    'id',
    'name',
    'json',
    'update_date'
  );
  
  public function __construct($db=null){
    parent::__construct($db);
  }
  
}
?>
