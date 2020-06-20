UPDATE `rv_monthly_report` SET `total` = 0 WHERE `total` < 0 AND `releaseFlag` = 'Y';
UPDATE `rv_monthly_report_leader` SET `total` = 0 WHERE `total` < 0 AND `releaseFlag` = 'Y';