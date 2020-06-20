ALTER TABLE `rv_record_year_performance_report`
  CHANGE COLUMN `changed_json` `changed_json` TEXT NULL COMMENT '改變的內容' COLLATE 'utf8_unicode_ci' ;
ALTER TABLE `rv_record_year_performance_report`
  ADD COLUMN `origin_json` TEXT NULL COMMENT '年考績原始欄位資料' AFTER `type`;