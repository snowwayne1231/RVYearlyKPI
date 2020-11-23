<?php
include __DIR__."/../ApiCore.php";
include BASE_PATH.'/Model/PHPExcel.php';
use \Model\Business\Multiple\YearlyAssessment;
use \Model\Business\RecordYearPerformanceQuestions;
use \Model\Business\RecordYearPerformanceReport;
use \Model\Business\RecordYearPerformanceDivisions;
use \Model\Business\YearPerformanceFeedbackQuestions;
use \Model\Business\Staff;
use \Model\Business\Multiple\StaffHistory;
$api = new ApiCore($_REQUEST);

//防制機制      //Session 沒用 改版再改用 file cache
$Excel_Request = isset($_COOKIE['Excel_Request'])?$_COOKIE['Excel_Request']:1;
if($Excel_Request==1){$api->denied('No Promised.');}
// $Excel_Processing = $api->SC->get('Excel_Processing');

// if(!empty($Excel_Processing)){
  // if((int)$Excel_Request - (int)$Excel_Processing < 30000){
    // $api->denied('Request Too Fast.');
  // }
// }
// $api->SC->set('Excel_Processing',$Excel_Request);

//excel 設定
$savename = '員工績效考核表_'.date("YmjHis");
$file_type = "vnd.ms-excel";
$file_ending = "xlsx";

