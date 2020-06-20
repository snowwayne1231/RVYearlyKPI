/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50617
Source Host           : localhost:3306
Source Database       : new_hr_qa2

Target Server Type    : MYSQL
Target Server Version : 50617
File Encoding         : 65001

Date: 2017-04-18 13:00:45
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `rv_staff_post`
-- ----------------------------
DROP TABLE IF EXISTS `rv_staff_post`;
CREATE TABLE `rv_staff_post` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT '職務',
  `type` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '職務名稱類別',
  `orderby` int(6) NOT NULL DEFAULT '1' COMMENT '排序順位',
  `enable` int(2) NOT NULL DEFAULT '1' COMMENT '1=ON,0=OFF',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of rv_staff_post
-- ----------------------------
INSERT INTO `rv_staff_post` VALUES ('1', '經理', '管理職', '23', '1');
INSERT INTO `rv_staff_post` VALUES ('2', '資深技術支援工程師', '專業職', '7', '1');
INSERT INTO `rv_staff_post` VALUES ('3', '資深人資專員', '行政職', '15', '1');
INSERT INTO `rv_staff_post` VALUES ('4', '資料庫管理工程師', '專業職', '6', '1');
INSERT INTO `rv_staff_post` VALUES ('5', '行政人員', '行政職', '13', '1');
INSERT INTO `rv_staff_post` VALUES ('6', '網頁設計師', '專業職', '6', '1');
INSERT INTO `rv_staff_post` VALUES ('7', '網頁程式設計師', '專業職', '6', '1');
INSERT INTO `rv_staff_post` VALUES ('8', '系統工程師', '專業職', '7', '1');
INSERT INTO `rv_staff_post` VALUES ('9', '稽核專員', '專業職', '6', '1');
INSERT INTO `rv_staff_post` VALUES ('10', '技術支援工程師', '專業職', '6', '1');
INSERT INTO `rv_staff_post` VALUES ('11', '客服專員', '專業職', '5', '1');
INSERT INTO `rv_staff_post` VALUES ('12', '助理', '其他', '1', '1');
INSERT INTO `rv_staff_post` VALUES ('13', '工讀生', '其他', '1', '1');
INSERT INTO `rv_staff_post` VALUES ('14', '資料庫操作員', '專業職', '5', '1');
INSERT INTO `rv_staff_post` VALUES ('15', '風控專員', '專業職', '5', '1');
INSERT INTO `rv_staff_post` VALUES ('16', '襄理', '管理職', '24', '1');
INSERT INTO `rv_staff_post` VALUES ('17', '營運長', '管理職', '25', '1');
INSERT INTO `rv_staff_post` VALUES ('18', '處長', '管理職', '21', '1');
INSERT INTO `rv_staff_post` VALUES ('19', '組長', '管理職', '20', '1');
