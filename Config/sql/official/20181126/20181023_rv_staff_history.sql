-- phpMyAdmin SQL Dump
-- version 4.6.5.2
-- https://www.phpmyadmin.net/
--
-- 主機: 127.0.0.1
-- 產生時間： 2018-10-23 11:33:22
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
-- 資料表結構 `rv_staff_history`
--

CREATE TABLE `rv_staff_history` (
  `id` int(11) NOT NULL COMMENT 'ID',
  `staff_id` int(11) NOT NULL COMMENT '職員ID',
  `modify_field` varchar(20) NOT NULL COMMENT '修改欄位',
  `old` varchar(20) NOT NULL COMMENT '舊資料',
  `new` varchar(20) NOT NULL COMMENT '新資料',
  `start_time` date NOT NULL COMMENT '開始時間',
  `end_time` date NOT NULL DEFAULT '0000-00-00' COMMENT '結束時間',
  `create_date` datetime NOT NULL COMMENT '建立時間',
  `update_date` datetime NOT NULL COMMENT '更新時間'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='職員歷史修改紀錄';

--
-- 已匯出資料表的索引
--

--
-- 資料表索引 `rv_staff_history`
--
ALTER TABLE `rv_staff_history`
  ADD PRIMARY KEY (`id`);

--
-- 在匯出的資料表使用 AUTO_INCREMENT
--

--
-- 使用資料表 AUTO_INCREMENT `rv_staff_history`
--
ALTER TABLE `rv_staff_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID';
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
