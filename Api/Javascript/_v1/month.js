
/**
  //取得所有組織列表、並檢察組織關係
  //必須 year, mouth
 */
self.getDepartmentList = function(data){
  return $.post(Data_PATH+"getDepartmentList", data);
}

/**
  //取得登入的職員所在的組織，以下的所有組織列表
 */
self.getLowerDepartmentList = function(data){
  return $.post(Data_PATH+"getLowerDepartmentList", data);
}
/**
//檢察組織關係 產生 報表,考評表
  //可選 year, mouth, del
 */
self.checkDepartment = function(data){
  
  return $.post(Data_PATH+"getDepartmentList?&check=true", data);
}
/**
//取得創作者的月績效考評表
  //必須
  //可選 year, month, staff_id
 */
self.getMonthlyProcessWithCreator = function(data){
  
  return $.post(Data_PATH+"Monthly/getMonthlyProcessWithCreator", data);
}
/**
//取得當前擁有者的月績效考評表
  //必須
  //可選 year, month, staff_id
 */
self.getMonthlyProcessWithOwner = function(data){
  
  return $.post(Data_PATH+"Monthly/getMonthlyProcessWithOwner", data);
}
/**
//取得當月 月績效考評表
  //必須 yaer, month (super user)
  //可選 status_code
 */
self.getMonthlyProcessByAdmin = function(data){
  
  return $.post(Data_PATH+"Monthly/getMonthlyProcessByAdmin", data);
}
/**
//取得已經核准的月績效報表
  //必須 year, month
  //可選 release
 */
self.getMonthlyReportWhenRelease = function(data){
  
  return $.post(Data_PATH+"Monthly/getMonthlyReportWhenRelease", data);
}
/**
//下載已經核准的月績效報表
  //必須 year, month
  //可選 release
 */
self.downloadMonthlyReportWhenRelease = function(data){
  if(!(data && data.year && data.month)){ return false; }
  return window.open(Excel_PATH+"downloadMonthly?year="+data.year+"&month="+data.month+(data.release?"&release=true":"")+(data.department_id?"&department_id="+data.department_id:""),'_blank');
}
/**
//取得月績效報表
  //必須 processing_id  |or|  manager_id, year, month  |or|  department_id, year, month
 */
self.getMonthlyReport = function(data){
  
  return $.post(Data_PATH+"Monthly/getMonthlyReport", data);
}

/**
//儲存修改的月績效報表
  //必須 report[ id,processing_id ]
  //可選 一般 report[ quality, completeness, responsibility, cooperation, attendance, addedValue, mistake, bonus]
  //可選 主管 report[ target, quality, method, error, backtrack, planning, execute, decision, resilience, attendance, attendance_members, addedValue, mistake, bonus]
 */
self.saveReport = function(data){
  
  return $.post(Data_PATH+"Monthly/saveReport", data);
}
/**
//送審核月績效考評表
  //必須 processing_id
  //可選 reason
 */
self.commitMonthly = function(data){
  
  return $.post(Data_PATH+"Monthly/commitMonthly", data);
}
/**
//必須 processing_id
 */
self.getMonthlyRejectList = function(data){
  
  return $.post(Data_PATH+"Monthly/getRejectList", data);
}
/**
//必須 processing_id, staff_id
  //可選 reason , turnback = 1 / 0 (是 admin 系統管理員)
 */
self.rejectMonthly = function(data){
  
  return $.post(Data_PATH+"Monthly/rejectMonthly", data);
}
/**
//開始啟用 月考評
  //必須 year, month   (是 admin 系統管理員)
 */
self.launchMonthly = function(data){
  
  return $.post(Data_PATH+"Monthly/launchMonthly", data);
}
/**
//暫時關閉 月考評
  //必須 year, month   (是 admin 系統管理員)
 */
self.pauseMonthly = function(data){
  
  return $.post(Data_PATH+"Monthly/pauseMonthly", data);
}
/**
//取得月考評單歷史記錄
  //必須 processing_id
 */
self.getMonthlyProcessHistory = function(data){
  
  return $.post(Data_PATH+"Monthly/getMonthlyProcessHistory", data)
}


/**
//新增評論
  //必須 staff_id, year ,month ,content  |or|  report_id, report_type ,content
 */
self.addComment = function(data){
  
  return $.post(Data_PATH+"addComment", data);
}
/**
//取得評論
  //必須 staff_id, year ,month  |or|  report_id, report_type
 */
self.getComment = function(data){
  
  return $.post(Data_PATH+"getComment", data);
}
/**
//更新評論
  //必須 comment_id, do=>del  |or|   comment_id, do=>upd, content
 */
self.updateComment = function(data){
  
  return $.post(Data_PATH+"updateComment", data);
}
/**
//取得區間設定
  //必須 year, month
 */
self.getCycleConfig = function(data){
  
  return $.post(Setting_PATH+"getCycleConfig", data);
}
/**
 //更新區間設定
 //必須 year, month, day_start, day_end, day_cut_addition
 */
