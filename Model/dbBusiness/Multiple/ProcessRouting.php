<?php
namespace Model\Business\Multiple;

include_once __DIR__.'/_MultipleSets.php';
include_once __DIR__.'/../MonthlyProcessing.php';
include_once __DIR__.'/../Department.php';
include_once __DIR__.'/../MonthlyReport.php';
include_once __DIR__.'/../MonthlyReportLeader.php';
include_once __DIR__.'/../RecordMonthlyProcessing.php';
include_once __DIR__.'/../RecordMonthlyReport.php';
require_once (BASE_PATH.'/Model/dbBusiness/ConfigCyclical.php');
require_once (BASE_PATH.'/Model/MailCenter.php');
require_once (BASE_PATH.'/Model/dbBusiness/Staff.php');
use \Model\Business\MonthlyProcessingEvaluating;
use \Model\Business\MonthlyReportEvaluating;
use \Exception;
use Model\MailCenter;
use Model\Business\Multiple\ProcessReport;
use Model\Business\Multiple\Leadership;


class ProcessRouting extends MultipleSets{

  protected $team;

  protected $process;

  protected $general;

  protected $leader;

  protected $year;

  protected $month;

  protected $record_porcess;
  protected $record_report;

  protected $id;
  protected $owner;
  protected $owner_department_id;
  protected $created_department_id;
  protected $supervisor;
  protected $processEvaluating;
  protected $eva_process_data;
  protected $staff_id;
  protected $reportEvaluating;

  public function __construct($process_id, $staff_id){

    $this->process = new \Model\Business\MonthlyProcessing();
    $this->processEvaluating = new MonthlyProcessingEvaluating();
    $this->reportEvaluating = new MonthlyReportEvaluating();
    $this->owner = $this->process->read($process_id)->getOwner();
    $this->owner_department_id = $this->process->data[0]['owner_department_id'];
    $this->created_department_id = $this->process->data[0]['created_department_id'];
    $this->id = $process_id;
    $this->year = $this->process->data[0]['year'];
    $this->month = $this->process->data[0]['month'];

    $this->staff_id = $staff_id;
    
    $data = $this->processEvaluating->select(['processing_id'=> $process_id, 'staff_id'=> $staff_id]);
    if (count($data)==1) {
      $this->eva_process_data = $data[0];
      
    } else {
      $this->eva_process_data = null;
    }

    $this->team = new \Model\Business\Department();
    $this->supervisor = $this->team->getSuperWithManager( $this->owner );

    $this->record_porcess = new \Model\Business\RecordMonthlyProcessing();
    $this->record_report = new \Model\Business\RecordMonthlyReport();
    $this->leader = new \Model\Business\MonthlyReportLeader();
    $this->general = new \Model\Business\MonthlyReport();

    return $this;
  }

