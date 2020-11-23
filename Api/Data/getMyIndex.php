<?php include __DIR__."/../ApiCore.php";
// include BASE_PATH.'/Model/dbBusiness/Multiple/Staff.php';

$api = new ApiCore();
$api->isLogin();

$my = $api->SC->getMember();
$self_id = $my['id'];
$result = [];

$undo = [];
$ym = ['year'=>(int)date('Y'),'mnoth'=>(int)date('m')];
//月績效

  $monthly = new \Model\Business\MonthlyProcessing();

  $undo['monthly'] = $monthly->getUnDo($self_id);

//年設定
$YCC = new \Model\Business\YearPerformanceConfigCyclical();
$year_config = $YCC->select(['processing','year'], 'where processing >= '.$YCC::PROCESSING_LAUNCHED.' and processing < '.$YCC::PROCESSING_FINISH_WELL );

if( count($year_config)>0 ){
  $year_config = $year_config[0];
  //部屬回饋
  if($year_config['processing']>$YCC::PROCESSING_CHECKED){
    $feedback = new \Model\Business\YearPerformanceFeedback();
    $undo['feedback'] = $feedback->getUnDo($self_id);
  }
  

  //年考評
  if($year_config['processing']>$YCC::PROCESSING_VERIFY){
    $yearly = new \Model\Business\YearPerformanceReport();
    
    if ($my['is_leader'] == 1) {
      $undo['yearly_assessment'] = $yearly->getUnDoWithLeader($my);
    } else {
      $undo['yearly_assessment'] = $yearly->getUnDo($self_id);
    }
  }
  

  //年部門單
  if($my['_department_lv']<=2 && $year_config['processing']>$YCC::PROCESSING_COLLECT && count($undo['yearly_assessment'])==0 ){
    $yearly_division = new \Model\Business\YearPerformanceDivisions();
    $undo['yearly_division'] = $yearly_division->getUnDo($self_id, $api->SC->isCEO() );
    
  }else{
    $undo['yearly_division'] =[];
  }
  $ym['year'] = (int)$year_config['year'];
  // $ym['year'] = 2014;
}


$result['undo'] = $undo;
$result['ym']= $ym;
//成功結果
$api->setArray($result);


print $api->getJSON(); ?>