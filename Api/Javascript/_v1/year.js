/**
 *  @method  取得年績效設定
 *  @param   必須 year
 *  @return  {
 *    year :                    //年度
 *    processing :              //年績效的進程 --> 0 未啟動, 1 部屬回饋產生, 2 部屬回饋收集, 3 部屬回饋關閉, 4 產生年考績, 5 收集年考績, 6 暫停收集, 7 完成收集年考績, 8 完成加減分調整, 9 進入歷史資料
 *    date_start :              //年績效的開始日
 *    date_end :                //年績效的結算日
 *    feedback_addition_day :   //部屬回饋考核天數
 *    feedback_date_start :     //部屬回饋開始日
 *    feedback_date_end :       //部屬回饋結束日
 *    assessment_addition_day : //年考績效考核天數
 *    assessment_date_start :   //年考績效開始日
 *    assessment_date_end :     //年考績效結束日
 *    update_date :             //更新日期
 *
 *  }
 */
self.getYearlyConfig = function(data){
  return $.post(Setting_PATH+"getYearCycleConfig",data);
}

/**
 *  @method  更新年績效設定
 *  @param   必須 year
 *  @param   可選 date_start
 *  @param   可選 date_end
 *  @param   可選 feedback_addition_day
 *  @param   可選 assessment_addition_day
 *  @return  #{{getYearlyConfig}}
 */
self.updateYearlyConfig = function(data){
  return $.post(Setting_PATH+"updateYearCycleConfig",data);
}

/**
 * 取得年度組織結構
 * @modifyDate 2017/9/14
 * @param      必須 year
 * @param      可選 reset  (帶此參數會重設年度組織結構)
 * @return     {array}
 * [{
 *     id,                  //流水號
 *     lv,                  //部門等級
 *     manager_staff_id,    // 該部門主管員工id
 *     name,                //單位名稱
 *     supervisor_staff_id, //該部門最高管理者員工id
 *     unit_id,             //單位id
 *     upper_id,            //上層單位id
 *     path_department,     //有主管的 單位路徑
 *     staff: [{
 *               id,                //員工id
 *               department_id,     //員工單位id
 *               lv,                //員工等級
 *               name,              //員工中文姓名
 *               name_en,           //員工英文姓名
 *               rank,              //員工rank
 *               staff_no,          //員工編號
 *               status_id,         //員工狀態 1:正式, 2: 約聘, 3: 試用, 4: 離職
 *               title_id,          //員工職稱id
 *               title,             //員工職稱類別
 *               post,              //員工職稱
 *               _can_feedback,     //是否參加部屬問卷回饋
 *               _can_assessment,   //是否參加年度考評
 *             }]
 * }]
 */
self.getYearlyConstruct = function(data){
  return $.post(Data_PATH+"Yearly/getYearlyConstruct",data);
}

/**
 *  更新年度組織結構
 *  @param   必須 year
 *  @param   必須 staff_id
 *  @param   可選 department_id => 該員工換至指定新單位
 *  @param   可選 feedback => 是否參加部屬問卷回饋 ; 1=參加 ,0=不參加
 *  @param   可選 assessment => 是否參加年考評 ; 1=參加 ,0=不參加
 *  @return  #{{getYearlyConstruct}}
 */
self.updateYearlyConstructStaff = function(data){
  return $.post(Data_PATH+"Yearly/updateYearlyConstructStaff",data);
}

/**
 *  @method  檢查/產生 年部屬回饋問卷 processing = 1
 *  @param   必須 year
 *  @param   可選 reset  (processing = 0)
 *  @return  {
 *    status:   //狀態
 *    change:   //改變的數量
 *    processing//進度
 *  }
 */
self.checkYearlyFeedback = function(data){
  return $.post(Data_PATH+"Yearly/checkYearlyFeedback",data);
}

/**
 *  @method  啟動 部屬回饋問卷 可以送交 processing = 2
 *  @param   必須 year
 *  @return  {
 *    status:   //狀態
 *    change:   //改變的數量
 *    processing//進度
 *  }
 */
self.launchYearlyFeedback = function(data){
  return $.post(Data_PATH+"Yearly/launchYearlyFeedback",data);
}

/**
 *  @method  取得 年部屬回饋問卷
 *  @param   必須 year
 *  @return  {
 *    "feedback" : [
 *      {
 *        id :    //部屬回饋問卷 id
 *        year :  //部屬回饋問卷年
 *        target_staff_id :  //受評人ID
 *        target_staff_name :  //受評人員中文姓名
 *        target_staff_name_en :  //受評人員英文姓名
 *        target_staff_post :  //受評人職務
 *        target_unit_id :  //受評單位代號
 *        target_unit_name :  //受評單位名
 *        department_id :  //自己單位ID
 *        department_name :  //自己單位名
 *        unit_id :  //自己單位代號
 *        multiple_choice_json : //選擇題JSON
 *      }
 *    ],
 *    "choice" : [    //該年選擇題
 *      {id, title, description, options_json, score}
 *    ],
 *    "question" : {  //該年問答題
 *      "normal" : [],
 *      "others" : [],
 *      "company" : []
 *    }
 *  }
 */
