<?php

include __DIR__."/../ApiCore.php";
//include BASE_PATH.'/Model/dbBusiness/ConfigCyclical.php';
include BASE_PATH.'/Model/dbBusiness/Multiple/ProcessReport.php';
include BASE_PATH.'/Model/dbBusiness/Multiple/DepartmentStaffCyclical.php';

// $time_start = microtime(true);
$api = new ApiCore($_REQUEST);

//use Model\Business\ConfigCyclical;
use Model\Business\Multiple\ProcessReport;
use Model\Business\Multiple\DepartmentStaffCyclical;
use Model\Business\Multiple\MonthlyReportLeadershipEvaluating;

if($api->checkPost(array("year","month")) || $api->checkPost(array("check"))){

	$year  = $api->post('year'); //integer 年份
	$month = $api->post('month');//integer 月份
	$check = $api->post('check');//bool 是否要產生 報表, 考評表
	$del   = $api->post('del');  //bool 是否要刪除

	if($check){
		if(!$year) $year = (int)date('Y');
		if(!$month) $month = (int)date('m');
	}

	if ($check || $del) {
		$dsc = new DepartmentStaffCyclical($year, $month, true);
	} else {
		$dsc = new DepartmentStaffCyclical($year, $month);
	}

	//取得該月的設定值
	$cyc_config = $dsc->getConfigCyclical();
	$config = $cyc_config->data;

	// if( empty($config['constructs']) );

	/* //自動檢查下一個月
	if($check && $api->isPast( $config['RangeEnd'] ) && !$api->isToday( $config['RangeEnd'] ) ){
		if($month==12){
			$year+=1;
			$month=1;
		}else{
			$month+=1;
		}
		$config = $cyc_config->getConfigWithDate( $year, $month );
	}
	*/

	//當下，考評期間已經開始
	if($api->isPast( $config['RangeStart'])){

		//依考評天數，計算考評結束日期
		$realRangeEnd = strtotime($config['RangeEnd'] . ' +' . $config['day_cut_addition'] . ' day');

		//取得真正結束考評的日期
		$finalEndDate = $realRangeEnd > strtotime($config['cut_off_date']) ? date('Y-m-d',$realRangeEnd) : $config['cut_off_date'];

		//是否在考評區間裡
		$isInRange = $api->isFuture( $finalEndDate ) || $api->isToday( $finalEndDate );
		
		$pr = new ProcessReport($year, $month);
		$mrle = new MonthlyReportLeadershipEvaluating($year, $month);

		if($del && $check && $api->SC->isAdmin() && $isInRange){

			$ym = array('year'=>$year,'month'=>$month);

			//將成績複製一份
			$pr->general->copyTmpData($year, $month);
			$pr->leader->copyTmpData($year, $month);

			//刪除月考評單
			$pr->process->delete($ym);
			$pr->general->delete($ym);
			$pr->leader->delete($ym);

			//初始化讀取資料
			$mrle->delete();
			$pr->initRead($ym);
		}

		$teamsData = $dsc->collect();//取得部門+部門職員資料
		$staffsData= $dsc->getStaffsData();//取得在職員工

		$Staff_key  = DepartmentStaffCyclical::$Staff;  //一般職員關鍵字
		$Manager_key= DepartmentStaffCyclical::$Manager;//管理者關鍵字
		$mapDepartmentToLeadership = $mrle->getLeadershipMap();


		//更新/檢查 月報表
		if( $api->SC->isAdmin() && $isInRange && ($check || $del) ){

			$emptyTeams = array();//沒有人的部門

			//產生月績效考評單
			foreach($teamsData as $loc){
				$manager_id    = $loc['manager_staff_id'];//部門管理者ID
				$super_id      = $loc['supervisor_staff_id'];//上層管理者ID
				$team_id       = $loc['id'];//部門ID
				$super_team_id = $staffsData[ $super_id ][ 'department_id' ];//上層部門ID

				$staffCount = $dsc->countStaff($team_id);//部門人數（不包含管理者）
				$hasManager = ($manager_id) ? true : false;//有無主管
				$hasStaff   = ($staffCount) ? true : false;//有無員工

				$real_manager_id = ($hasManager) ? $manager_id : $super_id;//實際的管理者ID
				$this_team_leaderships = isset($mapDepartmentToLeadership[$team_id]) ? $mapDepartmentToLeadership[$team_id] : [];

				//沒主管又沒員工
				if( (!$hasManager && !$hasStaff) ){
					$emptyTeams[] = $team_id;//將部門ID紀錄下來
					continue;//跳過後續處理
				}

				$subLeaderCount = $dsc->countSubLeader($team_id);//計算下層部門主管人數
				$superLeaderArray = $dsc->getSuperArrayWithManager($real_manager_id);//取得上層單位佬大

				//有主管，要檢查並建立單位主管的考評單
				if ($hasManager) {
					$staff = $loc[ $Manager_key ];//array 主管資料
					$pr->checkLeaderReport($manager_id, $super_id, $team_id, $super_team_id, $staff);
					
				}

				//有員工
				if($hasStaff){
					$staffs = $loc[ $Staff_key ];//array 員工資料

					//分開 管理職 和 一般職務
					$staffLeaders = array();
					$staffGenerals= array();
					foreach($staffs as $staff_id => $staff_value){
						if (in_array($staff_id, $this_team_leaderships)) {
							$staffLeaders[$staff_id] = $staff_value;
						} else {
							$staffGenerals[$staff_id] = $staff_value;
						}
						// if($staff_value['lv'] > 0 && $staff_value['lv'] <= 4){
						// 	$staffLeaders[$staff_id] = $staff_value;
						// }else{
						// 	$staffGenerals[$staff_id] = $staff_value;
						// }
					}

					//檢查並建立管理職的考評單
					if(!empty($staffLeaders)){
						foreach($staffLeaders as $leader_id => $leader_value){
							$pr->checkLeaderReport($leader_id, $super_id, $team_id, $super_team_id, $leader_value);
						}
					}

					//檢查並建立員工的考評單
					if(!empty($staffGenerals)){
						$pr->checkGeneralReport($staffGenerals, $real_manager_id, $team_id);
					}

					//有員工一定是組員對應該單位組長
					$stid = ($hasManager) ? $team_id : $super_team_id;

					//檢查進程是否存在，不存在就建立
					$pr->checkProcessing($real_manager_id, $real_manager_id, $team_id, $stid ,'2', $superLeaderArray);

				}

				//有下層主管，要建立主管的進程
				if($subLeaderCount && $hasManager){
					$pr->checkProcessing($manager_id, $manager_id, $team_id, $team_id ,'1', $superLeaderArray);
				}

			} // for ..count

			$times = $pr->releaseAllInsert();//一次將資料存入DB

			if($times > 0 || $check){
				$pr->updateReportProcessingID();//更新/檢查 月考評單的流程單

				// 檢查評估中表
				$mrle->refreshAllReports();
			}


			if($check && $del){

				//把評論組回到重新建立的單子上
				include_once BASE_PATH.'/Model/dbBusiness/RecordPersonalComment.php';
				$comment = new Model\Business\RecordPersonalComment();
				$comment->refresh($year,$month);

				//將成績塞回去
				$pr->leader->putScoreWithYM($year, $month);
				$pr->general->putScoreWithYM($year, $month);

				//刪除暫存資料表
				$pr->leader->delTmpData();
				$pr->general->delTmpData();
				
			}


			//產生的考評單比 部門數量還多，一定是第一次按，要寄 E-mail
			if($times > count($teamsData) && $check && empty($del)){
				$send_emails = [];
				$admin_emails= [];

				//組合部門管理者的 E-mail
				foreach($teamsData as $send_team){
					if( isset($staffsData[ $send_team['manager_staff_id'] ]) ){
						$send_emails[] = $staffsData[ $send_team['manager_staff_id'] ]['email'];
					}
				}

				if(count($send_emails) > 0){
					//組合系統管理者的 E-mail
					foreach($staffsData as $send_staff){
						if($send_staff['is_admin'] == 1) $admin_emails[] = $send_staff['id'];
					}

					//寄出 E-mail
					$emailer = new \Model\MailCenter();
					$emailer->addAddress($send_emails);
					$emailer->addCC($admin_emails);
					$res = $emailer->sendTemplate('monthly_admin_checkout',array(
						'year' => $year,
						'month' => $month
					));

				}
			}
		}

		if($check){

			$api->setArray('done');

			//儲存修改紀錄
			$self_id = $api->SC->getId();
			$record_data = $api->getPost();
			$record = new \Model\Business\RecordAdmin( $self_id );
			if($del){
				$record->type($record::TYPE_MONTH)->delete( $record_data );
			}else{
				$record->type($record::TYPE_MONTH)->update( $record_data );
			}

		}else{

			//把 process 組合進去
			$processMap = $pr->process->select(array('year'=>$year,'month'=>$month));
			foreach ($processMap as &$processValue) {
				$created_dev = $processValue['created_department_id'];
				$id = $processValue['id'];
				$teamsData[$created_dev]['_processing'][$id]= $processValue;
				/*if(isset($staffsData[$processValue['created_staff_id']])){
					$teamsData[$created_dev]['_manager'] = $staffsData[$processValue['created_staff_id']];
				}else{
					$api->denied('Error For The Leader ID ' . $processValue['created_staff_id'] . ' Is Not Exist.');
					exit;
				}*/
				// $mrle;
			}

			// 把管理層 組合進去
			$mapDepartmentToLeadership = $mrle->getLeadershipMap();
			foreach ($teamsData as $department_id => &$value) {
				$_leadership = [];
				if (isset($mapDepartmentToLeadership[$department_id])) {
					$leader_ids = $mapDepartmentToLeadership[$department_id];
					foreach ($leader_ids as $staff_id) {
						if ($staff_id == $value['manager_staff_id']) continue;
						$staff_data = $staffsData[$staff_id];
						$_leadership[] = $staff_data;
					}
				}
				$value['_leadership_staff'] = $_leadership;
			}

			$api->setArray($teamsData);
		}

	}else{
		//選擇時間還沒到
		$api->denied("Is Not In Time Zoon Date.");

	}

}

print $api->getJSON();

?>