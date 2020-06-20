/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50617
Source Host           : localhost:3306
Source Database       : new_hr_qa2

Target Server Type    : MYSQL
Target Server Version : 50617
File Encoding         : 65001

Date: 2017-04-18 13:00:36
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `rv_department`
-- ----------------------------
DROP TABLE IF EXISTS `rv_department`;
CREATE TABLE `rv_department` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lv` int(11) NOT NULL DEFAULT '0',
  `unit_id` varchar(6) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `supervisor_staff_id` int(11) NOT NULL DEFAULT '0',
  `manager_staff_id` int(11) NOT NULL DEFAULT '0',
  `duty_shift` int(11) DEFAULT NULL,
  `upper_id` int(11) NOT NULL DEFAULT '1',
  `enable` int(2) DEFAULT '1' COMMENT '啟用=1,關閉=0',
  `update_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of rv_department
-- ----------------------------
INSERT INTO `rv_department` VALUES ('1', '1', 'A00', '運維中心', '1', '1', '0', '0', '1', '2017-04-06 03:49:52');
INSERT INTO `rv_department` VALUES ('2', '2', 'B00', '架構發展事業部', '1', '2', '0', '1', '1', '0000-00-00 00:00:00');
INSERT INTO `rv_department` VALUES ('3', '2', 'F00', '稽核部', '1', '0', '0', '1', '1', '2017-04-06 04:11:54');
INSERT INTO `rv_department` VALUES ('4', '2', 'G00', '風險管理部', '1', '0', '0', '1', '1', '0000-00-00 00:00:00');
INSERT INTO `rv_department` VALUES ('5', '2', 'D00', '營運系統部', '1', '0', '0', '1', '1', '0000-00-00 00:00:00');
INSERT INTO `rv_department` VALUES ('6', '2', 'C00', '客戶服務部', '1', '5', '0', '1', '1', '2017-03-29 14:22:02');
INSERT INTO `rv_department` VALUES ('7', '3', 'B20', '總務行政處', '2', '0', '0', '2', '1', '2017-04-11 02:58:16');
INSERT INTO `rv_department` VALUES ('8', '3', 'D10', '系統管理處', '1', '0', '0', '5', '1', '0000-00-00 00:00:00');
INSERT INTO `rv_department` VALUES ('9', '3', 'D50', '開發處', '1', '0', '0', '5', '1', '2017-03-29 14:41:08');
INSERT INTO `rv_department` VALUES ('10', '3', 'G10', '風險管理處', '1', '0', '0', '4', '1', '0000-00-00 00:00:00');
INSERT INTO `rv_department` VALUES ('11', '3', 'C10', '客戶服務處', '5', '0', '0', '6', '1', '0000-00-00 00:00:00');
INSERT INTO `rv_department` VALUES ('12', '3', 'D30', '資料庫管理處', '1', '0', '0', '5', '1', '2017-04-10 10:44:05');
INSERT INTO `rv_department` VALUES ('13', '3', 'B10', '人力資源處', '2', '0', '0', '2', '1', '0000-00-00 00:00:00');
INSERT INTO `rv_department` VALUES ('14', '3', 'D20', '技術支援處', '1', '0', '0', '5', '1', '0000-00-00 00:00:00');
INSERT INTO `rv_department` VALUES ('15', '3', 'F10', '稽查訓練處', '1', '85', '0', '3', '1', '0000-00-00 00:00:00');
INSERT INTO `rv_department` VALUES ('16', '4', 'D31', '資料庫管理組', '1', '71', '0', '12', '1', '2017-04-10 10:33:33');
INSERT INTO `rv_department` VALUES ('17', '4', 'C12', '值班客服組', '5', '15', '1', '11', '1', '2017-04-14 00:03:44');
INSERT INTO `rv_department` VALUES ('18', '4', 'D26', '值班技術四組', '1', '70', '0', '14', '1', '0000-00-00 00:00:00');
INSERT INTO `rv_department` VALUES ('19', '4', 'D23', '值班技術一組', '1', '54', '1', '14', '1', '2017-04-14 00:03:51');
INSERT INTO `rv_department` VALUES ('20', '4', 'C11', '專屬客服組', '5', '7', '0', '11', '1', '0000-00-00 00:00:00');
INSERT INTO `rv_department` VALUES ('21', '4', 'D11', '系統管理組', '1', '0', '0', '8', '1', '0000-00-00 00:00:00');
INSERT INTO `rv_department` VALUES ('22', '4', 'D24', '值班技術二組', '1', '66', '0', '14', '1', '2017-03-29 14:39:25');
INSERT INTO `rv_department` VALUES ('23', '4', 'D51', '開發組', '1', '80', '0', '9', '1', '2017-03-29 14:40:48');
INSERT INTO `rv_department` VALUES ('24', '4', 'C13', '聊天管理組', '5', '33', '0', '11', '1', '0000-00-00 00:00:00');
INSERT INTO `rv_department` VALUES ('25', '4', 'D25', '值班技術三組', '1', '68', '0', '14', '1', '0000-00-00 00:00:00');
