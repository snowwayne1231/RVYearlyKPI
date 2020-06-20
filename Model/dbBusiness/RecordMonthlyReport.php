<?php
namespace Model\Business;

include_once __DIR__.'/Common/DBPropertyObject.php';

/**
 * 修改紀錄：月考評單
 */
class RecordMonthlyReport extends DBPropertyObject{

  //實體表 :: 單表
  public $table_name = "rv_record_monthly_report";

  //欄位
  public $tables_column = Array(
    'id',
    'operating_staff_id',
    'processing_record_id',
    'processing_id',
    'report_id',
    'report_type',
    'changed_json',
    'update_date'
  );

  public function __construct($db=null){
    parent::__construct();
  }

  //override
  public function select($a=null,$b=0,$c=null){
    parent::select($a,$b,$c);
    foreach($this->data as &$val){
      if(isset($val['changed_json']))$val['changed_json'] = json_decode($val['changed_json']);
    }
    return $this->data;
  }

  //override
  public function read($a=null,$b=0,$c=null){
    parent::read($a,$b,$c);
    foreach($this->data as &$val){
      if(isset($val['changed_json']))$val['changed_json'] = json_decode($val['changed_json']);
    }
    return $this;
  }

  public function getChangedWithProcessingRecordId($prid){
    $sql = " select a.update_date, a.changed_json, d.name, d.name_en
    from {table} as a
    left join rv_monthly_report as b on a.report_id = b.id and a.report_type = 2
    left join rv_monthly_report_leader as c on a.report_id = c.id and a.report_type = 1
    left join rv_staff as d on if(b.id>0,b.staff_id,c.staff_id) = d.id
    where a.processing_record_id = $prid ";
    $this->sql($sql);
    $map = array();
    foreach($this->data as &$val){
      $val['changed_json'] = json_decode($val['changed_json'],true);

      $map[$val['name_en']]['name'] = $val['name'];
      $npt = &$map[$val['name_en']];
      $npt['name_en'] = $val['name_en'];
      $npt['update_date'] = $val['update_date'];

      $npt['changed_json'] = isset($npt['changed_json'])? array_merge( $npt['changed_json'] , $val['changed_json'] ) : $val['changed_json'];
    }
    return empty($map)?null:$map;
  }

}
?>