self.getYearlyFeedback = function(data){
  return $.post(Data_PATH+"Yearly/getYearlyFeedback",data);
}

/**
 *  @method  取得所有主管
 *  @param   可選 year    //有給年 就是當年的主管、 沒給年就會是取得 當前組織的主管
 *  @return  [{
 *    id :        //主管id
 *    staff_no :  //人員編號
 *    title :     //職稱類別
 *    post :      //職稱
 *    name :      //人名
 *    name_en :   //英文名
 *  }]
 *
 */
self.getAllLeader = function(data){
  return $.post(Data_PATH+"getAllLeader",data);
}

/**
 *  @method  儲存年部屬回饋問卷 選擇題
 *  @param   必須 feedback_id
 *  @param   必須 multiple_choice[ {id:answer,..,..} ]
 *  @return  {
 *    status :      //狀態
 *    change :      //成功送交的內容 (有可能是內容重覆被過濾)
 *  }
 */
self.saveYearlyFeedback = function(data){
  return $.post(Data_PATH+"Yearly/saveYearlyFeedback",data);
}

/**
 *  @method  送出年部屬回饋問卷 選擇題
 *  @param   必須 feedback_id
 *  @param   必須 normal_questions[ {id:content,..} ]
 *  @param   可選 multiple_choice[ {id:answer,id:answer,..} ]
 *  @return  {
 *    id :                  //部屬回饋問卷的id
 *    department_id :       //部門id
 *    multiple_choice_json
 *    multiple_score:
 *    multiple_total
 *    staff_id
 *    staff_title
 *    staff_title_id
 *    status
 *    target_staff_id
 *    target_staff_title
 *    target_staff_title_id
 *    update_date
 *  }
 */
self.commitYearlyFeedback = function(data){
  return $.post(Data_PATH+"Yearly/commitYearlyFeedback",data);
}

/**
 *  @method  送出問卷回饋的問答題 (其他或公司)
 *  @param   必須 year
 *  @param   可選 others_questions[ {staff_id : {id:content,..},..} ]
 *  @param   可選 company_questions[ {id:content,..} ]
 *  @return  {
 *    status :      //狀態
 *    change :      //成功送交的內容 (有可能是內容重覆被過濾)
 *  }
 */
self.commitYearlySuggestion = function(data){
  return $.post(Data_PATH+"Yearly/commitYearlySuggestion",data);
}

/**
 *  @method  關閉年部屬回饋問卷 processing = 3
 *  @param   必須 year
 *  @return  { year, processing }
 */
self.closeYearlyFeedback = function(data){
  return $.post(Data_PATH+"Yearly/closeYearlyFeedback",data);
}

/**
 *  @method  確認該年的 月考評/出缺勤/部屬回饋問卷是否完整
 *  @param   必須 year
 *  @return  { feedback, attendance, monthly }
 */
self.checkYearly = function(data){
  return $.post(Data_PATH+"Yearly/checkYearly",data);
}

/**
 *  @method  檢查/產生 年考績 processing = 4
 *  @param   必須 year
 *  @param   可選 reset  (processing = 3)
 *  @return  { status, change, processing }
 */
self.checkYearlyAssessment = function(data){

  return $.post(Data_PATH+"Yearly/checkYearlyAssessment",data);
}

/**
 *  @method  取得該年度的 年考績主題
 *  @param   必須 year
 *  @return  {
 *    normal :[   //一般員工題目
 *      id:         //題目id
 *      name:       //題目名稱
 *      score:      //題目最大得分
 *      type:       //題目類型id
 *      type_name:  //題目類型名稱
 *    ],
 *    leader :[   //主管題目
 *      ...
 *    ]
 *  }
 */
self.getYearlyTopic = function(data){
  return $.post(Data_PATH+"Yearly/getYearlyTopic",data);
}

