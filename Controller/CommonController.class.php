<?php
include_once(BASE_PATH."/Model/DatabaseCenter.php");
use \Model\DatabaseCenter;

include_once(BASE_PATH."/Model/SessionCenter.php");
use \Model\SessionCenter;

class CommonController {
  
  const CACHE_TIME = 300;
  
  protected $DB;
  
  protected $SC;
  
  protected $value;
  
  protected $isFrame;
  protected $fn;
  protected $site;
  protected $param;
  
  protected $file_ext = array('html','php','htm','tmp');
  
  protected function __construct($fn,$site,$temp){
    
    $this->DB = new DatabaseCenter();
    
    $this->SC = new SessionCenter();
    
    $this->isFrame = $fn=='Frame';
    
    $this->fn = $fn;
    $this->site = $site;
    $this->temp = $temp;
    
    $this->value = array();
    
  }
  
  protected function fetchConfig($in){
    if(is_file($in)){
      return include $in;
    }else{
      return false;
    }
  }
  
  protected function display($site=null,$temp=null){
    if($site){$this->site = $site;}
    if($temp){$this->temp = $temp;}
    
    $fun = $this->fn;
    if(method_exists($this,$fun)){
      header('Content-Type: text/html; charset=utf-8');
      $this->$fun();
    }else{
      r404();
    }    
    
  }
  
  protected function getContent($path){
    if(empty($path)){r404();return null;};
    
    try{
      ob_start();
      include($path);
      $template = ob_get_clean();
    }catch(PDOException $e){
      $template = $e;
    }
    return $template;
  }
  
  protected function assign($key,&$value){
    $this->value[$key] = $value;
  }
  
  private function getViewPath($path){
    if(empty($path)){
      $path = $this->default_path;
    }
    $i = 0;
    $real=null;
    do{
      $file_path = RP("/View/$path.".$this->file_ext[$i]);
      // var_dump($file_path);
      if(file_exists($file_path)){$real=&$file_path;break;}
      $i++;
    }while($i < count($this->file_ext));
    return $real;
  }
  
  private function refreshContent($cnt){
    return preg_replace_callback('/\{\{([\w]+)\}\}/',function($mat){
      $key = $mat[1];
      if( isset($this->value[$key]) ){$value = $this->value[$key];}else{$value=$mat[0];}
      return $value;
    },$cnt);
  }
  
  public function get_frame_path(){
    return '_Frame_/'.$this->site;
  }
  public function get_template_path(){
    return 'Template/'.$this->temp;
  }
  
  public function Frame($path=null){
    if($path){$this->site = $path;}
    $frame = $this->getViewPath($this->get_frame_path());
    
    $content = $this->getContent($frame);
    
    $temp = $this->getViewPath($this->get_template_path());
    $content_sub = $this->getContent($temp);
    
    $this->assign('Template',$content_sub);
    
    echo $this->refreshContent( $content );
  }
  
  public function Template($path=null){
    if($path){$this->temp = $path;}
    $temp = $this->getViewPath($this->get_template_path());
    //可能 cache 到登入
    // if(IS_DEBUG_MODE==0){
      // header('Cache-Control: max-age='.self::CACHE_TIME.'');
      // header_remove('Pragma');
    // }
    // header('Cache-Control: max-age=60');
    $content_sub = $this->getContent($temp);
    
    echo $content_sub;
  }
  
}

?>
