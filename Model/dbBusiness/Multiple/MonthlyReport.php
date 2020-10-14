<?php
namespace Model\Business\Multiple;

include_once __DIR__.'/_MultipleSets.php';
include_once __DIR__.'/../MonthlyProcessing.php';
include_once __DIR__.'/../MonthlyReport.php';
include_once __DIR__.'/../MonthlyReportLeader.php';
include_once __DIR__.'/../RecordMonthlyReport.php';
include_once __DIR__.'/../RecordPersonalComment.php';
include_once __DIR__.'/../Staff.php';
include_once __DIR__.'/../StaffHistory.php';
include_once __DIR__.'/../Department.php';
include_once __DIR__.'/../ConfigCyclical.php';

use \Exception;
use \Model\Business\MonthlyProcessingEvaluating;
use \Model\Business\DepartmentLeadership;

class MonthlyReport extends MultipleSets{
	const REPORT_TYPE_LEADER = 1;
	const REPORT_TYPE_STAFF = 2;
	protected $team;

	protected $staff;
	protected $staffHistory;

	protected $general;

	protected $leader;

	protected $process;

	protected $year;

	protected $month;

	protected $pid;

	protected $condition;

	protected $reportData;

	protected $record;

	protected $comment;

	protected $config_cyc;

	protected $config;

	protected $processEvaluating;

	protected $departmentLeadership;

	public function __construct( $set=array() ){

		$this->config_cyc = new \Model\Business\ConfigCyclical();
		$this->process = new \Model\Business\MonthlyProcessing();
		$this->general = new \Model\Business\MonthlyReport();
		$this->leader = new \Model\Business\MonthlyReportLeader();
		$this->staff = new \Model\Business\Staff();
		$this->staffHistory = new \Model\Business\StaffHistory();
		$this->team = new \Model\Business\Department();
		$this->record = new \Model\Business\RecordMonthlyReport();
		$this->comment = new \Model\Business\RecordPersonalComment();
		$this->processEvaluating = new MonthlyProcessingEvaluating();
		$this->departmentLeadership = new DepartmentLeadership();

		if(isset($set['year']) && isset($set['year'])){
			$this->year       = $set['year'];
			$this->month      = $set['month'];
			$this->condition = array("year"=>$set['year'],"month"=>$set['month']);
			$this->config = $this->config_cyc->getConfigWithDate( $this->year, $this->month );
		}

		return $this;
	}

	public function getReport(){

		$this->general->read( $this->condition );

		$this->leader->read( $this->condition );

		$this->data = array_merge($this->general->data , $this->leader->data);

		return $this->data;
	}


	public function getReportWithProcess($id,$staff_id,$department_id){
		$collect = array();
		if( count($id)>0 ){
			$pidstr = ' in ('.join(',',$id).')';
		}else{ return null;}
		$pro_sql = $this->getFantasyProcessSQL("where main.id $pidstr");
		$processData = $this->process->sql($pro_sql)->map();

		$eva_pdata = $this->getEvaluatingProcess($pidstr, $department_id);
		// dd($data);

		$shunt = $this->process->shuntIdWithType();
		if(isset($shunt[1])){
			$leader_id = join(',',$shunt[1]);
			$sql= $this->getReportSQL($this->leader->table_name," processing_id in ($leader_id) ");
			$leader = $this->leader->sql($sql)->map('processing_id',false,true);
		}else{
			$leader = array();
		}
		if(isset($shunt[2])){
			$general_id = join(',',$shunt[2]);
			$sql2= $this->getReportSQL($this->general->table_name," processing_id in ($general_id) ");
			$general = $this->general->sql($sql2)->map('processing_id',false,true);
		}else{
			$general = array();
		}


		foreach($processData as $pid => &$val){
			// 組合設定區間
			$val['_interval'] = array( 'start'=>$this->config_cyc->getLastDate($val['year'],$val['month'],$val['day_start']), 'end'=>$this->config_cyc->getThisDate($val['year'],$val['month'],$val['day_end']) );
			// 組合該單的月報表
			$val['_reports'] = isset($leader[$pid])? $leader[$pid] : (isset($general[$pid]) ? $general[$pid] : array() );
			foreach($val['_reports'] as &$sub_val){
				// convert to array
				$sub_val['comment_id'] = array_unique(bomb($sub_val['comment_id']));
				$sub_val['_comment_count'] = count($sub_val['comment_id']);
			}
			// convert to array
			// $val['path_staff_id'] = json_decode($val['path_staff_id']);
			// 組合
			$val['_path_staff'] = $this->getStaffWithPath($val['path_staff_id']);
			
			$eva_processing_data = [];
			foreach ($eva_pdata as $ploc) {
				if ($ploc['staff_id'] == $staff_id) {
					$eva_processing_data = $ploc;
					break;
				}
			}
			$val['_authority'] = $this->getAuthority($val, $staff_id, $department_id, $eva_processing_data);
			// $val['_authority'] = $this->getAuthority($val,$eva_pdata);
			$val['_owner_department_leader_number'] = count($eva_pdata);
			$collect[$pid] = $val;
		}

		$this->data = &$collect;
		return $this->data;
	}

