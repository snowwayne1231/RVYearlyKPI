-- rv_year_performance_feedback 新增記錄 員工 當下的職類
ALTER TABLE `rv_year_performance_feedback`
  ADD COLUMN `staff_title_id` INT(2) NOT NULL DEFAULT '0' COMMENT '員工職類id' AFTER `staff_id`,
  ADD COLUMN `staff_title` VARCHAR(50) NOT NULL DEFAULT '0' COMMENT '員工職類' AFTER `staff_title_id`,
  ADD COLUMN `target_staff_title_id` INT(2) NOT NULL DEFAULT '0' COMMENT '目標職類id' AFTER `target_staff_id`,
  ADD COLUMN `target_staff_title` VARCHAR(50) NOT NULL DEFAULT '0' COMMENT '目標職類' AFTER `target_staff_title_id`;

-- rv_year_performance_report 新增記錄 員工 當下的職務類別id
ALTER TABLE `rv_year_performance_report`
  ADD COLUMN `staff_title_id` INT(2) NOT NULL COMMENT '職務類別id' AFTER `staff_title`;