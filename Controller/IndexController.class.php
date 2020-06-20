<?php
include(__DIR__."/CommonController.class.php");

class IndexController extends CommonController{
  
  public function __construct($p){
    
    $function = $p[1];
    $site = $p[0];
    $template = $p[2];
    
    parent::__construct($function, $site, $template);
    
    
    if($this->SC->isLogin()){
      //有登入
      $this->display();
    }else{
      //未登入
      $this->display('None','login');
    }
    
  }
  
}

?>
