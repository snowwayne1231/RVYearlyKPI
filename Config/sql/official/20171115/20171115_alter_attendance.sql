ALTER TABLE `rv_attendance` 
change column `staff_id` `staff_id` int(11) DEFAULT '0' COMMENT 'rv_staff.id',
change column `date` `date` DATE DEFAULT NULL COMMENT '日期',
change column `checkin_hours` `checkin_hours` TIME DEFAULT NULL COMMENT '上班',
change column `checkout_hours` `checkout_hours` TIME DEFAULT NULL COMMENT '下班',
add index staff_id (`staff_id`);