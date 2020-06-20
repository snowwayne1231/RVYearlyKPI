<?php
namespace Model\Business\Observer;

include_once __DIR__.'/../Common/DBPropertyObject.php';
use Model\Business\DBPropertyObject;
interface SplObserver{  
    public function added(DBPropertyObject $model);
    public function saved(DBPropertyObject $model);
    public function deleted(DBPropertyObject $model);
    /**
     * 修改前
     */
    public function adding(DBPropertyObject $model);
    public function saving(DBPropertyObject $model);
    /**
     * 儲存前
     */
    public function deleting(DBPropertyObject $model);
} 
?>