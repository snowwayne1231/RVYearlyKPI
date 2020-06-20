/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50617
Source Host           : localhost:3306
Source Database       : new_hr_qa3

Target Server Type    : MYSQL
Target Server Version : 50617
File Encoding         : 65001

Date: 2017-05-31 19:07:53
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `rv_year_performance_divisions`
-- ----------------------------
DROP TABLE IF EXISTS `rv_year_performance_divisions`;
CREATE TABLE `rv_year_performance_divisions` (
  `id` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `status` int(2) NOT NULL DEFAULT '0' COMMENT '部門配比單狀態',
  `processing` int(2) DEFAULT '0' COMMENT '部門配比單 進程',
  `year` int(4) NOT NULL COMMENT '年分',
  `division` int(6) NOT NULL COMMENT '部門id',
  `owner_staff_id` int(11) NOT NULL COMMENT '當前擁有的staff id',
  `update_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of rv_year_performance_divisions
-- ----------------------------
