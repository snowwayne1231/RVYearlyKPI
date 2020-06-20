-- 新增資料表備註
ALTER TABLE `rv_monthly_processing` COMMENT = '月考評進程';
ALTER TABLE `rv_monthly_report` COMMENT = '月考評單_一般人員';
ALTER TABLE `rv_monthly_report_leader` COMMENT = '月考評單_管理職';
ALTER TABLE `rv_record_monthly_processing` COMMENT = '紀錄：月考評進程';
ALTER TABLE `rv_record_monthly_report` COMMENT = '紀錄：月考評單';
ALTER TABLE `rv_config_cyclical` COMMENT = '月考評週期設定';

-- 月考評表 新增 產生時間 & 更新時間
ALTER TABLE `rv_monthly_report` ADD `create_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `exception_reason`, ADD `update_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `create_date`;

-- 月考評表 新增產生時間 & 更新時間
ALTER TABLE `rv_monthly_report_leader` ADD `create_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `exception_reason`, ADD `update_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `create_date`;

-- 建立 月考評暫存表
CREATE TABLE `rv_monthly_report_leader_tmp` LIKE `rv_monthly_report_leader`;
CREATE TABLE `rv_monthly_report_tmp` LIKE `rv_monthly_report`;

-- 職員資料表 添加時間備註
ALTER TABLE `rv_staff` CHANGE `update_date` `update_date` DATE NULL DEFAULT '0000-00-00' COMMENT '換單位日期';



-- ---------------- --
-- 建立 職員事件 表 --
-- ---------------- --
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE TABLE `rv_staff_event` (
  `id` int(11) NOT NULL COMMENT 'ID',
  `name` varchar(20) NOT NULL COMMENT '事件名稱'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='職員事件';


INSERT INTO `rv_staff_event` (`id`, `name`) VALUES
(1, '到職'),
(2, '考核通過'),
(3, '換單位'),
(4, '留停開始'),
(5, '預計留停結束'),
(6, '留停結束日期異動'),
(7, '復職');


ALTER TABLE `rv_staff_event` ADD PRIMARY KEY (`id`);

ALTER TABLE `rv_staff_event`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID', AUTO_INCREMENT=8;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


-- 新增職員狀態 (留停)
INSERT INTO `rv_staff_status` (`id`, `name`) VALUES (NULL, '留停');


-- ------------------------ --
-- 建立 職員歷史修改紀錄 表 --
-- ------------------------ --
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

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


ALTER TABLE `rv_staff_history` ADD PRIMARY KEY (`id`);

ALTER TABLE `rv_staff_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID';
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


-- ------------------------ --
-- 建立 職員事件歷史紀錄 表 --
-- ------------------------ --
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE TABLE `rv_staff_history_event` (
  `id` int(11) NOT NULL COMMENT 'ID',
  `staff_id` int(11) NOT NULL COMMENT '職員ID',
  `status` int(11) NOT NULL COMMENT '職員狀態',
  `event` int(11) NOT NULL COMMENT '事件ID',
  `event_day` date NOT NULL COMMENT '事件日期',
  `note` VARCHAR(200) NOT NULL COMMENT '備註',
  `department_id` int(11) NOT NULL COMMENT '部門ID',
  `post_id` int(11) NOT NULL COMMENT '職務ID',
  `title_id` int(11) NOT NULL COMMENT '職務類別ID',
  `create_date` datetime NOT NULL COMMENT '建立時間',
  `update_date` datetime NOT NULL COMMENT '更新時間'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='職員事件歷史紀錄';

ALTER TABLE `rv_staff_history_event` ADD PRIMARY KEY (`id`);

ALTER TABLE `rv_staff_history_event`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID';
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


-- ---------------------- --
-- 新增現任職員的歷史記錄 --
-- ---------------------- --
INSERT INTO rv_staff_history(staff_id, new, start_time, create_date, update_date)
SELECT
 id AS staff_id,
 status_id AS new,
 first_day AS start_time,
 first_day AS create_date,
 first_day AS update_date
FROM rv_staff
WHERE status_id IN(1,2,3);

UPDATE rv_staff_history SET modify_field = 'status', old = '0';

INSERT INTO rv_staff_history(staff_id, start_time, end_time, create_date, update_date)
SELECT
 id AS staff_id,
 first_day AS start_time,
 last_day AS end_time,
 first_day AS create_date,
 last_day AS update_date
FROM rv_staff
WHERE status_id = 4;

UPDATE rv_staff_history SET modify_field = 'status', old = '0', new = '1' WHERE modify_field = '';

#==================================================
INSERT INTO rv_staff_history(staff_id, new, start_time, create_date, update_date)
SELECT
 id AS staff_id,
 department_id AS new,
 first_day AS start_time,
 first_day AS create_date,
 first_day AS update_date
FROM rv_staff
WHERE status_id IN(1,2,3);

INSERT INTO rv_staff_history(staff_id, new, start_time, end_time, create_date, update_date)
SELECT
 id AS staff_id,
 department_id AS new,
 first_day AS start_time,
 last_day AS end_time,
 first_day AS create_date,
 last_day AS update_date
FROM rv_staff
WHERE status_id = 4;

UPDATE rv_staff_history SET modify_field = 'department', old = '0' WHERE modify_field = '';

#==================================================
INSERT INTO rv_staff_history(staff_id, new, start_time, create_date, update_date)
SELECT
 id AS staff_id,
 CONCAT(post_id,'#',title_id) AS new,
 first_day AS start_time,
 first_day AS create_date,
 first_day AS update_date
FROM rv_staff
WHERE status_id IN(1,2,3);

INSERT INTO rv_staff_history(staff_id, new, start_time, end_time, create_date, update_date)
SELECT
 id AS staff_id,
 CONCAT(post_id,'#',title_id) AS new,
 first_day AS start_time,
 last_day AS end_time,
 first_day AS create_date,
 last_day AS update_date
FROM rv_staff
WHERE status_id = 4;

UPDATE rv_staff_history SET modify_field = 'post', old = '0' WHERE modify_field = '';

INSERT INTO rv_staff_history_event(staff_id, status, event_day, department_id, post_id, title_id, create_date, update_date)
SELECT
 id AS staff_id,
 status_id AS status,
 first_day AS event_day,
 department_id,
 post_id,
 title_id,
 first_day AS create_date,
 first_day AS update_date
FROM rv_staff
WHERE status_id IN(1,2,3);

UPDATE rv_staff_history_event SET event = 1 WHERE status = 1;
UPDATE rv_staff_history_event SET event = 1 WHERE status = 2;
UPDATE rv_staff_history_event SET event = 1 WHERE status = 3;

INSERT INTO rv_staff_history_event(staff_id, status, event_day, department_id, post_id, title_id, create_date, update_date)
SELECT
 id AS staff_id,
 status_id AS status,
 first_day AS event_day,
 department_id,
 post_id,
 title_id,
 first_day AS create_date,
 first_day AS update_date
FROM rv_staff
WHERE status_id = 4;
UPDATE rv_staff_history_event SET status = 1, event = 1 WHERE status = 4;

INSERT INTO rv_staff_history_event(staff_id, status, event_day, department_id, post_id, title_id, create_date, update_date)
SELECT
 id AS staff_id,
 status_id AS status,
 last_day AS event_day,
 department_id,
 post_id,
 title_id,
 last_day AS create_date,
 last_day AS update_date
FROM rv_staff
WHERE status_id = 4;
UPDATE rv_staff_history_event SET event = 9 WHERE status = 4;