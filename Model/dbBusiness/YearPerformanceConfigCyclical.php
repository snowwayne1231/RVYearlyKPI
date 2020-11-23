<?php
namespace Model\Business;

include_once __DIR__.'/Common/DBPropertyObject.php';

class YearPerformanceConfigCyclical extends DBPropertyObject{

  //Processing 值
  const PROCESSING_RESET = 0 ; //重設
  const PROCESSING_CHECKED = 1 ; ////檢查/產生 年部屬回饋問卷
  const PROCESSING_LAUNCHED = 2 ; //啟動 部屬回饋問卷 可以送交
  const PROCESSING_CLOSE = 3 ; //關閉年部屬回饋問卷
  const PROCESSING_VERIFY = 4 ; //檢查/產生 年考績
  const PROCESSING_COLLECT = 5 ; //收集報表
  const PROCESSING_STOP = 6 ; //停止收集
  const PROCESSING_FINISH = 7 ; //完成收集個人報表  開始部門部門流程
  const PROCESSING_FINISH_WELL = 8 ; //完成部門單流程
  const PROCESSING_HISTORY = 9 ; //變成歷史資料

  const FIRST_YEAR = 2017;
  const DEFAULT_CEO = 1;
  const DEFAULT_CONSTRUCTOR = 2;
  const CONSTRUCTOR_ID = 2;

  const STAFF_FILTER_NONE = 0;
  const STAFF_FILTER_CAN_FEEDBACK = 1;
  const STAFF_FILTER_CAN_ASSESSMENT = 2;

  //實體表 :: 單表
  public $table_name = "rv_year_performance_config_cyclical";

  //欄位
  public $tables_column = Array(
    'id',
    'year',
    'date_start',
    'date_end',
    'processing', //0 = 未啟動, 1 = 部屬回饋產生, 2 = 部屬回饋收集, 3 = 部屬回饋關閉, 4 = 產生年考績, 5 = 收集年考績, 6= 暫停收集, 7= 完成收集, 8=完成加減分/核准, 9= 關帳 進入歷史資料
    'department_construct_json',  //[ [ (0)id, (1)lv, (2)supervisor_staff_id, (3)manager_staff_id, (4)upper_id, (5)staff, (6)path_department, (7)path_staff ], [],..]
    'constructor_staff_id',
    'ceo_staff_id',
    'feedback_status',
    'feedback_addition_day',
    'feedback_date_start',
    'feedback_date_end',
    'feedback_choice_ids',
    'feedback_question_ids',
    'assessment_status',
    'assessment_addition_day',
    'assessment_date_start',
    'assessment_date_end',
    'assessment_ids',
    'update_date'
  );

  private $year;

  private $iConstruct;
  private $process;

  private $department_construct_json = array();

  public static $aisleColumn = array("year","processing","date_start","date_end","feedback_addition_day","feedback_date_start","feedback_date_end","assessment_addition_day","assessment_date_start","assessment_date_end","update_date", "department_construct_json", "feedback_question_ids", "feedback_choice_ids", "assessment_ids","ceo_staff_id","constructor_staff_id");

  public function __construct($y=null){

    parent::__construct();

    if($y){ $this->getConfigWithYear($y); }

  }

  public function getConfigWithYear($year){
    if($year < self::FIRST_YEAR){$this->error('Year Is Wrong.');}
    $this->year = $year;
    $codition = array("year"=>$year);
    $col = self::$aisleColumn;
    if(isset($this->process)){
      return $this->data;
    }
    $data = $this->select( $col, $codition );
    if( count($data)==0){
      $defaultStart = "$year-01-01";
      $defaultEnd = "$year-12-31";
      // $this->add( array("year"=>$year, "date_start"=>$defaultStart, "date_end"=>$defaultEnd, "feedback_date_start"=>$defaultEnd, "feedback_date_end"=>$defaultEnd, "assessment_date_start"=>$defaultEnd, "assessment_date_end"=>$defaultEnd ) );
      $this->add( array("year"=>$year, "date_start"=>$defaultStart, "date_end"=>$defaultEnd, "ceo_staff_id"=>self::DEFAULT_CEO, "constructor_staff_id"=>self::DEFAULT_CONSTRUCTOR  ) );
      sleep(0.1);
      $data = $this->select( $col, $codition );
    }
    $this->data = $data[0];
    $this->department_construct_json = $data[0]['department_construct_json'];
    $this->process = $data[0]['processing'];

    return $this->data;
  }



