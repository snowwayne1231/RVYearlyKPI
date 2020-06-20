<?php
include __DIR__.'/../ApiCore.php';

$api = new ApiCore($_REQUEST);

use \Model\Business\Multiple\YearlyQuickly;

if($api->isAdmin() && $api->checkPost(['year'])){
  
  include BASE_PATH.'/Model/PHPExcel.php';  
  include __DIR__."/common_function.php";   // 快選 excel 儲存格
  
  $year = $api->post('year');
  $quick = new YearlyQuickly($year);
  $staff_map = $quick->getStaffMap();
  unset($staff_map[0]);
  $team_map = $quick->getDepartmentMap();
  // dd($staff_map);
  $col_name = ['單位','員工編號','名稱','參加部屬回饋問卷','參加年績效考核'];
  $print_data[] = $col_name;
  foreach($staff_map as $staff){
    $team = $team_map[ $staff['department_id'] ];
    
    $tmp = [];
    $tmp[] = $team['unit_id'].$team['name'];
    $tmp[] = $staff['staff_no'];
    $tmp[] = $staff['name_en'].' / '.$staff['name'];
    $tmp[] = $staff['_can_feedback']==1?'*':'';
    $tmp[] = $staff['_can_assessment']==1?'*':'';
    $print_data[] = $tmp;
  }
  
  // dd($print_data);
  // 初始化 
  $excel = new PHPExcel();
  
  $sheet = $excel->getActiveSheet();
  //印出 Excel
  $xy = [1,2];
  $xy = renderTable( $sheet, $print_data, $xy);
  // dd($xy);
  
  //樣式
  $sheet->getDefaultRowDimension()->setRowHeight(20);
  $sheet->getDefaultColumnDimension()->setWidth(24);
  $sheet->getColumnDimension('A')->setWidth(30);
  $sheet->getRowDimension('1')->setRowHeight(30);
  // $sheet->getColumnDimension('A')->setWidth(20);
  $css_ary = array(
      'borders' => array(
          'allborders'=>array(
              'style' => PHPExcel_Style_Border::BORDER_THIN,
              'color' => array('argb' => 'FF000000')
          )
      )
  );
  
  $col = $xy[0];
  $row = $xy[1];
  $sheet->getStyle( str_fetchColRow(1,2,$col,2) )->applyFromArray( $css_ary );
  // $sheet->getStyle( str_fetchColRow($col-1,2,$col,$row) )->applyFromArray( $css_ary );
  $allZoon = str_fetchColRow(1,1,5,$row);
  $sheet->getStyle( $allZoon )->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setWrapText(true);
  $sheet->getStyle( $allZoon )->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
  
  //年設製
  $sheet->setTitle("$year 年");
  $sheet->setCellValue(p(1,1), "$year 年度績效考核 人員清單");
  $sheet->mergeCells( str_fetchColRow(1,1,$col,1) );
  
  outputExcel("$year 年度績效考核人員清單");
  
}else{
  $api->denied('You Have Not Promied.');
}



?>