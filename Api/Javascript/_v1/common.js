/**
 *   解析格式
 */
self.format = function(jsf){
  return new grenalJSONFormat(jsf);
}
/**
  登入
  @param 必須 username, passwd
 */
self.loginWithData = function(data){
  return $.post(Member_PATH+"login", data);
}

/**
  登出

 */
self.logout = function(){
  return $.get(Member_PATH+"logout");
}

/**
  上傳出缺勤
  @param 必須 file
 */
self.addAbsence = function(file){
  return $.ajax({
    url : Absence_PATH+"add",
    type : "POST",
    data : file,
    dataType : "JSON",
    cache : false,
    contentType : false,
    processData: false
  });
}

/**
  取得出缺勤
  @param 必須 year, month
         可選 team_id[], staff_id[]
 */
self.getAbsence = function(data){
  return $.post(Absence_PATH+"get", data);
}

/**
  下載出缺勤 excel 表
  @param 必須 year, month
         可選 team_id[], staff_id[]
 */
self.downloadAbsence = function(data){
  var str = '',ary=[];
  for(var i in data){
    ary.push(i+'='+data[i]);
  }
  str = ary.join('&');
  return window.open(Excel_PATH+"downloadAbsence?"+str,'_blank');
}

/**
  取得自己底下的員工 ( 包含自己 )
  @param 可選 model (admin=ALL 限系統管理者使用, department=自己部門, rank=職等比自己低)
 */
self.getUnderStaff = function(data){
  return $.post(Data_PATH+"getUnderStaff", data);
}

/**
  取得所有單位
  @param
 */
self.getAllDepartment = function(){
  return $.get(Data_PATH+"getAllDepartment");
}

/**
  取得所有員工基礎資料
  @param 必須 是 super user
 */
self.getAllStaff = function(){
  return $.get(Data_PATH+"getAllStaff");
}

/**
  取得指定員工LOG資料
  @id  integer 職員ID
 */
self.getStaffLog = function(data){
  return $.post(Data_PATH+"getStaffLog", data);
}

/**
  所有職稱
  @param 必須 是 super user
 */
self.getAllStaffPost = function(){
  return $.get(Data_PATH+"getAllStaffPost");
}

/**
  所有職務
  @param 必須 是 super user
 */
self.getAllStaffTitleLv = function(){
  return $.get(Data_PATH+"getAllStaffTitleLv");
}

/**
  所有在職狀態
  @param 必須 是 super user
 */
self.getAllStaffStatus = function(){
  return $.get(Data_PATH+"getAllStaffStatus");
}

/**
  新增單位
  @param 必須 lv,unit_id, name, upper_id
 */
self.addDepartment = function(data){
  return $.post(Setting_PATH+"addDepartment",data);
}

/**
  更新單位
  @param 必須 id
         可選 upper_id, unit_id, name, manager_staff_id, enable, duty_shift
 */
self.updateDepartment = function(data){
  return $.post(Setting_PATH+"updateDepartment",data);
}

/**
  新增員工
  @param 必須 department_id, staff_no, account, passwd, name, name_en, email, status_id, title_id, post_id
         可選 first_day, last_day, update_date, rank
 */
self.addStaff = function(data){
  return $.post(Setting_PATH+"addStaff",data);
}

/**
  更新員工
  @param 必須 id, department_id
  @param 可選 passwd, name, name_en, email, first_day, last_day, update_date, status_id, title_id, post_id, is_admin, rank
 */
self.updateStaff = function(data){
  return $.post(Setting_PATH+"updateStaff",data);
}

/**
  下載員工 excel 表
  @param 必須 admin
 */
self.downloadStaffExcel = function(data){
  // return $.post(Setting_PATH+"downloadStaffExcel",data);
  return window.open(Excel_PATH+"downloadStaffExcel",'_blank');
}

/**
  上傳員工資料
  @param 必須 file  xls, xlsx
 */
self.batchStaffDataWithExcel = function(file){
  return $.ajax({
    url : Setting_PATH+"batchStaffDataWithExcel",
    type : "POST",
    data : file,
    dataType : "JSON",
    cache : false,
    contentType : false,
    processData: false
  });
}


/**
 *  更新設置 職務類別
 *  @param      必須 title_id
 *
 *  @param      可選 name
 *  @param      可選 enable (軟刪除用 1=開啟, 0=關閉)
 *  @return
 */
self.updateSettingTitle = function(data){
  return $.post(Setting_PATH+"updateSettingTitle", data);
}

/**
 *  新增設置 職務類別
 *  @param      必須 name
 *
 *  @param      必須 lv   (職務類別的等級 合理值為1-6整數，用來判斷該職務能否勝任主管職，數值越小；職位越大。 1=可任職執行長, 2=可任職部長, 3=處長, 4=組長, 5=正式員工, 6=試用)
 *  @return
 */
