/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50617
Source Host           : localhost:3306
Source Database       : new_hr_qa3

Target Server Type    : MYSQL
Target Server Version : 50617
File Encoding         : 65001

Date: 2017-05-31 19:08:29
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `rv_year_performance_report_topic`
-- ----------------------------
DROP TABLE IF EXISTS `rv_year_performance_report_topic`;
CREATE TABLE `rv_year_performance_report_topic` (
  `id` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `type` int(6) NOT NULL COMMENT 'type id',
  `name` varchar(16) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '題目名稱',
  `score` int(4) NOT NULL COMMENT '分數',
  `score_leader` int(4) NOT NULL COMMENT '主管分數',
  `sort` int(4) NOT NULL DEFAULT '1' COMMENT '排序 asc',
  `enable` int(2) NOT NULL DEFAULT '1' COMMENT '開啟狀態 1=on,0=off',
  `applicable` enum('normal','leader','both') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'both' COMMENT '題目的適用範圍',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of rv_year_performance_report_topic
-- ----------------------------
INSERT INTO `rv_year_performance_report_topic` VALUES ('1', '1', '工作效率', '20', '15', '1', '1', 'both');
INSERT INTO `rv_year_performance_report_topic` VALUES ('2', '1', '目標達成', '20', '15', '2', '1', 'both');
INSERT INTO `rv_year_performance_report_topic` VALUES ('3', '1', '績效改善', '20', '15', '3', '1', 'both');
INSERT INTO `rv_year_performance_report_topic` VALUES ('4', '2', '專業知識', '4', '4', '1', '1', 'both');
INSERT INTO `rv_year_performance_report_topic` VALUES ('5', '2', '創新能力', '4', '4', '2', '1', 'both');
INSERT INTO `rv_year_performance_report_topic` VALUES ('6', '2', '學習能力', '4', '4', '3', '1', 'both');
INSERT INTO `rv_year_performance_report_topic` VALUES ('7', '3', '合作協調能力', '4', '4', '1', '1', 'both');
INSERT INTO `rv_year_performance_report_topic` VALUES ('8', '3', '解決問題能力', '4', '4', '2', '1', 'both');
INSERT INTO `rv_year_performance_report_topic` VALUES ('9', '4', '品德操守', '4', '3', '1', '1', 'both');
INSERT INTO `rv_year_performance_report_topic` VALUES ('10', '4', '服務熱忱', '4', '3', '2', '1', 'both');
INSERT INTO `rv_year_performance_report_topic` VALUES ('11', '4', '責任感', '4', '3', '3', '1', 'both');
INSERT INTO `rv_year_performance_report_topic` VALUES ('12', '4', '團隊精神', '4', '3', '4', '1', 'both');
INSERT INTO `rv_year_performance_report_topic` VALUES ('13', '4', '遵守紀律', '4', '3', '5', '1', 'both');
INSERT INTO `rv_year_performance_report_topic` VALUES ('14', '5', '賦能授權 ', '0', '5', '1', '1', 'leader');
INSERT INTO `rv_year_performance_report_topic` VALUES ('15', '5', '溝通輔導', '0', '5', '2', '1', 'leader');
INSERT INTO `rv_year_performance_report_topic` VALUES ('16', '5', '賞罰公平', '0', '5', '3', '1', 'leader');
INSERT INTO `rv_year_performance_report_topic` VALUES ('17', '5', '變革領導', '0', '5', '4', '1', 'leader');
