<?php
namespace Model;

use \DateTime;
use \Exception;

abstract class PropertyObject
{
  public function __get($name)
  {
    if (method_exists($this, ($method = 'get_'.$name)))
    {
      return $this->$method();
    }
    else return;
  }
  
  public function __isset($name)
  {
    if (method_exists($this, ($method = 'isset_'.$name)))
    {
      return $this->$method();
    }
    else return;
  }
  
  public function __set($name, $value)
  {
    if (method_exists($this, ($method = 'set_'.$name)))
    {
      $this->$method($value);
    }
  }
  
  public function __unset($name)
  {
    if (method_exists($this, ($method = 'unset_'.$name)))
    {
      $this->$method();
    }
  }
  
  public function DateTime($a,$time=false){
    $date = new DateTime();
    if(isset($a)){
      if(is_a($a,'DateTime')){
        return $a;
      }else{
        $ary = preg_split('/[\-\_\/\\\\]/',$a);
        switch(count($ary)){
          case 1:
            if(strlen($ary[0])==4){
              $year = $ary[0];
              $month = $date->format("m");
            }else{
              $year = $date->format("Y");
              $month = str_pad($ary[0],2,"0",STR_PAD_LEFT);
            }
            $day = $date->format("d"); 
            
          break;
          case 2:
            $year = str_pad($ary[0],4,"0",STR_PAD_LEFT);
            $month = str_pad($ary[1],2,"0",STR_PAD_LEFT);
            $day = $date->format("d"); 
          break;
          case 3: default:
            $year = str_pad($ary[0],4,"0",STR_PAD_LEFT);
            $month = str_pad($ary[1],2,"0",STR_PAD_LEFT);
            $day = str_pad($ary[2],2,"0",STR_PAD_LEFT);
        }
        $date = new DateTime("$year-$month-$day");
      }
    }else{
      // return new DateTime();
    }
    if($time){ $date = strtotime($date->format('Y/m/d s:i:H')); }
    return $date;
  }
}

?>