<?php

  include(__DIR__."/../autoload.php");
  include(__DIR__."/../Global.php");
  
  include(BASE_PATH."/Model/SessionCenter.php");
  
  include_once(BASE_PATH."/Model/PropertyObject.php");
  
  include_once(BASE_PATH."/Model/JsonGeneralFomat.php");
  
  use Model\JsonGeneralFomat as JsonFomat;
  
  class ApiCore extends Model\PropertyObject{
    
    //建構子第三參數，判斷是否是管理者
    const IS_ADMIN = "isAdmin";
    
    //建構子第三參數，判斷是否是執行長
    const IS_CEO = "isCEO";
    
    const CACHE_TIME = 180;
    
    protected $postData;
    
    protected $jsonFomat;
    
    public $SC;
    
    public $cache = false;
    
    /**
     *  基本API class
     *  @param 第一個參數是Api 的 input
     *  @param 第二個參數是必須有的 input key
     *  @param 第三個參數是 額外的附加條件
     *  
     */
    public function __construct($data=Array(), $mustHas=Array(), $other_condition=''){
      
      $this->jsonFomat = new JsonFomat();
      
      if( !is_array($mustHas) ){ $this->denied('Data Error.'); }
      
      foreach($mustHas as $val){
        if( !isset( $data[$val] ) ){ $this->denied('Not Enough Param.'); }
      }
      
      $this->SC = new Model\SessionCenter();
      
      switch($other_condition){
        case self::IS_ADMIN:
          if(!$this->SC->isAdmin()){ $this->denied('You Are Not Admin.'); }
        break;
        case self::IS_CEO:
          if(!$this->SC->isCEO()){ $this->denied('You Are Not CEO.'); }
        break;
      }
      
      $this->postData = $data;
      
      return $this;
    }
    
    public function setMsg($str){
      $this->jsonFomat->setMsg($str);
      return $this;
    }
    
    public function setArray($input){
      if( isset($input->sqlError) ){ $this->sqlError($input->sqlError); }
      if( isset($input['error']) ){ $this->denied($input['error']); }
      try{
        $this->jsonFomat->setArray($input);
      }catch(Exception $e){
        return false;
      }
      return true;
    }
    
    public function result($in=null,$wait=false){
      if(is_null($in)){ $in=$this->getArray(); }else{
        if( !is_array($in) && is_object($in)){
          $in = $in->data;
        }
        if(!$this->setArray($in)){
          $this->denied('Somethings Wrong.');
        }
      } 
      
      if($wait){
        return $this;
      }else{
        if(IS_DEBUG_MODE==0 && $this->cache){
          header('Cache-Control: max-age='.self::CACHE_TIME.'');
          header_remove('Pragma');
        }
        print $this->getJSON();exit;
      }
    }
    
    public function getJSON(){
      return $this->jsonFomat->getResult();
    }
    
    public function getArray(){
      return $this->jsonFomat->getArray();
    }
    
    public function checkPost($map,$post=Array()){
      if(count($post)==0){$post = $this->postData;}
      foreach($map as $val){
        if(!(is_string($val) && isset($post[$val]))){
          $this->jsonFomat->setStatus(JsonFomat::$ERROR_PARAM);
          return false;
        }
      }
      return true;
    }
    
    public function post($key,$type='value'){
      $loc = false;
      if( isset($this->postData[$key]) ){
        $loc = $this->postData[$key];
        switch($type){
          case "Array":case "array": $loc = explode(',',preg_replace('/[^(\w\,)]+/','',$loc)); break;
          case "Int":case "int": $loc = (int) preg_replace('/[^\d]+/','',$loc); break;
        }
      }
      return $loc;
    }
    
    public function getPost($ary=null){
      $data = $this->postData;
      if($ary && is_array($ary)){
        foreach($data as $k => &$v){
          if(!in_array($k,$ary)){
            unset($data[$k]);
          }
        }
      }
      return $data;
    }

    public function getIp() {
      if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
      } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
      } else {
        $ip = $_SERVER['REMOTE_ADDR'];
      }
      return $ip;
    }
    
    public function denied($res=null){
      
      if (is_a($res, 'Exception')) {
        $res = [
                 'code' => $res->getCode(),
                 'file' => $res->getFile(),
                 'line' => $res->getLine(),
                'message' => $res->getMessage(),
               ];
        $denied = JsonFomat::$DENIED;      
        $this->jsonFomat->setArray($res);  
        $this->jsonFomat->setMsg($res['message']);
        $this->jsonFomat->setStatus([$denied[0], $res['message']]);
      } else {
        if($res){$this->jsonFomat->setMsg($res);}
        $denied = JsonFomat::$DENIED;
        //用這個，會把上面的setMsg 覆蓋，導致回傳的訊息都是固定
        //$this->jsonFomat->setStatus(JsonFomat::$DENIED);
        $this->jsonFomat->setStatus([$denied[0], $res]);
      }
      print $this->getJSON();exit;
    }
    
    public function sqlError($res=null){
      $this->jsonFomat->setStatus(JsonFomat::$ERROR_SQL);
      if($res){$this->jsonFomat->setMsg($res);}
      print $this->getJSON();exit;
    }
    
    public function inputWrong(){
      $this->jsonFomat->setStatus(JsonFomat::$INPUT_WRONG);
    }
    
    public function inputReject(){
      $this->jsonFomat->setStatus(JsonFomat::$INPUT_REJECT);
    }
    
    
    public function isPast($d){
      $date = strtotime($d);
      $now = strtotime('today');
      return $now >= $date;
    }
    
    public function isFuture($d){
      $date = strtotime($d);
      $now = strtotime('today');
      return $now < $date;
    }
    
    public function isToday($d){
      $today = date('Y-m-d');
      return strtotime($d)==strtotime($today);
    }
    
    public function isAdmin(){
      if(!$this->SC->isAdmin()){$this->denied('You Have Not Promised.');} return true;
    }
    
    public function isLeader(){
      if(!$this->SC->isLeader()){$this->denied('You Have Not Promised.');} return true;
    }
    
    public function isLogin(){
      if(!$this->SC->isLogin()){$this->denied('You Have Not Promised.');} return true;
    }
    
    public function condition($ary){
      $tmp = array();
      foreach($ary as $k => $v){
        if( !empty($v) ){ $tmp[$k] = $v; }
      }
      return $tmp;
    }
    
    public function parseDate($b){
      if(is_a($b,'DateTime')){
        $date = $d;
      }else{
        $date = new DateTime($b);
      }
      return $date;
    }
    
    public function getFiles(){
      $i = 0;
      $files = array();
      foreach ($_FILES as $file) {
          if (is_string($file['name'])) {
              $files[$i] = $file;
              $i++;
          }else if (is_array($file['name'])) {
              //暫無
          }
      }
      return $files;
    }
    
    public function uploadFile($fileInfo, $allowExt = array('xlsx', 'xls'), $maxSize = 2097152, $flag = false, $uploadPath = 'rv_uploads'){
      // 存放錯誤訊息
      $res = array();
      // 取得上傳檔案的擴展名
      $ext = pathinfo($fileInfo['name'], PATHINFO_EXTENSION); 

      // 確保檔案名稱唯一，防止重覆名稱產生覆蓋
      $uniName = md5(uniqid(microtime(true), true)) . '.' . $ext;
      $destination = $uploadPath . '/' . $uniName;
      
      // 判斷是否有錯誤
      if ($fileInfo['error'] > 0) {
          // 匹配的錯誤代碼
          switch ($fileInfo['error']) {
              case 1:
                  $res['mes'] = $fileInfo['name'] . ' 上傳的檔案超過了 php.ini 中 upload_max_filesize 允許上傳檔案容量的最大值';
                  break;
              case 2:
                  $res['mes'] = $fileInfo['name'] . ' 上傳檔案的大小超過了 HTML 表單中 MAX_FILE_SIZE 選項指定的值';
                  break;
              case 3:
                  $res['mes'] = $fileInfo['name'] . ' 檔案只有部分被上傳';
                  break;
              case 4:
                  $res['mes'] = $fileInfo['name'] . ' 沒有檔案被上傳（沒有選擇上傳檔案就送出表單）';
                  break;
              case 6:
                  $res['mes'] = $fileInfo['name'] . ' 找不到臨時目錄';
                  break;
              case 7:
                  $res['mes'] = $fileInfo['name'] . ' 檔案寫入失敗';
                  break;
              case 8:
                  $res['mes'] = $fileInfo['name'] . ' 上傳的文件被 PHP 擴展程式中斷';
                  break;
          }

          // 直接 return 無需在往下執行
          return $res;
      }

      // 檢查檔案是否是通過 HTTP POST 上傳的
      if (!is_uploaded_file($fileInfo['tmp_name']))
          $res['mes'] = $fileInfo['name'] . ' 檔案不是通過 HTTP POST 方式上傳的';
      
      // 檢查上傳檔案是否為允許的擴展名
      if (!is_array($allowExt))  // 判斷參數是否為陣列
          $res['mes'] = $fileInfo['name'] . ' 檔案類型型態必須為 array';
      else {
          if (!in_array($ext, $allowExt))  // 檢查陣列中是否有允許的擴展名
              $res['mes'] = $fileInfo['name'] . ' 非法檔案類型';
      }

      // 檢查上傳檔案的容量大小是否符合規範
      if ($fileInfo['size'] > $maxSize)
          $res['mes'] = $fileInfo['name'] . ' 上傳檔案容量超過限制';

      // 檢查是否為真實的圖片類型
      if ($flag && !@getimagesize($fileInfo['tmp_name']))
          $res['mes'] = $fileInfo['name'] . ' 不是真正的圖片類型';

      // array 有值表示上述其中一項檢查有誤，直接 return 無需在往下執行
      if (!empty($res))
          return $res;
      else {
          // 檢查指定目錄是否存在，不存在就建立目錄
          if (!file_exists($uploadPath))
              mkdir($uploadPath, 0777, true);
          
          // 將檔案從臨時目錄移至指定目錄
          if (!@move_uploaded_file($fileInfo['tmp_name'], $destination))  // 如果移動檔案失敗
              $res['mes'] = $fileInfo['name'] . ' 檔案移動失敗';


          $res['mes'] = '檔案已上傳';
          $res['dest'] = $destination;

          return $res;
      }
    }
    
  }
  
?>