  /**
   *  初始化 年度組織
   */
  public function initConstructWithStaffWithTeam($staff,$team,$year=null){
    if(empty($year)){$year=$this->year;}
    $config_data = $this->getConfigWithYear($year);
    if(empty($this->end_time)){ //看是哪一季的
      $date_end = $config_data['date_end'];
      $date_spilt = preg_split('/\-|\//',$date_end);
      $month = $date_spilt[1];
      $date_end = $date_spilt[0].'-'.($month >9 ? 10: ($month>6?7:4) ).'-01';
      $this->end_time = strtotime($date_end);
    }
    $ceo_id = 0;
    $constructor_id = 0;
    $json = array();
    $team_map = array();
    foreach($staff as $v2){
      if( $v2['status_id'] ==4){ continue;} //離職 or 試用
      $team_map[$v2['department_id']][] = $this->getConByStaff( $v2 );
    }
    foreach($team as $val){
      if($val['enable']!=1){continue;} //部門已關閉
      $loc = array(
        $val['id'],
        $val['lv'],
        $val['supervisor_staff_id'],
        $val['manager_staff_id'],
        $val['upper_id']
      );
      $loc[5] = isset($team_map[$loc[0]]) ? $team_map[$loc[0]] : array();
      $loc[6] = isset($val['path_department']) ? $val['path_department'] : array();
      $json[]=$loc;
      //執行者跟架構者設定
      if($val['upper_id']==0){$ceo_id=$val['manager_staff_id'];}
      if($val['id']==self::CONSTRUCTOR_ID){$constructor_id=$val['manager_staff_id'];}
    }
    // LG($json);
    $this->update(array('department_construct_json'=>json_encode($json), 'ceo_staff_id'=>$ceo_id, 'constructor_staff_id'=>$constructor_id), array('year'=>$year));
    if( isset($this->data[0]) ){$this->data[0]['department_construct_json']=$json;}
    return $this->getFullConstruct($json);
  }
  //解析 員工資料
  private $conStaffCol = ['id','title_id','post_id','is_leader','_can_feedback','_can_assessment'];
  private $end_time;
  private function getConByStaff($sv){
    $ary = [];
    if(isset($sv['id'])){
      $end = $this->end_time;
      $first = strtotime($sv['first_day']);
      foreach($this->conStaffCol as $v){
        $ary[] = isset($sv[$v]) ? $sv[$v] : 0;
      }
      $ary[4] = ($sv['department_id']==1&&$sv['lv']==1)?0:1; // CEO以外 都可以填問卷
      $ary[5] = ($sv['rank'] >0 && $first<=$end && in_array($sv['status_id'], array(1,2,3,5)))? 1:0;  // _can_assessment 是否為正職
    }else if(is_array($sv)){
      foreach($sv as $i => $v){
        $key = $this->conStaffCol[$i];
        $ary[$key] = $v;
      }
    }
    return $ary;
  }
  //取得 年組織(組合)
  public function getFullConstruct($json=null){
    if( isset($this->iConstruct) && is_null($json) ){ return $this->iConstruct; }
    $result = array();
    if(empty($json)){
      $json = $this->department_construct_json;
    }
    foreach($json as &$val){
      $staffs = [];
      foreach($val[5] as $sv){
        $staffs[] = $this->getConByStaff( $sv );
      }
      array_unshift($val[6],$val[0]);
      $result[] = array(
        'id' => $val[0],
        'lv' => $val[1],
        'supervisor_staff_id' => $val[2],
        'manager_staff_id' => $val[3],
        'upper_id' => $val[4],
        'staff' => $staffs,
        'path_department' => $val[6],
      );
    }
    $this->iConstruct = $result;
    return $result;
  }

