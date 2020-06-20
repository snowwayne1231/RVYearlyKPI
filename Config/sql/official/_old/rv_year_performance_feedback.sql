/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50617
Source Host           : localhost:3306
Source Database       : new_hr_qa3

Target Server Type    : MYSQL
Target Server Version : 50617
File Encoding         : 65001

Date: 2017-05-31 19:07:58
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `rv_year_performance_feedback`
-- ----------------------------
DROP TABLE IF EXISTS `rv_year_performance_feedback`;
CREATE TABLE `rv_year_performance_feedback` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `year` int(4) NOT NULL,
  `staff_id` int(11) NOT NULL DEFAULT '0' COMMENT '員工代號 rv_staff.id',
  `department_id` int(11) NOT NULL COMMENT 'department.id',
  `status` int(2) NOT NULL DEFAULT '0' COMMENT '狀態 0=未提交, 1=已提交',
  `target_staff_id` int(11) NOT NULL DEFAULT '0' COMMENT '目標staff_id, 0=公司',
  `multiple_choice_json` varchar(1020) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '單選題的結果',
  `multiple_total` int(4) DEFAULT '0' COMMENT '單選題總分',
  `multiple_score` int(4) DEFAULT '0' COMMENT '單選題總分',
  `update_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `year` (`year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of rv_year_performance_feedback
-- ----------------------------
