<?php
namespace Model\Business\Common;
class CacheFactory  {
  protected $instance ;
  /**
   * 建立instance，並回傳
   * @method     make
   * @author Alex Lin <alex.lin@rv88.tw>
   * @version    [version]
   * @modifyDate 2017-08-23T15:23:28+0800
   * @param      string                   $type 快取類型
   * @return     [type]                         [description]
   */
  public function make($type = 'file') {
    $cacheObject = 'Model\\Business\\Common\\'.$type.'Cache';
    if (!class_exists($cacheObject)) {
      throw new \Exception("Not Found This Type Cache", 1);
    }
    if (!$this->instance) {
      $this->instance = new $cacheObject;
    }
    return $this->instance;
  }
}