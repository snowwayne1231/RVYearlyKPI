<?php
namespace Model\Business\Multiple;

include_once __DIR__.'/_MultipleSets.php';

use \Exception;
use Model\MailCenter;

use Model\Business\YearPerformanceDivisions;
use Model\Business\YearPerformanceReport;
use Model\Business\YearPerformanceReportTopic;
use Model\Business\YearPerformanceReportTopicType;
use Model\Business\YearPerformanceReportDistributionRate;
use Model\Business\YearPerformanceReportPercents;
use Model\Business\RecordYearPerformanceQuestions;
use Model\Business\RecordYearPerformanceReport;
use Model\Business\RecordYearPerformanceDivisions;
use Model\Business\YearPerformanceFeedback;
use Model\Business\YearPerformanceFeedbackQuestions;
use Model\Business\MonthlyReport;
use Model\Business\MonthlyReportLeader;
use Model\Business\MonthlyProcessing;
use Model\Business\Staff;
use Model\Business\Department;
use Model\Business\ConfigCyclical;
use Model\Business\Attendance;
use Model\Business\Multiple\YearlyQuickly;
/*
所有年度考績相關
*/
class YearlyAssessment extends MultipleSets{

	const DIVISION_CENTER = 1;

	protected $staff;

	protected $team;

	protected $config;
	protected $config_quick;
	protected $report;

	protected $topic;
	protected $topic_type;
	protected $record;
	protected $record_divisions;
	protected $attendance;
	protected $feedback;
	protected $question;
	protected $question_template;  //問題樣版
	protected $monthly_report;
	protected $monthly_report_leader;
	protected $distribution_rate;
	protected $percents;
	protected $divisions;

	private $topic_data;

	private $year;
	private $year_config_data;
	private $year_config_data_ary;  //記錄每年的設定檔

	private $base_distribution_rate = [];
	public function __construct($year = null){
		$this->staff = new Staff();
		$this->team = new Department();

		if( is_numeric($year) ){
			$this->config_quick = new YearlyQuickly($year);
			$this->year = $year;
		}else{
			$this->config_quick = new YearlyQuickly();
		}

		$this->report = new YearPerformanceReport();
		$this->topic = new YearPerformanceReportTopic();
		$this->topic_type = new YearPerformanceReportTopicType();
		$this->attendance = new Attendance();
		$this->record = new RecordYearPerformanceReport();
		$this->record_divisions = new RecordYearPerformanceDivisions();
		$this->feedback = new YearPerformanceFeedback();
		$this->question = new RecordYearPerformanceQuestions();
		$this->question_template = new YearPerformanceFeedbackQuestions();
		$this->monthly_report = new MonthlyReport();
		$this->monthly_report_leader = new MonthlyReportLeader();
		$this->distribution_rate = new YearPerformanceReportDistributionRate();
		$this->percents = new YearPerformanceReportPercents();
		$this->divisions = new YearPerformanceDivisions();

	}

	//檢查/產生 報表
	public function checkA(){
		$result = array();
		// $year = $this->year;

		$this->refreshTopic();

		//這裡要加上檢查 前置作業 就是參考 ，只有問卷要停止才有用
		//1.各月份出缺勤資料是否完整
		// $attendance = $this->checkAttendAnce($config['date_start'],$config['date_end']);
		//2.各月份績效結果是否完整
		// $monthly_report = $this->checkMonthlyProcessing($config);
		//3.部屬回饋問卷是否已停止流程
		$this->checkFeedBackAvaiable();

		$result['change'] = $this->checkAssessment();
		$result['processing'] = $this->config_quick->data['processing'];
		if($result['change']>0){
			$result['status'] = 'OK.';
		}else{
			$result['status'] = 'Nothing Changed.';
		}

		return $result;
	}

