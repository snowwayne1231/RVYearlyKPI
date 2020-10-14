<?php
namespace Model\Business;

include_once __DIR__.'/Common/DBPropertyObject.php';

class AttendanceMonthlySpecial extends DBPropertyObject{
  
  const TYPE_NONE = 0;
  const TYPE_NOCARD = 1;        //沒帶卡
  const TYPE_FORGETCARD = 2;    //忘刷卡
  // const TYPE_LATE = 3;          //特殊遲到
  
  
  //實體表 :: 單表
  //出缺勤記錄表 (特殊事件)
  public $table_name = "rv_attendance_monthly_special";
  
  //欄位
  public $tables_column = Array(
    'id',           // int(11) NOT NULL AUTO_INCREMENT,
    'department_id',// int(11) NOT NULL COMMENT '部門id'',
    'staff_id',     // int(11) NOT NULL COMMENT '員工id',
    'outside_number',// int(11) NOT NULL COMMENT '外來編號',
    'date',         // date DEFAULT NULL COMMENT '日期',
    'time',         // time DEFAULT NULL COMMENT '時間',
    'year',         // int(4) DEFAULT NULL COMMENT '年戳',
    'month',        // int(4) DEFAULT NULL COMMENT '月戳',
    'type',         // int(2) NOT NULL COMMENT '類型id',
    'reason',       // varchar(32) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '原因',
    'remark',       // varchar(255) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '備註欄',
    'create_date'   // timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '建立日期',
  );

  public function __construct(){
    parent::__construct();
    $this->set_limit(0);
  }


  public function checkAttendanceScoreAllowed($score) {
    $max = 5;
    $cnt = count($this->data);
    if ($cnt > 5) {
      $max = 0;
    } else if ($cnt == 5) {
      $max = 1;
    } else if ($cnt == 4) {
      $max = 2;
    } else if ($cnt ==3) {
      $max = 3;
    } else if ($cnt ==2) {
      $max = 4;
    }
    return $score <= $max;
  }
  
  
  public function getMapStaffDate() {
    $map = [];
    foreach ($this->data as $data) {
      $map[$data['staff_id']][$data['date']][] = $data;
    }
    return $map;
  }
  
}
?>
