<?php
namespace Model\Business\Multiple;

include_once __DIR__.'/_MultipleSets.php';
include_once __DIR__.'/../Staff.php';
include_once __DIR__.'/../RecordPersonalComment.php';
include_once __DIR__.'/../RecordPersonalCommentChanged.php';
include_once __DIR__.'/../MonthlyReport.php';
include_once __DIR__.'/../MonthlyProcessing.php';
include_once __DIR__.'/../MonthlyReportLeader.php';
include_once __DIR__.'/../ConfigCyclical.php';

use \Exception;

/*
評論控制
*/
class CommentCenter extends MultipleSets{
  
  protected $staff;
  
  protected $comment;
  protected $comment_changed;
  
  protected $general;
  protected $process;
  
  protected $leader;
  
  protected $config;

  protected $processEvaluating;
  
  public function __construct(){
    $this->staff = new \Model\Business\Staff();
    $this->comment = new \Model\Business\RecordPersonalComment();
    $this->comment_changed = new \Model\Business\RecordPersonalCommentChanged();
    $this->general = new \Model\Business\MonthlyReport();
    $this->leader = new \Model\Business\MonthlyReportLeader();
    $this->process = new \Model\Business\MonthlyProcessing();
    $this->config = new \Model\Business\ConfigCyclical();
    $this->processEvaluating = new \Model\Business\MonthlyProcessingEvaluating();
  }
  
  public function addComment($ary){
    
    $report = $this->whichReport($ary,true);
    if(!$report){return false;}
    // $date = $this->config->getNowConfig();
    $report_data = $report->data[0];
    if($report_data['releaseFlag']=='Y'){return false;}
    // if( !$this->checkRank($ary['self_id'],$ary['target_staff_id']) ){return false;}
    
    if( !$this->checkDo( $ary['self_id'], $report_data['processing_id'] ) ){return false;};
    
    $cid = $this->comment->create(array(
      'create_staff_id' => $ary['self_id'],
      'target_staff_id' => $ary['target_staff_id'],
      'report_id' => $ary['report_id'],
      'report_type' => $ary['report_type'],
      'content' => $ary['content']
    ));
    
    $report->sql("update {table} set comment_id = concat(comment_id,',$cid') where id=".$ary['report_id']);
    
    return true;
    
  }
  
  private function checkDo($self_id,$pid){
    // $pdata = $this->process->select(array('owner_staff_id','path_staff_id'),$pid);
    $pdata = $this->processEvaluating->select(['id'], ['processing_id'=> $pid, 'staff_id'=>$self_id]);
    //不是對應的單子
    if( count($pdata)==0 ){return false;}
    // $path = $pdata[0]['path_staff_id'];
    // $self_position = array_search( $self_id , $path);
    // $owner_position = array_search( $pdata[0]['owner_staff_id'] , $path);
    // var_dump($self_id);
    // LG($path);
    // LG($self_position);
    // 不是在路程的主管中
    // if($self_position===false || $self_position < 0){ return false; }
    // 送出去了
    // if($owner_position > $self_position){ return false; }
    return true;
  }
  
  private function checkRank($self,$target){
    $data = $this->staff->sql("select id,rank from {table} where id in ($self,$target)")->map();
    if(count($data)<2){return false;}
    if($data[$self]['rank'] <= $data[$target]['rank']){return false;}
    return true;
  }
  
