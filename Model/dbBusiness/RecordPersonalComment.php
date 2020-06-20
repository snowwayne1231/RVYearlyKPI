<?php
namespace Model\Business;

include_once __DIR__.'/Common/DBPropertyObject.php';

class RecordPersonalComment extends DBPropertyObject{

  //實體表 :: 單表
  public $table_name = "rv_record_personal_comment";

  //欄位
  public $tables_column = Array(
    'id',
    'create_staff_id',
    'target_staff_id',
    'report_id',
    'report_type',
    'content',
    'status',
    'create_time'
  );

  public function __construct($db=null){
    parent::__construct($db);
  }

  //重開把 刪除 report 沒有得對應的 comment 組回對應的 report
  public function refresh($year, $month){
    //取得 沒有得對應的 comment
    $sql = "SELECT a.id , a.report_type
            FROM {table} AS a
            LEFT JOIN (SELECT id, comment_id FROM rv_monthly_report) AS b ON a.report_id = b.id AND a.report_type = 2
            LEFT JOIN (SELECT id, comment_id FROM rv_monthly_report_leader) AS c ON a.report_id = c.id AND a.report_type = 1
            WHERE b.id IS NULL AND c.id IS NULL ";
    $this->sql($sql);

    $id_str = '(0';
    foreach($this->data as $val){
      $id_str .= ',' . $val['id'];
    }
    $id_str .= ')';

    //組回對應的 report
    $sql = "UPDATE {table} AS a
            LEFT JOIN (SELECT id, staff_id FROM rv_monthly_report WHERE year=$year AND month=$month) AS b
              ON a.target_staff_id = b.staff_id AND a.report_type = 2
            LEFT JOIN (SELECT id, staff_id FROM rv_monthly_report_leader WHERE year=$year AND month=$month) AS c
              ON a.target_staff_id = c.staff_id AND a.report_type = 1
            SET a.report_id = if(b.id > 0, b.id, c.id)
            WHERE a.id IN $id_str AND (b.id > 0 OR c.id > 0)";
    $this->sql($sql);

    //把 組回去成功的 comment id 回寫到 report
    $new_data = $this->select(array('id','report_id','report_type') , "WHERE id IN $id_str AND status > 0");
    $update_data = array();
    foreach($new_data as $nv){
      $update_data[$nv['report_type']][$nv['report_id']][] = $nv['id'];
    }

    if( isset($update_data['1']) ){
      $table = 'rv_monthly_report_leader';
      foreach($update_data['1'] as $id => $lv){
        $comment_id = ','.join(',',$lv);
        $update_2 = "UPDATE $table SET comment_id = CONCAT(comment_id,'$comment_id') WHERE id = $id";
        $this->sql($update_2);
      }
    }

    if( isset($update_data['2']) ){
      $table = 'rv_monthly_report';
      foreach($update_data['2'] as $id => $gv){
        $comment_id = ','.join(',',$gv);
        $update_2 = "UPDATE $table SET comment_id = CONCAT(comment_id,'$comment_id') WHERE id = $id";
        $this->sql($update_2);
      }
    }

  }

}
?>
