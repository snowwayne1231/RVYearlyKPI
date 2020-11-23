<?php
namespace Model\Business;

include_once __DIR__.'/Common/DBPropertyObject.php';
// use Model\Business\Observer\YearPerformanceDivisionsObserver;
class YearPerformanceDivisions extends DBPropertyObject{

  CONST STATUS_INIT = 0;
  CONST STATUS_DIVISION = 1;
  CONST STATUS_FINISHED = 5;
  //流程狀態
  CONST PROCESSING_INIT = 0;
  CONST PROCESSING_DIRECTOR_ADJUST = 1;
  CONST PROCESSING_DIRECTOR_COMMIT = 2;
  CONST PROCESSING_ARCHITE_WAIT = 3;
  CONST PROCESSING_CEO_ADJUST = 4;
  CONST PROCESSING_CEO_COMMIT = 5;
  //管理者owner_staff_id
  const OWNER_STAFF_ID_ADMIN = 0 ;
  //實體表 :: 單表
  public $table_name = "rv_year_performance_divisions";

  //欄位
  public $tables_column = Array(
    'id',
    'status', // 0 = 初始 , 1 = 部門底下收齊 , 5 = 全收齊
    'processing', // 0 = 初始 , 1 = 部長加減分 , 2 = 部長 commit , 3 = 架構發展部確認 , 4 = ceo加減分 , 5 = ceo確認
    'year',
    'division',
    'owner_staff_id',
    'update_date'
  );

  public function __construct($db=null){
    parent::__construct($db);
    // parent::observe(YearPerformanceDivisionsObserver::class);
  }

  public function checkAllStatusIsTheSameProcessing($year, $processing){
    switch ($processing) {
      case self::PROCESSING_INIT:
        return false;  //初始化不用發通知
      case self::PROCESSING_DIRECTOR_ADJUST: //只要一筆就發通知
        return true;
        break;
      case self::PROCESSING_DIRECTOR_COMMIT: //丟給架構管理員
        return true;
        break;
      case self::PROCESSING_CEO_COMMIT:
        break;
      case self::PROCESSING_ARCHITE_WAIT:
        return true;
      break;
      case self::PROCESSING_CEO_ADJUST:  //不需要，因為是CEO自已做調整
        return false;
        break;
      default:
        break;
    }
    $whereSql = ' WHERE year =:year ';
    $sql = ' SELECT count(1) as total_count FROM '.$this->table.$whereSql;
    $bindData =[
                   ':year' => [
                                 'value' => $year,
                                 'type' => \PDO::PARAM_INT,
                              ],
                 ]
               ;
    $count = $this->sql($sql, $bindData)->data;
    if ($count) {
      $count = $count[0]['total_count'];
    } else {
      $count = 0;
    }
    $sql = $sql.' AND processing =:processing ';
    $bindData =[
                   ':year' => [
                                 'value' => $year,
                                 'type' => \PDO::PARAM_INT,
                              ],
                   ':processing' => [
                                 'value' => $processing,
                                 'type' => \PDO::PARAM_INT,
                              ],
                 ]
               ;
    $processing_count = $this->sql($sql, $bindData)->data;
    if ($processing_count) {
      $processing_count = $processing_count[0]['total_count'];
    } else {
      $processing_count = 0;
    }
    if ($count > 0 ) {
      return ($count == $processing_count);
    } else {
      throw new \Exception("Not Found Any Record", 1); //正常流程不可能進得來才對
    }
  }

  public function isCanfixDivision($row=null){
    if(is_null($row)){$row=$this->data[0];}
    return $row['status'] >= self::STATUS_DIVISION && $row['processing'] <= self::PROCESSING_DIRECTOR_COMMIT;
  }

  public function isCanfixCEO($row=null){
    if(is_null($row)){$row=$this->data[0];}
    return $row['status']==self::STATUS_FINISHED && ( $row['processing'] >= self::PROCESSING_ARCHITE_WAIT && $row['processing'] <= self::PROCESSING_CEO_COMMIT);
  }

  public function getUnDo($staff_id, $isCEO=false){
    if($isCEO){
      $data = $this->select(['id','division','processing'],['owner_staff_id'=>$staff_id, 'processing'=>'<'.self::PROCESSING_CEO_COMMIT]);
      $b = true; $center=[];
      foreach($data as $v){
        if($v['division']==1){$center[]=$v;}
        if($v['processing']<3){$b=false;}
      }
      if ($b) {
        $data = $center;
      } else {
        $data = array_values(array_filter($data, function ($_) {
          return $_['division'] != 1;
        }));
      }
    }else{
      $data = $this->select(['id'],['owner_staff_id'=>$staff_id, 'processing'=>'<'.self::PROCESSING_CEO_COMMIT]);
    }
    return $data;
  }

}
?>
