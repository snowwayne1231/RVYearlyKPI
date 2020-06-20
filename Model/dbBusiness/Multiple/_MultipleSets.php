<?php
namespace Model\Business\Multiple;

include_once __DIR__.'/../Common/DBPropertyObject.php';

use Model\Business\DBPropertyObject;

class MultipleSets extends DBPropertyObject
{
  
  public $table_name = "";
  
  public function create($a=null){
    return false;
  }
  
  public function update($a=null,$b=null){
    return false;
  }
  
  public function select($a=null,$b=0,$c=null){
    return $this->data;
  }
  
  public function delete($a=null){
    return false;
  }
  
  public function add($a=null){
    return $this;
  }
  
  public function read($a=null,$b=0,$c=null){
    return $this;
  }
  
}

?>