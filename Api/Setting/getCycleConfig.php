<?php

include __DIR__."/../ApiCore.php";
include BASE_PATH.'/Model/dbBusiness/ConfigCyclical.php';
include BASE_PATH.'/Model/dbBusiness/MonthlyProcessing.php';

$api = new ApiCore($_POST);

use Model\Business\ConfigCyclical;
use Model\Business\MonthlyProcessing;

if( $api->checkPost(array('year', 'month')) ){

	$year = $api->post('year');
	$month = $api->post('month');

	$condition = array('year'=>$year,'month'=>$month);


	$config = new ConfigCyclical($year,$month);

	$cdata = $config->data;

	$isLaunched = $cdata['monthly_launched'] == 1;

	$mp = new MonthlyProcessing();
	$mpd = $mp->select(array('id'),array('year'=>$year,'month'=>$month));
	if(count($mpd) == 0){
		$cdata['has_monthly_data'] = 0;//未產生考評資料
	}else{
		$cdata['has_monthly_data'] = 1;//產生過考評資料
	}

	if($isLaunched){

		$mpd = $mp->select(array('id'),array('year'=>$year,'month'=>$month,'status_code'=>'<5'));
		$isComplete = count($mpd) == 0;

		$nowDate = date("Y-m-d", time() - 86400);
		$cutDate = $cdata['cut_off_date'];
		// dd($cutDate);
		// dd($mpd);
		$isOverDate = strtotime( $nowDate ) > strtotime( $cutDate );

		$cdata['settingAllow'] = $isComplete && $isOverDate;
		$cdata['overDate'] = $isOverDate;
	}else{
		$cdata['settingAllow'] = true;
		$cdata['overDate'] = false;
	}


	$api->setArray($cdata);

}else{
	// var_dump($_POST);
}

print $api->getJSON();

?>