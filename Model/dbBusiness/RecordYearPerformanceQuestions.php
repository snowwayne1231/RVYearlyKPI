<?php
namespace Model\Business;

include_once __DIR__.'/Common/DBPropertyObject.php';

class RecordYearPerformanceQuestions extends DBPropertyObject{
  
  const TYPE_SAVE = 1;
  const TYPE_COMMIT = 2;
  const TYPE_AGREE = 3;
  const TYPE_RETURN = 4;
  const TYPE_OTHER = 5;
  
  //實體表 :: 單表
  public $table_name = "rv_record_year_performance_questions";
  
  //欄位
  public $tables_column = Array(
    'id',
    'question_id',
    'year',
    'from_type',  //來源 1=部屬, 2=其他部門, 3=上司, 4=其他
    'highlight',
    'target_staff_id',
    'content',
    'create_date'
  );
  
  const FROM_TYPE_SUBORDINATE = 1; //部屬
  const FROM_TYPE_ETC_DEPEART = 2; //其他部門
  const FROM_TYPE_BOSS = 3; //上司
  const FROM_TYPE_ETC = 4; //其他人
  //關注
  const HIGHTLIGHT_YES = 1;
  const HIGHTLIGHT_NO = 0;
  public function __construct($db=null){
    parent::__construct($db);
  }

}
?>
