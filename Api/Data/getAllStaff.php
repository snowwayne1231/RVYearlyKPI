<?php

include __DIR__."/../ApiCore.php";
include BASE_PATH.'/Model/dbBusiness/Staff.php';
include BASE_PATH.'/Model/dbBusiness/StaffHistory.php';

use Model\Business\Staff;
use Model\Business\StaffHistory;

$api = new ApiCore();

if( $api->SC->isLogin() && $api->SC->isAdmin() ){

	$staff = new Staff();
	$staffHistory = new StaffHistory();

	$col = null;

	$result = $staff->select( $col, '' );
	$resHistory = $staffHistory->filterOnStay();

	foreach($result as &$val){
		if($val['status_id'] == $staff::STATUS_STAY && isset($resHistory[$val['id']])){
			//現在是留停的人，要回傳留停時間
			$val['stay_start_day'] = $resHistory[$val['id']]['stay_start_day'];
			$val['stay_end_day'] = $resHistory[$val['id']]['stay_end_day'];
			$val['return_day'] = date("Y-m-d",strtotime($val['stay_end_day']."+1 day"));
		}else{
			$val['stay_start_day'] = '0000-00-00';
			$val['stay_end_day'] = '0000-00-00';
			$val['return_day'] = '0000-00-00';
		}
	}

	//成功結果
	$api->setArray($result);

}else{
	$api->denied();
}

print $api->getJSON();

?>