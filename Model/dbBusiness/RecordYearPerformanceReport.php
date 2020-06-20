<?php
namespace Model\Business;

include_once __DIR__.'/Common/DBPropertyObject.php';

class RecordYearPerformanceReport extends DBPropertyObject{
  
  const TYPE_SAVE = 1;
  const TYPE_COMMIT = 2;
  const TYPE_AGREE = 3;
  const TYPE_RETURN = 4;
  const TYPE_OTHER = 5;
  
  //實體表 :: 單表
  public $table_name = "rv_record_year_performance_report";
  
  //欄位
  public $tables_column = Array(
    'id',
    'operating_staff_id',
    'report_id',
    'type',
    'origin_json',
    'changed_json',
    'create_date'
  );
  
  public function __construct(){
    parent::__construct();
  }
  
  public function save($staff_id, $report_id, $origin_data, $update_data){
    $this->now_type = self::TYPE_SAVE;
    return $this->commonFn($staff_id, $report_id, $origin_data, $update_data);
  }
  
  public function commit($staff_id, $report_id, $origin_data, $update_data){
    $this->now_type = self::TYPE_COMMIT;
    return $this->commonFn($staff_id, $report_id, $origin_data, $update_data, false);
  }
  
  public function back($staff_id, $report_id, $origin_data, $update_data){
    $this->now_type = self::TYPE_RETURN;
    return $this->commonFn($staff_id, $report_id, $origin_data, $update_data, false);
  }
  
  public function agree($staff_id, $report_id, $origin_data, $update_data){
    $this->now_type = self::TYPE_AGREE;
    return $this->commonFn($staff_id, $report_id, $origin_data, $update_data, false);
  }
  
  public function other($staff_id, $report_id, $origin_data, $update_data){
    $this->now_type = self::TYPE_OTHER;
    return $this->commonFn($staff_id, $report_id, $origin_data, $update_data, false);
  }
  
  private $now_type;
  private function commonFn($a, $b, $c, $d, $filter=true){
    if($filter){
      foreach($c as $k=>$v){
        if( empty($d[$k]) ){ unset($c[$k]); }
        else if($v==$d[$k]){ unset($c[$k]);unset($d[$k]); }
      }
    }
    
    if(count($d)==0){return 0;}
    if(isset($d['reason'])){
      $d['reason'] = urlencode($d['reason']);
    }
    
    return $this->add(array(
      'operating_staff_id' => $a,
      'report_id' => $b,
      'origin_json' => $c,
      'changed_json' => $d,
      'type' => $this->now_type
    ));
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
    if (is_array($this->data)) {
      foreach($this->data as &$val){
        if(isset($val['changed_json']))$val['changed_json'] = json_decode($val['changed_json'],true);
        if(isset($val['origin_json']))$val['origin_json'] = json_decode($val['origin_json'],true);
      }
    }
    return $this;
  }
  
}
?>
