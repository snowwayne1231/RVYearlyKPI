/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50617
Source Host           : localhost:3306
Source Database       : new_hr_qa3

Target Server Type    : MYSQL
Target Server Version : 50617
File Encoding         : 65001

Date: 2017-05-31 19:07:39
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `rv_record_year_performance_report`
-- ----------------------------
DROP TABLE IF EXISTS `rv_record_year_performance_report`;
CREATE TABLE `rv_record_year_performance_report` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `report_id` int(11) NOT NULL COMMENT '年考績的id',
  `type` enum('commit','return','modify','other') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'other' COMMENT '年考績記錄類型',
  `changed_json` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '{}' COMMENT '改變的內容',
  `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '建立日期',
  PRIMARY KEY (`id`),
  KEY `report_id` (`report_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of rv_record_year_performance_report
-- ----------------------------
