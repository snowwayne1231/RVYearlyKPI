<?php

include __DIR__."/../ApiCore.php";
include BASE_PATH.'/Model/dbBusiness/Multiple/DepartmentStaffCyclical.php';
//include BASE_PATH.'/Model/dbBusiness/Staff.php';
//include BASE_PATH.'/Model/dbBusiness/Department.php';


// $time_start = microtime(true);
$api = new ApiCore($_REQUEST);

use Model\Business\Multiple\DepartmentStaffCyclical;
//use Model\Business\Staff;
//use Model\Business\Department;

if($api->checkPost(array('year', 'month'))){
	$year  = $api->post('year');
	$month = $api->post('month');
}else{
	$year = (int)date('Y');
	$month = (int)date('m');
}

$dc = new DepartmentStaffCyclical($year, $month);

if($api->SC->isAdmin()){
	$data = $dc->getLowerArray('1', true);
}else if($api->SC->isLeader()){
	$data = array();
	$department_list = $dc->getListWithManager($api->SC->getId(), true);
	// dd($department_list);
	foreach($department_list as $d_value){
		$data = array_merge($data, $dc->getLowerArray($d_value['id'], true));
	}
}else{
	$data = $dc->getLowerArray($api->SC->getDepartmentId(), true);
}

$api->setArray($data);

/*
$department = new Department();
$department_map = $department->read(array('enable'=>1))->map();

if($api->SC->isAdmin()){
	$data = $department->getLowerArray('1', true);
}else if($api->SC->isLeader()){
	$data = array();
	$department_list = $department->getListWithManager($api->SC->getId());
	foreach($department_list as $d_id){
		$data += $department->getLowerArray($d_id, true);
	}
}else{
	$data = $department->getLowerArray($api->SC->getDepartmentId(), true);
}

$api->setArray($data);
 */

/*
$department = new Department();
$department_map = $department->read(array('enable'=>1))->map();

if($api->SC->isAdmin()){
	$data = $department->getLowerArray('1', true);
}else{
	$data = $department->getLowerArray($api->SC->getDepartmentId(), true);
}

$api->setArray($data);
 */

print $api->getJSON();

?>