  /**
   * 送單子到上層主管
   * @param  integer $operating_staff 操作人員ID
   * @param  boolean $isAdmin        是否為系統管理者
   * @return [type]                   [description]
   */
  private function processToSupervisor(){
    $operating_staff = $this->staff_id;
    if(!$operating_staff) $operating_staff = $this->owner;

    //不是擁有者 也不是系統管理者，回傳錯誤訊息
    // if(!$isAdmin && $operating_staff != $this->owner){
    //   return $this->error('Can Not Do It.');
    // }

    $team  = $this->team->map('manager_staff_id',true);
    $super = $this->process->getNextOwner();//這裡的 super 是根據 path_staff 來決定
    $owner = $this->process->getOwner();
    $update= array(
      'owner_staff_id'      => $super,
      'prev_owner_staff_id' => $owner,
      'owner_department_id' => $team[$super]['id'],
      'commited'            => 1,
      'status_code'         => \Model\Business\MonthlyProcessing::STATUS_CODE_REVIEW,
    );

    // 更新舊的單全送了
    // $this->processEvaluating->update(['status_code'=> MonthlyProcessingEvaluating::STATUS_CODE_SUBMITED], ['processing_id'=> $this->id, 'staff_department_id'=> $team[$owner]['id']]);

    if ($super != $owner) {
      // 還有人要送  更新下層的進入準備
      $this->processEvaluating->update(['status_code'=> MonthlyProcessingEvaluating::STATUS_CODE_PERPARE], ['processing_id'=> $this->id, 'staff_department_id'=> $team[$super]['id']]);
    }

    $main_process_report = new ProcessReport();

    if ($this->process->isLeaderType()) {
      $this->leader->updateWithProcessingId($update ,$this->id, $this->year, $this->month); //更新管理職績效資料表
      $report_ids = $this->leader->select(['id'], ['processing_id'=> $this->id]);
      foreach ($report_ids as $report) {
        $id = $report['id'];
        $avgScore = $this->reportEvaluating->getAvgScore($id, $this->owner_department_id, 1);
        // $this->leader->update($avgScore, $id);
        $this->reportEvaluating->update(['json_data'=> $avgScore], ['evaluator_department_id'=>$team[$super]['id'], 'report_id'=>$id]);
        $report_update_data = array_merge($avgScore, ['id'=> $id]);
        $main_process_report->updateReport($report_update_data, $this->process->data[0], $operating_staff);
      }
    } else {
      $this->general->updateWithProcessingId($update ,$this->id, $this->year, $this->month);//更新一般職員績效資料表
      $report_ids = $this->general->select(['id'], ['processing_id'=> $this->id]);
      foreach ($report_ids as $report) {
        $id = $report['id'];
        $avgScore = $this->reportEvaluating->getAvgScore($id, $this->owner_department_id, 2);
        // $this->general->update($avgScore, $id);
        $this->reportEvaluating->update(['json_data'=> $avgScore], ['evaluator_department_id'=>$team[$super]['id'], 'report_id'=>$id]);
        $report_update_data = array_merge($avgScore, ['id'=> $id]);
        $main_process_report->updateReport($report_update_data, $this->process->data[0], $operating_staff);
      }
    }

    $this->process->update($update ,$this->id);//更新進程資料表

    //紀錄進程歷史
    $this->record_porcess->add( array('operating_staff_id'=>$operating_staff, 'target_staff_id'=>$super, 'processing_id'=>$this->id, 'action'=>'commit', 'changed_json'=> json_encode($update) ) );

    $this->catchReportChanged($this->id);

    return $update;
  }


  public function rejectToStaff( $staff_id, $reason, $operating_staff=0, $isAdmin=false ){

    if(!$operating_staff){
        $operating_staff = $this->owner;
    } else {
      //只有當前的owner可以駁回，或是擁有admin
      if (!(($operating_staff == $this->owner) || $isAdmin)) {
        return false;
      }
    }
    $team = $this->team->map('manager_staff_id',true);
    if( empty($team[$staff_id]) ){
      return false;
    }
    $owner_team = $team[$staff_id];


    if($owner_team['upper_id']==0){
      //取消核准
      $prev_owner_staff_id = $staff_id;

      if($isAdmin){
        $update = array('prev_owner_staff_id'=> $prev_owner_staff_id,'owner_staff_id'=>$staff_id,'owner_department_id'=>$owner_team['id'],'status_code'=> \Model\Business\MonthlyProcessing::STATUS_CODE_REVIEW);
        $add = array('operating_staff_id'=>$operating_staff, 'target_staff_id'=>$staff_id, 'processing_id'=>$this->id, 'action'=>'cancel', 'changed_json'=> json_encode($update) );
        if( $this->id ){
          // 把月報表重設回 未 release
          $report = $this->process->isLeaderType() ? $this->leader : $this->general;
          $report->update(array('releaseFlag'=>'N'),array('processing_id'=>$this->id));

          // 重設 考評中 單
          $this->processEvaluating->update(['status_code'=>MonthlyProcessingEvaluating::STATUS_CODE_PERPARE], ['processing_id'=> $this->id, 'staff_id'=>$staff_id]);
        }
        
      }else{
        return $this->error('You Are Not Admin.');
      }
    }else{
      //退回員工
      $prev_owner_staff_id = $this->getPreOwnerByStaffId($staff_id);
      
      $update = array('owner_staff_id'=>$staff_id,'prev_owner_staff_id'=>$prev_owner_staff_id,'owner_department_id'=>$owner_team['id'],'status_code'=> \Model\Business\MonthlyProcessing::STATUS_CODE_REJECT);
      $add = array('operating_staff_id'=>$operating_staff, 'target_staff_id'=>$staff_id, 'processing_id'=>$this->id, 'action'=>'return', 'changed_json'=> json_encode($update) );

      // 重設 考評中 單
      $this->processEvaluating->update(['status_code'=>MonthlyProcessingEvaluating::STATUS_CODE_PERPARE], ['processing_id'=> $this->id, 'staff_department_id'=>$owner_team['id']]);
    }
    //LG($this->process->data);
    $upd = $this->process->update( $update, $this->id);
    if($reason){$add['reason']=$reason;}
    if($upd){$this->record_porcess->add( $add );$this->catchReportChanged($this->id);}
    return $upd;
  }


