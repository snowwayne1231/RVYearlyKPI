<?php

include __DIR__."/../ApiCore.php";
include BASE_PATH.'/Model/dbBusiness/Multiple/StaffDepartment.php';

$api = new ApiCore($_POST);

if( $api->SC->isLogin() && $api->SC->isAdmin() ){

	include BASE_PATH.'/Model/dbBusiness/Multiple/MonthlyReport.php';
	include BASE_PATH.'/Model/PHPExcel.php';
	//excel 設定
	$savename = date("YmjHis");
	$file_type = "vnd.ms-excel";
	$file_ending = "xlsx";

	$staff = new \Model\Business\Multiple\StaffDepartment();
	$sh = new \Model\Business\Multiple\StaffHistory();
	$data = $staff->collect(true);
	foreach($data as &$val){
		$val['stay_start_day'] = '';
		$val['stay_end_day'] = '';
		$val['return_day'] = '';
		if($resSH = $sh->getStayWithStaff($val['id'])){
			$val['stay_start_day'] = $resSH[0]['start_day'];
			$val['stay_end_day'] = $resSH[0]['end_day'];
			$val['return_day'] = $resSH[0]['return_day'];
		}
	}

	// $result = $staff->select($select_column , $condition);


	// LG($result);
	$api->setArray($data);


	//
	$excel = new PHPExcel();
	$sheet = $excel->getActiveSheet();
	$sheet->setTitle('Staff');

	$colMapping = str_split("ABCDEFGHIJKLMNOPQRSTUVWXYZ");
	$colLength = count($colMapping);

	function str_fetchColRow($col,$row){
		global $colMapping;
		global $colLength;
		$col1 = (int) $col % $colLength;
		$col2 = floor($col / $colLength)-1;
		$colStr = ($col2 >= 0 ? $colMapping[$col2]:"").$colMapping[$col1];
		return $colStr.$row;
	}
	//

	ob_end_clean();
	//員工欄位
	$colData = array(
		"unit_id"=>"單位代號",
		"unit_name"=>"單位名稱",
		"staff_no"=>"員工編號",
		"account"=>"員工帳號",
		"name"=>"員工姓名",
		"name_en"=>"員工英文名",
		"title"=>"職務類別",
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

	$row = 1;
	$sheet->getRowDimension($row)->setRowHeight(32);

	$col = 0;
	foreach($colData as $k => $v){
		$colWidth = preg_match('/title|post|email/',$k) ? 24 : 16;
		$sheet->getColumnDimension( $colMapping[$col] )->setWidth($colWidth);
		$pos = str_fetchColRow($col,$row);
		$name = iconv('UTF-8','UTF-8', $v);
		$sheet->setCellValue($pos, $name);
		$col++;
	}
	// staff 資料塞入
	$row = 2;
	foreach($data as &$loc){

		$col = 0;
		$sheet->getRowDimension($row)->setRowHeight(24);
		foreach($colData as $k=>$v){
			$nowPosition = str_fetchColRow($col,$row);
			if( isset($loc[$k]) && $k!='passwd'){
				$name = iconv('UTF-8','UTF-8', $loc[$k]);
			}else{
				$name = '';
			}

			$sheet->setCellValue($nowPosition, $name);
			$col++;
		}
		$row++;
	}

	// http header
	header("Content-Type: application/$file_type;charset=gbk");
	header("Content-Disposition: attachment; filename=rv_staff_detail".$savename.".$file_ending");
	header("Pragma: no-cache");
	header('Content-Type: text/html; charset=utf-8');

	$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');

	$writer->save('php://output');exit;

}else{
	// var_dump($_POST);
}

print $api->getJSON();

?>