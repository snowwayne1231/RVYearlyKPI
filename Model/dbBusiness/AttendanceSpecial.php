<?php
namespace Model\Business;

include_once __DIR__.'/Common/DBPropertyObject.php';

class AttendanceSpecial extends DBPropertyObject{
  
  const TYPE_NOCARD = 1;        //沒帶卡
  const TYPE_FORGETCARD = 2;    //忘刷卡
  const TYPE_LATE = 3;          //特殊遲到
  
  
  //實體表 :: 單表
  //出缺勤記錄表 (特殊事件)
  public $table_name = "rv_attendance_special";
  
  //欄位
  public $tables_column = Array(
    'id',           // int(11) NOT NULL AUTO_INCREMENT,
    'staff_id',     // int(11) NOT NULL COMMENT '員工id',
    'date',         // date DEFAULT NULL COMMENT '日期',
    'type',         // int(2) NOT NULL COMMENT '類型id',
    'value',        // int(11) DEFAULT '0' COMMENT '數值內容',
    'value_char',   // varchar(1024) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '字符內容',
    'remark',       // varchar(255) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '備註欄',
    'create_date'   // timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '建立日期',
  );
  
  public function __construct(){
    parent::__construct();
  }
  
  
  
}
?>
