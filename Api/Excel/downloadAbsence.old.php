<?php
include __DIR__.'/../ApiCore.php';
include BASE_PATH.'/Model/dbBusiness/ConfigCyclical.php';
include BASE_PATH.'/Model/dbBusiness/Multiple/DepartmentAttendance.php';

$api = new ApiCore($_REQUEST);

if($api->checkPost(array("year","month"))){
  
    //年/月
    $year =  $api->post('year');
    $month = $api->post('month');
    $team_id = $api->post('team_id');
    $staff_id = $api->post('staff_id');
    
    
    //取得該月的設定值
    $webconfig = new Model\Business\ConfigCyclical();
    $config = $webconfig->getConfigWithDate($year, $month);
    $DateRangeStart = $config['RangeStart'];
    $DateRangeEnd = $config['RangeEnd'];
    
    $attendance = new Model\Business\Multiple\DepartmentAttendance();
    
    $result = array();
    
    if( $team_id ){
      $team_id = preg_replace('/[\[\]\)\(]+/','',$team_id);
      $result = $attendance->getWithDate($DateRangeStart, $DateRangeEnd, $team_id);
    }else if( $staff_id ){
      
      $staff_id = preg_replace('/[\s\r\n!@#%$\[\]]+/i','',$staff_id);
      
      $result = $attendance->getWithDate($DateRangeStart, $DateRangeEnd, null, $staff_id );
    }else{
      
      $result = $attendance->getWithDate($DateRangeStart, $DateRangeEnd);
    }
    
    $result = $attendance->staffList();
    // LG($result);
    // $api->setArray($result);
    // print $api->getJSON();
    // exit;
    
    
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
    
    $savename = "$year-$month-".date("YmjHis");
    $file_type = "vnd.ms-excel";
    $file_ending = "xlsx";
    
    include (__DIR__.'/../../Model/PHPExcel.php');
    $excel = new PHPExcel();
    $sheet = $excel->getActiveSheet();
    
    
    
    
    ob_end_clean();
    //設定寬度
    $sheet->getColumnDimension( $colMapping[0] )->setWidth(22);
    $sheet->getColumnDimension( $colMapping[1] )->setWidth(18);
    
    //合併日期頭
    $sheet->mergeCells("A1:B2");
    $sheet->setCellValue("A1", "$year 年 $month 月 出缺席記錄");
    $sheet->setCellValue("A3", "單位");
    $sheet->setCellValue("B3", "姓名");
    
    //標題頭
    $start = strtotime($DateRangeStart);
    $end = strtotime($DateRangeEnd);
    $weekend_map = array( '周日','周一','周二','周三','周四','周五','周六','周日'  );
    $date_array = array();
    
    //產生日期範圍
    $col = 2;
    while( $start <= $end ){
      
      
      $week = date("w",$start);
      $date = date("Y-m-d",$start);
      $md = date("m/d",$start);
      
      $date_array[$date] = true;
      
      $pcr = str_fetchColRow($col,1);
      $pcr2m = str_fetchColRow($col+2,1);
      $sheet->mergeCells($pcr.":".$pcr2m);
      $sheet->setCellValue($pcr, $md);
      
      $pcr = str_fetchColRow($col, 2 );
      $pcr2m = str_fetchColRow($col+2,2);
      $sheet->mergeCells($pcr.":".$pcr2m);
      $sheet->setCellValue($pcr, $weekend_map[$week]);
      
      $pcr = str_fetchColRow($col, 3 );
      $sheet->setCellValue($pcr, '上班');
      $pcr = str_fetchColRow($col+1, 3 );
      $sheet->setCellValue($pcr, '下班');
      $pcr = str_fetchColRow($col+2, 3 );
      $sheet->setCellValue($pcr, '備註');
      
      
      $start += 86400;
      $col+=3;
    }
    // LG($start);
    
    //塞入資料
    $row = 4;
    foreach($result as &$val){
      //每個員工
      
      // $sheet->getRowDimension($row)->setRowHeight(22);
      $nowPosition = str_fetchColRow(0,$row);
      $sheet->setCellValue($nowPosition, $val['unit_id'].'-'.$val['unit_name']);
      $nowPosition = str_fetchColRow(1,$row);
      $sheet->setCellValue($nowPosition, $val['name'].'-'.$val['name_en']);
      // $name = iconv('UTF-8','UTF-8', $loc[$k]);
      
      //每個出勤
      $col = 2;
      foreach( $date_array as $k=>&$v){
        $absence = $val['_absence'];
        if( isset($absence[$k]) ){
          $dataset = $absence[$k];
          //上班
          $nowPosition = str_fetchColRow($col,$row);
          $sheet->setCellValue($nowPosition, $dataset['checkin_hours']);
          $col++;
          //下班
          $nowPosition = str_fetchColRow($col,$row);
          $sheet->setCellValue($nowPosition, $dataset['checkout_hours']);
          $col++;
          //備註
          $nowPosition = str_fetchColRow($col,$row);
          $sheet->setCellValue($nowPosition, $dataset['remark']);
          $col++;
        }else{
          $col+=3;
        }
        
      }
      
      $row++;
      
    }
    
    
    
    
    
    header("Content-Type: application/$file_type;charset=gbk");
    header("Content-Disposition: attachment; filename=".$savename.".$file_ending");
    header("Pragma: no-cache");
    header('Content-Type: text/html; charset=utf-8');
    $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
    $writer->save('php://output');

}

?>