/**
 *  @method  取得 當前擁有的 年考績報表
 *  @param   必須 year
 *  @param   可選 assessment_id         //有給 id 就用id選, 反之給所有當前自己擁有的報表
 *  @param   可選 mode                  //如果 mode=self 只會回應自己的評分單且擁有者也是自己, mode=leader 回應包括各組還未評人員資料
 *  @return
 *  id :                      //年考績報表 id
    year :                    //年度
    staff_id :                //員工 id
    owner_staff_id :          //當前擁有者的 id
    department_id :           //單位id
    division_id :             //單位結構中，從屬"部門" id
    staff_is_leader :         //員工是否為主管
    staff_lv :                //員工職務類別的  lv
    staff_post :              //員工職務
    staff_title :             //員工職務類別
    staff_title_id :          //員工職務類別id
    staff_name :              //員工名稱
    staff_first_day :         //員工到職日
    staff_name_en :           //員工英文名
    rank :                    //員工職等
    enable :                  //是否作廢  1=正常 0=做作廢
    staff_no :                //員工編號
    processing_lv :           //當前年考績表 所在等級 , 4=組, 3=處, 2=部, 1=中心 0=不能再送了 做完了
    path :                    //該單會經過的每個主管 {lv:staff_id} 可以用來判斷 當前登入者能否修改多個 單位階級的分數
    before_level :            //去年考評級距等級
    monthly_average :         //今年月績效平均分數
    attendance_json :  {      //年績效區間內的出缺勤
      late :                    //遲到天數
      early :                   //早退天數
      nocard :                  //忘刷卡天數
      leave :                   //事假天數
      paysick :                 //有薪病假
      physiology :              //生理假
      sick :                    //病假
      absent :                  //曠職
    }
    assessment_json : {       //考績分數json
      [lv] :  {                 //每個階層lv 相應得到的分數 , 4=組, 3=處, 2=部, 1=中心, self=自評, undder=部屬回饋
        percent :                 //當階層的分數所占百分比例
        total :                   //當階層合計分數
        score : {topic_id:分數}   //當階層的每個單項目分數
      },..
      _tc : {                   //依照題目類別id 對應平均分數
        id : score                //
      }
    }
    assessment_total :        //年考績單總結分數
    assessment_total_division_change //年考績單部長加減分
    assessment_total_ceo_change //年考績單執行長/決策者加減分
    level :                   //年考績單最後被定義的 級距等級
    self_contribution :       //員工自我貢獻描述
    self_improve :            //員工自我改善描述
    upper_commentstaff_id : { //上層上司的評論
      lv : {                    //每個階層lv 相應的評論內容  4=組, 3=處, 2=部, 1=中心
        staff_id :                //評論人員的id
        content :                 //評論內容
      }
    }
    reason :                  //其他 註記 理由
    update_date :             //更新該單的時間
    department_code :         //單位代號
    department_name :         //單位名稱
    division_code :           //部門代號
    division_name :           //部門名稱
    date_start :              //年考評起始日
    date_end :                //年考評結束日
    _authority : {            //權限
      edit :                    //當前可以編輯/儲存
      return :                  //當前可以退回
      commit :                  //當前可以送出
      isFinished :              //當前是否已經完成， 用此來判斷是否可以進行 部長/執行長 加減分
    }
 */
self.getYearlyAssessment = function(data){
  return $.post(Data_PATH+"Yearly/getYearlyAssessment",data);
}

self.getYearlyAssessmentScoreDetailByAdmin = function(data) {
  return $.post(Data_PATH+"Yearly/getYearlyAssessmentScoreDetailByAdmin", data);
}

/**
 *  @method  取得 該人的部屬回饋/其他回饋
 *  @param   必須 year
 *  @param   必須 staff_id
 *  @return  {
 *    *form* : {          //*from*代表四種來源的key值  可能的範圍為 : under=部屬回饋, far=其他同仁, upper=上司, other=其他
 *      question_id : {     //依照題目ID分類所有評論
 *        title :             //該題目標題
 *        description :       //該題目描述
 *        contents : [        //該題目所有評論的內容
 *          content :           //評論內容
 *          create_date :       //評論時間
 *        ]
 *      }
 *    }
 *  }
 */
self.getYearlyQuestionsWithStaff = function(data){
  return $.post(Data_PATH+"Yearly/getYearlyQuestionsWithStaff",data);
}

/**
 *  @method  啟動年考績評分 可提交 processing = 5
 *  @param   必須 year
 *  @return  {status, change, processing}
 */
self.launchYearlyAssessment = function(data){
  return $.post(Data_PATH+"Yearly/launchYearlyAssessment",data);
}

/**
 *  @method  停止年考績評分 不可提交 processing = 6
 *  @param   必須 year
 *  @return  {status, change, processing}
 */
self.closeYearlyAssessment = function(data){
  return $.post(Data_PATH+"Yearly/closeYearlyAssessment", data);
}

/**
 *  @method  儲存 年考績
 *  @param   必須 assessment_id
 *  @param   可選 assessment_json       //分數       {lv:{ id:score , .. , .. },..}   //  lv=對應單位的等級 ，如果是自評 則是 self
 *  @param   可選 self_contribution     //自我貢獻
 *  @param   可選 self_improve          //自我改進
 *  @param   可選 comment               //留言/評語  {lv:content}                     //  lv=對應單位的等級 ，如果是自評 則是 self
 *  @return  {
 *    status :            //儲存狀態
 *    assessment_json :   //修改之後的分數
 *    self_contribution   //修改之後的自我貢獻
 *    self_improve        //修改之後的自我改進
 *    level               //結算的時候的等級級距
 *    upper_comment       //主管們的評論
 *  }
 */
self.saveYearlyAssessment = function(data){
  return $.post(Data_PATH+"Yearly/saveYearlyAssessment",data);
}

