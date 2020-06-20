<?php

include __DIR__."/../ApiCore.php";

use Model\Business\DepartmentLeadership;

$api = new ApiCore($_REQUEST, ['department_id']);

// if( $api->SC->isAdmin() ){

$id = $api->post('department_id');

$leadership = new DepartmentLeadership();

$result = $leadership->select(['department_id' => $id]);

foreach($result as &$value) {
    $value['is_leader_group'] = $value['status'] >= 1;
}

$api->setArray($result);
  
// }else{
//   $api->denied();
// }

print $api->getJSON();

?>