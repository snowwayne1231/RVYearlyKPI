-- MySQL dump 10.13  Distrib 5.6.17, for Win64 (x86_64)
--
-- Host: localhost    Database: new_hr_qa2
-- ------------------------------------------------------
-- Server version	5.6.17

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `rv_attendance`
--

DROP TABLE IF EXISTS `rv_attendance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rv_attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(6) DEFAULT '0' COMMENT '員工代號 - 系統内碼 rv_staff.id',
  `date` date DEFAULT NULL COMMENT '日期',
  `checkin_hours` time DEFAULT NULL COMMENT '上班',
  `checkout_hours` time DEFAULT NULL COMMENT '下班',
  `work_hours_total` float(5,2) NOT NULL DEFAULT '0.00' COMMENT '工時',
  `late` int(2) NOT NULL DEFAULT '0' COMMENT '考勤狀況 - 遲到',
  `early` int(2) NOT NULL DEFAULT '0' COMMENT '考勤狀況 - 早退',
  `nocard` int(2) NOT NULL DEFAULT '0' COMMENT '考勤狀況 - 忘卡',
  `remark` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '假日資料備註欄',
  `vocation_hours` float(5,2) NOT NULL DEFAULT '0.00' COMMENT '請假時數',
  `vocation_from` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '請假時數 - 開始時間',
  `vocation_to` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '請假時數 - 結束時間',
  `overtime_hours` float(5,2) NOT NULL DEFAULT '0.00' COMMENT '加班時數',
  `overtime_from` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '加班時數 - 開始時間',
  `overtime_to` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '加班時數 - 結束時間',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rv_attendance`
--

