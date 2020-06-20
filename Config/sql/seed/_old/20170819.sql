-- 年考評紀錄修改 新增 reason : 記錄修改理由
ALTER TABLE `rv_record_year_performance_report`
  ADD COLUMN `reason` VARCHAR(512)   NOT NULL DEFAULT ''  COMMENT '更改的理由' AFTER `changed_json`;

-- 年考評 新增 reason : 記錄修改理由
ALTER TABLE `rv_year_performance_report`
  ADD COLUMN `reason` VARCHAR(512)   NOT NULL DEFAULT ''  COMMENT '更改的理由' AFTER `upper_comment`;