<?php
include __DIR__."/../ApiCore.php";
include BASE_PATH.'/Model/dbBusiness/ConfigCyclical.php';

$api = new ApiCore($_POST);

use Model\Business\ConfigCyclical;

if( $api->checkPost(array('year', 'month', 'day_start', 'day_end', 'day_cut_addition')) ){

	$year             = $api->post('year');
	$month            = $api->post('month');
	$day_start        = $api->post('day_start');//起始日期
	$day_end          = $api->post('day_end');  //結束日期
	$day_cut_addition = $api->post('day_cut_addition');//考評天數
	$monthly_launched = $api->post('monthly_launched');//啟動考評 開 | 關

	$condition = array('year'=>$year,'month'=>$month);

	$endDate = "$year-$month-$day_end";//結束年月日

	if($api->isFuture($endDate) && $monthly_launched == 1){ //結束年月日是未來時間，且考評式啟動的，要顯示錯誤訊息
		$api->denied('End Days At Future. Can Not Be Launch.');
	}

	$config = new ConfigCyclical();

	$cutOffDate = date( 'Y-m-d' , strtotime("$year-$month-$day_end + $day_cut_addition days"));//計算考評結束日期

	//要存入資料庫中的資料
	$change = array(
		'day_start'        =>$day_start,       //起始日期
		'day_end'          =>$day_end,         //結束日期
		'day_cut_addition' =>$day_cut_addition,//考評天數
		'cut_off_date'     =>$cutOffDate,      //考評結束日期
		'monthly_launched' =>$monthly_launched //啟動考評 開 | 關
	);

	//修改資料
	$hasChanged = $config->update($change, $condition);
	if($hasChanged){
		//修改成功
		//儲存修改紀錄
		$self_id = $api->SC->getId();
		$change['year'] = $year;
		$change['month']= $month;
		$record = new \Model\Business\RecordAdmin( $self_id );
		$record->type($record::TYPE_MONTH)->update( $change );

		$change['hasChanged'] = 1;
	}

	$api->setArray($change);

}else{
	// var_dump($_POST);
}

print $api->getJSON();

?>