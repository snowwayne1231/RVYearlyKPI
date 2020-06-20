<?php
namespace Model\Business;

include_once __DIR__.'/Common/DBPropertyObject.php';

/**
 * 月考評進程
 */
class MonthlyProcessing extends DBPropertyObject{

  //實體表 :: 單表
  public $table_name = "rv_monthly_processing";

  //欄位
  public $tables_column = Array(
    'id',
    'status_code',
    'lv',
    'type',
    'commited',
    'created_staff_id',
    'created_department_id',
    'year',
    'month',
    'owner_staff_id',
    'owner_department_id',
    'prev_owner_staff_id',
    'path_staff_id'
  );

  //狀態 status_code
  const STATUS_CODE_ERROR = 0; //錯誤
  const STATUS_CODE_PERPARE = 1; //準備
  const STATUS_CODE_FIRST = 2; //初步考評
  const STATUS_CODE_REVIEW = 3; //審核階段
  const STATUS_CODE_REJECT = 4; //退回
  const STATUS_CODE_APPROVED = 5; //核准

  //status_code
  public static $statusCode = array(
    self::STATUS_CODE_ERROR => '錯誤',
    self::STATUS_CODE_PERPARE => '準備',
    self::STATUS_CODE_FIRST => '初步考評',
    self::STATUS_CODE_REVIEW => '審核階段',
    self::STATUS_CODE_REJECT => '退回',
    self::STATUS_CODE_APPROVED => '核准'
  );

  //type
  const TYPE_TEAM_LEADER = 1;
  const TYPE_TEAM_MEMBER = 2;

  public static $type = array(
    self::TYPE_TEAM_LEADER => '組長',
    self::TYPE_TEAM_MEMBER => '組員'
  );

  public $year;
  public $month;

  public function __construct($db=null){
    parent::__construct($db);
  }

  //override
  public function select($a=null,$b=0,$c=null){
    parent::select($a,$b,$c);
    foreach($this->data as &$val){
      if(isset($val['path_staff_id']))$val['path_staff_id'] = json_decode($val['path_staff_id']);
    }
    return $this->data;
  }

  //override
  public function read($a=null,$b=0,$c=null){
    parent::read($a,$b,$c);
    foreach($this->data as &$val){
      if(isset($val['path_staff_id']))$val['path_staff_id'] = json_decode($val['path_staff_id']);
    }
    return $this;
  }

  /**
   * 是否為建立者
   * @param  integer $manager_id 管理職ID
   * @param  array   $data       要用來判斷的資料
   * @return boolean 判斷結果
   */
  public function isOnCreator($manager_id,$data=false){
    $data = ($data)?$data:$this->data[0];
    return $data['created_staff_id']==$manager_id;
  }

  /**
   * 是否是初始單位
   */
  public function isOnCreateDepartment($department_id, $data=false) {
    $data = ($data)?$data:$this->data[0];
    return $data['created_department_id'] == $department_id;
  }

  /**
   * 是否為擁有者
   * @param  integer $manager_id 管理職ID
   * @param  array   $data       要用來判斷的資料
   * @return boolean 判斷結果
   */
  public function isOwner($manager_id,$data=false){
    $data = ($data)?$data:$this->data[0];
    return $data['owner_staff_id']==$manager_id;
  }

  /**
   * 是否為擁有者部門
   */
  public function isOwnerDepartment($department_id, $data=false) {
    $data = ($data)?$data:$this->data[0];
    return $data['owner_department_id']==$department_id;
  }

  /**
   * 是否為上一位擁有者
   * @param  integer $manager_id 管理職ID
   * @param  array   $data       要用來判斷的資料
   * @return boolean 判斷結果
   */
  public function isPreOwner($manager_id,$data=false){
    $data = ($data)?$data:$this->data[0];
    return $data['prev_owner_staff_id']==$manager_id;
  }

  /**
   * 是否在月考評單，送審的路程中
   * @param  integer $manager_id 管理職ID
   * @param  array   $data       要用來判斷的資料
   * @param  boolean $under      是否要判斷當前擁有者
   * @return boolean 判斷結果
   */
  public function isRelation($manager_id,$data=false,$under=false){
    $data = ($data)?$data:$this->data[0];
    $ary = (is_array($data['path_staff_id']))?$data['path_staff_id']:json_decode($data['path_staff_id']);
    $bl = in_array( $manager_id, $ary );
    if($under && $bl){
      $p1 = array_search( $manager_id, $ary );
      $p2 = array_search( $data['owner_staff_id'], $ary );
      $bl = $p2 <= $p1;
    }
    return $bl;
  }

  /**
   * 是否可以送審
   * @param  array   $data 要用來判斷的資料
   * @return boolean 判斷結果
   */
  public function isLaunch($data=false){
    $data = ($data)?$data:$this->data[0];
    return (int)$data['status_code'] > 1;
  }

  /**
   * 是否考評核准
   * @param  array   $data 要用來判斷的資料
   * @return boolean 判斷結果
   */
  public function isDone($data=false){
    $data = ($data)?$data:$this->data[0];
    return $data['status_code'] == 5;
  }

  /**
   * 是否為主管單
   * @return boolean 判斷結果
   */
  public function isLeaderType(){
    if( empty($this->data[0]) ){return null;}
    return $this->data[0]['type']==1;
  }

