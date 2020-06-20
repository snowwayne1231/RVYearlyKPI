<?php
namespace Model;

class SessionCenter {

  public static $sessionMemberKey = 'RV_HR_Member';

  private $sessinLifetime = 'session.gc_maxlifetime';

  private $pidStr = 'PHPSESSID';

  private $userMember_key = "USER";

  public function __construct($expire=0){
    if($expire==0){
        $expire=ini_get($this->sessinLifetime);
    }else{
        ini_set($this->sessinLifetime,$expire);
    }
    if(empty($_COOKIE[$this->pidStr])){
        session_start();
        session_set_cookie_params($expire);
    }else{
        session_start();
        setcookie($this->pidStr,session_id(),time()+$expire);
    }

    if(empty($_SESSION[self::$sessionMemberKey])){
      $_SESSION[self::$sessionMemberKey] = Array();
    }

  }

  public function get($key=null){
    $s=( !empty($_SESSION[self::$sessionMemberKey]) ) ? $_SESSION[self::$sessionMemberKey] : null;
    if(empty($key)){ return $s; }
    return isset($s[$key])?$s[$key]:null;
  }

  public function set($key,$val){
    $_SESSION[self::$sessionMemberKey][$key] = $val;
    return $this;
  }

  public function clear(){
    // session_destroy();
    $_SESSION[self::$sessionMemberKey] = null;
    return $this;
  }

  public function getMember(){
    $tmp = $this->get();
    return (isset($tmp) && count($tmp)>0) ? $tmp[$this->userMember_key] : null;
  }

  public function getDepartmentId(){
    $tmp = $this->getMember();
    return isset($tmp['department_id']) ? $tmp['department_id'] : null;
  }

  public function getId(){
    $tmp = $this->getMember();
    return isset($tmp['id']) ? $tmp['id'] : null;
  }

  public function getSubDepartmentId($hasSelf=false){
    $tmp = $this->getMember();
    $result = isset($tmp['_department_sub']) ? $tmp['_department_sub'] : null;
    if($hasSelf){array_unshift($result,$this->getDepartmentId());}
    //$result = array_unique($result);
    return $result;
  }

  public function getJSON(){
    $member = $this->getMember();
    $member['now'] = @date('Y-m-d H:i:s');
    if($member){
      $json = json_encode($member);
    }else{
      $json = 'false';
    }
    return $json;
  }

  public function setMember($ary){
    $this->set($this->userMember_key,$ary);
    return $this;
  }

  public function isCEO(){
    $tmp = $this->getMember();
    return (isset($tmp) && count($tmp['_department_upper_path']) <= 1 && $tmp['is_leader']) && $tmp['id'] == 1;
  }

  public function isSuperUser(){
    $tmp = $this->getMember();
    return $this->isCEO() || $this->isAdmin();
  }

  public function isTopLeader(){
    $tmp = $this->getMember();
    return (isset($tmp) && (int)$tmp['is_leader'] == 1 && (int)$tmp['lv'] <= 2);
  }

  public function isLeader(){
    $tmp = $this->getMember();
    return (isset($tmp) && (int)$tmp['is_leader'] == 1);
  }

  public function isAdmin(){
    $tmp = $this->getMember();
    return (isset($tmp) && (int)$tmp['is_admin'] == 1);
  }

  public function isLogin(){
    return isset($_SESSION[self::$sessionMemberKey]) && count($_SESSION[self::$sessionMemberKey])>0;
  }



}

?>
