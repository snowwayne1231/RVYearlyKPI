<?php
namespace Model\Business\Multiple;

include_once __DIR__.'/_MultipleSets.php';

use \Exception;
use \Model\Business\Staff;
use \Model\Business\ConfigCyclical;
use \Model\Business\Department;
use \Model\Business\DepartmentLeadership;

/*
主管相關權限判斷
*/
class Leadership extends MultipleSets{
  
  protected $staff;
  protected $department;
  protected $departmentLeadership;
  protected $configCyc;
  protected $self_data;
  
  public function __construct($self_staff_id){
    $this->staff = new Staff();
    $this->department = new Department();
    $this->departmentLeadership = new DepartmentLeadership();
    $this->configCyc = new ConfigCyclical();
    $_data = $this->staff->select($self_staff_id);
    if (count($_data) == 1) {
      $this->self_data = $_data[0];
    } else {
      $this->self_data = null;
    }
  }
  
  public function isMyUnderStaff($target_staff_id) {
    $result = false;
    $self_data = $this->self_data;

    if ($self_data['is_leader'] == 0) {
      return $result;
    }

    $target_staff_data = $this->staff->select(['department_id', 'is_leader'], $target_staff_id);
    if (count($target_staff_data) != 1) {
      return $result;
    }

    $target_staff_data = $target_staff_data[0];
    if ($target_staff_data['department_id'] == $self_data['department_id']) {

      $result = $target_staff_data['is_leader'] == 0;

    } else {
      
      $lower_department_ids = $this->department->getLowerIdArray($self_data['department_id']);

      $result = in_array($target_staff_data['department_id'], $lower_department_ids);

    }

    return $result;
  }
  
}
?>
