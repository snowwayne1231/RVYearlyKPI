<?php
namespace Model\Business\Common;

interface CacheContract {
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
  public function set($key, $value, $life_time);
  /**
   * 取得 指定的快取
   * @method     get
   * @author Alex Lin <alex.lin@rv88.tw>
   * @version    [version]
   * @modifyDate 2017-08-23T14:14:52+0800
   * @param      [type]                   $key [description]
   * @return     [type]                        [description]
   */
  public function get($key);
  /**
   * 刪除指定的快取
   * @method     delete
   * @author Alex Lin <alex.lin@rv88.tw>
   * @version    [version]
   * @modifyDate 2017-08-23T14:15:25+0800
   * @param      [type]                   $key [description]
   * @return     [type]                        [description]
   */
  public function delete($key);
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
  public function expire($key, $life_time);

}