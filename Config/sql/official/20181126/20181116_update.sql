ALTER TABLE `rv_monthly_processing` COMMENT = '月考評進程' ROW_FORMAT = COMPACT;
ALTER TABLE `rv_monthly_report` COMMENT = '月考評單_一般人員' ROW_FORMAT = COMPACT;
ALTER TABLE `rv_monthly_report_leader` COMMENT = '月考評單_管理職';
ALTER TABLE `rv_record_monthly_processing` COMMENT = '紀錄：月考評進程' ROW_FORMAT = COMPACT;
ALTER TABLE `rv_record_monthly_report` COMMENT = '紀錄：月考評單' ROW_FORMAT = COMPACT;
ALTER TABLE `rv_config_cyclical` COMMENT = '月考評週期設定' ROW_FORMAT = COMPACT;