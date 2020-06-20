<?php
include __DIR__.'/../ApiCore.php';

$api = new ApiCore($_REQUEST);

use \Model\Business\Multiple\YearlyFeedback;
// use \Model\Business\YearPerformanceConfigCyclical;
use \Model\Business\YearPerformanceFeedbackMultipleChoice;

if($api->SC->isAdmin() && $api->checkPost(['year'])){

  include BASE_PATH.'/Model/PHPExcel.php';
  include __DIR__."/common_function.php";

  $year = $api->post('year');

  $ya = new YearlyFeedback();
  $ya_list = $ya->getYearlyFeedbackList($year);
  if(count($ya_list)==0){ $api->denied('No Report.'); }
  $ch_ids = [];
  foreach($ya_list[0]['multiple_choice_json'] as $mid => $v){
    $ch_ids[]=$mid;
  }

  $ch = new YearPerformanceFeedbackMultipleChoice();
  // $ch_obj = $ch->read(['id','title'],['id'=>$ch_ids],' order by sort');
  $ch_data = $ch->read(['id','title'],['id'=>$ch_ids],' order by sort')->data;
  // $ch_map = $ch->cmap();

  // dd($ya_list);

  $q_title = '部屬回饋問卷';

  $print_data = [];
  $col_name = ['組織單位','評議人員','受評對象','總分'];
  foreach($ch_data as $cv){
    $col_name[] = $cv['title'];
  }

  $print_data[] = $col_name;
  foreach($ya_list as $data){
    $tmp = [];
    $tmp[] = $data['department']['unit_id'].$data['department']['name'];
    $tmp[] = $data['staff']['name_en'].' / '.$data['staff']['name'];
    $tmp[] = $data['target_staff']['name_en'].' / '.$data['target_staff']['name'];
    $tmp[] = $data['multiple_score'];
    foreach($ch_data as $cv){
      $tmp[] = $data['multiple_choice_json'][$cv['id']];
    }
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
  // $sheet->getColumnDimension('B')->setWidth(80);
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

  outputExcel("$year 年 $q_title");


}else{
  $api->denied('You Have Not Promied.');
}



?>