<?php
include(__DIR__."/CommonController.class.php");

class NoneController extends CommonController{
  
  public function __construct($p){
    
    $function = $p[1];
    $site = $p[0];
    $template = $p[2];
    
    parent::__construct($function, $site, $template);
    
    
    if(empty($this->SC->get())){
      //未登入
      $this->display('None','login');
      // $this->display();
    }else{
      //有登入
      $this->display();
    }
    
  }
  
}

?>
