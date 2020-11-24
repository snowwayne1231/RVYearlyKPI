<?php

include __DIR__."/../ApiCore.php";

use Model\Business\DepartmentLeadership;
use Model\Business\Department;
use Model\Business\Staff;

$api = new ApiCore($_REQUEST);

if( $api->SC->isAdmin() ){

    $id = $api->post('id');
    $staff_id = $api->post('staff_id');
    $department_id = $api->post('department_id');
    $is_leader_group = $api->post('is_leader_group');

    $leadership = new DepartmentLeadership();

    if ($staff_id == false || $department_id == false) {
        $api->denied('Not Enough Paramters.');
    }

    $status = $is_leader_group ? 1 : 0;

    if ($status) {
        $department = new Department();
        $d_data = $department->read(['id' => $department_id])->check(0, 'Wrong Department Id.')->data[0];
        if ($d_data['manager_staff_id'] == 0) {
            $api->denied('No Manager In This Department.');
        }

        if ($d_data['upper_id'] == 0) {
            $api->denied('Top Department Can Not Give Leadership.');
        }

        if ($d_data['lv'] <= 2) {
            $api->denied('Division Can Not Set Multiple Leadership.');
        }
    }

    $staff = new Staff();

    if ($id) {
        
        if ($staff->read(['id' => $staff_id, 'department_id'=> $department_id])->check(0, 'Staff Is Not In Department.')) {

            if ($leadership->read($id)->check(0, 'Not Found This Id.')) {
                $leadership->update(['status' => $status]);

                if ($status) {
                    $staff->update(['is_leader'=> 1], $staff_id);
                } else {
                    $staff->update(['is_leader'=> 0], $staff_id);
                }
                $api->setArray('ok');
            }
        }

    } else if ($status){

        if ($leadership->read(['department_id' => $department_id, 'staff_id' => $staff_id])->check(1, 'Staff Already Is A Leader In Department.')) {
            
        }

        $new_id = $leadership->create(['status' => $status, 'department_id' => $department_id, 'staff_id' => $staff_id]);
        $staff->update(['is_leader'=> 1], $staff_id);
        $api->setArray(['id' => $new_id]);

    } else {

        $api->denied('Not Enough Paramters.');

    }

}else{
  $api->denied('You Are Not Admin.');
}

print $api->getJSON();

?>