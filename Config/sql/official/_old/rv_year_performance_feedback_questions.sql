/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50617
Source Host           : localhost:3306
Source Database       : new_hr_qa3

Target Server Type    : MYSQL
Target Server Version : 50617
File Encoding         : 65001

Date: 2017-05-31 19:08:10
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `rv_year_performance_feedback_questions`
-- ----------------------------
DROP TABLE IF EXISTS `rv_year_performance_feedback_questions`;
CREATE TABLE `rv_year_performance_feedback_questions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mode` enum('normal','others','company','target') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'normal' COMMENT '問答題的模式',
  `title` varchar(63) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '問答題標題',
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '問答題描述',
  `sort` int(6) NOT NULL DEFAULT '1' COMMENT '排序設定 asc',
  `enable` int(2) NOT NULL DEFAULT '1' COMMENT '開啟狀態 1=on,0=off',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of rv_year_performance_feedback_questions
-- ----------------------------
INSERT INTO `rv_year_performance_feedback_questions` VALUES ('1', 'normal', '優點', '在本年度的工作中，您覺得受評主管令人敬配、或最值得學習的、或讓您忍不住想大力讚揚是什麼？', '1', '1');
INSERT INTO `rv_year_performance_feedback_questions` VALUES ('2', 'normal', '改善', '在本年度的工作中，您覺得受評主管有哪些是可以改善，進而促進上司與部屬之間的關係？', '2', '1');
INSERT INTO `rv_year_performance_feedback_questions` VALUES ('3', 'others', '建議', '除了您的受評主管之外，對於其它部門主管，是否有任何是您想提出嘉許或建議的？', '4', '0');
INSERT INTO `rv_year_performance_feedback_questions` VALUES ('4', 'normal', '建議', '在本年度的工作中，對於受評主管，是否有任何是您想提出建議的？', '3', '1');
INSERT INTO `rv_year_performance_feedback_questions` VALUES ('5', 'others', '建議', '除了您的受評主管之外，對於其它部門主管，是否有任何是您想提出嘉許或建議的？', '4', '1');
INSERT INTO `rv_year_performance_feedback_questions` VALUES ('6', 'company', '建議', '對於公司，是否有其他您想特別說明/補充呢?', '5', '1');
