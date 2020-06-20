<?php
namespace Model\Business\Multiple;

include_once __DIR__.'/_MultipleSets.php';

use \Exception;
use \Model\Business\YearPerformanceConfigCyclical;
use \Model\Business\RecordYearPerformanceDivisions;
use \Model\Business\RecordYearPerformanceQuestions;
use \Model\Business\RecordYearPerformanceReport;
use \Model\Business\YearPerformanceDivisions;
use \Model\Business\YearPerformanceFeedback;
use \Model\Business\YearPerformanceReport;

// use \Model\Business\Multiple\YearlyQuickly;


/*
年績效 所有年度資料
*/
class YearlyFinally extends MultipleSets{

  const CODE_NONE = 0;
  const CODE_PREPARE = 1;
  const CODE_LAUNCH = 2;
  const CODE_PENDING = 3;
  const CODE_FINISHED = 4;
  const CODE_DONE = 5;

  protected $config;
  protected $processing;
  protected $year;

  public function __construct($year=null){
    if(empty($year)){return $this;}
    $this->year = $year;
    $this->config = new YearPerformanceConfigCyclical( $year );
    $this->data = $this->config->data;
    $this->processing = $this->data['processing'];
  }



  // 刪除所有當年資料
  public function deleteAllData(){

    if($this->config->isHistory()){$this->error('This Year Is Already Been History Data.');}

    try{
      $year = $this->year;
      $record_table = (new RecordYearPerformanceReport)->table_name;
      // dd($record_table);
      $report = new YearPerformanceReport;
      $report->sql("delete a, b from {table} as a left join $record_table as b on a.id = b.report_id where a.year = $year");


      $record_division_table = (new RecordYearPerformanceDivisions)->table_name;

      $divisions = new YearPerformanceDivisions;
      $divisions->sql("delete a, b from {table} as a left join $record_division_table as b on a.id = b.division_id where a.year = $year");


      $question = new RecordYearPerformanceQuestions;
      $question->delete(array("year"=>$year));

      $feedback = new YearPerformanceFeedback;
      $feedback->delete(array('year'=>$year));

    }catch(Exception $e){
      $this->error( $e->getMessage() );
    }

    $c = $this->config->delete(array('year'=>$year));

    return $this->config->getConfigWithYear($year);
  }

  // 年績效完成
  public function finishYearly(){
    if($this->config->isHistory()){$this->error('This Year Is Already Been History Data.');}
    if(!$this->config->isFinished()){$this->error('This Year Is Not Finished.');}
    $codi = ['year'=>$this->year];
    //最後檢查
    $report = new YearPerformanceReport;
    $report->checkLevelHasByYear($this->year, $this->config->data['ceo_staff_id']);
    $report->update(['owner_staff_id'=>0],$codi);

    $divisions = new YearPerformanceDivisions;
    $divisions->update(['owner_staff_id'=>0],$codi);

    $this->config->update(['processing'=>YearPerformanceConfigCyclical::PROCESSING_HISTORY],$codi);
    $this->data['processing'] = YearPerformanceConfigCyclical::PROCESSING_HISTORY;
    return $this->data;
  }

