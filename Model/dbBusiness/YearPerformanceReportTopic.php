<?php
namespace Model\Business;

include_once __DIR__.'/Common/DBPropertyObject.php';

class YearPerformanceReportTopic extends DBPropertyObject{
  
  //實體表 :: 單表
  public $table_name = "rv_year_performance_report_topic";
  
  //欄位
  public $tables_column = Array(
    'id',
    'type',
    'name',
    'score',
    'score_leader',
    'sort',
    'enable',
    'applicable'
  );
  
  public function __construct($db=null){
    parent::__construct($db);
  }
  
  public function getSplitApplicable(){
    $res = array('normal'=>array(),'leader'=>array());
    foreach($this->data as $v){
      if( isset($v['applicable']) ){
        $av = $v['applicable'];
        unset($v['applicable']);
        switch($av){
          case "normal":
            unset($v['score_leader']);
            $res['normal'][] = $v;
          break;
          case "leader":
            $v['score']=$v['score_leader'];
            unset($v['score_leader']);
            $res['leader'][] = $v;
          break;
          case "both":
            $lv = $v;
            $lv['score']=$v['score_leader'];
            unset($v['score_leader']);
            unset($lv['score_leader']);
            $res['normal'][] = $v;
            $res['leader'][] = $lv;
          break;
        }
      }
    }
    return $res;
  }
  
}
?>
