-- phpMyAdmin SQL Dump
-- version 4.6.5.2
-- https://www.phpmyadmin.net/
--
-- 主機: 127.0.0.1
-- 產生時間： 2018-10-23 11:33:59
-- 伺服器版本: 10.1.21-MariaDB
-- PHP 版本： 5.6.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 資料庫： `new_hr_qa3`
--

-- --------------------------------------------------------

--
-- 資料表結構 `rv_staff_history_event`
--

CREATE TABLE `rv_staff_history_event` (
  `id` int(11) NOT NULL COMMENT 'ID',
  `staff_id` int(11) NOT NULL COMMENT '職員ID',
  `status` int(11) NOT NULL COMMENT '職員狀態',
  `event` int(11) NOT NULL COMMENT '事件ID',
  `event_day` date NOT NULL COMMENT '事件日期',
  `department_id` int(11) NOT NULL COMMENT '部門ID',
  `post_id` int(11) NOT NULL COMMENT '職務ID',
  `title_id` int(11) NOT NULL COMMENT '職務類別ID',
  `create_date` datetime NOT NULL COMMENT '建立時間',
  `update_date` datetime NOT NULL COMMENT '更新時間'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='職員事件歷史紀錄';

--
-- 已匯出資料表的索引
--

--
-- 資料表索引 `rv_staff_history_event`
--
ALTER TABLE `rv_staff_history_event`
  ADD PRIMARY KEY (`id`);

--
-- 在匯出的資料表使用 AUTO_INCREMENT
--

--
-- 使用資料表 AUTO_INCREMENT `rv_staff_history_event`
--
ALTER TABLE `rv_staff_history_event`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID';
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
