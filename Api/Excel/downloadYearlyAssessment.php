<?php
include __DIR__.'/../ApiCore.php';

$api = new ApiCore($_REQUEST);

use \Model\Business\Staff;
use \Model\Business\Department;

use Model\Business\Multiple\YearlyAssessment;

if($api->SC->isLogin() && $api->checkPost(['year','department_level'])){
  
  include BASE_PATH.'/Model/PHPExcel.php';
  require_once RP('/Model/PHPExcel/IOFactory.php');
  include __DIR__."/common_function.php";   // 快選 excel 儲存格
  
  $year = $api->post('year');
  
  $member = $api->SC->getMember();
  $is_divi_leader = $member['_is_division_leader'] || $member['is_admin'];
    
  $department_level = $api->post('department_level');
  if( !is_numeric($department_level) || (int)$department_level > 5){ $api->denied('Param Wrong.'); }
  
  $with_assignment = empty($api->post('with_assignment')) ? false : true;
  $is_over = empty($api->post('is_over')) ? false : true;
  
  $yfb = new YearlyAssessment( $year );
  $self_id = $api->SC->getId();
  
  
  $result = $yfb->getPerfomanceList( $self_id , $year, $department_level, $with_assignment, $is_over, true );
  // dd($result);
  $leader = $result['assessment']['leader'];
  foreach($leader as $lk=>$lv){
    if($lv['department_id']==1){unset($leader[$lk]);break;} //去除CEO
  }
  $staff = $result['assessment']['staff'];
  $distribution = $result['distribution'];
  $team_map = $yfb->getQuickDepartmentMap();
  // dd($team_map);
  // 初始化 
  // $excel = new PHPExcel();
  $file = __DIR__.'/Template/downloadYearlyAssessment_2.xlsx';
  $excel = PHPExcel_IOFactory::load($file);
  
  
  
  /**
   * 總覽結果頁
   */
  $sheet_1_col_map = [3=>'staff_no',4=>'staff_name',5=>'staff_first_day',6=>'staff_post',7=>'staff_title',8=>'staff_status',9=>'assessment_total_final',10=>'level'];
  function rewritePage_1(&$sht){
    Global $leader, $staff, $year, $sheet_1_col_map;
    $row = 3;  $col = 1;
    
    $sht->setCellValue(p(9,2), $year.'年 考核分數');
    $sht->setCellValue(p(10,2), $year.'年 考核等級');
    // dd($leader);
    $row = writeByData($sht,$leader,$row);
    $sht->mergeCells( str_fetchColRow(1,$row,10,$row) );$row++;
    $row = writeByData($sht,$staff,$row);
    $sht->getDefaultRowDimension()->setRowHeight(18);
    $sht->getDefaultColumnDimension()->setWidth(20);
    $sht->getColumnDimension('G')->setWidth(28);
    $sht->getColumnDimension('E')->setWidth(22);
  }
  //
  function writeByData(&$s,$data,$row){
    Global $sheet_1_col_map, $is_divi_leader;
    foreach( $data as $lv){
      $s->setCellValue(p(1, $row), $lv['division_code'].'_'.$lv['division_name']);
      if($lv['division_code']!=$lv['department_code']){ $s->setCellValue(p(2, $row), $lv['department_code'].'_'.$lv['department_name']); }
      if(!$is_divi_leader){$lv['assessment_total_final']='-';}
      foreach($sheet_1_col_map as $col=>$c_name){
        $s->setCellValue(p($col, $row), $lv[$c_name] );
      }
      $row++;
    }
    return $row;
  }
  
  /**
   *  等級分佈
   */
  $rate_name_map = ['A'=>'優秀','B'=>'良好','C'=>'普通','D'=>'尚需努力','E'=>'不符標準'];
  // if ($year >= 2020) {
  //   $rate_name_map = ['A+'=>'優秀','A'=>'良好','B'=>'普通','C'=>'尚需努力','D'=>'不符標準'];
  // }
  function rewritePage_2(&$sht){
    Global $leader, $staff, $distribution, $rate_name_map, $team_map;
    // dd($distribution);
    //修改標題
    $row = 1; $col = 4;
    foreach($distribution as $val){
      $sht->setCellValue(p($col, $row), $val['name'] );
      $dis = isset($rate_name_map[$val['name']]) ? $rate_name_map[$val['name']] : '其他';
      $sht->setCellValue(p($col, $row+1), $dis );
      $score_title = empty($val['score_least']) ? ($val['score_limit'].'分以下') : ($val['score_limit'].' ~ '.$val['score_least'].'分');
      $sht->setCellValue(p($col, $row+2), $score_title );
      $percent_title = ($val['rate_limit']==$val['rate_least'] ? ($val['rate_limit']) : ($val['rate_least'].' ~ '.$val['rate_limit'])).'%';
      $sht->setCellValue(p($col, $row+3), $percent_title );
      $col++;
    }
    //計算比例
    $all_data = array_merge($leader,$staff);
    // dd( $all_data );
    $team_data_map = $team_map;
    // dd( $team_data_map );
    foreach($all_data as $d){
      if($d['enable']==0){continue;}
      foreach($d['path_lv'] as $lv=>$pv){
        $team_id = $pv[0];
        if( empty($team_data_map[$team_id]['_total'])){
          $team_data_map[$team_id]['_total']=0;
          $team_data_map[$team_id]['_total_as']=0;
        }
        $team_data_map[$team_id]['_total']++;

        if (empty($team_data_map[$team_id]['_levelPeople'])) {
          $team_data_map[$team_id]['_levelPeople'] = [
            'formal' => [],   //正職
            'all' => [],      //+助理
          ];
        }

        if( empty($team_data_map[$team_id]['_levelPeople']['formal'][$d['level']])){
          $team_data_map[$team_id]['_levelPeople']['formal'][$d['level']] = 0;
          $team_data_map[$team_id]['_levelPeople']['all'][$d['level']] = 0; 
        }

        $team_data_map[$team_id]['_levelPeople']['all'][$d['level']]++;

        if($d['rank']>1){
          $team_data_map[$team_id]['_levelPeople']['formal'][$d['level']]++; 
          $team_data_map[$team_id]['_total_as']++;
        }
      }
    }

    function sortByOrder($a, $b) {
      
      return strcmp($a['unit_id'], $b['unit_id']);
    }

    $team_data_array = $team_data_map;

    usort($team_data_array, 'sortByOrder');

    // dd($team_data_array);
    // dd($distribution);
    //處理excel格式要列印的 map
    $row = 6;

    function writePage2LevelPeopleIntoRow($name, $total, $levelPeople, &$row, &$sht, $c_word_1, $c_word_2, $styleRange=null) {
      global $distribution, $rate_name_map;

      $col = 4;

      $sht->setCellValue(p(1, $row), $name );
      $sht->setCellValue(p(2, $row), $total );

      $sht->mergeCells(str_fetchColRow(1, $row, 1, $row+1));
      $sht->mergeCells(str_fetchColRow(2, $row, 2, $row+1));
      
      $sht->setCellValue(p(3, $row), $c_word_1 );
      $sht->setCellValue(p(3, $row+1), $c_word_2 );

      foreach($distribution as $val){
        $level_name = $val['name'];
        if(empty($rate_name_map[$level_name])) { continue; }
        if(isset($levelPeople[$level_name])) {

          $sht->setCellValue(p($col, $row), $levelPeople[$level_name] );

        } else {

          $sht->setCellValue(p($col, $row), 0 );

        }
        
        $rate = number_format(($total * (int)$val['rate_limit']) / 100,2);
        $sht->setCellValue(p($col, $row+1), $rate );
        
        $col++;
      }

      if ($styleRange) {
        // $style = $sht->getStyle($styleRange);
        // $sht->duplicateStyle($style, "A$row:H".($row+1));
        
      }

      $sht->getStyle(p(1, $row))->getAlignment()->setWrapText(true);
      $sht->getStyle(str_fetchColRow(3, $row, 8, $row))->applyFromArray([
        'font' => [
          'color' => array('rgb' => '0000FF'),
        ],
        'borders' => [
          'bottom' => [
            'style' => PHPExcel_Style_Border::BORDER_DOTTED ,
            'color' => array('rgb' => '000000')
          ]
        ]
      ]);

      $sht->getStyle(str_fetchColRow(1, $row, 8, $row+1))->applyFromArray([
        'borders' => [
          'outline' => [
            'style' => PHPExcel_Style_Border::BORDER_THIN
          ],
          'vertical' => [
            'style' => PHPExcel_Style_Border::BORDER_THIN
          ]
        ]
      ]);

      $row+=2;
    }

    //例外設置

    $other_team_names = [
      '6' => '客戶服務部(不含助理)',
    ];

    $need_print_sub = [5];

    //copy style
    // $style = $sht->getStyle('A8:H9');
    // $style_other = $sht->getStyle('A12:H13');
    $style_normal_range = 'A8:H9';

    $word_1 = $sht->getCell(p(3, 6))->getValue();
    $word_2 = $sht->getCell(p(3, 7))->getValue();

    //開始印
    foreach ($team_data_array as $tdmv) {
      $id = $tdmv['id'];
      
      if ($id <= 0) { continue; }
      // if (empty($tdmv['_levelPeople'])) { continue; }
      if (empty($tdmv['_total'])) { continue; }

      if ($tdmv['lv'] <= 2) {
        
        writePage2LevelPeopleIntoRow(
          $tdmv['name'],
          $tdmv['_total'],
          $tdmv['_levelPeople']['all'],
          $row,
          $sht,
          $word_1,
          $word_2,
          ($tdmv['lv'] != 1) ? $style_normal_range : null
        );
        
      }

      if (isset($other_team_names[$id])) {
        writePage2LevelPeopleIntoRow(
          $other_team_names[$id],
          $tdmv['_total_as'],
          $tdmv['_levelPeople']['formal'],
          $row,
          $sht,
          $word_1,
          $word_2,
          $style_normal_range
        );
      }

      if (in_array($tdmv['upper_id'], $need_print_sub)) {
        $upper_id = $tdmv['upper_id'];
        $new_name = $team_data_map[$upper_id]['name']."\n".$tdmv['name'];
        
        writePage2LevelPeopleIntoRow(
          $new_name,
          $tdmv['_total'],
          $tdmv['_levelPeople']['all'],
          $row,
          $sht,
          $word_1,
          $word_2,
          $style_normal_range
        );
      }
      
      
    }

    // $print_map = [
    //   [1,0],      //運維中心
    //   [2,0],      //架構發展事業部
    //   [6,0],      //客戶服務部
    //   [6,1],      //客戶服務部(不含助理)
    //   [8,0],      //系統管理處
    //   [14,0],     //技術支援處
    //   [12,0],     //資料庫管理處
    //   [9,0],      //開發處
    //   [3,0],      //稽核部
    //   [4,0]       //風險管理部
    // ];
    // //開始印
    // foreach($print_map as $v){
    //   $id=$v[0];  $mode=$v[1];
    //   $team = $team_data_map[$id];
    //   if(empty($team['_level'])){$row+=2;continue;}
    //   $level_ = $team['_level'];
    //   $total = $mode==0 ? $team['_total'] : $team['_total_as'];
    //   $sht->setCellValue(p(2, $row), $total );
    //   $col = 4;
    //   foreach($distribution as $val){
    //     $key = $val['name'];
    //     if(empty($rate_name_map[$val['name']])){continue;}
    //     $people = isset($level_[$key]) ? $level_[$key][$mode] : 0;
    //     // $rate = ($people==0) ? 0 : number_format(($people*100)/$total,2); 
    //     $rate = number_format(($total * (int)$val['rate_limit']) / 100,2);
    //     $sht->setCellValue(p($col, $row), $people );
    //     $sht->setCellValue(p($col, $row+1), $rate );
    //     $col++;
    //   }
    //   $row+=2;
    // }
    
    
    // $objPHPExcel->getActiveSheet()->duplicateStyle($objPHPExcel->getActiveSheet()->getStyle('B2'), 'B3:B7');
  }
  
  
  
  
  
  // render
  $sheet = $excel->getSheet(0);
  rewritePage_1( $sheet );
  
  if($is_divi_leader){
    $sheet = $excel->getSheet(1);
    rewritePage_2( $sheet );
  }else{
    $excel->removeSheetByIndex(1);
  }
  
  
  
  ob_end_clean();
    
    //excel 設定
    $savename = $year.' 年度考核結果分析_'.date("His");
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