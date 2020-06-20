<?php
namespace Model\Business\Multiple;

include_once __DIR__.'/_MultipleSets.php';
include_once __DIR__.'/../MonthlyProcessing.php';
include_once __DIR__.'/../MonthlyReport.php';
include_once __DIR__.'/../MonthlyReportLeader.php';
include_once __DIR__.'/../RecordMonthlyProcessing.php';
include_once __DIR__.'/../RecordMonthlyReport.php';

use \Exception;

/*
用月績效考評表為基底 組合 月績效報表
主要為程式內使用 不適用來組合資料給使用者 效能向
*/
class ProcessReport extends MultipleSets{

  protected $leader;
  protected $general;
  protected $process;
  protected $record_report;
  protected $record_process;

  protected $year;
  protected $month;

  protected $date_condition;

  public function __construct($year=null,$month=null){
    $this->leader = new \Model\Business\MonthlyReportLeader();
    $this->general = new \Model\Business\MonthlyReport();
    $this->process = new \Model\Business\MonthlyProcessing();
    $this->record_process = new \Model\Business\RecordMonthlyProcessing();
    $this->record_report = new \Model\Business\RecordMonthlyReport();

    if($year && $month){
      $this->year = $year;
      $this->month = $month;

      $this->date_condition = array("year"=>$year,"month"=>$month);
      $this->initRead( $this->date_condition );
    }

    return $this;
  }

  /**
   * 讀取初始資料
   * @param  array  $codi array("year" => 2018, "month" => 10)
   * @return 無回傳值
   */
  public function initRead($codi){
    $this->process->read($codi);
    $this->leader->read(array('id','staff_id','year','month'), $codi);
    $this->general->read(array('id','staff_id','year','month'), $codi);
  }

  /**
   * 檢查管理職的月考評單是否存在，若不存在則建立該管理職的月考評單
   * @param  integer $id            管理職 staff_id
   * @param  integer $super_id      上層部門的管理者ID
   * @param  integer $team_id       部門ID
   * @param  integer $super_team_id 上層部門ID
   * @param  array   $staff         管理職資料
   * @return object
   */
  public function checkLeaderReport($id, $super_id, $team_id, $super_team_id, $staff){
    $isExist = $this->leader->checkExist( $id, $this->year, $this->month );
    if(!$isExist){
      //直到最後上頭都跟自己相同的話 只有運維中心的主管 :: 不用建立 report
      if (!isset($staff['exception'])) {
        $error = [
          'staff'=> $staff,
          'msg'=> 'Leader Is Not In Normal Status.',
        ];
        dd($error);
        return $this;
      }
      if($super_id != $id){
        $this->leader->addStorage( array(
          "staff_id"            => $id,
          "title_id"            => $staff['title_id'],
          "post_id"             => $staff['post_id'],
          "year"                => $this->year,
          "month"               => $this->month,
          "owner_staff_id"      => $super_id,
          "owner_department_id" => $super_team_id,
          "exception"           => $staff['exception'],
          "exception_reason"    => $staff['exception_reason'],
        ) );
      }
    }
    return $this;
  }

  /**
   * 檢查進程是否存在，不存在就建立
   * @param  integer $create_id     建立者ID
   * @param  integer $owner_id      當前擁有者ID
   * @param  integer $team_id       部門ID
   * @param  integer $owner_team_id 當前擁有者部門ID
   * @param  integer $type          月考評單類型 1=主管, 2=一般
   * @param  array   $sa            單子的送審路程
   */
  public function checkProcessing($create_id, $owner_id, $team_id, $owner_team_id, $type, $sa){
    if( !$this->checkProcessIsExist($team_id,$type)){
      $this->addProcess($create_id, $owner_id, $team_id, $owner_team_id, $type, $sa);
    }
  }

  /**
   * 檢查指定部門的進程是否存在
   * @param  integer $id   部門ID
   * @param  integer $type 月考評單類型 1=主管, 2=一般
   * @return bool          結果
   */
  private function checkProcessIsExist($id,$type){
    $pmap = $this->process->map("created_department_id");
    $bl = isset($pmap[$id]);
    if($bl){
      if( isset($pmap[$id]['type']) ){
        $bl = $pmap[$id]['type']==$type ;
      }else{
        //當他有兩個結果 代表一定存在
        // foreach($pmap[$id] as $v){
          // if($v['type']==$type){break;}
        // }
      }
    }
    return $bl;
  }

  /**
   * 新增進程
   * @param integer $id            職員ID
   * @param integer $super_id      上層部門主管ID
   * @param integer $team_id       部門ID
   * @param integer $super_team_id 上層部門ID
   * @param integer $map           月考評單類型 1=主管, 2=一般
   * @param array   $sa            單子的送審路程
   */
  private function addProcess($id,$super_id,$team_id,$super_team_id,$map,$sa){
    $record = array(
      "created_staff_id"      =>$id,
      "created_department_id" =>$team_id,
      "year"                  => $this->year,
      "month"                 => $this->month,
      "owner_staff_id"        => $super_id,
      "owner_department_id"   => $super_team_id,
      "type"                  => $map,
      "path_staff_id"         => "'".json_encode($sa)."'"
    );
    $this->process->addStorage($record);
    $this->process->addData( $record );
  }


