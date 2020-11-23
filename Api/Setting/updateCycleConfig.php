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

	$config = new ConfigCyclical($year, $month);
	$old_config_data = $config->data;
	

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

		//修改特殊月出缺勤
		$adms = new \Model\Business\AttendanceMonthlySpecial();
		$day_start = (int) $day_start;
		$day_end = (int) $day_end;
		$year = (int) $year;
		$month = (int) $month;

		$before_month = (int) $month - 1;
		$new_date_start = "$year-$before_month-$day_start";
		$new_date_end = "$year-$month-$day_end";
		

		$old_date_start = "$year-$before_month-".$old_config_data['day_start'];
		$old_date_end = "$year-$month-".$old_config_data['day_end'];
		
		// 修改了起始時間
		if ($old_config_data['day_start'] > $day_start) {
			// 涵蓋範圍更大
		} else if ($old_config_data['day_start'] < $day_start) {
			$range_day_end = $day_start-1;
			$range_day_start = $old_config_data['day_start'];
			$range_date_start = "$year-$month-$range_day_start";
			$range_date_end = "$year-$month-$range_day_end";
			$range_update_year = $month == 1 ? $year - 1 : $year;
			$range_update_month = $month == 1 ? 12 : $month - 1;

			$adms->update(['year'=> $range_update_year, 'month'=> $range_update_month], ['date' => ['between', $range_date_start, $range_date_end]]);
		}

		//修改了結束時間
		if ($old_config_data['day_end'] > $day_end) {
			$range_day_end = $old_config_data['day_end'];
			$range_day_start = $day_end +1;
			$range_date_start = "$year-$month-$range_day_start";
			$range_date_end = "$year-$month-$range_day_end";
			$range_update_year = $month == 12 ? $year +1 : $year;
			$range_update_month = $month == 12 ? 1 : $month +1;

			$adms->update(['year'=> $range_update_year, 'month'=> $range_update_month], ['date' => ['between', $range_date_start, $range_date_end]]);
		} else if ($old_config_data['day_end'] < $day_end) {
			// 涵蓋範圍更大
		}

		$adms->update(['year'=> $year, 'month'=> $month], ['date' => ['between', $new_date_start, $new_date_end]]);

	}

	$api->setArray($change);

}else{
	// var_dump($_POST);
}

print $api->getJSON();

?>