/**
 *  @method  送出 年考績
 *  @param   必須 assessment_id
 *  @return  {
   *  status,         //儲存狀態
   *  chagnge,        //變更數
   *  processing_lv,  //修改之後的單階層
   *  owner_staff_id, //修改之後的擁有者
   *  turn_can_fix,   //修改之後  如果 = true 代表這單提交 讓該 部門的部長加減分生效， 反之一般來說會是 false
 *  }
 */
self.commitYearlyAssessment = function(data){
  return $.post(Data_PATH+"Yearly/commitYearlyAssessment",data);
}

/**
 *  @method  退回 年考績
 *  @param   必須 assessment_id
 *  @param   可選 reason
 *  @return  {status, change, processing_lv, owner_staff_id }
 */
self.rejectYearlyAssessment = function(data){
  return $.post(Data_PATH+"Yearly/rejectYearlyAssessment",data);
}

/**
 * 取得年考績的部門詳細 配比用
 * @method getYearlyDivisionZone
 * @param  必須  year
 * @return array
 * [{
 *    'date_start',        //開始時間
 *    'date_end',          //結束時間
 *    'division',          //部門id
 *    'division_name',     //部門名稱
 *    'id',                //該部門的配比單id
 *    'owner_staff_id',    //目前當前擁有的員工ID
 *    'processing',        //目前的進程 : 0 = 初始 , 1 = 部長加減分 , 2 = 部長 commit , 3 = 架構發展部確認 , 4 = ceo加減分 , 5 = ceo確認
 *    'status',            //狀態 0 = 初始 , 1 = 除了部長本身還沒，其他都收齊(可以行使部長加減分) , 5 = 全部收齊(可以部長/執行長加減分)
 *    'unit_id',           //單位id,
 *    'year',              //該年度
 *    '_canfix_ceo',       //CEO 是否可以加減分 true:可以，false:不可以修改
 *    '_canfix_division',  //部門主管 是否可以加減分 true:可以，false:不可以修改
 *    '_distribution':     //各級距的人數分佈
 *    [{
          'count' ,        // 符合的人數
          'lv',            // 等級，數字由低至高，愈低等級愈高
          'name',          // 評等等級名稱
          'rate_least',    // 該評等的百分比下限
          'rate_limit',    // 該評等的百分比上限
          'score_least',   // 該評等的最低分數
          'score_limit',   // 該評等的最高分數
 *    }],
 *    '_reports' :         //該部門所有部署的評等資料
 *    [{
          'assessment_total',                 //該考評的原始總分
          'assessment_total_ceo_change',      //CEO的加減分
          'assessment_total_division_change', //部門主管的加減分
          'department_code',                  //單位代號
          'department_name',                  //單位名稱
          'division_id',                      //部門id
          'id',                               //該考評的id
          'level',                            //該考評的評等
          'owner_staff_id',                   //該考評目前擁有修改權限的員工id
          'staff_name',                       //該考評的 員工中文姓名
          'staff_name_en',                    //該考評的 員工英文姓名
          'staff_no',                         //該考評的 員工編號
          'staff_post',                       //該考評的 員工職務
          'staff_title',                      //該考評的 員工職稱
          'total',                            //該考評的總分(原始總分+CEO的加減分+部門主管的加減分)
 *    }]
 * }]
 */
self.getYearlyDivisionZone = function(data){
  return $.post(Data_PATH+"Yearly/getYearlyDivisionZone",data);
}

/**
 *  @method  //取得每個等級有幾%人
 *  @param   必須 year
 *
 */
self.getDistributionRate = function(data){
  return $.post(Data_PATH+"Yearly/getYearlyDistributionRate", data);
}

/**
 * 設置加減分  部長/CEO
 * @modifyDate 2017-10-11
 * @param      必須 division_id       //部門單的ID
 * @param      必須 assessment_change = {id:score,..} //加減分的分數
 *
 * @return     [assessment,..]
 */
self.setFinallyScoreFix = function(data){

  return $.post(Data_PATH+"Yearly/setFinallyScoreFix",data);
}

/**
 * 把收集到部門單的年考績做往上呈的動作
 * @param  必須 division_id
 * @return {divisions}
 */
self.commitDivisionZone = function(data){
  //把收集到部門單的年考績做往上呈的動作
  return $.post(Data_PATH+"Yearly/commitDivisionZone", data);
}

 /**
 * 把收集到部門單的年考績做駁回的動作
 * @param   必須 division_id
 * @return  {divisions}
 */
self.rejectDivisionZone = function(data){
  return $.post(Data_PATH+"Yearly/rejectDivisionZone", data);
}

/**
 *  @method  整年全部從頭   (如變成歷史資料後 就不能刪除了)
 *  @param   必須 year
 *  @return  {year_config}
 */
self.resetYearly = function(data){
  return $.post(Data_PATH+"Yearly/resetYearly",data);
}


/**
 *  @method  取得部屬回饋 對公司的
 *  @param   必須 year
 *  @return  [
 *    {
 *      id :            //評論id
 *      highlight :     //是否關注  1=關注  0=沒有關注
 *      content :       //內容
 *      create_date :   //評論日期
      }
 *  ]
 */
