
alter table rv_monthly_report add column title_id int(11) not null default '0' comment '員工職稱類別id' after staff_id ;
alter table rv_monthly_report add column post_id int(11) not null default '0' comment '員工職稱id' after staff_id ;
alter table rv_monthly_report_leader add column title_id int(11) not null default '0' comment '主管職稱類別id' after staff_id ;
alter table rv_monthly_report_leader add column post_id int(11) not null default '0' comment '主管職稱id' after staff_id ;
