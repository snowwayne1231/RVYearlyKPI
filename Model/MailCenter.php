<?php
namespace Model;

include_once BASE_PATH.'/Model/PHPMailer/PHPMailerAutoload.php';
include_once BASE_PATH.'/Model/FakeMailer.php';
include_once BASE_PATH.'/Model/dbBusiness/EmailTemplate.php';
include_once BASE_PATH.'/Model/dbBusiness/Staff.php';
include_once BASE_PATH.'/Model/dbBusiness/MonthlyProcessing.php';

use \Exception;

class MailCenter {
  
  protected $CONF;
  
  protected $Mailer;
  
  protected $Template;
  
  protected $Staff;
  
  protected $Process;
  
  private $log_time;
  
  private $tmp_staff_data;
  
  private $enabled;
  
  public function __construct(){
    
    $this->CONF = include(BASE_PATH."/Config/mail_config.php");
    $this->enabled = ( isset($this->CONF['MAIL_CONFIG']['enabled']) && $this->CONF['MAIL_CONFIG']['enabled']==1 ) ? true : false;
    $this->buildMaillServiceConnection();
    $this->Template = new Business\EmailTemplate();
    $this->Staff = new Business\Staff();
    $this->Process = new Business\MonthlyProcessing();
    $this->log_time = microtime(true);
    // $mail->Encoding = "base64";  
    // $mail->addReplyTo('info@example.com', 'Information');
    // $mail->addBCC('bcc@example.com');
    // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
  }
  
  public function send($subject, $content, $html = true){
    $mail = $this->Mailer;
    $mail->isHTML($html); 
    $mail->Subject = $subject;
    $mail->Body    = $content;
    $mail->AltBody = $content;
    if(!$mail->send()) {
      $this->writeDBLog($mail->ErrorInfo);
       return $mail->ErrorInfo;
    } else {
       return true;
    }
  }
  
  public function sendTemplate($name,$data=array()){
   
    $data['URL'] = isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST'].WEB_ROOT : $this->CONF['SCHEDULE_CONFIG']['web_root'];
    $temp = $this->Template->map('name',true);
    if( empty($temp[$name]) ){
      $this->writeDBLog("No Match Template With Name [ $name ].");
      return false;
    }
    $loc = $temp[$name];
    $title = $this->parseData($loc['title'],$data);
    $text = $this->parseData($loc['text'],$data);
    // echo($text);exit;
    
    return $this->send($title, $text);
  }
  
  private function parseData($str,$data){
    return preg_replace_callback('/\{(.+?)\}/',function($m) use ($data){
      return isset($data[$m[1]])?$data[$m[1]]:$m[0];
    },$str);
  }
  
  public function addAddress($target, $name=''){
    if(is_int($target) || ctype_digit($target)){
      $staff = $this->Staff->select(['email'],$target);
      if( count($staff)>0 ){
        $this->Mailer->addAddress($staff[0]['email']);
      }else{
        $this->writeDBLog('Not Found Staff Id : '.$target);
      }
    }else{
      if (is_array($target)) {
        foreach ($target as $key => $email) {
          $this->addAddress($email, $name);  
        }
      } else {
        $this->Mailer->addAddress($target);  
      }
    }
    // $this->Mailer->addAddress($target, $name);
  }

  public function addAddressByStaffArray($staff_ids) {
    foreach ($staff_ids as $id) {
      $this->addAddress($id);
    }
  }
  
