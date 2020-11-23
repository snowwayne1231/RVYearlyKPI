<?php
namespace Model\Business\Multiple;

include_once __DIR__.'/_MultipleSets.php';
include_once __DIR__.'/../YearPerformanceConfigCyclical.php';
include_once __DIR__.'/../YearPerformanceFeedback.php';
include_once __DIR__.'/../YearPerformanceFeedbackMultipleChoice.php';
include_once __DIR__.'/../YearPerformanceFeedbackQuestions.php';
include_once __DIR__.'/../RecordYearPerformanceQuestions.php';
include_once __DIR__.'/../Staff.php';
include_once __DIR__.'/../Department.php';
// include_once __DIR__.'/../Attendance.php';

use \Exception;
use Model\Business\YearPerformanceReport;
use Model\Business\YearPerformanceConfigCyclical;
use Model\Business\YearPerformanceFeedback;
use Model\Business\StaffTitleLv;
use Model\Business\Multiple\YearlyQuickly;
/*
所有年度部屬回饋相關
*/
class YearlyFeedback extends MultipleSets{

  protected $staff;

  protected $team;

  protected $config;
  protected $feedback;
  protected $feedback_data;
  public $choice;
  public $question;
  protected $record_question;
  protected $attendance;

  private $year;

  //status
  const STATUS_NOT_ENABLE = -1;
  const STATUS_NONE = 0;
  const STATUS_FINISH = 1;

  public function __construct($year=null){
    if(is_null($year)){
      $this->config = new YearPerformanceConfigCyclical();
    }else{
      $this->year = $year;
      $this->config = new YearPerformanceConfigCyclical($year);
    }
    $this->staff = new \Model\Business\Staff();
    $this->team = new \Model\Business\Department();

    $this->feedback = new \Model\Business\YearPerformanceFeedback();
    $this->choice = new \Model\Business\YearPerformanceFeedbackMultipleChoice();
    $this->question = new \Model\Business\YearPerformanceFeedbackQuestions();
    // $this->attendance = new \Model\Business\Attendance();
    $this->record_question = new \Model\Business\RecordYearPerformanceQuestions();
  }

  /**
   * 檢查年度回饋，如果沒有的話，就新增
   */
  public function checkFeedback($reset=false,$year=null){
    if(is_null($year)){$year = $this->year;}

    $config = $this->refreshFeedbackChoice($reset);
    // LG($config);
    //只取得 需要部屬回饋的員工
    $allStaffIds = $this->config->getAllStaffId( YearPerformanceConfigCyclical::STAFF_FILTER_CAN_FEEDBACK );
    if( count($allStaffIds)==0 ){ return $this->error('Construt Not Found Any Staff.'); }

    // $staff_map = $this->staff->read( array('id','staff_no','name','name_en','lv','status_id','first_day','last_day','department_id','is_leader', 'title', 'title_id') , null )->map();

    $quick = new YearlyQuickly($year);

    $construct = $this->config->getFullConstruct();

    $staff_map = $quick->getStaffMap();

    $team_map = $quick->getDepartmentMap();;


    $feedback_map = $this->feedback->read( array('year'=>$year) )->map('staff_id');

    // dd($construct);
    foreach($construct as $v){
      // $team_id = $sval['id'];
      foreach($v['staff'] as $sv){

        $sid = $sv['id'];
        //不能做問卷
        if( $sv['_can_feedback']!=1 ){ continue; }
        //該員工有問卷了 不用產生
        if( isset( $feedback_map[ $sid ]) ){continue;}


        $staff = $staff_map[ $sid ];
        $all_leader_ids = [];

        if( $staff['is_leader']==1){
          //主管要看上層有沒無單位 來決定要不要產生 部屬回饋
          foreach($v['path_department'] as $upid){
            if($upid==$v['id']){ continue;}//路徑是自己
            $upper_team = $team_map[$upid];
            if($upper_team['manager_staff_id']==0){ continue;}//沒有主管
            $all_leader_ids[] = $upper_team['manager_staff_id'];
            if (isset($upper_team['_other_leaders'])) {
              $all_leader_ids = array_merge($all_leader_ids, $upper_team['_other_leaders']);
            }
          }
        }else{
          //員工只會有自己的主管
          $myteam = $team_map[ $staff['department_id'] ];
          if ($myteam['manager_staff_id'] > 0) {
            $target_team = $myteam;
          } else {
            foreach($v['path_department'] as $upid){
              if($upid==$v['id']){ continue;}
              $upper_team = $team_map[$upid];
              if($upper_team['manager_staff_id']==0){ continue;}
              $target_team = $upper_team;
              break;
            }
          }

          $all_leader_ids[] = $target_team['manager_staff_id'];
          $all_leader_ids = array_merge($all_leader_ids, $target_team['_other_leaders']);
        }

        foreach ($all_leader_ids as $target_staff_id) {
          $target_staff = $staff_map[ $target_staff_id ];
          $record = array(
            "year"=>$year,
            "staff_id"=>$sid,
            "staff_title_id"=> $staff['title_id'],
            "staff_title"=> $staff['title'],
            "department_id" => $staff['department_id'],
            "target_staff_id" => $target_staff_id,
            "target_staff_title_id"=> $target_staff['title_id'],
            "target_staff_title"=> $target_staff['title'],
            "multiple_choice_json" => $config['defaultChoiceJSON'],
            "multiple_total" => $config['defaultChoiceJSON_total']
          );
          $this->feedback->addStorage($record);
        }

      }

      // dd($construct);
    }
    $count = $this->feedback->addRelease();
    return $count;
  }