self.getCompanyQuestions = function(data){
  return $.post(Data_PATH+"Yearly/getPerformanceQuestions",data);
}

/**
 *  @method  儲存需要關注的題目  取消跟關注 做在同一個
 *  @param   必須 id 評論id
 *  @param   必須 highlight 0:取消，1:關注
 */
self.lightQuestion = function(data){
  return $.post(Data_PATH+"Yearly/lightQuestion",data);
}

/**
 *  @method  取得年考績總覽
 *  @param   必須 year
 *  @param   必須 department_level //單位等級   0=全部 , 1~5=單位階級lv
 *  @param   可選 with_assignment  //要包含助理
 *  @param   可選 is_over          //是否評分結束
 *  @return  {
 *    assessment : {
 *      leader : [year_assessment]
 *      staff : [year_assessment]
 *    }
 *    distribution : [
 *      lv :            //階級  lv1最高 依序往後
 *      name :          //級名稱
 *      score_least :   //分數至少
 *      score_limit :   //分數上限
 *      rate_least :    //該級距人數最少百分率
 *      rate_limit :    //該級距人數上限百分率
 *      count :         //該級距人數
 *    ]
 *  }
 */
self.getYearlyReportTotal = function(data){

  return $.post(Data_PATH+"Yearly/getYearlyReportTotal",data);
}

/**
 * ========== 取得年考績 員工意見回饋 ==========
 * @method getYearlyAllReportWord
 * @param  必須 assessment_id     //年考績id
 * @return array [
             {
               'questions' :        //問答題 array
                [
                  {
                     'question_id'             //問答題id
                     'question_title'          //問答題標題
                     'question_description'    //問答題描述
                     'year'                    //年份
                     'from_type'               //來源類型 1=部屬, 2=其他部門, 3=上司, 4=其他
                     'content'                 //內容
                     'create_date'             //建立時間
                  },..
                ],
               'self_contribution'             //自我貢獻
               'self_improve'                  //自我缺點
               'upper_comment' :               //主管評語
                {
                   "_1":                       // _單位lv
                    {
                      "staff_id",              // 評論主管id
                      "staff_name",            // 評論主管name
                      "staff_title",           // 評論主管title
                      "content"                // 評論內容
                    },..
                }
               'update_date'                   //年考績報表更新時間
             },..
          ]
 */
self.getYearlyAllReportWord = function(data){
  return $.post(Data_PATH+"Yearly/getYearlyAllReportWord", data);
}

/**
 * ========== 取得年考績 歷史流程記錄 ==========
 * @param  必須  assessment_id
 * @return array [
                   {
                     'type'                   //操作類型  0=創立, 1=儲存, 2=提交, 3=完成, 4=退回, 5=其他
                     'from'                   //本來的 staff id
                     'from_name'              //本來的 staff name
                     'from_name_en'           //本來的 staff name_en
                     'to'                     //目標的 staff id
                     'to_name'                //目標的 staff name
                     'to_name_en'             //目標的 staff name_en
                     'title'                  //動作名稱
                     'reason'                 //理由
                     'assessment_json'        //當時分數
                   },..
                 ]
 */
self.getYearlyHistoryRecord = function(data){
  return $.post(Data_PATH+"Yearly/getYearlyHistoryRecord", data);
}

/**
 * ========== 取得年度 特殊人員列表(月考評不計分) ==========
 * @method getYearlySpecialStaff
 * @param  {object} data {'year'}
 * @return array [
            {
              'staff_id'         //staff id
              'staff_name'       //員工名
              'staff_name_en'    //員工英文
              'staff_no'         //員工編號
              'staff_title'      //員工職類
              'staff_post'       //員工職稱
              'staff_status'     //員工狀態
              'unit_code'        //單位代號
              'department_id'    //單位 id
              'department_name'  //單位名稱
              'exceptions' : [   //例外 不計分
                {
                  'year'         //年
                  'month'        //月
                  'reason'       //理由
                },..
              ]
            },..
          ]
 */
self.getYearlySpecialStaff = function(data){
  return $.post(Data_PATH+"Yearly/getYearlySpecialStaff", data);
}

/**
 * 取得該年度部屬回饋問卷的統計  (管理者)
 * @modifyDate 2017-10-17
 * @param      必須 year
 * @return     {
    total :           //總共有問卷的人數
    received :        //目前收到的數量

 }
 */
self.getYearlyFeedBackStatistics = function(data) {
  return $.post(Data_PATH+"Yearly/getYearlyFeedBackStatistics",data);
}


/**
 * 取得年度部屬回饋問卷列表    (管理者)
 * @modifyDate 2017-10-18
 * @param      必須 year
 * @param      可選 feedback_id
 * @return     [
    {
      id,                         //問卷id
      staff_id,
      staff : {                   //評分人員
        name,
        name_en,
      },
      target_staff_id,
      target_staff : {            //受評主管
        name,
        name_en,
      },
      department_id,              //受評單位
      department : {
        unit_id,
        name
      }
      multiple_choice_json : {    //選擇題分數
         question_id : score
      },
      multiple_score              //選擇題總分
    }
   ]
 */
