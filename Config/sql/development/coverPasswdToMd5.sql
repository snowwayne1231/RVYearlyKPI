
update rv_staff as a set passwd = md5( concat(a.passwd,'rv123kpi456')) where CHAR_LENGTH(passwd)>0;

alter table rv_staff change column status status VARCHAR(16) default '' COMMENT '在職狀態';