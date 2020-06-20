<?php
namespace Model\Business;

include_once __DIR__.'/Common/DBPropertyObject.php';

class StaffHistoryEvent extends DBPropertyObject{

  //實體表 :: 單表
  public $table_name = "rv_staff_history_event";

  //欄位
  public $tables_column = Array(
    'staff_id',
    'status',
    'event',
    'event_day',
    'create_date'
  );

  public function __construct($db=null){
    parent::__construct($db);
  }

}
?>
