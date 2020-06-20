<?php
namespace Model\Business\Common;
use Model\Business\Common\CacheContract;
class FileCache implements  CacheContract {
  protected $enable = true;
  public function __construct($enable= true) {
    $this->toggleEnable($enable);
  }
  public function toggleEnable($enable) {
    $this->enable = $enable;
  }
  protected function getKeyPath($key) {
    $key = substr(md5($key), 0 , 10);
    $level_one = substr($key, 0, 2);
    $path = BASE_PATH.'/Log/Cache/'.$level_one;
    @mkdir( $path, 0777, true );
    $path = BASE_PATH.'/Log/Cache/'.$level_one.'/'.$key;
    return $path;
  }
  /**
   * 設定值
   * @method     set
   * @author Alex Lin <alex.lin@rv88.tw>
   * @version    [version]
   * @modifyDate 2017-08-23T14:12:30+0800
   * @param      [string]                   $key   [description]
   * @param      [object|array|string]                   $value [description]
   * @param      [int]                   $life_time 存活時間
   */
  public function set($key, $value, $life_time) {
    if (!$this->enable) {
      return false;
    }
    $life_time = ($life_time) ? $life_time : 86400;  //沒定義，預設一天
    $life_time = time()+$life_time;
    $path = $this->getKeyPath($key);
    $data = serialize($value);
    $write_data = [
                    'data' => serialize($value),
                    'life_time' => $life_time
    ];
    $fp = fopen($path, 'w');
    fwrite($fp, json_encode($write_data));
  }
  /**
   * 取得 指定的快取
   * @method     get
   * @author Alex Lin <alex.lin@rv88.tw>
   * @version    [version]
   * @modifyDate 2017-08-23T14:14:52+0800
   * @param      [type]                   $key [description]
   * @return     [type]                        [description]
   */
  public function get($key) {
    if (!$this->enable) {
      return [];
    }
    $path = $this->getKeyPath($key);
    $data = file_get_contents($path);
    if ($data) {
      $data = json_decode($data, true);
      //檢查是否過期
      $now = time();
      if ($data['life_time'] < $now) {  //過期
        @unlink($path);
        return [];
      } else {
        return unserialize($data['data']);
      }
    } else {
      return [];
    }
  }
  /**
   * 刪除指定的快取
   * @method     delete
   * @author Alex Lin <alex.lin@rv88.tw>
   * @version    [version]
   * @modifyDate 2017-08-23T14:15:25+0800
   * @param      [type]                   $key [description]
   * @return     [type]                        [description]
   */
  public function delete($key) {
    $path = $this->getKeyPath($key);
    @unlink($path);
  }
  /**
   * 設定指定的快取 過期時間，可用來延長快取時間
   * @method     expire
   * @author Alex Lin <alex.lin@rv88.tw>
   * @version    [version]
   * @modifyDate 2017-08-23T14:16:43+0800
   * @param      string                  $key       
   * @param      int                   $life_time 過期時間
   * @return     boolean                true:成功，false:失敗                   
   */
  public function expire($key, $life_time) {
    if (!$this->enable) {
      return false;
    }
    $path = $this->getKeyPath($key);
    $data = file_get_contents($path);
    if ($data) {
      $data = json_decode($data, true);
      //檢查是否過期
      $now = time();
      if ($data['life_time'] > $now) {  //過期
        @unlink($path);
        return false;
      } else {
        $this->set($key, unserialize($data['data']), $life_time);
        return true;
      }
    } else {
      return false;
    }
  }
}