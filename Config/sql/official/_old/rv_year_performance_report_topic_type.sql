/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50617
Source Host           : localhost:3306
Source Database       : new_hr_qa3

Target Server Type    : MYSQL
Target Server Version : 50617
File Encoding         : 65001

Date: 2017-05-31 19:08:33
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `rv_year_performance_report_topic_type`
-- ----------------------------
DROP TABLE IF EXISTS `rv_year_performance_report_topic_type`;
CREATE TABLE `rv_year_performance_report_topic_type` (
  `id` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(16) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '題目名稱',
  `sort` int(4) NOT NULL COMMENT '排序 asc',
  `enable` int(2) NOT NULL DEFAULT '1' COMMENT '開啟狀態 1=on,0=off',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of rv_year_performance_report_topic_type
-- ----------------------------
INSERT INTO `rv_year_performance_report_topic_type` VALUES ('1', '工作績效', '1', '1');
INSERT INTO `rv_year_performance_report_topic_type` VALUES ('2', '知識技能', '2', '1');
INSERT INTO `rv_year_performance_report_topic_type` VALUES ('3', '溝通協調', '3', '1');
INSERT INTO `rv_year_performance_report_topic_type` VALUES ('4', '品德及工作態度', '4', '1');
INSERT INTO `rv_year_performance_report_topic_type` VALUES ('5', '管理能力', '5', '1');