if( $api->checkPost(array('year'))  && $api->SC->isLogin()){

  //post input
  $year = $api->post('year');
  $division_id = $api->post('division_id');
  $department_id = $api->post('department_id');
  $staff_id = $api->post('staff_id');
  $dds = [ is_numeric($division_id)?(int)$division_id:0, is_numeric($department_id)?(int)$department_id:0, is_numeric($staff_id)?(int)$staff_id:0 ];

  $self_id = $api->SC->getId();
  $myself = $api->SC->getMember();
  $is_leader = $myself['is_leader'];
  $is_admin = $myself['is_admin'];
  $my_lv = $is_admin ? 0 : ($is_leader==1? $myself['_department_lv'] : $myself['_department_lv']+1);

  $is_super_user = $api->SC->isSuperUser();
  $is_division_leader = $myself['_is_division_leader'];


  //年考績
  $ya = new YearlyAssessment($year);
  $ryqq = new RecordYearPerformanceQuestions();
  $ypfq = new YearPerformanceFeedbackQuestions();
  $sh = new StaffHistory();

  //預處理資料  基本資料
  $all_reports = $ya->getAssessmentWithDDS( $dds, $self_id );
  if ($dds[0]==0) {

    $all_reports = array_filter($all_reports, function($report) {
      return !!$report['_authority']['view'];
    });
  }
  // dd($all_reports);
  $leader = [];
  $general = [];
  foreach($all_reports as &$arsv){
    $arsv['staff_stay'] = '';
    if($resSH = $sh->getStayWithStaff($arsv['staff_id'])){
      $tmp_stay = array();
      foreach($resSH as $shv){
        $tmp_stay[] = $shv['start_day'].' ~ '.$shv['end_day'];
      }
      $arsv['staff_stay'] = implode('，', $tmp_stay);
    }
    if($arsv['staff_is_leader']==1){
      $leader[]=$arsv;
    }else{
      $general[]=$arsv;
    }
  }

  $topic = $ya->getFullTopic($year);
  //$topic = ForeachTopic($topic);
  foreach($topic as $k => $v){
    $tmp = [];
    foreach($v as $kk => $vv){
      $tmp[$vv['type_name']][$vv['id']] = $vv;
    }
    $topic[$k] = $tmp;
  }


  $rate = $ya->getDistributionRate();

  $topic_leader = $topic['leader'];
  $topic_general = $topic['normal'];

  $questionTitle_map = $ypfq->read(['id','title'],['enable'=>1])->map();
  $question_list = $ryqq->select(['target_staff_id','content','question_id','from_type','create_date'],['year'=>$year]);
  sortByValue($question_list, ['target_staff_id', 'from_type', 'question_id']);
  //$question_map = $ryqq->read(['target_staff_id','content','question_id','create_date'],['year'=>$year])->amap('target_staff_id');
  $question_map = array();
  foreach($question_list as $val){
    $tsid = $val['target_staff_id'];
    $ft = $val['from_type'];
    if(empty($question_map[$tsid])) $question_map[$tsid] = array();
    if(empty($question_map[$tsid][$ft])) $question_map[$tsid][$ft] = array();

    foreach($questionTitle_map as $qid => $qtitle){
      if($val['question_id'] == $qid){
        if(empty($question_map[$tsid][$ft][$qid])){
          $question_map[$tsid][$ft][$qid] = array();
        }
        $question_map[$tsid][$ft][$qid][] = $val;
      }
    }
  }
  //dd($question_map);

  $staff = new Staff();
  $staff_map = $staff->read(['id','name','name_en','staff_no'],[])->cmap();


  // 初始化
  $excel = new PHPExcel();
  $column_score_lv_map = ['under'=>'部屬回饋','self'=>'自評','4'=>'組長評核','3'=>'處主管評核','2'=>'部門主管核定','1'=>'決策者核定','0'=>'HK核定'];
  $column_rate_map = ['A'=>'優秀','B'=>'良好','C'=>'普通','D'=>'尚需努力','E'=>'不符標準'];
  // $column_rate_map = ['A+'=>'優秀','A'=>'良好','B'=>'普通','C'=>'尚需努力','D'=>'不符標準'];
  $column_team_caller_map = ['0'=>'系統','1'=>'運維主管','2'=>'部門主管','3'=>'處主管','4'=>'組長','5'=>'員工'];
  include __DIR__."/common_function.php";


  //do
  try{
    $t1 = microtime(true);
    $i = 0;
    $wast_array = [];
    // $stop = 50;
    foreach($leader as $leader_value){
      // if($i>$stop-3){break;}
      $wast_array[] = createSheetByData($leader_value, $topic_leader, $rate, $i);
      $i++;
    }

    foreach($general as $general_value){
      // if($i>$stop){break;}
      $wast_array[] = createSheetByData($general_value, $topic_general, $rate, $i);
      $i++;
    }

    // $t2 = microtime(true);
    // $wast_time = $t2-$t1;
    // $byte = memory_get_usage();
    // $kb = $byte/1024;
    // $mb = $kb/1024;
    // dd('TIME:'.$wast_time.',  ', false);
    // dd('MB: '.$mb.',  ', false);
    // dd($wast_array);
  }catch(\Exception $e){
    dd($e->getMessage());
  }

  setcookie('Excel_Response',0,-1,'/');
  // $api->SC->set('Excel_Processing',0);

  ob_end_clean();

}else{
  dd('Param Wrong.');
}

if($dds[0]>0){
  $prefix = '';
}else if($dds[1]>0){
  $prefix = '';
}else if($dds[2]>0){
  $prefix = $staff_map[ $dds[2] ]['staff_no'];
}



/*
function ForeachTopic($t){
  foreach($t as $k => $v){
    $tmp = [];
    foreach($v as $kk => $vv){
      $tmp[$vv['type_name']][$vv['id']] = $vv;
    }
    $t[$k] = $tmp;
  }return $t;
}
*/


