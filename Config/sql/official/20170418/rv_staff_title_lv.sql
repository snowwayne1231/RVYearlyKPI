/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50617
Source Host           : localhost:3306
Source Database       : new_hr_qa2

Target Server Type    : MYSQL
Target Server Version : 50617
File Encoding         : 65001

Date: 2017-04-18 13:00:54
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `rv_staff_title_lv`
-- ----------------------------
DROP TABLE IF EXISTS `rv_staff_title_lv`;
CREATE TABLE `rv_staff_title_lv` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT '職類',
  `lv` int(2) DEFAULT '5',
  `enable` int(2) NOT NULL DEFAULT '1' COMMENT '1=ON,0=OFF',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of rv_staff_title_lv
-- ----------------------------
INSERT INTO `rv_staff_title_lv` VALUES ('1', '決策人員', '1', '1');
INSERT INTO `rv_staff_title_lv` VALUES ('2', '部級主管', '2', '1');
INSERT INTO `rv_staff_title_lv` VALUES ('3', '處級主管', '3', '1');
INSERT INTO `rv_staff_title_lv` VALUES ('4', '組長', '4', '1');
INSERT INTO `rv_staff_title_lv` VALUES ('5', '一般人員(行政/專技)', '5', '1');
INSERT INTO `rv_staff_title_lv` VALUES ('6', '約聘人員', '6', '1');
INSERT INTO `rv_staff_title_lv` VALUES ('7', '其他職員', '5', '1');
INSERT INTO `rv_staff_title_lv` VALUES ('8', '無', '9', '1');
INSERT INTO `rv_staff_title_lv` VALUES ('9', '轉正人員', '5', '1');
