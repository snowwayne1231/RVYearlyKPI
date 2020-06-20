<?php

include __DIR__."/../ApiCore.php";
include BASE_PATH.'/Model/dbBusiness/Multiple/Staff.php';
include BASE_PATH.'/Model/dbBusiness/Multiple/StaffHistory.php';
include BASE_PATH.'/Model/dbBusiness/ConfigCyclical.php';
use Model\Business\Multiple\Staff;
use Model\Business\Multiple\StaffHistory;
use Model\Business\ConfigCyclical;

$api = new ApiCore($_POST);

if( $api->checkPost(array('department_id','staff_no','title_id','post_id','account','passwd','email','status_id')) && $api->isAdmin() ){

	//當月評分
	$webconfig = new ConfigCyclical();
	$year = date("Y");
	$month = date("m");
	$config = $webconfig->getConfigWithDate($year,$month);
	if( $api->isPast($config['RangeEnd']) ){

	}else{
		//當月已經起動
		if($config['monthly_launched']==1){ $api->denied('Can Not Modify Staff When Monthly Launch.'); }
	}


	$data = $api->getPOST();

	if( empty($data['department_id']) || empty($data['staff_no']) || empty($data['title_id']) || empty($data['post_id']) || empty($data['account']) || empty($data['passwd']) || empty($data['email']) || empty($data['status_id']) ){
		$api->denied('Wrong Param, Has Empty Param.');
	}

	if(empty($data['first_day'])){$data['first_day']=date('Y-m-d');}
	// if(empty($data['update_date'])){$data['update_date']=date('Y-m-d');}
	// LG($data);
	$staff = new Staff();

	//檢查員編
	if(!preg_match('/^[a-zA-Z]{1}[\d]{2,4}$/',$data['staff_no'])){ $api->denied('Staff_no No Match Format.'); }
	//檢查mail
	if(!preg_match('/^[\w\d\_\.]+\@.+$/',$data['email'])){ $api->denied('Email No Match Format.'); }

	$count = $staff->select( array('staff_no'=>$data['staff_no']) );
	//員編不能重複
	if(count($count)==0){

		$data['passwd'] = $staff->getMd5PassWord( $data['passwd'] );

		$admin_id = $api->SC->getId();
		$new_staff = $staff->admission( $data, $admin_id );

		//新增歷史記錄
		$history = new StaffHistory();

		//新增歷史記錄 - 狀態
		$history->createMain([
			'staff_id' => $new_staff[0]['id'],
			'modify_field' => 'status',
			'old' => 0,
			'new' => $new_staff[0]['status_id'],
			'start_time' => $new_staff[0]['first_day'],
			'end_time' => '0000-00-00'
		]);

		//新增歷史記錄 - 職務
		$history->createMain([
			'staff_id' => $new_staff[0]['id'],
			'modify_field' => 'post',
			'old' => 0,
			'new' => $new_staff[0]['post_id'].'#'.$new_staff[0]['title_id'],
			'start_time' => $new_staff[0]['first_day'],
			'end_time' => '0000-00-00'
		]);

		//新增歷史記錄 - 部門
		$history->createMain([
			'staff_id' => $new_staff[0]['id'],
			'modify_field' => 'department',
			'old' => 0,
			'new' => $new_staff[0]['department_id'],
			'start_time' => $new_staff[0]['first_day'],
			'end_time' => '0000-00-00'
		]);

		//新增歷史事件記錄 - 到職
		$history->createEvent([
			'staff_id' => $new_staff[0]['id'],
			'status' => $new_staff[0]['status_id'],
			'event' => 1, 'event_day' => $new_staff[0]['first_day'],
			'department_id' => $new_staff[0]['department_id'],
			'post_id' => $new_staff[0]['post_id'],
			'title_id' => $new_staff[0]['title_id']
		]);

		$api->setArray($new_staff);

	}else{
		$api->denied('Double Staff_no');
	}


}else{
	// var_dump($_POST);
}

print $api->getJSON();

?>