//建立一個 excel分頁
function createSheetByData($report, $topic, $rate, $index=1){
  global $excel, $column_score_lv_map, $column_rate_map, $question_map, $questionTitle_map, $column_team_caller_map,$staff_map,$is_super_user, $my_lv, $is_division_leader, $is_admin;

  $t1 = microtime(true);
  if($index==0){
    $sheet = $excel->getActiveSheet();
  }else{
    $sheet = $excel->createSheet();
  }

  //分頁名稱
  $sheet->setTitle($report['staff_no']);

  // 把基本標題文字設置上
  $row = setTitleBySheet($sheet,$report,1);

  // 間隔開
  $row++;
  $row_assessment_start = $row;
  //如果是執行長 加上 HK評定
  $isCEO = $report['staff_is_leader']==1 && $report['division_id']==1;

  if($report['processing_lv']>0 || $is_super_user || true){   //還再考評中的個人 report 或是 超級使用者
    //格式 - 考評分數
    $sheet->mergeCells( str_fetchColRow(1,$row,4,$row+1) );
    $sheet->mergeCells( str_fetchColRow(5,$row,5,$row+1) );
    $sheet->setCellValue( p(1,$row), '考核項目' );
    $sheet->setCellValue( p(5,$row), '考核比重' );
    $aj_col = [6,6];
    $aj_score_map = [];
    $has_under_merge = false;


    if($isCEO){
      $report['assessment_json'][0] = $report['assessment_json']['self'];
      $report['assessment_json'][0]['percent'] = 40;
      $report['assessment_json'][0]['total'] = '';
      foreach($report['assessment_json'][0]['score'] as &$rajs){
        $rajs = '';
      }
      $report['level'] = '';
    }

    // - 考評分數 - 標題
    foreach($column_score_lv_map as $aj_key => $aj_title){
      if(empty($report['assessment_json'][$aj_key])){continue;}
      $aj_val = $report['assessment_json'][$aj_key];
      $pr = $aj_val['percent'].'%';
      $aj_val['double']=(int)$aj_val['percent']>=40 ? true : false; //如果佔比超過40%的用兩格
      $aj_val['overlv']=(is_numeric($aj_key) && (int)$aj_key<$my_lv)? true : false; //超過自己可以看的等級
      if($aj_val['overlv']){ $aj_val['total']=''; }

      $sheet->setCellValue( p($aj_col[1],$row), $pr );
      $sheet->setCellValue( p($aj_col[1],$row+1), $aj_title );

      if($aj_key=='under'){$has_under_merge=true;}    //是否有部屬回饋

      $aj_score_map[$aj_col[1]] = $aj_val;
      if($aj_val['double']){
        $sheet->mergeCells( str_fetchColRow($aj_col[1],$row,$aj_col[1]+1,$row) );
        $sheet->mergeCells( str_fetchColRow($aj_col[1],$row+1,$aj_col[1]+1,$row+1) );
        $aj_col[1]+=2;
      }else{
        $aj_col[1]++;
      }
    }

    // - 考評分數 - 分數內容
    $row=$row+2;
    $row_cache = $row;
    // dd($aj_score_map);
    foreach($topic as $t_type_name => $t_type_val){ //題目分類
      $t_col = 1;
      $t_row_count = count($t_type_val);
      $sheet->mergeCells( str_fetchColRow($t_col, $row, $t_col+1, $row+$t_row_count-1) );
      $sheet->setCellValue( p($t_col,$row), $t_type_name );

      foreach($t_type_val as $t_id => $t_val){ //題目單題
        $sheet->mergeCells( str_fetchColRow(3, $row, 4, $row) );
        $sheet->setCellValue( p(3,$row), $t_val['name'] );
        $sheet->setCellValue( p(5,$row), $t_val['score'].' 分' );
        foreach($aj_score_map as $ajm_col => $ajm_value){ //每題每階層分數

          if($has_under_merge && $ajm_col==$aj_col[0]){
            // $score = $aj_score; //如果是部屬回饋留空
          }else{
            if($ajm_value['double']){ $sheet->mergeCells( str_fetchColRow($ajm_col,$row,$ajm_col+1,$row) ); }
            if($ajm_value['overlv']){ $score='?'; }else{
              $score = isset($ajm_value['score'][$t_id])?$ajm_value['score'][$t_id]:'錯誤';
              if($score<0){$score='無';}
            }

            $sheet->setCellValue( p($ajm_col,$row), $score );
          }
        }
        $row++;
      }
    }
    //合併部屬
    if($has_under_merge){
      $under_next = isset( $aj_score_map[$aj_col[0]+1] ) ? $aj_col[0] : $aj_col[0]+1;
      $sheet->mergeCells( str_fetchColRow($aj_col[0], $row_cache, $under_next, $row-1) );
    }

    //總分
    $no_total = $isCEO || $report['processing_lv']>0; //執行長或還沒結束的單子不給總分
    // dd([$is_division_leader, $is_admin]);
    if ($is_division_leader || $is_admin) {
      $score_total = $no_total ? '':(int)$report['assessment_total'];
      $score_fix = $no_total ? '' : (int)$report['assessment_total_division_change'] + (int)$report['assessment_total_ceo_change']; //加減分
      $score_final = $no_total ? '' : $score_total + $score_fix; //核定總分
    } else {
      $score_total = '?';
      $score_fix = '?';
      $score_final = '?'; 
    }

    $final_col = $aj_col[1]-1;
    $sheet->mergeCells( str_fetchColRow(1, $row, 4, $row) );
    $sheet->setCellValue( p(1, $row), '總分' );
    $sheet->setCellValue( p(5, $row), $score_total );
    // dd($aj_score_map);
    foreach($aj_score_map as $ajm_col => $ajm_vale){
      $total = isset($ajm_vale['total'])?$ajm_vale['total']:'Error';
      if($ajm_vale['double']){ $sheet->mergeCells( str_fetchColRow($ajm_col,$row,$ajm_col+1,$row) ); }
      $sheet->setCellValue( p($ajm_col, $row), $total );
    }
    $row++;
    $sheet->mergeCells( str_fetchColRow(1, $row, 4, $row) );
    $sheet->setCellValue( p(1, $row), '加減分數' );
    $sheet->setCellValue( p(5, $row), $score_fix );
    $sheet->setCellValue( p(6, $row), '說明' );
    $sheet->mergeCells( str_fetchColRow(6, $row, 10, $row) );
    $row++;

  }else{
    //核定總分
    $score_final = '--';
    $final_col = 10;
  }

  $t2 = microtime(true);
  //核定總分
  $sheet->mergeCells( str_fetchColRow(1, $row, 4, $row) );
  $sheet->setCellValue( p(1, $row), '核定總分' );
  $sheet->setCellValue( p(5, $row), $score_final );
  $row++;
  //考核等級
  $sheet->mergeCells( str_fetchColRow(1, $row, 4, $row) );
  $sheet->setCellValue( p(1, $row), '考績等級 ' );
  $sheet->setCellValue( p(5, $row), $report['level'] );
  $col_rate = 6;
  foreach($rate as $name=>$value){
    $sheet->setCellValue( p($col_rate, $row), $name.' '.$column_rate_map[$name] );
    $sheet->setCellValue( p($col_rate, $row-1), $value['score_limit'].'~'.$value['score_least'].' 分' );
    $col_rate++;
  }
  //設置基礎樣式格式
  setStyleBySheet($sheet,$row);
  setStyleAssessmentBySheet($sheet, $row_assessment_start, $row, $final_col);

  $row+=2;
  $comment_row = $row;

  // 評論
  $row = addCommentBySheet($sheet, $row, $report['year'].'年在公司的主要貢獻：', $report['self_contribution']);

  $row = addCommentBySheet($sheet, $row, '未來需要加強及改進之處：', $report['self_improve']);

  foreach($report['upper_comment'] as $uc_lv => $uc_ary){
    $uc_name = $column_team_caller_map[$uc_lv];
    // $row = addCommentBySheet($sheet, $row, $uc_name.'評語：', $uc_ary['content']);
    if (is_array($uc_ary['staff_id'])) {
      foreach ($uc_ary['staff_id'] as $_uc_idx => $_uc_staff_id) {
        $staff_name = $staff_map[$_uc_staff_id]['name'];
        $uc_title = "$uc_name ($staff_name)";
        $row = addCommentBySheet($sheet, $row, $uc_title, $uc_ary['content'][$_uc_idx]);
      }
    } else {
      $row = addCommentBySheet($sheet, $row, $uc_name.'評語：', $uc_ary['content']);
    }
    
  }

  if( isset($question_map[$report['staff_id']]) ){
    //$row = addCommentBySheet($sheet, $row, '部屬/其他回饋：', $question_map[$report['staff_id']]);
    foreach($question_map[$report['staff_id']] as $qType => $qVal){
      switch($qType){
        case 4: $qTitle = '其它回饋：'; break;
        case 3: $qTitle = '上司回饋：'; break;
        case 2: $qTitle = '其它部門回饋：'; break;
        case 1:
        default:
          $qTitle = '部屬回饋：';
          break;
      }

      foreach($questionTitle_map as $qid => $qmtitle){
        if(isset($qVal[$qid])){
          $row = addCommentBySheet($sheet, $row, $qTitle.$qmtitle['title'], $qVal[$qid]);
          $row++;
        }
      }
    }

  }

  // 簽核流程
  $row++;
  $sheet->mergeCells( str_fetchColRow(1, $row, 10, $row) );
  $sheet->setCellValue( p(1, $row), '簽核流程' );

  // $sign_run = ['s'=>'本人', 4=>'組長', 3=>'處主管', 2=>'部門主管', 1=>'運維主管', 'c'=>'架構事業發展部人力資源處', 'f'=>'決策者核定'];
  $sign_run = ['s'=>'本人', 4=>'組長', 3=>'處主管', 2=>'部門主管', 1=>'運維主管', 'f'=>'決策者核定'];
  $col = 1;

  if($isCEO){
    $row = addCommentBySheet($sheet, $row, 'HK 評語：', "\r\n \r\n \r\n \r\n \r\n");
    $row++;
    $staff = $staff_map[$report['staff_id']];
    //$sign = $report['sign_json']['s'];
    //$time = date('Y/m/d H:i',(int)$sign[1]);
    $sheet->mergeCells( str_fetchColRow($col, $row, $col+4, $row) );
    $sheet->mergeCells( str_fetchColRow($col, $row+1, $col+4, $row+2) );
    $sheet->setCellValue( p($col,$row), 'HK核准' );
    $col+=5;
    $sheet->mergeCells( str_fetchColRow($col, $row, $col+4, $row) );
    $sheet->mergeCells( str_fetchColRow($col, $row+1, $col+4, $row+2) );
    $sheet->setCellValue( p($col,$row), '運維主管本人' );
    $col+=5;
  }else{
    $row++;
    foreach($sign_run as $sign_key=>$sign_name){
      if(empty($report['sign_json'][$sign_key])){continue;}
      $sign = $report['sign_json'][$sign_key];
      $staff_name = '';
      foreach ($sign as $_sidx => $_st) {
        $_is_even = $_sidx % 2 == 0;
        if ($_is_even) {
          $staff = $staff_map[$_st];
          $staff_name = $staff['name'].' / '.$staff['name_en'];
        } else {
          $time = date('Y/m/d H:i', (int)$_st);
          $sheet->mergeCells( str_fetchColRow($col, $row, $col+1, $row) );
          $sheet->mergeCells( str_fetchColRow($col, $row+1, $col+1, $row+1) );
          $sheet->mergeCells( str_fetchColRow($col, $row+2, $col+1, $row+2) );
          $sheet->setCellValue( p($col,$row), $sign_name );
          $sheet->setCellValue( p($col,$row+1), $staff_name );
          $sheet->setCellValue( p($col,$row+2), $time );
          $col+=2;
        }
      }
    }
  }
  $t3 = microtime(true);


  $sign_zoon = str_fetchColRow(1, $row, $col-1, $row+2);
  $sheet->getStyle( $sign_zoon )->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setWrapText(true);
  $sheet->getStyle( $sign_zoon )->applyFromArray( array(
      'borders' => array(
          'allborders' => array(
              'style' => PHPExcel_Style_Border::BORDER_THIN,
              'color' => array('argb' => 'FF000000'),
          )
      )
  ) );
  $zoon = str_fetchColRow(1,$comment_row,10,$row-1);
  $sheet->getStyle( $zoon )->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP)->setWrapText(true);


  $t5 = microtime(true);
  // dd([$t2-$t1,$t3-$t2,$t5-$t3]);
  return ($t5-$t1);
}

