-- 月考評-主管，新增不計分理由
ALTER TABLE `rv_monthly_report_leader`
  ADD COLUMN `exception_reason` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '不計分原因' AFTER `exception`;