ALTER TABLE `rv_record_staff` ADD COLUMN `doing` int(2) NOT NULL DEFAULT '2' COMMENT '操作 1=add, 2=update, 3=delete' AFTER `staff_id`;
alter table rv_record_staff add column ip varchar(64) default 'Undefined' comment '紀錄操作IP' ;