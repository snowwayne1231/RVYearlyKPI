ALTER TABLE `rv_year_performance_report` DROP IF EXISTS assessment_evaluating_json;
ALTER TABLE `rv_year_performance_report` DROP IF EXISTS own_department_id;
ALTER TABLE `rv_year_performance_report` DROP IF EXISTS path_lv_leaders;

ALTER TABLE `rv_year_performance_report` ADD assessment_evaluating_json VARCHAR(2048) DEFAULT '[]' AFTER assessment_json;
ALTER TABLE `rv_year_performance_report` ADD own_department_id INT(11) DEFAULT 0 AFTER owner_staff_id;
ALTER TABLE `rv_year_performance_report` ADD path_lv_leaders VARCHAR(255) DEFAULT '[]' AFTER path_lv;
ALTER TABLE `rv_year_performance_report` MODIFY upper_comment VARCHAR(8096) DEFAULT '';