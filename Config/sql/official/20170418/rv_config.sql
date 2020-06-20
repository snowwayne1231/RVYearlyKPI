/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50617
Source Host           : localhost:3306
Source Database       : new_hr_qa2

Target Server Type    : MYSQL
Target Server Version : 50617
File Encoding         : 65001

Date: 2017-04-18 13:01:15
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `rv_config`
-- ----------------------------
DROP TABLE IF EXISTS `rv_config`;
CREATE TABLE `rv_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT '設定檔名',
  `json` varchar(255) COLLATE utf8_unicode_ci DEFAULT '{}' COMMENT '設定檔內容',
  `update_date` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of rv_config
-- ----------------------------
INSERT INTO `rv_config` VALUES ('1', 'email', '{\"host\":\"mail.rv88.tw\",\r\n    \"user\" :\"dev.test@rv88.tw\",\r\n    \"pwd\" :\"NXsAZr2u6raXJXvt\",\r\n    \"secure\" : \"\",\r\n    \"port\" : 25,\r\n    \"from\" :\"dev.test@rv88.tw\",\"char\" :\"UTF-8\"}', '2017-04-10 21:46:08');
