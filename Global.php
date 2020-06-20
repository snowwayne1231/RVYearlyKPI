<?php
$ini = parse_ini_file(__DIR__.'/Config/main.ini');
define("VERSION",$ini['version']);
if(isset($_REQUEST['DEBUG'])){define("IS_DEBUG_MODE",1);}else{define("IS_DEBUG_MODE",$ini['IS_DEBUG_MODE']);}

if(IS_DEBUG_MODE){
  ini_set('error_reporting', E_ALL | E_STRICT);
  ini_set('display_errors', 1);
}
// LG( dirname(__FILE__) );
define('BASE_PATH', str_replace("\\","/",dirname(__FILE__)));

define('WEB_ROOT',  preg_replace( '/[\/\\\\]+$/','', preg_replace("/^[\/\\\\]+/" , "/" ,"/".str_replace(str_replace("\\","/",$_SERVER["DOCUMENT_ROOT"]),"",BASE_PATH.'/')) )  );

define('REQUEST_ROOT', dirname($_SERVER['SCRIPT_NAME'])) ;

// var_dump(BASE_PATH);
// LG(WEB_ROOT);
$_ROT = parseURI();



function U($uri){
  if(!preg_match("/^[\/]/i",$uri)){
    $file_path = debug_backtrace()[0]['file'];
    $file_path = dirname(str_replace('\\','/',$file_path));
    $rep = str_replace(BASE_PATH,'',$file_path);
    
    // $path = dirname($_SERVER['REQUEST_URI'])."/".$uri;
    $path = $rep.'/'.$uri;
  }else{
    $path =  WEB_ROOT.$uri;
    
  }
  if(IS_DEBUG_MODE){
    return $path .="?v=".date("h:i:sa");
  }else{
    return $path .="?v=".VERSION;
  }
  
}

function RP($uri){
  if(!preg_match('/^[\/\\\\]/',$uri)){
    $t = debug_backtrace();
    $f = $t[0]['file'];
    return preg_replace('/[\/\\\\]{1}[\w]*.php$/i','/',$f).$uri;
  }else{
    return BASE_PATH.$uri;
  }
}

function V($path){
  // $content = file_get_contents(RP("/View/$path"));
  $path = preg_replace('/^[\/\\\\]{1}/','',$path);
  $content = include(RP("/View/$path"));
  // var_dump($content);
  // return $content;
}

function parseURI(){
  $loc = str_replace(REQUEST_ROOT,'',$_SERVER['REQUEST_URI']);
  $loc = preg_replace('/\?.*/i','',$loc);
  $loc = bomb('/',$loc);
  $cout = count($loc);
  switch($cout){
    case 0: array_unshift($loc,'index');
    case 1: array_unshift($loc,'Frame');
    case 2: array_unshift($loc,'Index');
    case 3: break;
    default: array_splice($loc,3,$cout-3);
  }
  return $loc;
}

function bomb($key,$str=null){
  if(empty($str)){$str=$key;$key=',';}
  $loc = explode($key,$str);
  $i = 0;
  while($i < count($loc)){
    if( empty($loc[$i]) ){
      array_splice($loc,$i,1);
    }else{
      $i++;
    }
  }
  return $loc;
}

function r404(){
  global $_ROT;
  if(IS_DEBUG_MODE){var_dump($_ROT);}else{
    echo '404 Not Found.';
  }
  header("HTTP/1.0 404 Not Found");
  exit;
}

function LG($var){
  if(IS_DEBUG_MODE){
    var_dump($var);exit;
  }
}

function getIP(){
  if(!empty($_SERVER['HTTP_CLIENT_IP'])){
      $ip = $_SERVER['HTTP_CLIENT_IP'];
  }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
      $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
  }else{
      $ip = $_SERVER['REMOTE_ADDR'];
  }
  if(empty($ip)){$ip='Undefined';}
  return $ip;
}

function ErrorLog($str){
  stamp_log( $str, null, RP('/error_log') );
}

$time_start;stamp();
function stamp_log($str, $pre_time=null, $path=0){
  $log = (empty($log))? new \Logging() : $log;
  if($path===0){$path= '/php_log';}
  if(empty($pre_time)){
    $pre_time = $GLOBALS['time_start'];
  }
  $time_end = microtime(true);
  $spend_time = $time_end - $pre_time;
  $spend_string = "- Spend Time : ( $spend_time )\n";
  $log->lfile( $path );
  $log->lwrite("\n----------------------------------- START -----------------------------------\n ".$str."\n\r ".$spend_string."-----------------------------------  END  -----------------------------------\n");
  $log->lclose();
  if( IS_DEBUG_MODE ){echo "<br>$str <br> Spend Time = $spend_time<br>";}
}
function stamp(){
  global $time_start;
  $time_start = microtime(true);
}
function ms(){
  return (int) (microtime(true)*1000);
}
function strlen2($str){
  return mb_strlen($str,"utf-8");
}

/**
 * 列印變數
 * @param      [type]                   $var  [description]
 * @param      boolean                  $exit 是否強制中斷程式執行
 * @return     [type]                         [description]
 */
