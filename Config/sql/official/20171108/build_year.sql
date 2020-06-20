/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50617
Source Host           : localhost:3306
Source Database       : new_hr_qa3

Target Server Type    : MYSQL
Target Server Version : 50617
File Encoding         : 65001

Date: 2017-11-08 18:38:58
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `rv_attendance_special`
-- ----------------------------
DROP TABLE IF EXISTS `rv_attendance_special`;
CREATE TABLE `rv_attendance_special` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) NOT NULL COMMENT '員工id',
  `date` date DEFAULT NULL COMMENT '日期',
  `year` int(4) DEFAULT '0' COMMENT '年戳',
  `type` int(2) NOT NULL COMMENT '類型id',
  `value` int(11) DEFAULT '0' COMMENT '數值內容',
  `value_char` varchar(1024) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '字符內容',
  `remark` varchar(255) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '備註欄',
  `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '建立日期',
  PRIMARY KEY (`id`),
  KEY `type` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of rv_attendance_special
-- ----------------------------

-- ----------------------------
-- Table structure for `rv_email_template`
-- ----------------------------

DROP TABLE IF EXISTS `rv_email_template`;
CREATE TABLE `rv_email_template` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(55) NOT NULL COMMENT '名稱',
  `title` varchar(255) NOT NULL DEFAULT '標題' COMMENT '標題',
  `text` text NOT NULL COMMENT '模板',
  `update_operatinger_id` int(11) NOT NULL DEFAULT '0' COMMENT '操作人員的id',
  `update_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of rv_email_template
