<?php
namespace Model\Business;

include_once __DIR__.'/Common/DBPropertyObject.php';

class RecordStaff extends DBPropertyObject{
  
  //實體表 :: 單表
  public $table_name = "rv_record_staff";
  
  //欄位
  public $tables_column = Array(
    'id',
    'operating_staff_id',
    'staff_id',
    'doing',
    'changed_json',
    'update_date'
  );
  
  public function __construct($db=null){
    parent::__construct($db);
  }
  
  //override
  public function add($data=null){
    $data['ip'] = getIP();
    return parent::add($data);
  }
  
  //override
  public function create($data=null){
    $data['ip'] = getIP();
    return parent::create($data);
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
        if(isset($val['changed_json'])){
          $val['changed_json'] = json_decode($val['changed_json'],true);
        }
      }
    }
    return $this;
  }
  
}
?>
