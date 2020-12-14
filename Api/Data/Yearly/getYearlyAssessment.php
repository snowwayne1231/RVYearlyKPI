<?php
include __DIR__."/../../ApiCore.php";
include BASE_PATH.'/Model/dbBusiness/Multiple/YearlyAssessment.php';
include_once BASE_PATH.'/Model/dbBusiness/Multiple/StaffHistory.php';

$api = new ApiCore($_POST);

use Model\Business\Multiple\YearlyAssessment;
use Model\Business\Multiple\StaffHistory;


if( $api->checkPost(array('year')) && $api->SC->isLogin() ){

	$year = $api->post('year');
	$mode = $api->post('mode');
	$assessment_id = $api->post('assessment_id');
	// cache year;
	$api->SC->set('year',$year);
	$self = $api->SC->getId();


	$ya = new YearlyAssessment( $year );
	$sh = new StaffHistory();

	if($mode=='self'){
		$result = $ya->getAssessmentWithSelf($self);
	}else if(isset($assessment_id) && is_numeric($assessment_id)){
		$result = $ya->getAssessmentWithId( $assessment_id, $self );
	}else if($mode=='leader'){
		// $member = $api->SC->getMember();
		// $team_lv = $member['_department_lv'];
		// $teams = $member['_department_sub'];
		// $teams[] = $member['department_id'];

		// $result = $ya->getAssessmentWithLeader( $self,$teams,$team_lv );
		$result = $ya->getAssessment( $self );

	} else {
		// $result = $ya->getAssessmentWithOwner( $self,$self );
		$result = $ya->getAssessment( $self );
	}

	// dd($result);

	foreach($result as &$val){
		if ($resSH = $sh->getStayWithStaff($val['staff_id'])) {
			$val['staff_stay'] = $resSH;
		} else {
			$val['staff_stay'] = array();
		}

		if ($val['processing_lv'] && isset($val['path_lv_leaders'][$val['processing_lv']]) && $val['staff_id'] != $self) {
			$val['_is_on_multiple_leader_process'] = count($val['path_lv_leaders'][$val['processing_lv']]) > 1 && isset($val['assessment_evaluating_json'][$val['processing_lv']]);

			if ($val['_is_on_multiple_leader_process']) {
				$this_aej = $val['assessment_evaluating_json'][$val['processing_lv']];
				$idx_leader = array_search($self, $this_aej['leaders']);
				if ($idx_leader && $idx_leader >= 0) {
					$val['_should_count'] = $this_aej['should_count'][$idx_leader];
				} else {
					$val['_should_count'] = true;
				}
			} else {
				$val['_should_count'] = true;
			}
			
		} else {
			$val['_is_on_multiple_leader_process'] = false;
			$val['_should_count'] = true;
		}

		if (isset($val['upper_comment'])) {
			$processing_lv = $val['processing_lv'];
			foreach ($val['upper_comment'] as $lv => $comment) {
				if ($lv < $processing_lv) {
					unset($val['upper_comment'][$lv]);
				} else if ($lv == $processing_lv){
					$staff_id = $comment['staff_id'];
					if (is_array($staff_id)) {
						if (in_array($self, $staff_id)) { continue; }
					} else {
						if ($staff_id == $self) { continue; }
					}
					unset($val['upper_comment'][$lv]);
				}
			}
		}
	}


	// $result = $ya->getAssessmentWithAdmin( $api->post('year') );

	//結果
	$api->setArray($result);


}else{
	$api->denied('You Have Not Promised.');
}

print $api->getJSON();

?>