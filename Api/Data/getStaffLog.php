<?php

include __DIR__."/../ApiCore.php";
include BASE_PATH.'/Model/dbBusiness/Multiple/StaffHistory.php';
include BASE_PATH.'/Model/dbBusiness/StaffStatus.php';
include BASE_PATH.'/Model/dbBusiness/StaffPost.php';
include BASE_PATH.'/Model/dbBusiness/StaffTitleLv.php';
include BASE_PATH.'/Model/dbBusiness/StaffEvent.php';
include BASE_PATH.'/Model/dbBusiness/Department.php';

use Model\Business\Multiple\StaffHistory;
use Model\Business\StaffStatus;
use Model\Business\StaffPost;
use Model\Business\StaffTitleLv;
use Model\Business\StaffEvent;
use Model\Business\Department;

$api = new ApiCore($_POST);

if( $api->SC->isLogin() && $api->SC->isAdmin() ){

	$id = $api->post('id');
	if(!$id) $api->denied('No Such Staff.');

	$history = new StaffHistory();
	$status = new StaffStatus();
	$post = new StaffPost();
	$title = new StaffTitleLv();
	$event = new StaffEvent();
	$team = new Department();

	$result = array(
		'status' => array(),
		'post' => array(),
		'department' => array(),
		'events' => array(),
	);
	$post_map = $post->map();
	$title_map = $title->map();
	$status_map = $status->map();
	$event_map = $event->map();

	//20181102 Carmen 先限制只顯示留停相關事件
	$resMain = $history->selectMain([], ['staff_id' => $id, 'modify_field' => 'status', 'new' => '5']);
	//$resMain = $history->selectMain([], ['staff_id' => $id]);
	foreach($resMain as $m){
		switch($m['modify_field']){
			case 'status':// 在職狀態
				$result['status'][] = array(
					'id' => $m['id'],
					'status_id' => $m['new'],
					'status' => $status_map[$m['new']]['name'],
					'start' => $m['start_time'],
					'end' => $m['end_time']
				);
				break;
			case 'post':// 任職職務
				$tmp = explode('#', $m['new']);
				$post_id = isset($tmp[0]) ? $tmp[0] : 0;
				$title_id = isset($tmp[1]) ? $tmp[1] : 0;
				$result['post'][] = array(
					'id' => $m['id'],
					'post_id' => $post_id,
					'post' => $post_map[$post_id]['name'],
					'title_id' => $title_id,
					'title' => $title_map[$title_id]['name'],
					'start' => $m['start_time'],
					'end' => $m['end_time']
				);
				break;
			case 'department':// 任職單位
				$result['department'][] = array(
					'id' => $m['id'],
					'departments_id' => $team->getUpperArray($m['new']),
					'start' => $m['start_time'],
					'end' => $m['end_time']
				);
				break;
		}
	}

	//20181102 Carmen 先限制只顯示留停相關事件
	$resEvent = $history->selectEvent([], ['staff_id' => $id, 'event' => ['1', '4', '5', '6', '7']]);
	//$resEvent = $history->selectEvent([], ['staff_id' => $id]);
	foreach($resEvent as $m){
		$result['events'][] = array(
			'id' => $m['id'],
			'event_id' => $m['event'],
			'event' => $event_map[$m['event']]['name'],
			'status_id' => $m['status'],
			'status' => $status_map[$m['status']]['name'],
			'departments' => $team->getUpperArray($m['department_id']),
			'post_id' => $m['post_id'],
			'post' => $post_map[$m['post_id']]['name'],
			'title_id' => $m['title_id'],
			'title' => $title_map[$m['title_id']]['name'],
			'date' => $m['event_day'],
			'note' => $m['note'],
			'created_at' => $m['create_date'],
			'updated_at' => $m['update_date']
		);
	}

	$api->setArray($result);//成功結果

}else{
	$api->denied();
}

print $api->getJSON();
?>