  public function commitToNext($isAdmin) {
    $is_go_next = false;
    if ($isAdmin) {

      $is_go_next = $this->processToSupervisor();
      // $this->processEvaluating->update(['status_code'=> MonthlyProcessingEvaluating::STATUS_CODE_SUBMITED], ['processing_id'=> $this->id]);
      
    } else {

      $owner_department_id = $this->owner_department_id;
      
      // 自己的 評分中 單更新狀態
      if ($this->eva_process_data) {
        $update_data = ['status_code'=> MonthlyProcessingEvaluating::STATUS_CODE_SUBMITED];
        if ($this->eva_process_data['first_submit_date'] == '0000-00-00 00:00:00') {
          $update_data['first_submit_date'] = date('Y-m-d H:i:s');
        }
        $this->processEvaluating->update($update_data);
      }

      // 與當前擁有者一樣的 部門單位的所有 評分中 單
      $same_department_eva_process = $this->processEvaluating->select(['id', 'staff_id', 'status_code'], ['staff_department_id'=>$owner_department_id, 'processing_id'=>$this->id]);
      $is_all_finished = true;
      foreach ($same_department_eva_process as $process) {
        if ($process['status_code'] != MonthlyProcessingEvaluating::STATUS_CODE_SUBMITED) {
          $is_all_finished = false;
          break;
        }
      }

      if ($is_all_finished) {
        $is_go_next = $this->processToSupervisor();
      }
    }

    return $is_go_next;
  }

  public function isFinally(){
    return $this->process->getOwner() == $this->supervisor;
  }

  public function done($operating_staff=0){
    if(!$this->process->isDone()){
      $team = $this->team->map('manager_staff_id',true);
      $super = $this->supervisor;
      $type = $this->process->data[0]['type'];
      $created_staff_id = $this->process->data[0]['created_staff_id'];
      
      //報表分數結算
      if($type== \Model\Business\MonthlyProcessing::TYPE_TEAM_LEADER){
        $doneReport = $this->leader;
      }else{
        $doneReport = $this->general;
      }

      $report_ids = $doneReport->select(['id'], ['processing_id'=> $this->id]);

      foreach ($report_ids as $report) {
        $id = $report['id'];
        $avgScore = $this->reportEvaluating->getAvgScore($id, $team[$super]['id'], $type);
        $doneReport->update($avgScore, $id);
      }

      $doneReport->doneWithProcessingId($this->id, $team[$created_staff_id]['duty_shift'] );

      //更新考評單
      $update = array('prev_owner_staff_id'=>$super,'owner_staff_id'=>$super,'owner_department_id'=>$team[$super]['id'],'commited'=>1,'status_code'=> \Model\Business\MonthlyProcessing::STATUS_CODE_APPROVED);
      $this->process->update( $update ,$this->id);

      //記錄
      $this->record_porcess->add( array('operating_staff_id'=>$operating_staff, 'target_staff_id'=>$super, 'processing_id'=>$this->id, 'action'=>'done', 'changed_json'=> json_encode($update) ) );
      $this->catchReportChanged($this->id);
      $process_data = $this->process->select(array('year','id','month'), $this->id);
      //通知
      $this->notifyAdminAssessMentFinish($process_data[0]['year'], $process_data[0]['month']);

      // 評估中單
      $this->processEvaluating->update(['status_code'=> MonthlyProcessingEvaluating::STATUS_CODE_SUBMITED], ['processing_id'=> $this->id, 'staff_department_id'=> $team[$super]['id']]);

    }
    return $this;
  }