  //更新該年度 員工
  public function updateStaffOnConstruct($staff_id, $team_id=null, $can_fb=null, $can_as=null){
    $json = $this->department_construct_json;
    $stop = false;
    $tmp = null;
    $idx_can_fb = 4;
    $idx_can_as = 5;
    $idx_staffs = 5;
    $idx_staff_id = 0;
    $idx_is_leader = 3;

    foreach($json as &$v){
      foreach($v[$idx_staffs] as $i => &$sv){
        if($sv[$idx_staff_id]==$staff_id){  // find out
          if(is_numeric($can_fb)){ $sv[$idx_can_fb]= (int)$can_fb; }
          if(is_numeric($can_as)){ $sv[$idx_can_as]= (int)$can_as; }
          if(is_numeric($team_id) && $team_id>0 && $team_id != $v[0]){  // change department
            if($sv[$idx_is_leader]){ $this->error("The Leader Can Not Change Department."); }   //主管不能換部門
            $tmp = $sv;
            array_splice($v[$idx_staffs],$i,1);
          }
          $stop = true; break;
        }
      }
      if($stop){ break; }
    }
    if(!$stop){ $this->error('Not Find Staff Id.'); } //找不到員工 id

    if(!is_null($tmp)){   //有換部門
      foreach($json as &$v2){
        if($v2[0]==$team_id){   //find the new department
          array_push($v2[5], $tmp);
          $tmp = null;
        }
      }
    }

    if(!is_null($tmp)){ $this->error('Not Find Department Id.'); } //沒找到對應的 team_id
    $this->update(array('department_construct_json'=>json_encode($json)), array('year'=>$this->year));
    $this->department_construct_json = $json;
    return $this->getFullConstruct($json);
  }

  //
  public function getAllStaffId($filter=-1){
    $json = $this->department_construct_json;
    $res = array();

    switch($filter){
      case self::STAFF_FILTER_CAN_ASSESSMENT:
        $filter_can_assessment=true;
        $filter_can_feedback=false;
      break;
      case self::STAFF_FILTER_CAN_FEEDBACK:
        $filter_can_assessment=false;
        $filter_can_feedback=true;
      break;
      default:
        $filter_can_assessment=false;
        $filter_can_feedback=false;
    }

    foreach($json as $v){
      foreach($v[5] as $vv){
        if($filter_can_feedback && $vv[3]!=1){ continue; }
        if($filter_can_assessment && $vv[4]!=1){ continue; }
        array_push($res,$vv[0]);  //[5]員工 [0] id
      }
    }
    return $res;
  }
  //
  public function getAllLeader() {
    $json = $this->department_construct_json;
    $res = array();

    foreach($json as $v){
      // $res[] =$v[2];
      $res[] =$v[3];
    }
    $res = array_unique($res);
    return $res;
  }


  /**
   * 取得當年員工自己的主管
   * @method     getStaffSelfLeader
   * @author Alex Lin <alex.lin@rv88.tw>
   * @version    [version]
   * @modifyDate 2017-08-21T16:33:32+0800
   * @param      [type]                   $staff_id [description]
   * @return     [type]                             [description]
   */
  public function getStaffSelfLeader($staff_id) {
    if(isset($this->data[0])){
      $json = $this->data[0]['department_construct_json'];
    }else{
      $json = array();
    }
    $res = array();
    foreach($json as $v){
      if (in_array($staff_id, $v[5])) {
        $res[] = $v[2];
        $res[] = $v[3];
      }
    }
    $res = array_unique($res);
    return $res;
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
      if(isset($val['department_construct_json']))$val['department_construct_json'] = json_decode($val['department_construct_json'],true);
    }
    return $this;
  }

  public function checkProcessOver($i){
    return $this->process > $i;
  }

  public function isOverFeedback(){
    return $this->process >= self::PROCESSING_CLOSE;
  }

  public function isFinished(){
    return $this->process >= self::PROCESSING_FINISH_WELL;
  }

  public function isHistory(){
    return $this->process >= self::PROCESSING_HISTORY;
  }

}
?>
