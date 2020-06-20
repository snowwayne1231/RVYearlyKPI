--查出沒有被對應到的 評語ID
select a.id from rv_record_personal_comment as a
left join (select id,comment_id from rv_monthly_report) as b on a.report_id = b.id and a.report_type = 2
left join (select id,comment_id from rv_monthly_report_leader) as c on a.report_id = c.id and a.report_type = 1
where b.id is null and c.id is null

--更新 評語資料
update rv_record_personal_comment as a
left join (select id,staff_id from rv_monthly_report where year=2018 and month=10) as b on a.target_staff_id = b.staff_id and a.report_type = 2
left join (select id,staff_id from rv_monthly_report_leader where year=2018 and month=10) as c on a.target_staff_id = c.staff_id and a.report_type = 1
set
a.report_id = if(b.id>0, b.id, c.id)
where a.id in (這邊帶入上面查出來的評語ID)

--更新 月績效
select id, report_id, report_type from rv_record_personal_comment where id in (這邊帶入上面查出來的評語ID) and status >0;
