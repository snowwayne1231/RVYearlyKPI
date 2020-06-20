<?php

include __DIR__."/../ApiCore.php";
include BASE_PATH.'/Model/dbBusiness/Multiple/DepartmentStaffCyclical.php';

use Model\Business\Multiple\DepartmentStaffCyclical;
use Model\Business\Multiple\Leadership;


$api = new ApiCore($_POST);

if( $api->SC->isLogin() && ($api->SC->isAdmin() || $api->SC->isLeader()) ){

	$model = $api->post('model');
	if(!$model) $model='department';

	if($api->checkPost(array('year', 'month'))){
		$year  = $api->post('year');
		$month = $api->post('month');
	}else{
		$year = (int)date('Y');
		$month = (int)date('m');
	}

	$self_id = $api->SC->getId();

	$dsc = new DepartmentStaffCyclical($year, $month);
	$my_leadership = new Leadership($self_id);
	$staff = $dsc->getStaff();

	$col = $staff->invertColumn(array('passwd','is_leader','is_admin','rank'));
	if($model=='department'){

		$sc_sub_team = $api->SC->getSubDepartmentId(true);
		$dsc_sub_team= $dsc->getListIDWithManager($api->SC->getId());
		$sub_team = array_merge($sc_sub_team, $dsc_sub_team);
		$sub_team = array_unique($sub_team);

		$result = $staff->getOnDutyWithTeam( join(',',$sub_team), $col );

		// 去掉同單位主管
		$self_department_id = $api->SC->getDepartmentId();
		$i = 0;
		while ($i < count($result)) {
			$loc = $result[$i];
			if($loc['department_id']==$self_department_id){
				if (!$my_leadership->isMyUnderStaff($loc['id'])) {
					array_splice($result,$i,1);
					continue;
				}
			}
			$i += 1;
		}

	}else if($model=='admin' && $api->SC->isAdmin()){

		$result = $staff->select( $col, [], 'order by rank desc, staff_no asc' );

	}else{
		//只能找到 職等比自己低的
		$result = $staff->getOnDutyWithRank( ((int)$api->SC->getMember()['rank'])-1 , $col );
	}

	//去掉自己
	foreach($result as $i => &$val){
		if($val['id']==$self_id){
			array_splice($result,$i,1);break;
		}
	}

	// LG($result);
	//成功結果
	$api->setArray($result);

}else{
	$api->denied('You Have Not Promised.');
}

print $api->getJSON();

?>