<?php

include __DIR__."/../../ApiCore.php";
include_once BASE_PATH.'/Model/dbBusiness/Multiple/DepartmentStaffCyclical.php';
include_once BASE_PATH.'/Model/dbBusiness/Multiple/MonthlyReport.php';

$api = new ApiCore($_REQUEST);

use Model\Business\Multiple\DepartmentStaffCyclical;
use Model\Business\Multiple\MonthlyReport;


	if($api->checkPost(array("year","month")) && $api->SC->isLogin() ){

		$year = $api->post('year');
		$month = $api->post('month');
		$department_id = $api->post('department_id');
	
		$report = new MonthlyReport( $api->getPOST() );
		$dc = new DepartmentStaffCyclical($year, $month);
	
		$filter = !!$api->post("release");
	
		if(empty($department_id)){
			if( $api->SC->isAdmin() || $api->SC->isCEO() ){
				$team_id = false;
			}else if($api->SC->isLeader()){
				$manager_id = $api->SC->getId();
				$team_id = $dc->getListIDWithManager($manager_id);
			}else{
				$team_id = $api->SC->getDepartmentId();
			}
		}else{
			$team_id = $department_id;
		}
	
		$forcs_staff = $api->SC->getMember();
		
		$select_staff_id = $api->post('select_staff_id');
		if($team_id==false && $month==0 && empty($select_staff_id)){
			//整年蒐尋 主管一定要對單一員工
			$rgt = ['general'=>[],'leader'=>[]];
		}else if(is_array($team_id)){
			//有跨部門的主管
			$rgt = array('general'=>array(),'leader'=>array());
			foreach($team_id as $t_id){
				$tmp = $report->getTotallyShow($filter, $t_id, $forcs_staff, $select_staff_id );
				$rgt['general'] = array_merge($rgt['general'], $tmp['general']);
				$rgt['leader'] = array_merge($rgt['leader'], $tmp['leader']);
			}
		}else{
			
			$rgt = $report->getTotallyShow($filter,$team_id, $forcs_staff, $select_staff_id );
		}
	
		// $rgt = $report->getTotallyShow();
	
		$result = array(
			'staff' => $rgt['general'],
			'leader' => $rgt['leader']
		);
	
		$api->setArray($result);
	
	}else{
		$api->denied('You Have Not Promised.');
	}




print $api->getJSON();

?>