  /**
   *  取得組織圖
   */
  public function getYearlyOrganization($year, $self){
    // $time_1 = microtime(true);
    $res = [];

    $quick = new \Model\Business\Multiple\YearlyQuickly($year);
    //年設定
    $config = $quick->data;
    unset($config['department_construct_json']);
    unset($config['feedback_choice_ids']);
    unset($config['feedback_question_ids']);
    unset($config['assessment_ids']);
    unset($config['update_date']);
    unset($config['feedback_addition_day']);
    unset($config['assessment_addition_day']);
    // $time_2 = microtime(true);
    //組織結構

    $team_map = $quick->getDepartmentMap();

    

    if(count($team_map)==0){$this->error('No Data.');}

    $staff_map = $quick->getStaffMap();
    $staff_center = $staff_map[0];
    unset($team_map[0]);  unset($staff_map[0]);
    // $time_3 = microtime(true);
    // dd($team_map);
    //組合員工到 單位中
    
    if ($staff_map[$self['id']]['is_leader'] == 0 && $self['is_admin'] == 0) {
      // dd($staff_map);
      $res['unit_map'] = $team_map;
      return $res;
    }
    $staff_key = '_staff';
    foreach($staff_map as $sid=>$sv){
      unset($sv['title_id']);
      unset($sv['post_id']);
      unset($sv['status_id']);
      unset($sv['lv']);
      unset($sv['_can_feedback']);
      unset($sv['_can_assessment']);
      unset($sv['is_admin']);
      unset($sv['is_leader']);
      unset($sv['first_day']);
      $team_id = $sv['department_id'];
      // $key = ($team_map[$team_id]['manager_staff_id']==$sid)?'_manager':'_staff';

      $team_map[$team_id][$staff_key][]=$sv;
    }

    //整理員工排序
    foreach($team_map as &$tm_v){
      $tm_v['status_code'] = $tm_v['manager_staff_id']==0 ? self::CODE_NONE : self::CODE_PREPARE;
      if(empty($tm_v[$staff_key])){continue;}
      if($tm_v['manager_staff_id']==0){
        $tm_v['manager_staff_id'] = $tm_v['supervisor_staff_id'];
      }
      $tm_v['manager_staff_name'] = $staff_map[ $tm_v['manager_staff_id'] ]['name'];
      $tm_v['manager_staff_name_en'] = $staff_map[ $tm_v['manager_staff_id'] ]['name_en'];

      sortByValue($tm_v[$staff_key], ['rank|desc','staff_no|asc']);
    }

    $isLeader = $self['is_leader']==1;
    $isAdmin = $self['is_admin']==1;
    $isCEO = $self['id']==$config['ceo_staff_id'];

    array_push($self['_department_sub'],$self['department_id']);
    $all_team = $self['_department_sub'];

    // $time_4 = microtime(true);
    //依照流程組不同資料
    switch($config['processing']){
      case YearPerformanceConfigCyclical::PROCESSING_CHECKED: //產生問卷
      case YearPerformanceConfigCyclical::PROCESSING_LAUNCHED://開始問卷
      case YearPerformanceConfigCyclical::PROCESSING_CLOSE:   //關閉問卷
        //先加上計數
        foreach($team_map as &$tm_v){
          $tm_v['_feedback_total']=0;
          $tm_v['_feedback_finished']=0;
          // $tm_v['_feedback_not']=[];
        }
        //問卷資料
        $feedback = new YearPerformanceFeedback();  $feedback_key = '_feedback';  $submited = $feedback::STATUS_SUBMIT;
        $feedback_data = $feedback->read(['id','staff_id','status'],['year'=>$year])->cmap('staff_id',false,true);
        // dd($feedback_data);
        foreach($team_map as &$tm_v){

          if(empty($tm_v['_staff'])){continue;}

          foreach($tm_v['_staff'] as &$s_v){
            $sid = $s_v['id'];
            //問卷組合
            if(empty($feedback_data[$sid])){continue;}
            $s_v[$feedback_key] = $feedback_data[$sid];
            $auth = ($isAdmin || $isCEO) ? true : $isLeader && in_array($tm_v['id'], $all_team) && $sid!=$self['id'];
            $s_v['_authority'] = $auth;

            foreach($feedback_data[$sid] as $v){
              $done = $v['status']==$submited;
              $tm_v['_feedback_total']++;
              $tm_v['_feedback_finished']+=$done?1:0;
              /*foreach($tm_v['path_department'] as $upper_tm_id){
                $team_map[$upper_tm_id]['_feedback_total']++;
                $team_map[$upper_tm_id]['_feedback_finished']+=$done?1:0;
              }*/

            }

          }
          if($tm_v['_feedback_total']==$tm_v['_feedback_finished']){
            $sc = self::CODE_DONE;
          }else if($tm_v['_feedback_finished']>0){
            $sc = self::CODE_PENDING;
          }else{
            $sc = self::CODE_LAUNCH;
          }
          $tm_v['status_code'] = $sc;

        }
      break;
      case YearPerformanceConfigCyclical::PROCESSING_VERIFY:   //產生報表
      case YearPerformanceConfigCyclical::PROCESSING_COLLECT:  //收集報表
      case YearPerformanceConfigCyclical::PROCESSING_STOP:     //停止報表
      case YearPerformanceConfigCyclical::PROCESSING_FINISH:  //報表收集完成 / 開始加減分
      case YearPerformanceConfigCyclical::PROCESSING_FINISH_WELL:  //加減分完成 全部核准
      case YearPerformanceConfigCyclical::PROCESSING_HISTORY:  //進入歷史
        //先加上計數
        foreach($team_map as &$tm_v){
          $tm_v['_report_total']=0;
          $tm_v['_report_finished']=0;
          $tm_v['_report_this_total']=0;
          $tm_v['_report_this_finished']=0;
        }
        //報表
        $report = new YearPerformanceReport();  $report_key = '_report';  $submited = $report::PROCESSING_LV_STOP;
        $report_data = $report->read(['id','staff_id','owner_staff_id','division_id','processing_lv','level','enable'],['year'=>$year])->cmap('staff_id');

        $division = new YearPerformanceDivisions();
        $division_map = $division->read(['id','status','processing','division'],['year'=>$year])->cmap('division');;
        // dd($feedback_data);
        foreach($team_map as $tm_id => &$tm_v){
          if(empty($tm_v['_staff'])){continue;}

          $all_go = true;
          foreach($tm_v['_staff'] as &$s_v){
            $sid = $s_v['id'];
            //報表組合
            if(empty($report_data[$sid])){continue;}
            $data = $report_data[$sid];
            //擁有者名稱
            if(isset($staff_map[$data['owner_staff_id']])){
              $data['owner_staff_name'] =  $staff_map[$data['owner_staff_id']]['name'];
              $data['owner_staff_name_en'] =  $staff_map[$data['owner_staff_id']]['name_en'];
            }else{
              $data['owner_staff_name'] =  $staff_center['name'];
              $data['owner_staff_name_en'] =  $staff_center['name_en'];
            }

            $auth = ($isAdmin || $isCEO) ? true : $isLeader && in_array($tm_id, $all_team) && $sid!=$self['id'];
            $s_v['_authority'] = $auth;

            if(!$auth){
              $data['level']='?';
            }
            $s_v[$report_key] = $data;

            if($data['enable']==0){
              $s_v['_status_code'] = 0;
              continue;
            }

            $done = $data['processing_lv']==$submited;
            if($data['owner_staff_id']==$sid){ $all_go=false; $s_v['_status_code'] = 1; } //有正常單 還在手上
            else if($done){ $s_v['_status_code'] = 3; }
            else{ $s_v['_status_code'] = 2; }


            $tm_v['_report_this_total']++;
            $tm_v['_report_this_finished']+=$done?1:0;
            foreach($tm_v['path_department'] as $upper_tm_id){
              $team_map[$upper_tm_id]['_report_total']++;
              $team_map[$upper_tm_id]['_report_finished']+=$done?1:0;
            }

          }

          if($all_go){ $sc=($tm_v['_report_this_total']==0)?self::CODE_NONE : self::CODE_PENDING; }else{ $sc=self::CODE_LAUNCH; }
          $tm_v['status_code'] = $sc;

        }

        //部門狀態
        foreach($team_map as $tm_id => &$tm_v){
          if(empty($tm_v['_division'])){
            foreach($tm_v['path_department'] as $pdm_id){
              if(isset($division_map[$pdm_id])){$tm_v['_division']=$division_map[$pdm_id]; break;}
            }
          }

          if($tm_v['_division']['processing']==$division::PROCESSING_CEO_COMMIT){
            $tm_v['status_code'] = self::CODE_DONE;
          }else if($tm_v['_report_total']==$tm_v['_report_finished']){
            $tm_v['status_code'] = self::CODE_FINISHED;
          }

        }

      break;
    }//..switch

    // $time_5 = microtime(true);

    // $t_all = ($time_5 - $time_1);
    // $t_1 = (($time_2 - $time_1) / $t_all)*100;
    // $t_2 = (($time_3 - $time_2) / $t_all)*100;
    // $t_3 = (($time_4 - $time_3) / $t_all)*100;
    // $t_4 = (($time_5 - $time_4) / $t_all)*100;

    //最後檢查觀看權限
    if(!$isAdmin){
      // $my_lv = $self['_department_lv'];
      $my_team = $self['department_id'];
      $my_sub_team = $self['_department_sub'];
      foreach($team_map as $tm_id => &$tm_v){
        if(in_array($tm_v['id'], $my_sub_team) || $tm_v['id']==$my_team){continue;}
        //沒權限看
        $tm_v['status_code'] = self::CODE_NONE;
        if(isset($tm_v['_division'])){$tm_v['_division']['status'] = self::CODE_NONE;}
        $tm_v['_report_total']=0;
        $tm_v['_report_finished']=0;
        $tm_v['_feedback_finished']=0;
        $tm_v['_feedback_total']=0;
        // $tm_v['_division']['processing'] = 0;
        // $tm_v['_staff']=[];
      }
      // dd($team_map);
    }


    $res['config'] = $config;
    $res['unit_map'] = $team_map;

    return $res;
  }

}
?>
