INSERT INTO rv_staff_history_event(staff_id, status, event_day, department_id, post_id, title_id, create_date, update_date)
SELECT
 id AS staff_id,
 status_id AS status,
 first_day AS event_day,
 department_id,
 post_id,
 title_id,
 first_day AS create_date,
 first_day AS update_date
FROM rv_staff
WHERE status_id IN(1,2,3);

UPDATE rv_staff_history_event SET event = 1 WHERE status = 1;
UPDATE rv_staff_history_event SET event = 1 WHERE status = 2;
UPDATE rv_staff_history_event SET event = 1 WHERE status = 3;

INSERT INTO rv_staff_history_event(staff_id, status, event_day, department_id, post_id, title_id, create_date, update_date)
SELECT
 id AS staff_id,
 status_id AS status,
 first_day AS event_day,
 department_id,
 post_id,
 title_id,
 first_day AS create_date,
 first_day AS update_date
FROM rv_staff
WHERE status_id = 4;
UPDATE rv_staff_history_event SET status = 1, event = 1 WHERE status = 4;

INSERT INTO rv_staff_history_event(staff_id, status, event_day, department_id, post_id, title_id, create_date, update_date)
SELECT
 id AS staff_id,
 status_id AS status,
 last_day AS event_day,
 department_id,
 post_id,
 title_id,
 last_day AS create_date,
 last_day AS update_date
FROM rv_staff
WHERE status_id = 4;
UPDATE rv_staff_history_event SET event = 9 WHERE status = 4;