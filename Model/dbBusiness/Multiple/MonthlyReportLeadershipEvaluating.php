<?php
namespace Model\Business\Multiple;

include_once __DIR__.'/_MultipleSets.php';

use \Exception;

/*
月績效考評報表，增加多主管評分制
*/
class MonthlyReportLeadershipEvaluating extends MultipleSets{

  protected $department;
  protected $departmentLeadership;
  protected $reportEvaluating;
  protected $leader_report;
  protected $general_report;
  protected $processing;
  protected $processingEvaluating;

  protected $year;
  protected $month;

  protected $date_condition;

  public function __construct($year=null,$month=null){
    $this->department = new \Model\Business\Department();
    $this->departmentLeadership = new \Model\Business\DepartmentLeadership();
    $this->reportEvaluating = new \Model\Business\MonthlyReportEvaluating();
    $this->leader_report = new \Model\Business\MonthlyReportLeader();
    $this->general_report = new \Model\Business\MonthlyReport();
    $this->processing = new \Model\Business\MonthlyProcessing();
    $this->processingEvaluating = new \Model\Business\MonthlyProcessingEvaluating();
    

    if($year && $month){
      $this->year = $year;
      $this->month = $month;

      $this->date_condition = array("year"=>$year,"month"=>$month);
      // $this->initRead( $this->date_condition );
    }

    return $this;
  }

  /**
   * 讀取初始資料
   * @param  array  $codi array("year" => 2018, "month" => 10)
   * @return 無回傳值
   */
  public function init() {
    $this->reportEvaluating->read(array('id','staff_id','department_id','status'), $this->date_condition);
  }

  /**
   * 刪除資料
   */
  public function delete($condition = null) {
    if (isset($condition)) {
      $this->reportEvaluating->delete($condition);
      $this->processingEvaluating->delete($condition);
    } else {
      $this->reportEvaluating->delete($this->date_condition);
      $this->processingEvaluating->delete($this->date_condition);
    }
    return $this;
  }

  /**
   * 取得每個部門的管理層
   */
  public function getLeadershipMap() {
    $data = $this->departmentLeadership->select(['id', 'department_id', 'staff_id'], ['status' => 1]);
    $map = [];
    foreach ($data as $loc) {
      $department_id = $loc['department_id'];
      $staff_id = $loc['staff_id'];
      if (isset($map[$department_id])) {
        array_push($map[$department_id], $staff_id);
      } else {
        $map[$department_id] = [$staff_id];
      }
    }
    return $map;
  }

