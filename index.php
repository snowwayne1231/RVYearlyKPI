<?php
  ob_start();
  include __DIR__."/autoload.php";
  include __DIR__."/Global.php";

  // var_dump($_SERVER);

  //var_dump($_ROT);exit;

  $controller_name = ucfirst($_ROT[0]).'Controller';
  $controller_path = RP('Controller/'.$controller_name.'.class.php');

  // var_dump($controller_path);
  // var_dump(WEB_ROOT);
  ob_start();

  if(file_exists($controller_path)){
    include $controller_path;
    $cnt = new $controller_name($_ROT);
  }else{
    r404();
  }
  
  $content = ob_get_contents();
  $length = strlen($content);
  header('Content-Length: '.$length);


?>


