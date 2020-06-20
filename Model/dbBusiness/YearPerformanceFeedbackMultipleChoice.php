<?php
namespace Model\Business;

include_once __DIR__.'/Common/DBPropertyObject.php';

class YearPerformanceFeedbackMultipleChoice extends DBPropertyObject{
  
  //實體表 :: 單表
  public $table_name = "rv_year_performance_feedback_multiple_choice";
  
  //欄位
  public $tables_column = Array(
    'id',
    'title',
    'description',
    'sort',
    'options_json',
    'score',
    'enable'
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
  
  private function parseJSON(){
    foreach($this->data as &$val){
      if(isset($val['options_json']))$val['options_json'] = json_decode($val['options_json'],true);
    }
    return $this;
  }
  
}
?>
