-- SET @year = 2017;
-- SET @month = 5;
SELECT @year := 2018;
SELECT @month := 5;


-- 普通
INSERT INTO rv_monthly_report(
	title_id,staff_id,post_id,year,month,quality,completeness,responsibility,cooperation,attendance,releaseFlag,processing_id,owner_staff_id,owner_department_id
)
(
	SELECT
		a.title_id, a.id AS staff_id, a.post_id,
		@year AS year,
		@month AS month,
		Floor(RAND()*4+1) AS quality,
		Floor(RAND()*4+1) AS completeness,
		Floor(RAND()*4+1) AS responsibility,
		Floor(RAND()*4+1) AS cooperation,
		Floor(RAND()*4+1) AS attendance,
		'Y' AS releaseFlag,
		-1 AS processing_id,
		IF(b.manager_staff_id = 0, b.supervisor_staff_id, b.manager_staff_id) AS owner_staff_id,
		b.id AS owner_department_id
	FROM rv_staff AS a
	LEFT JOIN rv_department AS b ON a.department_id = b.id
	LEFT JOIN rv_monthly_report AS c ON c.month = @month
	WHERE a.status < 4 AND (c.id IS NULL) AND a.is_leader = 0
);

-- 主管
INSERT INTO rv_monthly_report_leader(
	title_id,staff_id,post_id,year,month,
	quality,method,error,backtrack,planning,execute,decision,resilience,attendance,attendance_members,
	releaseFlag,processing_id,owner_staff_id,owner_department_id
)
(
	SELECT
		a.title_id, a.id AS staff_id, a.post_id,
		@year AS year,
		@month AS month,
		Floor(RAND()*4+1) AS quality,
		Floor(RAND()*4+1) AS method,
		Floor(RAND()*4+1) AS error,
		Floor(RAND()*4+1) AS backtrack,
		Floor(RAND()*4+1) AS planning,
		Floor(RAND()*4+1) AS execute,
		Floor(RAND()*4+1) AS decision,
		Floor(RAND()*4+1) AS resilience,
		Floor(RAND()*4+1) AS attendance,
		Floor(RAND()*4+1) AS attendance_members,
		'Y' AS releaseFlag,
		-1 AS processing_id,
		IF(up_b.manager_staff_id=0,1,up_b.manager_staff_id) AS owner_staff_id,
		IF(up_b.manager_staff_id=0,1,up_b.id) AS owner_department_id
	FROM rv_staff AS a
	LEFT JOIN rv_department AS b ON a.department_id = b.id
	LEFT JOIN rv_monthly_report_leader AS c ON c.month = @month
	LEFT JOIN rv_department AS up_b ON b.upper_id = up_b.id
	WHERE a.status < 4 AND (c.id IS NULL) AND a.is_leader=1 AND a.id > 1
);

-- 分數
UPDATE rv_monthly_report AS a SET total = (a.quality*5 + a.completeness*5 + a.responsibility*5 + a.cooperation*3 + a.attendance*2)+a.addedValue-a.mistake;

UPDATE rv_monthly_report_leader AS b SET total = (b.target*2 + b.quality*2 + b.method*2 + b.error*2 + b.backtrack*2 + b.planning*2 + (b.execute*7/5) + (b.decision*7/5) + (b.resilience*6/5) + b.attendance*2 + b.attendance_members*2) + b.addedValue - b.mistake;

UPDATE rv_monthly_report AS c
LEFT JOIN rv_department AS d ON c.owner_department_id = d.id
SET total = (c.quality*5 + c.completeness*5 + c.responsibility*3 + c.cooperation*3 + c.attendance*4)+c.addedValue-c.mistake
WHERE d.duty_shift=1;