  /**
   * 取得建立者ID
   * @return integeer 建立者ID
   */
  public function getCreator(){
    if( empty($this->data[0]) ){return null;}
    return $this->data[0]['created_staff_id'];
  }

  /**
   * 取得擁有者ID
   * @return integeer 擁有者ID
   */
  public function getOwner(){
    if( empty($this->data[0]) ){return null;}
    return $this->data[0]['owner_staff_id'];
  }

  /**
   * 取得下一位擁有者
   * @return integeer 下一位擁有者ID
   */
  public function getNextOwner() {
    if( empty($this->data[0]) ){return null;}
    $currentOwner = $this->getOwner();
    $path = $this->data[0]['path_staff_id'];

    $current_index = array_search($currentOwner, $path, true);
    $count = count($path);
    $next_index = (($current_index+1) > $count )  ? $count-1 : $current_index+1;
    return $path[$next_index];
  }

  /**
   * 取得前一位擁有者
   * @param  integeer $owner 當前擁有者ID
   * @param  array    $path  進程路徑
   * @return integeer 前一位有者ID
   */
  public function getPrevOwner($owner = '', $path = '') {
    if ($owner !='') {
      $currentOwner = $owner;
    } else {
      if( empty($this->data[0]) ){return null;}
      $currentOwner = $this->getOwner();
    }
    if ($path == '') {
      $path = $this->data[0]['path_staff_id'];
    }
    $current_index = array_search($currentOwner, $path, true);
    $count = count($path);
    $prev_index = (($current_index-1) <= 0)  ? 0 : $current_index-1;
    return $path[$prev_index];
  }

  /**
   * 取得 當前擁有者與上一個擁有者的單子
   * @param  array   $data 要比對的進程資料
   * @param  boolean $all  是否要取得所有狀態，預設為只取「未核准」單
   * @return array   符合結果的進程單
   */
  public function getThisWithOwner($data, $all = false){
    if( empty($data['owner_staff_id']) ){ return []; }
    $where = "WHERE (owner_staff_id = ".$data['owner_staff_id']." OR prev_owner_staff_id = ".$data['owner_staff_id'].") ";
    $where .= $all ? '' : " AND status_code < ".self::STATUS_CODE_APPROVED;
    $where .= isset($data['year']) ? ' AND year = '.$data['year'] : '';
    $where .= isset($data['month']) ? ' AND month = '.$data['month'] : '';
    $where .= isset($data['date_after']) ? " AND create_date > '".$data['date_after']."'" : '';
    $this->select('*',$where,'order by year asc, month asc, type asc, created_department_id asc');
    return $this->data;
  }

  /**
   * 
   */
  public function getThisWithOwnerDepartmentId($data, $all = false){
    if( empty($data['owner_department_id']) ){ return []; }
    $where = "WHERE (owner_department_id = ".$data['owner_department_id'].") ";
    $where .= $all ? '' : " AND status_code < ".self::STATUS_CODE_APPROVED;
    $where .= isset($data['year']) ? ' AND year = '.$data['year'] : '';
    $where .= isset($data['month']) ? ' AND month = '.$data['month'] : '';
    $where .= isset($data['date_after']) ? " AND create_date > '".$data['date_after']."'" : '';
    return $this->select('*',$where,'order by year asc, month asc, type asc, created_department_id asc');
  }

  /**
   * 取得指定 職員 還未完成的單
   * @param  integer $staff_id 職員ID
   * @return array   未完成的單
   */
  public function getUnDo($staff_id){
    $inin = [self::STATUS_CODE_FIRST, self::STATUS_CODE_REVIEW, self::STATUS_CODE_REJECT];
    $three_month = date("Y-m-d", time() - (60 * 60 * 24 * 30 * 3));
    return $this->select(['id'],['status_code'=>'in('.join(',',$inin).')', 'owner_staff_id'=>$staff_id, 'create_date'=>">'$three_month'"]);
  }

  /**
   * 依照類型分配進程ID
   * @return array 分配結果
   */
  public function shuntIdWithType(){
    $loc = array();
    foreach($this->data as $val){
      $loc[$val['type']][] = $val['id'];
    }
    return $loc;
  }

  /**
   * 調整上一個使用者
   * @method     adjustPrevOwnerStaffId
   * @author Alex Lin <alex.lin@rv88.tw>
   * @version    [version]
   * @modifyDate 2017-08-24T11:51:06+0800
   * @return     [type]                   [description]
   */
  public function adjustPrevOwnerStaffId() {
    $list = $this->select(['path_staff_id', 'id', 'owner_staff_id', 'prev_owner_staff_id'],[1=>1]);
    foreach ($list as $key => $item) {
      $prev_owner_staff_id = $item['prev_owner_staff_id'];
      if ($item['prev_owner_staff_id'] != $item['owner_staff_id']) {
        $prev_owner_staff_id = $this->getPrevOwner($item['owner_staff_id'], $item['path_staff_id']);
      }
      if ( $prev_owner_staff_id != $item['prev_owner_staff_id']) { //有變更才修改
        $this->update(['prev_owner_staff_id' => $prev_owner_staff_id], ['id' => $item['id']]);
      }
    }
    return true;
  }

  /*20181116 Carmen 尚未被使用到，先拿掉
  public function drawAble($isAdmin = false) {
    if( empty($this->data[0]) ){return null;}
    if ($this->data[0]['status_code'] == self::STATUS_CODE_APPROVED) {
      return $isAdmin;
    } else {
      return true;
    }
  }
  */

}
?>
