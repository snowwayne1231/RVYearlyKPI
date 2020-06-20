<?php
include __DIR__.'/../ApiCore.php';

$api = new ApiCore($_REQUEST);

use \Model\Business\Staff;
use \Model\Business\Department;
use \Model\Business\YearPerformanceReport;

if($api->SC->isAdmin()){
  
  include BASE_PATH.'/Model/PHPExcel.php';  
  include __DIR__."/common_function.php";   // 快選 excel 儲存格
  $year = 2016;
  $ypr = new YearPerformanceReport();
  $ypr_map = $ypr->read(['id','staff_id', 'level'],['year'=>$year])->cmap('staff_id');
  
  $staff = new Staff();
  
  $team = new Department();
  $team_table = $team->table;
  
  $meta_data = $staff->sql("select a.id, a.staff_no, a.name, a.name_en, a.department_id, b.name as department_name, b.unit_id as department_code 
    from {table} as a left join $team_table as b on a.department_id = b.id 
    where a.status_id < 4 and a.rank > 0 order by b.unit_id, a.staff_no, a.rank 
  ")->map();
  
  foreach($meta_data as &$md_v){
    $md_v['level'] = empty($ypr_map[$md_v['id']]) ? '':$ypr_map[$md_v['id']]['level'];
  }
  
  
  // dd($meta_data);
  
  // 初始化 
  $excel = new PHPExcel();
  $col_ary = ['department_code','department_name','staff_no','name','name_en','level'];
  $col_map = ['staff_no'=>'員工編號','name'=>'姓名','name_en'=>'英文名','department_code'=>'部門代號','department_name'=>'部門名稱','level'=>'2016年評等'];
  
  $sheet = $excel->getActiveSheet();
    
  
  
  
  //樣式
  $sheet->getDefaultRowDimension()->setRowHeight(22);
  $sheet->getDefaultColumnDimension()->setWidth(16);
  $css_ary = array(
      'borders' => array(
          'outline'=>array(
              'style' => PHPExcel_Style_Border::BORDER_THIN,
              'color' => array('argb' => 'FF000000')
          )
      )
  );
  $sheet->getStyle( str_fetchColRow(1,1,count($col_ary),1) )->applyFromArray($css_ary );
  
  
  $sheet->setTitle("$year 年 年考績");
    
  $row = 1;  $col = 1;
  foreach($col_ary as $ca_v){
    $c_title = $col_map[$ca_v];
    $sheet->setCellValue( p($col,$row), $c_title );
    $col++;
  }
  
  $row++;
    // dd($meta_data);
    foreach($meta_data as $md_k=>$md_vv){
      $col = 1;
      foreach($col_ary as $ca_v){
        $content = $md_vv[$ca_v];
        $sheet->setCellValue( p($col,$row), $content );
        $col++;
      }
      $row++;
    }
  
  //評等樣式
  $levelZoon = str_fetchColRow($col-1,1,$col-1,$row-1);
  $sheet->getStyle( $levelZoon )->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setWrapText(true);
  $sheet->getStyle( $levelZoon )->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
  $css_ary = array(
      'borders' => array(
          'outline'=>array(
              'style' => PHPExcel_Style_Border::BORDER_THIN,
              'color' => array('argb' => 'FF000000')
          )
      )
  );
  $sheet->getStyle( $levelZoon )->applyFromArray($css_ary );
  
  
  ob_end_clean();
    
    //excel 設定
    $savename = $year.'_YearRateLevel_'.date("His");
    $file_type = "vnd.ms-excel";
    $file_ending = "xlsx";

    setcookie('Excel_Response',0,-1,'/');
    
    header("Content-Type: application/$file_type;charset=gbk");
    header("Content-Disposition: attachment; filename=".$savename.".$file_ending");
    header("Pragma: no-cache");
    header('Content-Type: text/html; charset=utf-8');
    $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
    $writer->save('php://output');

}else{
  $api->denied('You Have Not Promied.');
}



?>