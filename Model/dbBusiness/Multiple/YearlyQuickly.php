<?php
namespace Model\Business\Multiple;

include_once __DIR__.'/_MultipleSets.php';

use \Exception;
use \Model\Business\YearPerformanceConfigCyclical;
use \Model\Business\Staff;
use \Model\Business\Department;
use \Model\Business\StaffTitleLv;
use \Model\Business\StaffPost;

/*
年績效 快速資源獲取
*/
class YearlyQuickly extends MultipleSets{

  const PROCESSING_A_PRE = 4;
  const PROCESSING_A_LAUNCH = 5;
  const PROCESSING_A_CLOSE = 6;
  const PROCESSING_A_FINISH = 7;
  const PROCESSING_A_FINISH_WELL = 8;
  const PROCESSING_A_HISTORY = 9;

  protected $staff;
  protected $team;
  protected $year;
  protected $staff_title;
  protected $staff_post;

  protected $config;
  protected $construct;
  protected $processing;

  public function __construct($year){
    $this->staff = new Staff();
    $this->year = $year;
    $this->team = new Department();
    $this->staff_title = new StaffTitleLv();
    $this->staff_post = new StaffPost();

    $this->config = new YearPerformanceConfigCyclical( $year );
    $this->data = $this->config->data;
    $this->processing = $this->data['processing'];
  }


  /**
   *  取得組織關係
   */
  public function getConstrust($reset=false){
    //已經啟用 不能重設
    if(!empty($reset) && $this->processing >= YearPerformanceConfigCyclical::PROCESSING_LAUNCHED){
      $this->error('Already Launched. Can Not Be Reset.');
    }

    $config_data = $this->data;

    $staff_data = $this->staff->select(array('id','staff_no', 'title_id', 'post_id', 'lv', 'name','name_en','rank','department_id','status_id','is_admin','first_day'),null);

    $team_data = $this->team->select();

    if( $reset==1 || empty($config_data['department_construct_json']) || count($config_data['department_construct_json'])==0){
      //初始當前架構
      foreach($team_data as &$tv){
        $tv['path_department'] = $this->team->getUpperIdArray( $tv['id'],false,true );
      }
      $this->construct = $this->config->initConstructWithStaffWithTeam( $staff_data, $team_data, $this->year );
    }else{
      $this->construct = $this->config->getFullConstruct();
    }

    return $this->mergeData();
  }

  //更新組織
  public function updateConstrust($data){

    if($this->config->checkProcessOver(YearPerformanceConfigCyclical::PROCESSING_RESET)){ return $this->error("Can Not Modify. Yearly Processing Over RESET."); }

    $staff = isset($data['staff']) ? $data['staff'] : null;
    $department = isset($data['department']) ? $data['department'] : null;
    $feedback = isset($data['feedback']) ? $data['feedback'] : null;
    $assessment = isset($data['assessment']) ? $data['assessment'] : null;

    if(!is_numeric($department) && !is_numeric($feedback) && !is_numeric($assessment)){ $this->error('Nothing Getting Change.'); }

    $this->construct = $this->config->updateStaffOnConstruct($staff, $department, $feedback, $assessment);

    $this->staff->read(array('id','staff_no', 'title_id', 'post_id', 'lv', 'name','name_en','rank','department_id','status_id'),null);

    return $this->mergeData();

  }

  // 附加主管路徑
  public function getConstrustAdjLeader(){
    $construct = $this->getConstrust();
    $team_map = $this->getDepartmentMap();
    $ceo_id = $this->data['ceo_staff_id'];
    foreach($construct as &$vv){
     $vv['path_lv_leader'] = array();
     $team_map[$vv['id']]['assessment_staff_ids'] = array();
     foreach($vv['staff'] as $vvst){
       if($vvst['_can_assessment']==1 && $vvst['id']!=$ceo_id){ $team_map[$vv['id']]['assessment_staff_ids'][]=$vvst['id']; }
     }
     $team_map[$vv['id']]['sub_assessment_staff_ids'] = [];
     foreach($vv['path_department'] as &$vvv){
       $team = $team_map[$vvv];
       // $vv['path_leader'][] = $team['manager_staff_id'];
       $vv['path_lv_leader'][$team['lv']] = $team['manager_staff_id'] > 0 ? $team['manager_staff_id'] : $team['supervisor_staff_id'];
       $vv['path_lv_department'][$team['lv']] = $vvv;
       if( is_array($team_map[$vv['id']]['assessment_staff_ids'])){ $team_map[$vvv]['sub_assessment_staff_ids'] = array_merge($team_map[$vvv]['sub_assessment_staff_ids'], $team_map[$vv['id']]['assessment_staff_ids']); }
     }
     $minLv = min($vv['lv'],2);
     $vv['division_leader_id']=$vv['path_lv_leader'][$minLv];
    }

    foreach($construct as &$vv){
      $vv['sub_assessment_staff_ids'] = $team_map[$vv['id']]['sub_assessment_staff_ids'];
    }

    return $construct;
  }

  public function getStaffMap(){
    $data = $this->getSD();
    return $data['staff'];
  }

  public function getDepartmentMap(){
    $data = $this->getSD();
    return $data['department'];
  }

  public function getAdminId(){
    $staff= $this->getStaffMap();
    $ids = array();
    foreach($staff as &$val){
      if($val['is_admin']==1){$ids[]=$val['id'];}
    }
    return $ids;
  }

  public function getAllPrivilegeId($path = array()){
    $staff= $this->getStaffMap();
    $ids = array();
    foreach($staff as &$val){
      if($val['is_admin']==1){$ids[]=$val['id'];}
    }
    $ids[] = $this->data['ceo_staff_id'];
    $ids[] = $this->data['constructor_staff_id'];
    $ids = array_merge($ids,$path);
    $ids = array_unique($ids);
    return $ids;
  }

