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