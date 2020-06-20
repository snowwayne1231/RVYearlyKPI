<?php
include __DIR__."/../ApiCore.php";
include BASE_PATH.'/Model/dbBusiness/Multiple/Staff.php';
include BASE_PATH.'/Model/dbBusiness/StaffEvent.php';
include BASE_PATH.'/Model/dbBusiness/Multiple/StaffHistory.php';
include_once BASE_PATH.'/Model/dbBusiness/Department.php';
include BASE_PATH.'/Model/dbBusiness/MonthlyReport.php';
include BASE_PATH.'/Model/dbBusiness/ConfigCyclical.php';
use Model\Business\Multiple\Staff;
use Model\Business\StaffEvent;
use Model\Business\Multiple\StaffHistory;
use Model\Business\Department;
use Model\Business\DepartmentLeadership;
use Model\Business\MonthlyReport;
use Model\Business\ConfigCyclical;

$api = new ApiCore($_POST);

if( $api->SC->isAdmin() ){

	$id = $api->post('id');
	if(!$id) $api->denied('No Such Staff.');

	$data = $api->getPOST();

	//不能改變
	unset($data["account"]);
	unset($data["staff_no"]);
	unset($data["id"]);

	//離值日為空 設為 0000-00-00 配合正式機的 mysql版本/設定
	if( isset($data['last_day']) && empty($data['last_day']) ){
		$data['last_day'] = '0000-00-00';
	}

	//檢查日期
	foreach(['first_day','last_day','stay_start_day','stay_end_day','return_day','update_date'] as $dv){
		if( !isset($data[$dv]) ) continue;
		if( !preg_match('/[\d]{4}\-[\d]{1,2}\-[\d]+/',$data[$dv]) ){
			$api->denied('Not Match Date Format.');
		}
	}

	//多表 staff 操作
	$staff = new Staff();
	$target_staff = $staff->select( $id );
	$self = $api->SC->getId();

	//找不到員工
	if( count($target_staff)==0 ) $api->denied('This Staff Has Not Defined.');

	$isLeader = $target_staff[0]['is_leader'];
	$old_status_id = $target_staff[0]['status_id'];
	$old_department = $target_staff[0]['department_id'];
	$old_post_id = $target_staff[0]['post_id'];
	$old_title_id = $target_staff[0]['title_id'];

	$department_leadership = new DepartmentLeadership();
	$old_department_leadership = $department_leadership->select(['department_id'=>$old_department, 'staff_id'=>$id, 'status'=>1]);
	$is_in_leadership = false;
	if (isset($old_department_leadership) && count($old_department_leadership) >0) {
		$is_in_leadership = true;
		$isLeader = true;
	}

	if( count($data)==0 ) $api->denied('No Change Data.');


	//是主管不能換部門
	if($isLeader && $old_department != $data['department_id']){
		$api->denied("A Leader Can't Change The Department.");
	}


	//當月評分
	$webconfig = new ConfigCyclical();
	$year = date("Y");
	$month= date("m");
	$config = $webconfig->getConfigWithDate($year,$month);
	if( $api->isPast($config['RangeEnd']) ){
		if($month == 12){
			$month = 1;
			$year += 1;
		}else{
			$month += 1;
		}
	}else{
		if($config['monthly_launched']==1){ $api->denied('Can Not Modify Staff When Monthly Launch.'); }
	}


	$history = new StaffHistory();

	$department_id = isset($data['department_id']) ? $data['department_id'] : $old_department;
	$post_id = isset($data['post_id']) ? $data['post_id'] : $old_post_id;
	$title_id = isset($data['title_id']) ? $data['title_id'] : $old_title_id;

	//換新狀態
	$status_id = isset($data['status_id']) ? $data['status_id'] : $old_status_id;
	if($old_status_id != $status_id){

		if ($isLeader) {
			$api->denied('Leader Can Not Modify Status.');
		}
		$event_id = 0;
		$event_day = date('Y-m-d');

		if($old_status_id == 3 && $status_id == 1) $event_id = 2;//試用轉正職
		if($old_status_id == 2 && $status_id == 1) $event_id = 8;//約聘轉正職
		if($old_status_id == 5 && $status_id == 1) $event_id = 7;//留停復職-正式
		if($old_status_id == 5 && $status_id == 2) $event_id = 7;//留停復職-約聘
		if($old_status_id == 5 && $status_id == 3) $event_id = 7;//留停復職-試用
		if($status_id == 4) $event_id = 9;//離職

		if($event_id != 0){
			//設定復職日期
			if($event_id == 7 && isset($data['return_day'])) $event_day = $data['return_day'];
			if($event_id == 9 && isset($data['last_day'])) $event_day = $data['last_day'];
			$history->createEvent(['staff_id' => $id, 'status' => $status_id, 'event' => $event_id, 'event_day' => $event_day, 'department_id' => $department_id, 'post_id' => $post_id, 'title_id' => $title_id]);

			if($old_status_id != 5){	// 原先非留停者  更新時間
				$oldHistory = $history->getLogWithStatus($id, $old_status_id);
				if ($oldHistory) {
					$history->updateMain($oldHistory['id'], ['end_time' => $event_day]);
				}
			}

			// 記錄人員異動明細的在職狀態
			$historyData = [
				'staff_id' => $id,
				'modify_field' => 'status',
				'old' => $old_status_id,
				'new' => $status_id,
				'start_time' => $event_day,
				'end_time' => '0000-00-00'
			];
			$history->createMain($historyData);

		}else if($status_id == 5){//留停
			if($oldHistory = $history->getLogWithStatus($id, $status_id)){
				if(strtotime($data['stay_start_day']) < strtotime($oldHistory['end_time'])){
					$api->denied('新增留停資料，新的留停開始時間不可以小於前一次留停的結束時間');
				}
			}

			// 記錄人員異動明細的在職狀態
			if ($oldStatusHistory = $history->getLogWithStatus($id, $old_status_id)) {
				$event_day = date('Y-m-d H:i:s', strtotime($data['stay_start_day'].' -1 day'));
				$history->updateMain($oldStatusHistory['id'], ['end_time' => $event_day]);
			}
			
			$history->createStayLog($id, $old_status_id, $data['stay_start_day'], $data['stay_end_day'], $department_id, $post_id, $title_id);
		}
	}else if($status_id == 5 && (isset($data['stay_start_day']) || isset($data['stay_end_day']))){
		//修改留停資料
		$addNewStopRecord = false;//新增留停記錄
		if(isset($data['addNewStopRecord'])){
			$addNewStopRecord = filter_var($data['addNewStopRecord'], FILTER_VALIDATE_BOOLEAN);
		}

		if($oldHistory = $history->getLogWithStatus($id, $status_id)){
			$history_id = $oldHistory['id'];
		}

		$start_time = isset($data['stay_start_day']) ? $data['stay_start_day'] : $oldHistory['start_time'];
		$end_time = isset($data['stay_end_day']) ? $data['stay_end_day'] : $oldHistory['end_time'];

		if(empty($history_id)){
			$history->createStayLog($id, $old_status_id, $start_time, $end_time, $department_id, $post_id, $title_id);
		}else{
			/* 20181120 Carmen 會影響異動留停的功能，故先移除
			if($addNewStopRecord){
				if(strtotime($start_time) < strtotime($oldHistory['end_time'])){
					$api->denied('新增留停資料，新的留停開始時間不可以小於前一次留停的結束時間');
				}
			}
			*/

			$historyData = [
				'modify_field' => 'status',
				'start_time' => $start_time,
				'end_time' => $end_time,
			];
			$history->updateMain($history_id, $historyData);

			if($start_time != $oldHistory['start_time']){
				$sql = "SELECT id FROM {table} WHERE staff_id = $id AND event = 4 ORDER BY create_date DESC LIMIT 0, 1";
				if($res = $history->eventSQL($sql)->data){
					$dataEvent4ID = $res[0]['id'];
					$sql = "UPDATE {table} SET event_day = '$start_time', update_date = '".date('Y-m-d H:i:s')."' WHERE id = $dataEvent4ID";
					$history->eventSQL($sql);
				}
			}

			if($end_time != $oldHistory['end_time']){
				if($addNewStopRecord){
					$event_id = 6;
					$history->createEvent(['staff_id' => $id, 'status' => $status_id, 'event' => $event_id, 'event_day' => $end_time, 'department_id' => $department_id, 'post_id' => $post_id, 'title_id' => $title_id]);
				}else{
					$dataID = 0;
					$sql = "SELECT id FROM {table} WHERE staff_id = $id AND event = 4 ORDER BY create_date DESC LIMIT 0, 1";
					if($res = $history->eventSQL($sql)->data){
						$dataID = $res[0]['id'];
					}

					//是否曾經有留停延遲或提早的資料
					$sql = "SELECT id FROM {table} WHERE staff_id = $id AND event = 6 AND id > $dataID ORDER BY create_date DESC LIMIT 0, 1";
					if($res = $history->eventSQL($sql)->data){
						$dataID = $res[0]['id'];
						$sql = "UPDATE {table} SET event_day = '$end_time', update_date = '".date('Y-m-d H:i:s')."' WHERE id = $dataID";
						$history->eventSQL($sql);
					}else{
						$sql = "UPDATE {table} SET note = '$end_time', update_date = '".date('Y-m-d H:i:s')."' WHERE id = $dataID";
						$history->eventSQL($sql);
					}

				}

			}

		}
	}

	unset($data['stay_start_day']);
	unset($data['stay_end_day']);
	unset($data['addNewStopRecord']);
	unset($data['return_day']);

	//換新部門
	if($old_department != $data['department_id']){
		$team = new Department();
		$tm = $team->select( $data['department_id'] );
		if(count($tm)==0){ $api->denied('Department Id Is Wrong.'); }
		$newTeam = $tm[0];
		$newManager = ($newTeam['manager_staff_id']==0) ? $newTeam['supervisor_staff_id'] : $newTeam['manager_staff_id'];

		//換單位沒給換單位日
		if( empty($data['update_date']) ){
			$data['update_date'] = date('Y-m-d');
		}

		$history->createEvent(['staff_id' => $id, 'status' => $status_id, 'event' => 3, 'event_day' => $data['update_date'], 'department_id' => $department_id, 'post_id' => $post_id, 'title_id' => $title_id]);

		if ($oldHistory = $history->getLogWithTeam($id, $old_department)) {
			$history->updateMain($oldHistory['id'], ['end_time' => $data['update_date']]);
		}
		
		$historyData = [
			'staff_id' => $id,
			'modify_field' => 'department',
			'old' => $old_department,
			'new' => $data['department_id'],
			'start_time' => $data['update_date'],
			'end_time' => '0000-00-00'
		];
		$history->createMain($historyData);
	}

	//如果有更改單位日
	if( !empty($data['update_date']) ){

		//取得換單位日相應的 config
		$new_webconfig_2 = new ConfigCyclical();
		$sp_update = explode('-',$data['update_date']);
		$new_config = $new_webconfig_2->getConfigWithDate($sp_update[0], $sp_update[1], $sp_update[2] );

		//換單位日 考評日期
		if( count($new_config) != 0 ){
			//換單位的一定是員工
			$general = new MonthlyReport();
			//如果沒有換單位  還是找到主管確保資料完整性
			if(!isset($newManager)){
				$team = new Department();
				$tm = $team->select( array('manager_staff_id') , $data['department_id'] );
				$newManager = $tm[0]['manager_staff_id'];
			}

			//如果 換單位的那個時間點上的  報表還沒審核通過 就移到新的主管身上
			$general->update(array(
				"owner_staff_id" => $newManager,
				"owner_department_id" => $data['department_id']
			), array( 'staff_id'=>$id, 'releaseFlag'=>'N', 'year'=>$new_config['year'], 'month'=>$new_config['month'] ) );
		}

	}

	//換新職務類別 或 職務
	if($old_post_id != $post_id || $old_title_id != $title_id){
		$event_day = date('Y-m-d');

		if ($isLeader) {
			$api->denied('Leader Can Not Modify Post And Title.');
		}

		if ($oldHistory = $history->getLogWithPost($id, $old_post_id.'#'.$old_title_id)) {
			$history->updateMain($oldHistory['id'], ['end_time' => $event_day]);
		}
		
		$historyData = [
			'staff_id' => $id,
			'modify_field' => 'post',
			'old' => $old_post_id.'#'.$old_title_id,
			'new' => $post_id.'#'.$title_id,
			'start_time' => $event_day,
			'end_time' => '0000-00-00'
		];
		$history->createMain($historyData);
	}


	//密碼
	if( isset($data['passwd']) ) $data['passwd'] = $staff->inner->getMd5PassWord($data['passwd']);

	//更新
	$result = $staff->updateByAdmin( $data, $id, $self );

	$api->setArray($result);


}else{
	$api->denied();
}

print $api->getJSON();

?>