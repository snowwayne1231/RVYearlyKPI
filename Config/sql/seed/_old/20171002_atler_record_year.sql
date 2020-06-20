DROP TABLE IF EXISTS `rv_record_year_performance_report`;
CREATE TABLE `rv_record_year_performance_report` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `operating_staff_id` int(11) NOT NULL DEFAULT '0',
  `report_id` int(11) NOT NULL COMMENT '年考績的id',
  `type` int(2) NOT NULL COMMENT '年考績記錄類型 1=save, 2=commit, 3=agree, 4=return, 5=other, ',
	`origin_json` text COLLATE utf8_unicode_ci COMMENT '年考績原始欄位資料',
  `changed_json` text COLLATE utf8_unicode_ci COMMENT '改變的內容',
  `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '建立日期',
  PRIMARY KEY (`id`),
  KEY `report_id` (`report_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `rv_record_year_performance_divisions`;
CREATE TABLE `rv_record_year_performance_divisions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `operating_staff_id` int(11) NOT NULL DEFAULT '0',
  `division_id` int(11) NOT NULL COMMENT '年考績 部門單的id',
  `type` int(2) NOT NULL COMMENT '年考績記錄類型 1=save, 2=commit, 3=agree, 4=return, 5=other, ',
	`origin_json` text COLLATE utf8_unicode_ci COMMENT '年考績原始欄位資料',
  `changed_json` text COLLATE utf8_unicode_ci COMMENT '改變的內容',
  `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '建立日期',
  PRIMARY KEY (`id`),
  KEY `division_id` (`division_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
