<?php
  // 快選 excel 儲存格
  $col_mapping = str_split("ABCDEFGHIJKLMNOPQRSTUVWXYZ"); $col_mapping_length = count($col_mapping);
  function str_fetchColRow($col,$row,$col2=0,$row2=0){
    global $col_mapping,$col_mapping_length;
    $col--;
    $c1 = (int) $col % $col_mapping_length;
    $c2 = floor($col / $col_mapping_length)-1;
    $cs = ($c2 >= 0 ? $col_mapping[$c2]:"").$col_mapping[$c1];
    $str = $cs.$row;
    if($col2){
      $col2--;
      $c3 = (int) $col2 % $col_mapping_length;
      $c4 = floor($col2 / $col_mapping_length)-1;
      $cs_ = ($c4 >= 0 ? $col_mapping[$c4]:"").$col_mapping[$c3];
      $str .= (':'.$cs_.$row2);
    }
    return $str;
  }
  //快選單一位置
  function p($a,$b){
    return str_fetchColRow($a,$b);
  }
  
  function outputExcel($name=''){
    //excel 設定
    Global $excel;
    ob_end_clean();
    $savename = $name.'_'.date("His");
    $file_type = "vnd.ms-excel";
    $file_ending = "xlsx";

    setcookie('Excel_Response',0,-1,'/');
    
    header("Content-Type: application/$file_type;charset=gbk");
    header("Content-Disposition: attachment; filename=".$savename.".$file_ending");
    header("Pragma: no-cache");
    header('Content-Type: text/html; charset=utf-8');
    $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
    $writer->save('php://output');
    exit;
  }
  
  //
  function renderTable(&$s,$data,$xy=[1,1]){
    $row = &$xy[1];  $col = &$xy[0];  $origin_col = $col;  $max_col = $col;
    foreach($data as $r => $rowData){
      $col = $origin_col;
      foreach($rowData as $c => $colData){
        $s->setCellValue(p($col,$row), $colData);
        
        $col++;
      }
      $row++;  $max_col = max($max_col,$col);
    }
    $col = $max_col-1;
    $row = $row-1;
    return $xy;
  }
  
  function getDataByTable(&$s, $zoon_ary, $col_map=[], $key_name=null){
    $col = $zoon_ary[0]; $row = $zoon_ary[1];
    $max_col = $zoon_ary[2]; $max_row = $zoon_ary[3];
    $res = [];  $has_key = isset($key_name);
    for($c=$col,$cm=0; $c <= $max_col; $c++,$cm++){ if(!isset($col_map[$cm])){ $col_map[$cm]=$cm; } }
    for($r = $row; $r <= $max_row ; $r++){
      $tmp = [];
      for($c=$col,$cm=0; $c <= $max_col; $c++,$cm++){
        $key = $col_map[$cm];
        $val = $s->getCell(p($c,$r))->getValue();
        $tmp[$key]=$val;
      }
      if($has_key){
        $res[$tmp[$key_name]]=$tmp;
      }else{
        $res[]=$tmp;
      }
    }
    return $res;
  }
  
  // $s->getStyle( $allZoon )->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setWrapText(true);
  // $s->getStyle( $allZoon )->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)->setWrapText(true);
  
?>