//設置基礎樣式
function setStyleBySheet(&$s,$b){
  //樣式
  $s->getDefaultRowDimension()->setRowHeight(20);
  $s->getDefaultColumnDimension()->setWidth(16);
  //置中
  // dd(str_fetchColRow(1,1,10,40));
  $allZoon = str_fetchColRow(1,1,10,$b);
  $s->getStyle( $allZoon )->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setWrapText(true);
  $s->getStyle( $allZoon )->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);

  //標頭資訊樣式
  $styleTitleArray = array(
      'borders' => array(
          'allborders' => array(
              'style' => PHPExcel_Style_Border::BORDER_THIN,
              'color' => array('argb' => 'FF000000'),
          ),
          'outline'=>array(
              'style' => PHPExcel_Style_Border::BORDER_THICK,
              'color' => array('argb' => 'FF000000')
          )
      )
  );
  // $s->getStyle( $allZoon )->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN );
  $s->getStyle( str_fetchColRow(1,3,10,8) )->applyFromArray($styleTitleArray );

  // dd($s->getStyle( $allZoon )->getBorders());
}

//設置評分樣式
function setStyleAssessmentBySheet(&$s,$start,$end,$col){
  $styleArray = array(
      'borders' => array(
          'allborders' => array(
              'style' => PHPExcel_Style_Border::BORDER_THIN,
              'color' => array('argb' => 'FF000000'),
          ),
          'outline'=>array(
              'style' => PHPExcel_Style_Border::BORDER_THICK,
              'color' => array('argb' => 'FF000000')
          )
      )
  );

  $s->getStyle( str_fetchColRow(1,$start,10,$end) )->applyFromArray($styleArray );
  $s->getStyle( str_fetchColRow(1,$end-1,5,$end) )->getFill()->applyFromArray(
    array('type' => PHPExcel_Style_Fill::FILL_SOLID,'startcolor' => array('rgb' =>'FFFF00'))
  );

}

