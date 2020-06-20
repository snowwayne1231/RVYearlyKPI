DROP TABLE IF EXISTS `rv_record_admin`;
CREATE TABLE `rv_record_admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `operating_staff_id` int(11) NOT NULL DEFAULT '0',
  `type` int(6) NOT NULL COMMENT '操作類型',
  `doing` int(2) NOT NULL COMMENT '操作動作 1=add,2=update,3=delete',
	`api` varchar(255) COLLATE utf8_unicode_ci DEFAULT '' COMMENT 'API請求URL',
  `changed_json` varchar(1024) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '改變數值JSON',
  `update_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ip` varchar(64) COLLATE utf8_unicode_ci DEFAULT 'Undefined' COMMENT '紀錄操作IP',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;