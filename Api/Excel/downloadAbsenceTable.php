<?php
// include __DIR__.'/../ApiCore.php';

$table = isset($_POST['table'])?$_POST['table']:false;

if(!$table){ echo 'no table.';exit; }

$savename = date("YmjHis");
$file_type = "vnd.ms-excel";
$file_ending = "xlsx";
header("Content-Type: application/$file_type;charset=gbk");
header("Content-Disposition: attachment; filename=".$savename.".$file_ending");
header("Pragma: no-cache");
header('Content-Type: text/html; charset=utf-8');

include (__DIR__.'/../../Model/PHPExcel.php');

$newDom = "<html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8'/></head><body>".$table."</body></html>";
// echo $newDom;
libxml_use_internal_errors(true);
$dom = new DOMDocument("1.0", "utf-8");
$dom->loadHTML($newDom);
libxml_use_internal_errors(false);

$root = $dom->documentElement;

$trs = $root->getElementsByTagName("tr");

$excel = new PHPExcel();
$sheet = $excel->getActiveSheet();

$colMapping = str_split("ABCDEFGHIJKLMNOPQRSTUVWXYZ");
$colLength = count($colMapping);
$row = 1;

foreach($trs as $loc){
  $tds = $loc->childNodes;
  $col = 0;
  foreach($tds as $loc2){
    $nowPosition = str_fetchColRow($col,$row);
    
    $colspan = (int) $loc2->getAttribute("colspan");
    // var_dump($colspan);
    if($colspan>1){
      $toPosition = str_fetchColRow($col+$colspan-1,$row);
      // var_dump($nowPosition.":".$toPosition);
      $sheet->mergeCells($nowPosition.":".$toPosition);
      $col+=$colspan;
    }else{
      $col++;
    }
    $sheet->setCellValue($nowPosition, $loc2->nodeValue);
  }
  $row++;
}

function str_fetchColRow($col,$row){
  global $colMapping;
  global $colLength;
  $col1 = (int) $col % $colLength;
  $col2 = floor($col / $colLength)-1;
  $colStr = ($col2 >= 0 ? $colMapping[$col2]:"").$colMapping[$col1];
  return $colStr.$row;
}

$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');

$writer->save('php://output');

?>