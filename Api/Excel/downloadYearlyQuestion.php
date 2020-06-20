<?php
include __DIR__.'/../ApiCore.php';

$api = new ApiCore($_REQUEST);

use \Model\Business\RecordYearPerformanceQuestions;
use \Model\Business\YearPerformanceFeedbackQuestions;

if($api->SC->isAdmin() && $api->checkPost(['year'])){
  
  include BASE_PATH.'/Model/PHPExcel.php';  
  include __DIR__."/common_function.php";   // 快選 excel 儲存格
  
  $year = $api->post('year');
  $questions = new RecordYearPerformanceQuestions();
  $questions_template = new YearPerformanceFeedbackQuestions();
  $q_company = $questions_template->select(['description'],['mode'=>'company']);
  $q_map = $questions->select(['highlight','content','create_date'],['year'=>$year,'target_staff_id'=>0],'order by highlight desc,create_date desc');
  $q_title = [];
  foreach($q_company as $data){ $q_title[]=$data['description']; }
  $q_title = count($q_title)==0 ? '員工意見' : join(';',$q_title);
  
  $print_data = [];
  $col_name = ['是否關注','評論內容','評論時間'];
  
  $print_data[] = $col_name;
  foreach($q_map as $data){
    $tmp = [];
    $tmp[] = $data['highlight']==1 ? '*' : '';
    $tmp[] = $data['content'];
    $tmp[] = $data['create_date'];
    $print_data[] = $tmp;
  }
  
  
  // 初始化 
  $excel = new PHPExcel();
  
  $sheet = $excel->getActiveSheet();
  //印出 Excel
  $xy = [1,2];
  $xy = renderTable( $sheet, $print_data, $xy);
  // dd($xy);
  
  //樣式
  $sheet->getDefaultRowDimension()->setRowHeight(18);
  $sheet->getDefaultColumnDimension()->setWidth(24);
  $sheet->getColumnDimension('B')->setWidth(80);
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
  $allZoon = str_fetchColRow(1,1,1,$row);
  $sheet->getStyle( $allZoon )->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setWrapText(true);
  $sheet->getStyle( $allZoon )->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
  
  //年設製
  $sheet->setTitle("$year 年");
  $sheet->setCellValue(p(1,1), "$year 年 $q_title");
  $sheet->mergeCells( str_fetchColRow(1,1,$col,1) );
  
  outputExcel("$year 年員工意見評論");
  

}else{
  $api->denied('You Have Not Promied.');
}



?>