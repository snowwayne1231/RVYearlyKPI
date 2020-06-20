-- phpMyAdmin SQL Dump
-- version 4.6.5.2
-- https://www.phpmyadmin.net/
--
-- 主機: 127.0.0.1
-- 產生時間： 2018-10-22 12:00:24
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
-- 資料表結構 `rv_staff_event`
--

CREATE TABLE `rv_staff_event` (
  `id` int(11) NOT NULL COMMENT 'ID',
  `name` varchar(20) NOT NULL COMMENT '事件名稱'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='職員事件';

--
-- 資料表的匯出資料 `rv_staff_event`
--

INSERT INTO `rv_staff_event` (`id`, `name`) VALUES
(1, '到職'),
(2, '考核通過'),
(3, '換單位'),
(4, '留停開始'),
(5, '預計留停結束'),
(6, '留停結束延遲'),
(7, '復職');

--
-- 已匯出資料表的索引
--

--
-- 資料表索引 `rv_staff_event`
--
ALTER TABLE `rv_staff_event`
  ADD PRIMARY KEY (`id`);

--
-- 在匯出的資料表使用 AUTO_INCREMENT
--

--
-- 使用資料表 AUTO_INCREMENT `rv_staff_event`
--
ALTER TABLE `rv_staff_event`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID', AUTO_INCREMENT=8;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