  public function addAddressGroup($type='monthly_process',$data=array()){
    $staff_table = $this->Staff->table_name;
    $process_table = $this->Process->table_name;
    $year = isset($data['year'])?$data['year']:date('Y');
    $month = isset($data['month'])?$data['month']:date('m');
    switch($type){
      case 'monthly_process':
        // $result = $this->Process->sql(" select b.id, b.name , b.name_en, b.email, b.passwd, b.staff_no, b.is_admin, b.is_leader 
        // from {table} as a right join $staff_table as b on a.owner_staff_id = b.id where (a.year = $year and a.month = $month) or (b.is_admin=1 and b.status_id < 4) 
        // group by b.id ")->data;
        $result = $this->Staff->sql(" select a.id, a.name , a.name_en, a.email, a.passwd, a.staff_no, a.is_admin, a.is_leader, a.department_id
        from {table} as a left join $process_table as b on a.department_id = b.owner_department_id where (b.year = $year and b.month = $month and a.is_leader=1) or (a.is_admin=1 and a.status_id < 4) 
        group by a.id order by a.department_id ")->data;
        break;
      default:$result = array();
    }
    // dd($result);
    foreach($result as &$v){
      if($v['is_admin']==1 && $v['is_leader']==0){
        $this->addCC($v['email']);
      }else{
        $this->addAddress($v['email'],$v['name_en']);
      }
      $this->tmp_staff_data[$v['id']] = $v;
    }
    return $this;
  }
  
  public function addCC($target){
    if(is_int($target) || ctype_digit($target)){
      $staff = $this->Staff->map();
      if( isset($staff[$target]) ){
        $this->Mailer->addCC($staff[$target]['email']);
      }else{
        $this->writeDBLog('Not Found Staff Id : '.$target);
      }
    }else{
      if (is_array($target)) {
        foreach ($target as $key => $email) {
          $this->addCC($email);  
        }
      } else {
        $this->Mailer->addCC($target);  
      }
    }
  }
  
  private function buildMaillServiceConnection(){
    
    date_default_timezone_set("Asia/Taipei");
    try{
      
      $conf = $this->CONF['MAIL_CONFIG'];
      // $username = getenv('username'); 
      // $password = getenv('password'); 
      // $pop = new \POP3(); 
      // $auth = $pop->Authorise($conf['host'], 110, 30, $conf['user'], $conf['pwd'], 1); 
      
      $this->Mailer = ($this->enabled) ? new \PHPMailer : new FakeMailer;
      $mail = $this->Mailer;
      $mail->isSMTP();
      $mail->SMTPDebug = 0;
      
      $mail->Host = $conf['host']; 
      $mail->SMTPAuth = false;

      $mail->Username = $conf['user'];
      $mail->Password = $conf['pwd'];

      $mail->SMTPSecure = $conf['secure'];
      
      $mail->Port = $conf['port'];
      $mail->CharSet = $conf['char'];

      $mail->setFrom( $conf['from'], $conf['fromName']);
      
      $mail->smtpConnect([
          'ssl' => [
              'verify_peer' => false,
              'verify_peer_name' => false,
              'allow_self_signed' => true
          ]
      ]);
      
    } catch (Exception $e) {
        throw new Exception($e->getMessage());
        $this->writeDBLog($e.getMessage());
    }
    return $mail;
  }
  
  
  
  protected function writeDBLog($str){
    $file = '/mail_error';
    $time_end = microtime(true);
    $spend_time = $time_end - $this->log_time;
    
    $log = (empty($log))? new \Logging() : $log;
    $log->lfile( $file );
    $log->lwrite("\n----------------------------------------------------------------------\n ".$str."\n\r - Spend Time : ( ".$spend_time." )\n"."----------------------------------------------------------------------\n");
    
    $log->lclose();
  }

  /**
   * 
   */
  public function getMailer() {
    return $this->Mailer;
  }

  /**
   * 一次清除CC、Mail Address、....
   * @method     clear
   * @author Alex Lin <alex.lin@rv88.tw>
   * @version    [version]
   * @modifyDate 2017-08-21T10:52:43+0800
   * @param      array                    $clearItems 要清除的東西 ，空陣列表示所有的東西都清除
   *                                                  可以清除的項目為  
   *                                                  address     : 收件者Mail Address
                                                      cc          : 副件收件人
                                                      bcc         : Bcc收件人
                                                      reply       : 回覆
                                                      recipients  : 回條
                                                      attachments : 附件
                                                      header      : 表頭
   * @return     [type]                   [description]
   */
  public function clear($clearItems = []) {
    if ($clearItems) {
      $clearItems = array_unique($clearItems);
      foreach ($clearItems as $key => $item) {
        $item = trim($item);
        switch ($item) {
          case 'addresses':
          case 'address':
            $this->getMailer()->clearAddresses();
            break;
          case 'cc':
          case 'ccs':
            $this->getMailer()->clearCCs();
            break;
          case 'bcc':
          case 'bccs':
            $this->getMailer()->clearBCCs();
            break;
          case 'reply':
            $this->getMailer()->clearReplyTos();
            break;
          case 'recipients':
            $this->getMailer()->clearAllRecipients();
            break;
          case 'attachments':
            $this->getMailer()->clearAttachments();
            break;
          case 'header':
            $this->getMailer()->clearCustomHeaders();
            break;
          default:
            # code...
            break;
        }
      }
    } else {
      $this->getMailer()->clearAddresses();
      $this->getMailer()->clearCCs();
      $this->getMailer()->clearBCCs();
      $this->getMailer()->clearReplyTos();
      $this->getMailer()->clearAllRecipients();
      $this->getMailer()->clearAttachments();
      $this->getMailer()->clearCustomHeaders();
    }
  }
}

?>
