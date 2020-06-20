<?php
namespace Model\Business;

include_once __DIR__.'/Common/DBPropertyObject.php';

class YearPerformanceReportPercents extends DBPropertyObject{
  
  //實體表 :: 單表
  public $table_name = "rv_year_performance_report_percents";
  
  //欄位
  public $tables_column = Array(
    'id',
    'lv',
    'type',
    'percent_json',
    'enable'
  );
  
  private $meta_map;
  
  public function __construct(){
    parent::__construct();
  }
  
  public function getTypeLvMap(){
    if(empty($this->meta_map)){
      $map = $this->map('type,lv');
      $new = array();
      foreach($map as $k => $v){
        $new[$k] = $v['percent_json'];
      }
      $this->meta_map = $new;
    }
    return $this->meta_map;
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
      if(isset($val['percent_json'])){
        $new_json = json_decode($val['percent_json'],true);
        $new_result = array();
        foreach($new_json as $key => $val2){
          if($val2<=0){continue;}
          $new_key = str_replace('_','',$key);
          if($new_key==''){$new_key='under';}
          if($new_key=='0'){$new_key='self';}
          $new_result[ $new_key ] = $val2;
        }
        $val['percent_json'] = $new_result;
      }
    }
    return $this;
  }
}
?>