//設置標題
function setTitleBySheet(&$s,&$rp,$r){
  //格式
  $row = $r;
  $s->mergeCells( str_fetchColRow(1,$row,10,$row) ); $row++;
  $s->mergeCells( str_fetchColRow(1,$row,10,$row) ); $row++;
  $s->mergeCells( str_fetchColRow(1,$row,2,$row) );
  $s->mergeCells( str_fetchColRow(3,$row,5,3) );
  $s->mergeCells( str_fetchColRow(6,$row,7,$row) ); $row++;
  for($i=0 ; $i<=1 ; $i++){
    $s->mergeCells( str_fetchColRow(1,$row,2,$row) );
    $s->mergeCells( str_fetchColRow(3,$row,5,$row) );
    $s->mergeCells( str_fetchColRow(6,$row,7,$row) );
    $s->mergeCells( str_fetchColRow(8,$row,10,$row) );
    $row++;
  }

  //留停欄位
  if($rp['staff_stay'] != ''){
    $s->mergeCells( str_fetchColRow(1,$row,2,$row) );
    $s->mergeCells( str_fetchColRow(3,$row,10,$row) ); $row++;
    $s->setCellValue( p(1,6) , '留停 ');
    $s->setCellValue( p(3,6) , $rp['staff_stay']);
  }

  //標題
  $s->setCellValue( p(1,1) , '睿訊有限公司');
  $s->setCellValue( p(1,2) , $rp['year'].' 年員工績效考核表');
  $s->setCellValue( p(1,3) , '員工姓名');
  $s->setCellValue( p(6,3) , '員工編號');
  $s->setCellValue( p(9,3) , '到職日');
  $s->setCellValue( p(1,4) , '部門');
  $s->setCellValue( p(6,4) , '處/組單位');
  $s->setCellValue( p(1,5) , '職稱/職務 ');
  $s->setCellValue( p(6,5) , '職位');

  //標題 - 個資
  $s->setCellValue( p(3,3) , $rp['staff_name'].' / '.$rp['staff_name_en']);
  $s->setCellValue( p(8,3) , $rp['staff_no']);
  $s->setCellValue( p(10,3) , $rp['staff_first_day']);
  $s->setCellValue( p(3,4) , $rp['division_code'].' '.$rp['division_name']);
  $s->setCellValue( p(8,4) , ($rp['division_code']==$rp['department_code'])? '':($rp['department_code'].' '.$rp['department_name']));
  $s->setCellValue( p(3,5) , $rp['staff_title']);
  $s->setCellValue( p(8,5) , $rp['staff_post']);

  //標題 - 參考資料
  $s->mergeCells( str_fetchColRow(2,$row,3,$row) );
  $s->setCellValue( p(1,$row) , ((int)$rp['year']-1).'年考績');
  $s->setCellValue( p(2,$row) , '績效平均分數');
  $s->setCellValue( p(4,$row) , '遲到(次)');
  $s->setCellValue( p(5,$row) , '忘刷卡(次)');
  $s->setCellValue( p(6,$row) , '沒帶卡(次)');
  $s->setCellValue( p(7,$row) , '事假(時)');
  $s->setCellValue( p(8,$row) , '全薪病假(時)');
  // $s->setCellValue( p(9,$row) , '生理假(天)');
  $s->setCellValue( p(9,$row) , '半薪病假(時)');
  $s->setCellValue( p(10,$row) , '曠職(時)');
  $row++;
  //
  $s->mergeCells( str_fetchColRow(2,$row,3,$row) );
  $s->setCellValue( p(1,$row) , $rp['before_level']);
  $s->setCellValue( p(2,$row) , $rp['monthly_average']);
  $s->setCellValue( p(4,$row) , $rp['attendance_json']['late']);
  $s->setCellValue( p(5,$row) , $rp['attendance_json']['forgetcard']);
  $s->setCellValue( p(6,$row) , $rp['attendance_json']['nocard']);
  $s->setCellValue( p(7,$row) , $rp['attendance_json']['leave']);
  $s->setCellValue( p(8,$row) , $rp['attendance_json']['paysick']);
  // $s->setCellValue( p(8,$row) , $rp['attendance_json']['physiology']);
  $s->setCellValue( p(9,$row) , $rp['attendance_json']['sick']);
  $s->setCellValue( p(10,$row) , $rp['attendance_json']['absent']);
  $row++;

  return $row;
}