-- ----------------------------
INSERT INTO `rv_email_template` VALUES ('1', 'monthly_start', '{year}年{month}月  績效評核通知', '<h3>您好：</h3><p>本月份績效評核作業已開啟，考勤區間為上月<font color=\"red\">{day_start}日</font>至本月<font color=\"red\">{day_end}日</font>止，請各主管儘速於 <font color=\"red\">{cut_off_date}</font> 前完成評核作業，謝謝！</p>\r\n<p>入口網址：<a href=\"http://{URL}\">{URL}</a></p><p>帳 號：員編</p><p>密 碼：身分證字號（預設）</p><br><br><p>人力資源處</p>', '0', '2017-04-11 16:15:29');
INSERT INTO `rv_email_template` VALUES ('2', 'monthly_return', '【駁回通知】{year}年{month}月 {unit_id} {unit_name} 績效評核表已駁回', '<h3>您好：</h3><p>本月份送審之 【 {unit_id} {unit_name} 】績效評核表已駁回，請儘速完成評核作業，謝謝！</p><p>入口網址： <a href=\"http://{URL}\">{URL}</a></p><p>帳     號：員編</p><p>密     碼：身分證字號（預設）</p><br><br> <p>人力資源處</p>', '0', '2017-04-11 19:29:47');
INSERT INTO `rv_email_template` VALUES ('3', 'monthly_arrive', '【考評通知】{year}年{month}月 {unit_id} {unit_name} 績效評核表已送達', '<h3>您好：</h3><p>本月份送審之【 {unit_id} {unit_name} 】績效評核表已送達至您，請儘速完成評核作業，謝謝！</p><p>入口網址： <a href=\"http://{URL}\">{URL}</a></p><p>帳     號：員編</p><p>密     碼：身分證字號（預設）</p><br><br><p>人力資源處</p>', '0', '2017-04-11 19:28:51');
INSERT INTO `rv_email_template` VALUES ('4', 'monthly_pause', '【考評暫停通知】{year}年{month}月 績效評核表 暫時關閉', '<h3>您好：</h3><p>本月份送審之績效評核表暫時關閉，如有不便之處請見諒，謝謝！</p><br><br><br><p>人力資源處</p>', '0', '2017-04-11 18:35:57');
INSERT INTO `rv_email_template` VALUES ('5', 'monthly_delay', '【考評通知】{year}年{month}月 績效評核表', '<h3>您好：</h3><p>本月份送審之績效評核表已到截止時間 <font color=\"red\"> ( {cut_off_date} ) </font>，請儘速完成評核作業，謝謝！</p><p>入口網址： <a href=\"http://{URL}\">{URL}</a></p><p>帳     號：員編</p><p>密     碼：身分證字號（預設）</p><br> <br><p>人力資源處</p>', '0', '2017-04-11 21:15:43');
INSERT INTO `rv_email_template` VALUES ('9', 'monthly_assessment_finish', '【考評核准通知】{year}年{month}月 績效評核表', '<h3>您好：</h3><p>此月份考評單已經全部核准 </p> <p>特此通知</p>', '0', '2017-08-04 17:43:15');
INSERT INTO `rv_email_template` VALUES ('10', 'monthly_draw', '【考評通知】{year}年{month}月 {department} 績效評核表抽單', '<h3>您好：</h3><p>本月份送審之【 {department} 】績效評核表已被 {staff_name} 給抽回</p><p> 入口網址: <a target=\"_blank\" href=\"http://{URL}\">{URL}</a> </p> <p> 帳     號：員編 </p> <p>密     碼：身分證字號（預設） </p> <br><br> <p>人力資源處</p>', '0', '2017-08-25 11:05:28');
INSERT INTO `rv_email_template` VALUES ('11', 'yearly_assessment_staff_commit', '【年度考評 - 送達通知】{year}年 ** {unit_id}{department_name} {staff_name_en} {staff_name} ** 年度考核表已提交', '<h3>您好：</h3><p> {year} 年度 {unit_id}{department_name} {staff_name_en} {staff_name} 送審之年度考核表已抵達您的手中，請儘速完成評核作業，謝謝！<p> 入口網址: <a target=\"_blank\" href=\"{URL}\">{URL}</a> </p> <p> 帳     號：員編 </p> <p>密     碼：身分證字號（預設） </p> <br><br> <p>人力資源處</p>', '0', '2017-11-09 11:58:08');
INSERT INTO `rv_email_template` VALUES ('12', 'yearly_assessment_to_director_commit', '【年度考評 - 送達通知】{year}年【{unit_id}{division_name}】年度考核表已全數收齊', '<h3>您好：</h3><p> {year} 年度 {unit_id}{division_name} 送審之年度考核表已全數抵達部門單位，特此通知，請等待所有部門將個人考核表完成，謝謝！</p> <br><br> <p>人力資源處</p>', '0', '2017-11-09 11:25:35');
INSERT INTO `rv_email_template` VALUES ('13', 'yearly_division_to_consturct', '【年度考評 - 待確認通知】{year}年【{unit_id}{division_name}】年度考核表', '<h3>您好：</h3><p> {year} 年度 {unit_id}{division_name}  送審之年度考核表已送達 {owner_staff_name} {owner_staff_name_en} 的手中，請儘速完成確認作業，謝謝！<p> 入口網址: <a target=\"_blank\" href=\"{URL}\">{URL}</a> </p> <p> 帳     號：員編 </p> <p>密     碼：身分證字號（預設） </p> <br><br> <p>人力資源處</p>', '0', '2017-11-09 11:24:59');
INSERT INTO `rv_email_template` VALUES ('14', 'yearly_division_to_ceo', '【年度考評 - 待批准通知】{year}年【{unit_id}{division_name}】年度考核表', '<h3>您好：</h3><p> {year} 年度 {unit_id}{division_name}  送審之年度考核表已抵達 {owner_staff_name} {owner_staff_name_en} 的手中，請儘速完成評核作業，謝謝！<p> 入口網址: <a target=\"_blank\" href=\"{URL}\">{URL}</a> </p> <p> 帳     號：員編 </p> <p>密     碼：身分證字號（預設） </p> <br><br> <p>人力資源處</p>', '0', '2017-11-09 11:25:05');
INSERT INTO `rv_email_template` VALUES ('15', 'yearly_division_to_system', '【年度考評 - 批准通知】{year}年【{unit_id}{division_name}】年度考核表', '<h3>您好：</h3><p> {year} 年度 {unit_id}{division_name} 之年度考核已全數完成，特此通知！<p> 入口網址: <a target=\"_blank\" href=\"{URL}\">{URL}</a> </p> <p> 帳     號：員編 </p> <p>密     碼：身分證字號（預設） </p> <br><br> <p>人力資源處</p>', '0', '2017-11-09 11:25:11');
INSERT INTO `rv_email_template` VALUES ('16', 'yearly_assessment_to_reject_to_staff', '【年度考評 - 駁回通知】{year} 年 **{unit_id}{department_name} {staff_name_en} {staff_name}** 的年度考核表已駁回', '<h3>您好：</h3><p> {year} 年度 {unit_id}{department_name}  {staff_name_en} {staff_name} 送審之年度考核表已駁回，請儘速完成評核作業，謝謝！</p> <p>駁回原因：<span>{reason}</span></p>入口網址: <a target=\"_blank\" href=\"{URL}\">{URL}</a> </p> <p> 帳     號：員編 </p> <p>密     碼：身分證字號（預設） </p> <br><br> <p>人力資源處</p>', '0', '2017-11-09 11:10:36');
INSERT INTO `rv_email_template` VALUES ('17', 'yearly_division_reject_to_director', '【年度考評 - 駁回通知】{year}年【{unit_id}{division_name}】年度考核表', '<h3>您好：</h3><p> {year} 年度 {unit_id}{division_name} 送審之年度部門單位考核表已駁回 {owner_staff_name} {owner_staff_name_en} 的手中，請儘速完成評核作業，謝謝！<p> 入口網址: <a target=\"_blank\" href=\"{URL}\">{URL}</a> </p> <p> 帳     號：員編 </p> <p>密     碼：身分證字號（預設） </p> <br><br> <p>人力資源處</p>', '0', '2017-11-09 11:24:32');
INSERT INTO `rv_email_template` VALUES ('18', 'yearly_assessment_to_delay', '【年度考評通知】 {year} 年 **{unit_id}{department_name} {name_en} {name}** 年度考核表', '<h3>您好：</h3><p> {year} 年度考核作業已過截止時間 ( {assessment_date_end} ) ，請儘速完成評核作業，謝謝！<p> 入口網址: <a target=\"_blank\" href=\"{URL}\">{URL}</a> </p> <p> 帳     號：員編 </p> <p>密     碼：身分證字號（預設） </p> <br><br> <p>人力資源處</p>', '0', '2017-11-07 17:28:37');
INSERT INTO `rv_email_template` VALUES ('19', 'yearly_feedback_launch', '【年度考評通知】 {year} 年部屬回饋問卷調查。', '<h3>您好：</h3><p>本年度「部屬回饋問卷調查」已開始進行，敬請同仁儘速於<font color=\"red\">{day_end}</font>前完成問卷調查，謝謝！</p>\r\n<p>入口網址：<a href=\"http://{URL}\">{URL}</a></p><p>帳 號：員編</p><p>密 碼：身分證字號（預設）</p><br><br><p>人力資源處</p>', '0', '2017-11-06 19:16:49');
INSERT INTO `rv_email_template` VALUES ('20', 'yearly_feedback_close', '【年度考評通知】 {year} 年部屬回饋問卷調查已經停止。', '<h3>您好：</h3><p>本年度「部屬回饋問卷調查」已停止，暫時不再接收部屬回饋之意見，謝謝！</p><br><br><p>人力資源處</p>', '0', '2017-11-06 20:07:41');
INSERT INTO `rv_email_template` VALUES ('21', 'yearly_feedback_commit', '【年度考評通知】 {year} 年 ** {name_en} {name} ** 的部屬回饋問卷調查已提交。', '<p>本年度「部屬回饋問卷調查」由 【{name_en} {name}】 對 【{target_name_en} {target_name}】的問券已經提交，</p><br><br><p>人力資源處</p>', '0', '2017-11-07 11:31:56');
INSERT INTO `rv_email_template` VALUES ('22', 'yearly_feedback_delay', '【年度考評通知】 {year} 年 部屬回饋問卷尚未提交。', '<h3>您好：</h3><p>本年度「部屬回饋問卷調查」已至結束日〈<font color=\"red\">{day_end}</font>〉，敬請同仁儘速完成問卷調查，謝謝！</p>\r\n<p>入口網址：<a href=\"http://{URL}\">{URL}</a></p><p>帳 號：員編</p><p>密 碼：身分證字號（預設）</p><br><br><p>人力資源處</p>', '0', '2017-11-07 16:16:01');
INSERT INTO `rv_email_template` VALUES ('23', 'yearly_assessment_launch', '【年度考評通知】 {year} 年度績效考核作業開啟。', '<h3>您好：</h3><p>本年度「績效考核作業」已開始進行，敬請同仁儘速完成自評作業；</p><p>各部門考核作業請於{year}年{month}月{day}日前完成，謝謝！</p><p>【人事考評系統】</p>\r\n<p>入口網址：<a href=\"http://{URL}\">{URL}</a></p><p>帳 號：員編</p><p>密 碼：身分證字號（預設）</p><br><br><p>人力資源處</p>', '0', '2017-11-08 19:48:26');
INSERT INTO `rv_email_template` VALUES ('24', 'yearly_assessment_pause', '【年度考評通知】 {year} 年度績效考核作業暫停。', '<h3>您好：</h3><p>本年度「績效考核作業」已暫停</p><p>各部門考核作業暫時無法操作，敬請見諒！</p><br><br><p>人力資源處</p>', '0', '2017-11-08 19:49:53');
INSERT INTO `rv_email_template` VALUES ('25', 'yearly_assessment_all_report_done', '【年度考評通知】 {year} 年度績效考核作業 部門單位評核。', '<h3>您好：</h3><p>本年度「績效考核作業」個人考核表已全數完成。</p><p>開始進入部門級別單位之加減分，請盡速完成各部門考核作業，謝謝！</p><p> 入口網址: <a target=\"_blank\" href=\"{URL}\">{URL}</a> </p> <p> 帳     號：員編 </p> <p>密     碼：身分證字號（預設） </p> <br><br> <p>人力資源處</p>', '0', '2017-11-09 10:08:04');
INSERT INTO `rv_email_template` VALUES ('26', 'yearly_division_done', '【年度考評 - 批准通知】{year} 年 年度考核已全數批准 ', '<h3>您好：</h3><p> {year} 年度之年度考核已全數批准，請儘速完成作業，謝謝！<p> 入口網址: <a target=\"_blank\" href=\"{URL}\">{URL}</a> </p> <p> 帳     號：員編 </p> <p>密     碼：身分證字號（預設） </p> <br><br> <p>人力資源處</p>', '0', '2017-11-09 10:35:23');

