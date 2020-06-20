<?php
include __DIR__.'/../ApiCore.php';

$api = new ApiCore($_REQUEST);

use \Model\Business\Staff;
use \Model\Business\Department;
use \Model\Business\MonthlyReport;
use \Model\Business\MonthlyReportLeader;
// use \Model\Business\Multiple\MonthlyReport;
// use Model\Business\ConfigCyclical;

if($api->SC->isAdmin()){
  
  include BASE_PATH.'/Model/PHPExcel.php';  
  include __DIR__."/common_function.php";   // 快選 excel 儲存格
  //初始化
  $year = 2017;
  $month_array = [1,2,3];
  $index = 0;
  $staff = new Staff();
  $team = new Department();
  $team_table = $team->table;
  //到職日再4/21以前 還沒離職的人
  // $staff_map = $staff->read(['id','staff_no','name','name_en','first_day','department_id'],['status_id'=>'< 4','first_day'=>"< '2017-04-21'"])->cmap();
  $staff_map = $staff->sql("select a.id,a.staff_no,a.rank,a.name,a.name_en,a.first_day,a.department_id , b.name as department_name, b.unit_id 
  from {table} as a left join $team_table as b on a.department_id = b.id 
  where a.status_id != 4 and a.first_day < '2017-04-21' and rank > 0 and (b.lv > 1 or a.is_leader=0) 
  order by b.unit_id, a.rank desc, a.staff_no")->cmap();
  // dd($staff_map);
  $common_col = ['id','staff_id','year','month','total','releaseFlag','bonus'];
  $common_where = ['year'=>$year, 'month'=>'in('.join(',',$month_array).')'];
  $general = new MonthlyReport();
  $general_data = $general->select($common_col,$common_where);
  $leader = new MonthlyReportLeader();
  $leader_data = $leader->select($common_col,$common_where);
  
  $meta_data_map=[];
  foreach($general_data as $v){
    $meta_data_map[$v['month']][$v['staff_id']]=$v;
  }
  foreach($leader_data as $v){
    $meta_data_map[$v['month']][$v['staff_id']]=$v;
  }
  
  //依照年月執行
  function renderByYM($year,$month){
    Global $staff_map, $team_map, $index, $meta_data_map;
    // $report = new MonthlyReport( array( 'year'=>$year,'month'=>$month ) );
    // $rgt = $report->getTotallyShow( true, false );
    
    
    //預處理資料  已存在資料
    $filter_staff = [];
    $now_time_YM = strtotime("$year-$month-20");
    foreach($staff_map as $id=>$sv){
      $this_time = strtotime($sv['first_day']);
      if(!$this_time || $this_time>$now_time_YM){continue;}      //到職日還不在這個月
      $filter_staff[$id]=$sv;
    }
    
    $this_report = isset($meta_data_map[$month]) ? $meta_data_map[$month] :[];
    
    foreach($filter_staff as $id=>$sv){
      $filter_staff[$id]['_report'] = isset($this_report[$id]) ? $this_report[$id]:[];
    }
    
    createSheetByData($filter_staff,$year,$month, 'Monthly');
    // dd($rgt);
    
  }
  
  
  //建立一個 excel分頁
  function createSheetByData($data, $year, $month, $title){
    global $excel,$index;
    if($index==0){
      $sheet = $excel->getActiveSheet();
    }else{
      $sheet = $excel->createSheet();
    }
    $sheet->setTitle("$year-$month $title");
    //樣式
    $sheet->getDefaultRowDimension()->setRowHeight(22);
    $sheet->getDefaultColumnDimension()->setWidth(18);
    $css_ary = array(
        'borders' => array(
            'outline'=>array(
                'style' => PHPExcel_Style_Border::BORDER_THIN,
                'color' => array('argb' => 'FF000000')
         )));
    
    //ym
    $sheet->mergeCells( str_fetchColRow(1,1,5,1) );
    $sheet->setCellValue( p(1,1), $year.'年'.$month.'月 年績效總分' );
    // $sheet->setCellValue( p(5,1), $year );
    $sheet->setCellValue( p(6,1), $month );
    
    //標題
    $title_map = [1=>'單位代碼',2=>'單位名稱',3=>'員工編號',4=>'人員名稱',5=>'到職日',6=>'月績效總分'];
    $row = 2;
    $sheet->getStyle( str_fetchColRow(1,$row,6,$row) )->applyFromArray($css_ary );
    foreach($title_map as $col=>$name){
      $sheet->setCellValue( p($col,$row), $name );
    }
    $row++;
    
    
    //資料
    
    foreach($data as $id=>$val){
      
      $sheet->setCellValue( p(1,$row), $val['unit_id'] );
      $sheet->setCellValue( p(2,$row), $val['department_name'] );
      $sheet->setCellValue( p(3,$row), $val['staff_no'] );
      $sheet->setCellValue( p(4,$row), $val['name'].' / '.$val['name_en'] );
      $sheet->setCellValue( p(5,$row), $val['first_day'] );
      $score = !empty($val['_report']) ? $val['_report']['total'] : '';
      $sheet->setCellValue( p(6,$row), $score );
      $row++;
    }
    
    $sheet->getStyle( str_fetchColRow(6,2,6,$row-1) )->applyFromArray($css_ary );
    
    
    $index++;
    return $sheet;
  }
  
  
  //開始
  $excel = new PHPExcel();
  foreach($month_array as $month){
    renderByYM($year, $month);
  }
  // dd($index);
  
    
  
  ob_end_clean();
    
    //excel 設定
    $savename = $year.'-123_MothlyScore_'.date("His");
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