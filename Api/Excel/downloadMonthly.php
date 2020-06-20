<?php
include __DIR__."/../ApiCore.php";

$api = new ApiCore($_REQUEST);

if(!$api->checkPost(array('year','month'))){
	$api->denied();exit();
}

$year = $_REQUEST['year'];
$month = $_REQUEST['month'];

include_once BASE_PATH.'/Model/dbBusiness/Multiple/DepartmentStaffCyclical.php';
include_once BASE_PATH.'/Model/dbBusiness/Multiple/MonthlyReport.php';
include_once BASE_PATH.'/Model/PHPExcel.php';
//excel 設定
$savename = date("YmjHis");
$file_type = "vnd.ms-excel";
$file_ending = "xlsx";


use Model\Business\Multiple\MonthlyReport;
use Model\Business\Multiple\DepartmentStaffCyclical;
use Model\Business\ConfigCyclical;

$report = new MonthlyReport( array( 'year'=>$year,'month'=>$month ) );
$dc = new DepartmentStaffCyclical($year, $month);;

$config = new ConfigCyclical();
$con_data = $config->getConfigWithDate($year,$month);

$filter = !!$api->post("release");
$department_id = $_REQUEST['department_id'];

if( $api->SC->isAdmin() || $api->SC->isCEO()){
	if(empty($department_id)){
		$team_id = false;
	}else{
		$team_id = $department_id;
	}
}else if($api->SC->isLeader()){
	if(empty($department_id)){
		$team_id = $dc->getListIDWithManager($api->SC->getId());
	}else {
		$team_id = $department_id;
	}
}else{
	$team_id = $api->SC->getDepartmentId();
}
//獲得已核准月考評
$forcs_staff = $api->SC->getMember();

if(is_array($team_id)){
	//有跨部門的主管
	$rgt = array('general'=>array(),'leader'=>array());
	foreach($team_id as $t_id){
		$tmp = $report->getTotallyShow($filter, $t_id, $forcs_staff);
		$rgt['general'] = array_merge($rgt['general'], $tmp['general']);
		$rgt['leader'] = array_merge($rgt['leader'], $tmp['leader']);
	}
}else{
	$rgt = $report->getTotallyShow( $filter , $team_id, $forcs_staff );
}

//預處理資料
$leader = $rgt['leader'];
foreach($leader as $k=>&$lv){
	$leader[$k]['unit_new'] = $lv['unit_id'].'_'.$lv['unit_name'];
	$lv['_comments'] = join_comments($lv['_comments']);
	$lv['name'] = $lv['name'].' '.$lv['name_en'];
}

$general = $rgt['general'];
foreach($general as $k=>&$gv){
	$general[$k]['unit_new'] = $gv['unit_id'].'_'.$gv['unit_name'];
	$gv['_comments'] = join_comments($gv['_comments']);
	$gv['name'] = $gv['name'].' '.$gv['name_en'];
}
// LG($general);

function join_comments(&$loc){
	$tmp = '';
	if( isset($loc) && is_array($loc)){
		// LG($loc);
		foreach($loc as &$l){
			if( sizeof($loc) == 1){
				$tmp.= $l['_create_staff_account']." : ".$l['content'];
			}else{
				$tmp.= $l['_create_staff_account']." : ".$l['content']." 。\r\n";
			}

		}
	}
	return $tmp;
}
// stamp_log("DB_query");
// LG($rgt);
// stamp();

//兩個 sheet
$excel = new PHPExcel();
$sheet = $excel->getActiveSheet();
$sheet->setTitle('Staff');
$sheet_2 = $excel->createSheet();
$sheet_2->setTitle('Leader');



$colMapping= str_split("ABCDEFGHIJKLMNOPQRSTUVWXYZ");
$colLength = count($colMapping);

function str_fetchColRow($col,$row){
	global $colMapping;
	global $colLength;
	$col1 = (int) $col % $colLength;
	$col2 = floor($col / $colLength)-1;
	$colStr = ($col2 >= 0 ? $colMapping[$col2]:"").$colMapping[$col1];
	return $colStr.$row;
}



ob_end_clean();

//員工欄位
$colData = array(
	"unit_new"=>"單位",
	"staff_no"=>"員工編號",
	"post"=>"職務",
	"name"=>"員工姓名",
	"quality"=>"工作品質",
	"completeness"=>"工作績效",
	"responsibility"=>"責任感",
	"cooperation"=>"配合度",
	"attendance"=>"時間觀念",
	"addedValue"=>"特殊貢獻",
	"mistake"=>"重大缺失",
	"_total_score"=>"總分",
	"first_day"=>"到職日",
	"last_day"=>"離職日",
	"_work_day"=>"在職天數",
	"bonus"=>"獎金發放",
	"_comments"=>"備註"
);