	public function getDivisionZone($self_id){
		$year = $this->year;
		$config = $this->config_quick->data;
		$team_table = $this->team->table_name;
		$staff_table = $this->staff->table_name;

		$isCEO = $self_id==$config['ceo_staff_id'];

		$self_where = $isCEO? "" : "and a.owner_staff_id = $self_id ";

		$result = $this->divisions->sql(" select a.id, a.year, a.status, a.processing, a.division, a.owner_staff_id, b.name as division_name, b.unit_id
		from {table} as a left join $team_table as b on a.division = b.id
		where a.year = $year $self_where order by FIELD(a.processing,'6','5') asc , b.unit_id asc")->check('Not Found Division.')->data;
		// dd($result);
		foreach($result as $key => $val){
			$result[$key]['_authority'] = $this->getAuthority_division($val, $self_id);
			if($result[$key]['_authority']['isFinished'] && !$isCEO){unset($result[$key]);}
		}
		$dvs_ary = array();

		$dvs_str = implode(',', array_column($result, 'division'));
		// dd($dvs_str);
		$sql = " select ( a.assessment_total+ a.assessment_total_division_change+ a.assessment_total_ceo_change) as total, a.id, a.division_id, a.department_id, a.owner_staff_id, a.staff_id, a.staff_post, a.staff_title, a.assessment_total, a.assessment_total_division_change, a.assessment_total_ceo_change, a.level, a.assessment_json, a.path, a.path_lv, a.path_lv_leaders, a.upper_comment, a.enable, a.processing_lv, a.own_department_id, 
		b.name as department_name, b.unit_id as department_code, c.name as staff_name, c.name_en as staff_name_en, c.staff_no,
		d.name as owner_staff_name, d.name_en as owner_staff_name_en
		from {table} as a
		left join $team_table as b on a.department_id = b.id
		left join $staff_table as c on a.staff_id = c.id
		left join $staff_table as d on a.owner_staff_id = d.id
		where a.year = $year and a.division_id in($dvs_str) and a.enable = 1 and a.staff_id != $self_id
		order by b.unit_id asc, c.rank desc, c.staff_no asc  ";

		$report = $this->report->sql($sql)->map('division_id',false,true);
		$can_division_additions = true;
		foreach($report as &$div_val){
			foreach($div_val as &$rp_val){
				$rp_val['_authority'] = $this->getAuthority($rp_val, $self_id);
				$rp_val['_status'] = $this->getStatusByReport($rp_val);
			}
		}


			foreach($result as $key => &$division){
				$division['_canfix_division'] = $this->divisions->isCanfixDivision($division) && $division['processing']< YearPerformanceDivisions::PROCESSING_DIRECTOR_COMMIT;
				$division['_canfix_ceo'] = $this->divisions->isCanfixCEO($division) && $division['processing']>= YearPerformanceDivisions::PROCESSING_ARCHITE_WAIT;
				$division['_reports'] = isset($report[$division['division']]) ? $report[$division['division']] : [];
				// //取得每個級距有多少人
				$levelAry = array_column($division['_reports'], 'level');
				$distribution = $this->getGradeDistributionCount($levelAry);
				$division['_distribution'] = $distribution;
				$division['date_start'] = $config['date_start'];
				$division['date_end'] = $config['date_end'];

			}



		return $result;
	}

	public function getAssessment($owner_staff_id) {
		$construct = $this->config_quick->getConstrustAdjLeader();
		$teamLeadersMap = $this->config_quick->getDepartmentMultipleLeadersMap();
		$teamMap = $this->config_quick->getDepartmentMap();
		$is_leader_before = false;
		$staff_ids = array();
		// dd($construct);
		// dd($teamMap);
		foreach($construct as  $cval) {
			$manager_id = $cval['manager_staff_id'];
			$department_leaders = $teamLeadersMap[$cval['id']];
			// $dev_lv = $cval['lv'];
			$staff = $cval['staff'];
			

			// if ($manager_id == $owner_staff_id) {
			if (in_array($owner_staff_id, $department_leaders)) {
				// 自己部門 自己是主管
				$is_leader_before = true;
				foreach($staff as $s) {
					if ($s['is_leader'] == 0 || $s['id'] == $owner_staff_id) {
						array_push($staff_ids, $s['id']);
					}
				}
			} else if ($cval['division_leader_id'] == $owner_staff_id) {
				// 部門層級主管 為自己
				foreach($staff as $s) {
					array_push($staff_ids, $s['id']);
				}
			} else if ($cval['supervisor_staff_id'] == $owner_staff_id) {
				// 沒有主管的單位 或 自己的子單位主管
				
				$upper_id = $cval['upper_id'];
				$upper_department = $teamMap[$upper_id];
				$upper_lv = $upper_department['lv'];

				if ($upper_lv < 2) {
					foreach($staff as $s) {
						if (in_array($s['id'], $department_leaders) || $manager_id == 0) {
							array_push($staff_ids, $s['id']);
						};
					}
				} else {
					foreach($staff as $s) {
						array_push($staff_ids, $s['id']);
					}
				}
				
			} else if($cval['lv'] > 2) {
				// 最後檢查路徑上是否自己是主管
				foreach ($cval['path_department'] as $dep_id) {
					$department_leaders = $teamLeadersMap[$dep_id];
					if (in_array($owner_staff_id, $department_leaders)) {
						foreach($staff as $s) {
							array_push($staff_ids, $s['id']);
						}
					}
				}
			}
		}

		$staff_ids = array_unique($staff_ids);
		// dd($construct);
		// dd($staff_ids);
		if ($is_leader_before) {
			$result = $this->getAssessmentWithLeader($owner_staff_id, $staff_ids);
		} else {
			$result = $this->getAssessmentWithOwner($owner_staff_id, $owner_staff_id);
		}
		// dd($my_teams);
		// dd($construct);

		return $result;
	}

	public function getAssessmentWithSelf($owner_staff_id){
		$year = $this->year;
		$res = $this->getReportShowWithWhere(" a.year = $year and (a.owner_staff_id = $owner_staff_id) and a.enable = 1 and a.staff_id = $owner_staff_id ");
		foreach($res as &$val){
			$val['_authority'] = $this->getAuthority($val,$owner_staff_id);
		}
		return $res;
	}

	public function getAssessmentWithOwner($owner_staff_id, $self_id){
		$year = $this->year;
		$res = $this->getReportShowWithWhere(" a.year = $year and (a.owner_staff_id = $owner_staff_id) and a.enable = 1 ");
		if(is_numeric($self_id)){
			foreach($res as &$val){
				$val['_authority'] = $this->getAuthority($val,$self_id);
			}
		}
		return $res;
	}

	public function getAssessmentWithLeader($owner_staff_id, $staff_ids){
		$year = $this->year;

		$staff_ids = join(',',$staff_ids);
		$res = $this->getReportShowWithWhere("
			a.year = $year and 
			a.staff_id in ($staff_ids) and 
			a.enable = 1
		");

		// dd($res);
		foreach ($res as &$val) {
			$val['_authority'] = $this->getAuthority($val,$owner_staff_id);
		}
		$res = $this->adjStaffNameMapWithAssessment($res);
		return $res;
	}

	public function getAssessmentWithCeo($owner_staff_id) {
		$year = $this->year;
		$res = $this->getReportShowWithWhere("
			a.year = $year and
			a.enable = 1 and
			(
				d.manager_staff_id in (0,$owner_staff_id) or 
				(c.supervisor_staff_id=$owner_staff_id and b.is_leader=1)
			)
		");

		foreach($res as &$val){
			$val['_authority'] = $this->getAuthority($val,$owner_staff_id);
		}

		return $res;
	}

	public function getAssessmentWithAdmin($year, $self_id=null){
		$res = $this->getReportShowWithWhere(" a.year = $year ");
		if(is_numeric($self_id)){
			foreach($res as &$val){
				$val['_authority'] = $this->getAuthority($val,$self_id);
			}
		}
		return $res;
	}

	public function getAssessmentWithId($id, $self_id=null){
		$res = $this->getReportShowWithWhere(" a.id = $id ");
		if(is_numeric($self_id)){
			foreach($res as &$val){
				$val['_authority'] = $this->getAuthority($val,$self_id);
				if(!$val['_authority']['view']){ $this->error('You Have Not Promised.'); }
			}
			$res = $this->adjStaffNameMapWithAssessment($res);
		}
		if( count($res)==0){ $this->error('Not Found This Report.'); }
		
		return $res;
	}

	private function adjStaffNameMapWithAssessment(&$assessment_reports) {
		if (isset($assessment_reports[0]) && isset($assessment_reports[0]['path_lv_leaders'])) {
			$staff_map = $this->config_quick->getStaffMap();
			foreach ($assessment_reports as &$val) {
				$val['_staff_name_map'] = [];
				foreach ($val['path_lv_leaders'] as $leaders) {
					foreach ($leaders as $leader) {
						$val['_staff_name_map'][$leader] = [
							$staff_map[$leader]['name'],
							$staff_map[$leader]['name_en'],
							$staff_map[$leader]['staff_no'],
						];
					}
				}
			}
		}
		return $assessment_reports;
	}

	public function getAssessmentWithDDS($set, $self_id){
		$divi = $set[0]; $depm = $set[1]; $staf = $set[2]; $year = $this->year;
		if($staf>0){
			$where = " a.year=$year and a.staff_id=$staf and a.enable=1";
		}else if($depm>0){
			$where = " a.year=$year and a.department_id=$depm and a.enable=1";
		}else if($divi>0){
			if($divi==self::DIVISION_CENTER){
				$where = " a.year=$year and a.enable=1";
			}else{
				$where = " a.year=$year and a.division_id=$divi and a.enable=1";
			}
		}
		$res = $this->getReportShowWithWhere($where);
		if( count($res)==0){ $this->error('Not Found This Report.'); }
		foreach($res as &$val){ $val['_authority'] = $this->getAuthority($val,$self_id); }
		// dd($res);
		return $res;
	}

	private function getAuthority($report, $self_id){
		$res = array();
		$cq = $this->config_quick;
		// $teamLeadersMap = $cq->getDepartmentMultipleLeadersMap();
		$proc_launch = $cq->canProcessingYearlyAssessment();
		// $path_final = array_search($self_id, $report['path']) == (count($report['path'])-1);
		// $path_final = $report['owner_staff_id']==0;
		$path_final = $report['processing_lv']==YearPerformanceReport::PROCESSING_LV_STOP;

		$is_multiple_leaders = false;
		$this_lv_leaders = [];
		$this_lv_leaders_evaluating = [];
		$owner_department_lv = 0;
		$report_staff_department_lv = 0;
		foreach ($report['path_lv'] as $lv => $ds) {
			if ($ds[0] == $report['own_department_id']) { $owner_department_lv = $lv; }
			if ($ds[0] == $report['department_id']) { $report_staff_department_lv = $lv; }
		}
		
		$allStaffPath = [$report['staff_id']];
		// dd($report);
		foreach ($report['path_lv_leaders'] as $lv => $leader_ids) {
			if ($lv < $report_staff_department_lv) {
				$allStaffPath = array_merge($allStaffPath, $leader_ids);
			} else if ($lv == $report_staff_department_lv && !in_array($report['staff_id'], $leader_ids)) {
				$allStaffPath = array_merge($allStaffPath, $leader_ids);
			}
			
			if ($lv==$owner_department_lv) {
				$this_lv_leaders = $leader_ids;
				if (isset($report['assessment_evaluating_json'][$lv])) {
					$is_multiple_leaders = true;
					$this_lv_leaders_evaluating = $report['assessment_evaluating_json'][$lv];
				}
			}
		}

		$is_in_this_lv_leadership = in_array($self_id, $this_lv_leaders);
		if ($is_multiple_leaders) {
			$idx_evaluating_leader = array_search($self_id, $this_lv_leaders_evaluating['leaders']);
			$is_leader_already_commited = $this_lv_leaders_evaluating['commited'][$idx_evaluating_leader];
			$can_edit_by_self = $report['owner_staff_id'] == $report['staff_id'] && $report['owner_staff_id'] == $self_id;
			$can_edit_by_leader = $report['owner_staff_id'] != $report['staff_id'] && $is_in_this_lv_leadership && !$is_leader_already_commited;
		} else {
			$can_edit_by_self = $report['owner_staff_id'] == $self_id;
			$can_edit_by_leader = $report['owner_staff_id'] != $report['staff_id'] && $is_in_this_lv_leadership;
		}

		$res['view'] = in_array($self_id, $cq->getAllPrivilegeId($allStaffPath));
		$res['edit'] = !$path_final && ($can_edit_by_self || $can_edit_by_leader);
		$res['edit_comment'] = $is_in_this_lv_leadership || $can_edit_by_self;
		$res['return'] = $proc_launch && $res['edit'] && $report['owner_staff_id']!=$report['staff_id'];
		$res['commit'] = $proc_launch && $res['edit'] && !$path_final;
		$res['isFinished'] = $report['level']!='-' && $path_final;

		return $res;
	}

	private function getAuthority_division($division_data, $self_id){
		$res = [];
		$cq = $this->config_quick;
		$proc_launch = $cq->canProcessingYearlyDivision();
		$isConstrutor = $cq->data['constructor_staff_id']==$self_id;
		$isCSX = $isConstrutor && $division_data['processing'] == YearPerformanceDivisions::PROCESSING_DIRECTOR_COMMIT; //是架構審核
		$least_processing = $division_data['division']==1?4:0;
		$all_report_done = $cq->data['constructor_staff_id'];

		$res['view'] = true;
		// $res['edit'] = $division_data['owner_staff_id']==$self_id && $division_data['processing'] != YearPerformanceDivisions::PROCESSING_DIRECTOR_COMMIT;;
		$res['edit'] = $division_data['owner_staff_id']==$self_id && $proc_launch;
		$res['return'] = ($res['edit'] || $isCSX) && $division_data['processing'] > $least_processing && $division_data['processing'] >= YearPerformanceDivisions::PROCESSING_DIRECTOR_COMMIT;
		$res['commit'] = ($res['edit'] || $isCSX) && $division_data['processing'] < YearPerformanceDivisions::PROCESSING_CEO_COMMIT && $division_data['status'] >= ( $res['return']?5:1 );
		$res['isFinished'] = $division_data['processing'] == YearPerformanceDivisions::PROCESSING_CEO_COMMIT;

		return $res;
	}

	public function saveYearlyAssessment($id, $staff_val, $update_data, $should_count = 1){
		if($this->config_quick->isYearlyAssessmentFinished()){$this->error("This Year Is Been Finished.");}
			$result = array(); $update = array();
			//檢查能否編輯
			$assessment = $this->report->select($id);
			if(count($assessment)==0){$this->error('Not Found This Report.');}
			$assessment = $assessment[0];
			
			if($assessment['year'] != $this->year){$this->error('Year Wrong.');}
			$config = $this->config_quick->data;
			$year_team_map = $this->config_quick->getDepartmentMap();
			$team_leader_map = $this->config_quick->getDepartmentMultipleLeadersMap();
			$my_team = $year_team_map[$staff_val['department_id']];
			// $my_team_lv = $my_team['lv'];
			// dd($staff_val);
			$topic_map = $this->topic->read(array('id,score,score_leader'),array('id'=>'in('.$config['assessment_ids'].')'))->map(); //當年題目


			//有分數更新
			if( isset($update_data['assessment_json']) ){
				
				$u_a_json = $update_data['assessment_json'];
				$is_self_update = false;

				// 檢查權限
				if (!is_array($u_a_json)){ $this->error('Wrong [assessment_json] Type.'); }
				
				foreach ($u_a_json as $_ulv => $_uval) {
					if ($_ulv == 'self') {
						if ($assessment['owner_staff_id']!=$staff_val['id']){
							$this->error('You Are Not Owner.');
						}
						$is_self_update = true;
						break;
					} else {
						if ($staff_val['is_leader']!=1) { $this->error('You Are Not Leader.'); }

						$current_department = isset($assessment['path_lv'][$_ulv]) ? $assessment['path_lv'][$_ulv][0] : false;
						if (!$current_department) { $this->error('You Can Not Do It. [lv = '.$_ulv.']'); }
						
						// $current_leaders = $team_leader_map[$current_department];
						// if (!in_array($staff_val['id'], $current_leaders)){ $this->error('You Can Not Do It. [lv = '.$_ulv.']'); } //不在路程
					}
				}
				
				// 寫入資料
				$current_leaders = $team_leader_map[$my_team['id']];
				if (count($current_leaders) > 1 && !$is_self_update) {
					$new_eva_json = $this->updateEvaluatingAssessmentJson($assessment['assessment_evaluating_json'], $u_a_json, $staff_val['id'], $assessment['staff_is_leader'], $should_count);
					$update['assessment_evaluating_json'] = $new_eva_json;

					$update_to_assessment = [];
					
					foreach ($new_eva_json as $evalv => $evaval) {
						$_done = true;
						$num_should_count = 0;
						
						foreach ($evaval['totallist'] as $idx => $total_score) {
							if ($evaval['should_count'][$idx]) {
								$num_should_count += 1;
								if ($total_score <= 0) {
									$_done = false;
									break;
								}
							}
						}

						if ($_done && $num_should_count > 0) {
							$merged_score = [];
							foreach ($evaval['scores'] as $idx => $score) {
								if (!$evaval['should_count'][$idx]) { continue; }
								foreach ($score as $tid => $sc) {
									if (isset($merged_score[$tid])) {
										$merged_score[$tid] += $sc;
									} else {
										$merged_score[$tid] = $sc;
									}
								}
							}

							foreach ($merged_score as &$val) {
								$val = round($val / $num_should_count);
							}
							$update_to_assessment[$evalv] = $merged_score;
						}
					}

					$new_assessment_json = $this->updateAssessmentJson($assessment['assessment_json'], $update_to_assessment, $assessment['staff_is_leader']);

				} else {

					$new_assessment_json = $this->updateAssessmentJson($assessment['assessment_json'], $u_a_json, $assessment['staff_is_leader']);
					
				}

				//更新分數json
				$update['assessment_json'] = $new_assessment_json;
				

				$assessment_total = 0;    //結算總分
				$isDone = true; $all_percent = 0;
				// dd($new_assessment_json);
				foreach ($new_assessment_json as $new_a_value) {
					$all_percent+=$new_a_value['percent'];
					$assessment_total += $new_a_value['percent'] * ($new_a_value['total'] / 100);
					if($isDone && isset($new_a_value['score'])){ foreach($new_a_value['score'] as $av_score){ if($av_score<0){$isDone=false;break;} } }
				}

				//總分
				$update['assessment_total'] = round($assessment_total);

				// dd($isDone);
				if($isDone && $all_percent>=100){
					//全部分數都有的話 算出 level
					$update['level'] = $this->getLevelByTotal( $update['assessment_total'] );
				}

			}else{
				$result['assessment_json'] = $assessment['assessment_json'];
				$result['assessment_total'] = $assessment['assessment_total'];
			}



			//自我貢獻
			if( isset($update_data['self_contribution']) ){
				if( strlen2($update_data['self_contribution'])>500 ){$this->error("The self_contribution Is Over 500 Contents.");}   //超出字數
				$update['self_contribution'] = $update_data['self_contribution'];
			}

			//自我改進
			if( isset($update_data['self_improve']) ){
				if( strlen2($update_data['self_improve'])>500 ){$this->error("The self_improve Is Over 500 Contents.");}   //超出字數
				$update['self_improve'] = $update_data['self_improve'];
			}

			//上司評語
			if( isset($update_data['comment']) ){
				$up_comment = $assessment['upper_comment'];
				$comment = $update_data['comment'];

				if(!is_array($comment)){$this->error('Wrong Comment Data.');}
				foreach($up_comment as $up_clv => &$up_cv){
					if(empty($comment[$up_clv])){continue;}

					if (!in_array($staff_val['id'], $up_cv['staff_id'])){ $this->error("You Are Not The Leader Of This Comment [lv = $up_clv]"); }
					if( strlen2($comment[$up_clv])>2000 ){$this->error("The up_comment Is Over 2000 Contents.");}   //超出字數
					
					$leader_idx = array_search($staff_val['id'], $up_cv['staff_id']);
					$up_cv['content'][$leader_idx] = $comment[$up_clv];
					
				}
				$update['upper_comment'] = json_encode($up_comment,JSON_UNESCAPED_UNICODE);
				// dd($update);
			}


			// dd($update);

			// 更新資料
			$count = $this->report->update($update,$id);

			if($count>0){

				$origin_data = [];
				//有變動就存入 變動紀錄
				foreach($update as $uo_key => $uo_val){
					if($assessment[$uo_key]==$uo_val){
						unset($update[$uo_key]);
					}else{
						$origin_data[$uo_key] = $assessment[$uo_key];
					}
				}
				//更新紀錄
				$this->record->save($staff_val['id'], $id, $origin_data, $update);

				//結果
				$result['status'] = 'OK.';
				$result['assessment_json'] = $assessment['assessment_json'];
				$result['assessment_total'] = $assessment['assessment_total'];
				$result['level'] = $assessment['level'];
				$result['self_contribution'] = $assessment['self_contribution'];
				$result['self_improve'] = $assessment['self_improve'];
				$result['upper_comment'] = $assessment['upper_comment'];
				// $result['can_go_division_now'] = isset($divi_status) && $divi_status!=YearPerformanceDivisions::STATUS_INIT;

				if(isset($update['upper_comment'])){
					foreach($update['upper_comment'] as &$upc_v){
						if(empty($upc_v['content'])){continue;}
						$upc_v['content'] = urldecode($upc_v['content']);
					}
				}

				$result = array_merge($result,$update);

			}else{
				$result['status'] = 'Nothing Changed.';
				$this->error($result['status']);
			}

			return $result;
	}

	//
	private function updateAssessmentJson($old, $update, $staff_is_lader = 0) {
		$topic_map = $this->topic->map(); //當年題目
		$new_json = $old;
		foreach($new_json as $lv => &$value){
			if (!isset($update[$lv])) { continue; }
			$now_update_json = $update[$lv];
			$total = 0;
			//歷遍 預設格式
			foreach ($value['score'] as $t_id => &$score_value) {
				if( isset($now_update_json[$t_id]) ){   //送來的資料有該題目 id
						$new_value = $now_update_json[$t_id];
						if($staff_is_lader){    //切換要檢查的題目 id 要檢查 主管的還是一般人員的
							$check_score = $topic_map[$t_id]['score_leader'];
						}else{
							$check_score = $topic_map[$t_id]['score'];
						}

						if( (int)$new_value > (int)$check_score || (int)$new_value < 0 ){ $this->error("Score Too Over. { topic=$t_id , value=$new_value }"); }

						$score_value=(int)$new_value;
				}
				$total += $score_value > 0 ? $score_value : 0;
			}
			$value['total'] = $total;

		}
		return $new_json;
	}

	//
	private function updateEvaluatingAssessmentJson($old, $update, $my_staff_id, $target_staff_is_lader, $should_count = null) {
		$topic_map = $this->topic->map();
		$new_json = $old;
		foreach($new_json as $lv => &$value){
			if (!isset($update[$lv])) { continue; }
			$now_update_json = $update[$lv];
			
			$leader_index = array_search($my_staff_id, $value['leaders']);
			if ($leader_index >= 0) {

				if ($should_count == 1) {
					$value['should_count'][$leader_index] = true;
				} else if ($should_count == 2) {
					$value['should_count'][$leader_index] = false;
				}

				$total = 0;
				// 歷遍
				foreach ($value['scores'][$leader_index] as $t_id => &$score_value) {
					if( isset($now_update_json[$t_id]) ){   //送來的資料有該題目 id
						$new_value = $now_update_json[$t_id];
						if($target_staff_is_lader){    //切換要檢查的題目 id 要檢查 主管的還是一般人員的
							$check_score = $topic_map[$t_id]['score_leader'];
						}else{
							$check_score = $topic_map[$t_id]['score'];
						}

						if( (int)$new_value > (int)$check_score || (int)$new_value < 0 ){ $this->error("Score Too Over. { topic=$t_id , value=$new_value }"); }

						$score_value=(int)$new_value;
					}
					$total += $score_value > 0 ? $score_value : 0;
				}

				$value['totallist'][$leader_index] = $total;

			} else {
				$this->error("Wrong Leader Index: $leader_index");
			}
		}
		return $new_json;
	}

	//送審
	public function commitYearlyAssessment($id, $self_id){
		$result = array();
		if(!$this->config_quick->canProcessingYearlyAssessment()){$this->error("The Year Processing Not Allow.");}
		$assessment = $this->report->read(
		['id','staff_id','owner_staff_id','processing_lv','path','path_lv','path_lv_leaders','department_id','division_id','assessment_json','assessment_evaluating_json','sign_json','self_contribution','self_improve'],
		$id)->check('Not Found This Assessment.')->data[0];
		
		// dd($assessment);
		$now_lv = $assessment['processing_lv'];
		$this_lv_leaders = $assessment['path_lv_leaders'][$now_lv];
		$is_this_lv_leader = in_array($self_id, $this_lv_leaders);
		
		// 權限檢查
		if ($assessment['owner_staff_id'] != $self_id) {
			if (in_array($assessment['owner_staff_id'], $this_lv_leaders)) { // 擁有者確實在這一層
				if (!$is_this_lv_leader) { // 但自己不是這層主管
					$this->error("You Are Not Owner.");
				}
			} else {
				$this->error("Owner Not Match Leaders In This Process.");
			}
		}
		if( $assessment['processing_lv']==YearPerformanceReport::PROCESSING_LV_STOP){
			$this->error("This Report Already Done.");
		}

		if( empty($assessment['self_contribution']) || empty($assessment['self_improve']) ){
			$this->error('You Have To Writing Some Thing About Youself.');
		}

		$update = array();

		$assessment_evaluating_json = $assessment['assessment_evaluating_json'];
		$is_not_myself = $self_id != $assessment['staff_id'];
		
		if ($is_not_myself && isset($assessment_evaluating_json[$now_lv])) { // 有多主管 且不是自評
			$aej = &$assessment_evaluating_json[$now_lv];
			$aej_commited = &$aej['commited'];
			$which_leader_idx = array_search($self_id, $aej['leaders']);
			$aej_commited[$which_leader_idx] = true;
			$update['assessment_evaluating_json'] = $assessment_evaluating_json;

			$is_all_leaders_commited = true;
			foreach ($aej_commited as $idx => $_commited) {
				// if ($aej['should_count'][$idx]) {
				if (!$_commited) {
					$is_all_leaders_commited = false;
					break;
				}
				// }
			}
			$result['change'] = $this->report->update($update, $id);
			// dd([$aej_commited, $is_all_leaders_commited]);
			if (!$is_all_leaders_commited) {
				//簽时間戳
				$sign_lv = $assessment['owner_staff_id']==$assessment['staff_id']? 's' : $now_lv;//自評的話是 s
				$this->stampReportTime($assessment['id'], $sign_lv, $self_id, $assessment['sign_json']);
				
				$result['status'] = 'OK.';
				$result['processing_lv'] = $assessment['processing_lv'];
				$result['owner_staff_id'] = $assessment['owner_staff_id'];
				$result['turn_can_fix'] = false;
				
				return $result;
			}
		}
		
		
		//確認分數都有
		$as_final_lv=9;
		$staff_map = $this->config_quick->getStaffMap();
		$staff = $staff_map[ $self_id ];
		$current_staff_team = $this->config_quick->getDepartmentMap()[ $staff['department_id'] ];
		// dd($staff);
		$check_by_now_lv = (int) $current_staff_team['lv'];
		
		if( $staff['is_leader'] != 1){$check_by_now_lv++;}  //不是主管 不要檢查到 同單位層級
		// dd($check_by_now_lv); 
		// 檢查該填的分數填完了沒
		foreach($assessment['assessment_json'] as $lv => $score_json){
			if($lv=='under'){continue;}
			else if($lv=='self'){
			}else{
				$ilv = (int) $lv;
				$as_final_lv = min($as_final_lv,$ilv);
				if($ilv<$check_by_now_lv){continue;}
			}
			foreach($score_json['score'] as $qid => $q_score){
				if($q_score<0){$this->error('You Are Not Complete The Score Yet. [ question_id = '.$qid.' ]');}
			}
		}
		$is_final = false;
		
		//送到底了 不用在送
		if($as_final_lv==$assessment['processing_lv'] && $staff['is_leader']==1){  //是主管送到底的
			// $this->error('It Is Already On Final.');
			$update['owner_staff_id'] = isset($assessment['path_lv'][1])? $assessment['path_lv'][1][1] : $this->config_quick->data['ceo_staff_id'];
			$update['own_department_id'] = $staff_map[$update['owner_staff_id']]['department_id'];
			$update['processing_lv'] = YearPerformanceReport::PROCESSING_LV_STOP;
			$is_final = true;

		}else{

			if($is_this_lv_leader){
				//自己是主管 要往上層送
				$update['processing_lv'] = $this->getNextProcessingLvByReport( $assessment );
			}else{
				//自己是員工 要給主管
				$update['processing_lv'] = $this->getNextProcessingLvByReport( $assessment, true );
			}

			$update['owner_staff_id'] = $this->getNextOwnerStaffId($assessment, $update['processing_lv']);
			$update['own_department_id'] = $staff_map[$update['owner_staff_id']]['department_id'];

			//上頭是自己 就是結束了
			if($update['owner_staff_id']==$assessment['owner_staff_id']){
				// $update['owner_staff_id']=0;
				$update['owner_staff_id'] = isset($assessment['path_lv'][1])? $assessment['path_lv'][1][1] : $this->config_quick->data['ceo_staff_id'];
				$update['own_department_id'] = $staff_map[$update['owner_staff_id']]['department_id'];
				$update['processing_lv'] = YearPerformanceReport::PROCESSING_LV_STOP;
				$is_final = true;
			}

		}
		// dd($update);


		$count =  $this->report->update($update, $id);
		if($count>0){
			$oData = array(
				'assessment_json'=>$assessment['assessment_json'],
				'processing_lv'=>$assessment['processing_lv'],
				'owner_staff_id'=>$assessment['owner_staff_id']
			);

			//到底
			if($is_final){
				$this->record->agree($self_id, $id, $oData, $update);
			}else{
				$this->record->commit($self_id, $id, $oData, $update);
			}
			//簽时間戳
			$sign_lv = $assessment['owner_staff_id']==$assessment['staff_id']? 's' : $assessment['processing_lv'];//自評的話是 s
			$this->stampReportTime($assessment['id'], $sign_lv, $self_id, $assessment['sign_json']);

			// 送到部門已上 檢查更新 部門單狀態
			if($is_final){

				$init_processing = count($assessment['path_lv'])<=1 ? 3: 0;  //送審上層的人員只有一位  代表示 部門以上級
				$can_fix = $this->refreshDivisionStatus( $assessment['division_id'], $init_processing);
			}else{
				//還沒到底都要寄信
				$this->generalAssessmentEmail('yearly_assessment_staff_commit', $assessment['staff_id'], $update['owner_staff_id']);

			}

			$result['status'] = 'OK.';
		}else{
			$result['status'] = 'Nothing Changed.';
		}
		$result['change'] = $count;
		$result['processing_lv'] = $update['processing_lv'];
		$result['owner_staff_id'] = $update['owner_staff_id'];
		$result['turn_can_fix'] = $can_fix;

		return $result;
	}

	//更新 並取得新的狀態
	private function refreshDivisionStatus($divi_id, $init_processing=0){
		$ceo_id = $this->config_quick->data['ceo_staff_id'];
		$complete_total = $this->report->getAllDepartmentAssessmentComplete($this->year, $divi_id, $ceo_id);
		// dd($complete_total);
		$divi_status = YearPerformanceDivisions::STATUS_INIT;
		if($complete_total['done']+1 >= $complete_total['total']){
			if( $complete_total['done'] >= $complete_total['total'] ){
				$divi_status = YearPerformanceDivisions::STATUS_FINISHED;

				//寄信  單一部門完成 個人單
				$this->generalDivisionEmail('yearly_assessment_to_director_commit', $divi_id);
			}else if($complete_total['leader']==-1){
				$divi_status = YearPerformanceDivisions::STATUS_DIVISION;
			}
		}
		$updateData = array('status'=>$divi_status);
		if($divi_status==YearPerformanceDivisions::STATUS_INIT){
			//回到初始
			$updateData['processing'] = $init_processing;
			$team_map = $this->config_quick->getDepartmentMap();
			$team = $team_map[$divi_id];
			$team_leader = $team['manager_staff_id']==0?$team['supervisor_staff_id']:$team['manager_staff_id'];
			$updateData['owner_staff_id'] = $team_leader;
		}else if($divi_status==YearPerformanceDivisions::STATUS_FINISHED){
			//此單結束 判斷是否部門只有部長自己
			if($complete_total['leader']==$complete_total['total']){
				$updateData['processing'] = YearPerformanceDivisions::PROCESSING_ARCHITE_WAIT;
				$updateData['owner_staff_id'] = $ceo_id;
			}
		}



		$c = $this->divisions->update( $updateData , array('year'=>$this->year,'division'=>$divi_id) );
		if($c>0){
			//全部都完成了
			$count = $this->divisions->read(['id'],['year'=>$this->year,'status'=>'!='.YearPerformanceDivisions::STATUS_FINISHED])->count;
			if($count==0){
				//所有部門 個人單 結束
				$this->config_quick->doneReport();
				//寄信
				$sendData = ['year'=>$this->year];
				$peoples = $this->divisions->select(['owner_staff_id'],['year'=>$this->year]);
				$peoples = array_column($peoples,'owner_staff_id');
				$this->sendEmailByData('yearly_assessment_all_report_done', $sendData, $peoples, true );

			}else{ $this->config_quick->keepAssessment(); }
		}

		return $this->config_quick->isYearlyAssessmentReportFinished();
	}

	//檢查月考績是否收齊
	private function checkReportDoneByDivision($divi_id, $filter_divi_leader=false){
		$ceo_id = $this->config_quick->data['ceo_staff_id'];
		$complete_total = $this->report->getAllDepartmentAssessmentComplete($this->year, $divi_id, $ceo_id);
		// dd($complete_total);
		if($filter_divi_leader && $complete_total['leader']==-1){
			$dones = $complete_total['done']+1;
		}else{
			$dones = $complete_total['done'];
		}
		return $dones>=$complete_total['total'];
	}

	//檢查部門單是否收齊
	private function checkDivisionDone(){
		$dp = $this->divisions->select(['processing'],['year'=>$this->year]);
		$end_status = YearPerformanceDivisions::PROCESSING_CEO_COMMIT;
		$total = count($dp);
		$finish = 0;
		foreach($dp as $v){
			if($v['processing']==$end_status){$finish++;}
		}
		//考評結束
		$isDone = $total==$finish;
		if($isDone){
			$this->config_quick->done();
			//寄結束通知信
			$all_people_ids = $this->report->select(['staff_id'],['year'=>$this->year,'enable'=>1]);
			$all_people_ids = array_column($all_people_ids, 'staff_id');
			$this->sendEmailByData('yearly_division_done', ['year'=>$this->year], $all_people_ids, true);
		}else{
			$this->config_quick->doneReport();
		}
		return $isDone;
	}

	// 重設所有報表 level
	private function resettingYearlyReportLevelByLv($from_lv, $to_lv) {
		$distribution_map = $this->distribution_rate->read(['lv','name'], ['enable'=>1, 'lv'=>[$from_lv, $to_lv]])->map('lv');
		if (count($distribution_map) == 2) {
			$prevLevel = $distribution_map[$from_lv]['name'];
			$nextLevel = $distribution_map[$to_lv]['name'];
			$c = $this->report->update(['level' => $nextLevel], ['year'=>$this->year, 'level'=>$prevLevel]);
		}
	}

	private function getLevelByTotal($score){
		$map = $this->getDistributionRate();
		$res = '-';
		foreach($map as $lv => $v){
			if($score >= $v['score_least'] && $score <= $v['score_limit']){$res=$lv;break;}
		}
		return $res;
	}

	private function getNextProcessingLvByReport($report, $lock_leader=false){
		$json = $report['assessment_json'];
		$current_leader = $report['path_lv'][ $report['processing_lv'] ][1];
		if($lock_leader){
			$new_leader = $current_leader;
		}else{
			$index = array_search($current_leader, $report['path'])+1;
			$index = min($index , count($report['path'])-1);
			$new_leader = $report['path'][$index];
		}

		// dd($json);
		// dd($new_leader);
		$res = 1;
		for($i=1; $i<=5; $i++){ //從上層找  到主管
			if( isset($report['path_lv'][$i]) && $report['path_lv'][$i][1]==$new_leader ){
				//找到新主管發現 新主管不用評分
				if(empty($json[$i])){
					// $new_leader = $current_leader;
					continue;
				}

				$res = $i;
				break;
			}
		}
		return $res;
	}

	private function getPrevProcessingLvByReport($report){
		$json = $report['assessment_json']; $is_final = false; $res=[];
		if($report['processing_lv']==YearPerformanceReport::PROCESSING_LV_STOP){
			$current_leader = (int)end($report['path']); $is_final = true;
		}else{
			$current_leader = $report['path_lv'][ $report['processing_lv'] ][1];
		}
		// dd($current_leader);
		$index = array_search($current_leader, $report['path']) -1;
		// $isOrigin = $index==0;
		$index = max(0,$index);
		$new_leader = (int)$report['path'][$index];
		for($i=5; $i>=1; $i--){
			if( isset($report['path_lv'][$i]) && $report['path_lv'][$i][1]==$new_leader ){
				$res['processing_lv'] = $i;
				$res['own_department_id'] = $report['path_lv'][$i][0];
				break;
			}
		}
		$res['owner_staff_id'] = $new_leader;

		if ($res['processing_lv'] == $report['processing_lv']) {
			$res['owner_staff_id'] = $report['staff_id'];
		} else if(!$is_final){
			$res['owner_staff_id'] = $this->getNextOwnerStaffId($report, $res['processing_lv']);
			if($res['owner_staff_id']==$report['owner_staff_id']){
				//上一層主管還是自己 退給員工
				$res['owner_staff_id'] = $report['staff_id'];
			}
		}
		return $res;
	}

	public function rejectYearlyAssessment($id, $reason, $self_id, $is_admin=false){
		if(!$this->config_quick->canProcessingYearlyAssessment()){$this->error("The Year Processing Not Allow.");}
		$result = array();
		$assessment = $this->report->read($id)->check()->data[0];

		// $admin_staff_id = $this->config_quick->getAdminId();
		// dd($admin_staff_id);
		$isinleadership = $this->report->isInLeadership($self_id);
		if (!$isinleadership && !$is_admin) {
			
			if($assessment['owner_staff_id']!= 0){
				$this->error("You Are Not Owner.");
			}else{
				$this->error("This Report Already Done.");
			}
		}


		$update = array();

		$update = $this->getPrevProcessingLvByReport($assessment);

		if($assessment['owner_staff_id']==$update['owner_staff_id'] && $assessment['processing_lv']==$update['processing_lv']){ $this->error('No Change Owner.'); }
		$update['reason'] = $reason ? $reason : '無';

		$next_assessment_evaluating_json = $assessment['assessment_evaluating_json'];
		if (isset($next_assessment_evaluating_json[$update['processing_lv']])) {	//多主管
			foreach ($next_assessment_evaluating_json[$update['processing_lv']]['commited'] as &$commited) {
				$commited = false;
			}
			$update['assessment_evaluating_json'] = $next_assessment_evaluating_json;
		}

		$count = $this->report->update($update, $id);
		if($count>0) {
			$oData = array(
				'assessment_json'=>$assessment['assessment_json'],
				'processing_lv'=>$assessment['processing_lv'],
				'owner_staff_id'=>$assessment['owner_staff_id']
			);
			$this->record->back($self_id, $id, $oData, $update);
			$init_processing = count($assessment['path_lv'])<=1 ? 3: 0;  //送審上層的人員只有一位  代表示 部門以上級
			$can_fix = $this->refreshDivisionStatus( $assessment['division_id'], $init_processing );
			$result['status'] = 'OK.';
		} else {
			// $result['status'] = 'Nothing Changed.';
			$this->error('Nothing Changed.');
		}
		$result['change'] = $count;
		$result['processing_lv'] = $update['processing_lv'];
		$result['owner_staff_id'] = $update['owner_staff_id'];
		//寄信
		$this->generalAssessmentEmail( 'yearly_assessment_to_reject_to_staff', $assessment['staff_id'], $update['owner_staff_id'], $update );
		return $result;
	}

	//常用的 年考評寄信
	private function generalAssessmentEmail($template_name, $staff_id, $owner_staff_id, $sendData=[]){
		$sendData['year'] = $this->year;
		$staff_map = $this->config_quick->getStaffMap();
		$team_map = $this->config_quick->getDepartmentMap();
		$team_leaders_map = $this->config_quick->getDepartmentMultipleLeadersMap();
		$staff = $staff_map[ $staff_id ];
		$team = $team_map[ $staff['department_id'] ];
		$sendData['staff_name'] = $staff['name'];
		$sendData['staff_name_en'] = $staff['name_en'];
		$sendData['unit_id'] = $team['unit_id'];
		$sendData['department_name'] = $team['name'];

		$owner_department_id = $staff_map[$owner_staff_id]['department_id'];
		$new_owner_leaders = $team_leaders_map[$owner_department_id];
		$target_staff_ids = count($new_owner_leaders) > 1 ? $new_owner_leaders : $owner_staff_id;

		return $this->sendEmailByData($template_name, $sendData, $target_staff_ids, true );
	}
	//常用的 部門單寄信
	private function generalDivisionEmail($template_name, $division_id, $owner_staff_id=0, $sendData=[]){
		$sendData['year'] = $this->year;
		$team_map = $this->config_quick->getDepartmentMap();
		$division = $team_map[ $division_id ];
		$sendData['unit_id'] = $division['unit_id'];
		$sendData['division_name'] = $division['name'];
		$leader = empty($division['manager_staff_id'])?$division['supervisor_staff_id']:$division['manager_staff_id'];
		$peoples = [$leader];
		if($owner_staff_id>0){
			$peoples[]=$owner_staff_id;
			$staff_map = $this->config_quick->getStaffMap();
			$onwer = $staff_map[$owner_staff_id];
			$sendData['owner_staff_name'] = $onwer['name'];
			$sendData['owner_staff_name_en'] = $onwer['name_en'];
		}
		return $this->sendEmailByData($template_name, $sendData, $peoples, true );
	}

	public function getFullTopic($year){
		// $config = $this->getConfig($year);
		$config = $this->config_quick->data;

		$type_table = $this->topic_type->table_name;
		$tids = $config['assessment_ids'];
		if( strlen2($tids) <3){$this->error('Year Not Setted.');}
		$this->topic->sql(" select a.id, a.name, a.score, a.score_leader , a.applicable , a.type, b.name as type_name  from {table} as a left join $type_table as b on a.type = b.id where a.id in ($tids) order by b.sort, a.sort ");
		$result = $this->topic->getSplitApplicable();
		return $result;
	}

	public function launchAssessment(){

		$config = $this->config_quick->data;

		$res = array();
		if($config['processing']<YearlyQuickly::PROCESSING_A_PRE){ $this->error('Processing = ['.$config['processing'].'] Not Arrived Yet. '); }  //沒產生年考績或是被退回部屬
		if($config['processing']>YearlyQuickly::PROCESSING_A_CLOSE){ $this->error('Processing = ['.$config['processing'].'] Not Allow. '); }  //超過進度不能重啟
		$add = $config['assessment_addition_day'];
		$launch_date = date('Y-m-d');
		$ld_time = strtotime($launch_date);

		$end_time = strtotime("+".$add." day", $ld_time);
		$end_date = date('Y-m-d',$end_time);

		$update_data = [];
		$update_data['processing']=YearlyQuickly::PROCESSING_A_LAUNCH;
		$update_data['assessment_date_start']=$launch_date;
		$update_data['assessment_date_end']=$end_date;

		
		$count = $this->config_quick->update( $update_data );
		$res['change'] = $count;
		$res['processing'] = $this->config_quick->data['processing'];
		$res['status'] = $count==0 ? 'Already Done.' : 'OK.';

		//Email
		$split_data = explode('-',$end_date);
		
		$this->sendEmailByData('yearly_assessment_launch',['year'=>$this->year,'year2'=>$split_data[0],'month'=>$split_data[1],'day'=>$split_data[2]],[],true);

		return $res;
	}

	public function close(){
		$config = $this->config_quick->data;
		$up = YearlyQuickly::PROCESSING_A_CLOSE;
		if($config['processing']>$up){$this->error('No Need Close To Back.');}
		$count = $this->config_quick->update(array('processing'=> $up ));
		$res['change'] = $count;
		$res['processing'] = $this->config_quick->data['processing'];
		$res['status'] = $count==0 ? 'Already Done.' : 'OK.';
		//Email
		$split_data = explode('-',$config['assessment_date_end']);
		$this->sendEmailByData('yearly_assessment_pause',['year'=>$this->year,'month'=>$split_data[1],'day'=>$split_data[2]],[],true);

		return $res;
	}

	public function deleteAssessment($year=null){
		if($this->config_quick->isYearlyAssessmentFinished()){$this->error("This Year Is Been Finished.");}
		$year = $this->year;
		$record_table = $this->record->table_name;
		$record_divisions_table = $this->record_divisions->table_name;
		$this->report->sql("delete a, b from {table} as a left join $record_table as b on a.id = b.report_id where a.year = $year");  //刪除修改記錄
		$this->divisions->sql("delete a, b from {table} as a left join $record_divisions_table as b on a.id = b.division_id where a.year = $year");
		$this->question->delete(array("year"=>$year, "from_type"=> 3)); //刪除上司的話
		$count = $this->config_quick->update(array('processing'=>3),$year);
		if($count==0){$status = 'Already Done.'; }else{$status="OK.";}
		return array('status'=>$status,'change'=>$count,'processing'=>3);
	}

	/**
	 * 取得該年度分數級距設定
	 * @modifyDate 2017-08-14T09:44:23+0800
	 * @param      boolean                  $refresh [description]
	 * @return     [type]                            [description]
	 */
	public function getDistributionRate($refresh = false){
		if (!$this->base_distribution_rate || $refresh) {
				$this->base_distribution_rate = $this->distribution_rate->read(array('lv','name','score_least','score_limit','rate_least','rate_limit'),array('enable'=>1),'order by lv asc')->map('name');
		}
		return $this->base_distribution_rate;
	}

	/**
	 * 部長/CEO調整分數
	 * @modifyDate 2017-10-11
	 * @param      int                   $report_id 考評ID
	 * @param      int                   $change  修改分數
	 * @param      int                   $self_uid 修改的使用者
	 */
	public function setFinallyScoreFix($divi_id, $array_id_change, $self_uid) {
		$result = array();

		if(count($array_id_change)==0){ $this->error('Not Found Reports.'); }

		$divi = $this->divisions->read($divi_id)->check('Not Found This Devision.')->data[0];
		if($divi['owner_staff_id']!=$self_uid){
			$this->error('You Are Not The Division Owner.');
		}

		$divi['_canfix_division'] = $this->divisions->isCanfixDivision();
		$divi['_canfix_ceo'] = $this->divisions->isCanfixCEO();
		if(!$divi['_canfix_division'] && !$divi['_canfix_ceo']){ $this->error('Not Allow To Fix This Division.'); }

		$division_id = $divi['division'];
		$ceo_staff_id = $this->config_quick->data['ceo_staff_id'];
		if($ceo_staff_id==$self_uid && $division_id==1){
			//執行長 在運維中心做加減分
			$a_where = '';
		}else{
			$a_where = "division_id = $division_id and";
		}

		$report_id_keys = array_keys($array_id_change);

		$assessments = $this->report->read(['id','staff_id','assessment_total','assessment_total_division_change','assessment_total_ceo_change'],
		"where id in (".implode($report_id_keys,',').") and $a_where processing_lv = 0")->check('Not Found Assessment Data.')->map();

		foreach($report_id_keys as $rid){
			//check
			if(empty($assessments[$rid])){ $this->error('Not Allow To Fix Report With Id = '.$rid); }
			$report = $assessments[$rid];
			$change_value = $array_id_change[$rid];
			if($report['staff_id']==$self_uid){
				$this->error('You Can Not Fix Yourself.');
			}

			if (!filter_var($change_value, FILTER_VALIDATE_INT) && $change_value!=0) {
					$this->error("You Must Input Integer.");
			}

		}


		$teamMap = $this->config_quick->getDepartmentMap();
		// if(empty($teamMap[ $divi['division'] ])){ $this->error('Not Found This Department.'); }
		$team = $teamMap[ $divi['division'] ];

		if($divi['processing']>=YearPerformanceDivisions::PROCESSING_ARCHITE_WAIT){
			//執行長
			if($self_uid!=$ceo_staff_id){$this->error('Exception Staff Operation.');}
			$colName = 'assessment_total_ceo_change';

			if($divi['processing']<YearPerformanceDivisions::PROCESSING_CEO_ADJUST){
				$this->divisions->update(['processing'=>YearPerformanceDivisions::PROCESSING_CEO_ADJUST],$divi_id);
			}
		}else{
			//部門經理
			$leader = $team['manager_staff_id']>0 ? $team['manager_staff_id'] : $team['supervisor_staff_id'];
			if($self_uid!=$leader){$this->error('Exception Staff Operation.');}
			$colName = 'assessment_total_division_change';
			if($divi['processing']<YearPerformanceDivisions::PROCESSING_DIRECTOR_ADJUST){
				$this->divisions->update(['processing'=>YearPerformanceDivisions::PROCESSING_DIRECTOR_ADJUST],$divi_id);
			}
		}
		// dd($team);

		foreach($array_id_change as $id => &$change){
			$change = (int)$change;
			$update = [ $colName => $change ];
			$report = $assessments[$id];

			$finalTotal = $this->getCaculateFinalGrade($report, $colName, $change);  //沒發生錯誤，代表就是對的

			$update['level'] = $this->getLevelByTotal( $finalTotal );

			$c = $this->report->update($update, $id);
			// if($c>0){ $this->record->save($self_uid, $id, $report, $update); }    //先不用存 save

			$result[] = array_merge($report, $update);

		}

		// dd($result);

		return $result;
	}

	private function getReportShowWithWhere($where, $specify_report_columns = []){
		$staff_table = $this->staff->table_name;
		$team_table = $this->team->table_name;
		$a_columns = 'a.*';
		if ($specify_report_columns && count($specify_report_columns) > 0) {
			foreach ($specify_report_columns as &$_col) {
				$_col = "a.$_col";
			}
			$a_columns = join(',', $specify_report_columns);
		}
		$tmp = $this->report->sql( " select $a_columns, b.name as staff_name, b.first_day as staff_first_day , b.name_en as staff_name_en, b.rank, b.staff_no, b.status as staff_status,
		c.unit_id as department_code, c.name as department_name, d.unit_id as division_code, d.name as division_name
		from {table} as a
		left join $staff_table as b on a.staff_id = b.id
		left join $team_table as c on a.department_id = c.id
		left join $team_table as d on a.division_id = d.id
		where $where order by c.unit_id , b.rank DESC , b.staff_no " )->check('Not Found Report.')->data;

		$config = $this->config_quick->data;

		foreach ($tmp as $key => $value) {
			$tmp[$key]['monthly_average'] = round($value['monthly_average']);
			if ($config) {
				$tmp[$key]['date_start'] = $config['date_start'];
				$tmp[$key]['date_end'] = $config['date_end'];
			} else {
				$tmp[$key]['date_start'] = '';
				$tmp[$key]['date_end'] = '';
			}
		}
		return $tmp;
	}

	// 檢查年績效報表
	private function checkAssessment(){

		$year = $this->year;
		$config = $this->config_quick->data;
		//年績效員工
		// $staff_map = $this->staff->read( array('id','staff_no','name','name_en','lv','status_id','status','post','title', 'title_id','first_day','last_day','department_id','is_leader') , array('id'=>"in($staff_ids)") )->map();
		//年組織結構
		$construct = $this->config_quick->getConstrustAdjLeader();
		// dd($construct);
		//員工
		$staff_map = $this->config_quick->getStaffMap();
		//部門
		$team_map = $this->config_quick->getDepartmentMap();

		$team_leaders_map = $this->config_quick->getDepartmentMultipleLeadersMap();
		// dd($config);
		//年考評單
		$report_map = $this->report->read( array('id','staff_id','level'),array('year'=>$year) )->map('staff_id',true);
		$report_before_map = $this->report->read( array('id','staff_id','level'),array('year'=>(int)$year-1) )->map('staff_id',true);
		//出席
		$staff_ids = join(',',$this->config_quick->getAllAssessmentStaffId());
		// dd($staff_ids);
		$attendance_map = $this->attendance->getMapWithTwoDate(
			$config['date_start'],
			$config['date_end'],
			array('staff_id', 'late', 'early', 'nocard', 'remark', 'vocation_hours'),
			"staff_id in ($staff_ids) and (late >0 or early >0 or nocard >0 or vocation_hours >0)"
		);
		// dd($attendance_map);
		//出席 特殊
		$attendance_special = new \Model\Business\AttendanceSpecial();
		$attendance_special_data = $attendance_special->select(['staff_id','type','value'],['year'=>$year,'type'=>[$attendance_special::TYPE_NOCARD, $attendance_special::TYPE_FORGETCARD]]);
		$attendance_special_map=[];  $type_map = [1=>'nocard',2=>'forgetcard'];
		foreach($attendance_special_data as $adsdv){
			$key = $type_map[$adsdv['type']];
			$attendance_special_map[$adsdv['staff_id']][$key]=$adsdv['value'];
		}
		// dd($attendance_map);
		//月平均
		$average_map = $this->getMonthlyAverageMap();
		// LG($average_map);
		// echo memory_get_usage(true);exit;

		//分配百分率設定
		$this->percents->read(array('lv','type','percent_json'),array('enable'=>1));
		//部屬問卷回饋
		$this->feedback->read(array('target_staff_id','multiple_total','multiple_score'),array('year'=>$year,'status'=>1));
		//部門單
		$division_map = $this->divisions->read(array('id','division'),array('year'=>$year))->map('division',true,false);
		// dd($construct);
		// dd($team_leaders_map);
		// 開始檢查
		foreach($construct as $v){
			//找到部門
			$division = $v['lv']<=2 ? $v['id'] : $v['path_lv_department'][2];
			//部門單 沒有
			if($division>0 && $division==$v['id'] && !isset($division_map[$division]) && count($v['sub_assessment_staff_ids'])>0){
				//產生部門單
				$division_leader = $v['division_leader_id'];
				// $processing = ($v['manager_staff_id']==0? YearPerformanceDivisions::PROCESSING_INIT : ($v['manager_staff_id']==$v['supervisor_staff_id']? YearPerformanceDivisions::PROCESSING_ARCHITE_WAIT: YearPerformanceDivisions::PROCESSING_INIT) );
				//沒有部長的
				$processing = $v['lv']==1 ? YearPerformanceDivisions::PROCESSING_ARCHITE_WAIT : YearPerformanceDivisions::PROCESSING_INIT ;
				$owner_staff_id = $division_leader;
				$divisionData = array(
					'year'=>$year,
					'division'=>$division,
					'owner_staff_id'=> $owner_staff_id,
					'processing'=> $processing
				);
				$division_map[$division] = $divisionData;
				//部門單資料
				$this->divisions->addStorage($divisionData);
			}

			//每層LV
			$default_path_lv_staff = $v['path_lv_leader'];
			//單子會經過的單位的路徑
			$default_path = array();
			$default_path_lv_leaders = [];


			//初始上層主管評論
			$default_upper_comment = array();
			$upper_path = array_slice($v['path_department'],0);
			foreach($upper_path as $up){
				$up_team = $team_map[ $up ];
				$uplv = $up_team['lv'];

				$default_path_lv_staff[$uplv] = array( $up , $default_path_lv_staff[$uplv] );
				if($up_team['manager_staff_id']==0){continue;}

				
				$_leaders = $team_leaders_map[$up];
				$_contents = [];
				foreach ($_leaders as $_l) {
					$_contents[] = '';
				}
				$default_path_lv_leaders[ $uplv ] = $_leaders;
				$default_upper_comment[ $uplv ] = ['staff_id'=>$_leaders, 'content'=>$_contents];
				
				$default_path[] = (int)$up_team['manager_staff_id'];
			}


			//為每個員工產生報表
			foreach($v['staff'] as $sv){
				//檢查是否參與 年考績
				$sid = $sv['id'];
				if (empty($sv['_can_assessment']) || $sv['_can_assessment']!=1) {
						continue;
				}
				//該員工有問卷了 不用產生
				if( isset( $report_map[ $sid ]) ){continue;}
				//ceo 不用產生
				// if( $sid == $config['ceo_staff_id'] ){continue;}

				$staff = $staff_map[ $sid ];
				$myteam = $team_map[ $staff['department_id'] ];
				$report_type = $staff['is_leader']==1 ? 1 : 2;
				//有出勤異常者
				if( isset($attendance_map[ $sid ]) ){
					$attendance_rows = $attendance_map[ $sid ];
				}else{
					$attendance_rows = array();
				}
				//有特殊出勤者
				$attendance_special_rows = isset($attendance_special_map[$sid])?$attendance_special_map[$sid]:[];
				//平均分數
				$monthly_average = isset($average_map[ $sid ]) ? $average_map[ $sid ]['average'] : 0;
				//預設上層主管評論
				$upper_comment = $default_upper_comment;
				if($report_type==1){
					//主管刪掉自己的
					unset($upper_comment[$myteam['lv']]);
				}

				$record_default_assessment = $this->getDefaultAssessment( $report_type, $myteam['lv'], $sid );

				$record = array(
					"year"=>$year,
					"staff_id"=>$sid,
					"owner_staff_id" => $sid,
					"own_department_id" => $staff['department_id'],
					"department_id" => $staff['department_id'],
					"division_id" => $division,
					"staff_is_leader" => $staff['is_leader'],
					"staff_lv" => $staff['lv'],
					"staff_post" => $staff['post'],
					"staff_title" => $staff['title'],
					"staff_title_id" => $staff['title_id'],
					"processing_lv" => $myteam['lv'],
					"path" => json_encode($default_path),
					"path_lv" => json_encode($default_path_lv_staff),
					"path_lv_leaders" => json_encode($default_path_lv_leaders),
					"before_level" => isset($report_before_map[$sid]) ? $report_before_map[$sid]['level'] : '-',
					"monthly_average" => $monthly_average,
					"attendance_json" => $this->report->getAttendanceCountWithRows( $attendance_rows,$attendance_special_rows ),
					"assessment_json" => json_encode($record_default_assessment),
					"assessment_evaluating_json" => json_encode($this->getDefaultAssessmentEvaluating($record_default_assessment, $default_path_lv_staff, $team_leaders_map)),
					// "assessment_total" => 0,
					// "assessment_total_division_change" => 0,
					// "assessment_total_ceo_change" => 0,
					// "level" => '-',
					// "self_contribution" => '',
					// "self_improve" => '',
					"upper_comment" => json_encode($upper_comment)
				);
				// LG($record);

				$this->report->addStorage($record);

			}

		}

		$count = $this->report->addRelease();
		$count2 = $this->divisions->addRelease();

		return $count + $count2;
	}

	private function getMonthlyAverageMap(){
		$map1 = $this->monthly_report->select(array('staff_id','total',), array('releaseFlag'=>'Y','exception'=>0,'year'=>$this->year));
		$map2 = $this->monthly_report_leader->select(array('staff_id','total',), array('releaseFlag'=>'Y','exception'=>0,'year'=>$this->year));
		$result = array();
		foreach( array_merge($map1,$map2) as $v){
			$result[$v['staff_id']][] = $v['total'];
		}
		foreach( $result as &$vv){
			$count = count($vv);
			$score = 0;
			foreach($vv as $vv2){
				$score+= (int)$vv2;
			}
			$vv['average'] = number_format( $score / $count,2 );
			$vv['count'] = $count;
			// $vv['average'] = $score / $vv['count'] ;
		}
		return $result;
	}

	private $default_assessment_score;
	private function getDefaultAssessment($type, $department_lv, $staff_id){
		if( empty($this->default_assessment_score) ){
			$das = &$this->default_assessment_score;
			$das = array();
			$aids = bomb( $this->config_quick->data['assessment_ids'] );
			if( is_array($aids) && strrpos($aids[0],']')>-1 ){ $this->error('Config Not Setted.'); }
			$tm = $this->topic->read(['id','type','applicable'],['enable'=>1])->map();
			foreach($aids as $av){
				$topic = $tm[$av];
				$das[$av] = $topic;
			}
		}
		$defaultScore = $this->default_assessment_score;
		// dd($defaultScore);
		$percents_map = $this->percents->getTypeLvMap();
		if(isset($percents_map["$type-$department_lv"])){
			$percents_json = $percents_map["$type-$department_lv"];
		}else{
			$this->error('Percents Config Not Setted.');
		}

		// dd($percents_map);

		$res = array(); $realScoreTopic = array();
		$myUnderScore = 0;
		$fb_map = $this->feedback->map('target_staff_id',false,true);
		if($type==1){
			if( isset($fb_map[$staff_id]) ){
				$total = 0;
				$score = 0;
				foreach( $fb_map[$staff_id] as $fbv ){
					$total+=(int)$fbv['multiple_total'];
					$score+= min( (int)$fbv['multiple_score'],(int)$fbv['multiple_total'] );
					// $score+=(int)$fbv['multiple_score'];
				}
				$myUnderScore = round($score / $total * 100);
			}
			foreach($defaultScore as $key => $value){
				if($value['applicable']!='normal'){$realScoreTopic[$key]=-1;}
			}
		}else{
			if( isset($fb_map[$staff_id]) ){
				$this->error('Should Not Have Feedback.');
			}
			foreach($defaultScore as $key => $value){
				if($value['applicable']!='leader'){$realScoreTopic[$key]=-1;}
			}
		}
		// LG($json);

		foreach($percents_json as $lv => $p){
			switch($lv){
				case "under": $res['under'] = array('percent'=>$p,'total'=>$myUnderScore);break;
				default: $res[$lv] = array('percent'=>$p,'total'=>0,'score'=>$realScoreTopic);
			}
		}
		return $res;
	}

	private function refreshTopic(){
		$condiY = array('year'=>$this->year);
		$config_data = $this->config_quick->data;

		// dd($config_data);
		//進度
		if($config_data['processing']<4){
			$config_data['processing']=4;
		}
		if($config_data['processing']<=4){
			//選取當年當下的評分題
			$topic = $this->topic->select( array('id'), array('enable'=>1), array('sort'=>'asc') );
			// $topic_data = $topic;
			$topic_ids = $this->topic->getTiny();

			$update_data = array();
			// LG($topic_ids);
			$update_data['assessment_ids'] = join(',',$topic_ids);
			$update_data['processing']=4;
			//assessment date
			$update_data['assessment_date_start'] = date('Y-m-d');
			$update_data['assessment_date_end'] = date('Y-m-d',strtotime( $update_data['assessment_date_start'].' +'.$config_data['assessment_addition_day'].' day' ));

			//update
			$this->config_quick->update( $update_data );

		}else{
			$this->error('This Year Assessment Already Done.');
		}
	}

	private function getDefaultAssessmentEvaluating($ass_json, $path_lV, $team_leaders_map) {
		$res = [];
		try {
			foreach ($ass_json as $key => $val) {
				if (isset($path_lV[$key])) {
					$departmet_id = $path_lV[$key][0];
					if (isset($team_leaders_map[$departmet_id])) {
						$leaders = $team_leaders_map[$departmet_id];
						if (count($leaders) > 1) {
							$res[$key]['leaders'] = $leaders;
							$res[$key]['scores'] = [];
							$res[$key]['totallist'] = [];
							$res[$key]['commited'] = [];
							$res[$key]['should_count'] = [];
							foreach ($leaders as $idx => $leader_id) {
								$res[$key]['scores'][$idx] = $val['score'];
								$res[$key]['totallist'][$idx] = 0;
								$res[$key]['commited'][$idx] = false;
								$res[$key]['should_count'][$idx] = true;
							}
						}
						$ass_json[$key]['_leaders'] = $leaders;
					}
				}
			}
		} catch (Exception $err) {
			dd($err);
		}
		return $res;
	}

	protected function get_team(){
		return $this->team;
	}
	protected function get_staff(){
		return $this->staff;
	}
	protected function get_report(){
		return $this->report;
	}
	protected function get_config(){
		return $this->config;
	}

	/**
	 * 取得年度績效總覽
	 * @modifyDate 2020-11-20
	 * @param      int                   $year            [description]
	 * @param      int                   $staff_level     [description]
	 * @param      boolean                  $with_assignment [description]
	 * @return     array                                    [description]
	 */
	public function getPerfomanceList($self_id, $year, $department_level =0, $with_assignment = false, $is_over = false, $is_enable = true, $specify_report_columns = []) {
		$staff_map = $this->config_quick->getStaffMap();
		if(empty($staff_map[$self_id])){
			return ['error' => 'no staff'];
		}
		$self = $staff_map[$self_id];
		// dd($self);
		$admin_ids = $this->config_quick->getAdminId();
		// $team_map  = $this->config_quick->getDepartmentMap();
		// dd(in_array($self_id, $admin_ids));
		// $where = 'a.year='.$year.' and staff_id != '.$this->config_quick->data['ceo_staff_id'];
		$where = 'a.year='.$year.' ';
		//管理者沒有限制
		$is_admin = in_array($self_id, $admin_ids);
		$my_team_id = $staff_map[$self_id]['department_id'];
		if($is_admin){
			$my_team = $this->config_quick->getDepartmentMap();
		}else{
			$my_team = $this->config_quick->getAllMyDepartment( $my_team_id );
		}
		// dd($my_team);
		//找到可以看的組
		$my_team_ids = [];
		$my_team_lv = 0;
		$my_team_same_lv_other_leaders = [];
		foreach($my_team as $team){
			if( is_numeric($department_level) && $department_level>0){
				if($team['lv']==$department_level){ $my_team_ids[] = $team['id']; }
			}else{
				$my_team_ids[] = $team['id'];
			}

			$im_manager = $team['manager_staff_id'] == $self_id;
			if ($im_manager || in_array($self_id, $team['_other_leaders'])) {
				$my_team_lv = $team['lv'];
				if ($im_manager) {
					$my_team_same_lv_other_leaders = $team['_other_leaders'];
				} else {
					$my_team_same_lv_other_leaders = array_merge(
						[$team['manager_staff_id']],
						array_filter(
							$team['_other_leaders'],
							function ($_) use ($self_id) {
								return $_ != $self_id;
							}
						)
					);
					
				}
			}
		}
		if(count($my_team_ids)==0){$this->error('Not Found Department.');}
		$where.= ' and c.id in('.join(',',$my_team_ids).') ';

		//是否結束
		if($is_over){
			$where.=' and a.processing_lv=0 ';
		}
		//員工只能看自己
		if($self['is_leader']==0 && !$is_admin){
			$where.=' and a.staff_id='.$self_id;
		}

		if($is_enable){
			$where.=' and a.enable=1 ';
		}

		if (!$with_assignment) {
			$where.=' and b.rank > 1 ';
		}
		// 所有報表
		// $all_report = $this->report->read([ 'id','staff_id','owner_staff_id','department_id','monthly_average',
		// 'assessment_json','assessment_total','assessment_total_division_change','assessment_total_ceo_change','level'
		// ],$where)->check('Not Found Any Report.')->data;

		$all_report = $this->getReportShowWithWhere($where, $specify_report_columns);
		// dd($all_report);
		$assessment = ['leader'=>[],'staff'=>[]];

		foreach($all_report as $ari => &$arval){
			// $staff = $staff_map[$arval['staff_id']];
			// if(!$with_assignment && $arval['rank']<=1){ unset($all_report[$ari]);continue;}
			if (isset($arval['assessment_json'])) {
				$arval['assessment_json'] = $this->parseAssessmentJSON_type($arval['assessment_json']);
				$arval['assessment_total_final'] = $arval['assessment_total']+$arval['assessment_total_division_change']+$arval['assessment_total_ceo_change'];

				if ($my_team_lv > 1) {
					$_i_lv = 1;
					while ($_i_lv < $my_team_lv) {
						if (isset($arval['assessment_json'][$_i_lv])) {
							unset($arval['assessment_json'][$_i_lv]);
						}
						$_i_lv += 1;
					}
				}
			}

			if (isset($arval['staff_is_leader']) && $arval['staff_is_leader']==1) {
				if (!in_array($arval['staff_id'], $my_team_same_lv_other_leaders)) {	// 前面撈過自己組底下了 所以自己所在的組一定是最高層
					$assessment['leader'][]=$arval;
				}
			} else {
				$assessment['staff'][]=$arval;
			}
			// if($arval['enable']==0){unset($all_report[$ari]);}
		}

		// dd(count($all_report));

		$return = [
			'assessment' => $assessment,
			'distribution' => [],
		];

		$levelAry = array_column(array_merge($assessment['leader'], $assessment['staff']), 'level');
		// dd( count($levelAry) );
		$distribution = $this->getGradeDistributionCount($levelAry);
		$return['distribution'] = $distribution;

		return $return;
	}
	//解析 分數成 分類總分
	private function parseAssessmentJSON_type($j){
		$tmap = $this->topic->cmap();
		$tmp = [];
		foreach($j as $l=>&$v){
			if(empty($v['score'])){continue;}
			foreach($v['score'] as $qid=>$sc){
				$topic = $tmap[$qid];
				$tmp[$topic['type']][] = $sc;
			}
		}
		foreach($tmp as &$t){
			$tc = count($t);
			$tt = array_sum($t);
			$t = $tt/$tc;
		}
		// dd($tmp);
		$j['_tc'] = $tmp;
		return $j;
	}

	/**
	 * 取得每個級距的人數統計
	 * @modifyDate 2017-10-16
	 * @param      [type]                    $levelAry [description]
	 * @return     [type]                              [description]
	 */
	public function getGradeDistributionCount($levelAry) {
		$gradeDistributionCount = [];
		//先將所有各級距的人員歸零
		$base_distribution_rate = $this->getDistributionRate();
		// $base_distribution_rate = $this->distribution_rate->map('name');

		// dd($base_distribution_rate);
		$keys = array_keys($base_distribution_rate);
		foreach ($keys as $key => $value) {
			$gradeDistributionCount[$value] = 0;
		}
		foreach ($levelAry as $key => $value) {
			if(isset($gradeDistributionCount[$value])){ $gradeDistributionCount[$value]++; }
			// $gradeDistributionCount[$value] += isset($gradeDistributionCount[$value]) ? ($gradeDistributionCount[$value]+1) : 1;
		}
		// $total = count($levelAry);
		$return = [];
		foreach ($gradeDistributionCount as $name => $value) {
			$item = $base_distribution_rate[$name];
			$item['count'] = $value;
			// $item['_count_limit'] = round($value/ () );
			// $item['_count_least'] = $value;
			$return[] = $item;
		}
		return $return;
	}


	/**
	 * 取得 該考評 下一個所屬的 staff_id
	 * @param      [type]                   $report             [description]
	 * @param      [type]                   $next_processing_lv [description]
	 * @return     [type]                                       [description]
	 */
	private function getNextOwnerStaffId($report, $next_processing_lv) {
		if (!isset($report['path_lv'])) {
			$this->error("Error Reort Path");
		}
		$path = $report['path_lv'];
		// dd($next_processing_lv);
		if (!isset($path[$next_processing_lv])) {
			$this->error("Not found Next StaffId [processing_lv = $next_processing_lv]");
		}
		$owner_staff_id = $path[$next_processing_lv][1];
		return $owner_staff_id;
	}

	/**
	 * 把收集到部門單的年考績做往上呈的動作
	 * @modifyDate 2017-10-11
	 * @param      int                   $id      該部門考績單id
	 * @param      int                   $self_id 目前要修改的人員id
	 * @return     [type]                            [description]
	 */
	public function commitDivisionZone($id, $self_id) {
		if(!$this->config_quick->canProcessingYearlyDivision()){$this->error("The Year Processing Not Allow.");}
		//先檢查這張單是否可以由當下使用者
		$divisions = $this->divisions->read($id)->check('Not Found Division')->data[0];

		if ($divisions['owner_staff_id'] != $self_id) {
			$this->error("You Are Not Owner.");
		}
		// dd($divisions);
		$update = $this->getPerformanceDivisionNextOwnerStaffId($divisions, 'commit');
		
		if($divisions['division']==self::DIVISION_CENTER){ //如果是運維中心 直接全部更新
			$c = $this->divisions->update( $update, ['year'=>$divisions['year']] );
		}else{
			$c = $this->divisions->update($update, $id);
		}


		if($c>0){
			$this->record_divisions->commit($self_id, $id, $divisions, $update);
			//簽时間戳
			$done = false;
			switch($update['processing']){
				case 5:$done=true; $email_template_name='yearly_division_to_system';
				case 4:$sign_lv='f'; break;
				case 3:$sign_lv='c'; $email_template_name='yearly_division_to_ceo';break;
				default:$sign_lv=2; $email_template_name='yearly_division_to_consturct';
			}
			$divi_report = $this->report->select(['id','sign_json'],['division_id'=>$divisions['division']]);
			foreach($divi_report as $dv){
				$this->stampReportTime($dv['id'], $sign_lv, $self_id, $dv['sign_json'], $done);
			}

			//寄email
			$this->generalDivisionEmail($email_template_name, $divisions['division'], $update['owner_staff_id']);

			if($done){
				// 部門審核通過了
				if ($this->checkDivisionDone()) {
					// 把 lv 3 中間數的報表等級 更新成 lv 2 的報表 (使其好看一點)
					$this->resettingYearlyReportLevelByLv(3, 2);
				}
			}
		}

		$res = array_merge($divisions,$update);

		return $res;
	}

	/**
	 * 把收集到部門單的年考績做駁回的動作
	 * @modifyDate 2017-10-11
	 * @param      int                   $id      該部門考績單id
	 * @param      int                   $self_id 目前要修改的人員id
	 * @return     [type]                            [description]
	 */
	public function rejectDivisionZone($id, $self_id, $isAdmin=false) {
		if(!$this->config_quick->canProcessingYearlyDivision() && !$isAdmin){$this->error("The Year Processing Not Allow.");}

		//先檢查這張單是否可以由當下使用者
		$divisions = $this->divisions->read($id)->check('Not Found Divsion')->data[0];
		if ($divisions['owner_staff_id'] != $self_id && !$isAdmin) {
			$this->error("You Are Not Owner.");
		}
		if($isAdmin && $divisions['processing']<YearPerformanceDivisions::PROCESSING_CEO_COMMIT){ //取消核准只有 processing <5才可以執行
			$this->error("This Division Not Finish Yet.");
		}
		$update = $this->getPerformanceDivisionNextOwnerStaffId($divisions, 'reject', $isAdmin);
		$target = $isAdmin ? ['year'=>$this->year]: $id;
		$c = $this->divisions->update($update, $target);

		if($c>0){
			$this->record_divisions->back($self_id, $id, $divisions, $update);
			$this->checkDivisionDone();
			//寄信
			$this->generalDivisionEmail('yearly_division_reject_to_director', $divisions['division'], $update['owner_staff_id'] );
		}

		$res = array_merge($divisions,$update);
		return $res;
	}

	/**
	 * 取得部門年考績，下一個流程的人員
	 * @modifyDate 2017-10-11
	 * @param      Array                                 $division 目前的設定
	 * @param      String                                 $id ENUM :['commit', 'reject']
	 * @return     [type]                                     [description]
	 */
	protected function getPerformanceDivisionNextOwnerStaffId($division, $action, $isCancel=false) {

		$config = $this->config_quick->data;
		// dd($config);
		$team_map = $this->config_quick->getDepartmentMap();
		// dd($team_map);
		$team = $team_map[$division['division']];
		// dd($team);
		$current_process = $division['processing'];
		$owner_staff_id = $division['owner_staff_id'];  //先assign 成當下的
		switch ($action) {
			case 'commit':

				$current_process++;
				if ($current_process < YearPerformanceDivisions::PROCESSING_ARCHITE_WAIT) {
					$current_process=YearPerformanceDivisions::PROCESSING_ARCHITE_WAIT;
				}
				if($current_process > YearPerformanceDivisions::PROCESSING_ARCHITE_WAIT){
					$current_process=YearPerformanceDivisions::PROCESSING_CEO_COMMIT;
				}
				break;
			case 'reject':
				if($current_process==YearPerformanceDivisions::PROCESSING_DIRECTOR_ADJUST){ $this->error('Do Not Need To Do.'); }
				// if($team['manager_staff_id']==$team['supervisor_staff_id']){ $this->error('Do Not Need To Do.'); }

				if($isCancel){
					$current_process = YearPerformanceDivisions::PROCESSING_CEO_ADJUST; //取消核准一定是回到執行長
				}else{
					$current_process = YearPerformanceDivisions::PROCESSING_DIRECTOR_ADJUST; //退回一定是回到部長加減
				}
				break;
			default:
				$this->error('Action Wrong.');
				break;
		}

		//取得該部門預設第一個流程狀態，不能小於它
		// $min_processing = $team['manager_staff_id']==$team['supervisor_staff_id']? YearPerformanceDivisions::PROCESSING_ARCHITE_WAIT: YearPerformanceDivisions::PROCESSING_INIT ;
		// if ($current_process < $min_processing) {
			// $this->error('Can Not Return Any More');
		// }
		//取得下一個人員
		switch ($current_process) {
			case YearPerformanceDivisions::PROCESSING_INIT:
			case YearPerformanceDivisions::PROCESSING_DIRECTOR_ADJUST:
				$owner_staff_id = ($team['manager_staff_id']>0?$team['manager_staff_id']:$team['supervisor_staff_id']);
				break;
			case YearPerformanceDivisions::PROCESSING_DIRECTOR_COMMIT:
				$owner_staff_id = $config['constructor_staff_id'];
				if(!$this->checkReportDoneByDivision($division['division'],true)){ $this->error('Not Complete Division Score.'); }  //除了經理之外都要好
				break;
			case YearPerformanceDivisions::PROCESSING_ARCHITE_WAIT:
			case YearPerformanceDivisions::PROCESSING_CEO_ADJUST:
				$owner_staff_id = $config['ceo_staff_id'];
				if(!$this->checkReportDoneByDivision($division['division'],true) && $team['manager_staff_id']!=$owner_staff_id){ $this->error('Not Complete Division Score.'); }  //除了CEO之外都要好
				break;
			case YearPerformanceDivisions::PROCESSING_CEO_COMMIT:
				if($owner_staff_id!=$config['ceo_staff_id']){ $this->error('You Are Not CEO.');}
				$owner_staff_id = YearPerformanceDivisions::OWNER_STAFF_ID_ADMIN;
				break;

			default:
				$this->error("Not Defined Process :".$current_process);

				break;
		}
		return ['processing' => $current_process, 'owner_staff_id' => $owner_staff_id];
	}

	protected function sendEmailByData($template_name, $setting_data, $peoples, $cc_admin = false ){
		$mail = new \Model\MailCenter;
		if(empty($peoples)){
			$year=  $setting_data['year'];
			$reprot_table = $this->report->table;
			$ids = $this->report->select(['staff_id'],['year'=>$year,'enable'=>1]);
			$ids = array_column($ids,'staff_id');
			$emails = $this->staff->getEmailByWhere( ['id'=>$ids] );
		}else if(is_string($peoples)){
			$emails = $peoples;
		}else{
			$emails = $this->staff->select(['email'],['id'=>$peoples]);
			$emails = array_column($emails,'email');
		}
		// dd($emails);
		$mail->addAddress($emails);
		if ($cc_admin) {
			$email_ary = $this->staff->getAdminUserEmail();
			$mail->addCC($email_ary);
		}

		return $mail->sendTemplate($template_name, $setting_data);
	}

	private function mres($value)
	{
			$search = array("\\",  "\x00", "\n",  "\r",  "'",  '"', "\x1a");
			$replace = array("\\\\","\\0","\\n", "\\r", "\'", '\"', "\\Z");

			return str_replace($search, $replace, $value);
	}


	/**
	 * 取得 部長/CEO 加減分之後的分數
	 * @modifyDate 2017-10-11
	 * @param      [type]                   $assessment        [description]
	 * @param      [type]                   $new_adjust_column [description]
	 * @param      [type]                   $new_value         [description]
	 * @return     [type]                                      [description]
	 */
	protected function getCaculateFinalGrade($assessment, $new_adjust_column, $new_value) {
		$assessment_total = isset($assessment['assessment_total']) ? $assessment['assessment_total'] : 0;
		$assessment_total_division_change = isset($assessment['assessment_total_division_change']) ? $assessment['assessment_total_division_change'] : 0;
		$assessment_total_ceo_change = isset($assessment['assessment_total_ceo_change']) ? $assessment['assessment_total_ceo_change'] : 0;
		switch ($new_adjust_column) {
			case 'assessment_total_division_change':
				$assessment_total_division_change = $new_value;
				break;
			case 'assessment_total_ceo_change':
				$assessment_total_ceo_change = $new_value;
				break;
			default:
				throw new \Exception("Not Validate Column, Please Assign Correct Column", 1);  //正常的呼叫，不應該會進來這裡
				break;
		}
		$total = $assessment_total + $assessment_total_division_change + $assessment_total_ceo_change ;
		$max = 100;
		$min = 0;
		if ( ($total <= $max) && ($total >= $min)) {
			return $total;
		} else {
			$this->error('Score Wrong.');
		}
	}

	/**
	 * 取得員工的意見回饋，只有員工本人 和主管 才有權限查看
	 * @modifyDate 2020-11-12
	 * @param      int                   $staff_id      當前查看的員工id
	 * @param      int                   $assessment_id 考評id
	 * @return     array                                  [description]
	 */
	public function getYearlyAllReportWord($staff_id, $assessment_id) {
		$result = array();
		$assessment = $this->report->read(['year', 'staff_id', 'path', 'path_lv', 'path_lv_leaders', 'self_contribution', 'self_improve', 'upper_comment', 'update_date'],$assessment_id)->check('Not Found.')->data[0];
		//檢查當前使用者
		if (!$this->checkAuthorityCanView($staff_id, $assessment)) {
			$this->error('You Have No Promised.');
		}
		//取得今年度的問答題，抓被問的
		$sql = ' SELECT record.from_type, record.year, record.content, record.create_date, record.question_id, question.title as question_title, question.description as question_description
		FROM '.$this->question->table_name.' record
		LEFT JOIN '.$this->question_template->table_name.' question ON question.id = record.question_id
		WHERE target_staff_id = :target_staff_id AND year = :year
		';
		$bind_data =[
									 ':year' => [
																 'value' => $assessment['year'],
																 'type' => \PDO::PARAM_INT,
															],
									 ':target_staff_id' => [
																 'value' => $assessment['staff_id'],
																 'type' => \PDO::PARAM_INT,
															],
								 ];

		$staff_map = $this->config_quick->getStaffMap();
		$question = $this->question->sql($sql, $bind_data)->data;
		unset($assessment['staff_id'], $assessment['path'], $assessment['year'], $assessment['path_lv'], $assessment['path_lv_leaders']);
		shuffle($question);
		$assessment['question'] = $question;
		foreach ($assessment['upper_comment'] as &$comment) {
			$comment['staff_name'] = [];
			foreach ($comment['staff_id'] as $sid) {
				$staff_data = $staff_map[$sid];
				$comment['staff_name'][] = [$staff_data['name'], $staff_data['name_en']];
			}

		}
		return $assessment;
	}

	/**
	 * 取得年考績 歷史流程記錄
	 * @modifyDate 2017-10-12
	 */
	public function getYearlyHistoryRecord($staff_id, $assessment_id, $is_all=false) {
		$result = array();
		$assessment = $this->report->read(['id', 'year', 'staff_id', 'department_id', 'path', 'path_lv', 'path_lv_leaders', 'create_date'],$assessment_id)->check('Not Found This Assessment.')->data[0];
		// dd($assessment);
		//檢查當前使用者
		if (!$this->checkAuthorityCanView($staff_id, $assessment)) {
			$this->error('You Have No Promised.');
		}

		$path_lv_leaders = $assessment['path_lv_leaders'];
		$multiple_leader_staff_id_map = [];
		foreach ($path_lv_leaders as $lv => $leaders) {
			if (count($leaders) > 1) {
				foreach ($leaders as $leader_id) {
					$multiple_leader_staff_id_map[$leader_id] = $lv;
				}
			}
		}

		$where = ['report_id'=>$assessment_id];
		// 如果不是全搜 要加上type
		if(!$is_all){
			// $where['type']='in('.RecordYearPerformanceReport::TYPE_COMMIT.','.RecordYearPerformanceReport::TYPE_RETURN.','.RecordYearPerformanceReport::TYPE_AGREE.')';
			$where['type']='>'.RecordYearPerformanceReport::TYPE_SAVE;
		}

		$record = $this->record->select(['type','changed_json','origin_json','create_date', 'operating_staff_id'], $where, 'order by create_date asc');

		// dd($record);
		$staff_map = $this->config_quick->getStaffMap();
		$team_map = $this->config_quick->getDepartmentMap();


		//根據 type changed_json 取得標題
		$start_record = $this->getRecordFirst( $staff_map[$assessment['staff_id']], $assessment['create_date']);
		$result[] = $start_record;
		// dd($staff_map);
		foreach ($record as $key => $value) {
				$sub_val = $this->getRecordForShow($value);
				$is_multiple_leader_from = isset($multiple_leader_staff_id_map[$sub_val['from']]);
				$is_multiple_leader_to = isset($multiple_leader_staff_id_map[$sub_val['to']]);
				$staff_from = $staff_map[ $sub_val['from'] ];
				$staff_to = $staff_map[ $sub_val['to'] ];
				$staff_operating = $staff_map[ $value['operating_staff_id'] ];

				$sub_val['_is_multiple_leader_from'] = $is_multiple_leader_from;
				$sub_val['_is_multiple_leader_to'] = $is_multiple_leader_to;

				if ($staff_from['department_id'] > 0) {
					$sub_val['from_department_name'] = $team_map[$staff_from['department_id']]['name'];
				} else {
					$sub_val['from_department_name'] = $staff_from['name'];
				}

				if ($staff_to['department_id'] > 0) {
					$sub_val['to_department_name'] = $team_map[$staff_to['department_id']]['name'];
				} else {
					$sub_val['to_department_name'] = $staff_to['name'];
				}

				$sub_val['from_name'] = $staff_from['name'];
				$sub_val['from_name_en'] = $staff_from['name_en'];
				$sub_val['to_name'] = $staff_to['name'];
				$sub_val['to_name_en'] = $staff_to['name_en'];
				$sub_val['operating_staff_id'] =  $value['operating_staff_id'];
				$sub_val['operating_staff_name'] =  $staff_operating['name'];
				$sub_val['operating_staff_name_en'] =  $staff_operating['name_en'];

				$result[] = $sub_val;
		}


		return $result;
	}
	/**
	 * only for getYearlyHistoryRecord and getYearlyAllReportWord
	 */
	private function checkAuthorityCanView($staff_id, $assessment) {
		$path_staff = [];
		//把staff_id加在 path裡，一次判斷是否可以存取
		$path_staff[] = $assessment['staff_id'];
		//加入 constructor_staff_id、ceo_staff_id
		$yearConfig = $this->config_quick->data;
		$path_staff[] = $yearConfig['constructor_staff_id'];
		$path_staff[] = $yearConfig['ceo_staff_id'];
		//加上管理者
		$staff = $this->staff;
		$admin_staff_id = $this->config_quick->getAdminId();
		$path_staff = array_merge($path_staff, $admin_staff_id);
		$path_staff = array_unique($path_staff);
		if ( !in_array($staff_id, $path_staff) && !$this->report->isInLeadership($staff_id) ) {  //需要在 審核流程的人員、ceo、架構管理人員，以及本人 才能看
			return false;
		} else {
			return true;
		}
	}

	/**
	 * 取得考績歷史流程的
	 * @modifyDate 2017-10-13
	 */
	private function getRecordForShow($record_row) {
		$res = [];
		$rc = $record_row['changed_json']; $ro = $record_row['origin_json'];

		$res['from'] = isset($ro['owner_staff_id'])?$ro['owner_staff_id']:0;
		$res['to'] = isset($rc['owner_staff_id'])?$rc['owner_staff_id']:0;
		$res['date'] = $record_row['create_date'];
		$res['type'] = $record_row['type'];

		switch ($res['type']) {
			case RecordYearPerformanceReport::TYPE_COMMIT:    //提交
				$res['title'] = '提交'; break;
			case RecordYearPerformanceReport::TYPE_AGREE :     //同意
				$res['title'] = '完成'; break;
			case RecordYearPerformanceReport::TYPE_RETURN :    //退回
				$res['title'] = '退回'; break;
			default:
				// $this->error('Exception RecordYearPerformanceReport Type.');
				if( isset($rc['enable']) && $rc['enable']==0){
					$res['title'] = '作廢';
				}else{
					$res['title'] = '其它';
				}
		}

		$res['assessment_json'] = isset($ro['assessment_json']) ?$ro['assessment_json']: [];
		$res['reason'] = isset($rc['reason'])? $this->parseReason($rc['reason']) :'';
		return $res;
	}

	private function parseReason($str){
		return urldecode( $str );
		// return html_entity_decode(preg_replace("/u([0-9a-f]{4})/i", iconv('UCS-2BE', 'UTF-8', pack('H4', '\\1')), $str));
	}

	private function getRecordFirst($staff, $create_date){
		$res = $this->getRecordForShow(array(
			'create_date' => $create_date,
			'type' => 0,
			'changed_json' => ['owner_staff_id'=>$staff['id']],
			'origin_json' => ['owner_staff_id'=>$staff['id']]
		));
		$res['title'] = '創立';
		$res['from_name'] = $staff['name'];
		$res['from_name_en'] = $staff['name_en'];
		$res['to_name'] = $staff['name'];
		$res['to_name_en'] = $staff['name_en'];
		return $res;
	}

	/**
	 * 取得年度 特殊人員列表(月考評不計分)
	 * @author Alex Lin <alex.lin@rv88.tw>
	 * @modifyDate 2017-10-17
	 * @param      [type]                   $year [description]
	 * @return     [type]                         [description]
	 */
	public function getYearlySpecialStaff($year) {
		$monthly_report = new MonthlyReport();
		// $monthly_report = new MonthlyReport();
		$separator = '|';
		$sql = ' SELECT staff_id, GROUP_CONCAT(month) as month_list , GROUP_CONCAT(exception_reason SEPARATOR \''.$separator.'\' ) as exception_reason_list
						 FROM '.$this->monthly_report->table_name.'
						 WHERE exception = '.MonthlyReport::EXCEPTION_YES.'
						 AND year = :year
						 GROUP BY staff_id
					 ';
		$sql .= ' UNION SELECT staff_id, GROUP_CONCAT(month) as month_list , GROUP_CONCAT(exception_reason SEPARATOR \''.$separator.'\' ) as exception_reason_list
						 FROM '.$this->monthly_report_leader->table_name.'
						 WHERE exception = '.MonthlyReport::EXCEPTION_YES.'
						 AND year = :year1
						 GROUP BY staff_id
					 ';
		$bind_data =[
									 ':year' => [
																 'value' => $year,
																 'type' => \PDO::PARAM_INT,
															],
									 ':year1' => [
																 'value' => $year,
																 'type' => \PDO::PARAM_INT,
															],
								 ]
							 ;
		$records = $this->monthly_report_leader->sql($sql, $bind_data)->map('staff_id');
		$staff_id_list = array_column($records, 'staff_id');
		$return = [];
		if ($staff_id_list) {
			$sql = 'SELECT staff.id as staff_id, staff.name as staff_name, staff.name_en as staff_name_en, staff.staff_no, staff.title as staff_tilte, staff.post as staff_post, staff.status as staff_status , staff.department_id, department.unit_id, department.name as department_name
			FROM '.$this->staff->table_name.' staff
			LEFT JOIN '.$this->team->table_name.' department ON staff.department_id = department.id
			WHERE staff.id IN ('.implode(',', $staff_id_list).')
			';
			$staff_list = $this->staff->sql($sql)->map('staff_id');
			foreach ($records as $key => $staff) {
				if (isset($staff_list[$key])) {
					$exception_reason_list = explode($separator, $staff['exception_reason_list']);
					$month_list = explode(',', $staff['month_list']);
					if (count($month_list) != count($exception_reason_list)) {
						throw new \Exception("Not Validate Exception Count", 1);
					}
					$exceptions = [];
					if ($exception_reason_list) {
						foreach ($exception_reason_list as $index => $reason) {
							$exceptions[] = [
																	'year' => $year,
																	'month' => $month_list[$index],
																	'reason' => $reason,
															 ];
						}
					}

					$staff['exceptions'] = $exceptions;

					$item = array_merge($staff, $staff_list[$key]);
					unset($staff['exception_reason_list'], $staff['month_list'], $item['_ORDER_POSITION']);
					$return[] = $item;
				}
			}
			return $return;
		} else {
			return [];
		}
	}

	/**
	 * 確認 各月份出缺勤資料是否完整
	 * @method     checkAttendAnce
	 * @author Alex Lin <alex.lin@rv88.tw>
	 * @version    [version]
	 * @modifyDate 2017-08-18T15:07:39+0800
	 * @param      [type]                   $start [description]
	 * @param      [type]                   $end   [description]
	 * @return     [type]                          [description]
	 */
	public function checkAttendAnce($start, $end) {
		$atd_authority = $this->attendance->isExistDate( array($start, $end) );
		if ($atd_authority) {
			return ['status' => true];
		} else {
			return ['status' => false, 'error' => 'Attendance Have Not Complete'];
		}
	}

	/**
	 * 檢查各月份績效結果是否完整
	 */
	public function checkMonthlyProcessing($config) {
		$year = $config['year'];

		$start_time = strtotime($config['date_start']);
		$end_time = strtotime($config['date_end']);

		//找到月績效真正的月份
		$mc = new ConfigCyclical();
		$m_data = $mc->select();
		foreach($m_data as $row){
			$r_start_d = $row['month']==1 ? ($row['year']-1).'-12-'.$row['day_start'] : $row['year'].'-'.((int)$row['month']-1).'-'.$row['day_start'];
			$r_end_d = $row['year'].'-'.($row['month']).'-'.$row['day_end'];
			$r_time_start = strtotime($r_start_d);
			$r_time_end = strtotime($r_end_d);
			if( $start_time >= $r_time_start && $start_time <= $r_time_end ){
				$start_year = $row['year'];
				$start_month = $row['month'];
			}
			if( $end_time >= $r_time_start && $end_time <= $r_time_end ){
				$end_year = $row['year'];
				$end_month = $row['month'];
			}
		}

		if(empty($start_year) || empty($start_month) || empty($end_year) || empty($end_month)){
			return ['status' => false, 'error' => 'Monthly Not Exist.'];
		}

		$mp = new MonthlyProcessing();
		$mpm = $mp->select(array('year','month'), "where (year = $start_year and month = $start_month) or (year = $end_year and month = $end_month) ");
		if (count($mpm)==2) {
			return ['status' => true];
		} else {
			return ['status' => false, 'error' => 'Monthly Process Have Not Complete.'];
		}
	}
	/**
	 * 檢查部屬回饋問卷是否已停止流程
	 * @modifyDate 2017-9-29
	 * @param      [type]                   $year [description]
	 * @return     [type]                         [description]
	 */
	public function checkFeedBackAvaiable() {
		$year = $this->year;
		$data = $this->feedback->select(array('id'), array('status'=>0, 'year' => $year));
		$res = array();
		if ( (count($data)==0) ) {
			$res['status'] = true;
		} else {
			$res['status'] = false;
			$res['error'] = 'Feedback Have Not complete';
		}
		return $res;
	}

	/**
	 * 年考評延遲的通知信（年考評期間已結束，但年考評還有單還未至決策者核准）寄給當前的擁有者
	 * @method     notifyOverdeadline
	 * @author Alex Lin <alex.lin@rv88.tw>
	 * @version    [version]
	 * @modifyDate 2017-08-21T09:39:21+0800
	 * @param      [type]                   $year [description]
	 * @return     [type]                         [description]
	 */
	public function notifyOverdeadline($year, $now) {
		$yearConfig = $this->getConfig($year);
		if (!$yearConfig) {
			 throw new \Exception("Not Found This Year Config", 1);
		}
		//檢查是否到期
		$now = strtotime(date("Y-m-d 23:59:59", strtotime($now)));
		$config_date_end = strtotime(date("Y-m-d 23:59:59", strtotime($yearConfig['date_end'])));
		$config_date_start = strtotime(date("Y-m-d 00:00:00", strtotime($yearConfig['date_start'])));

		if ( $now < $config_date_start) {
				throw new \Exception("This Year Assessment Is Not Started", 1);
		}

		if ( $now <= $config_date_end) {
				throw new \Exception("This Year Assessment Is Not Finish", 1);
		}
		$in_processing_lv = [
														YearPerformanceReport::PROCESSING_LV_CEO,
														YearPerformanceReport::PROCESSING_LV_DIRECTOR,
														YearPerformanceReport::PROCESSING_LV_MINISTER,
														YearPerformanceReport::PROCESSING_LV_LEADER,
												];
		$report_list = $this->report->read(['owner_staff_id', 'department_id', 'staff_id'], ['processing_lv'=> ' in ('.implode(',', $in_processing_lv).')']);

		$id = array_column($report_list, 'id');
		if ($report_list) {
				//要寄的對象
				$owner_staff_id_list = array_column($report_list->data, 'owner_staff_id');
				//考評單上面的人的單位
				$department_id_list = array_column($report_list->data, 'department_id');
				//考評單上面的人
				$staff_id_list = array_column($report_list->data, 'staff_id');
				//批次取得Email、姓名、英文姓名
				$staff_list = $this->staff->read(['id', 'email', 'name', 'name_en'], ['id' => 'in ('.implode(',', array_merge($owner_staff_id_list, $staff_id_list)).')'])->map('id');
				$department_list = $this->team->read(['id', 'name'], ['id' => 'in ('.implode(',', $department_id_list).')'])->map('id');
				$mail = new \Model\MailCenter;

				//CC給系統管理員
				$admin_email_ary = $this->staff->getAdminUserEmail();
				$site_host = 'http://'.config('mail.SCHEDULE_CONFIG.web_root');
				foreach ($report_list->data as $key => $value) {
						if (isset($staff_list[$value['owner_staff_id']])) {
								$mail_address = $staff_list[$value['owner_staff_id']]['email'];
								$divison_name = $department_list[$value['department_id']]['name'];
								$staff_name_en = isset($staff_list[$value['staff_id']]) ? $staff_list[$value['staff_id']]['name_en'] : '';
								$staff_name = isset($staff_list[$value['staff_id']]) ? $staff_list[$value['staff_id']]['name'] : '';
								$mail_data = [
														'year' => $year,
														'yearly_assessment_dead_line' => $yearConfig['date_end'],
														'site_host' => $site_host,
														'divison_name' => $divison_name,
														'staff_name_en' => $staff_name_en,
														'staff_name' => $staff_name,
													 ];
								$mail->addAddress($mail_address);
								$mail->addCC($admin_email_ary);
								$mail->sendTemplate('yearly_assessment_to_delay', $mail_data);
								$mail->clear(['addresses', 'cc', 'bcc']);
						}

				}
		}
		return ['status' => true];
	}

	/**
	 * 管理者取到 需要查看的年報表
	 * @modifyDate 2017-10-17
	 * @param      [type]                         $year     [description]
	 * @param      [type]                         $staff_id [description]
	 * @return     [type]                                   [description]
	 */
	public function getYearlyAssessmentByAdmin($staff_id) {
		$res = [];
		$config_data = $this->config_quick->data;
		$year = $this->year;

		// $construst = $this->config_quick->getConstrust();
		$staff_map = $this->config_quick->getStaffMap();
		$team_map = $this->config_quick->getDepartmentMap();

		$report_data = $this->report->select(['id','staff_id','staff_post','staff_title','owner_staff_id','department_id','division_id','path','path_lv','processing_lv','level','enable','assessment_json'],[ 'year'=>$year ]);
		$division_data = $this->divisions->select([ 'year'=>$year ]);
		$res['report_info'] =[];
		$res['report_info']['total'] = count($report_data);
		$res['report_info']['valid'] =0;
		$res['report_info']['finished'] =0;
		$res['report_info']['pending'] =0;
		// $res['report_info']['pending_lv'] =[];


		foreach($report_data as &$data){
			$data['staff_name'] = $staff_map[$data['staff_id']]['name'];
			$data['staff_name_en'] = $staff_map[$data['staff_id']]['name_en'];
			$data['staff_no'] = $staff_map[$data['staff_id']]['staff_no'];
			$data['owner_staff_name'] = $staff_map[$data['owner_staff_id']]['name'];
			$data['owner_staff_name_en'] = $staff_map[$data['owner_staff_id']]['name_en'];
			$data['owner_staff_no'] = $staff_map[$data['owner_staff_id']]['staff_no'];
			$data['department_name'] = $team_map[$data['department_id']]['name'];
			$data['department_unit_id'] = $team_map[$data['department_id']]['unit_id'];
			$data['department_lv'] = $team_map[$data['department_id']]['lv'];
			$data['division_name'] = $team_map[$data['division_id']]['name'];
			$data['division_unit_id'] = $team_map[$data['division_id']]['unit_id'];
			$data['division_lv'] = $team_map[$data['division_id']]['lv'];
			$data['division_lv'] = $data['owner_staff_id']==0;

			$data['_status'] = $this->getStatusByReport($data);


			unset($data['assessment_json']);

			if($data['enable']==1){$res['report_info']['valid']++;}else{continue;}
			if($data['owner_staff_id']==0){$res['report_info']['finished']++;$data['finished']=1;}else{$res['report_info']['pending']++;$data['finished']=0;}
			// $res['report_info']['pending_lv'][$data['processing_lv']]= isset($res['report_info']['pending_lv'][$data['processing_lv']]) ? $res['report_info']['pending_lv'][$data['processing_lv']]+1 : 1;
			// $data[]
		}
		$res['reports'] = $report_data;
		$res['divisions'] = $division_data;


		return $res;
	}

	private function getStatusByReport($d){
		if($d['enable']==0){return 4;}
		if($d['owner_staff_id']==0){return 3;}
		foreach($d['assessment_json']['self']['score'] as $dv){
			if($dv<0){return 1;}
		}return 2;
	}

	/**
	 * 年考評作廢
	 * @modifyDate 2017-10-18
	 */
	public function setAssessmentCancel($assessment_id, $enable, $self_id) {
		if($this->config_quick->isYearlyAssessmentFinished()){$this->error("This Year Is Been Finished.");}
		$assessment = $this->report->read(['id','division_id','enable','staff_id','owner_staff_id','processing_lv','assessment_total','assessment_total_division_change','assessment_total_ceo_change','level','path','path_lv'],$assessment_id)->check('Not Found This Report.')->data[0];
		// $admin_staff_id = $this->staff->getAdminStaffId();
		$divi= $this->divisions->select(['owner_staff_id'=>$assessment['staff_id']]);
		if( count($divi)>0 ){ $this->error('Can Not Cancel The Division Leader.'); }
		$update = ['enable' => $enable];
		$c = $this->report->update($update, ['id' => $assessment_id]);
		if($c>0){
			$this->record->other($self_id, $assessment_id, $assessment, $update);
			$assessment['enable'] = $enable;
			$assessment['status'] = 'OK.';
			$init_processing = count($assessment['path_lv'])<=1 ? 3: 0;  //送審上層的人員只有一位  代表示 部門以上級
			$this->refreshDivisionStatus( $assessment['division_id'], $init_processing );
		}else{
			$assessment['status'] = 'Nothing Changed.';
		}


		return $assessment;
	}


	/**
	 *  取得年考績 自己的完整報表
	 */
	public function getYearlyReportMyTranscripts($self_id){
		$res = [];
		$config_data = $this->config_quick->data;
		$fqis = $config_data['feedback_question_ids'];
		// $fcis = $config_data['feedback_choice_ids'];
		// $ais = $config_data['assessment_ids'];
		$my_report = $this->report->read(
		['staff_id','department_id','division_id','staff_post','staff_title','monthly_average','level','self_contribution','self_improve','upper_comment']
		,['staff_id'=>$self_id,'year'=>$this->year])->check('Not Found This Report.')->data[0];

		$res = $my_report;

		$team_map = $this->config_quick->getDepartmentMap();
		$staff_map = $this->config_quick->getStaffMap();

		$res['staff'] = $staff_map[ $res['staff_id'] ];
		$res['division'] = $team_map[ $res['division_id'] ];
		$res['department'] = $team_map[ $res['department_id'] ];

		$res['staff']['title']=$res['staff_title'];
		$res['staff']['post']=$res['staff_post'];
		unset($res['staff_title']);unset($res['staff_post']);
		unset($res['staff']['_can_feedback']);unset($res['staff']['_can_assessment']);

		foreach($res['upper_comment'] as &$val){
			$sid = $val['staff_id'];
			$val['staff_name']=$staff_map[$sid]['name'];
			$val['staff_name_en']=$staff_map[$sid]['name_en'];
		}

		$qq = $this->question_template->read(['id','description'],['id'=>'in('.$fqis.')'])->cmap();
		$res['feedbacks'] = $this->question->select(['question_id','content','create_date'],['target_staff_id'=>$self_id,'year'=>$this->year]);
		// $res['qq'] = $qq;
		foreach($res['feedbacks'] as &$fval){
			$fval['description'] = $qq[$fval['question_id']]['description'];
		}
		return $res;
	}

	/**
	 *  寫入時間戳  /  審核結束时把單子指回0
	 */
	public function stampReportTime($report_id, $lv, $staff_id, $sign_json=null, $done=false){
		$tt = time();$update=[];
		if(is_null($sign_json)){
			$sign_json = $this->report->read(['sign_json'],$report_id)->check('Not Found Report.')->data[0]['sign_json'];
		}
		if (isset($sign_json[$lv])) {
			$num_sign = count($sign_json[$lv]);
			$i = 0;
			while ($i < $num_sign) {
				$signed_staff_id = $sign_json[$lv][$i];
				if ($signed_staff_id == $staff_id) {
					$sign_json[$lv][$i+1] = $tt;
					break;
				}
				$i += 2;
			}
			if ($i >= $num_sign) {
				array_push($sign_json[$lv], $staff_id, $tt);
			}
		} else {
			$sign_json[$lv] = [$staff_id,$tt];
		}
		
		$update['sign_json'] = $sign_json;
		if($done){ $update['owner_staff_id']=0; }
		$c = $this->report->update($update,$report_id);
		return $c>0;
	}
	//部門map
	public function getQuickDepartmentMap(){
		return $this->config_quick->getDepartmentMap();
	}


	/**
	 * 
	 */
	public function getYearlyAssessmentScoreDetailByAdmin() {
		$result = [];
		$config = $this->config_quick->data;
		$team_map = $this->config_quick->getDepartmentMap();
		$staff_map = $this->config_quick->getStaffMap();

		$staff_map_for_show = [];
		
		// $result['team'] = $team_map;
		$reports = $this->report->select(['assessment_evaluating_json', 'assessment_json', 'assessment_total', 'department_id', 'division_id', 'staff_id', 'staff_is_leader', 'staff_lv', 'staff_post', 'staff_title', 'path', 'path_lv', 'path_lv_leaders'], ['year' => $this->year]);
		foreach ($reports as &$report) {
			$staff_id = $report['staff_id'];
			$staff = $staff_map[$staff_id];
			$team = $team_map[$report['department_id']];
			$report['staff_name'] = $staff['name'];
			$report['staff_name_en'] = $staff['name_en'];
			$report['department_name'] = $team['name'];
			$report['department_unit_id'] = $team['unit_id'];
		}

		foreach ($staff_map as $staff) {
			$sid = $staff['id'];
			$staff_map_for_show[$sid] = [
				'name' => $staff['name'],
				'name_en' => $staff['name_en'],
				'staff_no' => $staff['staff_no'],
			];
		}
		
		$result['reports'] = $reports;
		$result['staff_map'] = $staff_map_for_show;
		return $result;
	}

}
?>
