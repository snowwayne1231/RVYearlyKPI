<?php
namespace Model\Business;

include_once __DIR__.'/Common/DBPropertyObject.php';

/**
 * 月考評週期設定
 */
class ConfigCyclical extends DBPropertyObject{

	//實體表 :: 單表
	public $table_name = "rv_config_cyclical";

	protected $year;

	protected $month;

	//欄位
	public $tables_column = Array(
		'id',
		'year',
		'month',
		'day_start',
		'day_end',
		'day_cut_off',
		'monthly_launched',
		'constructs',
		'update_date'
	);

	public function __construct($y=null, $m=null){
		parent::__construct();
		if($y && $m) $this->getConfigWithDate($y,$m);
	}

	private function collect(){
		foreach($this->data as &$val){
			$val['RangeEnd'] = $this->getThisDate($val['year'],$val['month'],$val['day_end']);
			$val['RangeStart'] = $this->getLastDate($val['year'],$val['month'],$val['day_start']);
		}
		return $this->data;
	}

	public function getLastDate($y,$m,$day){
		return ( ($m==1) ? ($y-1)."-12-" : $y."-".($m-1)."-" ).$day;
	}

	public function getThisDate($y,$m,$day){
		return "$y-$m-$day";
	}

	/**
	 * 依照 年月日 取得該月份的組織設定 & 考評設定
	 * @param  string $year  日
	 * @param  string $month 月
	 * @param  string $day   非必填，日期
	 * @return array         組織設定 & 考評設定
	 */
	public function getConfigWithDate($year, $month, $day=null){
		$this->year = $year;
		$this->month = $month;
		if( !isset($day) ){
			if(empty($month)){
				$codition = array("year"=>$year);
			}else{
				$codition = array("year"=>$year,"month"=>$month);
			}

			$data = $this->select( $codition );
			if( count($data)==0){
				if(empty($codition['month'])) $codition['month']=1;
				$this->add( $codition );
				sleep(0.1);
				$data = $this->select( $codition );
			}
		}else{
			$this->read("where DATE('$year-$month-$day') BETWEEN DATE(concat(year,'-',month-1,'-',day_start)) and DATE(concat(year,'-',month,'-',day_end))");
		}

		$this->collect();

		foreach($this->data as &$d){
			if( isset($d['constructs']) ){ $d['constructs']=json_decode($d['constructs'],true); }
		}

		if(count($this->data) == 1 && $month > 0){
			$this->data = $this->data[0];
			$res = $this->data;
		}else{
			$res = $this->map('month');
		}

		return $res;
	}

	/**
	 * 是否啟動考評
	 * @return boolean 判斷結果
	 */
	public function isLaunch(){
		$il = false;
		if( isset($this->data['monthly_launched']) && $this->data['monthly_launched']==1 ){
			$il=true;
		}
		return $il;
	}

	/**
	 * 更新當月組織關係
	 * @param  array  $team 部門資料
	 * @return object
	 */
	public function updateConstructsByTeamAndStaff($team_map, $staff_map){
		$tmp = [];
		
		foreach($team_map as $v){
			$team_id = $v['id'];
			$supervisor_id = $v['supervisor_staff_id'];
			$manager_id = $v['manager_staff_id'];
			$team_upper_id = $v['upper_id'];

			$staff = [];
			$staff_leaders = [];
			foreach ($staff_map as $s) {
				$staff_id = $s['id'];
				if ($s['department_id'] == $team_id) {
					$staff[] = $staff_id;
					if ($s['is_leader'] == 1) {
						$staff_leaders[] = $staff_id;
					}
				}
			}

			$tmp[] = [$team_id, $supervisor_id , $manager_id, $team_upper_id, $staff_leaders, $staff];
		}
		$c = $this->update(['constructs'=>$tmp],$this->data['id']);
		if($c>0){$this->data['constructs']=$tmp;}
		return $this;
	}

	/**
	 * 解析當月組織關係
	 * @param  array $team_id_map 部門資料
	 * @return array
	 */
	public function parseConstructsByTeam($team_id_map){
		$tmp = [];
		foreach($this->data['constructs'] as $c){
			$id = $c[0];
			if(empty($team_id_map[$id])){continue;}
			$team = $team_id_map[$id];
			$team['supervisor_staff_id'] = $c[1];
			$team['manager_staff_id'] = $c[2];
			$team['upper_id'] = $c[3];
			$team['staff_leaders'] = isset($c[4]) ? $c[4] : [];
			$team['staff_ids'] = isset($c[5]) ? $c[5] : [];
			$tmp[$id] = $team;
		}
		return $tmp;
	}

	public function hasConstructs() {
		return !empty($this->data['constructs']);
	}

}
?>
