update `rv_year_performance_feedback` set 
multiple_choice_json = 
case  
   when id mod 3 = 1 then '[{"id":1,"ans":0,"score":5},{"id":2,"ans":0,"score":5},{"id":3,"ans":0,"score":5},{"id":11,"ans":0,"score":5},{"id":5,"ans":0,"score":5},{"id":6,"ans":0,"score":5},{"id":7,"ans":0,"score":5},{"id":8,"ans":0,"score":5},{"id":9,"ans":0,"score":5},{"id":10,"ans":0,"score":5}]' 
   when id mod 3 = 2 then '[{"id":1,"ans":1,"score":3},{"id":2,"ans":1,"score":3},{"id":3,"ans":1,"score":3},{"id":11,"ans":1,"score":3},{"id":5,"ans":1,"score":3},{"id":6,"ans":1,"score":3},{"id":7,"ans":1,"score":3},{"id":8,"ans":1,"score":3},{"id":9,"ans":1,"score":3},{"id":10,"ans":1,"score":3}]'
   when id mod 3 = 0 then '[{"id":1,"ans":2,"score":1},{"id":2,"ans":2,"score":1},{"id":3,"ans":2,"score":1},{"id":11,"ans":2,"score":1},{"id":5,"ans":2,"score":1},{"id":6,"ans":2,"score":1},{"id":7,"ans":2,"score":1},{"id":8,"ans":2,"score":1},{"id":9,"ans":2,"score":1},{"id":10,"ans":2,"score":1}]'
end ,
multiple_score = 
case  
   when id mod 3 = 1 then 50
   when id mod 3 = 2 then 30
   when id mod 3 = 0 then 10
end ,
status = 1 
where status = 0;