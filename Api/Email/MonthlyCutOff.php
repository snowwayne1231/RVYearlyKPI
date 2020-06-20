<?php
include __DIR__.'/../ApiCore.php';
include BASE_PATH.'/Model/MailCenter.php';
include_once BASE_PATH.'/Model/dbBusiness/MonthlyProcessing.php';
include_once BASE_PATH.'/Model/dbBusiness/ConfigCyclical.php';


if( empty($_SERVER['HTTP_HOST']) ){
  //被命令呼叫


}else{
  //被網頁呼叫


}

//只催該月的
$year = date('Y');
$month = date('m');
if($month==1){$year--;}

$config_cyc = new Model\Business\ConfigCyclical($year,$month);
$cyc_data = $config_cyc->data;
$cutTime = strtotime($cyc_data['cut_off_date']);
$month_need_send = ( $cutTime && $cyc_data['monthly_launched']!=0 && $cutTime<strtotime(date('Y-m-d')) );


//月催
if( $month_need_send ){
  $process = new Model\Business\MonthlyProcessing();
  $process_data = $process->select(array('owner_staff_id','id','status_code'),array('status_code'=>'< 5','year'=>$year,'month'=>$month));
  $month_done = count($process_data)==0;

  if(!$month_done){

    $process_map = $process->map('owner_staff_id',true);

    $mail = new Model\MailCenter;
    foreach($process_map as $key => &$val){
      $mail->addAddress($key);
    }
     // $mail->addAddress(80);
    // $mail->addCC('mavis.wu@rv88.tw');

    $res = $mail->sendTemplate('monthly_delay',array(
      'year'=>$year,
      'month'=>$month,
      'cut_off_date'=>str_replace('-','/',$cyc_data['cut_off_date'])
    ));

    if($res===true){
      echo 'Sended by Monthly. ';
    }else{
      echo $res;
    }

  }else{
    echo 'Complete Monthly. ';
  }
}


//年催
$year_config = new \Model\Business\YearPerformanceConfigCyclical();
$year_data = $year_config->select(['year','processing','feedback_date_end','assessment_date_end'],['processing'=>'<'.$year_config::PROCESSING_HISTORY],'order by year desc');
// dd($year_data);
if( count($year_data)>0){   //有未完成的年績效
  $year_data = $year_config->data[0];
  $year = $year_data['year'];
  $processing = (int) $year_data['processing'];
  if($processing == 0){echo 'Yearly Not Launch';exit;}

  $feedback_time_end = strtotime($year_data['feedback_date_end']);
  $assessment_time_end = strtotime($year_data['assessment_date_end']);
  $now = strtotime(date('Y-m-d'));
  // $staff = new Model\Business\Staff();

  // var_dump($year_data);
  if($feedback_time_end && $processing < $year_config::PROCESSING_CLOSE && $now >= $feedback_time_end){
    //部屬問卷還未關閉 且時間超過
    $feedback = new \Model\Business\YearPerformanceFeedback();
    $feedback_staff = $feedback->select(['staff_id'],['status'=>$feedback::STATUS_UN_SUBMIT]);

    if(count($feedback_staff)>0){
      $mail = new \Model\MailCenter();
      foreach($feedback_staff as $key => $fsv){
        $mail->addAddress($fsv);
      }

      $res = $mail->sendTemplate('yearly_feedback_delay',array(
        'year'=>$year,
        'day_end'=>$year_data['feedback_date_end']
      ));
      if($res===true){
        echo 'Sended by Feedback :: '.count($feedback_staff).' . ';
      }else{
        echo $res;
      }
    }else{
      echo 'Done With Feedback.  ';
    }

  }

  if($assessment_time_end && $processing < $year_config::PROCESSING_FINISH_WELL && $now >= $assessment_time_end){
    //年考評還未走入歷史 且時間超過
    $assessment = new \Model\Business\YearPerformanceReport();
    $assessment_staff = $assessment->select(['staff_id','department_id','owner_staff_id','path','path_lv'],['processing_lv'=>'!='.$assessment::PROCESSING_LV_STOP,'owner_staff_id'=>'>0']);
    $division = new  \Model\Business\YearPerformanceDivisions();
    $division_staff = $division->select(['division','owner_staff_id'], ['processing'=>'<'.$division::PROCESSING_ARCHITE_WAIT,'owner_staff_id'=>'>0']);
    
    foreach($division_staff as &$dsv){
      $dsv['department_id'] = $dsv['division'];
      $dsv['staff_id'] = $dsv['owner_staff_id'];
    }
    
    $yearly_staff = array_merge($assessment_staff,$division_staff);
    // $yearly_staff = [['owner_staff_id'=>2],['owner_staff_id'=>56],['owner_staff_id'=>80],['owner_staff_id'=>31]];  //testing
    if(count($yearly_staff)>0){

      
      $yearly_staff_owner = array_column($yearly_staff,'owner_staff_id');
      foreach($assessment_staff as $asv){
        foreach($asv['path'] as $asvp_id){
          if( !in_array($asvp_id, $yearly_staff_owner) ){$yearly_staff_owner[]=(int)$asvp_id;}
        }
      }
      // dd($yearly_staff_owner);
      $staff = new Model\Business\Staff();
      $all_yearly_staff = array_merge($yearly_staff_owner, array_column($yearly_staff,'staff_id') );
      $all_yearly_staff = array_unique($all_yearly_staff);
      
      $yearly_staff_map = $staff->mergeDepartmentForShow($all_yearly_staff, ['email'])->map();
      $admin_staff = $staff->getAdminUserEmail();
      
      $team = new Model\Business\Department();
      $team_map = $team->map();
      
      $assement_map = $assessment->cmap('staff_id');
      
      // $yearly_staff_emails = array_column($yearly_staff_data, 'email');
      // dd($yearly_staff_map);
      // dd($yearly_staff);
      // dd($admin_staff);
      
      foreach($yearly_staff as $yssendv){
        $mail = new \Model\MailCenter();
        // $mail->clear(['address','cc']);
        $this_staff_id = $yssendv['staff_id'];
        $this_department_id = $yssendv['department_id'];
        $this_staff = $yearly_staff_map[ $this_staff_id ];
        $this_team = $team_map[ $this_department_id ];
        
        $owner_staff_id = $yssendv['owner_staff_id'];
        $owner = $yearly_staff_map[$owner_staff_id];
        $mail->addAddress($owner['email']);
        if( isset($yssendv['path']) ){
          foreach($yssendv['path'] as $send_staff_id){
            $send_staff = $yearly_staff_map[$send_staff_id];
            $mail->addAddress($send_staff['email']);
          };
        }
        
        $mail->addCC($admin_staff);
        
        $yssendv['year'] = $year;
        $yssendv['assessment_date_end'] = $year_data['assessment_date_end'];
        $yssendv['unit_id'] = $this_team['unit_id'];
        $yssendv['department_name'] = $this_team['name'];
        $yssendv['name'] = $this_staff['name'];
        $yssendv['name_en'] = $this_staff['name_en'];
        $mail->sendTemplate('yearly_assessment_to_delay',$yssendv);
        usleep(50000);
      }
      // dd($yssendv);
      echo 'Sended by Yearly :: '.count($yearly_staff).' . ';
    }else{
      echo 'Done With Yearly Report.  ';
    }

  }


}else{
  echo 'Complete Yearly. ';
}




?>