  public function getComment($ary){
    
    $report = $this->whichReport($ary);
    if(!$report){return false;}
    //$report->read($ary['report_id']);
    if(count($report->data)==0){ return false;}
    
    // $data = $this->comment->select(array('report_id'=>$ary['report_id']));
    $staff_table = $this->staff->table_name;
    $data = $this->comment->sql("select a.* , 
    b.name as _created_staff_name , b.name_en as _created_staff_name_en , b.rank as _created_staff_rank , 
    c.name as _target_staff_name , c.name_en as _target_staff_name_en 
    from {table} as a 
    left join $staff_table as b on a.create_staff_id = b.id 
    left join $staff_table as c on a.target_staff_id = c.id 
    where a.report_id = ".$ary['report_id']." and a.status = 1  
    and a.report_type = ".$ary['report_type'])->data;
    
    $rf = $report->data[0]['releaseFlag']!='Y';
    $cd = $this->checkDo( $ary['self_id'], $report->data[0]['processing_id']);
    
    foreach($data as &$v){
      if($rf && $v['create_staff_id']==$ary['self_id'] && $cd){
        $v['_authority'] = true;
      }else{
        $v['_authority'] = false;
      }
    }
    // LG($data);
    return array( 'comments'=>$data, '_authority'=> $rf && $cd );
  }
  
  public function deleteComment($id,$operating){
    //沒這留言
    $comment = $this->comment->select($id);
    if( count($comment)==0 ){return false;}
    $comment = $comment[0];
    
    if($comment['report_type']==1){
      $report = $this->leader;
    }else{
      $report = $this->general;
    }
    
    $report_id = $comment['report_id'];
    $pid = $report->select(array('processing_id'),$report_id);
    //沒這考評表
    if( count($pid)==0){return false;}
    //考評表交出去了
    if( !$this->checkDo( $operating, $pid[0]['processing_id'] ) ){return false;};
    
    $res = $this->comment->update(array('status'=>0), array('id'=>$id,'create_staff_id'=>$operating));
    if($res){ 
    
      $report_id = $comment['report_id'];
      $comments_id = $this->comment->select(array('id'),array('report_id'=>$report_id,'report_type'=>$comment['report_type'],'status'=>1));
      $comment_update_str=',';
      if(count($comments_id)!=0){
        foreach($comments_id as $val){
          $comment_update_str.=$val['id'].',';
        }
      }
      $report->sql("update {table} set comment_id = '$comment_update_str' where id = $report_id");
    }
    return $res;
  }
  
  public function updateComment($id,$operating,$content){
    //沒這留言
    $comment = $this->comment->select($id);
    if( count($comment)==0 ){return false;}
    $comment = $comment[0];
    
    if($comment['report_type']==1){
      $report = $this->leader;
    }else{
      $report = $this->general;
    }
    
    $report_id = $comment['report_id'];
    $pid = $report->select(array('processing_id'),$report_id);
    //沒這考評表
    if( count($pid)==0){return false;}
    //考評表交出去了
    if( !$this->checkDo( $operating, $pid[0]['processing_id'] ) ){return false;};
    
    $upd = $this->comment->update(array('content'=>$content,'create_time'=>date('Y-m-d H:i:s')),array('id'=>$id,'status'=>1,'create_staff_id'=>$operating));
    if($upd){
      $comment_table = $this->comment->table_name;
      $this->comment_changed->sql(" insert into {table} (comment_id,create_staff_id,target_staff_id,content) 
      select id,create_staff_id,target_staff_id,content from $comment_table where id = $id");
    }
    return $upd;
  }
  
  private function whichReport(&$w,$release=false){
    $col = array('id','staff_id','comment_id','processing_id','releaseFlag');
    if($w['mode']==1){
      
      $condi = array('staff_id'=>$w['target_staff_id'], 'year'=>$w['year'], 'month'=>$w['month'] );
      
      $report = $this->leader->read( $col, $condi );
      if(count($report->data)==0){
        $w['report_type'] = 2;
        $report = $this->general->read( $col, $condi );
      }else{
        $w['report_type'] = 1;
      }
      if(count($report->data)==0){return false;}
      
      $w['report_id'] = $report->data[0]['id'];
      
    }else{
      if($w['report_type']==1){
        $report = $this->leader;
      }else{
        $report = $this->general;
      }
      $condi = array('id'=>$w['report_id']);
      if($release){$condi['releaseFlag']='N';}
      $report->read( $col ,$condi);
      if(count($report->data)==0){return false;}
      $w['target_staff_id'] = $report->data[0]['staff_id'];
    }
    return $report;
  }
  
  
  protected function get_comment(){
    return $this->comment;
  }
  protected function get_staff(){
    return $this->staff;
  }
  
}
?>
