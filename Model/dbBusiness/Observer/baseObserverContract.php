<?php
namespace Model\Business\Observer;
use Model\Business\Observer\SplObserver;
use Model\Business\DBPropertyObject;
class baseObserverContract implements SplObserver{
    public function added(DBPropertyObject $model) {
      
    }
    public function saved(DBPropertyObject $model) {
      
    }

    public function deleted(DBPropertyObject $model) {
      
    }
    
    public function adding(DBPropertyObject $model) {

    }

    public function saving(DBPropertyObject $model) {

    }

    public function deleting(DBPropertyObject $model) {

    }
}