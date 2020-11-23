<?php
namespace Model\Business;

include_once __DIR__.'/Common/DBPropertyObject.php';
// use Model\Business\Observer\YearPerformanceReportObserver;
class YearPerformanceReport extends DBPropertyObject{
  
  //考評階段
  const PROCESSING_LV_LOCK = -1 ; //-1 設為歷史狀態
  const PROCESSING_LV_STOP = 0 ; //0 設為停止再動，理論上不能再commit
  const PROCESSING_LV_CEO = 1 ; // 送到CEO審核的階段
  const PROCESSING_LV_DIRECTOR = 2 ; //1 送到部長審核的階段
  const PROCESSING_LV_MINISTER = 3 ; //2 送到處長審核的階段
  const PROCESSING_LV_LEADER = 4 ; //4 送到組長填考評的階段
  //管理者owner_staff_id
  const OWNER_STAFF_ID_ADMIN = 0 ;
  //默認level
  const DEFAULT_LEVEL = '-';
  //實體表 :: 單表
  public $table_name = 'rv_year_performance_report';
  
  //欄位
  public $tables_column = Array(
    'id',
    'year',
    'staff_id',
    'owner_staff_id',
    'own_department_id',
    'department_id',
    'division_id',
    'staff_post',
    'staff_title',
    'staff_title_id',
    'enable',
    'processing_lv', // -2 作廢 , -1 設為歷史狀態, 0 設為已核準 送到CEO審核的階段, 1 送到處長審核的階段, 2 送到部長審核的階段, 3 送到員工填考評的階段
    'path',
    'path_lv',
    'path_lv_leaders',
    'before_level',
    'monthly_average',
    'attendance_json',    // {'late':0,'early':0,'nocard':0,'leave':0,'paysick':0,'physiology':0,'sick':0,'absent':0}
    'assessment_json',
    'assessment_evaluating_json',
    'sign_json',
    'assessment_total',
    'assessment_total_division_change',
    'assessment_total_ceo_change',
    'level',
    'self_contribution',
    'self_improve',
    'upper_comment',
    'update_date',
    'reason'
  );
  
  public function __construct($db=null){
    parent::__construct($db);
    // parent::observe(YearPerformanceReportObserver::class);//  先不要用  沒把效能修好
  }
  
