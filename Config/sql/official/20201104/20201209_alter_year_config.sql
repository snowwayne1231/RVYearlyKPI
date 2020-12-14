ALTER TABLE `rv_year_performance_config_cyclical` DROP IF EXISTS promotion_c_to_b;

ALTER TABLE `rv_year_performance_config_cyclical` ADD promotion_c_to_b INT(2) DEFAULT '1' AFTER assessment_ids;
UPDATE `rv_year_performance_config_cyclical` SET promotion_c_to_b = 0 WHERE year < 2020;
