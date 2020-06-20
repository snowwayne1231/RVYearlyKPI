/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50617
Source Host           : localhost:3306
Source Database       : new_hr_qa3

Target Server Type    : MYSQL
Target Server Version : 50617
File Encoding         : 65001

Date: 2017-05-31 19:07:48
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `rv_year_performance_config_cyclical`
-- ----------------------------
DROP TABLE IF EXISTS `rv_year_performance_config_cyclical`;
CREATE TABLE `rv_year_performance_config_cyclical` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `year` int(4) NOT NULL,
  `date_start` date NOT NULL DEFAULT '0000-00-00' COMMENT '起算日',
  `date_end` date NOT NULL DEFAULT '0000-00-00' COMMENT '結算日',
  `processing` int(3) NOT NULL DEFAULT '0' COMMENT '進程階段',
  `department_construct_json` varchar(4096) NOT NULL DEFAULT '{}' COMMENT '部門架構',
  `constructor_staff_id` int(11) NOT NULL DEFAULT '2' COMMENT '架構發展staff.id',
  `ceo_staff_id` int(11) NOT NULL DEFAULT '1' COMMENT '決策者staff.id',
  `feedback_status` int(2) NOT NULL DEFAULT '0' COMMENT '問券回饋提交狀態',
  `feedback_addition_day` int(6) DEFAULT '7' COMMENT '問券回饋提交天數',
  `feedback_date_start` date DEFAULT '0000-00-00' COMMENT '問券回饋起始時間',
  `feedback_date_end` date DEFAULT '0000-00-00' COMMENT '問券回饋結束時間',
  `feedback_choice_ids` varchar(127) NOT NULL DEFAULT '[]' COMMENT '年度問券回饋選擇題id',
  `feedback_question_ids` varchar(63) NOT NULL DEFAULT '[]' COMMENT '年度問券回饋問答題id',
  `assessment_status` int(2) NOT NULL DEFAULT '0' COMMENT '年考評提交狀態',
  `assessment_addition_day` int(6) DEFAULT '7' COMMENT '年考評提交天數',
  `assessment_date_start` date DEFAULT '0000-00-00' COMMENT '年考評起始日期',
  `assessment_date_end` date DEFAULT '0000-00-00' COMMENT '年考評結束日期',
  `assessment_ids` varchar(255) NOT NULL DEFAULT '[]' COMMENT '年考評題目id',
  `update_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cYear` (`year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of rv_year_performance_config_cyclical
-- ----------------------------
