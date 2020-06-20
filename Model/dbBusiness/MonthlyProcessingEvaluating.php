<?php
namespace Model\Business;

include_once __DIR__.'/Common/DBPropertyObject.php';

/**
 * 月考評進程
 */
class MonthlyProcessingEvaluating extends DBPropertyObject{

  //實體表 :: 單表
  public $table_name = "rv_monthly_processing_evaluating";

  //欄位
  public $tables_column = Array(
    'id',
    'year',
    'month',
    'staff_id',
    'department_id',
    'processing_id',
    'status_code',
    'first_submit_date'
  );

  //狀態 status_code
  const STATUS_CODE_ERROR = 0; //錯誤
  const STATUS_CODE_PERPARE = 1; //準備
  const STATUS_CODE_SUBMITED = 3; //送出中
  const STATUS_CODE_REJECT = 4; //退回

  //status_code
  public static $statusCode = array(
    self::STATUS_CODE_ERROR => '錯誤',
    self::STATUS_CODE_PERPARE => '準備',
    self::STATUS_CODE_SUBMITED => '送出中',
    self::STATUS_CODE_REJECT => '退回',
  );

  public $year;
  public $month;

  /**
   * 送出
   */
  public function submit() {
    if (isset($this->data)) {
      foreach ($this->data as $record) {
        $this->update(['status'=> 3], $record['id']);
      }
    }
    return $this;
  }

  /**
   * 是否送出
   * @param  array   $data 要用來判斷的資料
   * @return boolean 判斷結果
   */
  public function isSubmited($data=false){
    $data = ($data)?$data:$this->data[0];
    return $data['status_code'] == self::STATUS_CODE_SUBMITED;
  }

  /**
   * 取得指定 職員 還未完成的單
   * @param  integer $staff_id 職員ID
   * @return array   未完成的單
   */
  public function getUnDo($staff_id){
    return $this->select(['id'],['staff_id'=>$staff_id]);
  }

}
?>