  private function catchReportChanged($pid){
    // $process_table = $this->process->table_name;
    $process_record_table = $this->record_porcess->table_name;
    $sql = "UPDATE {table} AS a
            LEFT JOIN $process_record_table AS c ON
              c.processing_id = a.processing_id
              AND c.id = (SELECT max(id) FROM $process_record_table WHERE processing_id = a.processing_id)
            SET a.processing_record_id = c.id
            WHERE a.processing_id = $pid AND a.processing_record_id = 0";
    $this->record_report->sql($sql);
  }

  protected function get_team(){
    return $this->team;
  }
  protected function get_staff(){
    return $this->staff;
  }
  protected function get_process(){
    return $this->process;
  }
  protected function get_creator(){
    return $this->creator;
  }
  protected function get_supervisor(){
    return $this->supervisor;
  }

  protected function get_owner(){
    return $this->owner;
  }

  public function getTeam(){
    if(count($this->process->data)==0){
      $this->process->read($this->id);
    }
    $team_id = $this->process->data[0]['created_department_id'];
    $team = $this->team->select($team_id);
    return count($team)>0?$team[0]:[];

  }
  /**
   * 通知管理者已經所有單子已經核準通過
   * @method     notifyAdminAssessMentFinish
   * @author Alex Lin <alex.lin@rv88.tw>
   * @version    [version]
   * @modifyDate 2017-08-08T09:21:10+0800
   * @param      int                      $year  年 若為 空白，則取系統當前時間的年份
   * @param      int                      $month 月 若為 空白，則取系統當前時間的月份
   * @return     [type]                             [description]
   */
  public function notifyAdminAssessMentFinish($year = '', $month = '')
  {
    $return = [
      'status' => true,
      'message' => '',
    ];
    $year = ($year) ? $year : date('Y');
    $month = ($month) ? $month : date('m');
    $table_name = $this->process->table_name;
    //把所有的當月的status_code 的數量加總就是當月所有的流程數量
    $sql = ' SELECT status_code, count(1) as count FROM '.$table_name.' WHERE year = :year AND month = :month GROUP BY status_code ';
    $bind_data = array(
      ':year' => array(
        'value' => $year,
        'type'  => \PDO::PARAM_INT,
      ),
      ':month' => array(
        'value' => $month,
        'type'  => \PDO::PARAM_INT,
      ),
    );
    $result = $this->process->sql($sql, $bind_data)->data;
    if ($result) {
      $total = 0; //總數量
      $approved_count = 0; //批準總數量
      foreach ($result as $key => $item) {
        $total += $item['count'];
        if ($item['status_code'] == \Model\Business\MonthlyProcessing::STATUS_CODE_APPROVED) {
          $approved_count = $item['count'];
        }
      }
      if ($total != $approved_count) {  //代表尚未全部批準過
        $return = [
          'status' => false,
          'message' => 'Not All Assessment Has Approved',
        ];
        return $return;
      }
    } else {  //沒找到該月份任何考評紀錄
      $return = [
        'status' => false,
        'message' => 'Not Found Any Assessment Record.',
      ];
      return $return;
    }

    $staff = new \Model\Business\Staff();
    $admin_staff = $staff->select(['id', 'email'], ['is_admin' => $staff::ISADMIN_YES, 'status_id' => 'in('.implode(',', [$staff::STATUS_ENABLE, $staff::STATUS_PARTTIME, $staff::STATUS_TRIAL ]).')']);
    $mail = new \Model\MailCenter;
    foreach($admin_staff as $key => $val){
      $mail->addAddress($val['email']);
    }

    $res = $mail->sendTemplate('monthly_assessment_finish',array(
      'year'=>$year,
      'month'=>$month,
    ));
    $return = [
      'status' => $res,
      'message' => 'Has Send To Administrator',
    ];
    return $return;
  }

