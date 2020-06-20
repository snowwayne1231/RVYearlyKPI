<?php
namespace Model\Business\Observer;
use Model\Business\Observer\baseObserverContract;
use Model\Business\DBPropertyObject;
use Model\Business\Staff;
use Model\Business\Multiple\YearlyAssessment;
class YearPerformanceDivisionsObserver extends baseObserverContract{
    public function saved(DBPropertyObject $model) {
      
    }

    public function deleted(DBPropertyObject $model) {
      
    }
}