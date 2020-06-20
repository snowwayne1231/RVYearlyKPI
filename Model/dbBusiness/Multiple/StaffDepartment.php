<?php
namespace Model\Business\Multiple;

include_once __DIR__.'/_MultipleSets.php';
include_once __DIR__.'/../Staff.php';
include_once __DIR__.'/../Department.php';

use \Exception;

/*
用員工為基底 組合 部門
*/
class StaffDepartment extends MultipleSets{

  protected $staff;

  protected $team;

  public function __construct(){
    $this->staff = new \Model\Business\Staff();
    $this->team = new \Model\Business\Department();
    // $this->data = $this->team->select();
  }

  //組合單位到該員工
  public function collect($order=false){
    if($order){
      $team_table = $this->team->table_name;
      $this->data = $this->staff->sql("select a.* , b.name as unit_name, b.unit_id, b.upper_id, b.manager_staff_id, b.supervisor_staff_id  from {table} as a left join $team_table as b on a.department_id = b.id order by b.unit_id asc, a.rank desc, a.staff_no asc")->data;
    }else{
      $team_map = $this->team->select(array('id','name as unit_name','unit_id','upper_id','manager_staff_id','supervisor_staff_id'),null);
      $staff_map = $this->staff->map();
      $this->data = $this->staff->join(array('department_id'=>'id'),$team_map);
    }
    return $this->data;
  }

  public function staffFullData($id){
    $staff_table = $this->staff->table_name;
    $team_table = $this->team->table_name;

    $sql = "select a.*, b.name as unit_name, b.unit_id, b.upper_id, b.manager_staff_id, b.supervisor_staff_id from $staff_table as a left join $team_table as b
    on a.department_id = b.id
    where a.id = $id ";

    $this->data = $this->staff->DB->doSQL($sql);
    return $this->data;
  }

  public function getStaffCounts($id){
    $c = 0;
    $map = $this->staff->map('department_id');
    if( isset($map[$id]['id']) ){
      $c = 1;
    }else if( isset($map[$id]) ){
      $c = count($map[$id]);
    }
    return $c;
  }


  protected function get_team(){
    return $this->team;
  }

  protected function get_staff(){
    return $this->staff;
  }

}
?>
