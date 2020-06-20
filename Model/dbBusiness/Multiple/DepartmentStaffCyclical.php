<?php
namespace Model\Business\Multiple;

include_once __DIR__.'/_MultipleSets.php';
include_once __DIR__.'/../ConfigCyclical.php';
include_once __DIR__.'/../MonthlyProcessing.php';
include_once __DIR__.'/../Department.php';
include_once __DIR__.'/../Staff.php';
include_once __DIR__.'/../StaffHistory.php';


use \Exception;
use \Model\Business\DepartmentLeadership;

/**
 * 取得指定時間區間的 部門 & 職員 狀態
 */
class DepartmentStaffCyclical extends MultipleSets{

	protected $cyc_config;  // 考評期間設定 (裡面有各個月份的組織設定)
	protected $processing;  // 進程設定 (有當月份，各 staff 在哪個組織的紀錄)
	protected $department;  // 目前 department 資料表
	protected $staff;       // 目前 staff 資料表
	protected $staffHistory;// staff 歷史資料表

	protected $year; // 要查詢的年份
	protected $month;// 要查詢的月份

	public static $Staff = "_staff";
	public static $Manager = "_manager";

	protected $nowTeamsData = array();//目前的部門資料
	protected $departmentsData = array();
	protected $staffsData = array();
	protected $departmentLeadership;


	public function __construct($year=null, $month=null, $froce_refresh=false){
		if(empty($year)) $year = (int)date('Y');
		if(empty($month)) $month = (int)date('m');

		$this->cyc_config = new \Model\Business\ConfigCyclical($year, $month);
		$this->processing = new \Model\Business\MonthlyProcessing();
		$this->department = new \Model\Business\Department();
		$this->staff = new \Model\Business\Staff();
		$this->staffHistory = new \Model\Business\StaffHistory();
		$this->departmentLeadership = new DepartmentLeadership();

		$this->$year  = $year;
		$this->$month = $month;

		//將部門資料組進該年月的組織架構中
		$teamsData = $this->department->read(array('enable'=>1))->map();
		if (!$this->cyc_config->hasConstructs() || $froce_refresh) {
			$staff_map = $this->staff->map();
			$this->cyc_config->updateConstructsByTeamAndStaff($teamsData, $staff_map);
		}
		$this->nowTeamsData = $teamsData;
		$this->departmentsData = $this->cyc_config->parseConstructsByTeam($teamsData);
		$this->adjUpperIds();
		$this->department->data = $this->departmentsData;

	}

	// 上層單位路徑
	private function adjUpperIds() {
		$data = $this->departmentsData;
		foreach($data as &$val){
			$id = $val['id'];
			$val['path_upper_department_ids'] = $this->department->getUpperIdArray( $id );
		}
		$this->departmentsData = $data;
	}


	//按照部門編號排序
	private static function sortByUnitID($a, $b){
		if($a['unit_id'] == $b['unit_id']) return 0;
		return ($a['unit_id'] > $b['unit_id']) ? 1 : -1;
	}


	/**
	 * 將員工組合到各個部門裡
	 * @return array  組合好的資料
	 */
	public function collect(){
		$this->filterStaffOnDuty();
		$stayStaff = $this->filterStaffOnStay();
		$stayStaffID = array();
		foreach($stayStaff as $val){
			if($val['work_days'] <= 0) {
				$stayStaffID[] = $val['staff_id'];
			}
		}

		$departmentsData = $this->departmentsData;
		$staffsData= $this->staffsData;

		$processingData = $this->processing->read(array('created_staff_id','created_department_id'), array('year'=>$this->year, 'month'=>$this->month))->map('created_staff_id');

		$staffKey  = self::$Staff;
		$managerKey= self::$Manager;

		//將 staffsData 的資料塞進 departmentsData
		foreach($staffsData as $staffID => &$staffValue){
			if(in_array($staffID, $stayStaffID) && $staffValue['status_id'] == 5) continue;//去掉留停中的職員
			$departmentID = isset($processingData[$staffID]) ? $processingData[$staffID]['created_department_id'] :$staffValue['department_id'];

			$staffValue['exception'] = isset($stayStaff[$staffID]) ? 1 : 0;
			$staffValue['exception_reason'] = isset($stayStaff[$staffID]) ? '留停' : '';

			if(isset($departmentsData[$departmentID])){
				$departments = &$departmentsData[$departmentID];
				if(isset($departments[ $staffKey ])){
					$departments[$staffKey][$staffID] = $staffValue;
				}else{
					$departments[$staffKey] = array($staffID => $staffValue);
				}
			}
		}

		//將 manager 的資料塞進 departmentsData
		$o_key = $this->department->origin;
		foreach($departmentsData as $departmentID => &$departmentValue){
			$manager_id = $departmentValue['manager_staff_id'];
			if( isset($staffsData[ $manager_id ]) ){
				$departmentValue[$managerKey] = $staffsData[$manager_id];
			}
			$position = $departmentValue[$o_key];
		}

		$this->data = $departmentsData;

		return $this->data;
	}


