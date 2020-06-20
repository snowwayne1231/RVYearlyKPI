<?php
namespace Model\Business;

trait BaseReport{

  protected $id_array_cache = array();

  //override
  public function select($a=null,$b=0,$c=null){
    parent::select($a,$b,$c);
    foreach($this->data as &$val){
      if(isset($val['comment_id'])){
        $val['comment_id'] = array_unique(bomb($val['comment_id']));
        $val['_comment_count'] = count($val['comment_id']);
      }
    }
    return $this->data;
  }
  //override
  public function read($a=null,$b=0,$c=null){
    parent::read($a,$b,$c);
    foreach($this->data as &$val){
      if(isset($val['comment_id'])){
        $val['comment_id'] = bomb($val['comment_id']);
        $val['_comment_count'] = count($val['comment_id']);
      }
    }
    return $this;
  }

  public function checkExist($id,$year,$month){
    if( isset($this->id_array_cache[$id]) ){return true;}
    if(!isset($this->data)){
      $this->read( array("year"=>$year, "month"=>$month) );
    }

    $map = $this->map('staff_id');

    $this->id_array_cache[$id] = true;

    return isset($map[$id]);
  }

  public function clearExistCache($id){
    $this->id_array_cache[$id] = null;
  }

}
?>