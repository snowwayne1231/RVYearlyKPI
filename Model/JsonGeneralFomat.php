<?php
namespace Model;

if(!function_exists('ms')){
  function ms(){
    return (int) (microtime(true)*1000);
  }
}

class JsonGeneralFomat {
  
  public static $SUCCESS = array(200,"SUCCESS.");
    
  public static $ERROR_OTHER = array(0,"Undefined Error.");
  
  public static $ERROR_PARAM = array(1,"Param Error.");
  
  public static $ERROR_SQL = array(2,"Database Error.");
  
  public static $DENIED = array(3,"Denied Request.");
  
  public static $INPUT_REJECT = array(4,"Input Reject.");
  
  public static $INPUT_WRONG = array(5,"Input Wrong.");
  
  private $jsonString;
  
  private $jsonArray;
  
  private $msg;
  
  private $status;
  
  private $result;
  
  private $runtime_start;
  private $runtime = 0;
  
  public function __construct($str='[]'){
    try{
      $this->jsonArray = json_decode($str,true);
      $this->checkJson();
      $this->runtime_start = ms();
    }catch(Exception $e){
      $this->status = 0;
      $this->msg = $e;
    }
  }
  
  private function checkJson(){
    $this->status = (count($this->jsonArray)>0)? (self::$SUCCESS[0]):(self::$ERROR_OTHER[0]);
    $this->jsonString = json_encode($this->jsonArray);
  }
  
  public function getResult(){
    $this->runtime = ms() - $this->runtime_start;
    $json = Array(
      "msg" => $this->msg,
      "status" => $this->status,
      "runtime" => $this->runtime.'ms',
      "result" => $this->jsonArray
    );
    try{
      $this->result = json_encode($json);
    }catch(Exception $e){
      $this->result = '{"msg":null,"status":0}';
    }
    
    ob_clean();
    $length = strlen($this->result);
    header('Content-Length: '.$length);
    header('Content-Type: application/json; charset=utf-8');
    
    
    return $this->result;
  }
  
  public function getArray(){
    return $this->jsonArray;
  }
  public function setArray($ary){
    $this->jsonArray = $ary;
    $this->msg = '';
    $this->checkJson();
    return $this;
  }
  
  public function getJson(){
    $this->jsonString = json_encode($this->jsonArray);
    return $this->jsonString;
  }
  
  public function setJson($a="[]"){
    // $this->jsonString = $a;
    try{
      $this->jsonArray = json_decode($a,true);
      $this->checkJson();
    }catch(Exception $e){
      $this->status = 0;
      $this->msg = $e;
    }
    return $this;
  }
  
  public function isActive(){
    return ($this->status==(self::$SUCCESS[0]))?true:false;
  }
  
  public function setMsg($msg=""){
    if(is_array($msg)){ $msg=join(", ",$msg); }
    $this->msg = $msg;
    return $this;
  }
  
  public function setStatus($val){
    $this->status = $val[0];
    $this->msg = $val[1];
    return $this;
  }
  
}

?>
