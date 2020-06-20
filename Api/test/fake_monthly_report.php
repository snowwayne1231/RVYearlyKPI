<?php


include __DIR__."/../ApiCore.php";
include BASE_PATH.'/Model/dbBusiness/ConfigCyclical.php';
include BASE_PATH.'/Model/dbBusiness/Multiple/ProcessReport.php';
include BASE_PATH.'/Model/dbBusiness/Multiple/DepartmentStaff.php';

$api = new ApiCore($_REQUEST);

use Model\Business\ConfigCyclical;
use Model\Business\Multiple\DepartmentStaff;
use Model\Business\Multiple\ProcessReport;



if($api->checkPost(array("year","month")) || $api->checkPost(array("check"))){

  $year      = $api->post('year');
  $month     = $api->post('month');


  //取得該月的設定值
  $cyc_config = new ConfigCyclical( $year,$month );
  $config = $cyc_config->data;


    $ds = new DepartmentStaff();

    $pr = new ProcessReport($year,$month);

    //過濾還再職的員工
    $ds->staff->read( array('id','staff_no','name','name_en','lv','status_id','first_day','last_day','department_id') ,'')->filterOnDuty( $config['RangeStart'],$config['RangeEnd'] );
    $teamsData = $ds->collect($year,$month);


    $staffMaps = $ds->staff->map();

    $Staff_key = DepartmentStaff::$Staff;


    //更新/檢查 月報表
    if( $api->SC->isAdmin() ){

      $emptyTeams = array();

      //檢查 未產生的月績效考評單
      foreach($teamsData as &$loc){
        $manager_id = $loc['manager_staff_id'];
        $super_id = $loc['supervisor_staff_id'];
        $real_manager_id = ($manager_id) ? $manager_id : $super_id;
        $team_id = $loc['id'];
        $super_team_id = $staffMaps[ $super_id ][ 'department_id' ];

        $staffCount = $ds->countStaff($team_id);
        //沒主管又沒員工
        if( ($manager_id + $staffCount) == 0 ){$emptyTeams[]=$team_id;continue;}

        $leaderCount = $ds->countSubLeader($team_id);
        $superArray = $ds->team->getSuperArrayWithManager($real_manager_id);

        //有主管
        if( $manager_id ){

          $pr->checkLeaderReport($manager_id, $super_id, $team_id, $super_team_id);

        }

        //除了主管的員工數量
        if( $staffCount ){

          $staffs = $loc[ $Staff_key ];
          $dev_manager_id = ($manager_id) ? $manager_id : $super_id;

          $pr->checkGeneralReport($staffs,$dev_manager_id,$team_id);

          //有員工一定是組員對應該單位組長

          $stid = ($manager_id) ? $team_id : $super_team_id;
          $pr->checkProcessing($real_manager_id, $real_manager_id, $team_id, $stid ,'2', $superArray);

        }
        //檢察是否有下層主管
        if( $leaderCount && $manager_id){

          $pr->checkProcessing($manager_id, $manager_id, $team_id, $team_id ,'1', $superArray);

        }

      } // for ..count
      //一次塞入資料
      $times = $pr->releaseAllInsert();
      //檢查 績效表的流程單
      if($times>0){

        $pr->updateReportProcessingID();

      }

    }



  //檢查完畢 開始塞假資料

  $MRL = new Model\Business\MonthlyReportLeader();
  $MR = new Model\Business\MonthlyReport();
  $MP = new Model\Business\MonthlyProcessing();
  $CMT = new Model\Business\RecordPersonalComment();

  $leader_report = $MRL->select(['id','owner_staff_id','staff_id','comment_id'],['year'=>$year,'month'=>$month,'releaseFlag'=>'N']);
  $normal_report = $MR->select(['id','owner_staff_id','staff_id','comment_id'],['year'=>$year,'month'=>$month,'releaseFlag'=>'N']);

  $mrl_col = ['target','quality','method','error','backtrack','planning','execute','decision','resilience','attendance','attendance_members'];
  $mr_col = ['quality','completeness','responsibility','cooperation', 'attendance'];

  function mUpdate($data, $cols, $model){
    Global $CMT;
    if(count($data)==0){return;}
    // dd($data);
    $type= count($cols)>5 ? 1: 2;
    foreach($data as $v){
      $mod = rand(1,10);
      $update_data = ['releaseFlag'=>'Y','addedValue'=>$mod<3?$mod*10:0,'mistake'=>$mod>8?$mod:0];
      foreach($cols as $col){
        $update_data[$col] = rand(1,5);
      }

      $update_data['comment_id'] = $v['comment_id'];
      $times = rand(1,3);
      for($i=0; $i<$times ; $i++){
        $content = '測試月績效評論 _'.MD5(rand(1,10));
        $id = $CMT->create([ 'create_staff_id'=>$v['owner_staff_id'], 'target_staff_id'=>$v['staff_id'], 'report_id'=>$v['id'], 'report_type'=>$type, 'content'=>$content ]);
        $update_data['comment_id'][]= $id;
      }
      // dd($update_data);
      $update_data['comment_id'] = join(',',$update_data['comment_id']);
      $model->update( $update_data ,$v['id']);
    }
  }

  mUpdate($leader_report, $mrl_col, $MRL);
  mUpdate($normal_report, $mr_col, $MR);
  //總分
  $MR->sql("update rv_monthly_report as a set total = (a.quality*5 + a.completeness*5 + a.responsibility*5 + a.cooperation*3 + a.attendance*2)+a.addedValue-a.mistake where a.month = $month");
  $MR->sql("update rv_monthly_report_leader as b set total = (b.target*2 + b.quality*2 + b.method*2 + b.error*2 + b.backtrack*2 + b.planning*2 + (b.execute*7/5) + (b.decision*7/5) + (b.resilience*6/5) + b.attendance*2 + b.attendance_members*2) + b.addedValue - b.mistake where b.month = $month");
  $MR->sql("update rv_monthly_report as c left join rv_department as d on c.owner_department_id = d.id set total = (c.quality*5 + c.completeness*5 + c.responsibility*3 + c.cooperation*3 + c.attendance*4)+c.addedValue-c.mistake where d.duty_shift=1 and  c.month = $month");

  $MP->update(['status_code'=>5,'commited'=>1,'owner_staff_id'=>1,'owner_department_id'=>1],['year'=>$year,'month'=>$month]);
  // $CMT->refresh($year,$month);
  $cyc_config->update(['monthly_launched'=>1],['year'=>$year,'month'=>$month]);

  $api->setArray([
    'LC' => count($leader_report),
    'NC' => count($normal_report)
  ]);

}

print $api->getJSON();

?>