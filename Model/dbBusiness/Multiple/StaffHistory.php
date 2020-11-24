<?php
namespace Model\Business\Multiple;

include_once __DIR__.'/_MultipleSets.php';
include_once __DIR__.'/../StaffHistory.php';
include_once __DIR__.'/../StaffHistoryEvent.php';

use \Exception;

class StaffHistory extends MultipleSets{

	protected $main;
	protected $event;


	public function __construct(){
		$this->main = new \Model\Business\StaffHistory();
		$this->event = new \Model\Business\StaffHistoryEvent();
	}

	public function createStayLog($staffID, $oldStatus, $startTime, $endTime, $department_id, $post_id, $title_id){
		$statusID = 5;

		$this->createEvent(['staff_id' => $staffID, 'status' => $statusID, 'event' => 4, 'event_day' => $startTime, 'note' => $endTime, 'department_id' => $department_id, 'post_id' => $post_id, 'title_id' => $title_id]);
		//$this->createEvent(['staff_id' => $staffID, 'status' => $statusID, 'event' => 5, 'event_day' => $endTime, 'department_id' => $department_id, 'post_id' => $post_id, 'title_id' => $title_id]);

		$historyData = [
			'staff_id' => $staffID,
			'modify_field' => 'status',
			'old' => $oldStatus,
			'new' => $statusID,
			'start_time' => $startTime,
			'end_time' => $endTime
		];
		$this->createMain($historyData);
	}

	public function createMain($set=array()){
		$set['create_date'] = date('Y-m-d H:i:s');
		$set['update_date'] = date('Y-m-d H:i:s');
		return $this->main->create($set);
	}

	public function updateMain($id, $set=array()){
		$set['update_date'] = date('Y-m-d H:i:s');
		return $this->main->update($set, $id);
	}

	public function createEvent($set=array()){
		$set['create_date'] = date('Y-m-d H:i:s');
		$set['update_date'] = date('Y-m-d H:i:s');
		if (empty($set['note'])) {
			$set['note'] = '';
		}
		return $this->event->create($set);
	}

	public function eventSQL($sql){
		return $this->event->sql($sql);
	}

	//依照修改狀態 取得 指定使用者的記錄
	public function getLogWithStatus($staff_id, $status_id){
		$sql = "SELECT id, start_time, end_time FROM {table} WHERE staff_id = $staff_id AND modify_field = 'status' AND new = '$status_id' ORDER BY create_date DESC";
		if($res = $this->main->sql($sql)->data){
			return $res[0];
		}else{
			return false;
		}
	}

	public function getStayWithStaff($staff_id){
		$return = array();
		$sql = "SELECT start_time, end_time FROM {table}
				WHERE staff_id = $staff_id AND `modify_field` = 'status' AND `new` = '5' AND `old` != '5' ORDER BY `create_date` DESC";
		if($resMain = $this->main->sql($sql)->data){
			foreach($resMain as $key => $val){
				$return[$key]['start_day'] = $val['start_time'];
				$return[$key]['end_day'] = $val['end_time'];
			}
		}

		$sql = "SELECT event_day FROM {table} WHERE staff_id = $staff_id AND `event` = '7' ORDER BY `create_date` DESC";
		if($resEvent = $this->event->sql($sql)->data){
			foreach($resEvent as $key => $val){
				$return[$key]['return_day'] = $val['event_day'];
			}
		}

		return $return;
	}

	//依照修改部門 取得 指定使用者的記錄
	public function getLogWithTeam($staff_id, $team_id){
		$sql = "SELECT id, start_time, end_time FROM {table} WHERE staff_id = $staff_id AND modify_field = 'department' AND new = '$team_id' ORDER BY create_date DESC";
		if($res = $this->main->sql($sql)->data){
			return $res[0];
		}else{
			return false;
		}
	}

	//依照修改職務 取得 指定使用者的記錄
	public function getLogWithPost($staff_id, $postTitle){
		$sql = "SELECT id, start_time, end_time FROM {table} WHERE staff_id = $staff_id AND modify_field = 'post' AND new = '$postTitle' ORDER BY create_date DESC";
		if($res = $this->main->sql($sql)->data){
			return $res[0];
		}else{
			return false;
		}
	}

	public function selectMain($col=null, $where=array()){
		return $this->main->select($col, $where, 'ORDER BY id DESC');
	}

	public function selectEvent($col=null, $where=array()){
		return $this->event->select($col, $where, 'ORDER BY create_date DESC');
	}
}