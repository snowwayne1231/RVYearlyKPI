<?php

include __DIR__."/../ApiCore.php";

$api = new ApiCore($_POST);

use Model\Business\RecordStaff;
use Model\Business\RecordAdmin;
use Model\Business\Staff;
  
if( $api->isAdmin() ){
  $type = (int)$api->post('type');
  $limit = (int)$api->post('count');
  
  // $where_type = $type==0 ? [] : ['type'=>$type];
  $where_type = $type==0 ? '' : "where type = $type";
  $limit = empty($limit) ? 25 : $limit;
  
  $staff = new Staff();
  $staff_table = $staff->table_name;
  
  $data = []; $staff_data = [];
  switch($type){
    case 0:
    case 7:
      $record_staff = new RecordStaff();
      $staff_data = $record_staff->sql("select a.id, a.operating_staff_id, b.name as operating_staff_name, b.name_en as operating_staff_name_en,
      a.staff_id, c.name as staff_name, c.name_en as staff_name_en, a.doing, a.update_date, a.ip from {table} as a 
      left join $staff_table as b on a.operating_staff_id = b.id 
      left join $staff_table as c on a.staff_id = c.id 
      order by update_date desc limit $limit
      ")->data; if($type==7){break;}
    default:
      $record_admin = new RecordAdmin();
      $data = $record_admin->sql("select a.id, a.operating_staff_id, a.type, a.doing, a.api, a.update_date, a.ip, b.name, b.name_en from {table} as a 
      left join $staff_table as b on a.operating_staff_id = b.id 
      $where_type
      order by update_date desc limit $limit
      ")->data;
  }
  
  
  
  // $record_staff->limit = 25;
  
  
  $res = [];
  $res['system'] = $data;
  $res['staff'] = $staff_data;
  
  $api->setArray($res);
  
}

print $api->getJSON();

?>