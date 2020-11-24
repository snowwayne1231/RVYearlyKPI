<?php
return array(
  "DB_CONFIG" => Array(
    // "server" => "172.16.0.145",
    "server" => "127.0.0.1",          //DB ip
    "user" => "root",                 //DB user name
    "pwd" => "",                      //DB user password
    "content" => "new_hr_qa4",        //DB 庫名
    "limit_record" => 5000,           //DB query 上限，建議2000-5000 才能完整呈現
    "long_time" => 0.001                //DB long time設定， 單位 second , 高於此數值的 db操作會在log中印出
  )

);