  public function getFeedbackWithStaff($id,$year=null){
    if( is_null($year) ){$year=$this->year;}
    $result = array('feedback'=>[],'choice'=>[],'question'=>[]);
    $staff_table = $this->staff->table_name;
    $team_table = $this->team->table_name;
    //檢查年考評狀態，在關閉之前才能評
    $process = $this->config->data['processing'];
    if($process >=\Model\Business\YearPerformanceConfigCyclical::PROCESSING_CLOSE){ return $result; }

    $sert1 = $id===0 ? "a.status >= ".self::STATUS_NONE." " : " a.status = ".self::STATUS_NONE." and a.staff_id = $id ";
    $sert2 = isset($year) ? " and a.year = $year " : "";

    $feedback = $this->feedback->sql( "select a.id, a.year, a.target_staff_id, b.name as target_staff_name, b.name_en as target_staff_name_en,b.post as target_staff_post, d.unit_id as target_unit_id ,d.name as target_unit_name, a.department_id, c.name as department_name, c.unit_id, a.multiple_choice_json
    from {table} as a
    left join $staff_table as b on a.target_staff_id = b.id
    left join $team_table as c on a.department_id = c.id
    left join $team_table as d on b.department_id = d.id
    where $sert1 $sert2 order by b.rank asc" )->data;

    // LG($feedback);
    $this->feedback_data = $feedback;
    $result['feedback'] = $feedback;

    if( count($feedback)>0 ){

      //假想只會有一個年份
      $year = $feedback[0]['year'];
      $config = $this->config->select(array('processing','feedback_status','feedback_choice_ids','feedback_question_ids'), array('year'=>$year))[0];

    }else if(isset($year)){
      //有年 沒資料
      $config = $this->config->select(array('processing','feedback_status','feedback_choice_ids','feedback_question_ids'), array('year'=>$year));
      if(count($config)==0){ return array('error'=>'Not Exist Config.'); }else{
        $config = $config[0];
      }

    }else{
      //取最大的一年
      $config = $this->config->select(array('processing','feedback_status','feedback_choice_ids','feedback_question_ids'), array('processing'=> '>'.\Model\Business\YearPerformanceConfigCyclical::PROCESSING_CHECKED), array('year'=>'DESC') );
      if(count($config)==0){ return array('error'=>'Not Exist Config.'); }else{
        $config = $config[0];
      }
    }
    //選擇題
    $choice = $this->choice->select( array('id','title','description','options_json','score'), array('id'=>'in('.$config['feedback_choice_ids'].')','enable'=>1), array('sort'=>'ASC') );
    $result['choice'] = $choice;
    //問答題
    $question = $this->question->read( array('id','mode','title','description'), array('id'=>'in('.$config['feedback_question_ids'].')','enable'=>1), array('sort'=>'ASC') )->map('mode',false,true);
    $result['question'] = $question;

    return $result;
  }

  public function getFeedbackStatusWithYear($year){
    $staff_map = $this->staff->read(array('id','name','name_en','staff_no','rank'),null)->map();
    $feedback = $this->feedback->select(array('id','year','staff_id','department_id','status','target_staff_id','multiple_total','multiple_score','update_date'),array('year'=>$year));
    foreach($feedback as &$v){
      $s = $staff_map[ $v['staff_id'] ];
      $v['staff_name'] = $s['name'];
      $v['staff_name_en'] = $s['name_en'];
      $v['staff_no'] = $s['staff_no'];
      $ts = $staff_map[ $v['target_staff_id'] ];

      $v['target_staff_name'] = $ts['name'];
      $v['target_staff_name_en'] = $ts['name_en'];
      $v['target_staff_no'] = $ts['staff_no'];
    }
    return $feedback;
  }

  public function saveFeedbackMultipleChoice($id, $staff_id, $choice_json=null, $question_json=null, $pass=false){
    $result = array();
    $feedback = $this->feedback->select($id);

    if(count($feedback)==0){
      $result['error'] = 'Not Found This Feedback.';
    }else if($feedback[0]['status'] > self::STATUS_NONE){
      $result['error'] = 'This Feedback Already Done.';
    }else{
      $this->feedback_data = $feedback;
      $feedback = $feedback[0];
      $year = $feedback['year'];

      $this->config->read(array('year'=>$year));
      // $quick = new YearlyQuickly($year);
      // $staff_map = $quick->getStaffMap();

      $update = array();
      //該單是這位選手的
      if($feedback['staff_id']==$staff_id){

        //需要更新選擇題

        if( isset($choice_json) && is_array($choice_json) ){
          $choices = $this->getChoiceWithYear( $feedback['year'] );
          if( isset($choices['error']) ){return $choices;}

          $new_choice_json = $feedback['multiple_choice_json'];
          $multiple_score = 0;
          foreach($choices as $k => &$v){
            $cid = $v['id'];
            if( !isset($choice_json[$cid]) ){ continue;}
            $ans = (int)$choice_json[ $cid ];

            if( empty($v['options_json'][$ans]) ) { continue; }
              // if (empty($question_json)) {
                // continue;
              // }
              // $result['error']='Wrong Answer Format.';return $result;
            // }
            $loc = &$v['options_json'][$ans];
            $percent = (int)$loc['percent'];
            $score = round($v['score'] * $percent / 100);
            $new_choice_json[$k] = array(
              'id' => $cid,
              'ans' => $ans,
              'score' => $score
            );
          }

          $update['multiple_choice_json'] = $new_choice_json;
          foreach($new_choice_json as &$newVal){
            $multiple_score+=$newVal['score'];
          }
          $update['multiple_score'] = $multiple_score;
        }
        // 有問答題一定是送審
        if( !empty($question_json) && is_array($question_json) ){
          //檢查進度是否可以送審
          $config_map = $this->getConfigMap($year);
          $processing = $config_map[$year]['processing'];
          if($processing < \Model\Business\YearPerformanceConfigCyclical::PROCESSING_LAUNCHED){
            $this->error('The Processing Not Arrived Yet.');
          }
          //檢查選擇題是否都選了
          $mcj = isset($new_choice_json) ? $new_choice_json : $feedback['multiple_choice_json'];
          foreach($mcj as &$val){
            if($val['ans']<0){ $result['error'] = 'Multiple Choice Is Not Finished.';return $result;}
          }

          $question_map = $this->getQuestionWithYear( $feedback['year'] );
          foreach($question_json as $qkey => &$qval){
            if( !isset($question_map[$qkey]) ){ $this->error('Questions Id Is Wrong.'); }
            if( strlen2($qval)>255 ){$this->error("The Questions Id $qkey Is Too Many Content.");}
            if( !empty($qval) ){ $this->record_question->addStorage( array('question_id'=>$qkey,'year'=>$feedback['year'],'target_staff_id'=>$feedback['target_staff_id'],'content'=>$qval) ); }
          }

          $update['status'] = self::STATUS_FINISH;

          //Email
          // $staff_data = $this->staff->read(['id','name','name_en','email'],['id'=>[$feedback['staff_id'], $feedback['target_staff_id']]])->map();
          $staff_data = $this->staff->mergeDepartmentForShow([$feedback['staff_id'], $feedback['target_staff_id']],['email'])->cmap();
          // dd($staff_data);
          $email_data = $staff_data[ $feedback['staff_id'] ];
          $email_data['year'] = $year;
          $email_data['target_name'] = $staff_data[ $feedback['target_staff_id'] ]['name'];
          $email_data['target_name_en'] = $staff_data[ $feedback['target_staff_id'] ]['name_en'];

          $this->sendEmailByData('yearly_feedback_commit', $email_data, $email_data['email'] , true);

        }

        // 更新資料
        if( count($update)==0 ){ $result['error'] = 'Can Not Update.';return $result; }

        $this->record_question->addRelease();

        $feedback_count = $this->feedback->update( $update ,$id);

        $feedback['multiple_choice_json'] = $new_choice_json;
        $feedback['multiple_score'] = $multiple_score;

        $result = $feedback;

      } else {
        $result['error'] = 'You Are Not Owner.';
      }
    }
    if( isset($result['error']) ){ $this->error( $result['error'] ); }
    return $result;
  }

  public function deleteFeedback($year=null){
    if( is_null($year) ){$year = $this->year;}
    $config_data = $this->config->select(array('processing'),array('year'=>$year));
    if(count($config_data)==0){ $this->error('Config Not Found.'); }
    if($config_data[0]['processing']>\Model\Business\YearPerformanceConfigCyclical::PROCESSING_CLOSE){ $this->error('Checkout Year Config Is Over Feedback.'); }
    $this->feedback->delete(array('year'=>$year));
    $this->record_question->delete(array("year"=>$year));
    $count = $this->config->update(array('processing'=> \Model\Business\YearPerformanceConfigCyclical::PROCESSING_RESET),array('year'=>$year));
    return $count;
  }

  public function launchFeedback($year){
    $config_data = $this->config->getConfigWithYear($year);
    // dd($config_data);
    if($config_data['processing']>YearPerformanceConfigCyclical::PROCESSING_VERIFY){ $this->error('Can Not Be Launched.'); }
    $add = $config_data['feedback_addition_day'];
    $launch_date = date('Y-m-d');
    $ld_time = strtotime($launch_date);
    $end_time = strtotime("+".$add." day", $ld_time);
    $end_date = date('Y-m-d',$end_time);

    $update_data = [];
    $update_data['processing']=YearPerformanceConfigCyclical::PROCESSING_LAUNCHED;
    $update_data['feedback_date_start']=$launch_date;
    $update_data['feedback_date_end']=$end_date;

    // 年設定
    $count = $this->config->update($update_data,array('year'=>$year));
    // 部屬問卷狀態
    $this->feedback->update(array('status'=> self::STATUS_NONE),array('year'=>$year,'status'=> self::STATUS_NOT_ENABLE));
    // Email
    $this->sendEmailByData('yearly_feedback_launch', ['year'=>$year,'day_end'=>$end_date], [] , true);


    return $count;
  }

  public function close($year){
    $config = $this->config->select(array('processing','year'),array('year'=>$year));
    if(count($config)>0){
      if($config[0]['processing']<\Model\Business\YearPerformanceConfigCyclical::PROCESSING_CLOSE){ $this->config->update(array('processing'=>\Model\Business\YearPerformanceConfigCyclical::PROCESSING_CLOSE,'feedback_status'=>1),array('year'=>$year)); }
      $this->feedback->update(array('status'=> self::STATUS_NOT_ENABLE),array('year'=>$year,'status'=> self::STATUS_NONE));

      // Email
      $this->sendEmailByData('yearly_feedback_close', ['year'=>$year], [] , true);

      return $this->config->select(array('processing','year'),array('year'=>$year))[0];
    }else{
      return array('error'=>'Not Exist This Year.');
    }
  }

  public function collectQuestion($set){

    $result = array();
    $config = $this->config->data;
    $year = $config['year'];

    if($config['processing'] != YearPerformanceConfigCyclical::PROCESSING_LAUNCHED){ $this->error('The Processing Not Arrived Yet.'); }

    $staff_ids = $this->config->getAllStaffId();
    //如果員工出錯 跳出
    if(count($staff_ids)==0){ $this->error('Construt Not Found Any Staff.'); }

    //取得建議類問題id
    // $serach_question_id = array();
    $serach_qids = $this->question->read(array('id','mode'),"where enable=1 and (mode='others' or  mode='company')")->getTiny();
    $serach_qid = join(',',$serach_qids);
    $all_question_map = $this->record_question->read(array('target_staff_id','content'), array('year'=>$year,'question_id'=>"in($serach_qid)"))->map('target_staff_id',false,true);
    // dd($all_question_map);
    foreach($all_question_map as $tsi => &$cntv){
      $new_cntv = array();
      foreach($cntv as $inner_cntv){
        if(!empty($inner_cntv['content'])){
          $new_cntv[$inner_cntv['content']] = 1;
        }
      }
      $cntv = $new_cntv;
    }

    $fb_map = $this->question->map();

    // LG($all_question_map);
    if(isset($set['other']) && is_array($set['other'])){

      foreach($set['other'] as $sid => $oq){
        if(!in_array($sid,$staff_ids)){$this->error("Not Exist Staff Id [ $sid ].");}
        if(!is_array($oq)){$this->error("Data Error With Other Questions.");}

        foreach($oq as $qid => $cnt){
          if(empty($cnt)){continue;}  //沒內容
          if( strlen2($cnt)>255 ){$this->error("The Questions Id $qid Is Too Many Content.");}   //超出字數
          if(!in_array($qid,$serach_qids)){$this->error("Not Exist Question Id [ $qid ].");}
          if($fb_map[ $qid ]['mode']!='others'){$this->error("Not Match Question Mode [ $qid ].");}

            //檢查是否同樣的內容
          if( isset($all_question_map[$sid]) ){
            $match_cnt = str_replace("'",'"',$cnt);
            if( isset($all_question_map[$sid][$match_cnt]) ){
              continue;
            }
          }

          $this->record_question->addStorage(array(
            'question_id'=>$qid,
            'year'=>$year,
            'from_type'=>2,
            'target_staff_id'=>$sid,
            'content'=>$cnt
          ));
        }
      }
    }
    if(isset($set['company']) && is_array($set['company']) ){
      foreach($set['company'] as $qid => $cnt){
        if(empty($cnt)){continue;}  //沒內容
        if( strlen2($cnt)>255 ){$this->error("The Questions Id $qid Is Too Many Content.");}   //超出字數
        if(!in_array($qid,$serach_qids)){$this->error("Not Exist Question Id [ $qid ].");}
        if($fb_map[ $qid ]['mode']!='company'){$this->error("Not Match Question Mode [ $qid ].");}

        //檢查是否同樣的內容
        if( isset($all_question_map[0]) && isset($all_question_map[0][$cnt]) ){
          continue;
        }

        $this->record_question->addStorage(array(
          'question_id'=>$qid,
          'year'=>$year,
          'from_type'=>4,
          'target_staff_id'=>0, //staff_id = 0 = 公司
          'content'=>$cnt
        ));
      }
    }
    return $this->record_question->addRelease();
  }

  //重新整理 部屬選擇題 問題
  private function refreshFeedbackChoice($reset=false){
    $condiY = array('year'=>$this->year);
    $config_data = $this->config->select($condiY)[0];

    //進度
    if($config_data['processing']< YearPerformanceConfigCyclical::PROCESSING_CHECKED  || $reset){
      $config_data['processing']= YearPerformanceConfigCyclical::PROCESSING_CHECKED;
    }
    if($config_data['processing']<= YearPerformanceConfigCyclical::PROCESSING_CHECKED ){
      //選取當年當下的選擇題
      $choice = $this->choice->select( array('id','score'), array('enable'=>1), array('sort'=>'asc') );
      $choice_ids = array();
      $choice_ary = array();
      $choice_total = 0;
      foreach($choice as $val){
        $choice_ids[]=$val['id'];
        $choice_ary[] = array('id'=>$val['id'],'ans'=>-1,'score'=>0);
        $choice_total+=(int)$val['score'];
      }
      $config_data['feedback_choice_ids'] = join(',',$choice_ids);

      //選取當下的問答題
      $question = $this->question->select( array('id'), array('enable'=>1), array('sort'=>'asc') );
      $question_ids = array();
      foreach($question as $qv){
        $question_ids[]=$qv['id'];
      }
      $config_data['feedback_question_ids'] = join(',',$question_ids);

      //feedback date
      $config_data['feedback_date_start'] = date('Y-m-d');
      $config_data['feedback_date_end'] = date('Y-m-d',strtotime( $config_data['feedback_date_start'].' +'.$config_data['feedback_addition_day'].' day' ));

      //update
      $this->config->update( $config_data, $condiY );

      $config_data['defaultChoiceJSON'] = json_encode($choice_ary);
      $config_data['defaultChoiceJSON_total'] = $choice_total;
    }else{
      // $config_data = array('error'=>'This Year Feedback Already Done.');
      $this->error('This Year Feedback Already Done.');
    }
    // $config_data['construct'] = $this->config->getFullConstruct();
    return $config_data;
  }


  private function getConfigMap($year){
    return $this->config->map('year');
  }

  private function getChoiceWithYear($year){
    $config_map = $this->getConfigMap($year);
    if( isset($config_map[$year]) ){
      $processing = $config_map[$year]['processing'];
      if($processing >= \Model\Business\YearPerformanceConfigCyclical::PROCESSING_CHECKED){ //開始部屬回饋
        $ids = $config_map[$year]['feedback_choice_ids'];
        $bob_ids = bomb($ids);
        $cMap = $this->choice->read(array('id','options_json','score'),array('id'=>"in($ids)"))->map();
        //為了順序
        $result=array();
        foreach($bob_ids as $vid){
          array_push($result, $cMap[$vid] );
        }
      }else{
        $result = array('error'=>'The Processing Not Arrived Yet.');
      }

    }else{
      $result = array('error'=>'Not Found This Year.');
    }


    return $result;
  }

  private function getQuestionWithYear($year){
    $config_map = $this->config->map('year');
    $ids = $config_map[$year]['feedback_question_ids'];
    $bob_ids = bomb($ids);
    $result = array();
    foreach($bob_ids as &$id){
      $result[$id]=true;
    }
    return $result;
  }

  protected function get_team(){
    return $this->team;
  }
  protected function get_staff(){
    return $this->staff;
  }
  protected function get_feedback(){
    return $this->feedback;
  }
  protected function get_config(){
    return $this->config;
  }

  /**
   * 取得 取得該年度部屬回饋問卷的統計
   * @modifyDate 2017-10-17
   * @param      [type]                      $year        [description]
   * @param      boolean                     $with_feedback [description]
   * @param      boolean                     $with_submit [description]
   * @return     [type]                                   [description]
   */
  public function getYearlyFeedBackStatistics($year) {

    // $staffTitleLv =  new StaffTitleLv();
    // $title_lv = $staffTitleLv->read( ['id', 'name'],[])->map('id');

    $res = [];
    $config = $this->config;
    // $config_data = $config->getConfigWithYear($year);

    $fb_data = $this->feedback->select(
      array('staff_id', 'target_staff_id', 'status'),
      array('year'=>$year)
    );
    // $fb_map = $this->feedback->map('staff_id',false,true);

    $res['total'] = count($fb_data);
    $res['received'] =0;
    // $res['detail'] =$fb_map;
    foreach($fb_data as $id => $v){
      $res['received'] += $v['status']==1?1:0;
    }

    return $res;
  }

  /**
   * 部屬回饋問卷管理 - 取得該年度的所有的部屬回饋問卷
   * @modifyDate 2017-10-18
   * @param      [type]                   $year [description]
   * @return     [type]                         [description]
   */
  public function getYearlyFeedbackList($year, $feedback_id=null) {
    $where = [];  $where['year'] = $year;
    if(!empty($feedback_id)){$where['id']=$feedback_id;}
    $feedback_list = $this->feedback->read(['id','staff_id', 'target_staff_id', 'department_id', 'multiple_choice_json', 'multiple_score'], $where)->check('Not Found Feedback.')->data;
    $staff_id = array_column($feedback_list, 'staff_id');
    $target_staff_id = array_column($feedback_list, 'target_staff_id');
    $staff_id = array_merge($staff_id, $target_staff_id);
    $staff_id = array_unique($staff_id);
    if ($staff_id) {
      $staff_list = $this->staff->read(['name', 'name_en', 'id'], ['id' => ' in ('.implode(',', $staff_id).')'])->cmap('id');
      $team_map = $this->team->read(['id','unit_id','name'],[])->cmap();
    } else {
      $staff_list = [];
    }
    // $fb_ids = [];
    foreach ($feedback_list as $key => $feedback) {
      $staff = isset($staff_list[$feedback['staff_id']]) ? $staff_list[$feedback['staff_id']] : [];
      $target_staff = isset($staff_list[$feedback['target_staff_id']]) ? $staff_list[$feedback['target_staff_id']] : [];
        // unset($staff['_ORDER_POSITION'], $target_staff['_ORDER_POSITION']);
      $feedback_list[$key]['staff'] = $staff;
      $feedback_list[$key]['target_staff'] = $target_staff;
      $multiple_choice_json = [];
      foreach ($feedback_list[$key]['multiple_choice_json'] as $choice_key => $choice) {
        $multiple_choice_json[$choice['id']] = $choice['score'];
        // $fb_ids[$choice['id']] = $choice['id'];
      }
      $feedback_list[$key]['multiple_choice_json'] = $multiple_choice_json;
      $feedback_list[$key]['department'] = $team_map[$feedback['department_id']];
    }
    // $choice = $this->choice->select(['title'],['id'=>$fb_ids]);
    return $feedback_list;
  }

  // 部屬回饋寄送 mail
  protected function sendEmailByData($template_name, $setting_data, $peoples, $cc_admin = false ){
    $mail = new \Model\MailCenter;
    if(empty($peoples)){
      $year=  $setting_data['year'];
      $feedback_table = $this->feedback->table_name;
      $emails = $this->staff->getEmailByWhere( ['id'=>"in(select staff_id from $feedback_table where year = $year)"] );
    }else if(is_string($peoples)){
      $emails = $peoples;
    }else{
      $emails = $this->staff->select(['email'],['id'=>$peoples]);
    }
    $mail->addAddress($emails);
    if ($cc_admin) {
      $email_ary = $this->staff->getAdminUserEmail();
      $mail->addCC($email_ary);
    }


    return $mail->sendTemplate($template_name, $setting_data);
  }

  public function getFeedbackDetailByStaff($staff,$year){

    // $staff_c = $this->staff->read(['id'],['id'=>$staff,'is_leader'=>1])->check('Staff Not Leader.');
    $result = []; $tmp=[]; $total = 0;
    $fb = $this->feedback->read(['multiple_choice_json','multiple_score','multiple_total'],['target_staff_id'=>$staff,'status'=>self::STATUS_FINISH,'year'=>$year])->check('Not Found Feedback.')->data;
    $choice_map = $this->choice->read( array('id','title','score'), [] )->map();
    foreach($fb as $v){
      foreach($v['multiple_choice_json'] as $mc){
        if(empty($tmp[$mc['id']]['score_total'])){$tmp[$mc['id']]['score_total']=0;}
        // $tmp[$mc['id']][] = $mc['score'];
        $tmp[$mc['id']]['score_total']+= (int)$mc['score'];

      }
      $total += (int)$v['multiple_total'];
    }
    $count = count($fb);
    $math = 100 / round($total / $count);
    // dd($fb);
    foreach($tmp as $qid => $tv){
      $r = [];
      $r['name'] = $choice_map[$qid]['title'];
      $r['score_avg'] = (float)number_format($tv['score_total'] / $count,2);
      $r['score_max'] = $choice_map[$qid]['score'];
      $r['point'] = $r['score_avg'] * $math;
      $result[] = $r;
    }
    return $result;
  }




}
?>
