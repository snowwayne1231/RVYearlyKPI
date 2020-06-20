/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50617
Source Host           : localhost:3306
Source Database       : new_hr_qa3

Target Server Type    : MYSQL
Target Server Version : 50617
File Encoding         : 65001

Date: 2017-12-12 20:30:21
*/

SET FOREIGN_KEY_CHECKS=0;

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
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8;

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
INSERT INTO `rv_email_template` VALUES ('11', 'yearly_assessment_staff_commit', '【年度考評 - 送達通知】{year}年 ** {unit_id}{department_name} {staff_name_en} {staff_name} ** 年度考核表已提交', '<h3>您好：</h3><p> 本年度 【{unit_id}{department_name} {staff_name_en} {staff_name} 】之年度考核表已提交送審，敬請儘速完成主管評核作業，謝謝！<p>【人事考評系統】</p><p> 入口網址: <a target=\"_blank\" href=\"{URL}\">{URL}</a> </p> <p> 帳     號：員編 </p> <p>密     碼：身分證字號（預設） </p> <br><br> <p>人力資源處</p>', '0', '2017-12-05 17:36:48');
INSERT INTO `rv_email_template` VALUES ('12', 'yearly_assessment_to_director_commit', '【年度考評通知】{year}年【{unit_id}{division_name}】年度考核表已全數收齊', '<h3>您好：</h3><p> 本年度【 {unit_id}{division_name}】全部同仁之年度考核表已完成部門主管評分，特此通知；另請等待其他部門完成個人考核表，謝謝！\r\n</p> <br><br> <p>人力資源處</p>', '0', '2017-12-05 17:36:08');
INSERT INTO `rv_email_template` VALUES ('13', 'yearly_division_to_consturct', '【年度考評 [沒用了]】{year}年【{unit_id}{division_name}】', '<h3>您好：</h3><p> 本年度 {unit_id}{division_name}  送審之年度考核表已送達 {owner_staff_name} {owner_staff_name_en} 的手中，請儘速完成確認作業，謝謝！<p> 入口網址: <a target=\"_blank\" href=\"{URL}\">{URL}</a> </p> <p> 帳     號：員編 </p> <p>密     碼：身分證字號（預設） </p> <br><br> <p>人力資源處</p>', '0', '2017-12-05 17:32:50');
INSERT INTO `rv_email_template` VALUES ('14', 'yearly_division_to_ceo', '【年度考評 - 待批准通知】{year}年【{unit_id}{division_name}】年度考核表', '<h3>您好：</h3><p> 本年度【{unit_id}{division_name}】之部門單位考核已完成部級調整階段。\r\n開始進行執行長批淮核定階段，請儘速完成評核作業，謝謝！<p>【人事考評系統】</p><p> 入口網址: <a target=\"_blank\" href=\"{URL}\">{URL}</a> </p> <p> 帳     號：員編 </p> <p>密     碼：身分證字號（預設） </p> <br><br> <p>人力資源處</p>', '0', '2017-12-05 17:41:18');
INSERT INTO `rv_email_template` VALUES ('15', 'yearly_division_to_system', '【年度考評 - 批准通知】{year}年【{unit_id}{division_name}】年度考核表', '<h3>您好：</h3><p> 本年度【{unit_id}{division_name} 】之部門單位考核已全數完成，特此通知！</p><br><br> <p>人力資源處</p>', '0', '2017-12-05 17:44:35');
INSERT INTO `rv_email_template` VALUES ('16', 'yearly_assessment_to_reject_to_staff', '【年度考評 - 駁回通知】{year} 年 **{unit_id}{department_name} {staff_name_en} {staff_name}** 的年度考核表已駁回', '<h3>您好：</h3><p> 本年度【 {unit_id}{department_name}  {staff_name_en} {staff_name} 】送審之年度考核表已駁回，敬請儘速完成評核作業，謝謝！</p> <p>駁回原因：<span>{reason}</span><p>【人事考評系統】</p></p>入口網址: <a target=\"_blank\" href=\"{URL}\">{URL}</a> </p> <p> 帳     號：員編 </p> <p>密     碼：身分證字號（預設） </p> <br><br> <p>人力資源處</p>', '0', '2017-12-05 17:28:37');
INSERT INTO `rv_email_template` VALUES ('17', 'yearly_division_reject_to_director', '【年度考評 - 駁回通知】{year}年【{unit_id}{division_name}】年度考核表', '<h3>您好：</h3><p> 本年度 {unit_id}{division_name} 送審之部門單位考核已駁回，請儘速完成評核作業，謝謝！</p><p>【人事考評系統】</p><p> 入口網址: <a target=\"_blank\" href=\"{URL}\">{URL}</a> </p> <p> 帳     號：員編 </p> <p>密     碼：身分證字號（預設） </p> <br><br> <p>人力資源處</p>', '0', '2017-12-11 15:27:13');
INSERT INTO `rv_email_template` VALUES ('18', 'yearly_assessment_to_delay', '【年度考評通知】 {year} 年 **{unit_id}{department_name} {name_en} {name}** 年度考核表', '<h3>您好：</h3><p> 本年度考核作業已過截止時間 ( {assessment_date_end} ) ，敬請儘速完成評核作業，謝謝！</p><p>【人事考評系統】</p><p> 入口網址: <a target=\"_blank\" href=\"{URL}\">{URL}</a> </p> <p> 帳     號：員編 </p> <p>密     碼：身分證字號（預設） </p> <br><br> <p>人力資源處</p>', '0', '2017-12-11 15:27:05');
INSERT INTO `rv_email_template` VALUES ('19', 'yearly_feedback_launch', '【部屬回饋問卷通知】 {year} 年部屬回饋問卷調查', '<h3>您好：</h3><p>本年度「部屬回饋問卷調查」已開始進行，敬請同仁儘速於<font color=\"red\">{day_end}</font>前完成問卷調查，謝謝！</p>\r\n<p>【人事考評系統】</p><p>入口網址：<a href=\"http://{URL}\">{URL}</a></p><p>帳 號：員編</p><p>密 碼：身分證字號（預設）</p><br><br><p>人力資源處</p>', '0', '2017-12-05 11:24:15');
INSERT INTO `rv_email_template` VALUES ('20', 'yearly_feedback_close', '【部屬回饋問卷通知】 {year} 年部屬回饋問卷調查結束', '<h3>您好：</h3><p>本年度「部屬回饋問卷調查」已結束，系統關閉問卷將不再接收部屬回饋之意見，謝謝！</p><br><br><p>人力資源處</p>', '0', '2017-12-05 11:24:32');
INSERT INTO `rv_email_template` VALUES ('21', 'yearly_feedback_commit', '【部屬回饋問卷通知】 {year} 年 ** {name_en} {name} ** 的部屬回饋問卷已完成', '<p>本年度「部屬回饋問卷調查」由 【{name_en} {name}】 對 【{target_name_en} {target_name}】的問卷已提交完成。</p><br><br><p>人力資源處</p>', '0', '2017-12-05 11:26:37');
INSERT INTO `rv_email_template` VALUES ('22', 'yearly_feedback_delay', '【部屬回饋問卷通知】 {year} 年部屬回饋問卷尚未完成', '<h3>您好：</h3><p>本年度「部屬回饋問卷調查」已過截止時間〈<font color=\"red\">{day_end}</font>〉，敬請同仁儘速完成問卷調查，謝謝！</p>\r\n<p>【人事考評系統】</p><p>入口網址：<a href=\"http://{URL}\">{URL}</a></p><p>帳 號：員編</p><p>密 碼：身分證字號（預設）</p><br><br><p>人力資源處</p>', '0', '2017-12-05 11:27:57');
INSERT INTO `rv_email_template` VALUES ('23', 'yearly_assessment_launch', '【年度考評通知】 {year} 年　年度績效考核作業', '<h3>您好：</h3><p>本年度「績效考核作業」已開始進行，敬請同仁儘速完成自評作業；</p><p>各部門考核作業請於{year}年{month}月{day}日前完成，謝謝！</p><p>【人事考評系統】</p>\r\n<p>入口網址：<a href=\"http://{URL}\">{URL}</a></p><p>帳 號：員編</p><p>密 碼：身分證字號（預設）</p><br><br><p>人力資源處</p>', '0', '2017-12-05 11:29:23');
INSERT INTO `rv_email_template` VALUES ('24', 'yearly_assessment_pause', '【年度考評通知】 {year} 年　年度績效考核作業暫停', '<h3>您好：</h3><p>本年度「績效考核作業」已暫停</p><p>各部門考核作業暫時無法操作，敬請見諒！</p><br><br><p>人力資源處</p>', '0', '2017-12-05 11:30:15');
INSERT INTO `rv_email_template` VALUES ('25', 'yearly_assessment_all_report_done', '【年度考評通知】 {year} 年　年度績效考核作業 - 部門單位評核', '<h3>您好：</h3><p>本年度「績效考核作業」個人考核表已全數完成。\r\n開始進行部門級別單位之分數調整階段，請儘速完成各部門考核作業，謝謝！</p><p>【人事考評系統】</p><p> 入口網址: <a target=\"_blank\" href=\"{URL}\">{URL}</a> </p> <p> 帳     號：員編 </p> <p>密     碼：身分證字號（預設） </p> <br><br> <p>人力資源處</p>', '0', '2017-12-05 17:39:53');
INSERT INTO `rv_email_template` VALUES ('26', 'yearly_division_done', '【年度考評 - 批准通知】{year} 年　年度績效考核作業核定完成', '<h3>您好：</h3><p>本年度「績效考核作業」已核定完成，同仁請自行登入系統查看個人考核結果，謝謝！</p><p>【人事考評系統】</p><p> 入口網址: <a target=\"_blank\" href=\"{URL}\">{URL}</a> </p> <p> 帳     號：員編 </p> <p>密     碼：身分證字號（預設） </p> <br><br> <p>人力資源處</p>', '0', '2017-12-05 18:00:29');
INSERT INTO `rv_email_template` VALUES ('27', 'monthly_admin_checkout', '{year} 年 {month} 月 員工工作評語通知', '<h3>您好：</h3><p>本月份員工工作評語可開始進行評論，特此通知，謝謝！</p>\r\n<p>入口網址：<a href=\"http://{URL}\">{URL}</a></p><p>帳 號：員編</p><p>密 碼：身分證字號（預設）</p><br><br><p>人力資源處</p>', '0', '2017-12-12 20:24:58');