self.addSettingTitle = function(data){
  return $.post(Setting_PATH+"addSettingTitle", data);
}

/**
 *  更新設置 職務
 *  @param      必須 post_id
 *
 *  @param      可選 name
 *  @param      可選 type     (職務名稱類型描述，暫時還沒用到 可填可不填)
 *  @param      可選 orderby  (排序順序 數字越大越前面 1-100整數)
 *  @param      可選 enable   (軟刪除用 1=開啟, 0=關閉)
 *  @return
 */
self.updateSettingPost = function(data){
  return $.post(Setting_PATH+"updateSettingPost", data);
}

/**
 *  新增設置 職務
 *  @param      必須 name
 *
 *  @param      可選 type     (職務名稱類型描述，暫時還沒用到 可填可不填)
 *  @param      可選 orderby  (排序順序 數字越大越前面 1-100整數)
 *  @param      可選 enable   (軟刪除用 1=開啟, 0=關閉)
 *  @return
 */
self.addSettingPost = function(data){
  return $.post(Setting_PATH+"addSettingPost", data);
}


/**
 *  下載 忘刷/帶卡 的情況
 *  @param    必須 year
 *  @return   Excel
 */
self.downloadForgetCard = function(data){
  var url = Excel_PATH+"downloadForgetCard?year="+data.year;
  return downloadExcel(url, new Date().getTime());
}


/**
 *  上傳 忘刷/帶卡
 *  @param    必須 file Excel
 *  @return
 */
self.uploadForgetCard = function(file){
  return $.ajax({
    url : Excel_PATH+"uploadForgetCard",
    type : "POST",
    data : file,
    dataType : "JSON",
    cache : false,
    contentType : false,
    processData: false
  });
}

/**
 *  取得操作紀錄
 *  @param  必須  count   取得多少數量
 *  @param  必須  type    取得類型     0=全部,  1=組織設定, 2=月績效, 3=年考評, 4=月報表, 5=年報表, 6=EXCEL,  7=人員
 *  @return  {
 *    system : {              //系統操作的紀錄
 *      [
 *        id,                   //記錄id
 *        operating_staff_id,   //操作者id
 *        type,                 //操作類型  1=系統  2=月區間  3=年區間  4=月報表  5=年報表  6=Excel
 *        doing,                //操作動作  1=新增  2=更新  3=刪除
 *        api,                  //API端口路徑
 *        update_date,          //操作日期
 *        ip,                   //操作者當時ip
 *        name,                 //操作者名稱
 *        name_en,              //操作者英文名
 *        _operating,           //API路徑 轉換的中文釋名
 *      ]
 *    },
 *    staff : {               //人員操作的紀錄
 *      [
 *        id,                     //記錄id
 *        operating_staff_id,     //操作者
 *        operating_staff_name,
 *        operating_staff_name_en,
 *        staff_id,               //被修改的人
 *        staff_name,
 *        staff_name_en,
 *        doing,                  //操作動作  1=新增  2=更新  3=刪除
 *        update_date,            //操作日期
 *        ip,                     //操作者當時ip
 *      ]
      }
 *  }
 */
self.getAdminOperatingRecord = function(data){
  return $.post(Setting_PATH+"getAdminOperatingRecord", data);
}

/**
 *  取得操作記錄詳細資訊
 *  @param  必須  type   紀錄類型  1=system操作,  2=staff操作
 *  @param  必須  id     記錄的id
 *  @return  {
 *    //暫時先不用  更改內容 只有開發看得懂
    }
 */
self.getAdminOperatingRecordDetail = function(data){
  return $.post(Setting_PATH+"getAdminOperatingRecordDetail", data);
}

/**
 *  取得首頁必須資訊
 *  @param
 *
 */
self.getMyIndex = function(data){
  return $.post(Data_PATH+"getMyIndex", data);
}

/**
 * Carmen 測試區
 */
self.carmenTest = function(data){
  return $.post(API_PATH+"test/carmen", data);
}


/**
 * getDepartmentLeadership
 * @param 必須 department_id
 * 
 */

self.getDepartmentLeadership = function(data) {
  return $.post(Setting_PATH+"getDepartmentLeadership", data);
}


/**
 * upsertDepartmentLeadership
 * @param staff_id, department_id, is_leader_group
 * @param id, is_leader_group 
 * >> 無法使用，回傳錯誤訊息為 Staff Is Not In Department.
 */

self.upsertDepartmentLeadership = function(data) {
  return $.post(Setting_PATH+"upsertDepartmentLeadership", data);
}


/**
 * 
 */

 self.uploadForgetCardMonthly = function(data) {
  return $.ajax({
    url : Excel_PATH+"uploadMonthlyForgetCard",
    type : "POST",
    data : data,
    dataType : "JSON",
    cache : false,
    contentType : false,
    processData: false
  });
 }