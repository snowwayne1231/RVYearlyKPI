<?php
namespace Model\Business;

include_once __DIR__.'/Common/DBPropertyObject.php';
include_once __DIR__.'/Common/BaseReport.php';


/**
 * 月考評單 - 管理職
 */
class MonthlyReportLeader extends DBPropertyObject{

	//是否例外
	const EXCEPTION_YES = 1; //是例外
	const EXCEPTION_NO = 0; //不是例外

	//實體表 :: 單表
	public $table_name = "rv_monthly_report_leader";

	//欄位
	public $tables_column = Array(
		'id',
		'staff_id',
		'year',
		'month',
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
		'total',
		'comment_count',
		'status',
		'releaseFlag',
		'bonus',
		'processing_id',
		'owner_staff_id',
		'owner_department_id',
		'exception',  // 0  是正常，1為例外當月不計分
		'exception_reason' // 不計分原因
	);

	use BaseReport;

	/**
	 * 依照 進程ID 「批准」月考評單
	 * @param  integer $pid  進程ID
	 * @param  integer $duty 是否為值班單位（預設為否）
	 * @return object
	 */
	public function doneWithProcessingId($pid,$duty=0){
		$this->sql("UPDATE {table} AS b SET
		total = ((b.target*2 + b.quality*2 + b.method*2 + b.error*2 + b.backtrack*2 + b.planning*2 + (b.execute*7/5) + (b.decision*7/5) + (b.resilience*6/5) + b.attendance*2 + b.attendance_members*2) + b.addedValue - b.mistake) ,
		releaseFlag = 'Y'
		where b.processing_id = $pid;");

		$sql_zero = "UPDATE {table}
		SET total = 0
		WHERE total < 0 AND processing_id = $pid";
		$this->sql($sql_zero);
		
		return $this;
	}

	/**
	 * 依照 進程ID 更新月考評資料表
	 * @param  array   $data         要更新的資料
	 * @param  integer $processingId 進程ID
	 * @param  integer $year         年
	 * @param  integer $month        月
	 * @return object
	 */
	public function updateWithProcessingId($data, $processingId, $year=null, $month=null){
		if(!$year) $year = (int)date('Y');
		if(!$month) $month = (int)date('m');

		$updateData = array(
			'owner_staff_id' => $data['owner_staff_id'],
			'owner_department_id' => $data['owner_department_id']
		);
		$this->update($updateData, array('processing_id' => $processingId, 'year' => $year, 'month' => $month));

		return $this;
	}

	/**
	 * 依照 年 月 將月考評的資料備份到 _tmp資料表
	 * @param  integer $year  年
	 * @param  integer $month 月
	 * @return true
	 */
	public function copyTmpData($year, $month){
		$this->sql('CREATE TABLE IF NOT EXISTS {table}_tmp LIKE {table}');

		$arrUPDATE = array();
		$fieldName = '';
		$data = $this->sql("SELECT * FROM {table} WHERE year = $year AND month = $month AND create_date != update_date")->data;
		if(!empty($data)){
			foreach($data as $d){
				$arrUPDATE[] = "('" . implode("', '", $d) . "')";
				if(count($arrUPDATE) == 1){//組合欄位名稱
					$fieldName = "(" . implode(", ", array_map(function ($v, $k) { return $k; }, $d, array_keys($d))) . ")";
				}
			}
			$strUPDATE = implode(',', $arrUPDATE);
			$this->sql("INSERT INTO {table}_tmp $fieldName VALUES $strUPDATE");
		}

		return true;
	}

	/**
	 * 依照 年 月 將被分資料表的成績塞回主資料表
	 * 當 此報告的擁有者與主資料表相同時，才會將成績放回主資料表
	 * @param  string $year  年
	 * @param  string $month 月
	 * @return true
	 */
	public function putScoreWithYM($year, $month){
		$update_date = date('Y-m-d H:i:s');
		$sql = "UPDATE {table} AS main JOIN {table}_tmp AS tmp SET
					main.target = tmp.target,
					main.quality = tmp.quality,
					main.method = tmp.method,
					main.error = tmp.error,
					main.backtrack = tmp.backtrack,
					main.planning = tmp.planning,
					main.execute = tmp.execute,
					main.decision = tmp.decision,
					main.resilience = tmp.resilience,
					main.attendance = tmp.attendance,
					main.attendance_members = tmp.attendance_members,
					main.addedValue = tmp.addedValue,
					main.mistake = tmp.mistake,
					main.total = tmp.total,
					main.create_date = tmp.create_date,
					main.update_date = '$update_date'
				WHERE main.staff_id = tmp.staff_id
					AND main.owner_staff_id = tmp.owner_staff_id
					AND main.year = '$year' AND main.month = '$month'";
		$this->sql($sql);
		$sql = "UPDATE {table} AS main JOIN {table}_tmp AS tmp SET
					main.exception = tmp.exception,
					main.exception_reason = tmp.exception_reason,
					main.create_date = tmp.create_date,
					main.update_date = '$update_date'
				WHERE main.staff_id = tmp.staff_id
					AND main.owner_staff_id = tmp.owner_staff_id
					AND main.year = '$year' AND main.month = '$month'
					AND main.exception != 1 AND main.exception_reason != '留停'";
		$this->sql($sql);
		return true;
	}

	/**
	 * 清空備份資料表
	 * @return true
	 */
	public function delTmpData(){
		$data = $this->sql("SELECT 1 FROM {table}_tmp")->data;
		if(!empty($data)){//有資料才要清空
			$this->sql('TRUNCATE TABLE {table}_tmp');
		}
		return true;
	}
}
?>
