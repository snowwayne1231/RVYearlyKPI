<?php

include __DIR__."/../ApiCore.php";

$api = new ApiCore($_POST);

use Model\Business\YearPerformanceConfigCyclical;
//'processing'
//0 = 未啟動, 1 = 部屬回饋產生, 2 = 部屬回饋收集, 3 = 部屬回饋關閉, 4 = 產生年考績, 5 = 收集年考績, 6= 暫停收集, 7= 完成收集, 8= 關帳 進入歷史資料
if( $api->checkPost(array('year')) && $api->SC->isAdmin() ){

  $year = $api->post('year');

  $data = $api->getPost( array("date_start","date_end","feedback_addition_day","assessment_addition_day") );

  if(count($data)==0){
    $api->denied('Not Enough Param.');
  }

  $nothing_date = '0000-00-00';

  $condition = array('year'=>$year);

  $config = new YearPerformanceConfigCyclical($year);

  $year_data = $config->data;

  if( (isset($data['date_start']) || isset($data['date_end'])) && $year_data['processing'] > YearPerformanceConfigCyclical::PROCESSING_RESET ){
    //產生問卷後 不能再設定  起始與結束日期
    $api->denied('Can Not Setting.');
  }

  if( isset($data['feedback_addition_day']) ){
    //問卷開始後 不能設定 問卷天數
    if($year_data['processing'] >= YearPerformanceConfigCyclical::PROCESSING_LAUNCHED){ $api->denied('Can Not Setting.'); }
    $data['feedback_date_start'] = $nothing_date;
    $data['feedback_date_end'] = $nothing_date;

  }

  if( isset($data['assessment_addition_day']) ){
    //年考績開始收集 不能設定 考評天數
    if($year_data['processing'] >= YearPerformanceConfigCyclical::PROCESSING_COLLECT){ $api->denied('Can Not Setting.'); }
    $data['assessment_date_start'] = $nothing_date;
    $data['assessment_date_end'] = $nothing_date;
  }

  $cdata = $config->update($data, $condition);

  if($cdata>0){
    $res = $config->select($condition)[0];
    unset($res['department_construct_json']);

    //紀錄
    $self_id = $api->SC->getId();
    $record_data = $data;
    $record_data['id']=$res['id'];
    $record_data['year']=$res['year'];
    $record = new \Model\Business\RecordAdmin( $self_id );
    $record->type($record::TYPE_YEAR)->update( $record_data );


    $api->setArray( $res );
  }else{
    $api->denied('Nothing Change.');
  }



}else{
  $api->denied();
}

print $api->getJSON();

?>