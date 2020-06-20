/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50617
Source Host           : localhost:3306
Source Database       : new_hr_qa3

Target Server Type    : MYSQL
Target Server Version : 50617
File Encoding         : 65001

Date: 2017-05-31 19:08:14
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `rv_year_performance_report`
-- ----------------------------
DROP TABLE IF EXISTS `rv_year_performance_report`;
CREATE TABLE `rv_year_performance_report` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `year` int(4) NOT NULL COMMENT '年份',
  `staff_id` int(11) NOT NULL COMMENT 'staff.id',
  `owner_staff_id` int(11) NOT NULL COMMENT '當前擁有者',
  `department_id` int(11) NOT NULL COMMENT 'department.id',
  `division_id` int(11) NOT NULL COMMENT '部門單位的ID',
  `staff_is_leader` int(2) NOT NULL COMMENT '員工當時是否為主管',
  `staff_lv` int(2) NOT NULL COMMENT '員工當時lv',
  `staff_post` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT '職務',
  `staff_title` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT '職務類別',
  `processing_lv` int(2) NOT NULL DEFAULT '5' COMMENT '進程 部門lv 用來判斷交到哪一層了',
  `path` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT '[]' COMMENT '考績會經由哪幾個 staff.id 手上',
  `before_level` varchar(6) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'A' COMMENT '去年的考績等級',
  `monthly_average` float(5,2) NOT NULL DEFAULT '0.00' COMMENT '月考評平均值',
  `attendance_json` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '{"late":0,"early":0,"nocard":0,"leave":0,"paysick":0,"physiology":0,"sick":0,"absent":0}' COMMENT '出缺勤json',
  `assessment_json` varchar(1020) COLLATE utf8_unicode_ci NOT NULL DEFAULT '{"under":{"percent":10,"total":100,"score":[15,15,15,4,4,4,4,4,3,3,3,3,3,5,5,5,5]},"self":{"percent":40,"total":100,"score":[15,15,15,4,4,4,4,4,3,3,3,3,3,5,5,5,5]},"upper_1":{"percent":50,"total":100,"score":[15,15,15,4,4,4,4,4,3,3,3,3,3,5,5,5,5]}}' COMMENT '考積分數json',
  `assessment_total` float(4,2) NOT NULL DEFAULT '0.00' COMMENT '考績結算總分',
  `assessment_total_division_change` int(4) DEFAULT '0' COMMENT '架構發展者 加減分',
  `assessment_total_ceo_change` int(4) DEFAULT '0' COMMENT '決策者/執行長 加減分',
  `level` varchar(6) COLLATE utf8_unicode_ci NOT NULL DEFAULT '-' COMMENT '今年的考績等級',
  `self_contribution` varchar(1020) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '自己在公司的貢獻描述',
  `self_improve` varchar(1020) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '自己在公司需要改善的描述',
  `upper_comment` varchar(2040) COLLATE utf8_unicode_ci DEFAULT '{1:{"staff_id":1,"content":""},2:{"staff_id":19,"content":""}}' COMMENT '上層主管們的評論',
  `update_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `year` (`year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of rv_year_performance_report
-- ----------------------------
