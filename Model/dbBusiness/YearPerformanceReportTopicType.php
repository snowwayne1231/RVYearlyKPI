<?php
namespace Model\Business;

include_once __DIR__.'/Common/DBPropertyObject.php';

class YearPerformanceReportTopicType extends DBPropertyObject{
  
  //實體表 :: 單表
  public $table_name = "rv_year_performance_report_topic_type";
  
  //欄位
  public $tables_column = Array(
    'id',
    'name',
    'sort',
    'enable'
  );
  
  public function __construct($db=null){
    parent::__construct($db);
  }
  
}
?>
