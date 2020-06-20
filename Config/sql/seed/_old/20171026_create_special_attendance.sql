CREATE TABLE `rv_attendance_special` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) NOT NULL COMMENT '員工id',
  `date` date DEFAULT NULL COMMENT '日期',
  `year` int(4) DEFAULT '0' COMMENT '年戳',
  `type` int(2) NOT NULL COMMENT '類型id',
  `value` int(11) DEFAULT '0' COMMENT '數值內容',
  `value_char` varchar(1024) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '字符內容',
  `remark` varchar(255) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '備註欄',
  `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '建立日期',
  PRIMARY KEY (`id`),
  KEY `type` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;