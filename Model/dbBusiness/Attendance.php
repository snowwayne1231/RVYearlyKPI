<?php
namespace Model\Business;

include_once __DIR__.'/Common/DBPropertyObject.php';

class Attendance extends DBPropertyObject{
  
  //實體表 :: 單表
  //出缺勤記錄表
  public $table_name = "rv_attendance";
  
  //欄位
  public $tables_column = Array(
    'id',
    'staff_id',
    'date',
    'checkin_hours',
    'checkout_hours',
    'work_hours_total',
    'late',
    'early',
    'nocard',
    'remark',
    'vocation_hours',
    'vocation_from',
    'vocation_to',
    'overtime_hours',
    'overtime_from',
    'overtime_to'
  );
  
  public function __construct(){
    parent::__construct();
  }
  
  public function isExistDate($ary){
    $is = false;
    if( is_array($ary) ){
      $where = "('".join("','",$ary)."')";
      $result = $this->read(array('date'),"where date in $where")->map('date',true);
      $is = count($ary)==count($result);
    }
    return $is;
    
  }
  
  public function getMapWithTwoDate($startDate, $endDate, $col=array('staff_id', 'late', 'early', 'nocard', 'remark'), $addWhere=''){
    $columns = is_array($col) ? join(',',$col) : $col;
    if(!empty($addWhere)){ $addWhere=" AND $addWhere"; }
    $this->sql(" select $columns from {table} where date BETWEEN '$startDate' AND '$endDate' $addWhere");
    $map = array();
    foreach($this->data as $v){
      $map[$v['staff_id']][] = $v;
    }
    return $map;
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
        if(isset($val['remark'])){ $val['remark'] = preg_replace('/([\d]+\:[\d]{2})\:[\d]{2}/','$1', $val['remark']); }
      }
    }
    return $this;
  }
}
?>
