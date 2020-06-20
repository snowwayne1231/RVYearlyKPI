/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50617
Source Host           : localhost:3306
Source Database       : new_hr_qa2

Target Server Type    : MYSQL
Target Server Version : 50617
File Encoding         : 65001

Date: 2017-04-18 13:00:50
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `rv_staff_status`
-- ----------------------------
DROP TABLE IF EXISTS `rv_staff_status`;
CREATE TABLE `rv_staff_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of rv_staff_status
-- ----------------------------
INSERT INTO `rv_staff_status` VALUES ('1', '正式');
INSERT INTO `rv_staff_status` VALUES ('2', '約聘');
INSERT INTO `rv_staff_status` VALUES ('3', '試用');
INSERT INTO `rv_staff_status` VALUES ('4', '離職');