self.updateCycleConfig = function(data){
  
  return $.post(Setting_PATH+"updateCycleConfig", data);
}

/**
 * ========== 月績效不記分人員 ==========
 * @param {object} data {
           'report_type' // (主管/leader) = 1 , (一般人/staff/general) = 2
           'report_id',  // 月績效報表id
           'exception',  // 例外=1  正常=0
           ['reason']    // 理由
          }
 * @return boolean
 */
self.updateMonthlyNoScore = function(data){
   return $.post(Data_PATH+"updateMonthlyNoScore", data);
}
/**
 * 抽單
 * @modifyDate 2017-10-01
 * @param      {object}                 data {
 *                                            processing_id, //必填 流程id
 *                                            reason,        //理由
 * }
 * @return     {[type]}                      [description]
 */
self.drawSingle = function(data){
  return $.post(Data_PATH+"Monthly/drawSingle", data);
}

/**
 * 列出可以抽單的列表
 * @modifyDate 2017-10-01
 * @param      {[type]}                 data [description]
 * @return     {[type]}                      [description]
 */
self.getDrawSingle = function(data) {
  return $.post(Data_PATH+"Monthly/getDrawSingle", data); 
}

/**
 * 列出個人一個區間內的 績效表現
 * @modifyDate 2017-10-20
 * @param      必須 year_start
 * @param      必須 year_end
 * @param      必須 month_start
 * @param      必須 month_end
 * @param      必須 staff_id
 * @return     {
    staff_info :{               //員工基本訊息
      staff_no,
      name,                       //名稱
      name_en,
      department_id,              //單位
      department_name,
      department_code,
      title,                      //職類
      post,
      status,
      first_day,                  //到職日
    },
    monthly_info:{              //月績效基本資訊
      addedValue,                 //加總特殊貢獻
      mistake,                    //加總重大缺失
      average,                    //月績效平均值
      analysis_leader,            //主管能力分析百分率  ['quality','target','method','error','backtrack','planning','execute','decision','resilience','attendance','attendance_members']
      analysis_normal,            //ㄧ般員功能力分析百分率  ['quality','completeness','responsibility','cooperation','attendance']
    },
    monthly_every :[            //月績效報表
      {
        id,                       //月報表id
        year,                 
        month,
        total,                    //當月總分
        releaseFlag,              //是否核准 Y/N
        exception,                //是否例外不計分 0=正常 1=不計分
        exception_reason,         //不計分理由
        _comment_count,           //評論總數
        _comments :[              //評論
          {
            report_id,              //月報表id
            content,                //評論內容
            create_staff_id,        //評論人
            create_time,            //評論時間
            create_staff_name,      //評論人名
            create_staff_name_en,   //評論人英文名
          }
        ]
      }
    ],
    attendance_info :{          //出缺席 資訊
      base:{                      //基本資訊
        late,                       //遲到次數
        early,                      //早退次數
        nocard,                     //沒帶卡次數
        forgetcard,                 //忘刷卡次數
        leave,                      //事假次數 
        paysick,                    //有薪病假
        sick,                       //半薪病假
        absent,                     //曠職
        overtime,                   //加班
        relax,                      //加班補休
        working : {                 //上班實數細節
          should_hours,               //正常工時
          total_hours,                //上班工時數加總
        }
      },
      exception_date :[           //有紀錄的非正常上班
        date,                       //日期
        checkin_hours,              //打卡上班
        checkout_hours,             //打卡下班
        late,                       //遲到分鐘數
        early,                      //早退分鐘數
        remark,                     //備註
        work_hours_total,           //當日工時數
        vocation_hours,             //請假時數
        minute,                     //打卡分鐘數
      ]
    }
 }
 */
 
self.getDetailMonthlyByPerson = function(data){
  return $.post(Data_PATH+"Monthly/getDetailMonthlyByPerson", data); 
}


/**
 * ========== 下載2017年 1-3月 月考評分 考評單Excel ==========
 * @param   必須  file( 格式=download_2016_YearlyAssessment )
 * @return  {
   update_count,  //成功更新的資料
   insert_count,  //成功加入的資料
   status,        //狀態
 }
 */
self.upload_2017_123_Monthly = function(file){
  return $.ajax({
    url : Excel_PATH+"upload_2017_123_Monthly",
    type : "POST",
    data : file,
    dataType : "JSON",
    cache : false,
    contentType : false,
    processData: false
  });
}


/**
 * ========== 匯入2017年 1-3月 月考評 考評單Excel ==========
 * @param   none
 * @return  Excel
 */
self.download_2017_123_Monthly = function(){
  var url = Excel_PATH+"download_2017_123_Monthly";
  downloadExcel(url, new Date().getTime());
}


/**
 * 取得各級評分
 * @param processing_id
 * @return Object
 */
self.getMonthlyEvaluatingReportByProcess = function(processing_id) {
  return $.post(Data_PATH+"Monthly/getMonthlyEvaluatingReportByProcess", {processing_id});
}