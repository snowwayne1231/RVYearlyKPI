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

	}else{
		// $result = $ya->getAssessmentWithOwner( $self,$self );
		$result = $ya->getAssessment( $self );
	}

	foreach($result as &$val){
		if($resSH = $sh->getStayWithStaff($val['staff_id'])){
			$val['staff_stay'] = $resSH;
		}else{
			$val['staff_stay'] = array();
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