	//算出部門員工數
	public function countStaff($id, $no_leader=true){
		if(empty($this->data)) $this->collect();
		$staffKey = self::$Staff;
		$data = $this->data;

		$department = $data[$id];

		if(!isset($department[$staffKey])) return 0;

		$counts = count($department[$staffKey]);
		$manager_id = $department['manager_staff_id'];
		if(isset($department[$staffKey][$manager_id]) && $no_leader){
			$counts--;
		}

		return $counts;
	}

	//算出下層部門主管數
	public function countSubLeader($id){
		if(empty($this->data)) $this->collect();
		$data = $this->data;

		$total = 0;

		$arrayLowerID = $this->getLowerIdArray($id);
		foreach($arrayLowerID as $departmentID){
			if(!empty($data[$departmentID]['manager_staff_id'])){
				$total++;
			}
		}

		return $total;
	}

	/**
	 * 篩選出在職的員工
	 * @return array 在職的員工資料
	 */
	public function filterStaffOnDuty(){
		$config = $this->cyc_config->data;

		$this->staff->read(array('id','staff_no','title_id','post_id','name','name_en','lv','status_id','first_day','last_day','department_id','email','is_admin', 'is_leader') ,'')->map();
		$this->staff->filterOnDuty( $config['RangeStart'], $config['RangeEnd'] );
		$this->staffsData = $this->staff->data;

		return $this->staffsData;
	}

	/**
	 * 篩選出在留停中的員工
	 * @return array 留停中的員工ID
	 */
	public function filterStaffOnStay(){
		$config = $this->cyc_config->data;
		return $this->staffHistory->filterOnStay( $config['RangeStart'], $config['RangeEnd'] );
	}

	//用主管 取得上層單位佬大
	public function getSuperArrayWithManager($manager_id, $end_id=0, $filter_self=false){
		return $this->department->getSuperArrayWithManager($manager_id, $end_id, $filter_self);
	}

	/**
	 * 取得下層部門ID
	 * @param  integer $id        部門ID
	 * @param  boolean $includeMe 是否要回傳包含自己部門的ID
	 * @return array              下層部門ID
	 */
	public function getLowerIdArray($id, $includeMe = false){
		if(!is_int($id)) $id = (int)$id;
		return $this->department->getLowerIdArray($id, $includeMe);
	}

	/**
	 * 取得下層部門的資料
	 * @param  integer $id        部門ID
	 * @param  boolean $includeMe 是否要回傳包含自己部門的資料
	 * @return array              下層部門的資料
	 */
	public function getLowerArray($id, $includeMe = false){
		if(!is_int($id)) $id = (int)$id;
		$return = array();
		$lower = $this->getLowerIdArray($id, $includeMe);

		foreach($lower as $lower_id){
			foreach($this->departmentsData as $value){
				if($value['id'] == $lower_id){
					$return[] = $value;
					break;
				}
			}
		}

		usort($return, array(get_class($this), 'sortByUnitID'));

		return $return;
	}


	//取得指定管理職，擔任哪些部門的主管
	public function getListWithManager($staff_id, $include_leadership = false){
		$manager_id = $staff_id;
		if ($include_leadership) {

			$department_id = $this->staff->find(['department_id'], $staff_id);
			if (is_int($department_id)) {
				$manager_id = $this->department->find(['manager_staff_id'], $department_id);
			}
		}
		$result = $this->department->getListWithManager($manager_id);
		return $result;
	}

	//取得指定管理職，擔任哪些部門的主管 (只要ID)
	public function getListIDWithManager($staff_id){
		$return = array();

		$list = $this->getListWithManager($staff_id);
		foreach($list as $value){
			$return[] = $value['id'];
		}
		if (count($return) == 0) {
			$staff_data = $this->staff->select(['department_id'], $staff_id)[0];

			$leaderships = $this->departmentLeadership->select(['id'], ['department_id'=>$staff_data['department_id'], 'staff_id'=>$staff_id, 'status'=>1]);
			if (count($leaderships)>0) {
				$return[] = $staff_data['department_id'];
			}
		}
		return $return;
	}

	public function getNowTeamsData(){
		return $this->nowTeamsData;
	}

	public function getStaffsData(){
		return $this->staffsData;
	}

	public function getConfigCyclical(){
		return $this->cyc_config;
	}



	public function getStaff(){
		return $this->staff;
	}
}