self.getYearlyFeedbackList = function(data){
  //取得年度部屬回饋問卷列表
  return $.post(Data_PATH+"Yearly/getYearlyFeedbackList",data);
}

/**
 * 管理者取得 監控當前考評狀態
 * @modifyDate 2017-10-17
 * @param      必須  year
 * @return      {
    report_info : {         //當年報表的收集狀況
      total,                  //總人數
      valid,                  //有效人數
      finished,               //完成人數
      pending,                //進行人數
    }
    reports : [             //當年報表簡單資訊
      id,                     //報表id
      staff_id,               //員工..
      staff_post,
      staff_title,
      staff_name,
      staff_name_en,
      staff_no,
      owner_staff_id,         //當前擁有員工
      owner_staff_name,
      owner_staff_name_en,
      owner_staff_no,
      processing_lv,          //進度lv
      level,                  //該員工的年度分數級距
      enable,                 //是否啟用
      department_id,          //該員工所屬單位
      department_name,
      department_unit_id,
      department_lv,
      division_id,            //該員工部門
      division_name,
      division_unit_id,
      division_lv,
      finished                //是否完成
      _status                 //狀態  1= 初始, 2= 送審中, 3=核准, 4=作廢
    ],
    divisions : {部門單資料}
 }
 */
self.getYearlyAssessmentByAdmin = function(data) {
 return $.post(Data_PATH+"Yearly/getYearlyAssessmentByAdmin", data);
}


/**
 * 管理者取得 當前考評分數詳情
 * @modifyDate 2020-11-16
 * @param      必須  year
 * @return      {
  
  }
*/
self.getYearlyAssessmentScoreDetailByAdmin = function(data) {
  return $.post(Data_PATH+"Yearly/getYearlyAssessmentScoreDetailByAdmin", data);
}


 /**
 * 把收集到部門單的年考績做駁回的動作   (管理者)(取消核准)
 * @param   必須 division_id          //部門單的id
 * @param   必須 year                 //年
 * @return  {divisions}
 */
self.rejectDivisionZoneByAdmin = function(data){
  return $.post(Data_PATH+"Yearly/rejectDivisionZoneByAdmin", data);
}

/**
 * 將指定的考評作廢   (管理者)
 * @modifyDate 2017-10-17
 * @param     必須 assessment_id
 * @param     可選 enable             //enable=1 的話 可以還原該單
 * @return    {
    id,                               //assessment_id
    enable,                           //啟用狀態
    owner_staff_id,                   //擁有者
    processing_lv,                    //進度lv
    assessment_total,                 //分數總分
    assessment_total_division_change, //部長加減分
    assessment_total_ceo_change,      //執行長加減分
    level,                            //評分級距
    status,                           //執行操作的狀態
 }
 */
self.setAssessmentCancel = function(data) {
 return $.post(Data_PATH+"Yearly/setAssessmentCancel", data);
}

/**
 *  把該年度設成歷史資料   (管理者) (processing=7才能執行)
 *  @param  必須 year
 *  @return {year_config}
 */
self.finishYearly = function(data) {
 return $.post(Data_PATH+"Yearly/finishYearly", data);
}








/**
 *  @method  取得自己的成績單
 *  @param   必須  year
 *  @return  {
 *    staff_id,
 *    department_id,
 *    division_id,
 *    monthly_average,        //月績效平均分數
 *    level,                  //該年的級距
 *    self_contribution,      //自我貢獻
 *    self_improve,           //自我改進
 *    upper_comment : {       //上司評論
 *      lv : {                  //單位階層lv
 *        staff_id,               //評論人的id
 *        content,                //評論人的評論內容
 *        staff_name,             //評論人名
 *        staff_name_en           //評論人名
 *      }
 *    },
 *    staff : {
 *      {一般員工資料}
 *    },
 *    division : {
 *      {部門單位資料}
 *    },
 *    department : {
 *      {自己單位資料}
 *    },
 *    feedbacks : [             //部屬回饋問卷
 *      question_id,              //題目id
 *      content,                  //評論內容
 *      create_date,              //建立時間
 *      description               //題目標題描述
 *    ]
 }
 */
self.getYearlyReportMyTranscripts = function(data){
  return $.post(Data_PATH+"Yearly/getYearlyReportMyTranscripts", data);
}


/**
 * ========== 匯出年考績 考評單Excel ==========
 * @param   必須 year
 * @param   必須 ( division_id || department_id || staff_id )  //部門 或單位 或員工 id 至少給一種
 * @return  Excel
 */
self.exportYearlyAssessmentExcel = function(data){
  if(!(data && data.year )){ return false; }
  var tt = new Date().getTime();
  var url = Excel_PATH+"exportYearlyAssessmentExcel?year="+data.year+"&division_id="+data.division_id+("&department_id="+data.department_id)+("&staff_id="+data.staff_id);

  // return ifa.deferred;
  return downloadExcel(url,tt);
}


