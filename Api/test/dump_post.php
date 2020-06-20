<?php

include __DIR__."/../ApiCore.php";

$api = new ApiCore($_POST);

  $post = $api->getPost();
  
  var_dump($post);

?>