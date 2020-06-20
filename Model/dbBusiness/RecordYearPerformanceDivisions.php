<?php
namespace Model\Business;

include_once __DIR__.'/Common/DBPropertyObject.php';

class RecordYearPerformanceDivisions extends DBPropertyObject{
  
  const TYPE_SAVE = 1;
  const TYPE_COMMIT = 2;
  const TYPE_AGREE = 3;
  const TYPE_RETURN = 4;
  const TYPE_OTHER = 5;
  
  //實體表 :: 單表
  public $table_name = "rv_record_year_performance_divisions";
  
  //欄位
  public $tables_column = Array(
    'id',
    'operating_staff_id',
    'division_id',
    'type',
    'origin_json',
    'changed_json',
    'create_date'
  );
  
  public function __construct($db=null){
    parent::__construct($db);
  }
  
  public function save($staff_id, $report_id, $origin_data, $update_data){
    $this->now_type = self::TYPE_SAVE;
    return $this->commonFn($staff_id, $report_id, $origin_data, $update_data);
  }
  
  public function commit($staff_id, $report_id, $origin_data, $update_data){
    $this->now_type = self::TYPE_COMMIT;
    return $this->commonFn($staff_id, $report_id, $origin_data, $update_data);
  }
  
  public function back($staff_id, $report_id, $origin_data, $update_data){
    $this->now_type = self::TYPE_RETURN;
    return $this->commonFn($staff_id, $report_id, $origin_data, $update_data);
  }
  
  public function agree($staff_id, $report_id, $origin_data, $update_data){
    $this->now_type = self::TYPE_AGREE;
    return $this->commonFn($staff_id, $report_id, $origin_data, $update_data);
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
    return $this->add(array(
      'operating_staff_id' => $a,
      'division_id' => $b,
      'origin_json' => $c,
      'changed_json' => $d,
      'type' => $this->now_type
    ));
  }
  
}
?>
