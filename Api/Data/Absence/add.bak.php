
<?php
ini_set('max_execution_time', 300);

include __DIR__.'/../../ApiCore.php';
// include_once RP('/Model/ToolKit/upload.func.php');
include_once RP('/Model/dbBusiness/Attendance.php');
include_once RP('/Model/dbBusiness/Staff.php');

$api = new ApiCore();

$attendance = new Model\Business\Attendance();
$staff = new Model\Business\Staff();


$files = $api->getFiles();
if(count($files)==0){
  $api->denied('沒有檔案.');
}
// var_dump($files);



$staff_map = $staff->map('staff_no',true);

// LG($staff_map);
$time_start = microtime(true);
$loop_time = 0;
$insert_time = 0;
$update_time = 0;
require_once RP('/Model/PHPExcel.php');
require_once RP('/Model/PHPExcel/IOFactory.php');

$all = array();
$staff_id_array = array();
$date_array = array();
$wrongMsgArray = array();

// 依上傳檔案數執行
foreach ($files as $fileInfo) {
    // 呼叫封裝好的 function
    $res = $api->uploadFile($fileInfo,array('xlsx', 'xls', 'csv'),2097152,false,RP('/Uploads'));
	
	
    // 上傳成功，將實際儲存檔名存入 array（以便存入資料庫）
    if (!empty($res['dest'])) {
        	//$uploadFiles[] = $res['dest'];
        	$file = $res['dest'];

	        $objPHPExcel = PHPExcel_IOFactory::load($file);
	        $sheetNo = $objPHPExcel->getSheetCount(); //檔案sheet頁數

	       	// 預設變數
	        $staffNo; 					// 員工編號
	        $sheetYear; 				// sheet的年份
            $staffId;
	        $attendanceArray = array(); // 存放整個檔案每筆資料，預設為陣列
	        $totalRowNum = 0;			// 整個檔案寫入資料總列數
	        $takeOffNum = 10; 			// 每頁非資料列數
	        $allColumn = 16;			// 每頁表格共16欄
        
	        $thisheetHRow = 0;
	        $totalRowNum = 0;

			// 每個檔案更新成功訊息容器
			$successNum = 0;

			// 每個檔案更新錯誤訊息容器
			$wrongNum =  0;
			$wrongMsg = '';
			


			// 撈每頁內容
			for($sheetRow=0; $sheetRow <= $sheetNo-1; $sheetRow++){

				// 讀取資料
			   	$sheet = $objPHPExcel->getSheet($sheetRow); // 讀取工作表(編號從 0 開始)
		        $highestRow = $sheet->getHighestRow(); 		// 取得該頁總列數
		        $thisheetHRow += $highestRow-$takeOffNum; 	// 該頁筆數
                $page = $sheetRow+1;
                
                
		        $totalRowNum += $thisheetHRow;

		        //取得ID
		        $id = $sheet->getCell('B5')->getValue();
		        $id_str = explode(" ",$id); // 拆解
		        $staffNo = $id_str[0]; // 取的該sheet的員工編碼
           
		        // 員工編碼資料比對的
            
            $num = isset($staff_map[ $staffNo ]) ? 1 : 0;
            
		        // 預設
		        $strArray1 = array();					// 存放每行資料
                
		        if($num == 0){
              $wrongMsgArray[]="第 $page 頁: 沒有員工。";
		        	continue;					// 沒有批配員工ID，則寫入"NULL"
		        } else if($num == 1){
		        	$staffId = $staff_map[ $staffNo ]['id'];// 存放員工資料庫排序ID
            } 

		        //取得年份
		        $year = $sheet->getCell('A2')->getValue();
		        $year_str = explode("年",$year);
		        $sheetYear = $year_str[0];
		        $sheetYearFloor = strlen(floor($sheetYear));

		        if($sheetYearFloor !== 4 ){
                // 若該頁年份格是錯誤或員工編號有誤，則不進行後續處理
                $wrongMsgArray[]="第 $page 頁: 年份格式錯誤，請檢查檔案。";
                continue;
            }
                    
            $attendanceArrayListNum = 0;
            $strArray1[8] = 0.00;
            $strArray1[11] = 0.00;
            $strArray1[9] = null;
            $strArray1[10] = null;
            $strArray1[12] = null;
            $strArray1[13] = null;
            $strArray1[14] = null; // 假別(加班別)
            $strArray1[15] = null;
                    
            // 拆解/重組資料陣列
            for ($row = 9; $row <= $highestRow-2; $row++) {
                for ($column = 0; $column <= $allColumn; $column++) {

                    // 取得該欄位內容
                    $val = $sheet->getCellByColumnAndRow($column, $row)->getValue();
                    
                    // 合併儲存格
                    $mergeCells = "mergeCells";
                            
                    if($column === 0){
                        // 日期欄位
                       if($val == null){
                            $vocation_hours = $sheet->getCellByColumnAndRow(12, $row)->getValue();
                            $overtime_hours = $sheet->getCellByColumnAndRow(10, $row)->getValue();
                            if($vocation_hours > 0 || $overtime_hours > 0){
                                $date = $mergeCells;
                            } else {
                                $date = "NULL";break;
                            }
                        } else {
                            // 日期欄位文字處理 YYYY-MM-DD
                            $valstr = explode("/", $val);
                            
                            if( empty($valstr[1]) ){ $wrongMsgArray[]="第 $page 頁 : 日期資料錯誤。";break; }
                            
                            $valstr2 = strtotime($sheetYear.'-'.(int)$valstr[0].'-'.(int)$valstr[1]);
                            $date = date("Y-m-d", $valstr2);
                        }
                        $strArray1[0] = $staffId;		// 每行資料存入前必須先放員工資料庫ID($staffId)
                        $strArray1[1] = $date;
                    } else if($column === 2 || $column === 3){
                        // 處理欄位為時間格式的 2:上班 3:下班
                        $timeType = "H:i";
                        $nullValue = "00:00";
                        if($val !== null){
                            $timestr = strtotime($val);
                            $timestr2 = date($timeType, $timestr);
                        } else if($val === null){
                            // 時間欄位為空值
                            $val = $nullValue;
                            $timestr = strtotime($val);
                            $timestr2 = date($timeType, $timestr);
                        } else {
                            $timestr2 = "NULL";
                        }
                        $strArray1[$column] = $timestr2;
                    } else if($column === 4 || $column === 10 || $column === 12){
                        // 處理欄位格式為時數 4:工時 10:加班時數 11:請假時數
                        if($val == null){
                            // 次數欄位為空值
                            $val = 0.00;
                            (float)$val;
                        } else if($val !== null) {
                            (float)$val;
                        } else {
                            $val = "NULL";
                        }
                        $hours = $val;

                        if($strArray1[1] == $mergeCells){
                            $lastArray = end($attendanceArray);
                            //echo $row."/";
                            $prevAnnual = $lastArray[8];
                            $prevOver = $lastArray[11];
                            if($column === 10){
                                $attendanceArray[$attendanceArrayListNum-1][11] = $prevOver+$hours; // 加班
                            }
                            if($column === 12){
                               $attendanceArray[$attendanceArrayListNum-1][8] = $prevAnnual+$hours;	// 請假
                            }
                        }
                            if($column === 10){
                                $strArray1[11] = $hours; // 加班
                            }
                            if($column === 12){
                                $strArray1[8] = $hours;	// 請假
                            }

                        if($column === 4){
                            $strArray1[$column] = $hours;
                        }

                    } else if($column === 5 || $column === 6 || $column === 8){
                        // 處理欄位格式為次數 5:遲到 6:早退 8:忘卡
                        if($val === null){
                            // 次數欄位為空值
                            $val = 0;
                        } else if($val !== null) {

                        } else {
                            $val = "NULL";
                        }
                        $time = (int)$val;
                        if($column === 8){
                            $strArray1[$column-1] =$time;
                        } else {
                            $strArray1[$column] =$time;
                        }
                    } else if($column === 9){
                        // 9: 假別(加班別)
                        if($strArray1[1] == $mergeCells){
                            $lastArray = end($attendanceArray);
                            $prevAberrant = $lastArray[15];
                            $attendanceArray[$attendanceArrayListNum-1][15] = $prevAberrant.$val;
                        }else{
                            $strArray1[14] = $val;

                        }

                        if($val !== null){
                            $strArray1[15] = $val;
                        }else{
                            $strArray1[15] = null;
                        }

                    } else if($column === 13 || $column === 15){
                        // 13:開始時間 15:結束時間
                        $vocation_hours = $strArray1[8];
                        $overtime_hours = $strArray1[11];
                        $SBtime = $val;
                        if($strArray1[1] == $mergeCells){
                            $lastArray = end($attendanceArray);
                            $prevRemark = $lastArray[15];
                            // $prevAnnualS = $lastArray[9];
                            // $prevAnnualE = $lastArray[10];
                            // $prevOverS = $lastArray[12];
                            // $prevOverE = $lastArray[13];
                            if($overtime_hours > 0){
                                if($column === 13){
                                    $attendanceArray[$attendanceArrayListNum-1][15] = $prevRemark."(".$SBtime;
                                    //$strArray1[15] .= ",".$SBtime;
                                }
                                if($column === 15){
                                    $attendanceArray[$attendanceArrayListNum-1][15] = $prevRemark."-".$SBtime.')、';
                                    //$strArray1[15] .= ",".$SBtime.";";
                                }
                            }
                            if($vocation_hours > 0){
                                if($column === 13){
                                    $attendanceArray[$attendanceArrayListNum-1][15] = $prevRemark."(".$SBtime;
                                    //$strArray1[15] .= ",".$SBtime;
                                }
                                if($column === 15){
                                    $attendanceArray[$attendanceArrayListNum-1][15] = $prevRemark."-".$SBtime.')、';
                                    //$strArray1[15] .= ",".$SBtime.";";
                                }
                            }
                        }else{
                            if($overtime_hours > 0){
                                if($column === 13){
                                    $strArray1[12] = $SBtime;
                                    $strArray1[9] = null;
                                    $strArray1[15] .= "(".$SBtime;
                                }
                                if($column === 15){
                                    $strArray1[13] = $SBtime;
                                    $strArray1[10] = null;
                                    $strArray1[15] .= "-".$SBtime.")、";
                                }
                            }
                            if($vocation_hours > 0){
                                if($column === 13){
                                    $strArray1[9] = $SBtime;
                                    $strArray1[12] = null;
                                    $strArray1[15] .= "(".$SBtime;
                                }
                                if($column === 15){
                                    $strArray1[10] = $SBtime;
                                    $strArray1[13] = null;
                                    $strArray1[15] .= "-".$SBtime.")、";
                                }
                            }
                            if($vocation_hours == 0 || $overtime_hours == 0){
                                $strArray1[12] = null;
                                $strArray1[9] = null;
                                $strArray1[13] = null;
                                $strArray1[10] = null;
                            }
                        }

                    } else {
                        // 非資料欄位
                    }

                    if($column === $allColumn){
                        // 當每行資料組成陣列後，將該陣列為一個單位放入$attendanceArray陣列

                        if($strArray1[1] == $mergeCells){

                        } else {
                            array_push($attendanceArray, $strArray1);
                            $attendanceArrayListNum ++;
                        }
                        $strArray1 = array();
                    }
                }
            }

            // 準備寫入資料庫
            
            // LG($attendanceArray);
            $num_att = count($attendanceArray);
            $null = null;
            $aberrantGroup =  "";

            if($num_att >= 1){
                for($row=0; $row <= $num_att-1; $row++){
                    $list             = $row+1;
                    $staff_id         = $attendanceArray[$row][0];
                    $date             = $attendanceArray[$row][1];
                    $checkin_hours    = $attendanceArray[$row][2];
                    $checkout_hours   = $attendanceArray[$row][3];
                    $work_hours_total = $attendanceArray[$row][4];
                    $late             = $attendanceArray[$row][5];
                    $early            = $attendanceArray[$row][6];
                    $nocard           = $attendanceArray[$row][7];
                    $vocation_hours     = $attendanceArray[$row][8];
                    $vocation_from      = $attendanceArray[$row][9];
                    $vocation_to        = $attendanceArray[$row][10];
                    $overtime_hours   = $attendanceArray[$row][11];
                    $overtime_from    = $attendanceArray[$row][12];
                    $overtime_to      = $attendanceArray[$row][13];
                    $aberrant         = $attendanceArray[$row][14]; // 假別(加班別)
                    $remark           = $attendanceArray[$row][15]; // 假別(加班別),開始時間,結束時間;

                            // echo $remark; 

                    if($staff_id === "NULL" ){
                        // 更新錯誤訊息容器
                        $wrongMsgArray[]="第 $page 頁 / 第 $list. 筆: 査無此員工編號  $staffNo 。";
                        continue;
                    }else if($date === "'NULL'" ){
                        // 更新錯誤訊息容器
                        $wrongMsgArray[]="第 $page 頁 / 第 $list. 筆: 日期或資料內容有誤。";
                        continue;
                    }else if($checkin_hours === "NULL" || $checkout_hours === "NULL" || $work_hours_total === "NULL" || $late === "NULL" || $early === "NULL" || $nocard === "NULL" || $vocation_hours === "NULL" || $vocation_from === "NULL" || $vocation_to === "NULL" || $overtime_hours === "NULL" || $overtime_from === "NULL" || $overtime_to === "NULL"){
                        $wrongMsgArray[]="第 $page 頁 / 第 $list. 筆: 資料是否正確。";
                        continue;
                    } else {
                        // $staffAttendance = array(
                            // 0 => $staff_id,
                            // 1 => $date,
                            // 2 => $checkin_hours,
                            // 3 => $checkout_hours,
                            // 4 => $work_hours_total,
                            // 5 => $late,
                            // 6 => $early,
                            // 7 => $nocard,
                            // 8 => $vocation_hours,
                            // 9 => $vocation_from,
                            // 10 => $vocation_to,
                            // 11 => $overtime_hours,
                            // 12 => $overtime_from,
                            // 13 => $overtime_to,
                            // 14 => $remark
                        // );
                        $staffAttendance = array(
                            'staff_id' => $staff_id,
                            'date' => $date,
                            'checkin_hours' => $checkin_hours,
                            'checkout_hours' => $checkout_hours,
                            'work_hours_total' => $work_hours_total,
                            'late' => $late,
                            'early' => $early,
                            'nocard' => $nocard,
                            'vocation_hours' => $vocation_hours,
                            'vocation_from' => $vocation_from,
                            'vocation_to' => $vocation_to,
                            'overtime_hours' => $overtime_hours,
                            'overtime_from' => $overtime_from,
                            'overtime_to' => $overtime_to,
                            'remark' => iconv(mb_detect_encoding($remark, mb_detect_order(), true), "UTF-8", $remark)
                        );
                        $all[$staff_id.'-'.str_replace("'","",$date)] = $staffAttendance;
                        $staff_id_array[$staff_id] = $staff_id;
                        $date_array[$date] = $date;
                                // 檢查 id && date 是否已存在
                                // $sql = "SELECT * FROM `rv_attendance` WHERE `staff_id` = \"$staffId\" AND `date` = \"$date\"";
                                // $api->DB -> query($sql);
                                // $mothlyAtt_result = $dbc->getPDO() -> prepare($sql);
                                // $mothlyAtt_result -> execute();
                                // $mothlyAtt = $mothlyAtt_result -> fetchAll(PDO::FETCH_ASSOC);
                                // $num3 = count($mothlyAtt);

                                // if($num3 == 1){
                                    // 更新資料 -> rv_attendance資料表單已有同ID DATE的資料
                                    // $sql = "UPDATE `rv_attendance` SET `checkin_hours`='$checkin_hours',`checkout_hours`='$checkout_hours',`work_hours_total`='$work_hours_total',`late`='$late',`early`='$early',`nocard`='$nocard',`vocation_hours`='$vocation_hours',`vocation_from`='$vocation_from',`vocation_to`='$vocation_to',`overtime_hours`='$overtime_hours',`overtime_from`='$overtime_from',`overtime_to`='$overtime_to',`remark`='$remark'  WHERE `staff_id`='$staff_id' AND `date`='$date'";
                                // }else if($num3 == 0){
                                    // 新增資料
                                    // $sql = "INSERT INTO rv_attendance(`staff_id`, `date`, `checkin_hours`, `checkout_hours`, `work_hours_total`, `late`, `early`, `nocard`, `vocation_hours`, `vocation_from`, `vocation_to`, `overtime_hours`, `overtime_from`, `overtime_to`, `remark`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                                // }



                                // $sth = $dbc->getPDO() -> prepare($sql);

                                // if($sth->execute($staffAttendance) || $sth->execute()){
                                    // $successNum ++;
                                // } else {
                                    
                                // }
                      }
                  }
                  
                  $attendanceArray = array();
            } else {
                // 預計寫入的陣列中並無資料
                
            }
        }// for sheet
        // LG($all);
        if(count($wrongMsgArray)!=0){
          //有錯誤訊息  跳出印訊息
          break;
        }
        
        $all_count = count($all);
        
        //檢查是否存在記錄
        $sids = join(',',$staff_id_array);
        $dates = "'".join("','",$date_array)."'";
        $exist = $attendance->read($attendance->invertColumn(array('id'))," where staff_id in ($sids) and date in ($dates)")->map('staff_id,date',true);
        $update = array();
        
        // LG($all);
        foreach($exist as $key => &$val){
          unset($val['_ORDER_POSITION']);
          if( isset($all[$key]) ){
            foreach( $val as $kk => &$vv ){
              $invar=$all[$key][$kk];
              if( !empty($invar) && $vv!=$invar && strpos($vv,$invar)===false){
                $update[$key]=$all[$key]; 
                // var_dump($kk);
                // var_dump($vv);
                // var_dump($invar);
                break; 
              }
            }
            // $update[$key]=$val; 
            unset($all[$key]); 
          }
        }
        // LG($update);
        // LG($exist);
        // LG($all);
        
        $loop_time += microtime(true) - $time_start;
        $time_start = microtime(true);
        
        $insert_count = count($all);
        if( $insert_count>0){
          foreach($all as &$val){
            foreach($val as &$vv){$vv = "'$vv'";}
            $attendance->addStorage($val);
          }
          $insert_count = $attendance->addRelease();
        }
        
        
        
        $insert_time += microtime(true) - $time_start;
        $time_start = microtime(true);
        
        // 有空在試 虛擬表 update
        // CREATE TEMPORARY TABLE temp ... ENGINE = MEMORY;
        // INERT INTO temp ... VALUES ..., ...;
        // UPDATE target, temp SET target.name = temp.name WHERE target.id = temp.id;
        
        $update_count = count($update);
        if( $update_count>0){
          foreach($update as &$val){
            $attendance->update($val,' where staff_id ='.$val['staff_id'].' and date ="'.$val['date'].'"');
          }
          
        }
        $update_time += microtime(true) - $time_start;
        
        
      //if
    }else{
      $wrongMsgArray[]='File Error.';
    }
    
}//for files

if(count($wrongMsgArray)==0){
  $api->setArray(array(
   'insert_count' => $insert_count,
   'insert_time' => $insert_time,
   'update_count' => $update_count,
   'update_time' => $update_time,
   'all_count' => $all_count,
   'loop_time' => $loop_time
  ));
}else{
  $api->setArray(array(
   'all_count' => 0,
   'loop_time' => $loop_time
  ));
  $api->denied(join(',',$wrongMsgArray));
}
print $api->getJSON();
//print_r($uploadFiles);
?>
