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
    // $sheet->getColumnDimension( $colMapping[0] )->setWidth(18);
    // $sheet->getColumnDimension( $colMapping[1] )->setWidth(18);
    $sheet->setTitle("$year 年 $month 月 出缺席記錄");
    //合併日期頭
    $sheet->mergeCells("A1:B1");
    $sheet->mergeCells("A2:B2");
    $sheet->mergeCells("A3:B3");
    
    $sheet->setCellValue("A1", "部門");
    $sheet->setCellValue("A2", "姓名");
    $sheet->setCellValue("A3", "日期");
    // LG($result);
    //標題頭
    $col = 2;
    foreach($result as &$val){
      //每個員工
      
      // $sheet->getRowDimension($row)->setRowHeight(22);
      $nowPosition = str_fetchColRow($col,1);
      $sheet->mergeCells($nowPosition.":".str_fetchColRow($col+2,1));
      $sheet->setCellValue($nowPosition, $val['unit_id'].'-'.$val['unit_name']);
      
      $nowPosition = str_fetchColRow($col,2);
      $sheet->mergeCells($nowPosition.":".str_fetchColRow($col+2,2));
      $sheet->setCellValue($nowPosition, $val['name'].' '.$val['name_en']);
      
      $nowPosition = str_fetchColRow($col,3);
      $sheet->setCellValue($nowPosition, '上班');
      $nowPosition = str_fetchColRow($col+1,3);
      $sheet->setCellValue($nowPosition, '下班');
      $nowPosition = str_fetchColRow($col+2,3);
      $sheet->setCellValue($nowPosition, '備註');
      
      $col+=3;
      
    }
    
    //產生日期範圍
    $start = strtotime($DateRangeStart);
    $end = strtotime($DateRangeEnd);
    $weekend_map = array( '周日','周一','周二','周三','周四','周五','周六','周日'  );
    $date_array = array();
    
    $row = 4;
    while( $start <= $end ){
      
      $col = 0;
      
      $week = date("w",$start);
      $date = date("Y-m-d",$start);
      $md = date("m/d",$start);
      
      
      
      $pcr = str_fetchColRow($col,$row);
      $sheet->setCellValue($pcr, $md);
      $col++;
      
      $pcr = str_fetchColRow($col, $row );
      $sheet->setCellValue($pcr, $weekend_map[$week]);
      $col++;
       //塞入資料
      foreach($result as &$val){
        $absence = $val['_absence'];
        if( isset($absence[$date]) ){
          $dataset = $absence[$date];
          //上班
          $pcr = str_fetchColRow($col,$row);
          $sheet->setCellValue($pcr, $dataset['checkin_hours'] );
          //下班
          $pcr = str_fetchColRow($col+1,$row);
          $sheet->setCellValue($pcr, $dataset['checkout_hours'] );
          //備註
          $pcr = str_fetchColRow($col+2,$row);
          $remark = $dataset['remark'];
          if(empty($remark)){
            if($dataset['late']>0){$remark.='遲到 : '.$dataset['late'].' 分,';}
            if($dataset['early']>0){$remark.='早退 : '.$dataset['early'].' 分,';}
            if($dataset['nocard']>0){$remark.='忘卡,';}
          }
          $sheet->setCellValue($pcr, $remark);
        }
        $col+=3;
      }
      
      $row++;
      $start += 86400;
    }
    
    
    
    
    header("Content-Type: application/$file_type;charset=gbk");
    header("Content-Disposition: attachment; filename=".$savename.".$file_ending");
    header("Pragma: no-cache");
    header('Content-Type: text/html; charset=utf-8');
    $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
    $writer->save('php://output');

}

?>