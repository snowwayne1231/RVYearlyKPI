<?php
namespace Model\Business\Observer;
use Model\Business\Observer\baseObserverContract;
use Model\Business\DBPropertyObject;
use Model\Business\Staff;
use Model\Business\Multiple\YearlyAssessment;
class YearPerformanceReportObserver extends baseObserverContract{
    
    
    public function saved(DBPropertyObject $model) {
      
    }

    public function deleted(DBPropertyObject $model) {
      
    }
    
    public function saving(DBPropertyObject $model) {
      // $this->checkEditAble($model);
    }

    public function deleting(DBPropertyObject $model) {
      // $this->checkEditAble($model);
    }

    protected function checkEditAble($model) {
      
    }
}