DROP TABLE IF EXISTS `rv_department_leaderships`;
CREATE TABLE IF NOT EXISTS `rv_department_leaderships` (
    `id` INT(11) PRIMARY KEY AUTO_INCREMENT,
    `department_id` INT(11) NOT NULL,
    `staff_id` INT(11) NOT NULL,
    `status` TINYINT(4) NOT NULL DEFAULT '1',
    `update_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `rv_monthly_report_evaluating`;
CREATE TABLE IF NOT EXISTS `rv_monthly_report_evaluating` (
    `id` INT(11) PRIMARY KEY AUTO_INCREMENT,
    `year` INT(11) NOT NULL,
    `month` INT(11) NOT NULL,
    `staff_id` INT(11) NOT NULL,
    `staff_id_evaluator` INT(11) NOT NULL,
    `staff_department_id` INT(11) NOT NULL,
    `evaluator_department_id` INT(11) NOT NULL,
    `report_type` INT(11) NOT NULL COMMENT '月表類型1=主管, 2=一般',
    `report_id` INT(11) NOT NULL COMMENT '',
    `status_code` INT(11) NOT NULL DEFAULT '0' COMMENT '報表狀態碼',
    `submitted` TINYINT(4) NOT NULL DEFAULT '0' COMMENT '是否提交',
    `should_count` TINYINT(4) NOT NULL DEFAULT '1' COMMENT '是否算分',
    `json_data` VARCHAR(2048) DEFAULT '{}' COMMENT '詳細評分內容',
    `update_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `rv_monthly_processing_evaluating`;
CREATE TABLE IF NOT EXISTS `rv_monthly_processing_evaluating` (
    `id` INT(11) PRIMARY KEY AUTO_INCREMENT,
    `year` INT(11) NOT NULL,
    `month` INT(11) NOT NULL,
    `staff_id` INT(11) NOT NULL,
    `staff_department_id` INT(11) NOT NULL,
    `processing_department_id` INT(11) NOT NULL,
    `processing_id` INT(11) NOT NULL COMMENT '進程id',
    `status_code` INT(11) NOT NULL DEFAULT '1' COMMENT '報表狀態碼',
    `first_submit_date` TIMESTAMP DEFAULT '0000-00-00 00:00:00' COMMENT '第一次提交時間',
    `update_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;