var excelIframe;
function downloadExcel(url,tat){
  //IE救另開心頁
  if(window.navigator.userAgent.indexOf('MSIE')>0){
    var def = $.Deferred();
    def.resolve('{"msg":"","status":200,"runtime":"","result":"OK."}');
    window.open(url,'_blank');
    return def;
  }

  if(!excelIframe){excelIframe = $('#ele_ExportYearlyAssessmentExcel');}
  if(excelIframe.length==0){excelIframe=$('<iframe id="ele_ExportYearlyAssessmentExcel"></iframe>').hide().appendTo('body'); $.removeCookie('Excel_Request',{path:'/'});}
  var ifa = excelIframe;
  var ER = $.cookie('Excel_Request');
  if(ER){alert('正在下載Excel');return ifa.deferred;}
  //開始新的下載
  if(excelIframe.timer){clearInterval(excelIframe.timer);}
  $.cookie('Excel_Request',tat,{path:'/'});

  ifa.deferred = $.Deferred();
  ifa.limit = 240; ifa.i = 0;   //兩分鐘 timeout
  ifa.timer = setInterval(function(){
    var back_tt = $.cookie('Excel_Response');
    if(back_tt==0){
      ifa.deferred.resolve('{"msg":"","status":200,"runtime":"","result":"OK."}');
      clear();
    }else if(ifa.i++>ifa.limit){
      ifa.deferred.resolve('{"msg":"timeout.","status":4,"runtime":"","result":""}');
      clear();
    }else{
      var innerText = ifa.contents().text();
      if(innerText.length>10){ ifa.deferred.resolve(innerText); clear(); }
    }
  },500);
  ifa.attr('src',url);
  $.cookie('Excel_Response',tat,{path:'/'});
  function clear(){
      ifa.remove();
      clearInterval(ifa.timer);
      excelIframe = null;
      $.removeCookie('Excel_Request',{path:'/'});
  }
  return ifa.deferred;
}



/**
 * ========== 下載年考績評分總覽 考評單Excel ==========
 *  @param   必須 year
 *  @param   必須 department_level //單位等級   0=全部 , 1~5=單位階級lv
 *  @param   可選 with_assignment  //要包含助理
 *  @param   可選 is_over          //是否核准
 */
self.downloadYearlyAssessmentTotal = function(data){
  var str = '', ary = ['year','department_level','with_assignment','is_over'], val;
  for(var i in ary){
    val = ary[i];
    if(data[val]){ str+=('&'+val+'='+data[val]); }
  }
  var url = Excel_PATH+"downloadYearlyAssessment?v=0"+str;
  return downloadExcel(url, new Date().getTime());
}


/**
 * ========== 匯入2016年年考績評分 考評單Excel ==========
 * @param   必須  file( 格式=download_2016_YearlyAssessment )
 * @return  {
   update_count,  //成功更新的資料
   insert_count,  //成功加入的資料
   status,        //狀態
 }
 */
self.upload_2016_YearlyAssessment = function(file){
  return $.ajax({
    url : Excel_PATH+"upload_2016_YearlyAssessment",
    type : "POST",
    data : file,
    dataType : "JSON",
    cache : false,
    contentType : false,
    processData: false
  });
}


/**
 * ========== 下載2016年年考績評分 考評單Excel ==========
 * @param   none
 * @return  Excel
 */
self.download_2016_YearlyAssessment = function(){
  var url = Excel_PATH+"download_2016_YearlyAssessment";
  return downloadExcel(url, new Date().getTime());
}


/**
 * ========== 下載年 對公司評論Excel ==========
 * @param   必須  year
 * @return  Excel
 */
self.downloadYearlyQuestion = function(data){
  var url = Excel_PATH+"downloadYearlyQuestion?year="+data.year;
  return downloadExcel(url, new Date().getTime());
}


