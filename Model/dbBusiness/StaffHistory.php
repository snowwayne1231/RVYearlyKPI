<?php
namespace Model\Business;

include_once __DIR__.'/Common/DBPropertyObject.php';

class StaffHistory extends DBPropertyObject{

	//實體表 :: 單表
	public $table_name = "rv_staff_history";

	//欄位
	public $tables_column = Array(
		'staff_id',
		'modify_field',
		'old',
		'new',
		'start_time',
		'end_time',
		'create_date',
		'update_date'
	);

	public function __construct($db=null){
		parent::__construct($db);
	}

	//override
	public function read($a=null,$b=0,$c=null){
		$order = isset($c)?$c:' order by id desc';
		return parent::read($a,$b,$order);
	}
	//override
	public function select($a=null,$b=0,$c=null){
		$order = isset($c)?$c:' order by id desc';
		return parent::select($a,$b,$order);
	}


	/**
	 * 依照所傳入的日期區間，過濾該期間內在留停的人員
	 * @param  string $startDate 起始日期
	 * @param  string $endDate   非必填，結束日期
	 * @return obj
	 */
	public function filterOnStay($startDate='', $endDate=''){

		if($startDate != '') $startDate = strtotime($startDate);
		if($endDate != '') $endDate = strtotime($endDate);

		$tmp = array();
		$tmp_filter = $this->select(['staff_id', 'start_time', 'end_time'], ['modify_field' => 'status', 'new' => 5]);

		if($startDate != '' || $endDate != ''){
			//取得指定區間的留停人員
			foreach($tmp_filter as $val){
				$stayStartDay = strtotime($val['start_time']);
				$stayEndDay   = strtotime($val['end_time']);

				//留停區間不在查詢區間的，跳過不處理。
				if($stayStartDay < $startDate && $stayEndDay < $startDate) continue;
				if($stayStartDay > $endDate && $stayEndDay > $endDate) continue;

				//計算留停天數
				$workDays = $this->dateDiff($endDate, $startDate);
				if($stayStartDay <= $startDate && $stayEndDay >= $endDate){//留停時間超過查詢區間
					$workDays = 0;
					$stayDays = $this->dateDiff($endDate, $startDate);
				}else if($stayStartDay >= $startDate && $stayEndDay <= $endDate){//留停時間在查詢區間之內
					$stayDays = $this->dateDiff($stayEndDay, $stayStartDay);
					$workDays = $workDays - $stayDays;
				}else if($stayEndDay >= $endDate){//留停結束時間在查詢區間之後
					$stayDays = $this->dateDiff($endDate, $stayStartDay);
					$workDays = $workDays - $stayDays;
				}else if($stayStartDay <= $startDate){//留停開始時間在查詢區間之前
					$stayDays = $this->dateDiff($stayEndDay, $startDate);
					$workDays = $workDays - $stayDays;
				}

				$tmp[$val['staff_id']] = array(
					'staff_id' => $val['staff_id'],
					'stay_start_day' => $val['start_time'],
					'stay_end_day' => $val['end_time'],
					'work_days' => $workDays,
					'stayDays' => $stayDays,
				);
			}
		}else{
			//取出各個留停人員的最新留停記錄
			foreach($tmp_filter as $val){
				if(empty($tmp[$val['staff_id']])) {
					$tmp[$val['staff_id']] = array(
						'stay_start_day' => $val['start_time'],
						'stay_end_day' => $val['end_time']
					);
				}
			}
		}

		return $tmp;
	}


	public function dateDiff($startDate, $endDate){
		return round(($startDate - $endDate)/3600/24) + 1;
	}

}
?>