  /**
   * 更新所有報表
   */
  public function refreshAllReports() {
    $all_leader_report = $this->leader_report->select(null, $this->date_condition, ['processing_id'=>'ASC']);
    $all_general_report = $this->general_report->select(null, $this->date_condition, ['processing_id'=>'ASC']);
    $map_process = $this->processing->read(['id', 'type', 'created_staff_id', 'created_department_id', 'path_staff_id'], $this->date_condition)->map();
    $map_department_manager = $this->department->read(['id', 'lv', 'supervisor_staff_id', 'manager_staff_id', 'upper_id'], ['manager_staff_id'=>'> 0'])->map('manager_staff_id', true);
    $map_leadership = $this->getLeadershipMap();

    $eva = $this->reportEvaluating;
    $map_eva_leader_report = $eva->read(['report_id'], ['year'=>$this->year, 'month'=>$this->month, 'report_type'=> 1])->map('report_id');
    $map_eva_general_report = $eva->read(['report_id'], ['year'=>$this->year, 'month'=>$this->month, 'report_type'=> 2])->map('report_id');

    $last_year = $this->month == 1 ? $this->year -1: $this->year;
    $last_month = $this->month == 1 ? 12 : $this->month -1;
    $map_last_month_eva_leader_report = [];
    $map_last_month_eva_general_report = [];

    foreach($eva->select(['staff_id_evaluator', 'report_id', 'should_count'], ['year'=>$last_year, 'month'=>$last_month, 'report_type'=> 1]) as $loc) {
      $rid = $loc['report_id'];
      $evaluator = $loc['staff_id_evaluator'];
      $sc = $loc['should_count'];
      $map_last_month_eva_leader_report[$rid][$evaluator] = $sc;
    }

    foreach($eva->select(['staff_id_evaluator', 'report_id', 'should_count'], ['year'=>$last_year, 'month'=>$last_month, 'report_type'=> 2]) as $loc) {
      $rid = $loc['report_id'];
      $evaluator = $loc['staff_id_evaluator'];
      $sc = $loc['should_count'];
      $map_last_month_eva_general_report[$rid][$evaluator] = $sc;
    } 
    // $tmp = [];

    // var_dump($map_department_manager);
    // exit(2);
    // 主管單
    foreach ($all_leader_report as $report) {
      $r_id = $report['id'];
      if (isset($map_eva_leader_report[$r_id])) {
        continue;
      }
      $processing_data = $map_process[$report['processing_id']];
      $path_process = $processing_data['path_staff_id'];
      $type = $processing_data['type'];
      $process_created_department_id = $processing_data['created_department_id'];
      

      foreach ($path_process as $p_staff_id) {
        $manager_department = $map_department_manager[$p_staff_id];
        $manager_department_id = $manager_department['id'];
        $manager_id = $p_staff_id;
        $leaders = isset($map_leadership[$manager_department_id]) ? $map_leadership[$manager_department_id] : [];
        if (!in_array($manager_id, $leaders)) {
          $leaders[] = $manager_id;
        }

        foreach ($leaders as $leader_id) {
          $next_should_count = 1;
          if (isset($map_last_month_eva_leader_report[$r_id]) && isset($map_last_month_eva_leader_report[$r_id][$leader_id])) {
            $next_should_count = $map_last_month_eva_leader_report[$r_id][$leader_id];
          }

          $new_data = [
            'year'=> $this->year,
            'month'=> $this->month,
            'staff_id'=> $report['staff_id'],
            'staff_id_evaluator'=> $leader_id,
            'staff_department_id'=> $process_created_department_id,
            'evaluator_department_id'=> $manager_department_id,
            'report_type'=> $type,
            'report_id'=> $r_id,
            'json_data'=> $eva->parseReportScoreJSON($report, $type),
            'should_count'=> $next_should_count,
          ];
          // $tmp[] = $new_data;
          $eva->add($new_data);
        }
      }
    }

    foreach ($all_general_report as $report_2) {
      $r_id = $report_2['id'];
      if (isset($map_eva_general_report[$r_id])) {
        continue;
      }
      $processing_data = $map_process[$report_2['processing_id']];
      $path_process = $processing_data['path_staff_id'];
      $type = $processing_data['type'];
      $process_created_department_id = $processing_data['created_department_id'];

      foreach ($path_process as $p_staff_id) {
        $manager_department = $map_department_manager[$p_staff_id];
        $manager_department_id = $manager_department['id'];
        $manager_id = $p_staff_id;
        $leaders = isset($map_leadership[$manager_department_id]) ? $map_leadership[$manager_department_id] : [];
        if (!in_array($manager_id, $leaders)) {
          $leaders[] = $manager_id;
        }

        foreach ($leaders as $leader_id) {
          $next_should_count = 1;
          if (isset($map_last_month_eva_general_report[$r_id]) && isset($map_last_month_eva_general_report[$r_id][$leader_id])) {
            $next_should_count = $map_last_month_eva_general_report[$r_id][$leader_id];
          }

          $new_data = [
            'year'=> $this->year,
            'month'=> $this->month,
            'staff_id'=> $report_2['staff_id'],
            'staff_id_evaluator'=> $leader_id,
            'staff_department_id'=> $process_created_department_id,
            'evaluator_department_id'=> $manager_department_id,
            'report_type'=> $type,
            'report_id'=> $r_id,
            'json_data'=> $eva->parseReportScoreJSON($report_2, $type),
            'should_count'=> $next_should_count,
          ];
          
          $eva->add($new_data);
        }
      }
    }

    $eva_process = $this->processingEvaluating;
    $map_eva_process = $eva_process->read(['processing_id'], $this->date_condition)->map('processing_id');

    // dd($map_process);

    foreach ($map_process as $process) {
      $pid = $process['id'];
      $path_process = $process['path_staff_id'];
      $process_department_id = $process['created_department_id'];

      if (isset($map_eva_process[$pid])) {
        continue;
      }

      foreach ($path_process as $p_staff_id) {

        $manager_department = $map_department_manager[$p_staff_id];
        $manager_department_id = $manager_department['id'];
        $manager_id = $p_staff_id;
        $leaders = isset($map_leadership[$manager_department_id]) ? $map_leadership[$manager_department_id] : [];
        if (!in_array($manager_id, $leaders)) {
          $leaders[] = $manager_id;
        }

        foreach ($leaders as $leader_id) {
          $new_data = [
            'year'=> $this->year,
            'month'=> $this->month,
            'staff_id'=> $leader_id,
            'staff_department_id'=> $manager_department_id,
            'processing_department_id'=> $process_department_id,
            'processing_id'=> $pid,
          ];
          $eva_process->add($new_data);
        }
      }
    }

    // var_dump($tmp);
    // exit(2);
  }

  /**
   * 用新制報表覆蓋舊制分數
   */
  public function replaceByReportData($report, $evaluator_id) {
    $new_report = $report;
    $id = $report['id'];
    $eva_report = $this->reportEvaluating->select(['json_data', 'submitted', 'should_count'], ['report_id'=>$id, 'staff_id_evaluator'=>$evaluator_id])[0];
    $eva_json = $eva_report['json_data'];
    foreach ($eva_json as $ekey => $eval) {
      $new_report[$ekey] = $eval;
    }
    $new_report['submitted'] = $eva_report['submitted'];
    $new_report['should_count'] = $eva_report['should_count'];
    return $new_report;
  }

  /**
   * 確認是否已經送出
   */
  public function checkCommitedProcessing($processing_id, $staff_id) {
    $data = $this->processingEvaluating->select(['status_code'], ['processing_id'=>$processing_id, 'staff_id'=>$staff_id]);
    foreach ($data as $loc) {
      $code = $loc['status_code'];
      if ($code == \Model\Business\MonthlyProcessingEvaluating::STATUS_CODE_SUBMITED) {
        return true;
      }
    }
    return false;
  }

}
?>
