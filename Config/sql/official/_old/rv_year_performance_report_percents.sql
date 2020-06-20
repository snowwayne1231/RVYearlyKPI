/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50617
Source Host           : localhost:3306
Source Database       : new_hr_qa3

Target Server Type    : MYSQL
Target Server Version : 50617
File Encoding         : 65001

Date: 2017-05-31 19:08:25
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `rv_year_performance_report_percents`
-- ----------------------------
DROP TABLE IF EXISTS `rv_year_performance_report_percents`;
CREATE TABLE `rv_year_performance_report_percents` (
  `id` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `lv` int(2) NOT NULL COMMENT '組織階層',
  `type` int(2) NOT NULL DEFAULT '1' COMMENT '適用對象 1=主管,2=一般人員',
  `percent_json` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '分數配率百分比 {lv:percent,..}',
  `enable` int(2) NOT NULL DEFAULT '1' COMMENT '開啟狀態 1=on,0=off',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of rv_year_performance_report_percents
-- ----------------------------
INSERT INTO `rv_year_performance_report_percents` VALUES ('1', '1', '1', '{\"_0\":40,\"_1\":0,\"_2\":0,\"_3\":0,\"_4\":0,\"_5\":0,\"_6\":0,\"_\":60}', '1');
INSERT INTO `rv_year_performance_report_percents` VALUES ('3', '2', '1', '{\"_0\":40,\"_1\":40,\"_2\":0,\"_3\":0,\"_4\":0,\"_5\":0,\"_6\":0,\"_\":20}', '1');
INSERT INTO `rv_year_performance_report_percents` VALUES ('4', '4', '1', '{\"_0\":40,\"_1\":0,\"_2\":30,\"_3\":20,\"_4\":0,\"_5\":0,\"_6\":0,\"_\":10}', '1');
INSERT INTO `rv_year_performance_report_percents` VALUES ('5', '3', '1', '{\"_0\":40,\"_1\":0,\"_2\":40,\"_3\":0,\"_4\":0,\"_5\":0,\"_6\":0,\"_\":20}', '1');
INSERT INTO `rv_year_performance_report_percents` VALUES ('6', '5', '1', '{\"_0\":40,\"_1\":0,\"_2\":20,\"_3\":20,\"_4\":20,\"_5\":0,\"_6\":0,\"_\":0}', '1');
INSERT INTO `rv_year_performance_report_percents` VALUES ('7', '1', '2', '{\"_0\":40,\"_1\":60,\"_2\":0,\"_3\":0,\"_4\":0,\"_5\":0,\"_6\":0,\"_\":0}', '1');
INSERT INTO `rv_year_performance_report_percents` VALUES ('8', '2', '2', '{\"_0\":40,\"_1\":0,\"_2\":60,\"_3\":0,\"_4\":0,\"_5\":0,\"_6\":0,\"_\":0}', '1');
INSERT INTO `rv_year_performance_report_percents` VALUES ('9', '3', '2', '{\"_0\":40,\"_1\":0,\"_2\":20,\"_3\":40,\"_4\":0,\"_5\":0,\"_6\":0,\"_\":0}', '1');
INSERT INTO `rv_year_performance_report_percents` VALUES ('10', '4', '2', '{\"_0\":40,\"_1\":0,\"_2\":20,\"_3\":20,\"_4\":20,\"_5\":0,\"_6\":0,\"_\":0}', '1');
