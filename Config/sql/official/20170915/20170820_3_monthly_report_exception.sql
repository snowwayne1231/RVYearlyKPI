alter table rv_monthly_report add column exception_reason varchar(255) default '' not null comment '不計分原因' after exception