  //取得有參加月績效的人員
  public function getAllAssessmentStaffId(){
    return $this->config->getAllStaffId(2);

  }
  //取得有參加部屬回饋的人員
  public function getAllFeedbackStaffId(){
    return $this->config->getAllStaffId(1);
  }

  //取得所有跟自己有關係的單位
  public function getAllMyDepartment($team_id=null){
    $team_map = $this->getDepartmentMap();
    $res = [];
    foreach($team_map as $id => $val){
      if( isset($val['path_department']) && in_array($team_id,$val['path_department'])){ $res[]=$val; }
    }
    // dd($res);
    return $res;
  }

  //cache 人跟部門資料
  private $staffAndDepartment;
  private function getSD(){
    // $time_1 = microtime(true);
    if(!isset($this->staffAndDepartment)){
      $this->staffAndDepartment = array();
      $data = $this->getConstrust();

      foreach($data as $dv){

        foreach($dv['staff'] as $sv){
          $this->staffAndDepartment['staff'][$sv['id']] = $sv;
        }
        unset($dv['staff']);
        $this->staffAndDepartment['department'][$dv['id']] = $dv;
      }
      //staff 0 是中心
      $this->staffAndDepartment['staff'][0] = ['id'=>0,'department_id'=>0,'name'=>'系統中心','name_en'=>'System Center','is_admin'=>1,'is_leader'=>0,'staff_no'=>'---'];
      $this->staffAndDepartment['department'][0] = ['id'=>0,'name'=>'系統中心','name_en'=>'System Center','lv'=>0,'unit_id'=>'---'];
      // dd($this->staffAndDepartment['department']);
    }
    // $time_2 = microtime(true);
    // var_dump($time_2-$time_1);
    return $this->staffAndDepartment;
  }

  //組合資料
  private function mergeData($data=null){
    // $time_1=microtime(true);
    $result = is_null($data) ? $this->construct : $data;

    $team_map = $this->team->cmap();
    $staff_map = $this->staff->cmap();
    $staff_title_map = $this->staff_title->cmap();
    $staff_post_map = $this->staff_post->cmap();

    foreach($result as &$v){
      $v['name'] = $team_map[$v['id']]['name'];
      $v['unit_id'] = $team_map[$v['id']]['unit_id'];
      $v['manager_staff_name'] = isset($staff_map[$v['manager_staff_id']]) ? $staff_map[$v['manager_staff_id']]['name'] : '';
      $v['manager_staff_name_en'] = isset($staff_map[$v['manager_staff_id']]) ? $staff_map[$v['manager_staff_id']]['name_en'] : '';
      foreach($v['staff'] as $i => $vv){
        $tmp = array(
          'title' => $staff_title_map[$vv['title_id']]['name'],
          'post' => $staff_post_map[$vv['post_id']]['name'],
          'department_id' => $v['id'],
          'is_leader' => $v['manager_staff_id']==$vv['id'] ? 1 : 0
        );
        $tmp = array_merge($vv,$tmp);
        $v['staff'][$i] = array_merge( $staff_map[$vv['id']] ,$tmp);
      }
    }
    // $time_2=microtime(true);
    return $result;
  }


  protected function get_construct(){
    return $this->construct;
  }

  //override
  public function update($a=null,$year=null){
    if($this->isYearlyAssessmentHistory()){$this->error('This Year Is Been History.');}
    if(is_null($year)){$year=$this->year;}
    $c = $this->config->update($a,array('year'=>$year));
    if($c>0){
      foreach($a as $k=>$v){
        $this->data[$k] = $v;
      }
    }
    return $c;
  }
  //override
  public function delete($a=null){
    if($this->isYearlyAssessmentHistory()){$this->error('This Year Is Been History.');}
    if(is_null($a)){$a=array('year'=>$this->year);}
    return $this->config->delete($a);
  }

  public function done(){
    return $this->update(['processing'=>YearPerformanceConfigCyclical::PROCESSING_FINISH_WELL]);
  }

  public function doneReport(){
    return $this->update(['processing'=>YearPerformanceConfigCyclical::PROCESSING_FINISH]);
  }

  public function keepAssessment(){
    if($this->data['processing']==YearPerformanceConfigCyclical::PROCESSING_COLLECT){return 0;}
    return $this->update(['processing'=>YearPerformanceConfigCyclical::PROCESSING_COLLECT]);
  }

  public function canProcessingYearlyAssessment(){
    return $this->data['processing']==YearPerformanceConfigCyclical::PROCESSING_COLLECT || $this->data['processing']==YearPerformanceConfigCyclical::PROCESSING_FINISH;
  }

  public function canSaveYearlyAssessment(){
    return $this->data['processing']==YearPerformanceConfigCyclical::PROCESSING_COLLECT || $this->data['processing']==YearPerformanceConfigCyclical::PROCESSING_VERIFY;
  }

  public function canProcessingYearlyDivision(){
    return $this->isYearlyAssessmentReportFinished();
  }

  public function isYearlyAssessmentStop(){
    return $this->data['processing']==YearPerformanceConfigCyclical::PROCESSING_STOP;
  }
  public function isYearlyAssessmentReportFinished(){
    return $this->data['processing']>=YearPerformanceConfigCyclical::PROCESSING_FINISH;
  }

  public function isYearlyAssessmentFinished(){
    return $this->data['processing']>=YearPerformanceConfigCyclical::PROCESSING_FINISH_WELL;
  }

  public function isYearlyAssessmentHistory(){
    return $this->data['processing']==YearPerformanceConfigCyclical::PROCESSING_HISTORY;
  }

}
?>