//主管欄位
$colDataLeader = array(
	"unit_new"=>"單位",
	"staff_no"=>"員工編號",
	"post"=>"職務",
	"name"=>"員工姓名",
	"target"=>"目標達成率",
	"quality"=>"工作品質",
	"method"=>"工作方法",
	"error"=>"出錯率",
	"backtrack"=>"進度追查/回報",
	"planning"=>"企劃能力",
	"execute"=>"執行力",
	"decision"=>"判斷力",
	"resilience"=>"應變能力",
	"attendance"=>"出缺勤率",
	"attendance_members"=>"組員出缺勤率",
	"addedValue"=>"特殊貢獻",
	"mistake"=>"重大缺失",
	"_total_score"=>"總分",
	"first_day"=>"到職日",
	"last_day"=>"離職日",
	"_work_day"=>"在職天數",
	"bonus"=>"獎金發放",
	"_comments"=>"備註"
);

$row = 4;
$sheet->getRowDimension($row)->setRowHeight(32);
$sheet_2->getRowDimension($row)->setRowHeight(32);

$sharedStyle1 = new PHPExcel_Style();
$sharedStyle1->applyFromArray(  array('borders' => array(
	'left' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
	'top' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
	'right' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
	'bottom' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM)
)));


//標頭欄位
$sheet->setSharedStyle($sharedStyle1, 'A4:Q4' );
$sheet_2->setSharedStyle($sharedStyle1, 'A4:W4' );
$col = 0;

foreach($colData as $k => $cv){
	// var_dump($general);
	$sheet->getColumnDimension( $colMapping[$col] )->setWidth(23);
	$pos = str_fetchColRow($col,$row);
	$name = iconv('UTF-8','UTF-8', $cv);
	$sheet->setCellValue($pos, $name);
	$col++;

}
$col = 0;
foreach($colDataLeader as $k => $cv){
	$sheet_2->getColumnDimension( $colMapping[$col] )->setWidth(23);
	$pos = str_fetchColRow($col,$row);
	$name = iconv('UTF-8','UTF-8', $cv);
	$sheet_2->setCellValue($pos, $name);
	$col++;
}

// LG($general);


//置中
$general_count = count($general)+10;
$leader_count = count($leader)+10;
$sheet->getStyle('A1:Q'.$general_count)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setWrapText(true);
$sheet->getStyle('A1:Q'.$general_count)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
$sheet_2->getStyle('A1:W'.$leader_count)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setWrapText(true);
$sheet_2->getStyle('A1:W'.$leader_count)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
//表頭
$sheet->setCellValue('B1', '年月');
$sheet->setCellValue('C1', $year.'/'.$month);
$sheet->setCellValue('B2', '區間');
$sheet->setCellValue('C2', $con_data['RangeStart'].'/'.$con_data['RangeEnd']);
$sheet_2->setCellValue('B1', '年月');
$sheet_2->setCellValue('C1', $year.'/'.$month);
$sheet_2->setCellValue('B2', '區間');
$sheet_2->setCellValue('C2', $con_data['RangeStart'].'/'.$con_data['RangeEnd']);

// staff 資料塞入
$row = 5;
foreach($general as &$gloc){

	$col = 0;
	$sheet->getRowDimension($row)->setRowHeight(26);
	foreach($colData as $k=>$v){
		$nowPosition = str_fetchColRow($col,$row);
		if( isset($gloc[$k]) ){
			if( $k=='bonus' ){ $name= ($gloc[$k]==1)?'是':'否'; }else{
				$name = iconv('UTF-8','UTF-8', $gloc[$k]);
			}

		}else{
			$name = '';
		}

		$sheet->setCellValue($nowPosition, $name);
		$col++;
	}
	$row++;
}

// leader 資料塞入
$row = 5;
foreach($leader as &$lloc){
	$col = 0;
	$sheet_2->getRowDimension($row)->setRowHeight(26);
	foreach($colDataLeader as $k=>$v){
		$nowPosition = str_fetchColRow($col,$row);
		if( isset($lloc[$k]) ){
			if( $k=='bonus' ){ $name= ($lloc[$k]==1)?'是':'否'; }else{
				$name = iconv('UTF-8','UTF-8', $lloc[$k]);
			}
		}else{
			$name = '';
		}

		$sheet_2->setCellValue($nowPosition, $name);
		$col++;
	}
	$row++;
}

// http header
header("Content-Type: application/$file_type;charset=gbk");
header("Content-Disposition: attachment; filename=".$savename.".$file_ending");
header("Pragma: no-cache");
header('Content-Type: text/html; charset=utf-8');

$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');

$writer->save('php://output');

?>