<?php
namespace Model;

include_once BASE_PATH.'/Model/PHPMailer/PHPMailerAutoload.php';


use \Exception;

class FakeMailer extends \PHPMailer {

  /**
   * 覆寫send 這裡，直接讓它寫到Log去，而不是直接真的寄出去
   * @method     send
   * @author Alex Lin <alex.lin@rv88.tw>
   * @version    [version]
   * @modifyDate 2017-08-07T12:07:15+0800
   * @return     [type]                   [description]
   */
  public function send()
  {
     $log = (empty($log))? new \Logging() : $log;
     $file = '/fake_mail_result';
     $message = [
                 'to' => $this->getMailWithName($this->getToAddresses()),
                 'from' => $this->From,
                 'cc' => $this->getMailWithName($this->getCcAddresses()),
                 'bcc' => $this->getMailWithName($this->getBccAddresses()),
                 'reply' => $this->getMailWithName($this->getReplyToAddresses()),
                 'subject' => $this->Subject,
                 'contentType' => $this->ContentType,
                 'body' => $this->Body,
                ];
     $message = array_filter($message);
     writeLog($file, print_r($message, true));
     return true;
  }

  public function smtpConnect($ary = []) {
    return false;
  }

  private function getMailWithName($array)
  {
    $return = [];
    if ($array) {
      foreach ($array as $key => $subAry) {
        if (is_array($subAry)) {
          list($email, $name) = $subAry;
          $return[] = $email;
        }
      }
    }
    return implode(',', $return);
  }
}