	public function getStaffWithPath(&$ary, $should_be_order= false){
		if(!is_array($ary)){$ary = json_decode($ary);}
		$join = join(',',$ary);
		$map = array();
		if(count($ary)>0){
			// $map = $this->staff->read(array('department_id','name','name_en','id'),"where id in ($join)")->map();
			$team = $this->team->table_name;
			$columns = " a.department_id, a.name, a.name_en, a.id, b.name as department_name, b.unit_id as department_code";
			$joinState = " {table} as a LEFT JOIN $team AS b ON a.department_id = b.id ";

			if ($should_be_order) {
				$map = $this->staff->sql("SELECT $columns FROM $joinState WHERE a.id IN ($join) ORDER BY b.lv ASC, b.unit_id ASC ")->data;
			} else {
				$map = $this->staff->sql("SELECT $columns FROM $joinState WHERE a.id IN ($join)")->map();
			}
		}
		return $map;
	}

	public function getDepartmentWithPath(&$ary){
		if(!is_array($ary)){$ary = json_decode($ary);}
		$join = join(',',$ary);
		if(count($ary)>0){
			$map = $this->team->read(array('lv','name','unit_id','id'),"where id in ($join)")->map();
		}else{
			$map = array();
		}
		return $map;
	}

	public function getTotallyShow($release=false, $manager_team_id_id=false, $member_data=[], $select_staff_id=0){

		$leader = $this->leader->table_name;
		$general = $this->general->table_name;
		$config = $this->config;
		$year = $this->year;
		$month = $this->month;

		$collect = array();
		if($release){$release=" and main.releaseFlag='Y' ";}else{$release=' ';}


		if($manager_team_id_id){    //被限制觀看部門的
			$manager_team_id_id = (int) $manager_team_id_id;
			if(empty($member_data['id'])){$this->error('Not Found Member.');}
			$my_id = $member_data['id'];
			
			// $teams[] = $manager_team_id_id;

			if($member_data['is_admin'] ==1) {	// 管理者看該部分

				$lower_teams = $this->team->read()->getLowerIdArray($manager_team_id_id, true);

				$staffwhere = " and ( staff.department_id in (".(join(',',$lower_teams)).") ) ";

			} else if($member_data['is_leader']==1 && $month>0) { //要看所有下屬的

				$lower_teams = $this->team->read()->getLowerIdArray($manager_team_id_id);

				if (count($lower_teams) > 0) {
					$staffwhere = " and ( staff.department_id in (".(join(',',$lower_teams)).") or ( staff.department_id = $manager_team_id_id and staff.is_leader = 0 ) or (staff.id = $my_id) ) ";
				} else {
					$staffwhere = " and ( (staff.department_id = $manager_team_id_id and staff.is_leader = 0) or (staff.id = $my_id) ) ";
				}

			} else {    //看個人
				if(empty($select_staff_id) || $select_staff_id==$my_id){ //是自己
					$staffwhere = " and main.staff_id = $my_id ";
				}else{
					$staffwhere = " and main.staff_id = $select_staff_id ";
				}
			}

		}else{
			$staffwhere = !empty($select_staff_id) && $month==0 ? "and main.staff_id=$select_staff_id" : '';
		}

		$monthWhere = $month==0? '' : "and main.month = $month";
		$where = "main.year = $year $monthWhere $release $staffwhere ";

		$sql= $this->getReportSQL($leader,$where);
		$collect['leader'] = $this->leader->DB->doSQL($sql);


		$sql2= $this->getReportSQL($general,$where);

		$collect['general'] = $this->general->DB->doSQL($sql2);

		$report_type_id_array = array();

		$comment_id_array = array();
		$pointNeedle = array('1'=>array(),'2'=>array());
		// stamp();
		// dd($collect['general']);
		// dd($config);
		foreach($collect['leader'] as $key=>&$val){
			$val['_total_score'] = $this->mathLeaderScore($val);
			if($val['releaseFlag']=='Y' && $val['_total_score']!=$val['total']){  $this->parseReleaseTotal($val);  }

			$this_config = $month==0 ? $config[$val['month']] : $config;
			$stayList = $this->staffHistory->filterOnStay($this_config['RangeStart'], $this_config['RangeEnd']);
			$val['_work_day'] = $this->getOnWorkDays( $this_config['RangeStart'] , $this_config['RangeEnd'] , $val['first_day'], $val['last_day'] );
			if(isset($stayList[$val['staff_id']])){
				$val['_work_day'] = $val['_work_day'] - $stayList[$val['staff_id']]['stayDays'];
				if($val['_work_day'] < 0) $val['_work_day'] = 0;
			}

			$report_type_id_array[] = '"1-'.$val['id'].'"';
			$val['comment_id'] = array_unique(bomb($val['comment_id']));
			$val['_comment_count'] = count($val['comment_id']);
			if($val['_comment_count']>0){ $comment_id_array = array_merge($comment_id_array,$val['comment_id']); }
			$pointNeedle[1][$val['id']] = &$val;
		}

		foreach($collect['general'] as $key=>&$gval){
			if( $gval['duty_shift'] > 0 ){
				$gval['_total_score'] = $this->mathCSITScore($gval);
			}else{
				$gval['_total_score'] = $this->mathGeneralScore($gval);
			}
			if($gval['releaseFlag']=='Y' && $gval['_total_score']!=$gval['total']){  $this->parseReleaseTotal($gval);  }

			$this_config = $month==0 ? $config[$gval['month']] : $config;
			$stayList = $this->staffHistory->filterOnStay($this_config['RangeStart'], $this_config['RangeEnd']);

			$gval['_work_day'] = $this->getOnWorkDays( $this_config['RangeStart'] , $this_config['RangeEnd'] , $gval['first_day'], $gval['last_day'] );
			if(isset($stayList[$gval['staff_id']])){
				$gval['_work_day'] = $gval['_work_day'] - $stayList[$gval['staff_id']]['stayDays'];
				if($gval['_work_day'] < 0) $gval['_work_day'] = 0;
			}
			$report_type_id_array[] = '"2-'.$gval['id'].'"';
			$gval['comment_id'] = array_unique(bomb($gval['comment_id']));
			$gval['_comment_count'] = count($gval['comment_id']);
			if($gval['_comment_count']>0){ $comment_id_array = array_merge($comment_id_array,$gval['comment_id']); }
			$pointNeedle[2][$gval['id']] = &$gval;
		}
		// LG($comment_id_array);
		//20171212 紀錄沒用到
		/*if(count($report_type_id_array)>0){
			$record = $this->record->select(
				array('operating_staff_id','report_id','report_type','changed_json','update_date'),
				'where CONCAT(report_type,"-",report_id) in('.join(',',$report_type_id_array).')'
			);
			foreach($record as &$val){
				$pointNeedle[ $val['report_type'] ][ $val['report_id'] ][ '_changed_record' ][] = $val;
			}
		}*/


		if(count($comment_id_array)>0){
			$staff_table = $this->staff->table_name;
			// $comments = $this->comment->select( 'where id in('.join(',',$comment_id_array).')' );
			$comments = $this->comment->sql( "select main.content, main.create_time, main.report_type, main.report_id, b.name as _create_staff_name, b.name_en as _create_staff_name_en, b.account as _create_staff_account
			from {table} as main
			left join $staff_table as b on main.create_staff_id = b.id
			where main.id in(".join(',',$comment_id_array).") and main.status = 1" )->data;
			// LG($comments);
			foreach($comments as &$val){
				$pointNeedle[ $val['report_type'] ][ $val['report_id'] ][ '_comments' ][] = $val;
			}
		}



		// LG($collect);

		$this->data = $collect;
		return $this->data;
	}

	private function getReportSQL($table,$where=' '){
		$staff = $this->staff->table_name;
		$team = $this->team->table_name;
		return " SELECT main.*,
		staff.name, staff.name_en, staff.first_day, staff.staff_no , staff.post, staff.title, staff.last_day,
		team_s.name as unit_name, team_s.unit_id, team_s.duty_shift,
		team_m.name as owner_unit_name, team_m.unit_id as owner_unit_id,
		lv.name as title,
		post.name as post
		from $table as main
		left join $staff as staff on main.staff_id = staff.id
		left join $team as team_s on staff.department_id = team_s.id
		left join $team as team_m on main.owner_department_id = team_m.id
		left join rv_staff_title_lv as lv on main.title_id = lv.id
		left join rv_staff_post as post on main.post_id = post.id
		where $where
		order by main.month asc, team_s.unit_id asc, staff.rank desc, staff.staff_no ";
	}

	private function getFantasyProcessSQL($process_where){
		return "SELECT main.*,
		b.name as created_department_name , b.unit_id as created_department_code,
		c.name as created_staff_name, c.name_en as created_staff_name_en,
		c.staff_no as created_staff_no, c.post as created_staff_post,
		d.day_start, d.day_end
		from {table} as main
		left join ".$this->staff->table_name." as c on main.created_staff_id = c.id
		left join ".$this->team->table_name." as b on main.created_department_id = b.id
		left join ".$this->config_cyc->table_name." as d on main.year = d.year and main.month = d.month
		$process_where";
	}

	private function getEvaluatingProcess($in_pidstr, $department_id) {
		// $sql = "SELECT a.id, a.staff_id FROM {table} as a
		// LEFT JOIN ".$this->staff->table_name." as s on a.staff_id = s.id
		// LEFT JOIN ".$this->team->table_name." as t on s.department_id = t.id
		// WHERE t.id = $department_id AND a.processing_id $in_pidstr";
		// return $this->processEvaluating->sql($sql);
		return $this->processEvaluating->select(['id', 'staff_id', 'status_code'],['processing_id'=>$in_pidstr, 'staff_department_id'=> $department_id]);
	}

	private function getAuthority($process_data, $staff_id, $department_id, $process_data_eva){
		$team_manager_id = $this->team->select(['manager_staff_id'], $department_id)[0]['manager_staff_id'];
		$has_prev_owner = $process_data['prev_owner_staff_id'] > 0;
		// $is_create = $this->process->isOnCreateDepartment($department_id,$process_data);
		// $is_owner_department = $this->process->isOwnerDepartment($department_id,$process_data);
		$is_owner = $this->process->isOwner($team_manager_id,$process_data);
		$eva_commited = $this->processEvaluating->isSubmited($process_data_eva);

		
		$relative = $this->process->isRelation($team_manager_id,$process_data,true);
		$done = $this->process->isDone($process_data);
		//$launch = $this->process->isLaunch($process_data);
		$is_pre_owner = $this->process->isPreOwner($team_manager_id,$process_data);

		$config = $this->config_cyc->getConfigWithDate( $process_data['year'], $process_data['month'] );
		$launch = $config['monthly_launched'];
		return array(
			// 'is_creator' => $creator,
			'commit' => $launch && $is_owner && !$done && !$eva_commited,
			'editor' => $is_owner && !$done && !$eva_commited,
			'return' => $has_prev_owner && $is_owner && !$done,
			'comment' => $relative && !$done,
			// 'drawing' => $is_pre_owner && !$done && !$owner,
			'drawing' => false,
		);
	}

	private function mathLeaderScore($loc){
		$score = 0;

		$score += $loc['target']*2;
		$score += $loc['quality']*2;
		$score += $loc['method']*2;
		$score += $loc['error']*2;
		$score += $loc['backtrack']*2;
		$score += $loc['planning']*2;

		$score += $loc['execute']*7/5;
		$score += $loc['decision']*7/5;
		$score += $loc['resilience']*6/5;

		$score += $loc['attendance']*2;
		$score += $loc['attendance_members']*2;
		if($score>100){$score=100;}

		$score += $loc['addedValue'];
		$score -= $loc['mistake'];
		$score = $score<0?0:$score;
		return round($score);
	}

	private function mathCSITScore($loc){
		$score = 0;

		$score += $loc['quality']*5;
		$score += $loc['completeness']*5;
		$score += $loc['responsibility']*3;
		$score += $loc['cooperation']*3;
		$score += $loc['attendance']*4;
		if($score>100){$score=100;}

		$score += $loc['addedValue'];
		$score -= $loc['mistake'];
		$score = $score<0?0:$score;
		return round($score);
	}

	private function mathGeneralScore($loc){
		$score = 0;

		$score += $loc['quality']*5;
		$score += $loc['completeness']*5;
		$score += $loc['responsibility']*5;
		$score += $loc['cooperation']*3;
		$score += $loc['attendance']*2;
		if($score>100){$score=100;}

		$score += $loc['addedValue'];
		$score -= $loc['mistake'];
		$score = $score<0?0:$score;
		return round($score);
	}

	private $skill_map=[
	'target'=>1, 'quality'=>1, 'method'=>1, 'error'=>1, 'backtrack'=>1,
	'planning'=>1, 'execute'=>1, 'decision'=>1, 'resilience'=>1,'attendance'=>1, 'attendance_members'=>1,
	'quality'=>2, 'completeness'=>2, 'responsibility'=>2, 'cooperation'=>2, 'attendance'=>2,
	'addedValue'=>3, 'mistake'=>3
	];
	private function parseReleaseTotal(&$o){
		if((int)$o['total']!=(int)$o['_total_score']){
			if($o['processing_id']<0){
				foreach($this->skill_map as $k=>$v){
					$o[$k]='-';
				};
			}
			$o['_total_score']=$o['total'];
		}
	}

	private function getOnWorkDays($start,$end,$first,&$last){
		$ed = $this->DateTime($end,true);
		$st = $this->DateTime($start,true);
		$ft = $this->DateTime($first,true);
		$la = $this->DateTime($last,true);
		if($la){
			if($la>=$st){
				$final = min($ed,$la);
			}else{
				return 0;
			}
		}else{
			$final = $ed;
			$last='--';
		}
		$start = max($st,$ft);
		$gap = $final - $start;
		return (int)($gap/86400)+1;
	}

	protected function get_team(){
		return $this->team;
	}
	protected function get_staff(){
		return $this->staff;
	}
	protected function get_process(){
		return $this->process;
	}
	protected function get_creator(){
		return $this->creator;
	}
	protected function get_supervisor(){
		return $this->supervisor;
	}

	/**
	 * 修改 月績效不記分人員-主管
	 * @modifyDate 2017-09-29
	 * @param      int                         $report_id 月績效
	 * @param      int                         $exception 1:例外，0:正常
	 * @param      string                      $reason    原因
	 * @return     array                                  修改結果
	 */
	public function updateMonthlyNoScore($report_type, $report_id, $exception, $reason) {

		switch ($report_type) {
			case self::REPORT_TYPE_LEADER:
				$model = $this->leader;
				break;
			case self::REPORT_TYPE_STAFF:
				$model = $this->general;
				break;
			default:
				throw new \Exception("Error Report Type, Please Input Correct Report Type", 1);
				break;
		}
		if (!in_array($exception, [$model::EXCEPTION_NO,$model::EXCEPTION_YES])) {
			throw new \Exception("Please Input Correct Exception. ", 1);
		}
		//檢查有沒有這筆績效
		$record = $model->select($report_id);
		if (!$record) {
			throw new \Exception("Please Input Validate Report Id.", 1);
		}
		$update = [
			'exception' => $exception,
			'exception_reason' => $reason,
			'update_date' => date('Y-m-d H:i:s'),
		];
		$model->update($update, $report_id);
		return ['status' => true];
	}

	/**
	 *  取得個人詳細表現
	 */
	public function getDetailMonthlyByPerson($year_set, $month_set, $target_staff_id, $operating_staff_id, $operating_department_id, $isAdmin=false){
		$res = [];
		$team_map = $this->team->cmap();
		$staff_map = $this->staff->read(['id','staff_no','name','name_en','department_id','title','post','status','first_day', 'is_leader'],[])->cmap();
		if(isset($staff_map[$target_staff_id])){
			$target_staff = $staff_map[$target_staff_id];
		}else{
			$this->error('Not Found Staff.');
		}
		$team_lowerIds = $this->team->getLowerIdArray($operating_department_id);
		$is_allowed = false;
		
		// 是否能看
		if (in_array($target_staff['department_id'], $team_lowerIds) || $isAdmin || $operating_staff_id == $target_staff_id) {
			$is_allowed = true;
		} else if ($team_map[$operating_department_id]['manager_staff_id']==$operating_staff_id) {
			if ($staff_map[$target_staff_id]['is_leader'] == 0) {
				$is_allowed = true;
			}
		} else {
			$all_leaders = [];
			$leaderships = $this->departmentLeadership->select(['id'], ['department_id'=> $operating_department_id, 'staff_id'=> $operating_staff_id, 'status'=> 1]);
			// dd($staff_map[$target_staff_id]);
			if (count($leaderships) >0 && $staff_map[$target_staff_id]['is_leader'] == 0) {
				$is_allowed = true;
			}
		}
		

		//查詢員工 不是自己單位組織下
		// if( ! && !$isAdmin ){ $this->error('You Can Not Intervene In This Matter.'); }
		if( !$is_allowed ){ $this->error('You Can Not Intervene In This Matter.'); }
		//組 單位
		$target_staff['department_name'] = $team_map[ $target_staff['department_id'] ]['name'];
		$target_staff['department_code'] = $team_map[ $target_staff['department_id'] ]['unit_id'];

		$allYM = [];
		$mm = $month_set[0];$yy = $year_set[0];
		while($mm<=$month_set[1] && $yy<=$year_set[1]){
			$allYM[] = "'$yy-$mm'";
			if(($mm++)>=12){ $mm=1; $yy++; }
		}
		// dd($allYM);
		$YMSTR = join(',',$allYM);
		// dd($target_staff_id);
		$common_col = ['id','year','month','total','releaseFlag','exception','exception_reason','comment_id','addedValue','mistake','processing_id'];
		$common_where = "where staff_id = $target_staff_id and CONCAT(year,'-',month) in($YMSTR)";
		$baseic_col=[]; $baseic_col['g'] = ['quality','completeness','responsibility','cooperation','attendance'];
		$baseic_col['l'] = ['quality','target','method','error','backtrack','planning','execute','decision','resilience','attendance','attendance_members'];
		$g_col = array_merge($baseic_col['g'],$common_col);
		$l_col = array_merge($baseic_col['l'],$common_col);
		// 取得月報表
		$reports_g = $this->general->select($g_col,$common_where,'order by year asc, month asc');
		$reports_l = $this->leader->select($l_col,$common_where,'order by year asc, month asc');
		$report = array_merge($reports_g,$reports_l);

		//平均分數 & 評論
		$monthly_info = ['addedValue'=>0,'mistake'=>0];
		$all_comment_ary = [];   $analysis=[];  $analysis['l']=['count'=>0,'ary'=>[]];  $analysis['g']=['count'=>0,'ary'=>[]];
		$avg = 0;$avg_count = 0;
		foreach($report as &$rv){
			$all_comment_ary = array_merge($all_comment_ary,$rv['comment_id']);
			if($rv['releaseFlag']=='N'){$rv['total']=0;}else if($rv['exception']==1){continue;}else{$avg_count++;}
			$avg+=$rv['total'];
			if($rv['processing_id']==-1){continue;}   //分數是匯入的  不用進入能力值分析
			$ana_key = isset($rv['target'])?'l':'g';
			$analysis[$ana_key]['count']++;
			foreach($baseic_col[$ana_key] as $col_name){
				if(empty($analysis[$ana_key]['ary'][$col_name])){ $analysis[$ana_key]['ary'][$col_name]=0; }
				$analysis[$ana_key]['ary'][$col_name]+=(int)$rv[$col_name];
			}
			$monthly_info['addedValue']+=$rv['addedValue'];
			$monthly_info['mistake']+=$rv['mistake'];
		}

		$avg = $avg / $avg_count;

		//每月各項能力值
		$monthly_info['average'] = (float)number_format($avg,2);
		foreach($analysis as &$aac){
			foreach($aac['ary'] as &$ana_col){
				$ana_col = (float)number_format($ana_col / $aac['count'] * 20,2);
			}
		}
		// dd($analysis);
		$monthly_info['analysis_leader'] = $analysis['l']['ary'];
		$monthly_info['analysis_normal'] = $analysis['g']['ary'];

		//取得評論
		if(count($all_comment_ary)>0){
			$all_comment_str = join(',',$all_comment_ary);
			$comments = $this->comment->read(['report_id','content','create_staff_id','create_time'],['id'=>"in($all_comment_str)",'status'=>1])->amap('report_id');
		}else{
			$comments = [];
		}
		//出缺席
		$config_date = $this->config_cyc->sql("select day_end, day_start, concat(year,'-',month) as ym from {table} where ( year=".$year_set[0]." and month=".$month_set[0].") or ( year=".$year_set[1]." and month=".$month_set[1].") order by year, month")->cmap('ym');
		$start_ym = $year_set[0].'-'.$month_set[0];
		$end_ym = $year_set[1].'-'.$month_set[1];

		$true_start_ym = $month_set[0]==1 ? ((int)$year_set[0]-1).'-12' : $year_set[0].'-'.((int)$month_set[0]-1);

		$start_date = $true_start_ym.'-'.( isset($config_date[$start_ym]) ? $config_date[$start_ym]['day_start'] : '21' );
		$end_date = $end_ym.'-'.( isset($config_date[$end_ym]) ? $config_date[$end_ym]['day_end'] : '20' );


		$attendance_info = $this->getAttendanceInfoWithStaff($target_staff_id, $start_date, $end_date);


		//組合
		foreach($report as &$rvv){
			if(empty($comments[$rvv['id']])){continue;}
			$crv = $comments[$rvv['id']];
			foreach($crv as &$cv){
				$cv['create_staff_name'] = $staff_map[$cv['create_staff_id']]['name'];
				$cv['create_staff_name_en'] = $staff_map[$cv['create_staff_id']]['name_en'];
			}
			$rvv['_comments'] = $crv;
		}

		$res['staff_info'] = $target_staff;
		$res['monthly_info'] = $monthly_info;
		$res['monthly_every'] = $report;
		$res['attendance_info'] = $attendance_info;

		return $res;
	}


	// 取得個人出缺勤
	public function getAttendanceInfoWithStaff($staff_id, $start_date, $end_date){
		$attendance = new \Model\Business\Attendance();
		$attendance_special = new \Model\Business\AttendanceSpecial();

		$at_data = $attendance->select(['date','checkin_hours','checkout_hours','late','early','remark','work_hours_total','vocation_hours','overtime_hours'],
		"where date between '$start_date' and '$end_date' and staff_id = $staff_id order by date asc");

		$year_s = (int)substr($start_date,0,4);
		$year_e = (int)substr($end_date,0,4);
		// $all_year = [];
		// for($year_s ; $year_s<=$year_e ; $year_s++){
		// 	$all_year[]=$year_s;
		// }
		// $all_year = join(',',$all_year);
		// dd($all_year);
		$at_data_specail = $attendance_special->select(['type','value','year'],
		"where staff_id = $staff_id and year in ($year_e)");

		$conuts = array('late'=>0,'early'=>0,'nocard'=>0,'forgetcard'=>0,'leave'=>0,'paysick'=>0,'physiology'=>0,'sick'=>0,'absent'=>0,'overtime'=>0,'relax'=>0,'other_vt'=>0,'other_ot'=>0,'working'=>[]);
		$conuts['working'] = ['should_hours'=>0,'total_hours'=>0,'total_overtime'=>0,'total_vocation'=>0];
		// $conuts['vacation'] = ['total_hours'=>0,'total_days'=>0];

		//特殊日
		foreach($at_data_specail as $adsp){
			if($adsp['type']==$attendance_special::TYPE_NOCARD){ $conuts['nocard']+=(int)$adsp['value']; }
			if($adsp['type']==$attendance_special::TYPE_FORGETCARD){ $conuts['forgetcard']+=(int)$adsp['value']; }
		}

		//初始化
		$enum_vt = new \Model\Enum\VacationType();

		// $t1 = microtime(true);
		// dd($at_data);.

		foreach($at_data as &$atv){
			$atv_vh = (float)(empty($atv['vocation_hours'])?0:$atv['vocation_hours']);
			$atv_oh = (float)$atv['overtime_hours'];
			$wht = (float)$atv['work_hours_total'];
			$shw = $wht + $atv_vh - $atv_oh;
			if($shw>0){ $shw=8; }else{ $shw=0; }   //一天不是正常8小時上班日  ==  假日加班
			$conuts['working']['should_hours'] += $shw;

			// $conuts['working']['total_hours'] += (float)$atv['work_hours_total'];
			$conuts['working']['total_hours'] += ($shw==0) ? ($atv_oh-$atv_vh):($shw - $atv_vh + $atv_oh);
			$conuts['working']['total_vocation']+=$atv_vh;
			$conuts['overtime']+=$atv_oh;

			$rm = trim($atv['remark']);
			if(empty($rm)){
				$conuts['late']+= (int)$atv['late']>0?1:0;
				$conuts['early']+= (int)$atv['early']>0?1:0;
				if($atv['early']+$atv['late']==0){continue;}  //正常上班
			}else{
				$enum_vt->itName($rm);
				$hidden = $enum_vt->isHide();

				if($enum_vt->one){

					if($enum_vt->isWork()){ $conuts['working']['total_hours'] += $atv_vh; }
					$c_key = $enum_vt->getKey();
					if(isset($conuts[$c_key])){$conuts[$c_key]+= $atv_vh;}

				}else if($atv_vh>0){

					$vt_times = $enum_vt->getTime($atv_vh);
					if(empty($vt_times)){ continue;/*$this->error( 'Wrong Date = '.$atv['date'] );*/ }
					foreach($vt_times as $vv_vh){
						if($vv_vh['work']){ $conuts['working']['total_hours'] += $vv_vh['time']; }
						if(isset($conuts[$vv_vh['key']])){$conuts[$vv_vh['key']] += $vv_vh['time'];}
					}
					// dd($vt_times);
				}

				if($hidden){continue;}
			}

			unset($atv['late']);
			unset($atv['early']);

			$exception_date[]= $atv;
		}
		// $t2 = microtime(true);
		// dd($t2-$t1);

		return ['basic'=>$conuts,'exception_date'=>$exception_date];
	}

}
?>
