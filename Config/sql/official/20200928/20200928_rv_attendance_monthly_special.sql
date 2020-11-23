
SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `rv_attendance_special`
-- ----------------------------
DROP TABLE IF EXISTS `rv_attendance_monthly_special`;
CREATE TABLE `rv_attendance_monthly_special` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `department_id` int(11) NOT NULL COMMENT '部門id',
  `staff_id` int(11) NOT NULL COMMENT '員工id',
  `outside_number` varchar(32) DEFAULT 0 COMMENT '外來編號',
  `date` date DEFAULT NULL COMMENT '日期',
  `time` time DEFAULT NULL COMMENT '時間',
  `year` int(4) DEFAULT '0' COMMENT '年戳',
  `month` int(4) DEFAULT '0' COMMENT '月戳',
  `type` int(2) NOT NULL COMMENT '類型id 1=未帶 2=忘刷',
  `status_duty` int(2) NOT NULL COMMENT '上班狀態 1=上班 2=下班',
  `reason` varchar(32) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '原因',
  `remark` varchar(255) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '備註欄',
  `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '建立日期',
  PRIMARY KEY (`id`),
  KEY `type` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `rv_attendance_monthly_special` ADD INDEX staff_id(`staff_id`);
ALTER TABLE `rv_attendance_monthly_special` ADD INDEX ym(`year`, `month`);
