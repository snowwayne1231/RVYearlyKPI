/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50617
Source Host           : localhost:3306
Source Database       : new_hr_qa3

Target Server Type    : MYSQL
Target Server Version : 50617
File Encoding         : 65001

Date: 2017-05-31 19:08:05
*/

SET FOREIGN_KEY_CHECKS=0;

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