function dd($var, $exit = true) {
  $type = gettype($var);
  switch($type){
    case 'array': header('Content-type: text/javascript'); print json_encode($var, JSON_PRETTY_PRINT); break;
    default: print_r($var);
  }
  if ($exit) { exit; }
}

function writeLog($path, $message)
{
  $log = new \Logging() ;
  $log->lfile( $path);
  $log->lwrite($message);
  $log->lclose();
}
/**
 * 取得 過濾指定的欄位 不含某個數值 的陣列
 * @method     dictionaryFilterList
 * @author Alex Lin <alex.lin@rv88.tw>
 * @version    [version]
 * @modifyDate 2017-08-10T11:28:50+0800
 * @param      array                    $filter 要過濾的數值
 * @param      array                    $source   來源
 * @param      string                   $column 
 * @return     [type]                           [description]
 */
function dictionaryFilterList(array $filter, array $source, string $column) 
{
    $new = array_column($source, $column);
    $keep = array_diff($new, $filter);
    return array_intersect_key($data, $keep);
}

/**
 * 刪除陣列裡指定的欄位
 * @method     removeColumnFromArray
 * @author Alex Lin <alex.lin@rv88.tw>
 * @version    [version]
 * @modifyDate 2017-08-14T10:24:31+0800
 * @param      array                    $removeColumn [description]
 * @param      array                    $source       [description]
 * @return     [type]                                 [description]
 */
function removeColumnFromArray(array $source, array $removeColumn) {
  if ($removeColumn) {
    foreach ($removeColumn as $key => $value) {
      unset($source[$value]);
    }
  }
  return $source;
}

/**
 * 取得設定檔的內容
 * @method     config
 * @author Alex Lin <alex.lin@rv88.tw>
 * @version    [version]
 * @modifyDate 2017-08-15T10:31:04+0800
 * @param      string                   $key 命名對應實體路徑規則 -> 種類.屬性 , 有多層請用 . 做為區隔
 *                                           例: site.site_host 表示 為 Config\site_config.php 的 site_host 的內容，使用方式和Laravel的 Configure::get() 一樣
 * @param      string                   $default_val 若找不到就回傳預設值
 * @return     [type]                        [description]
 */

function config($key, $default_val = '') {
  $split = explode('.', $key);
  $configure_path = BASE_PATH."/Config/".$split[0]."_config.php";
  if (!file_exists($configure_path)) {
    throw new \Exception("Not Found This Configure File :".$split[0] , 1);
  }
  $conf = include($configure_path);
  array_shift($split); 
  $conf = array_traverse($conf, $split, $default_val);
  return $conf;
}

/**
 * 傳入欲搜尋的階層陣列，取得指定的資料
 * @method     array_traverse
 * @author Alex Lin <alex.lin@rv88.tw>
 * @version    [version]
 * @modifyDate 2017-08-15T10:53:11+0800
 * @param      array                    $source           來源資料
 * @param      array                    $search_ary_level 階層陣列
 * @param      string                   $default_val 找不到的預設值
 * @return     [type]                                     [description]
 */
function array_traverse(array $source, array $search_ary_level, $default_val) {
  $key = array_shift($search_ary_level);
  if (isset($source[$key])) {
    if ((count($search_ary_level) >= 1) && (is_array($source[$key]))) {
      return array_traverse($source[$key], $search_ary_level, $default_val);
    } else {
      return $source[$key];
    }
  } else {
    return $default_val;
  }
}

//用 value sort
function sortByValue (&$ary, $key) {
  global $sbv_key;
  if( !is_array($key) ){
    $key = [$key];
  }
  foreach($key as &$v){
    $s = explode('|',$v);
    $s[0] = trim($s[0]);
    if(empty($s[1])){$s[1]='asc';}
    $v=$s;
  }
  $sbv_key = $key;
  usort($ary, 'sub_sort');
  return $ary;
}
$sbv_key;
function sub_sort($a,$b) {
  global $sbv_key; $change = false;
  foreach($sbv_key as $v){
    $key = $v[0];
    $mode = $v[1];
    if($a[$key] == $b[$key]){continue;}
    if($mode=='asc'){
      $change = $a[$key] > $b[$key];
    }else{
      $change = $a[$key] < $b[$key];
    }
    break;
  }
  return $change;
}


function make($object_name) {
  return new $object_name;
}

class Logging {
    private $log_file, $fp;
    private $file_path;
    public function __construct(){
      $this->file_path = RP('/Log');
      if (!file_exists($this->file_path)){ mkdir($this->file_path, 0777, true); }
    }
    public function lfile($path) { 
        $time = @date('Y_m_d');
        $this->log_file = $this->file_path.$path."_$time.txt";
    }
    public function lwrite($message) {
        if (!is_resource($this->fp)) {
            $this->lopen();
        }
        $script_name = pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME);
        $time = @date('[d/M/Y:H:i:s]');
        fwrite($this->fp, "$time ($script_name) $message" . PHP_EOL);
    }
    public function lclose() {
        fclose($this->fp);
    }
    private function lopen() {
        $lfile = $this->log_file;
        $this->fp = fopen($lfile, 'a') or exit("Can't open $lfile!");
    }
}

?>