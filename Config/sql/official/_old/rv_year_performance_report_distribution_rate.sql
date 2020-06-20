/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50617
Source Host           : localhost:3306
Source Database       : new_hr_qa3

Target Server Type    : MYSQL
Target Server Version : 50617
File Encoding         : 65001

Date: 2017-05-31 19:08:19
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `rv_year_performance_report_distribution_rate`
-- ----------------------------
DROP TABLE IF EXISTS `rv_year_performance_report_distribution_rate`;
CREATE TABLE `rv_year_performance_report_distribution_rate` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `lv` int(11) unsigned NOT NULL COMMENT '等級',
  `name` varchar(8) COLLATE utf8_unicode_ci NOT NULL COMMENT '等級別名',
  `score_least` int(4) NOT NULL DEFAULT '60' COMMENT '評等的分數下限',
  `score_limit` int(4) NOT NULL DEFAULT '100' COMMENT '評等的分數上限',
  `rate_least` int(4) NOT NULL COMMENT '百分比下限',
  `rate_limit` int(4) NOT NULL COMMENT '百分比上限',
  `enable` int(2) NOT NULL DEFAULT '1' COMMENT '是否啟用',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of rv_year_performance_report_distribution_rate
-- ----------------------------
INSERT INTO `rv_year_performance_report_distribution_rate` VALUES ('1', '1', 'A', '91', '100', '5', '5', '1');
INSERT INTO `rv_year_performance_report_distribution_rate` VALUES ('2', '2', 'B', '81', '90', '20', '20', '1');
INSERT INTO `rv_year_performance_report_distribution_rate` VALUES ('3', '3', 'C', '71', '80', '60', '60', '1');
INSERT INTO `rv_year_performance_report_distribution_rate` VALUES ('4', '4', 'D', '61', '70', '10', '15', '1');
INSERT INTO `rv_year_performance_report_distribution_rate` VALUES ('5', '5', 'E', '0', '60', '0', '5', '1');
