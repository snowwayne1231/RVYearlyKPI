<?php

include __DIR__."/../ApiCore.php";
include BASE_PATH.'/Model/dbBusiness/Department.php';
include BASE_PATH.'/Model/dbBusiness/ConfigCyclical.php';
include BASE_PATH.'/Model/dbBusiness/MonthlyProcessing.php';
include BASE_PATH.'/Model/dbBusiness/MonthlyReportLeader.php';
include BASE_PATH.'/Model/dbBusiness/MonthlyReport.php';
include BASE_PATH.'/Model/dbBusiness/RecordMonthlyProcessing.php';
include BASE_PATH.'/Model/dbBusiness/Staff.php';
include BASE_PATH.'/Model/dbBusiness/RecordStaff.php';

$api = new ApiCore($_POST);

use Model\Business\Department;
use Model\Business\ConfigCyclical;
use Model\Business\MonthlyProcessing;
use Model\Business\MonthlyReport;
use Model\Business\MonthlyReportLeader;
use Model\Business\RecordMonthlyProcessing;
use Model\Business\Staff;
use Model\Business\RecordStaff;
use Model\Business\DepartmentLeadership;

if( $api->checkPost(array('id')) && $api->SC->isAdmin() ){
	$isChangeLeader = false;
	$isChangeUpper = false;
	$codi = $api->getPOST();

	$self_id = $api->SC->getId();

	//找到當期設定
	$webconfig = new ConfigCyclical();
	$year = date("Y");
	$month = date("m");
	$config = $webconfig->getConfigWithDate($year,$month);
	if( $api->isPast($config['RangeEnd']) ){
		//超過需要月考評的日期  要改變下個月單子
		if($month==12){
			$month = 1;
			$year += 1;
		}else{
			$month += 1;
		}
	}else{
		//反之 改變當前月份
		if($config['monthly_launched']==1){
			//正啟動月考評中 不准異動組織
			$api->denied('Monthly On Launched, Do not Change Any Department.');
		}
	}

	$team = new Department();
	$staff = new Staff();
	// LG($team->tables_column);
	//過濾input
	$id = $api->post('id');
	unset($codi['id']);
	$codi = $team->trueColumn($codi);

	//取得所有 員工與單位
	$all_team = $team->map();
	$all_staff = $staff->map();

	//舊的
	$old_team = $all_team[$id];
	$old_leader = $old_team['manager_staff_id'];
	$old_upper_id = $old_team['upper_id'];

	//是否換新主管
	if( isset($codi['manager_staff_id'])){
		if($codi['manager_staff_id']==$old_leader ){
			unset($codi['manager_staff_id']);
		}else{
			$new_leader = $codi['manager_staff_id'];

			if( $new_leader != 0 ){
				if( empty($all_staff[$new_leader])){ $api->denied('Not Found The Staff.'); }
				if( $all_staff[$new_leader]['lv'] > $old_team['lv'] ){ $api->denied('Not Enough Lvevl.'); }

				$staff->update(['is_leader'=> 1], $new_leader);
				$staff->update(['is_leader'=> 0], $old_leader);
			} else {
				$departmentleadership = new DepartmentLeadership();
				$departmentleadership->read(['department_id'=>$id, 'status'=>1])->check(1, 'Can Not Change Manager With Have Leaderships.');
				$staff->update(['is_leader'=> 0], $old_leader);
			}

			$isChangeLeader = true;
		}
	}

	//是否換上層單位
	if( isset($codi['upper_id']) ){
		if($codi['upper_id']==$old_team['upper_id'] ){
			unset($codi['upper_id']);
			$supervisor_staff_id = $old_team['supervisor_staff_id'];
		}else{
			//上層單位換
			$new_upper = $codi['upper_id'];

			if( empty($all_team[$new_upper]) ){
				//錯誤的upper_id
				$api->denied('Error Of Upper Id.');
			}

			$newUpperTeam = $all_team[$new_upper];

			if($newUpperTeam['lv'] >= $old_team['lv']){
				//新的部門 階級沒有 舊的高
				$api->denied('Wrong Department Relation.');
			}
			//目標單位的主管
			$supervisor_staff_id = ($newUpperTeam['manager_staff_id']>0) ? $newUpperTeam['manager_staff_id'] : $newUpperTeam['supervisor_staff_id'];
			$codi['supervisor_staff_id'] = $supervisor_staff_id;

			$isChangeUpper = true;
		}
	}else{
		$supervisor_staff_id = $old_team['supervisor_staff_id'];
	}

	if( isset($codi['unit_id']) ){
		if( !preg_match('/^[A-Z]{1}[\d]{2}$/i',$codi['unit_id']) ){
			$api->denied('Unit Id Wrong.');
		}
		$see = $team->search(array('unit_id'=>$codi['unit_id']));
		if( count($see)>0){
			$api->denied('Double unit_id.');
		}
	}



	//更新單位
	$team->update( $codi , $id );
	//紀錄
	$record_data = $codi;
	$record_data['id'] = $id;
	$record = new \Model\Business\RecordAdmin( $self_id );
	$record->type($record::TYPE_SETTING)->update( $record_data );

	//開始更新月績效
	$process        = new MonthlyProcessing();
	$leader         = new MonthlyReportLeader();
	$general        = new MonthlyReport();
	$record_monthly = new RecordMonthlyProcessing();
	$record_staff   = new RecordStaff();


	//上層單位更換
	if($isChangeUpper){

		$final_leader = (isset($new_leader)) ? $new_leader : $old_leader;

		//上司的部門
		$super_team_id = $all_staff[ $supervisor_staff_id ]['department_id'];

		//上層單位改變了  自己的報表 要換給新的 上司
		$leader->update( array('owner_staff_id'=>$supervisor_staff_id, 'owner_department_id'=>$super_team_id, 'staff_id'=>$final_leader ) , array('staff_id'=>$old_leader,'year'=>$year,'month'=>$month ) );

	}

	//主管換人
	if($isChangeLeader){

		//舊人 月績效
		if($old_leader>0){
			$leader->delete( array('staff_id'=>$old_leader,'year'=>$year,'month'=>$month) );
			// $staff->update( array('is_leader'=>0) , $old_leader );
			$record_staff->add( array('operating_staff_id'=>$self_id, 'staff_id'=>$old_leader, 'changed_json'=>'{is_leader:0}'));
		}
		//主管拔掉
		if($new_leader==0){
			$new_leader = $supervisor_staff_id;
		}else{
			//新人 月績效
			$general->delete( array("staff_id"=>$new_leader,'year'=>$year,'month'=>$month ) );
			// $staff->update( array('is_leader'=>1) ,$new_leader );
			$record_staff->add( array('operating_staff_id'=>$self_id, 'staff_id'=>$new_leader, 'changed_json'=>'{is_leader:1}') );
		}

		//把 單子移除
		// $process->delete(" where created_department_id in ($id) and year = $year and month = $month ");

		//員工 報表歸屬
		$general->update(array(
			"owner_staff_id" => $new_leader
		), array( 'owner_department_id'=>$id,'year'=>$year,'month'=>$month ) );

	}

	if($isChangeLeader || $isChangeUpper){
		//下層的部門
		$sub_department = $team->getLowerIdArray( $id );
		$sub_department[] = $id;
		$sub_department[] = $old_upper_id;

		$string_sub_team = join(',',$sub_department);

		//直接移除 自動生成 關聯部門的 月考評
		$process->delete(" where created_department_id in ($string_sub_team) and year = $year and month = $month ");
		//更新部門關係
		$team->refreshRelation();

	}

	//最後檢查一次 是不是所有在部門擔任主管的人 is_leader  20170928 懶得抽直接做SQL
	// $team_table_name = $team->table;
	// $staff->sql( "UPDATE {table} as a left join $team_table_name as b on a.id = b.manager_staff_id set a.is_leader = IF(b.enable=1,1,0)" );

	$api->setArray('ok');

}else{

	$api->denied();

}

print $api->getJSON();

?>