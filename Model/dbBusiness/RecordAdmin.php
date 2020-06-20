<?php
namespace Model\Business;

include_once __DIR__.'/Common/DBPropertyObject.php';

class RecordAdmin extends DBPropertyObject{

  const TYPE_SETTING = 1;
  const TYPE_MONTH = 2;
  const TYPE_YEAR = 3;
  const TYPE_MONTH_REPORT = 4;
  const TYPE_YEAR_REPORT = 5;
  const TYPE_EXCEL = 6;



  //實體表 :: 單表
  public $table_name = "rv_record_admin";

  //欄位
  public $tables_column = Array(
    'id',
    'operating_staff_id',
    'type',
    'doing',
    'api',
    'changed_json',
    'update_date',
    'ip'
  );

  protected $api_map = Array(
    '/Api/Setting/addDepartment'=>'新增單位',
    '/Api/Setting/addSettingPost'=>'新增職務',
    '/Api/Setting/addSettingTitle'=>'新增職務類別',
    '/Api/Setting/updateCycleConfig'=>'更新月考評區間設定',
    '/Api/Setting/updateDepartment'=>'更新單位',
    '/Api/Setting/updateSettingPost'=>'更新職務設定',
    '/Api/Setting/updateSettingTitle'=>'更新職務類別設定',
    '/Api/Setting/updateYearCycleConfig'=>'更新年考評區間設定',
    '/Api/Excel/uploadForgetCard'=>'更新忘刷忘帶卡紀錄',
    '/Api/Data/updateMonthlyNoScore'=>'更新月考評不計分人員',
    '/Api/Data/getDepartmentList'=>'檢查/更新 月績效報表',
    '/Api/Data/Yearly/updateYearlyConstructStaff'=>'更新年組織設定',
    '/Api/Data/Yearly/getYearlyConstruct'=>'建立年組織結構',
    '/Api/Data/Yearly/checkYearlyFeedback'=>'部屬回饋問卷報表',
    '/Api/Data/Yearly/launchYearlyFeedback'=>'啟動部屬回饋問卷',
    '/Api/Data/Yearly/closeYearlyFeedback'=>'關閉部屬回饋問卷',
    '/Api/Data/Yearly/checkYearlyAssessment'=>'年考評報表',
    '/Api/Data/Yearly/launchYearlyAssessment'=>'啟動年考評',
    '/Api/Data/Yearly/closeYearlyAssessment'=>'關閉年考評',
    '/Api/Data/Yearly/finishYearly'=>'結束年考評',
    '/Api/Data/Yearly/setAssessmentCancel'=>'年考評作廢'
  );

  protected $type_map = Array(
    1=>'設定',
    2=>'月考評',
    3=>'年考評',
    4=>'月考評報表',
    5=>'年考評報表',
    6=>'EXCEL'
  );

  private $operating_staff_id;
  private $type;

  public function __construct($staff_id=0, $type=1){
    $this->operating_staff_id = $staff_id;
    $this->type = $type;
    parent::__construct();
  }

  public function type($t){
    $this->type = $t;
    return $this;
  }

  //override
  public function add($data=null){
    $str = json_encode($data,JSON_UNESCAPED_UNICODE);
    return $this->general(1,$str);
  }
  //override
  public function update($data=null, $b=null){
    $str = json_encode($data,JSON_UNESCAPED_UNICODE);
    return $this->general(2,$str);
  }
  //override
  public function delete($data=null){
    $str = json_encode($data,JSON_UNESCAPED_UNICODE);
    return $this->general(3,$str);
  }
  private function general($do, $str_json){
    $add = [];
    $add['operating_staff_id'] = $this->operating_staff_id;
    $add['type'] = $this->type;
    $add['doing'] = $do;
    $api = preg_replace('/\?[\w\W]*/i','',$_SERVER['REQUEST_URI']);
    $api = str_replace(WEB_ROOT,'',$api);
    $api = preg_replace('/^\/+/','/',$api);
    $add['api'] = $api;
    $add['changed_json'] = $str_json;
    $add['ip'] = getIP();
    return parent::add($add);
  }

  //override
  public function select($a=null,$b=0,$c=null){
    parent::select($a,$b,$c);
    return $this->parseJSON()->data;
  }
  //override
  public function read($a=null,$b=0,$c=null){
    parent::read($a,$b,$c);
    return $this->parseJSON();
  }
  //override
  public function sql($a, $bindData= []){
    parent::sql($a, $bindData);
    return $this->parseJSON();
  }

  private function parseJSON(){
    if (is_array($this->data)) {
      foreach($this->data as &$val){
        if(isset($val['changed_json'])){  $val['changed_json'] = json_decode($val['changed_json'],true); }
        if(isset($val['api'])){  $val['_operating'] = isset($this->api_map[$val['api']]) ? $this->api_map[$val['api']] : 'Unknow'; }
        // if(isset($val['type'])){  $val['type'] = isset($this->type_map[$val['type']]) ? $this->type_map[$val['type']] : 'Unknow'; }
      }
    }
    return $this;
  }

}
?>