  public function drawSingle($self_id, $isAdmin = false, $reason = '') {
    $prev_owner = $this->process->getPrevOwner();
    if ( $this->process->isDone() ) {  //如果已經核準，不能抽回
      $this->error('Already Done.');
    }
    //是否 是上一個擁有者
    if ($prev_owner == $self_id) {

      $return = $this->doDraw( $prev_owner, $reason, $prev_owner, true );
      if ($return) {
        //寄信
        $staff_model = new \Model\Business\Staff;
        $staff = $staff_model->select(['id', 'name', 'name_en', 'department_id'], ['id' => $self_id]);
        $staff = array_pop($staff);

        $target_staff_id = $this->process->data[0]['owner_staff_id'];

        $Leadership = new Leadership($target_staff_id);
        $target_leaders = $Leadership->getSameDepartmentLeaders(true);

        $department = $this->team->select(['name'], ['id' => $staff['department_id'] ]);
        $department = array_pop($department);
        $mail_data = [
          'year' => $this->process->data[0]['year'],
          'month' => $this->process->data[0]['month'],
          'staff_name' => $staff['name_en'].' '.$staff['name'],
          'department' => $department['name']
        ];
        $mail = new \Model\MailCenter;
        // $mail->addAddress($this->process->data[0]['owner_staff_id']);
        $mail->addAddressByStaffArray($target_leaders);
        $res = $mail->sendTemplate('monthly_draw', $mail_data);
        //去抓最新的process
        $return = $this->process->read($this->id)->data;
        $return = array_pop($return);
      } else {
        return $this->error('Nothing Changed.');
      }
      return $return;
    } else {
      $this->error('You Are Not PrevOnwer.');
    }
  }

  public function checkOwnerSameDepartment() {
    $department_id = $this->eva_process_data['processing_department_id'];
    return $department_id == $this->created_department_id;
  }

  private function doDraw( $staff_id, $reason, $operating_staff=0, $isAdmin=false ){

    if(!$operating_staff){
        $operating_staff = $this->owner;
    }
    $team = $this->team->map('manager_staff_id',true);
    if( empty($team[$staff_id]) ){
      return $this->error('You Are Not Leader.');
    }
    $owner_team = $team[$staff_id];
    //檢查是否啟動
    if( $this->process->isLaunch() ){
      //是否是創立者
      $current_creator = $this->process->getCreator();
      if( $staff_id == $current_creator){
        $status_code = \Model\Business\MonthlyProcessing::STATUS_CODE_FIRST;
      }else{
        $status_code = \Model\Business\MonthlyProcessing::STATUS_CODE_REVIEW;
      }
      $commited = 1;
    }else{
      $status_code = \Model\Business\MonthlyProcessing::STATUS_CODE_PERPARE;
      $commited = 0;
    }



    //退回員工
    $update = array('prev_owner_staff_id'=>$staff_id ,'owner_staff_id'=>$staff_id,'owner_department_id'=>$owner_team['id'],'status_code'=> $status_code, 'commited' => $commited);
    $add = array('operating_staff_id'=>$operating_staff, 'target_staff_id'=>$staff_id, 'processing_id'=>$this->id, 'action'=>\Model\Business\RecordMonthlyProcessing::ACTION_DRAWING, 'changed_json'=> json_encode($update) );

    //LG($this->process->data);
    $upd = $this->process->update( $update, $this->id);
    if($reason){$add['reason']=$reason;}
    if($upd){$this->record_porcess->add( $add );$this->catchReportChanged($this->id);}
    return $upd;
  }

  private function getPreOwnerByStaffId($staff_id) {
    $path_staff_id = $this->process->data[0]['path_staff_id'];
    $idx = array_search($staff_id, $path_staff_id);
    if ($idx == 0) {
      return 0;
    } else {
      return $path_staff_id[$idx -1];
    }
  }

}
?>
