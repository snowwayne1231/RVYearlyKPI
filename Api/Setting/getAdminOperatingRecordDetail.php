<?php

include __DIR__."/../ApiCore.php";

$api = new ApiCore($_POST);

use Model\Business\RecordStaff;
use Model\Business\RecordAdmin;
use Model\Business\Staff;
  
if( $api->isAdmin() && $api->checkPost(['id','type'])){
  $type = (int)$api->post('type');
  $id = (int)$api->post('id');
  
  switch($type){
    case 1:case 2:case 3:case 4:case 5:case 6: $record = new RecordAdmin(); break;
    case 7: $record = new RecordStaff(); break;
    default:
    $api->denied('Wrong Type.');
  }
  
  $staff = new Staff();
  $staff_table = $staff->table_name;
  
  $data = $record->sql("select a.*, b.name, b.name_en  from {table} as a 
  left join $staff_table as b on a.operating_staff_id = b.id 
  where a.id = $id ")->check('Not Found This Record.')->data[0];
  
  
  $api->setArray($data);
  
}

print $api->getJSON();

?>