<?php
namespace Model\Business;

include_once __DIR__.'/Common/DBPropertyObject.php';

class YearPerformanceFeedback extends DBPropertyObject{
  
  //狀態
  const STATUS_UN_SUBMIT = 0; //未提交
  const STATUS_SUBMIT = 1; //已提交
  const STATUS_FAILURE = -1; //作廢
  //實體表 :: 單表
  public $table_name = "rv_year_performance_feedback";
  
  //欄位
  public $tables_column = Array(
    'id',
    'year',
    'staff_id',
    'department_id',
    'status', // -1: 作廢, 0: 未提交, 1: 已提交
    'target_staff_id',
    'multiple_choice_json',
    'multiple_total',
    'update_date'
  );
  
  public function __construct($db=null){
    parent::__construct($db);
  }
  
  //override
  public function select($a=null,$b=0,$c=null){
    parent::select($a,$b,$c);
    return $this->parseJSON()->data;
  }
  //override
  public function read($a=null,$b=0,$c=null){
    parent::read($a,$b,$c);
    return $this->parseJSON();
  }
  //override
  public function sql($a, $bindData= []){
    parent::sql($a, $bindData);
    return $this->parseJSON();
  }
  
  private function parseJSON(){
    foreach($this->data as &$val){
      if(isset($val['multiple_choice_json']))$val['multiple_choice_json'] = json_decode($val['multiple_choice_json'],true);
    }
    return $this;
  }
  
  /**
   *  取得 還未完成的單
   */
  public function getUnDo($staff_id){
    return $this->select(['id'],['status'=>self::STATUS_UN_SUBMIT, 'staff_id'=>$staff_id]);
  }
  
}
?>
