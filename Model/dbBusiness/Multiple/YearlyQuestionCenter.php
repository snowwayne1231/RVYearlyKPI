<?php
namespace Model\Business\Multiple;

include_once __DIR__.'/_MultipleSets.php';
// include_once __DIR__.'/../Staff.php';
// include_once __DIR__.'/../Department.php';
include_once __DIR__.'/../YearPerformanceConfigCyclical.php';
include_once __DIR__.'/../RecordYearPerformanceQuestions.php';
include_once __DIR__.'/../YearPerformanceFeedbackQuestions.php';

use \Exception;

/*
問答/部屬回饋
*/
class YearlyQuestionCenter extends MultipleSets{
  
  // protected $staff;
  // protected $team;
  // `from_type` int(2) NOT NULL DEFAULT '1' COMMENT '來源 1=部屬, 2=其他部門, 3=上司, 4=其他'
  const FROM_TPYE_UNDER = 1;
  const FROM_TPYE_FAR = 2;
  const FROM_TPYE_UPPER = 3;
  const FROM_TPYE_OTHER = 4;
  
  protected $config;
  protected $record;
  protected $question;
  
  public function __construct(){
    // $this->staff = new \Model\Business\Staff();
    // $this->team = new \Model\Business\Department();
    $this->config = new \Model\Business\YearPerformanceConfigCyclical();
    $this->record = new \Model\Business\RecordYearPerformanceQuestions();
    $this->question = new \Model\Business\YearPerformanceFeedbackQuestions();
  }
  
  //取問題
  public function getQuestionsWithStaff($year,$staff){
    $question_table = $this->question->table_name;
    $records = $this->record->sql( "select a.*, b.title, b.description  from {table} as a 
    left join $question_table as b on a.question_id = b.id 
    where a.year = $year and a.target_staff_id = $staff " )->data;
    $result = array();
    // dd($this->record->getSql());
    foreach($records as $v){
      $key = $this->fromType($v['from_type']);
      
      $result[$key][$v['question_id']]['title'] = $v['title'];
      $result[$key][$v['question_id']]['description'] = $v['description'];
      $result[$key][$v['question_id']]['contents'][]=[
        'content' => $v['content'],
        'create_date' => $v['create_date']
      ];
      
    }
    return $result;
  }
  //類型 字串
  private function fromType($inft){
    switch($inft){
      case self::FROM_TPYE_UNDER: $key = 'under';break;
      case self::FROM_TPYE_FAR: $key = 'far';break;
      case self::FROM_TPYE_UPPER: $key = 'upper';break;
      case self::FROM_TPYE_OTHER: default: $key = 'other';break;
    }
    return $key;
  }
  
  
}
?>
