<?php
/**
 * 將月考評的評語對應回到考評單上
 *
 * 2018-12-22 Carmen Yeh
 */

include __DIR__."/../ApiCore.php";
include BASE_PATH.'/Model/dbBusiness/Config.php';

$api = new ApiCore($_REQUEST);

use Model\Business\Config;


if($api->checkPost(array("year","month"))){

	$year  = $api->post('year');
	$month = $api->post('month');

	$config = new Config();

	// 查出沒有被對應到的 評語ID
	$sql = "SELECT a.id FROM rv_record_personal_comment AS a
			LEFT JOIN (SELECT id,comment_id FROM rv_monthly_report) AS b ON a.report_id = b.id AND a.report_type = 2
			LEFT JOIN (SELECT id,comment_id FROM rv_monthly_report_leader) AS c ON a.report_id = c.id AND a.report_type = 1
			WHERE b.id IS NULL AND c.id IS NULL";
	if($resComment = $config->sql($sql)->data){
		$arrCommentID = array();
		foreach($resComment as $cVal){
			$arrCommentID[] = $cVal['id'];
		}

		//更新 評語資料
		$sql = "UPDATE rv_record_personal_comment AS a
				LEFT JOIN (SELECT id,staff_id FROM rv_monthly_report WHERE year=$year AND month=$month) AS b ON a.target_staff_id = b.staff_id AND a.report_type = 2
				LEFT JOIN (SELECT id,staff_id FROM rv_monthly_report_leader WHERE year=$year AND month=$month) AS c ON a.target_staff_id = c.staff_id AND a.report_type = 1
				SET a.report_id = IF(b.id>0, b.id, c.id)
				WHERE a.id IN (".implode(",", $arrCommentID).") AND (b.id > 0 OR c.id > 0)";
		$config->sql($sql);

/*
select * from rv_record_personal_comment AS a
LEFT JOIN (SELECT id,staff_id FROM rv_monthly_report WHERE year=2018 AND month=12) AS b ON a.target_staff_id = b.staff_id AND a.report_type = 2
LEFT JOIN (SELECT id,staff_id FROM rv_monthly_report_leader WHERE year=2018 AND month=12) AS c ON a.target_staff_id = c.staff_id AND a.report_type = 1
*/

		//更新 月績效
		$sql = "SELECT id, report_id, report_type FROM rv_record_personal_comment WHERE id IN (".implode(",", $arrCommentID).") AND status > 0";
		if($res = $config->sql($sql)->data){
			$leader = array();
			$staff = array();
			foreach($res as $val){
				if($val['report_type'] == 1){
					if(empty($leader[$val['report_id']])){
						$leader[$val['report_id']] = ','.$val['id'];
					}else{
						$leader[$val['report_id']] .= ','.$val['id'];
					}
				}else if($val['report_type'] == 2){
					if(empty($staff[$val['report_id']])){
						$staff[$val['report_id']] = ','.$val['id'];
					}else{
						$staff[$val['report_id']] .= ','.$val['id'];
					}
				}
			}

			foreach($leader as $lKey => $lVal){
				$sql = "UPDATE rv_monthly_report_leader SET comment_id = '$lVal' WHERE id = $lKey";
				$config->sql($sql);
			}

			foreach($staff as $sKey => $sVal){
				$sql = "UPDATE rv_monthly_report SET comment_id = '$sVal' WHERE id = $sKey";
				$config->sql($sql);
			}

		}


	}

	echo '更新完成';


}

