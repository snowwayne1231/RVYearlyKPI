/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50617
Source Host           : localhost:3306
Source Database       : new_hr_qa3

Target Server Type    : MYSQL
Target Server Version : 50617
File Encoding         : 65001

Date: 2017-05-31 19:07:32
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `rv_record_year_performance_questions`
-- ----------------------------
DROP TABLE IF EXISTS `rv_record_year_performance_questions`;
CREATE TABLE `rv_record_year_performance_questions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `question_id` int(11) NOT NULL COMMENT '受評的題目id',
  `year` int(4) NOT NULL COMMENT '問答題年份',
  `from_type` int(2) NOT NULL DEFAULT '1' COMMENT '來源 1=部屬, 2=其他部門, 3=上司, 4=其他',
  `highlight` int(2) NOT NULL DEFAULT '0' COMMENT '是否關注',
  `target_staff_id` int(11) NOT NULL COMMENT '受評的staff.id',
  `content` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '評論的內容',
  `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '評論建立日期',
  PRIMARY KEY (`id`),
  KEY `target_staff_id` (`target_staff_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of rv_record_year_performance_questions
-- ----------------------------