/**
 * ========== 年度組織圖用 ==========
 * @param   必須  year
 * @return  {
    config : {
      year,
      processing,               //年進程 --> 0 未啟動, 1 部屬回饋產生, 2 部屬回饋收集, 3 部屬回饋關閉, 4 產生年考績, 5 收集年考績, 6 暫停收集, 7 完成收集年考績, 8 完成加減分調整, 9 進入歷史資料
      date_start,               //年起始日
      date_end,                 //年結束日
      feedback_date_start,      //部屬回饋開始日
      feedback_date_end,        //部屬回饋截止日
      assessment_date_start,    //年考評開始日
      assessment_date_end,      //年考評截止日
      ceo_staff_id,             //執行長ID
      constructor_staff_id      //架構發展者ID
    },
    unit_map : {
      id : {                    //KEY=單位id
        id,                       //單位ID
        lv,                       //單位lv
        supervisor_staff_id,      //單位上層上司
        manager_staff_id,         //單位主管
        manager_staff_name,       //單位主管名
        manager_staff_name_en,    //單位主管英文
        upper_id,                 //上層單位
        path_department,          //上層路徑
        name,                     //單位名稱
        unit_id,                  //單位代號
        status_code,              //狀態碼   0= 無主管/空, 1=準備狀態, 2=初評, 3=審核中, 4=初步完成, 5=核准
        _staff : [                //單位內員工數
          {
            id,                     //員工id
            staff_no,               //員工編號
            name,                   //名子
            name_en,                //英文名
            rank,                   //職等
            department_id,          //單位id
            title,                  //職稱類別
            post,                   //職稱
            _authority,             //登入者是否有權限 對此員工做操作
            _feedback : [           //部屬回饋問卷數
              {
                id,                   //問卷id
                status                //問卷狀態  1=交出, 0=未交, -1=不收了
              }
            ],
            _report : {             //年考評報表
              id,                     //報表id
              owner_staff_id,         //擁有者
              owner_staff_name,
              owner_staff_name_en,
              division_id,            //部門單位id
              processing_lv,          //進程lv
              level,                  //評等等級名稱   (如果沒有權限看 會是?)
              _status_code,           //個人狀態碼    0=作廢, 1=自評, 2=審核中, 3=分數打完有level
            }
          }
        ],
        _feedback_total,          //部屬回饋總數  (processing=1~3 才會出現)
        _feedback_finished,       //部屬回饋提交數(processing=1~3 才會出現)

        _report_total,            //年考評報表總數(processing=4~9 才會出現)
        _report_finished,         //年考評報表完成數(processing=4~9 才會出現)
        _report_this_total,       //此單位的年考評報表總數(processing=4~9 才會出現)
        _report_this_finished,    //此單位的年考評報表完成數(processing=4~9 才會出現)
        _division:{               //年考評部門單(processing=4~9 才會出現)
          id,                       //部門單id
          status,                   //部門單狀態 0=未平完, 1=剩部長還沒評完, 5=整個部門都評完
          processing,               //部門單進程 0=初始, (1,2)=部長加減分, (3,4)=執行長加減分, 5=部門核准
        }
      }
    }
 }
 */
self.getYearlyOrganization = function(data){
  return $.post(Data_PATH+"Yearly/getYearlyOrganization", data);
}


/**
 *  下載年考評人員名單
 *  @param  必須 year
 *  @return Excel
 */
self.downloadYearlyAssessmentPeopleList = function(data){
  var url = Excel_PATH+"downloadYearlyAssessmentPeopleList?year="+data.year;
  return downloadExcel(url, new Date().getTime());
}

/**
 *  觀看詳細部屬回饋
 *  @param  必須 year
 *  @param  必須 staff_id
 *  @return [
 *    {
 *      name :        //該題目的 title
 *      point :       //該題目的得分率(滿分100)   (全部加起來 就是部屬回饋的總分)
 *      score_avg,    //該題目的得分
 *      score_max,    //該題目的得分上限
      }
 *  ]
 *
 */
self.getFeedbackDetailByStaff = function(data){
  return $.post(Data_PATH+"Yearly/getFeedbackDetailByStaff", data);
}


/**
 *  下載部屬回饋Excel
 *  @param  必須 year
 *  @return Excel
 *
 */
self.downloadYearlyFeedback = function(data){
  var url = Excel_PATH+"downloadYearlyFeedback?year="+data.year;
  return downloadExcel(url, new Date().getTime());
  // return $.post(Excel_PATH+"downloadYearlyFeedback", data);
}

/**
 *  取得對其他主管的部屬回饋問題  (Admin)
 *  @param  必須 year
 *  @return {
 *    staff_id : {    //員工id
 *      name :          //員工名
 *      name_en :       //員工英文名
 *      questions :[    //回饋問題
 *        {
 *          qid :           //問題id
 *          content :       //內容
 *          create_date :   //問題評論時間
 *        },..
 *      ]
      },..
    }
 *
 */
self.getYearlyOtherLeaderSuggestions = function(data){
  return $.post(Data_PATH+"Yearly/getYearlyOtherLeaderSuggestions", data);
}


/**
 *  移動部屬回饋問題給另一個主管   (Admin)
 *  @param  必須 question_id
 *  @param  必須 target_staff_id
 *  @return {
 *    id :                //該問題id
 *    target_staff_id :   //目標員工id
 *    content :           //內容
 *    create_date :       //創立時間
 *    name :              //員工名稱
 *    name_en :           //員工英文名
    }
 *
 */
self.moveYearlyQuestionToLeader = function(data){
  return $.post(Data_PATH+"Yearly/moveYearlyQuestionToLeader", data);
}


/**
 * 更新年報表的多主管commit狀態
 * @param  必須 report_id
 * @param  必須 leader_id
 * @param  必須 commit   ( 設為已送審 = 1,  設為未審核 = 0)
 * @return {
 *  
 * 
 * }
 * 
 */
self.updateYearlyLeaderCommitment = function(data) {
  return $.post(Setting_PATH+"updateYearlyReportLeaderCommitment", data);
}