//產生一個評論區塊
function addCommentBySheet(&$s, $r, $title, $content){
  if(empty($content)){return $r;}
  $s->mergeCells( str_fetchColRow(1, $r, 10, $r) );
  $s->setCellValue( p(1, $r), $title );
  $start_r = $r;
  $r++;
  if(is_array($content)){
    $str = '';
    $now_r = $r; $i = 1;
    foreach($content as $cv){
      if (isset($cv['content'])) {
        $str .= ("$i . ".$cv['content']." 。\r\n");
      } else {
        $str .= ("$i . ".$cv." 。\r\n");
      }
      $r++; $i++;
    }
    $s->mergeCells( str_fetchColRow(1, $now_r, 10, $r) );
    $s->setCellValue( p(1, $now_r), $str );

  }else{
    $line = preg_match_all('/[\r\n]{1,2}/',$content);
    $line = max($line,2);
    $s->mergeCells( str_fetchColRow(1, $r, 10, $r+$line) );
    $s->setCellValue( p(1, $r), $content );
    $r+=$line+1;
  }

  $styleArray = array(
      'borders' => array(
          'outline'=>array(
              'style' => PHPExcel_Style_Border::BORDER_THICK,
              'color' => array('argb' => 'FF000000')
          )
      )
  );
  // $zoon = str_fetchColRow(1,$start_r,10,$r);
  // $s->getStyle( $zoon )->applyFromArray($styleArray);    //太拖重
  // $s->getStyle( $zoon )->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
  // $s->getStyle( $zoon )->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP)->setWrapText(true);

  // $r+=2;
  return $r;
}



// http header
header("Content-Type: application/$file_type;charset=gbk");
header("Content-Disposition: attachment; filename=".$prefix.$savename.".$file_ending");
header("Pragma: no-cache");
header('Content-Type: text/html; charset=utf-8');

$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');

$writer->save('php://output');
?>