LOCK TABLES `rv_attendance` WRITE;
/*!40000 ALTER TABLE `rv_attendance` DISABLE KEYS */;
/*!40000 ALTER TABLE `rv_attendance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rv_config`
--

DROP TABLE IF EXISTS `rv_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rv_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT '設定檔名',
  `json` varchar(255) COLLATE utf8_unicode_ci DEFAULT '{}' COMMENT '設定檔內容',
  `update_date` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rv_config`
--

LOCK TABLES `rv_config` WRITE;
/*!40000 ALTER TABLE `rv_config` DISABLE KEYS */;
INSERT INTO `rv_config` VALUES (1,'email','{\"host\":\"mail.rv88.tw\",\r\n    \"user\" :\"dev.test@rv88.tw\",\r\n    \"pwd\" :\"NXsAZr2u6raXJXvt\",\r\n    \"secure\" : \"\",\r\n    \"port\" : 25,\r\n    \"from\" :\"dev.test@rv88.tw\",\"char\" :\"UTF-8\"}','2017-04-10 21:46:08');
/*!40000 ALTER TABLE `rv_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rv_config_cyclical`
--

DROP TABLE IF EXISTS `rv_config_cyclical`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rv_config_cyclical` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `year` int(4) NOT NULL,
  `month` int(4) NOT NULL,
  `day_start` int(2) NOT NULL DEFAULT '21',
  `day_end` int(2) NOT NULL DEFAULT '20',
  `day_cut_addition` int(2) NOT NULL DEFAULT '2' COMMENT '按開始審核階段的幾日後是結算日',
  `cut_off_date` date NOT NULL DEFAULT '0000-00-00' COMMENT '截止日期',
  `monthly_launched` int(2) DEFAULT '0' COMMENT '月績效開關 啟動=1,關閉=0',
  `update_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rv_config_cyclical`
--

LOCK TABLES `rv_config_cyclical` WRITE;
/*!40000 ALTER TABLE `rv_config_cyclical` DISABLE KEYS */;
INSERT INTO `rv_config_cyclical` VALUES (1,2017,4,21,20,2,'0000-00-00',0,'2017-04-18 04:29:34');
/*!40000 ALTER TABLE `rv_config_cyclical` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rv_department`
--

DROP TABLE IF EXISTS `rv_department`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rv_department`
--

LOCK TABLES `rv_department` WRITE;
/*!40000 ALTER TABLE `rv_department` DISABLE KEYS */;
INSERT INTO `rv_department` VALUES (1,1,'A00','運維中心',1,1,0,0,1,'2017-04-05 19:49:52'),(2,2,'B00','架構發展事業部',1,2,0,1,1,'0000-00-00 00:00:00'),(3,2,'F00','稽核部',1,0,0,1,1,'2017-04-05 20:11:54'),(4,2,'G00','風險管理部',1,0,0,1,1,'0000-00-00 00:00:00'),(5,2,'D00','營運系統部',1,0,0,1,1,'0000-00-00 00:00:00'),(6,2,'C00','客戶服務部',1,5,0,1,1,'2017-03-29 06:22:02'),(7,3,'B20','總務行政處',2,0,0,2,1,'2017-04-10 18:58:16'),(8,3,'D10','系統管理處',1,0,0,5,1,'0000-00-00 00:00:00'),(9,3,'D50','開發處',1,0,0,5,1,'2017-03-29 06:41:08'),(10,3,'G10','風險管理處',1,0,0,4,1,'0000-00-00 00:00:00'),(11,3,'C10','客戶服務處',5,0,0,6,1,'0000-00-00 00:00:00'),(12,3,'D30','資料庫管理處',1,0,0,5,1,'2017-04-10 02:44:05'),(13,3,'B10','人力資源處',2,0,0,2,1,'0000-00-00 00:00:00'),(14,3,'D20','技術支援處',1,0,0,5,1,'0000-00-00 00:00:00'),(15,3,'F10','稽查訓練處',1,85,0,3,1,'0000-00-00 00:00:00'),(16,4,'D31','資料庫管理組',1,71,0,12,1,'2017-04-10 02:33:33'),(17,4,'C12','值班客服組',5,15,1,11,1,'2017-04-13 16:03:44'),(18,4,'D26','值班技術四組',1,70,0,14,1,'0000-00-00 00:00:00'),(19,4,'D23','值班技術一組',1,54,1,14,1,'2017-04-13 16:03:51'),(20,4,'C11','專屬客服組',5,7,0,11,1,'0000-00-00 00:00:00'),(21,4,'D11','系統管理組',1,0,0,8,1,'0000-00-00 00:00:00'),(22,4,'D24','值班技術二組',1,66,0,14,1,'2017-03-29 06:39:25'),(23,4,'D51','開發組',1,80,0,9,1,'2017-03-29 06:40:48'),(24,4,'C13','聊天管理組',5,33,0,11,1,'0000-00-00 00:00:00'),(25,4,'D25','值班技術三組',1,68,0,14,1,'0000-00-00 00:00:00');
/*!40000 ALTER TABLE `rv_department` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rv_email_template`
--

DROP TABLE IF EXISTS `rv_email_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rv_email_template` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(55) NOT NULL COMMENT '名稱',
  `title` varchar(255) NOT NULL DEFAULT '標題' COMMENT '標題',
  `text` text NOT NULL COMMENT '模板',
  `update_operatinger_id` int(11) NOT NULL DEFAULT '0' COMMENT '操作人員的id',
  `update_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rv_email_template`
--

LOCK TABLES `rv_email_template` WRITE;
/*!40000 ALTER TABLE `rv_email_template` DISABLE KEYS */;
INSERT INTO `rv_email_template` VALUES (1,'monthly_start','{year}年{month}月  績效評核通知','<h3>您好：</h3><p>本月份績效評核作業已開啟，考勤區間為上月<font color=\"red\">{day_start}日</font>至本月<font color=\"red\">{day_end}日</font>止，請各主管儘速於 <font color=\"red\">{cut_off_date}</font> 前完成評核作業，謝謝！</p>\r\n<p>入口網址：<a href=\"http://{URL}\">{URL}</a></p><p>帳 號：員編</p><p>密 碼：身分證字號（預設）</p><br><br><p>人力資源處</p>',0,'2017-04-11 08:15:29'),(2,'monthly_return','【駁回通知】{year}年{month}月 {unit_id} {unit_name} 績效評核表已駁回','<h3>您好：</h3><p>本月份送審之 【 {unit_id} {unit_name} 】績效評核表已駁回，請儘速完成評核作業，謝謝！</p><p>入口網址： <a href=\"http://{URL}\">{URL}</a></p><p>帳     號：員編</p><p>密     碼：身分證字號（預設）</p><br><br> <p>人力資源處</p>',0,'2017-04-11 11:29:47'),(3,'monthly_arrive','【考評通知】{year}年{month}月 {unit_id} {unit_name} 績效評核表已送達','<h3>您好：</h3><p>本月份送審之【 {unit_id} {unit_name} 】績效評核表已送達至您，請儘速完成評核作業，謝謝！</p><p>入口網址： <a href=\"http://{URL}\">{URL}</a></p><p>帳     號：員編</p><p>密     碼：身分證字號（預設）</p><br><br><p>人力資源處</p>',0,'2017-04-11 11:28:51'),(4,'monthly_pause','【考評暫停通知】{year}年{month}月 績效評核表 暫時關閉','<h3>您好：</h3><p>本月份送審之績效評核表暫時關閉，如有不便之處請見諒，謝謝！</p><br><br><br><p>人力資源處</p>',0,'2017-04-11 10:35:57'),(5,'monthly_delay','【考評通知】{year}年{month}月 績效評核表','<h3>您好：</h3><p>本月份送審之績效評核表已到截止時間 <font color=\"red\"> ( {cut_off_date} ) </font>，請儘速完成評核作業，謝謝！</p><p>入口網址： <a href=\"http://{URL}\">{URL}</a></p><p>帳     號：員編</p><p>密     碼：身分證字號（預設）</p><br> <br><p>人力資源處</p>',0,'2017-04-11 13:15:43');
/*!40000 ALTER TABLE `rv_email_template` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rv_monthly_processing`
--

DROP TABLE IF EXISTS `rv_monthly_processing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rv_monthly_processing` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `status_code` int(6) DEFAULT '1',
  `type` int(2) NOT NULL DEFAULT '1' COMMENT '月表類型 1=主管, 2=一般',
  `commited` int(2) DEFAULT '0',
  `created_staff_id` int(6) NOT NULL,
  `created_department_id` int(11) NOT NULL,
  `year` int(4) NOT NULL,
  `month` int(4) NOT NULL,
  `owner_staff_id` int(11) NOT NULL DEFAULT '0' COMMENT '目前報告所有權 - 報告在誰手裏',
  `owner_department_id` int(6) DEFAULT '1',
  `path_staff_id` varchar(63) NOT NULL DEFAULT '[1]' COMMENT '單子的送審路程[]',
  `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rv_monthly_processing`
--

LOCK TABLES `rv_monthly_processing` WRITE;
/*!40000 ALTER TABLE `rv_monthly_processing` DISABLE KEYS */;
/*!40000 ALTER TABLE `rv_monthly_processing` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rv_monthly_report`
--

DROP TABLE IF EXISTS `rv_monthly_report`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rv_monthly_report` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(6) NOT NULL DEFAULT '0' COMMENT '員工代號 - 系統内碼 rv_staff.id',
  `year` int(4) NOT NULL DEFAULT '2016' COMMENT '評核年度',
  `month` int(2) NOT NULL COMMENT '評核月份',
  `quality` int(5) NOT NULL DEFAULT '5' COMMENT '工作品質',
  `completeness` int(5) NOT NULL DEFAULT '5' COMMENT '工作績效',
  `responsibility` int(5) NOT NULL DEFAULT '5' COMMENT '責任感',
  `cooperation` int(5) NOT NULL DEFAULT '5' COMMENT '配合度',
  `attendance` int(5) NOT NULL DEFAULT '5' COMMENT '時間觀念出席率',
  `addedValue` int(5) NOT NULL DEFAULT '0' COMMENT '特殊貢獻-依照貢獻度額外加分',
  `mistake` int(5) NOT NULL DEFAULT '0' COMMENT '重大缺失-若有重大疏失依照情節予以扣分或獎金不予發放',
  `total` int(5) NOT NULL COMMENT '總分 - 應該是不需要，因爲是計算出來的',
  `comment_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '對應評論的ID',
  `status` enum('N','Y') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'N' COMMENT '是否已提交',
  `releaseFlag` enum('Y','N') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'N' COMMENT '已審批與否 - 可否送交給總部',
  `bonus` int(2) DEFAULT '1' COMMENT '當月是否發放獎金 ■是=1,□否=0',
  `processing_id` int(11) DEFAULT '0',
  `owner_staff_id` int(11) NOT NULL COMMENT '目前報告所有權 - 報告在誰手裏',
  `owner_department_id` int(6) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `staff` (`staff_id`) USING BTREE,
  KEY `report_pid` (`processing_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rv_monthly_report`
--

LOCK TABLES `rv_monthly_report` WRITE;
/*!40000 ALTER TABLE `rv_monthly_report` DISABLE KEYS */;
/*!40000 ALTER TABLE `rv_monthly_report` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rv_monthly_report_leader`
--

DROP TABLE IF EXISTS `rv_monthly_report_leader`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rv_monthly_report_leader` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(6) NOT NULL DEFAULT '0' COMMENT '員工代號 - 系統内碼 rv_staff.id',
  `year` int(4) NOT NULL DEFAULT '2016' COMMENT '評核年度',
  `month` int(2) NOT NULL COMMENT '評核月份',
  `target` int(5) NOT NULL DEFAULT '5' COMMENT '目標達成率',
  `quality` int(5) NOT NULL DEFAULT '5' COMMENT '工作品質',
  `method` int(5) NOT NULL DEFAULT '5' COMMENT '工作方法',
  `error` int(5) NOT NULL DEFAULT '5' COMMENT '出錯率',
  `backtrack` int(5) NOT NULL DEFAULT '5' COMMENT '進度追蹤/回報',
  `planning` int(5) NOT NULL DEFAULT '5' COMMENT '企劃能力',
  `execute` int(5) NOT NULL DEFAULT '5' COMMENT '執行力',
  `decision` int(5) NOT NULL DEFAULT '5' COMMENT '判斷力',
  `resilience` int(5) NOT NULL DEFAULT '5' COMMENT '應變能力',
  `attendance` int(5) NOT NULL DEFAULT '5' COMMENT '出缺勤率',
  `attendance_members` int(5) NOT NULL DEFAULT '5' COMMENT '組員出缺勤率',
  `addedValue` int(5) NOT NULL DEFAULT '0' COMMENT '特殊貢獻-依照貢獻度額外加分',
  `mistake` int(5) NOT NULL DEFAULT '0' COMMENT '重大缺失-若有重大疏失依照情節予以扣分或獎金不予發放',
  `total` int(5) NOT NULL COMMENT '總分 - 應該是不需要，因爲是計算出來的',
  `comment_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '對應評論的ID',
  `status` enum('Y','N') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'N' COMMENT '是否已提交',
  `releaseFlag` enum('Y','N') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'N' COMMENT '已審批與否 - 可否往下一關',
  `bonus` int(2) DEFAULT '1' COMMENT '當月是否發放獎金 ■是=1,□否=0',
  `processing_id` int(11) DEFAULT '0',
  `owner_staff_id` int(11) NOT NULL DEFAULT '0' COMMENT '目前報告所有權 - 報告在誰手裏',
  `owner_department_id` int(6) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `staff` (`staff_id`) USING BTREE,
  KEY `report_leader_pid` (`processing_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rv_monthly_report_leader`
--

LOCK TABLES `rv_monthly_report_leader` WRITE;
/*!40000 ALTER TABLE `rv_monthly_report_leader` DISABLE KEYS */;
/*!40000 ALTER TABLE `rv_monthly_report_leader` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rv_record_monthly_processing`
--

DROP TABLE IF EXISTS `rv_record_monthly_processing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rv_record_monthly_processing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `operating_staff_id` int(11) NOT NULL DEFAULT '0',
  `target_staff_id` int(11) NOT NULL,
  `processing_id` int(11) NOT NULL,
  `action` enum('launch','commit','return','done','cancel','other') COLLATE utf8_unicode_ci NOT NULL,
  `reason` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `changed_json` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `update_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `record_monthly_update` (`update_date`),
  KEY `processing_id` (`processing_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rv_record_monthly_processing`
--

LOCK TABLES `rv_record_monthly_processing` WRITE;
/*!40000 ALTER TABLE `rv_record_monthly_processing` DISABLE KEYS */;
/*!40000 ALTER TABLE `rv_record_monthly_processing` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rv_record_monthly_report`
--

DROP TABLE IF EXISTS `rv_record_monthly_report`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rv_record_monthly_report` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `operating_staff_id` int(11) NOT NULL DEFAULT '0',
  `processing_id` int(11) NOT NULL,
  `processing_record_id` int(11) NOT NULL,
  `report_id` int(11) NOT NULL,
  `report_type` int(2) NOT NULL DEFAULT '1' COMMENT '月表類型 1=主管, 2=一般',
  `changed_json` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `update_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `record_monthly_update` (`update_date`),
  KEY `report_id` (`report_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rv_record_monthly_report`
--

LOCK TABLES `rv_record_monthly_report` WRITE;
/*!40000 ALTER TABLE `rv_record_monthly_report` DISABLE KEYS */;
/*!40000 ALTER TABLE `rv_record_monthly_report` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rv_record_personal_comment`
--

DROP TABLE IF EXISTS `rv_record_personal_comment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rv_record_personal_comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `create_staff_id` int(11) NOT NULL,
  `target_staff_id` int(11) NOT NULL,
  `report_id` int(11) NOT NULL COMMENT '對應哪一個月報表',
  `report_type` int(2) NOT NULL DEFAULT '1' COMMENT '對應主管或組員',
  `content` varchar(255) DEFAULT '',
  `status` int(2) DEFAULT '1' COMMENT '記錄狀態 1=正常 0=關閉',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `create_time` (`create_time`),
  KEY `report_id` (`report_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rv_record_personal_comment`
--

LOCK TABLES `rv_record_personal_comment` WRITE;
/*!40000 ALTER TABLE `rv_record_personal_comment` DISABLE KEYS */;
/*!40000 ALTER TABLE `rv_record_personal_comment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rv_record_personal_comment_changed`
--

DROP TABLE IF EXISTS `rv_record_personal_comment_changed`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rv_record_personal_comment_changed` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `comment_id` int(11) NOT NULL COMMENT '對應哪一個評論',
  `create_staff_id` int(11) NOT NULL,
  `target_staff_id` int(11) NOT NULL,
  `content` varchar(255) DEFAULT '',
  `change_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rv_record_personal_comment_changed`
--

LOCK TABLES `rv_record_personal_comment_changed` WRITE;
/*!40000 ALTER TABLE `rv_record_personal_comment_changed` DISABLE KEYS */;
/*!40000 ALTER TABLE `rv_record_personal_comment_changed` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rv_record_staff`
--

DROP TABLE IF EXISTS `rv_record_staff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rv_record_staff` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `operating_staff_id` int(11) NOT NULL DEFAULT '0',
  `staff_id` int(11) NOT NULL,
  `changed_json` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `update_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `record_monthly_update` (`update_date`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rv_record_staff`
--

LOCK TABLES `rv_record_staff` WRITE;
/*!40000 ALTER TABLE `rv_record_staff` DISABLE KEYS */;
INSERT INTO `rv_record_staff` VALUES (1,2,1,'{\"department_id\":1,\"staff_no\":\"R001\",\"account\":\"mickey.hou\",\"name\":\"u4fafu7d71u63da\",\"name_en\":\"Mickey\",\"title_id\":1,\"post_id\":1,\"passwd\":\"24962821\",\"email\":\"mickey.hou@rv88.tw\",\"first_day\":\"2012-11-01\",\"last_day\":\"\",\"update_date\":\"\",\"status_id\":1,\"rank\":','2017-04-18 04:29:45'),(2,2,5,'{\"department_id\":6,\"staff_no\":\"R031\",\"account\":\"eric.lin\",\"name\":\"u6797u5b50u96f2\",\"name_en\":\"Eric\",\"title_id\":2,\"post_id\":1,\"passwd\":\"24962821\",\"email\":\"eric.lin@rv88.tw\",\"first_day\":\"2014-12-01\",\"last_day\":\"\",\"update_date\":\"\",\"status_id\":1,\"rank\":\"9\",\"p','2017-04-18 04:29:45'),(3,2,7,'{\"department_id\":20,\"staff_no\":\"R020\",\"account\":\"vincent.lee\",\"name\":\"u674eu5a01u63da\",\"name_en\":\"Vincent\",\"title_id\":4,\"post_id\":19,\"passwd\":\"24962821\",\"email\":\"vincent.lee@rv88.tw\",\"first_day\":\"2014-04-09\",\"last_day\":\"\",\"update_date\":\"\",\"status_id\":1,\"r','2017-04-18 04:29:45'),(4,2,15,'{\"department_id\":17,\"staff_no\":\"R024\",\"account\":\"rita.hsu\",\"name\":\"u8a31u96c5u73b2\",\"name_en\":\"Rita\",\"title_id\":4,\"post_id\":19,\"passwd\":\"24962821\",\"email\":\"rita.hsu@rv88.tw\",\"first_day\":\"2014-08-04\",\"last_day\":\"\",\"update_date\":\"\",\"status_id\":1,\"rank\":\"6\",','2017-04-18 04:29:46'),(5,2,33,'{\"department_id\":24,\"staff_no\":\"R013\",\"account\":\"jessie.chiao\",\"name\":\"u7126u5c0fu7d05\",\"name_en\":\"Jessie\",\"title_id\":4,\"post_id\":19,\"passwd\":\"24962821\",\"email\":\"jessie.chiao@rv88.tw\",\"first_day\":\"2013-08-01\",\"last_day\":\"\",\"update_date\":\"\",\"status_id\":1,\"','2017-04-18 04:29:47'),(6,2,54,'{\"department_id\":19,\"staff_no\":\"R008\",\"account\":\"turtle.huang\",\"name\":\"u9ec3u6d77u5a01\",\"name_en\":\"Turtle\",\"title_id\":4,\"post_id\":19,\"passwd\":\"24962821\",\"email\":\"turtle.huang@rv88.tw\",\"first_day\":\"2013-04-01\",\"last_day\":\"\",\"update_date\":\"\",\"status_id\":1,\"','2017-04-18 04:29:48'),(7,2,66,'{\"department_id\":22,\"staff_no\":\"R049\",\"account\":\"ryan.sung\",\"name\":\"u5b8bu9577u6d32\",\"name_en\":\"Ryan\",\"title_id\":4,\"post_id\":19,\"passwd\":\"24962821\",\"email\":\"ryan.sung@rv88.tw\",\"first_day\":\"2015-03-09\",\"last_day\":\"\",\"update_date\":\"\",\"status_id\":1,\"rank\":\"6','2017-04-18 04:29:49'),(8,2,68,'{\"department_id\":25,\"staff_no\":\"R021\",\"account\":\"luke.yang\",\"name\":\"u694au4f2fu9e9f\",\"name_en\":\"Luke\",\"title_id\":4,\"post_id\":19,\"passwd\":\"24962821\",\"email\":\"luke.yang@rv88.tw\",\"first_day\":\"2014-06-18\",\"last_day\":\"2017-04-16\",\"update_date\":\"\",\"status_id\":4','2017-04-18 04:29:49'),(9,2,70,'{\"department_id\":18,\"staff_no\":\"R018\",\"account\":\"quake.chiu\",\"name\":\"u90b1u67cfu6d0b\",\"name_en\":\"Quake\",\"title_id\":4,\"post_id\":19,\"passwd\":\"24962821\",\"email\":\"quake.chiu@rv88.tw\",\"first_day\":\"2013-12-02\",\"last_day\":\"\",\"update_date\":\"\",\"status_id\":1,\"rank\"','2017-04-18 04:29:49'),(10,2,71,'{\"department_id\":16,\"staff_no\":\"R002\",\"account\":\"richard.chang\",\"name\":\"u5f35u88d5\",\"name_en\":\"Richard\",\"title_id\":4,\"post_id\":19,\"passwd\":\"24962821\",\"email\":\"richard.chang@rv88.tw\",\"first_day\":\"2012-11-01\",\"last_day\":\"\",\"update_date\":\"\",\"status_id\":1,\"ra','2017-04-18 04:29:49'),(11,2,77,'{\"department_id\":23,\"staff_no\":\"R036\",\"account\":\"jemmy.lai\",\"name\":\"u8cf4u653fu7537\",\"name_en\":\"Jemmy\",\"title_id\":4,\"post_id\":19,\"passwd\":\"24962821\",\"email\":\"jemmy.lai@rv88.tw\",\"first_day\":\"2014-12-15\",\"last_day\":\"2017-01-31\",\"update_date\":\"\",\"status_id\":','2017-04-18 04:29:50'),(12,2,80,'{\"department_id\":23,\"staff_no\":\"R058\",\"account\":\"snow.jhung\",\"name\":\"u838au683cu7dad\",\"name_en\":\"Snow\",\"title_id\":4,\"post_id\":19,\"passwd\":\"24962821\",\"email\":\"snow.jhung@rv88.tw\",\"first_day\":\"2015-04-13\",\"last_day\":\"\",\"update_date\":\"\",\"status_id\":1,\"rank\":','2017-04-18 04:29:50'),(13,2,85,'{\"department_id\":15,\"staff_no\":\"R009\",\"account\":\"hako.yang\",\"name\":\"u694au52adu5100\",\"name_en\":\"Hako\",\"title_id\":3,\"post_id\":18,\"passwd\":\"24962821\",\"email\":\"hako.yang@rv88.tw\",\"first_day\":\"2013-04-15\",\"last_day\":\"\",\"update_date\":\"\",\"status_id\":1,\"rank\":\"7','2017-04-18 04:29:50'),(14,2,25,'{\"department_id\":15,\"staff_no\":\"R056\",\"account\":\"evelyn.kao\",\"name\":\"u9ad8u8587u96ef\",\"name_en\":\"Evelyn\",\"title_id\":5,\"post_id\":9,\"passwd\":\"\",\"email\":\"evelyn.kao@rv88.tw\",\"first_day\":\"2015-04-07\",\"last_day\":\"\",\"update_date\":\"2017-01-23\",\"status_id\":1,\"ran','2017-04-18 04:29:50'),(15,2,203,'{\"department_id\":10,\"staff_no\":\"R162\",\"account\":\"aries.chen\",\"name\":\"u9673u6642u4ef2\",\"name_en\":\"Aries\",\"title_id\":5,\"post_id\":15,\"passwd\":\"\",\"email\":\"aries.chen@rv88.tw\",\"first_day\":\"2016-12-01\",\"last_day\":\"\",\"update_date\":\"\",\"status_id\":1,\"rank\":\"3\",\"po','2017-04-18 04:29:51'),(16,2,1,'{\"department_id\":1,\"staff_no\":\"R001\",\"account\":\"mickey.hou\",\"name\":\"u4fafu7d71u63da\",\"name_en\":\"Mickey\",\"title_id\":1,\"post_id\":1,\"passwd\":\"24962821\",\"email\":\"mickey.hou@rv88.tw\",\"first_day\":\"2012-11-01\",\"last_day\":\"\",\"update_date\":\"\",\"status_id\":1,\"rank\":','2017-04-18 04:51:29'),(17,2,5,'{\"department_id\":6,\"staff_no\":\"R031\",\"account\":\"eric.lin\",\"name\":\"u6797u5b50u96f2\",\"name_en\":\"Eric\",\"title_id\":2,\"post_id\":1,\"passwd\":\"24962821\",\"email\":\"eric.lin@rv88.tw\",\"first_day\":\"2014-12-01\",\"last_day\":\"\",\"update_date\":\"\",\"status_id\":1,\"rank\":\"9\",\"p','2017-04-18 04:51:29'),(18,2,7,'{\"department_id\":20,\"staff_no\":\"R020\",\"account\":\"vincent.lee\",\"name\":\"u674eu5a01u63da\",\"name_en\":\"Vincent\",\"title_id\":4,\"post_id\":19,\"passwd\":\"24962821\",\"email\":\"vincent.lee@rv88.tw\",\"first_day\":\"2014-04-09\",\"last_day\":\"\",\"update_date\":\"\",\"status_id\":1,\"r','2017-04-18 04:51:29'),(19,2,15,'{\"department_id\":17,\"staff_no\":\"R024\",\"account\":\"rita.hsu\",\"name\":\"u8a31u96c5u73b2\",\"name_en\":\"Rita\",\"title_id\":4,\"post_id\":19,\"passwd\":\"24962821\",\"email\":\"rita.hsu@rv88.tw\",\"first_day\":\"2014-08-04\",\"last_day\":\"\",\"update_date\":\"\",\"status_id\":1,\"rank\":\"6\",','2017-04-18 04:51:30'),(20,2,33,'{\"department_id\":24,\"staff_no\":\"R013\",\"account\":\"jessie.chiao\",\"name\":\"u7126u5c0fu7d05\",\"name_en\":\"Jessie\",\"title_id\":4,\"post_id\":19,\"passwd\":\"24962821\",\"email\":\"jessie.chiao@rv88.tw\",\"first_day\":\"2013-08-01\",\"last_day\":\"\",\"update_date\":\"\",\"status_id\":1,\"','2017-04-18 04:51:31'),(21,2,54,'{\"department_id\":19,\"staff_no\":\"R008\",\"account\":\"turtle.huang\",\"name\":\"u9ec3u6d77u5a01\",\"name_en\":\"Turtle\",\"title_id\":4,\"post_id\":19,\"passwd\":\"24962821\",\"email\":\"turtle.huang@rv88.tw\",\"first_day\":\"2013-04-01\",\"last_day\":\"\",\"update_date\":\"\",\"status_id\":1,\"','2017-04-18 04:51:32'),(22,2,66,'{\"department_id\":22,\"staff_no\":\"R049\",\"account\":\"ryan.sung\",\"name\":\"u5b8bu9577u6d32\",\"name_en\":\"Ryan\",\"title_id\":4,\"post_id\":19,\"passwd\":\"24962821\",\"email\":\"ryan.sung@rv88.tw\",\"first_day\":\"2015-03-09\",\"last_day\":\"\",\"update_date\":\"\",\"status_id\":1,\"rank\":\"6','2017-04-18 04:51:33'),(23,2,68,'{\"department_id\":25,\"staff_no\":\"R021\",\"account\":\"luke.yang\",\"name\":\"u694au4f2fu9e9f\",\"name_en\":\"Luke\",\"title_id\":4,\"post_id\":19,\"passwd\":\"24962821\",\"email\":\"luke.yang@rv88.tw\",\"first_day\":\"2014-06-18\",\"last_day\":\"2017-04-16\",\"update_date\":\"\",\"status_id\":4','2017-04-18 04:51:33'),(24,2,70,'{\"department_id\":18,\"staff_no\":\"R018\",\"account\":\"quake.chiu\",\"name\":\"u90b1u67cfu6d0b\",\"name_en\":\"Quake\",\"title_id\":4,\"post_id\":19,\"passwd\":\"24962821\",\"email\":\"quake.chiu@rv88.tw\",\"first_day\":\"2013-12-02\",\"last_day\":\"\",\"update_date\":\"\",\"status_id\":1,\"rank\"','2017-04-18 04:51:33'),(25,2,71,'{\"department_id\":16,\"staff_no\":\"R002\",\"account\":\"richard.chang\",\"name\":\"u5f35u88d5\",\"name_en\":\"Richard\",\"title_id\":4,\"post_id\":19,\"passwd\":\"24962821\",\"email\":\"richard.chang@rv88.tw\",\"first_day\":\"2012-11-01\",\"last_day\":\"\",\"update_date\":\"\",\"status_id\":1,\"ra','2017-04-18 04:51:33'),(26,2,77,'{\"department_id\":23,\"staff_no\":\"R036\",\"account\":\"jemmy.lai\",\"name\":\"u8cf4u653fu7537\",\"name_en\":\"Jemmy\",\"title_id\":4,\"post_id\":19,\"passwd\":\"24962821\",\"email\":\"jemmy.lai@rv88.tw\",\"first_day\":\"2014-12-15\",\"last_day\":\"2017-01-31\",\"update_date\":\"\",\"status_id\":','2017-04-18 04:51:33'),(27,2,80,'{\"department_id\":23,\"staff_no\":\"R058\",\"account\":\"snow.jhung\",\"name\":\"u838au683cu7dad\",\"name_en\":\"Snow\",\"title_id\":4,\"post_id\":19,\"passwd\":\"24962821\",\"email\":\"snow.jhung@rv88.tw\",\"first_day\":\"2015-04-13\",\"last_day\":\"\",\"update_date\":\"\",\"status_id\":1,\"rank\":','2017-04-18 04:51:34'),(28,2,85,'{\"department_id\":15,\"staff_no\":\"R009\",\"account\":\"hako.yang\",\"name\":\"u694au52adu5100\",\"name_en\":\"Hako\",\"title_id\":3,\"post_id\":18,\"passwd\":\"24962821\",\"email\":\"hako.yang@rv88.tw\",\"first_day\":\"2013-04-15\",\"last_day\":\"\",\"update_date\":\"\",\"status_id\":1,\"rank\":\"7','2017-04-18 04:51:34'),(29,2,25,'{\"department_id\":15,\"staff_no\":\"R056\",\"account\":\"evelyn.kao\",\"name\":\"u9ad8u8587u96ef\",\"name_en\":\"Evelyn\",\"title_id\":5,\"post_id\":9,\"passwd\":\"\",\"email\":\"evelyn.kao@rv88.tw\",\"first_day\":\"2015-04-07\",\"last_day\":\"\",\"update_date\":\"2017-01-23\",\"status_id\":1,\"ran','2017-04-18 04:51:34'),(30,2,203,'{\"department_id\":10,\"staff_no\":\"R162\",\"account\":\"aries.chen\",\"name\":\"u9673u6642u4ef2\",\"name_en\":\"Aries\",\"title_id\":5,\"post_id\":15,\"passwd\":\"\",\"email\":\"aries.chen@rv88.tw\",\"first_day\":\"2016-12-01\",\"last_day\":\"\",\"update_date\":\"\",\"status_id\":1,\"rank\":\"3\",\"po','2017-04-18 04:51:34');
/*!40000 ALTER TABLE `rv_record_staff` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rv_staff`
--

DROP TABLE IF EXISTS `rv_staff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rv_staff` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_no` varchar(5) COLLATE utf8_unicode_ci NOT NULL COMMENT '員工工號',
  `title` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '職類',
  `title_id` int(2) DEFAULT '1',
  `post` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT '職務',
  `post_id` int(2) DEFAULT '1',
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT '中文性名',
  `name_en` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT '英文性名',
  `account` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT '登入帳號',
  `passwd` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '12121212' COMMENT '登入密碼',
  `email` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '聯絡用郵件地址',
  `lv` int(2) NOT NULL COMMENT '組織階層 米奇開始為1，Eric 為2.....依此類推',
  `first_day` date NOT NULL DEFAULT '0000-00-00' COMMENT '到職日',
  `last_day` date DEFAULT '0000-00-00' COMMENT '離職日',
  `update_date` date DEFAULT '0000-00-00' COMMENT '最後更新日期',
  `status` enum('約聘','試用','正式','離職') COLLATE utf8_unicode_ci NOT NULL DEFAULT '試用' COMMENT '在職狀態',
  `status_id` int(2) DEFAULT '1',
  `department_id` int(11) NOT NULL DEFAULT '0',
  `is_leader` int(2) NOT NULL DEFAULT '0' COMMENT '是否為單位長',
  `is_admin` int(2) NOT NULL DEFAULT '0' COMMENT '是否為管理者',
  `rank` int(6) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `staff_no` (`staff_no`)
) ENGINE=InnoDB AUTO_INCREMENT=236 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rv_staff`
--

LOCK TABLES `rv_staff` WRITE;
/*!40000 ALTER TABLE `rv_staff` DISABLE KEYS */;
INSERT INTO `rv_staff` VALUES (1,'R001','決策人員',1,'經理',1,'侯統揚','Mickey','mickey.hou','24962821','mickey.hou@rv88.tw',1,'2012-11-01','0000-00-00','0000-00-00','正式',1,1,1,0,13),(2,'R019','部級主管',2,'經理',1,'蘇穎珊','Susan','susan.su','24962821','susan.su@rv88.tw',2,'2013-12-02','0000-00-00','0000-00-00','正式',1,2,1,1,9),(3,'R050','一般人員(行政/專技)',5,'資深人資專員',3,'吳美君','Mavis','mavis.wu','24962821','mavis.wu@rv88.tw',5,'2015-04-01','0000-00-00','0000-00-00','正式',1,13,0,1,4),(4,'R115','一般人員(行政/專技)',5,'行政人員',5,'鄧幼華','Liz','liz.teng','','liz.teng@rv88.tw',5,'2016-03-17','2017-02-12','0000-00-00','離職',4,7,0,1,3),(5,'R031','部級主管',2,'經理',1,'林子雲','Eric','eric.lin','24962821','eric.lin@rv88.tw',2,'2014-12-01','0000-00-00','0000-00-00','正式',1,6,1,0,9),(6,'R010','一般人員(行政/專技)',5,'客服專員',11,'王姸茹','Zoe','zoe.wang','','zoe.wang@rv88.tw',5,'2013-04-15','0000-00-00','0000-00-00','正式',1,20,0,0,3),(7,'R020','組長',4,'組長',19,'李威揚','Vincent','vincent.lee','24962821','vincent.lee@rv88.tw',4,'2014-04-09','0000-00-00','0000-00-00','正式',1,20,1,0,6),(8,'R027','一般人員(行政/專技)',5,'客服專員',11,'林欣馨','Shin','shin.lin','','shin.lin@rv88.tw',5,'2014-10-01','0000-00-00','0000-00-00','正式',1,20,0,0,3),(9,'R033','一般人員(行政/專技)',5,'客服專員',11,'朱書賢','Kevin','kevin.chu','','kevin.chu@rv88.tw',5,'2014-12-02','0000-00-00','0000-00-00','正式',1,20,0,0,3),(10,'R116','一般人員(行政/專技)',5,'客服專員',11,'李孝龍','Bruce','bruce.lee','','bruce.lee@rv88.tw',5,'2016-03-21','0000-00-00','0000-00-00','正式',1,20,0,0,3),(11,'R015','一般人員(行政/專技)',5,'客服專員',11,'甘博仁','Ken','ken.kan','','ken.kan@rv88.tw',5,'2013-10-02','0000-00-00','0000-00-00','正式',1,17,0,0,3),(12,'R017','一般人員(行政/專技)',5,'客服專員',11,'黃忠信','Jeff','jeff.huang','','jeff.huang@rv88.tw',5,'2013-11-12','0000-00-00','0000-00-00','正式',1,17,0,0,3),(13,'R022','一般人員(行政/專技)',5,'客服專員',11,'林世創','Strong','strong.lin','','strong.lin@rv88.tw',5,'2014-08-04','0000-00-00','0000-00-00','正式',1,17,0,0,3),(14,'R023','一般人員(行政/專技)',5,'客服專員',11,'楊麗萍','Crystal','crystal.yang','','crystal.yang@rv88.tw',5,'2014-08-04','0000-00-00','0000-00-00','正式',1,17,0,0,3),(15,'R024','組長',4,'組長',19,'許雅玲','Rita','rita.hsu','24962821','rita.hsu@rv88.tw',4,'2014-08-04','0000-00-00','0000-00-00','正式',1,17,1,0,6),(16,'R025','一般人員(行政/專技)',5,'客服專員',11,'林碩人','David','david.lin','','david.lin@rv88.tw',5,'2014-08-04','2017-03-14','0000-00-00','離職',4,17,0,0,3),(17,'R032','一般人員(行政/專技)',5,'客服專員',11,'高君龢','Herman','herman.gao','','herman.gao@rv88.tw',5,'2014-12-02','0000-00-00','0000-00-00','正式',1,17,0,0,3),(18,'R041','一般人員(行政/專技)',5,'客服專員',11,'廖逸楷','Kai','kai.liao','','kai.liao@rv88.tw',5,'2015-01-07','2017-01-31','0000-00-00','離職',4,17,0,0,3),(19,'R042','一般人員(行政/專技)',5,'客服專員',11,'蔣雄貴','Kuei','kuei.jiang','','kuei.jiang@rv88.tw',5,'2015-01-07','0000-00-00','0000-00-00','正式',1,17,0,0,3),(20,'R043','一般人員(行政/專技)',5,'客服專員',11,'鄒宜君','Candy','candy.tsou','','candy.tsou@rv88.tw',5,'2015-01-07','0000-00-00','0000-00-00','正式',1,17,0,0,3),(21,'R044','一般人員(行政/專技)',5,'客服專員',11,'余佩霖','Peilin','peilin.yu','','peilin.yu@rv88.tw',5,'2015-01-07','0000-00-00','0000-00-00','正式',1,17,0,0,3),(22,'R046','一般人員(行政/專技)',5,'客服專員',11,'林育賢','Matt','matt.lin','','matt.lin@rv88.tw',5,'2015-01-19','0000-00-00','0000-00-00','正式',1,17,0,0,3),(23,'R047','一般人員(行政/專技)',5,'客服專員',11,'鍾瀅','Saya','saya.zhun','','zhun.saya@rv88.tw',5,'2015-03-04','0000-00-00','0000-00-00','正式',1,17,0,0,3),(24,'R055','一般人員(行政/專技)',5,'客服專員',11,'譚奇勝','Jacob','jacob.tan','','jacob.tan@rv88.tw',5,'2015-04-07','0000-00-00','0000-00-00','正式',1,17,0,0,3),(25,'R056','一般人員(行政/專技)',5,'稽核專員',9,'高薇雯','Evelyn','evelyn.kao','','evelyn.kao@rv88.tw',5,'2015-04-07','0000-00-00','2017-01-23','正式',1,15,0,0,3),(27,'R075','一般人員(行政/專技)',5,'客服專員',11,'洪廷霖','Lin','lin.hong','','lin.hong@rv88.tw',5,'2015-07-14','0000-00-00','0000-00-00','正式',1,17,0,0,3),(28,'R082','一般人員(行政/專技)',5,'客服專員',11,'林宛諭','Alison','alison.lin','','alison.lin@rv88.tw',5,'2015-08-03','0000-00-00','0000-00-00','正式',1,17,0,0,3),(29,'R100','一般人員(行政/專技)',5,'客服專員',11,'葉庭瑋','Willie','willie.yeh','','willie.yeh@rv88.tw',5,'2015-10-05','0000-00-00','0000-00-00','正式',1,17,0,0,3),(30,'R104','一般人員(行政/專技)',5,'客服專員',11,'黃志衡','George','george.huang','','george.huang@rv88.tw',5,'2015-11-02','0000-00-00','0000-00-00','正式',1,17,0,0,3),(31,'R125','一般人員(行政/專技)',5,'客服專員',11,'徐澄','Raven','raven.hsu','','raven.hsu@rv88.tw',5,'2016-06-01','0000-00-00','0000-00-00','正式',1,17,0,0,3),(32,'R128','一般人員(行政/專技)',5,'稽核專員',9,'丁于希','Yusi','yusi.ting','','yusi.ting@rv88.tw',5,'2016-06-13','0000-00-00','0000-00-00','正式',1,15,0,0,3),(33,'R013','組長',4,'組長',19,'焦小紅','Jessie','jessie.chiao','24962821','jessie.chiao@rv88.tw',4,'2013-08-01','0000-00-00','0000-00-00','正式',1,24,1,0,6),(34,'R072','轉正人員',9,'助理',12,'陳星羽','Annie','annie.chen','','annie.chen@rv88.tw',5,'2015-07-13','0000-00-00','0000-00-00','正式',1,24,0,0,1),(35,'R080','轉正人員',9,'助理',12,'張奇勳','Tristan','tristan.chang','','tristan.chang@rv88.tw',5,'2015-07-23','0000-00-00','0000-00-00','正式',1,24,0,0,1),(39,'R109','一般人員(行政/專技)',5,'客服專員',11,'李洪彰','Moore','moore.lee','','moore.lee@rv88.tw',5,'2015-11-11','0000-00-00','0000-00-00','正式',1,17,0,0,3),(41,'R111','一般人員(行政/專技)',5,'客服專員',11,'袁書豪','White','white.yuan','','white.yuan@rv88.tw',5,'2015-12-14','0000-00-00','2017-03-01','正式',1,17,0,0,3),(49,'R029','一般人員(行政/專技)',5,'系統工程師',8,'黃瑞龍','Ray','ray.huang','','ray.huang@rv88.tw',5,'2014-11-04','0000-00-00','0000-00-00','正式',1,21,0,0,3),(50,'R106','一般人員(行政/專技)',5,'系統工程師',8,'陳禹豪','David','david.chen','','david.chen@rv88.tw',5,'2015-11-02','0000-00-00','0000-00-00','正式',1,21,0,0,3),(52,'R006','一般人員(行政/專技)',5,'技術支援工程師',10,'全士芃','Peter','peter.chuang','','peter.chuang@rv88.tw',5,'2013-03-04','0000-00-00','0000-00-00','正式',1,19,0,0,3),(53,'R007','一般人員(行政/專技)',5,'技術支援工程師',10,'林子翔','Poki','poki.lin','','poki.lin@rv88.tw',5,'2013-04-01','0000-00-00','2017-03-21','正式',1,22,0,0,3),(54,'R008','組長',4,'組長',19,'黃海威','Turtle','turtle.huang','24962821','turtle.huang@rv88.tw',4,'2013-04-01','0000-00-00','0000-00-00','正式',1,19,1,0,6),(55,'R011','一般人員(行政/專技)',5,'技術支援工程師',10,'何俊達','Dada','dada.ho','','dadazax.ho@rv88.tw',5,'2013-05-02','0000-00-00','0000-00-00','正式',1,19,0,0,3),(56,'R026','一般人員(行政/專技)',5,'技術支援工程師',10,'趙祐晟','Johnson','johnson.chao','','johnson.chao@rv88.tw',5,'2014-10-01','0000-00-00','0000-00-00','正式',1,19,0,0,3),(57,'R059','一般人員(行政/專技)',5,'技術支援工程師',10,'陳昱丞','Yuchen','yuchen.chen','','yuchen.chen@rv88.tw',5,'2015-04-15','0000-00-00','0000-00-00','正式',1,19,0,0,3),(58,'R063','其他職員',7,'助理',12,'黃心潔','Lulu','lulu.huang','','lulu.huang@rv88.tw',5,'2015-05-11','2017-01-31','0000-00-00','離職',4,15,0,0,2),(59,'R094','一般人員(行政/專技)',5,'技術支援工程師',10,'王智威','Peter','peter.wang','','peter.wang@rv88.tw',5,'2015-09-14','0000-00-00','0000-00-00','正式',1,19,0,0,3),(60,'R113','一般人員(行政/專技)',5,'技術支援工程師',10,'張士駿','Grey','grey.chang','','grey.chang@rv88.tw',5,'2016-03-01','0000-00-00','0000-00-00','正式',1,19,0,0,3),(62,'R120','一般人員(行政/專技)',5,'技術支援工程師',10,'郭庭維','Duke','duke.kuo','','duke.kuo@rv88.tw',5,'2016-05-03','0000-00-00','0000-00-00','正式',1,18,0,0,3),(65,'R123','一般人員(行政/專技)',5,'技術支援工程師',10,'賴佳昌','Exia','exia.lai','','exia.lai@rv88.tw',5,'2016-05-16','0000-00-00','0000-00-00','正式',1,19,0,0,3),(66,'R049','組長',4,'組長',19,'宋長洲','Ryan','ryan.sung','24962821','ryan.sung@rv88.tw',4,'2015-03-09','0000-00-00','0000-00-00','正式',1,22,1,0,6),(67,'R069','一般人員(行政/專技)',5,'技術支援工程師',10,'許嘉維','Vic','vic.hsu','','vic.hsu@rv88.tw',5,'2015-07-13','0000-00-00','0000-00-00','正式',1,22,0,0,3),(68,'R021','組長',4,'組長',19,'楊伯麟','Luke','luke.yang','24962821','luke.yang@rv88.tw',4,'2014-06-18','2017-04-16','0000-00-00','離職',4,25,1,0,6),(69,'R052','一般人員(行政/專技)',5,'技術支援工程師',10,'林永鋒','Dennis','dennis.lin','','dennis.lin@rv88.tw',5,'2015-04-01','0000-00-00','0000-00-00','正式',1,25,0,0,3),(70,'R018','組長',4,'組長',19,'邱柏洋','Quake','quake.chiu','24962821','quake.chiu@rv88.tw',4,'2013-12-02','0000-00-00','0000-00-00','正式',1,18,1,0,6),(71,'R002','組長',4,'組長',19,'張裕','Richard','richard.chang','24962821','richard.chang@rv88.tw',4,'2012-11-01','0000-00-00','0000-00-00','正式',1,16,1,0,6),(72,'R053','一般人員(行政/專技)',5,'資料庫操作員',14,'徐銘鴻','Leo','leo.hsu','','leo.hsu@rv88.tw',5,'2015-04-07','0000-00-00','0000-00-00','正式',1,16,0,0,3),(73,'R067','一般人員(行政/專技)',5,'資料庫管理工程師',4,'王嘉偉','Falcon','falcon.wang','','falcon.wang@rv88.tw',5,'2015-07-01','0000-00-00','0000-00-00','正式',1,16,0,0,4),(76,'R034','一般人員(行政/專技)',5,'網頁設計師',6,'吳敏絹','Joan','joan.wu','','joan.wu@rv88.tw',5,'2014-12-02','0000-00-00','0000-00-00','正式',1,23,0,0,3),(77,'R036','組長',4,'組長',19,'賴政男','Jemmy','jemmy.lai','24962821','jemmy.lai@rv88.tw',4,'2014-12-15','2017-01-31','0000-00-00','離職',4,23,0,0,6),(78,'R039','一般人員(行政/專技)',5,'網頁程式設計師',7,'張景翔','Chris','chris.chang','','chris.chang@rv88.tw',5,'2014-12-16','0000-00-00','0000-00-00','正式',1,23,0,0,3),(79,'R040','一般人員(行政/專技)',5,'技術支援工程師',10,'陳家德','Jader','jader.chen','','jader.chen@rv88.tw',5,'2014-12-22','0000-00-00','2017-02-20','正式',1,25,0,0,3),(80,'R058','組長',4,'組長',19,'莊格維','Snow','snow.jhung','24962821','snow.jhung@rv88.tw',4,'2015-04-13','0000-00-00','0000-00-00','正式',1,23,1,0,6),(81,'R064','一般人員(行政/專技)',5,'網頁程式設計師',7,'程晉鴻','Castle','castle.cheng','','castle.cheng@rv88.tw',5,'2015-06-01','0000-00-00','0000-00-00','正式',1,23,0,0,3),(82,'R066','一般人員(行政/專技)',5,'網頁程式設計師',7,'戴妙妃','Sophia','sophia.tai','','sophia.tai@rv88.tw',5,'2015-06-22','0000-00-00','0000-00-00','正式',1,23,0,0,3),(83,'R096','一般人員(行政/專技)',5,'網頁程式設計師',7,'朱德溎','Wade','wade.zhu','','wade.zhu@rv88.tw',5,'2015-09-21','0000-00-00','0000-00-00','正式',1,23,0,0,3),(85,'R009','處級主管',3,'處長',18,'楊劭儀','Hako','hako.yang','24962821','hako.yang@rv88.tw',3,'2013-04-15','0000-00-00','0000-00-00','正式',1,15,1,0,7),(86,'R035','一般人員(行政/專技)',5,'稽核專員',9,'陳孟成','Roy','roy.chen','','roy.chen@rv88.tw',5,'2014-12-02','0000-00-00','0000-00-00','正式',1,15,0,0,3),(87,'R037','一般人員(行政/專技)',5,'稽核專員',9,'黃子寧','Ruiza','ruiza.huang','','ruiza.huang@rv88.tw',5,'2014-12-15','0000-00-00','0000-00-00','正式',1,15,0,0,3),(89,'R139','一般人員(行政/專技)',5,'網頁設計師',6,'林威逸','Bryan','bryan.lin','','bryan.lin@rv88.tw',5,'2016-08-03','0000-00-00','0000-00-00','正式',1,23,0,0,3),(100,'R133','一般人員(行政/專技)',5,'客服專員',11,'李政庭','Tim','tim.lee','','tim.lee@rv88.tw',5,'2016-07-04','0000-00-00','0000-00-00','正式',1,17,0,0,3),(103,'R136','一般人員(行政/專技)',5,'客服專員',11,'陳葭','Jasmine','jasmine.chen','','jasmine.chen@rv88.tw',5,'2016-07-18','0000-00-00','0000-00-00','正式',1,17,0,0,3),(106,'R141','約聘人員',6,'工讀生',13,'鄭嘉宏','Red','red.cheng','','red.cheng@rv88.tw',6,'2016-08-29','2017-02-26','0000-00-00','離職',4,24,0,0,0),(108,'R144','約聘人員',6,'工讀生',13,'周晉平','Leo','leo.chou','','leo.chou@rv88.tw',6,'2016-08-31','2017-02-27','0000-00-00','離職',4,24,0,0,0),(111,'R147','一般人員(行政/專技)',5,'技術支援工程師',10,'蔡偉豪','Andy','andy.tsai','','andy.tsai@rv88.tw',5,'2016-09-05','0000-00-00','2017-03-01','正式',1,18,0,0,3),(116,'R148','一般人員(行政/專技)',5,'風控專員',15,'吳雅雯','Lucy','lucy.wu','','lucy.wu@rv88.tw',5,'2016-09-19','0000-00-00','2017-03-01','正式',1,10,0,0,3),(120,'R150','約聘人員',6,'工讀生',13,'林榆傑','Jay','jay.lin','','jay.lin@rv88.tw',6,'2016-10-03','2017-04-09','0000-00-00','離職',4,24,0,0,0),(121,'R152','一般人員(行政/專技)',5,'技術支援工程師',10,'馬瑞成','Ricky','ricky.ma','','ricky.ma@rv88.tw',5,'2016-10-11','0000-00-00','2017-02-20','正式',1,18,0,0,3),(124,'R045','一般人員(行政/專技)',5,'風控專員',15,'張振華','Walter','walter.chang','','walter.chang@rv88.tw',5,'2015-01-07','0000-00-00','0000-00-00','正式',1,10,0,0,3),(147,'R154','一般人員(行政/專技)',5,'系統工程師',8,'洪辰錐','CJ','cj.hung','','cj.hung@rv88.tw',5,'2016-10-24','2017-02-03','0000-00-00','離職',4,21,0,0,3),(148,'R155','約聘人員',6,'工讀生',13,'楊東諺','DonYan','donyan.yang','','donyan.yang@rv88.tw',6,'2016-10-24','0000-00-00','0000-00-00','約聘',2,24,0,0,0),(149,'R156','一般人員(行政/專技)',5,'技術支援工程師',10,'楊程筑','Eric','eric.yang','','eric.yang@rv88.tw',5,'2016-11-01','0000-00-00','0000-00-00','正式',1,18,0,0,3),(150,'R157','一般人員(行政/專技)',5,'技術支援工程師',10,'李承哲','Fred','fred.lee','','fred.lee@rv88.tw',5,'2016-11-01','0000-00-00','0000-00-00','正式',1,18,0,0,3),(199,'R158','約聘人員',6,'工讀生',13,'林鼎鈞','Jun','jun.lin','','jun.lin@rv88.tw',6,'2016-11-21','0000-00-00','0000-00-00','約聘',2,24,0,0,0),(201,'R160','約聘人員',6,'工讀生',13,'林哲賢','Jack','jack.lin','','jack.lin@rv88.tw',6,'2016-11-25','2017-02-23','0000-00-00','離職',4,24,0,0,0),(202,'R161','一般人員(行政/專技)',5,'技術支援工程師',10,'林佳穎','Joseph','joseph.lin','','joseph.lin@rv88.tw',5,'2016-12-01','0000-00-00','0000-00-00','正式',1,25,0,0,3),(203,'R162','一般人員(行政/專技)',5,'風控專員',15,'陳時仲','Aries','aries.chen','','aries.chen@rv88.tw',5,'2016-12-01','0000-00-00','0000-00-00','正式',1,10,0,0,3),(204,'R163','約聘人員',6,'工讀生',13,'袁書慧','Piggy','piggy.yuan','','piggy.yuan@rv88.tw',6,'2016-12-20','0000-00-00','0000-00-00','約聘',2,24,0,0,0),(205,'R164','一般人員(行政/專技)',5,'技術支援工程師',10,'謝文中','Donem','donem.hsieh','','donem.hsieh@rv88.tw',5,'2017-01-09','0000-00-00','0000-00-00','正式',1,19,0,0,3),(213,'R165','一般人員(行政/專技)',5,'系統工程師',8,'張超','Eric','eric.zhang','','eric.zhang@rv88.tw',5,'2017-02-02','2017-02-28','0000-00-00','離職',4,21,0,0,3),(214,'R170','一般人員(行政/專技)',5,'行政人員',5,'陳幸玉','Jade','jade.chen','','jade.chen@rv88.tw',5,'2017-02-13','0000-00-00','0000-00-00','試用',3,7,0,0,3),(215,'R176','一般人員(行政/專技)',5,'客服專員',11,'余采疄','Cailin','cailin.yu','','cailin.yu@rv88.tw',5,'2017-03-01','0000-00-00','0000-00-00','試用',3,6,0,0,3),(216,'R177','一般人員(行政/專技)',5,'客服專員',11,'張愷倫','Karen','karen.chang','','karen.chang@rv88.tw',5,'2017-03-01','0000-00-00','0000-00-00','試用',3,6,0,0,3),(217,'R167','一般人員(行政/專技)',5,'客服專員',11,'吳冠伶','Ling','ling.wu','','ling.wu@rv88.tw',5,'2017-02-06','0000-00-00','0000-00-00','試用',3,20,0,0,3),(218,'R174','一般人員(行政/專技)',5,'客服專員',11,'阮宗哲','Kevin','kevin.yuen','','kevin.yuen@rv88.tw',5,'2017-02-20','0000-00-00','0000-00-00','試用',3,20,0,0,3),(219,'R181','一般人員(行政/專技)',5,'客服專員',11,'陳銘','Flex','flex.chen','','flex.chen@rv88.tw',5,'2017-04-05','0000-00-00','0000-00-00','試用',3,20,0,0,3),(220,'R168','一般人員(行政/專技)',5,'客服專員',11,'洪靖旻','Zoe','zoe.hung','','jine-min.hung@rv88.tw',5,'2017-02-06','0000-00-00','0000-00-00','試用',3,17,0,0,3),(221,'R178','一般人員(行政/專技)',5,'客服專員',11,'陳孟伶','Lynn','lynn.chen','','lynn.chen@rv88.tw',5,'2017-03-01','0000-00-00','0000-00-00','試用',3,17,0,0,3),(222,'R179','一般人員(行政/專技)',5,'客服專員',11,'李家維','Vic','vic.lee','','vic.lee@rv88.tw',5,'2017-03-01','0000-00-00','0000-00-00','試用',3,17,0,0,3),(223,'R182','一般人員(行政/專技)',5,'客服專員',11,'楊詠蓁','Joy','joy.yang','','joy.yang@rv88.tw',5,'2017-04-05','0000-00-00','0000-00-00','試用',3,17,0,0,3),(224,'R171','約聘人員',6,'工讀生',13,'施宗甫','Eric','eric.shih','','eric.shih@rv88.tw',6,'2017-02-15','0000-00-00','0000-00-00','約聘',2,24,0,0,0),(225,'R172','約聘人員',6,'工讀生',13,'黃敏哲','Egan','egan.huang','','egan.huang@rv88.tw',6,'2017-02-20','0000-00-00','0000-00-00','約聘',2,24,0,0,0),(226,'R173','約聘人員',6,'工讀生',13,'黃忠政','Sky','sky.huang','','sky.huang@rv88.tw',6,'2017-02-20','0000-00-00','0000-00-00','約聘',2,24,0,0,0),(227,'R175','約聘人員',6,'工讀生',13,'陳宥達','Simon','simon.chen','','simon.chen@rv88.tw',6,'2017-02-21','0000-00-00','0000-00-00','約聘',2,24,0,0,0),(228,'R180','約聘人員',6,'工讀生',13,'林旻諺','MingYang','mingyang.lin','','ming-yang.lin@rv88.tw',6,'2017-03-28','0000-00-00','0000-00-00','約聘',2,24,0,0,0),(229,'R183','一般人員(行政/專技)',5,'系統工程師',8,'蕭睿廷','Ryan','ryan.hsiao','','ryan.hsiao@rv88.tw',5,'2017-04-05','0000-00-00','0000-00-00','試用',3,21,0,0,3),(230,'R184','一般人員(行政/專技)',5,'技術支援工程師',10,'李宗振','Tsung','tsung.lee','','tsung.lee@rv88.tw',5,'2017-04-05','0000-00-00','0000-00-00','試用',3,19,0,0,3),(231,'R185','一般人員(行政/專技)',5,'技術支援工程師',10,'許玉暉','Henry','henry.hsu','','henry.hsu@rv88.tw',5,'2017-04-05','0000-00-00','0000-00-00','試用',3,19,0,0,3),(232,'R186','一般人員(行政/專技)',5,'技術支援工程師',10,'曾彥嘉','Jack','jack.zeng','','jack.zeng@rv88.tw',5,'2017-04-05','0000-00-00','0000-00-00','試用',3,19,0,0,3),(233,'R166','一般人員(行政/專技)',5,'風控專員',15,'陳彥傑','Ian','ian.chen','','ian.chen@rv88.tw',5,'2017-02-02','0000-00-00','0000-00-00','試用',3,10,0,0,3),(234,'R169','一般人員(行政/專技)',5,'風控專員',15,'林明葆','Jimmy','jimmy.lin','','jimmy.lin@rv88.tw',5,'2017-02-06','0000-00-00','0000-00-00','試用',3,10,0,0,3),(235,'R187','一般人員(行政/專技)',5,'技術支援工程師',10,'王彥翔','David','david.wang','','david.wang@rv88.tw',5,'2017-04-17','0000-00-00','0000-00-00','試用',3,19,0,0,3);
/*!40000 ALTER TABLE `rv_staff` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rv_staff_post`
--

DROP TABLE IF EXISTS `rv_staff_post`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rv_staff_post` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT '職務',
  `type` varchar(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '職務名稱類別',
  `orderby` int(6) NOT NULL DEFAULT '1' COMMENT '排序順位',
  `enable` int(2) NOT NULL DEFAULT '1' COMMENT '1=ON,0=OFF',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rv_staff_post`
--

LOCK TABLES `rv_staff_post` WRITE;
/*!40000 ALTER TABLE `rv_staff_post` DISABLE KEYS */;
INSERT INTO `rv_staff_post` VALUES (1,'經理','管理職',23,1),(2,'資深技術支援工程師','專業職',7,1),(3,'資深人資專員','行政職',15,1),(4,'資料庫管理工程師','專業職',6,1),(5,'行政人員','行政職',13,1),(6,'網頁設計師','專業職',6,1),(7,'網頁程式設計師','專業職',6,1),(8,'系統工程師','專業職',7,1),(9,'稽核專員','專業職',6,1),(10,'技術支援工程師','專業職',6,1),(11,'客服專員','專業職',5,1),(12,'助理','其他',1,1),(13,'工讀生','其他',1,1),(14,'資料庫操作員','專業職',5,1),(15,'風控專員','專業職',5,1),(16,'襄理','管理職',24,1),(17,'營運長','管理職',25,1),(18,'處長','管理職',21,1),(19,'組長','管理職',20,1);
/*!40000 ALTER TABLE `rv_staff_post` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rv_staff_status`
--

DROP TABLE IF EXISTS `rv_staff_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rv_staff_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rv_staff_status`
--

LOCK TABLES `rv_staff_status` WRITE;
/*!40000 ALTER TABLE `rv_staff_status` DISABLE KEYS */;
INSERT INTO `rv_staff_status` VALUES (1,'正式'),(2,'約聘'),(3,'試用'),(4,'離職');
/*!40000 ALTER TABLE `rv_staff_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rv_staff_title_lv`
--

DROP TABLE IF EXISTS `rv_staff_title_lv`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rv_staff_title_lv` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT '職類',
  `lv` int(2) DEFAULT '5',
  `enable` int(2) NOT NULL DEFAULT '1' COMMENT '1=ON,0=OFF',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rv_staff_title_lv`
--

LOCK TABLES `rv_staff_title_lv` WRITE;
/*!40000 ALTER TABLE `rv_staff_title_lv` DISABLE KEYS */;
INSERT INTO `rv_staff_title_lv` VALUES (1,'決策人員',1,1),(2,'部級主管',2,1),(3,'處級主管',3,1),(4,'組長',4,1),(5,'一般人員(行政/專技)',5,1),(6,'約聘人員',6,1),(7,'其他職員',5,1),(8,'無',9,1),(9,'轉正人員',5,1);
/*!40000 ALTER TABLE `rv_staff_title_lv` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-04-18 16:42:34
