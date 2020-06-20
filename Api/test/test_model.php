<?php

include __DIR__."/../ApiCore.php";

$api = new ApiCore($_REQUEST);

$post = $api->getPost();
$ip = $api->getIp();

if ($ip != '::1' && !preg_match('/172\.16\.[\d]\./', $ip)) {
  return '';
}

if (!$api->checkPost(['model', 'fn', 'params'])) {
  return '';
}

$model_name = $api->post('model');
$model = new $model_name();
$fn = $api->post('fn');
$params = json_decode($api->post('params'), true);

switch (count($params)) {
  case 1:
    $res = $model->$fn($params[0]);
  break;
  case 2:
    $res = $model->$fn($params[0], $params[1]);
  break;
  case 3:
    $res = $model->$fn($params[0], $params[1], $params[2]);
  break;
  default:
    $res = $model->$fn();
}
  
dd($res);

?>