-- ----------------------------
-- Table structure for `rv_record_admin`
-- ----------------------------
DROP TABLE IF EXISTS `rv_record_admin`;
CREATE TABLE `rv_record_admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `operating_staff_id` int(11) NOT NULL DEFAULT '0',
  `type` int(6) NOT NULL COMMENT '操作類型',
  `doing` int(2) NOT NULL COMMENT '操作動作 1=add,2=update,3=delete',
  `api` varchar(255) COLLATE utf8_unicode_ci DEFAULT '' COMMENT 'API請求URL',
  `changed_json` varchar(1024) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '改變數值JSON',
  `update_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `ip` varchar(64) COLLATE utf8_unicode_ci DEFAULT 'Undefined' COMMENT '紀錄操作IP',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of rv_record_admin
-- ----------------------------

-- ----------------------------
-- Table structure for `rv_record_year_performance_divisions`
-- ----------------------------
DROP TABLE IF EXISTS `rv_record_year_performance_divisions`;
CREATE TABLE `rv_record_year_performance_divisions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `operating_staff_id` int(11) NOT NULL DEFAULT '0',
  `division_id` int(11) NOT NULL COMMENT '年考績 部門單的id',
  `type` int(2) NOT NULL COMMENT '年考績記錄類型 1=save, 2=commit, 3=agree, 4=return, 5=other, ',
  `origin_json` text COLLATE utf8_unicode_ci COMMENT '年考績原始欄位資料',
  `changed_json` text COLLATE utf8_unicode_ci COMMENT '改變的內容',
  `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '建立日期',
  PRIMARY KEY (`id`),
  KEY `division_id` (`division_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of rv_record_year_performance_divisions
-- ----------------------------

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
  `from_staff_id` int(11) NOT NULL DEFAULT '0' COMMENT '問答/評論來源，0=不記名人員',
  `content` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '評論的內容',
  `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '評論建立日期',
  PRIMARY KEY (`id`),
  KEY `target_staff_id` (`target_staff_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of rv_record_year_performance_questions
-- ----------------------------

-- ----------------------------
-- Table structure for `rv_record_year_performance_report`
-- ----------------------------
DROP TABLE IF EXISTS `rv_record_year_performance_report`;
CREATE TABLE `rv_record_year_performance_report` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `operating_staff_id` int(11) NOT NULL DEFAULT '0',
  `report_id` int(11) NOT NULL COMMENT '年考績的id',
  `type` int(2) NOT NULL COMMENT '年考績記錄類型 1=save, 2=commit, 3=agree, 4=return, 5=other, ',
  `origin_json` text COLLATE utf8_unicode_ci COMMENT '年考績原始欄位資料',
  `changed_json` text COLLATE utf8_unicode_ci COMMENT '改變的內容',
  `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '建立日期',
  PRIMARY KEY (`id`),
  KEY `report_id` (`report_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of rv_record_year_performance_report
-- ----------------------------

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

-- ----------------------------
-- Table structure for `rv_year_performance_feedback`
-- ----------------------------
DROP TABLE IF EXISTS `rv_year_performance_feedback`;
CREATE TABLE `rv_year_performance_feedback` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `year` int(4) NOT NULL,
  `staff_id` int(11) NOT NULL DEFAULT '0' COMMENT '員工代號 rv_staff.id',
  `staff_title_id` int(2) NOT NULL DEFAULT '0' COMMENT '員工職類id',
  `staff_title` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0' COMMENT '員工職類',
  `department_id` int(11) NOT NULL COMMENT 'department.id',
  `status` int(2) NOT NULL DEFAULT '0' COMMENT '狀態 0=未提交, 1=已提交',
  `target_staff_id` int(11) NOT NULL DEFAULT '0' COMMENT '目標staff_id, 0=公司',
  `target_staff_title_id` int(2) NOT NULL DEFAULT '0' COMMENT '目標職類id',
  `target_staff_title` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0' COMMENT '目標職類',
  `multiple_choice_json` varchar(1020) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '單選題的結果',
  `multiple_total` int(4) DEFAULT '0' COMMENT '單選題總分',
  `multiple_score` int(4) DEFAULT '0' COMMENT '單選題總分',
  `update_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `year` (`year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of rv_year_performance_feedback
-- ----------------------------

-- ----------------------------
-- Table structure for `rv_year_performance_feedback_multiple_choice`
-- ----------------------------
DROP TABLE IF EXISTS `rv_year_performance_feedback_multiple_choice`;
CREATE TABLE `rv_year_performance_feedback_multiple_choice` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(63) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '選擇題標題',
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '選擇題描述',
  `sort` int(6) NOT NULL DEFAULT '1' COMMENT '排序設定 asc',
  `options_json` varchar(1020) COLLATE utf8_unicode_ci DEFAULT '{}' COMMENT '選擇題選項json',
  `score` int(4) NOT NULL DEFAULT '10' COMMENT '題目分數總額',
  `enable` int(2) NOT NULL DEFAULT '1' COMMENT '開啟狀態 1=on,0=off',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of rv_year_performance_feedback_multiple_choice
-- ----------------------------
INSERT INTO `rv_year_performance_feedback_multiple_choice` VALUES ('1', '專業能力', '', '1', '[{\"name\":\"具卓越的專業知識，能運用相關資源解決部屬的問題，教導並激發部屬解決問題的潛力。\",\"percent\":100}, {\"name\":\"專業知識足以解決部屬大部份問題，或能與部屬討論更佳解決方案。\",\"percent\":60}, {\"name\":\"專業知識無法應付部屬的疑難，或不願分享知識給部屬。\",\"percent\":20}]', '5', '1');
INSERT INTO `rv_year_performance_feedback_multiple_choice` VALUES ('2', '責任感', '', '2', '[{\"name\":\"主管能對自己的決策結果負起100%的責任，有擔當不推諉給部屬，而對於部屬的功勞會大力讚揚。\",\"percent\":100}, {\"name\":\"主管能對決策結果負起80%責任，但亦會要求部屬共同承擔，屬於部屬之功勞會予以讚賞。\",\"percent\":60}, {\"name\":\"主管對決策結果無法負責，或時常推諉給部屬，或將部屬之功勞歸在自己。\",\"percent\":20}]', '5', '1');
INSERT INTO `rv_year_performance_feedback_multiple_choice` VALUES ('3', '解決問題', '', '3', '[{\"name\":\"主管能傾聽與解決部屬問題，並促進部屬站在更宏觀的角度思考，有效提升部屬思維能力。\",\"percent\":100}, {\"name\":\"主管能傾聽部屬的問題，並能提供建設性解決辦法。\",\"percent\":60}, {\"name\":\"主管無法理解部屬的難處，或對部屬提出之疑問置之不理，或提供之辦法毫無建設性。\",\"percent\":20}]', '5', '1');
INSERT INTO `rv_year_performance_feedback_multiple_choice` VALUES ('4', '自律性', '', '4', '[{\"name\":\"對公司各項政策及規定都有確實宣導，以身作則並要求部屬遵守。\",\"percent\":100}, {\"name\":\"對公司各項政策及規定大致有宣導，儘可能努力達成。\",\"percent\":60}, {\"name\":\"甚少理會公司政策及規定且態度放任。\",\"percent\":20}]', '5', '0');
INSERT INTO `rv_year_performance_feedback_multiple_choice` VALUES ('5', '公正性', '', '5', '[{\"name\":\"對部屬一視同仁，部屬成功達成任務會加以讚揚；任務失敗則會予以鼓勵且給予適當的處分勉勵。\",\"percent\":100}, {\"name\":\"主管有偏好某些部屬，但尚能公平以待，不致造成部屬間產生嫌隙。\",\"percent\":60}, {\"name\":\"主管會偏袒某些部屬，無法給予公平待遇，或造成部屬間嫌隙，或對部門紛爭視若無睹。\",\"percent\":20}]', '5', '1');
INSERT INTO `rv_year_performance_feedback_multiple_choice` VALUES ('6', '溝通能力', '', '6', '[{\"name\":\"能處理衝突、整合意見，讓部屬成員達成共識。\",\"percent\":100}, {\"name\":\"具基本溝通技巧進行建設性的溝通。\",\"percent\":60}, {\"name\":\"僅能單向溝通且傳達訊息常常不夠清楚與充分。\",\"percent\":20}]', '5', '1');
INSERT INTO `rv_year_performance_feedback_multiple_choice` VALUES ('7', '團隊建立', '', '7', '[{\"name\":\"除了能凝聚部門向心力，亦能有效進行跨部門合作，進而促進公司整體同仁向心力。\",\"percent\":100}, {\"name\":\"能鼓勵成員發表不同意見、或整合部門同仁意見達成共識，凝聚部門向心力。\",\"percent\":60}, {\"name\":\"無法使同仁了解各自任務、角色及使命，或漠視團隊成員衝突。\",\"percent\":20}]', '5', '1');
INSERT INTO `rv_year_performance_feedback_multiple_choice` VALUES ('8', '情緒管理', '', '8', '[{\"name\":\"主管遇事能平緩自己與他人的負面情緒，以正向態度冷靜理性的解決問題。\",\"percent\":100}, {\"name\":\"主管甚少發脾氣，或從未辱罵部屬，或尚能以最快速度平復情緒，協同部屬解決問題。\",\"percent\":60}, {\"name\":\"主管情緒管裡不佳，或時常辱罵部屬，無法快速跳脫負面情緒。\",\"percent\":20}]', '5', '1');
INSERT INTO `rv_year_performance_feedback_multiple_choice` VALUES ('9', '彈性創新', '', '9', '[{\"name\":\"鼓勵部屬提出新作為或新方法，並與部屬評估討論後，有效的執行，進而提升部門運作。\",\"percent\":100}, {\"name\":\"對於部屬的新發想、改善方式持正面能度，且願意一試。\",\"percent\":60}, {\"name\":\"主管態度保守，或較少聽取部屬提供的改善方法，或不願改變。\",\"percent\":20}]', '5', '1');
INSERT INTO `rv_year_performance_feedback_multiple_choice` VALUES ('10', '信任授權', '', '10', '[{\"name\":\"主管願意授權部屬工作及挑戰機會，信任部屬能達成任務，且時常關心並支持部屬。\",\"percent\":100}, {\"name\":\"主管多能授權部屬工作，但部屬會感受到主管信任程度不足。\",\"percent\":60}, {\"name\":\"許多工作都集中在主管身上，或主管卻無法分配工作，或不願讓部屬協助分擔。\",\"percent\":20}]', '5', '1');
INSERT INTO `rv_year_performance_feedback_multiple_choice` VALUES ('11', '自律性', '', '4', '[{\"name\":\"對公司各項政策及規定都有確實宣導，以身作則並要求部屬遵守。\",\"percent\":100}, {\"name\":\"對公司各項政策及規定大致有宣導，儘可能努力達成。\",\"percent\":60}, {\"name\":\"甚少理會公司政策及規定且態度放任。\",\"percent\":20}]', '5', '1');

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
INSERT INTO `rv_year_performance_feedback_questions` VALUES ('6', 'company', '建議', '其它建議：（針對公司，是否還有其它您想特別說明/補充呢？）', '5', '1');

-- ----------------------------
-- Table structure for `rv_year_performance_report`
-- ----------------------------
DROP TABLE IF EXISTS `rv_year_performance_report`;
CREATE TABLE `rv_year_performance_report` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `year` int(4) NOT NULL COMMENT '年份',
  `staff_id` int(11) NOT NULL COMMENT 'staff.id',
  `owner_staff_id` int(11) NOT NULL COMMENT '當前擁有者',
  `department_id` int(11) NOT NULL COMMENT 'department.id',
  `division_id` int(11) NOT NULL COMMENT '部門單位的ID',
  `staff_is_leader` int(2) NOT NULL COMMENT '員工當時是否為主管',
  `staff_lv` int(2) NOT NULL COMMENT '員工當時lv',
  `staff_post` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT '職務',
  `staff_title` varchar(64) COLLATE utf8_unicode_ci NOT NULL COMMENT '職務類別',
  `staff_title_id` int(2) NOT NULL COMMENT '職務類別id',
  `enable` int(2) NOT NULL DEFAULT '1' COMMENT '啟用狀態 1=正常, 0=作廢',
  `processing_lv` int(2) NOT NULL DEFAULT '5' COMMENT '進程 部門lv 用來判斷交到哪一層了',
  `path` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT '[]' COMMENT '考績會經由哪幾個 staff.id 手上',
  `path_lv` varchar(128) COLLATE utf8_unicode_ci NOT NULL DEFAULT '{}' COMMENT '考績每一層 部門等級對應的 主管id與單位id',
  `before_level` varchar(6) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'A' COMMENT '去年的考績等級',
  `monthly_average` float(5,2) NOT NULL DEFAULT '0.00' COMMENT '月考評平均值',
  `attendance_json` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '{"late":0,"early":0,"nocard":0,"leave":0,"paysick":0,"physiology":0,"sick":0,"absent":0}' COMMENT '出缺勤json',
  `assessment_json` varchar(2048) COLLATE utf8_unicode_ci NOT NULL DEFAULT '{}' COMMENT '考積分數json {"[under|self|lv]":{"percent":(int),"total":(int),"score":[id:score,...]}',
  `sign_json` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '{}' COMMENT '簽名時間戳',
  `assessment_total` float(5,2) NOT NULL DEFAULT '0.00' COMMENT '考績結算總分',
  `assessment_total_division_change` int(4) DEFAULT '0' COMMENT '架構發展者 加減分',
  `assessment_total_ceo_change` int(4) DEFAULT '0' COMMENT '決策者/執行長 加減分',
  `level` varchar(6) COLLATE utf8_unicode_ci NOT NULL DEFAULT '-' COMMENT '今年的考績等級',
  `self_contribution` varchar(1020) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '自己在公司的貢獻描述',
  `self_improve` varchar(1020) COLLATE utf8_unicode_ci DEFAULT '' COMMENT '自己在公司需要改善的描述',
  `upper_comment` varchar(2040) COLLATE utf8_unicode_ci DEFAULT '{1:{"staff_id":1,"content":""},2:{"staff_id":19,"content":""}}' COMMENT '上層主管們的評論',
  `reason` varchar(512) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '更改的理由',
  `update_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `year` (`year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of rv_year_performance_report
-- ----------------------------

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

-- ----------------------------
-- Table structure for `rv_year_performance_report_percents`
-- ----------------------------
DROP TABLE IF EXISTS `rv_year_performance_report_percents`;
CREATE TABLE `rv_year_performance_report_percents` (
  `id` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `lv` int(2) NOT NULL COMMENT '組織階層',
  `type` int(2) NOT NULL DEFAULT '1' COMMENT '適用對象 1=主管,2=一般人員',
  `percent_json` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '分數配率百分比 {lv:percent,..}',
  `enable` int(2) NOT NULL DEFAULT '1' COMMENT '開啟狀態 1=on,0=off',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of rv_year_performance_report_percents
-- ----------------------------
INSERT INTO `rv_year_performance_report_percents` VALUES ('1', '1', '1', '{\"_0\":40,\"_1\":0,\"_2\":0,\"_3\":0,\"_4\":0,\"_5\":0,\"_6\":0,\"_\":20}', '1');
INSERT INTO `rv_year_performance_report_percents` VALUES ('3', '2', '1', '{\"_0\":40,\"_1\":40,\"_2\":0,\"_3\":0,\"_4\":0,\"_5\":0,\"_6\":0,\"_\":20}', '1');
INSERT INTO `rv_year_performance_report_percents` VALUES ('4', '4', '1', '{\"_0\":40,\"_1\":0,\"_2\":30,\"_3\":20,\"_4\":0,\"_5\":0,\"_6\":0,\"_\":10}', '1');
INSERT INTO `rv_year_performance_report_percents` VALUES ('5', '3', '1', '{\"_0\":40,\"_1\":0,\"_2\":40,\"_3\":0,\"_4\":0,\"_5\":0,\"_6\":0,\"_\":20}', '1');
INSERT INTO `rv_year_performance_report_percents` VALUES ('6', '5', '1', '{\"_0\":40,\"_1\":0,\"_2\":20,\"_3\":20,\"_4\":20,\"_5\":0,\"_6\":0,\"_\":0}', '1');
INSERT INTO `rv_year_performance_report_percents` VALUES ('7', '1', '2', '{\"_0\":40,\"_1\":60,\"_2\":0,\"_3\":0,\"_4\":0,\"_5\":0,\"_6\":0,\"_\":0}', '1');
INSERT INTO `rv_year_performance_report_percents` VALUES ('8', '2', '2', '{\"_0\":40,\"_1\":0,\"_2\":60,\"_3\":0,\"_4\":0,\"_5\":0,\"_6\":0,\"_\":0}', '1');
INSERT INTO `rv_year_performance_report_percents` VALUES ('9', '3', '2', '{\"_0\":40,\"_1\":0,\"_2\":20,\"_3\":40,\"_4\":0,\"_5\":0,\"_6\":0,\"_\":0}', '1');
INSERT INTO `rv_year_performance_report_percents` VALUES ('10', '4', '2', '{\"_0\":40,\"_1\":0,\"_2\":20,\"_3\":20,\"_4\":20,\"_5\":0,\"_6\":0,\"_\":0}', '1');

-- ----------------------------
-- Table structure for `rv_year_performance_report_topic`
-- ----------------------------
DROP TABLE IF EXISTS `rv_year_performance_report_topic`;
CREATE TABLE `rv_year_performance_report_topic` (
  `id` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `type` int(6) NOT NULL COMMENT 'type id',
  `name` varchar(16) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '題目名稱',
  `score` int(4) NOT NULL COMMENT '分數',
  `score_leader` int(4) NOT NULL COMMENT '主管分數',
  `sort` int(4) NOT NULL DEFAULT '1' COMMENT '排序 asc',
  `enable` int(2) NOT NULL DEFAULT '1' COMMENT '開啟狀態 1=on,0=off',
  `applicable` enum('normal','leader','both') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'both' COMMENT '題目的適用範圍',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of rv_year_performance_report_topic
-- ----------------------------
INSERT INTO `rv_year_performance_report_topic` VALUES ('1', '1', '工作效率', '20', '15', '1', '1', 'both');
INSERT INTO `rv_year_performance_report_topic` VALUES ('2', '1', '目標達成', '20', '15', '2', '1', 'both');
INSERT INTO `rv_year_performance_report_topic` VALUES ('3', '1', '績效改善', '20', '15', '3', '1', 'both');
INSERT INTO `rv_year_performance_report_topic` VALUES ('4', '2', '專業知識', '4', '4', '1', '1', 'both');
INSERT INTO `rv_year_performance_report_topic` VALUES ('5', '2', '創新能力', '4', '4', '2', '1', 'both');
INSERT INTO `rv_year_performance_report_topic` VALUES ('6', '2', '學習能力', '4', '4', '3', '1', 'both');
INSERT INTO `rv_year_performance_report_topic` VALUES ('7', '3', '合作協調能力', '4', '4', '1', '1', 'both');
INSERT INTO `rv_year_performance_report_topic` VALUES ('8', '3', '解決問題能力', '4', '4', '2', '1', 'both');
INSERT INTO `rv_year_performance_report_topic` VALUES ('9', '4', '品德操守', '4', '3', '1', '1', 'both');
INSERT INTO `rv_year_performance_report_topic` VALUES ('10', '4', '服務熱忱', '4', '3', '2', '1', 'both');
INSERT INTO `rv_year_performance_report_topic` VALUES ('11', '4', '責任感', '4', '3', '3', '1', 'both');
INSERT INTO `rv_year_performance_report_topic` VALUES ('12', '4', '團隊精神', '4', '3', '4', '1', 'both');
INSERT INTO `rv_year_performance_report_topic` VALUES ('13', '4', '遵守紀律', '4', '3', '5', '1', 'both');
INSERT INTO `rv_year_performance_report_topic` VALUES ('14', '5', '賦能授權 ', '0', '5', '1', '1', 'leader');
INSERT INTO `rv_year_performance_report_topic` VALUES ('15', '5', '溝通輔導', '0', '5', '2', '1', 'leader');
INSERT INTO `rv_year_performance_report_topic` VALUES ('16', '5', '賞罰公平', '0', '5', '3', '1', 'leader');
INSERT INTO `rv_year_performance_report_topic` VALUES ('17', '5', '變革領導', '0', '5', '4', '1', 'leader');

-- ----------------------------
-- Table structure for `rv_year_performance_report_topic_type`
-- ----------------------------
DROP TABLE IF EXISTS `rv_year_performance_report_topic_type`;
CREATE TABLE `rv_year_performance_report_topic_type` (
  `id` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(16) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '題目名稱',
  `sort` int(4) NOT NULL COMMENT '排序 asc',
  `enable` int(2) NOT NULL DEFAULT '1' COMMENT '開啟狀態 1=on,0=off',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of rv_year_performance_report_topic_type
-- ----------------------------
INSERT INTO `rv_year_performance_report_topic_type` VALUES ('1', '工作績效', '1', '1');
INSERT INTO `rv_year_performance_report_topic_type` VALUES ('2', '知識技能', '2', '1');
INSERT INTO `rv_year_performance_report_topic_type` VALUES ('3', '溝通協調', '3', '1');
INSERT INTO `rv_year_performance_report_topic_type` VALUES ('4', '品德及工作態度', '4', '1');
INSERT INTO `rv_year_performance_report_topic_type` VALUES ('5', '管理能力', '5', '1');