  /**
   * 檢查一般人員的月考評單是否存在，若不存在則建立一般人員的月考評單
   * @param  array   $staff_ary  職員資料
   * @param  integer $manager_id 主管ID
   * @param  integer $team_id    部門ID
   */
  public function checkGeneralReport($staff_ary,$manager_id,$team_id){
    foreach($staff_ary as $val){
      $id = $val["id"];
      if(!$id) continue;

      $isExist = $this->general->checkExist( $id, $this->year, $this->month );
      if(!$isExist){
        if($id === $manager_id) continue;

        $this->general->addStorage( array(
          "staff_id"            => $id,
          "title_id"            => $val['title_id'],
          "post_id"             => $val['post_id'],
          "year"                => $this->year,
          "month"               => $this->month,
          "owner_staff_id"      => $manager_id,
          "owner_department_id" => $team_id,
          "exception"           => $val['exception'],
          "exception_reason"    => $val['exception_reason'],
        ) );
      }
    }
  }

  /**
   * 新增資料到資料庫中
   * @return 新增結果
   */
  public function releaseAllInsert(){
    $a = $this->leader->addRelease();
    $b = $this->general->addRelease();
    $c = $this->process->addRelease();
    return $a+$b+$c;
  }

  /**
   * 以 年 月 來更新職員的職務名稱
   * @param  integer $year  年
   * @param  integer $month 月
   */
  public function refreshAllStaffPostTitleWithYM($year, $month){
    $sql = "UPDATE
              {table} AS a
              LEFT JOIN rv_staff AS b ON a.staff_id = b.id
            SET a.post_id = b.post_id, a.title_id = b.title_id
            WHERE (a.post_id != b.post_id OR a.title_id != b.title_id) AND a.year = $year AND a.month = $month";
    $this->general->sql($sql);
    $this->leader->sql($sql);
  }

  /**
   * 更新一般人員和管理職的進程ID
   * @return object
   */
  public function updateReportProcessingID(){
    $process = $this->process->table_name;
    $general = $this->general->table_name;
    $leader  = $this->leader->table_name;
    $year    = $this->year;
    $month   = $this->month;

    //一般人員
    $sql = "UPDATE
              $general AS general
              LEFT JOIN $process AS process ON
                general.owner_department_id = process.created_department_id
                AND general.year = process.year
                AND general.month= process.month
                AND process.type = '2'
            SET processing_id = process.id
            WHERE general.year = $year AND general.month = $month";
    $this->general->DB->doSQL($sql);

    //管理職
    $sql2 = "UPDATE
              $leader AS leader
              LEFT JOIN $process AS process ON
                leader.owner_department_id = process.created_department_id
                AND leader.year = process.year
                AND leader.month = process.month
                AND process.type = '1'
            SET processing_id = process.id
            WHERE leader.year = $year AND leader.month = $month";
    $this->leader->DB->doSQL($sql2);

    $this->deleteNoReportProcess();//刪除空的進程

    $this->refreshAllStaffPostTitleWithYM($year,$month);//更新職員的職務名稱

    return $this;
  }

  /**
   * 更新月考評單
   * @param  array   $data     要修改的資料
   * @param  array   $process  進程
   * @param  integer $staff_id 修改者ID
   * @return array   有被修改的資料欄位
   */
  public function updateReport($data,$process,$staff_id){
    $type = $process['type'];
    if($type==1){
      $table = $this->leader;
    }else{
      $table = $this->general;
    }
    $id = $data['id'];
    unset($data['id']);

    $data['update_date'] = date('Y-m-d H:i:s');

    $report = $table->read($id)->data;
    $report = array_pop($report);

    $chagne = $table->update($data,$id);

    
    //把沒有修改的部份 移除掉
    foreach ($report as $key => $value) {
      if (isset($data[$key])) {
        if ($report[$key] == $data[$key]) {
          unset($data[$key]);
        }
      }
    }

    unset($data['update_date']);

    $report['id'] = $id;
    if($chagne && count($data)>0){
      $pr_id = $process['id'];
      // $rprt_name = $this->record_process->table_name;
      $this->record_report->add(array(
        'operating_staff_id'   =>$staff_id,
        'processing_id'        =>$pr_id,
        // 'processing_record_id'=>"(select id from $rprt_name where processing_id = $pr_id order by update_date desc limit 1)",
        'processing_record_id' =>0,
        'report_id'            =>$id,
        'report_type'          =>$type,
        'changed_json'         =>json_encode($data)
      ));
    }
    return $this;
  }

  /**
   * 刪除空的進程
   * @return [type] [description]
   */
  public function deleteNoReportProcess(){
    $general = $this->general->table_name;
    $leader  = $this->leader->table_name;
    $year    = $this->year;
    $month   = $this->month;

    $sql = "DELETE FROM {table} WHERE id NOT IN (
              SELECT processing_id FROM $general WHERE year=$year AND month = $month UNION
              SELECT processing_id FROM $leader WHERE year=$year AND month = $month
            ) AND year = $year AND month = $month ";
    $this->process->sql($sql);

    return $this;
  }

  /* 20181116 Carmen 沒有被使用到，先拿掉。
  public function checkOutDutyWithStaffs($staffs,$year,$month){
    $general = $this->general->table_name;
    $leader = $this->leader->table_name;
    $ids = [];
    foreach($staffs as $id=>$val){
      $ids[]=$val['id'];
    }
    $id_str = join(',',$ids);
    $col = 'id,staff_id,releaseFlag';
    $where = "where year = $year and month = $month and staff_id in ($id_str)";
    $data = $this->leader->sql("SELECT $col FROM {table} $where UNION SELECT $col FROM $general $where")->data;
    foreach($data as &$d){
      $staff = $staffs[$d['staff_id']];
      // $this->error($staff['name'].' 出現不該有');
    }
  }
  */

  protected function get_team(){
    return $this->team;
  }
  protected function get_leader(){
    return $this->leader;
  }
  protected function get_general(){
    return $this->general;
  }
  protected function get_process(){
    return $this->process;
  }

}
?>
