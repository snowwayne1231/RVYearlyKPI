delete from `rv_email_template` where `name` = 'monthly_draw';

INSERT INTO `rv_email_template` (`name`, `title`, `text`, `update_date`) VALUES ('monthly_draw', '【考評通知】{year}年{month}月 {department} 績效評核表抽單', '<h3>您好：</h3><p>本月份送審之【 {department} 】績效評核表已被 {staff_name} 給抽回</p><p> 入口網址: <a target="_blank" href="http://{URL}">{URL}</a> </p> <p> 帳     號：員編 </p> <p>密     碼：身分證字號（預設） </p> <br><br> <p>人力資源處</p>', '2017-08-25 11:05:28');