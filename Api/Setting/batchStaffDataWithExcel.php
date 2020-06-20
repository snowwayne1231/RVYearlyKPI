<?php
include_once __DIR__."/../ApiCore.php";
include_once BASE_PATH.'/Model/dbBusiness/Multiple/Staff.php';
include_once BASE_PATH.'/Model/dbBusiness/Department.php';
include_once BASE_PATH.'/Model/dbBusiness/MonthlyReport.php';
include_once BASE_PATH.'/Model/dbBusiness/MonthlyReportLeader.php';
include_once BASE_PATH.'/Model/dbBusiness/MonthlyProcessing.php';
include_once BASE_PATH.'/Model/dbBusiness/ConfigCyclical.php';
use Model\Business\Multiple\Staff;
use Model\Business\Department;
use Model\Business\MonthlyReport;
use Model\Business\MonthlyReportLeader;
use Model\Business\MonthlyProcessing;
use Model\Business\ConfigCyclical;

$api = new ApiCore($_POST);

if( $api->SC->isAdmin() ){

	$files = $api->getFiles();
	if(count($files)==0){
		$api->denied('沒有檔案.');
	}
	//當月評分
	$webconfig = new ConfigCyclical();
	$year = date("Y");
	$month = date("m");
	$config = $webconfig->getConfigWithDate($year,$month);
	if($config['monthly_launched']==1 && $api->isFuture($config['cut_off_date']) ){
		$api->denied('Can Not Modify Staff When Monthly On Launch.');
	}
	if( $api->isPast($config['RangeEnd']) ){
		if($month==12){
			$month = 1;
			$year += 1;
		}else{
			$month += 1;
		}
	}

	require_once RP('/Model/PHPExcel.php');
	require_once RP('/Model/PHPExcel/IOFactory.php');


	$result = array();
	//員工欄位
	$colData = array(
		"unit_id"=>"單位代號",
		"unit_name"=>"單位名稱",
		"staff_no"=>"員工編號",
		"account"=>"員工帳號",
		"name"=>"員工姓名",
		"name_en"=>"員工英文名",
		"title"=>"職稱",
		"post"=>"職務",
		"passwd"=>"密碼",
		"email"=>"電子郵件",
		"first_day"=>"到職日",
		"last_day"=>"離職日",
		"update_date"=>"換單位日",
		"stay_start_day"=>"留停起日",
		"stay_end_day"=>"留停迄日",
		"return_day"=>"復職日",
		"status"=>"狀態",
		"rank"=>"職等"
	);
	$count_col = count($colData);
	$position_col_data = array();
	foreach($colData as $k=>$v){
		$position_col_data[] = $k;
	}

	//員工編號 map
	$staff = new Staff();
	$staff_no_map = $staff->map('staff_no',true);

	//單位代號 map
	$team = new Department();
	$team_code_map = $team->read(array('id','unit_id','name'),null)->map('unit_id',true);

	//職稱 map
	$title_name_map = $staff->title->map('name',true);
	//職務 map
	$post_name_map = $staff->post->map('name',true);
	//狀態 map
	$status_name_map = $staff->status->map('name',true);
	//md5
	$md5_staff = $staff->inner;

	//記錄錯誤用
	$error_msg = array();
	//更新用
	$update_data = array();
	//新增用
	$insert_data = array();

	foreach($files as $ffi){
		//檔案數

		$res = $api->uploadFile($ffi,array('xlsx', 'xls', 'csv'),2097152,false,RP('/Uploads'));

		if (!empty($res['dest'])) {
			$file_path = $res['dest'];

			$objPHPExcel = PHPExcel_IOFactory::load($file_path);

			$sheet = $objPHPExcel->getSheet(0);

			$highestRow = $sheet->getHighestRow();
			//歷遍
			for ($row = 2; $row <= $highestRow; $row++) {

				$tmp = array();
				for( $col = 0; $col < $count_col; $col++){
					$key = $position_col_data[$col];
					$value = $sheet->getCellByColumnAndRow($col, $row)->getFormattedValue();
					if($key=='unit_id'){
						if( empty($value) ){
							// 首欄空值
							break;
						}
						if( empty($team_code_map[$value]) ){
							$error_msg[] = "第 $row 行 單位代號錯誤 $value 找不到該單位";break;
						}
						$tmp['department_id'] = $team_code_map[$value]['id'];
					}else if($key=='unit_name'){
						//
					}else if($key=='title'){
						if( empty($title_name_map[$value]) ){
							$error_msg[] = "第 $row 行 職稱錯誤 $value 找不到該職稱";break;
						}
						$tmp['title_id'] = $title_name_map[$value]['id'];
					}else if($key=='post'){
						if( empty($post_name_map[$value]) ){
							$error_msg[] = "第 $row 行 職務錯誤 $value 找不到該職務";break;
						}
						$tmp['post_id'] = $post_name_map[$value]['id'];
					}else if($key=='status'){
						if( empty($status_name_map[$value]) ){
							$error_msg[] = "第 $row 行 狀態錯誤 $value 找不到該狀態";break;
						}
						$tmp['status_id'] = $status_name_map[$value]['id'];
					}else if($key=='passwd'){
						$trimValue = trim($value);
						$tmp['passwd'] = empty($trimValue) ? '' : $md5_staff->getMd5PassWord( $trimValue ) ;
					}else if($key=='stay_start_day' || $key=='stay_end_day' || $key=='return_day'){
						//留停相關欄位不上傳
						continue;
					}else if($key=='first_day' || $key=='last_day' || $key=='update_date'){
						$value = strtotime($value);
						if(empty($value) || $value < 1451624400){continue;} //1451624400 = 2016-01-01
						$value = date('Y-m-d',$value);
						$tmp[$key] = $value;
					}else{
						$tmp[$key] = $value;
					}

				}

				if( empty($tmp['staff_no']) ){ continue; }
				$no = $tmp['staff_no'];
				//檢查員編有沒有人
				if( isset($staff_no_map[$no]) ){
					$loc = $staff_no_map[$no];
					//檢查數值是否有被改變
					foreach($tmp as $key=>$val){
						if(!empty($val) && $val!=$loc[$key]){
							$update_data[ $loc['id'] ][ $key ] = $val;
							// break;
						}
					}
				}else{
					// $error_msg[] = "員工編號 $no 找不到該員工";
					// continue;
					$insert_data[] = $tmp;
				}
				// LG($staff_no_map[$no]);

			}//..歷遍

		}else{
			$api->denied($res['msg']);
		}

	}

	// LG($update_data);
	// dd($insert_data);
	// LG($error_msg);
	if(count($error_msg)>0){
		//有錯誤訊息
		$error_str = join(', ',$error_msg);
		$api->denied( $error_str );
	}else{

		//開始更新資料
		$self_id = $api->SC->getId();
		foreach($update_data as $id => $val){
			$staff->updateByAdmin($val,$id,$self_id);
		}

		//加入新員工
		foreach($insert_data as $val){
			// LG($val);
			foreach($val as &$vv){ if(gettype($vv)=='string'){ $vv = "'$vv'"; } }
			$staff->insertStorage($val);
		}
		$insert_count = $staff->releaseInsertStorage();
		$update_count = count($update_data);

		//有變動 就把 月考評表全刪了 在自動生成一次
		// if($update_count+$insert_count>0){

			// $condiYM = array('year'=>$year,'month'=>$month);

			// $report = new MonthlyReport();
			// $report->delete($condiYM);

			// $report_leader = new MonthlyReportLeader();
			// $report_leader->delete($condiYM);

			// $process = new MonthlyProcessing();
			// $process->delete($condiYM);

		// }

		$api->setArray(array(
			'update_count' => $update_count,
			'insert_count' => $insert_count
		));

	}



}else{
	$api->denied();
}

print $api->getJSON();

?>