  public function getAttendanceCountWithRows($data, $special_data=[]){
    $conuts = array('late'=>0,'early'=>0,'nocard'=>0,'forgetcard'=>0,'leave'=>0,'paysick'=>0,'physiology'=>0,'sick'=>0,'absent'=>0);
    $enum_vt = new \Model\Enum\VacationType();
    foreach($data as $v){
      $rm = trim($v['remark']);
      $atv_vh = (float)(empty($v['vocation_hours'])?0:$v['vocation_hours']);
      if(empty($rm)){
        $conuts['late']+= (int)$v['late']>0?1:0;
        $conuts['early']+= (int)$v['early']>0?1:0;
      }else{
        $enum_vt->itName($rm);
        $hidden = $enum_vt->isHide();
        if($hidden){continue;}
        
        if($enum_vt->one){
          
          $c_key = $enum_vt->getKey();
          if(isset($conuts[$c_key])){$conuts[$c_key]+= $atv_vh;}
          
        }else{
          
          $vt_times = $enum_vt->getTime($atv_vh);
          if(empty($vt_times)){ $this->error( 'Wrong Date = '.$v['date'] ); }
          foreach($vt_times as $vv_vh){
            if(isset($conuts[$vv_vh['key']])){$conuts[$vv_vh['key']] += $vv_vh['time'];}
          }
          
        }
        
      }
    }
    foreach($special_data as $name=>$sd){
      $conuts[$name]=$sd;
    };
    return json_encode($conuts);
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
        if(isset($val['path'])){
          $val['path'] = json_decode($val['path'],true);
          $val['path_lv'] = json_decode($val['path_lv'],true);
          if (isset($val['path_lv_leaders'])) {
            $val['path_lv_leaders'] = json_decode($val['path_lv_leaders'],true);
          }
        }
        if(isset($val['attendance_json']))$val['attendance_json'] = json_decode($val['attendance_json'],true);
        if(isset($val['assessment_json']))$val['assessment_json'] = json_decode($val['assessment_json'],true);
        if(isset($val['assessment_evaluating_json']))$val['assessment_evaluating_json'] = json_decode($val['assessment_evaluating_json'],true);
        if(isset($val['upper_comment'])){
          // $val['upper_comment'] = json_decode($val['upper_comment'],true);
          
          $val['upper_comment'] = preg_replace('/[\n\r]/i','\r\n',$val['upper_comment']);
          // dd($val['upper_comment']);
          $val['upper_comment'] = json_decode($val['upper_comment'],true);
          // foreach($val['upper_comment'] as &$vvv){
            // $vvv['content'] = urldecode($vvv['content']);
          // }
        }
        if(isset($val['monthly_average']))$val['monthly_average'] = (float) number_format( (float)$val['monthly_average'],2);
        if(isset($val['sign_json']))$val['sign_json'] = json_decode($val['sign_json'],true);
      }
    }
    return $this;
  }

  /**
   * 傳入 年份，部門 id ，檢查該部門的 考評是否全部送出
   * @modifyDate 2017-10-06
   * @param      int                               $year 該年份
   * @param      int                               $division_id 部門id
   * @return     [type]                            [description]
   */
  public function getAllDepartmentAssessmentComplete($year, $division_id, $ceo_id = 1) {
    
    //取得該部門總共有多少張考評單
    // $whereSql = ' WHERE year =:year AND division_id =:division_id AND enable = 1 AND staff_id !='.$ceo_id;
    $whereSql = ' WHERE year =:year AND division_id =:division_id AND enable = 1 ';
    $sql = ' SELECT id, staff_is_leader, department_id, processing_lv FROM '.$this->table.$whereSql;
    $bindData =[
                   ':year' => [
                                 'value' => $year,
                                 'type' => \PDO::PARAM_INT,
                              ],  
                   ':division_id' => [
                                 'value' => $division_id,
                                 'type' => \PDO::PARAM_INT,
                              ],
                 ];
    $res = array();
    $res['total'] = $this->sql($sql, $bindData)->count;
    
    $res['done'] = 0;
    $res['leader'] = 0;
    foreach($this->data as $data){
      $done = $data['processing_lv']==self::PROCESSING_LV_STOP;
      if($data['staff_is_leader']==1 && $data['department_id']==$division_id){ 
        $res['leader'] = $done ? 1 : -1;
      }
      if($done){ $res['done']++; }
    }
    
    // dd($processing_count.' - '.$total_count);
    return $res;
    
    
  }
  
  //確認所有年績效都有 級距
  public function checkLevelHasByYear($year, $ceo_staff_id){
    $d = $this->select(['id','level'],['year'=>$year,'enable'=>1,'level'=>self::DEFAULT_LEVEL,'staff_id'=>'!='.$ceo_staff_id]);
    if(count($d)>0){
      $ids = array_column($d,'id');
      $str = implode($ids,',');
      $this->error("Has Some Report No Score Level [ $str ]"); 
    }
    return true;
  }
  
  /**
   *  取得未完成
   */
  public function getUnDo($staff_id){
    return $this->select(['id'],['owner_staff_id'=>$staff_id, 'processing_lv'=>'>'.self::PROCESSING_LV_STOP,'enable'=>1]);
  }

  public function getUnDoWithLeader($staff_data) {
    $res = [];
    $department_id = $staff_data['department_id'];
    $reports = $this->select(['id', 'staff_id', 'owner_staff_id', 'assessment_evaluating_json', 'processing_lv'], ['own_department_id'=> $department_id, 'processing_lv'=>'>'.self::PROCESSING_LV_STOP,'enable'=>1]);
    // dd($staff_data);
    // dd($reports);
    foreach ($reports as $report) {
      if ($report['staff_id'] == $report['owner_staff_id'] && $report['staff_id'] != $staff_data['id']) { continue; }
      $processing_lv = $report['processing_lv'];
      if (isset($report['assessment_evaluating_json'][$processing_lv])) {
        $aej = $report['assessment_evaluating_json'][$processing_lv];
        
        $idx_leader = array_search($staff_data['id'], $aej['leaders']);
        if ($idx_leader >= 0) {
          $is_commited = $aej['commited'][$idx_leader];
          if (!$is_commited) {
            $res[] = ['id'=>$report['id']];
          }
        }
      } else {
        $res[] = ['id'=>$report['id']];
      }
    }
    return $res;
  }

  /**
   *  確認主管ID是否存在這張單
   */
  public function isInLeadership($staff_id) {
    if (count($this->data) == 1) {
      if (isset($this->data[0]['path_lv_leaders'])) {
        $all_leaders = [];
        foreach ($this->data[0]['path_lv_leaders'] as $lv => $leaders) {
          $all_leaders = array_merge($all_leaders, $leaders);
        }
        return in_array($staff_id, $all_leaders);
      } else {
        return false;
      }
    } else {
      return false;
    }
  }
  
}
?>
