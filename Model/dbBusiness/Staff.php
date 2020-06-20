<?php
namespace Model\Business;

include_once __DIR__.'/Common/DBPropertyObject.php';

class Staff extends DBPropertyObject{
	//員工等級
	const STAFF_RANK_ASSIGMENT = 0; //助理
	//是否為管理者
	const ISADMIN_YES = 1;  //是
	const ISADMIN_NO = 0;  //否

	//狀態
	const STATUS_ENABLE = 1;  //正式
	const STATUS_PARTTIME = 2;  // 約聘
	const STATUS_TRIAL = 3;  // 試用
	const STATUS_LEAVE = 4;  // 離職
	const STATUS_STAY = 5;  // 留停

	//快選
	const UNIT_TABLE_NAME = 'rv_department';

	//實體表 :: 單表
	public $table_name = "rv_staff";

	//狀態
	public static $status = Array(
		1 => '正式',
		2 => '約聘',
		3 => '試用',
		4 => '離職',
		5 => '留停',
	);

	//欄位
	public $tables_column = Array(
		'id',
		'staff_no',
		'title',
		'title_id',
		'post',
		'post_id',
		'name',
		'name_en',
		'account',
		'passwd',
		'email',
		'lv',
		'first_day',
		'last_day',
		'update_date',
		'status',
		'status_id',
		'department_id',
		'is_leader',
		'is_admin',
		'rank' //0=工讀, 1=轉正/助理, 2=其他職員, 3~5=一般人員, 6=組長, 7~8=處長, 9~12=部門主管,  13=決策人員
	);

	private $hash_key = 'rv123kpi456';
	private $data_filter;

	//override
	public function read($a=null,$b=0,$c=null){
		$order = isset($c)?$c:' order by rank desc , staff_no asc ';
		return parent::read($a,$b,$order);
	}

	//override
	public function select($a=null,$b=0,$c=null){
		$order = isset($c)?$c:' order by rank desc , staff_no asc ';
		return parent::select($a,$b,$order);
	}

	/**
	 * 依照所傳入的日期區間，過濾該期間內在職的人員
	 * @param  string $startDate 起始日期
	 * @param  string $endDate   非必填，結束日期
	 * @return obj
	 */
	public function filterOnDuty($startDate, $endDate=''){
		$startDate = $this->DateTime($startDate, true);
		$endDate   = $this->DateTime($endDate, true);
		$tmp = array();
		$tmp_filter = $this->data;
		foreach($tmp_filter as $i => $val){
			// if($val["status_id"]==4){continue;}
			$first = $this->DateTime($val["first_day"], true);
			$last  = $this->DateTime($val["last_day"], true);
			if($last){
				if($last < $startDate) continue;
			}else if($val["status_id"] == 4){//離職
				continue;
			}else if($endDate && $first && $first > $endDate){
				continue;
			}
			$tmp[$val['id']] = $val;
			//array_push($tmp,$val);
			unset($tmp_filter[$i]);
		}
		$this->data = $tmp;
		$this->data_filter = $tmp_filter;

		return $this;
	}

	/**
	 * 依照 部門ID 取得 當月 在職的人員
	 * @param  integer $team_id 部門ID
	 * @param  array   $col     所需欄位
	 * @return array   搜尋結果
	 */
	public function getOnDutyWithTeam($team_id,$col=null){
		// return $this->select( $col, array("status_id"=>"<>4","department_id"=>"in($team_id)") );
		$date1 = $this->getDate('-1 month');
		$date2 = $this->getDate('+1 month');
		return $this->select( $col, "where department_id in ($team_id) and ( (last_day between '$date1' and '$date2') or status_id <> 4)" );
	}

	/**
	 * 依照 職等 取得 當月 在職的人員
	 * @param  integer $rank 職等
	 * @param  array   $col     所需欄位
	 * @return array   搜尋結果
	 */
	public function getOnDutyWithRank($rank,$col=null){
		$date1 = $this->getDate('-1 month');
		$date2 = $this->getDate('+1 month');
		return $this->select( $col, "where rank <= $rank and ( (last_day between '$date1' and '$date2') or status_id <> 4)" );
	}

	private function getDate($order){
		$today = date('Y-m-d');
		return date('Y-m-d', strtotime( $today.$order));
	}

	/**
	 * 取得管理者Email
	 * @modifyDate 2017-11-6
	 * @return array Email
	 */
	private $cache_aue;
	public function getAdminUserEmail() {
		if(isset($this->cache_aue)) return $this->cache_aue;

		$admin_staff = $this->select(['id', 'email'], ['is_admin' => self::ISADMIN_YES, 'status_id' => 'in('.implode(',', [self::STATUS_ENABLE, self::STATUS_PARTTIME, self::STATUS_TRIAL ]).')']);
		$email_ary = array_column($admin_staff, 'email');
		$this->cache_aue = $email_ary;
		return $email_ary;
	}

	/**
	 * 依照搜尋條件取得 Email
	 * @param  array $w 搜尋條件
	 * @return array Email
	 */
	public function getEmailByWhere($w){
		$staffs = $this->select(['email'], $w);
		$email_ary = array_column($staffs, 'email');
		return $email_ary;
	}

	//快選員工 顯示用
	public function mergeDepartmentForShow( $staff_id, $addtion_a_col=[], $addtion_b_col=[] ){
		$basic_a_col = ['id', 'staff_no', 'name', 'name_en'];
		$basic_b_col = ['unit_id', 'name as department_name'];
		$department_table = self::UNIT_TABLE_NAME;
		if(!empty($addtion_a_col)){
			$basic_a_col = array_merge($basic_a_col,$addtion_a_col);
		}
		if(!empty($addtion_b_col)){
			$basic_b_col = array_merge($basic_b_col,$addtion_b_col);
		}
		foreach($basic_a_col as &$av){ $av = 'a.'.$av; }
		foreach($basic_b_col as &$bv){ $bv = 'b.'.$bv; }
		$basic_a_col = join(',',$basic_a_col);
		$basic_b_col = join(',',$basic_b_col);
		if(is_array($staff_id)){
			$staff_where = 'a.id in('.join(',',$staff_id).')';
		}else{
			$staff_where = "a.id = $staff_id";
		}
		$sql = "SELECT $basic_a_col, $basic_b_col
				FROM {table} AS a LEFT JOIN $department_table AS b ON a.department_id = b.id
				WHERE $staff_where ORDER BY rank DESC, staff_no";
		return $this->sql($sql);
	}

	public function login($name,$pwd){
		$md5_pass_word = $this->getMd5PassWord($pwd);
		$ary = $this->select(
			array('id','account','department_id','first_day','is_leader','is_admin','lv','name','name_en','post','rank','staff_no','status','status_id','title','update_date'),
			array('staff_no'=>$name,'passwd'=>$md5_pass_word)
		);
		return $ary;
	}

	public function getMd5PassWord($pwd){
		return md5($pwd.$this->hash_key,false);
	}

	/*20181116 Carmen 沒有用到，先拿掉
	protected function get_rdata(){
		return $this->data_filter;
	}
	*/
}
?>
