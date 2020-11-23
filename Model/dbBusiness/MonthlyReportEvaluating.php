<?php
namespace Model\Business;

include_once __DIR__.'/Common/DBPropertyObject.php';

/**
 * 月考評單 -評分中
 */
class MonthlyReportEvaluating extends DBPropertyObject{

	//實體表 :: 單表
	public $table_name = "rv_monthly_report_evaluating";

	//欄位
	public $tables_column = Array(
		'id',
		'year',
		'month',
		'staff_id',
		'staff_id_evaluator',
		'evaluator_department_id',
		'report_type',
		'report_id',
		'status_code',
		'submitted',
		'should_count',
		'json_data',
	);

	// 一般人員分數欄位
	public $general_report_column = Array(
		'quality',
		'completeness',
		'responsibility',
		'cooperation',
		'attendance',
		'addedValue',
		'mistake',
		// 'total',
		'bonus',
	);

	// 主管人員分數欄位
	public $leader_report_column = Array(
		'target',
		'quality',
		'method',
		'error',
		'backtrack',
		'planning',
		'execute',
		'decision',
		'resilience',
		'attendance',
		'attendance_members',
		'addedValue',
		'mistake',
		// 'total',
		'bonus',
	);

	/**
	 * 依照報表產生JSON儲存格式
	 */
	public function parseReportScoreJSON($reportData, $type=1){
		$next = [];
		if ($type == 1) {
			foreach ($this->leader_report_column as $col) {
				$next[$col] = isset($reportData[$col]) ? $reportData[$col] : 0;
			}
		} else {
			foreach ($this->general_report_column as $col) {
				$next[$col] = isset($reportData[$col]) ? $reportData[$col] : 0;
			}
		}
		return $next;
	}

	/**
	 * 取得平均分數
	 */
	public function getAvgScore($report_id, $evaluator_department_id, $report_type) {
		$data = $this->select(['report_type', 'json_data'], ['report_id'=> $report_id, 'evaluator_department_id'=> $evaluator_department_id, 'should_count'=> 1]);
		$result = [];
		$length_data = count($data);

		$columns = ($report_type == 1) ? $this->leader_report_column : $this->general_report_column;
		foreach ($columns as $col) {
			$result[$col] = 0;
		}
		
		if ($length_data > 0) {
			// $type = $data[0]['report_type'];
			
			foreach ($data as $loc) {
				$json_data = $loc['json_data'];
				foreach ($columns as $col) {
					$result[$col] += $json_data[$col];
				}
			}
			foreach ($columns as $col) {
				if ($col=='bonus') {
					$result[$col] = $result[$col] == $length_data ? 1 : 0;
				} else if ($col == 'addedValue' || $col == 'mistake') {
					continue;
				} else {
					$result[$col] = round($result[$col] / $length_data);
				}
			}
		}
		return $result;
	}

	//
	public function putScoreWithYM($year, $month) {
		$sql = "UPDATE {table} AS main JOIN {table}_tmp AS tmp ON main.staff_id = tmp.staff_id AND main.staff_id_evaluator = tmp.staff_id_evaluator SET
					main.status_code = tmp.status_code,
					main.submitted = tmp.submitted,
					main.should_count = tmp.should_count,
					main.json_data = tmp.json_data
				WHERE main.staff_id = tmp.staff_id
					AND main.staff_id_evaluator = tmp.staff_id_evaluator
					AND main.year = '$year' AND main.month = '$month'";
		$this->sql($sql);
		return $this;
	}

	//override
	public function select($a=null,$b=0,$c=null){
		parent::select($a,$b,$c);
		return $this->parseJSON()->data;
	}
	//override
	public function read($a=null,$b=0,$c=null){
		parent::read($a,$b,$c);
		return $this->parseJSON();
	}
	//override
	public function sql($a, $bindData= []){
		parent::sql($a, $bindData);
		return $this->parseJSON();
	}
	
	private function parseJSON(){
		if (is_array($this->data)) {
			foreach($this->data as &$val){
				if(isset($val['json_data'])){
					$val['json_data'] = json_decode($val['json_data'], true);
				}
			}
		}
		return $this;
	}

}
?>
