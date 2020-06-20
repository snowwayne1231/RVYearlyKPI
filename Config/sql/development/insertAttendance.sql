INSERT into rv_attendance (staff_id,date,checkin_hours,checkout_hours,work_hours_total,late,early,nocard,remark,vocation_hours,vocation_from,vocation_to,overtime_from,overtime_hours,overtime_to) 
 
(SELECT staff_id, DATE_ADD(date,INTERVAL 1 MONTH) as date ,checkin_hours,checkout_hours,work_hours_total,late,early,nocard,remark,vocation_hours,vocation_from,vocation_to,overtime_from,overtime_hours,overtime_to 
FROM `rv_attendance` where YEAR(date)=2017 and MONTH(date)=12)