<?php
include __DIR__.'/../ApiCore.php';

$api = new ApiCore($_REQUEST);

use \Model\Business\Staff;
use \Model\Business\Department;
use \Model\Business\AttendanceSpecial;
$year = $api->post('year');
if($api->SC->isAdmin() && is_numeric($year)){
  
  include BASE_PATH.'/Model/PHPExcel.php';  
  include __DIR__."/common_function.php";
  
  $ads = new AttendanceSpecial();
  $ads_data = $ads->select(['id','staff_id','type','value'],['type'=>[$ads::TYPE_NOCARD, $ads::TYPE_FORGETCARD],'year'=>$year]);
  $ads_map = [];
  foreach($ads_data as &$av){
    $ads_map[$av['staff_id']][$av['type']] = $av;
    unset($av['staff_id']);
    unset($av['type']);
  }
  // dd($ads_map);
  $staff = new Staff();
  $team = new Department();
  $team_table = $team->table;
  
  $meta_data = $staff->sql("select a.id, a.staff_no, a.name, a.name_en, a.department_id, b.name as department_name, b.unit_id as department_code, a.status_id  
    from {table} as a left join $team_table as b on a.department_id = b.id 
    where a.status_id < 4 and a.rank > 0 
    order by b.unit_id, a.staff_no, a.rank 
  ")->cmap();
  
  // dd($meta_data);
  
  $print_data = [];
  $col_name = ['單位代碼','單位名稱','員工編號','人員名稱','未帶卡','忘刷卡'];
  // $col_key = ['department_code','department_name','staff_no','name/name_en','未帶卡','忘刷卡'];
  $print_data[] = $col_name;
  foreach($meta_data as $staff_id=>$data){
    if($data['status_id']==4 && empty($ads_map[$staff_id])){continue;}    //沒有紀錄 又離職的人
    $tmp = [];
    $atten = isset($ads_map[$staff_id])? $ads_map[$staff_id] : [];
    
    $tmp[] = $data['department_code'];
    $tmp[] = $data['department_name'];
    $tmp[] = $data['staff_no'];
    $tmp[] = $data['name'].' / '.$data['name_en'];
    $tmp[] = isset( $atten[$ads::TYPE_NOCARD] )? $atten[$ads::TYPE_NOCARD]['value'] : 0;
    $tmp[] = isset( $atten[$ads::TYPE_FORGETCARD] )? $atten[$ads::TYPE_FORGETCARD]['value'] : 0;
    
    $print_data[] = $tmp;
  }
  
  
  
  // dd($meta_data);
  
  // 初始化 
  $excel = new PHPExcel();
  
  $sheet = $excel->getActiveSheet();
  //印出 Excel
  $xy = [1,2];
  $xy = renderTable( $sheet, $print_data, $xy);
  // dd($xy);
  
  //樣式
  $sheet->getDefaultRowDimension()->setRowHeight(20);
  $sheet->getDefaultColumnDimension()->setWidth(20);
  $sheet->getColumnDimension('D')->setWidth(32);
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
  $sheet->getStyle( str_fetchColRow($col-1,2,$col,$row) )->applyFromArray( $css_ary );
  $allZoon = str_fetchColRow($col-1,2,$col,$row);
  $sheet->getStyle( $allZoon )->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setWrapText(true);
  $sheet->getStyle( $allZoon )->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
  
  //年設製
  $sheet->setTitle("$year 年 未帶&忘刷卡");
  $sheet->setCellValue(p(1,1), $year);
  
  outputExcel("$year 年特殊出缺勤");

}else{
  $api->denied('You Have Not Promied.');
}



?>