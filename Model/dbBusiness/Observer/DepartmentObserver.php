<?php
namespace Model\Business\Observer;
use Model\Business\Observer\baseObserverContract;
use Model\Business\DBPropertyObject;
class DepartmentObserver extends baseObserverContract{
    /**
     * 修改前
     * @method     saving
     * @author Alex Lin <alex.lin@rv88.tw>
     * @version    [version]
     * @modifyDate 2017-08-23T09:43:08+0800
     * @param      DBPropertyObject         $model [description]
     * @return     [type]                          [description]
     */
    public function saving(DBPropertyObject $model) {

    }
    /**
     * 儲存前
     * @method     deleting
     * @author Alex Lin <alex.lin@rv88.tw>
     * @version    [version]
     * @modifyDate 2017-08-23T09:43:19+0800
     * @param      DBPropertyObject         $model [description]
     * @return     [type]                          [description]
     */
    public function deleting(DBPropertyObject $model) {

    }

    public function saved(DBPropertyObject $model) {

    }
    public function deleted(DBPropertyObject $model) {
      
    }
}