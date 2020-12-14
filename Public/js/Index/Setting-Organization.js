var $SettingOrganization = $("#SettingOrganization").generalController(
  function () {
    var ts = this,
      monthConfigDate;
    var current = $.ym.get();
    ts.vues = {};

    //開發時打開點比較好看
    function devv() {
      var tcq = ts.q(".container");
      if (tcq.width() < 1000) {
        tcq.width("100%");
      }
    }
    devv();

    //開始啟動
    function goLauncher() {
      prepareData();

      buildDepartmentVue();

      buildDepartmentDetailVue();

      buildStaffList();

      buildStaffDetail();

      buildEditPost();

      //套件
      ts.q(".tabs").tabs();
      ts.q(".dropdown-button").dropdown();
      ts.q(".modal").modal();

      // jquery-ui datepicker
      $.datepicker.regional["zh-TW"] = {
        closeText: "關閉",
        prevText: "&#x3C;上月",
        nextText: "下月&#x3E;",
        currentText: "今天",
        monthNames: [
          "一月",
          "二月",
          "三月",
          "四月",
          "五月",
          "六月",
          "七月",
          "八月",
          "九月",
          "十月",
          "十一月",
          "十二月"
        ],
        monthNamesShort: [
          "一月",
          "二月",
          "三月",
          "四月",
          "五月",
          "六月",
          "七月",
          "八月",
          "九月",
          "十月",
          "十一月",
          "十二月"
        ],
        dayNames: [
          "星期日",
          "星期一",
          "星期二",
          "星期三",
          "星期四",
          "星期五",
          "星期六"
        ],
        dayNamesShort: ["周日", "周一", "周二", "周三", "周四", "周五", "周六"],
        dayNamesMin: ["日", "一", "二", "三", "四", "五", "六"],
        weekHeader: "周",
        dateFormat: "yy-mm-dd",
        firstDay: 1,
        isRTL: false,
        showMonthAfterYear: true,
        yearSuffix: "年"
      };
      $.datepicker.setDefaults($.datepicker.regional["zh-TW"]);
      $.datepicker.setDefaults({
        dateFormat: "yy-mm-dd",
        onSelect: function (dateText) {
          this.dispatchEvent(new Event("input"));
        }
      });
      ts.q(".rv-ui-datepicker").datepicker();
    }
    //單位menu的vue
    function buildDepartmentVue() {
      var lvs = {};
      for (var i in ts.department) {
        var loc = ts.department[i];
        var key = loc.lv == 1 ? 2 : loc.lv;
        if (!lvs[key]) {
          lvs[key] = [];
        }
        lvs[key].push(loc);
      }
      ts.vues.Department = new Vue({
        el: "#DepartmentMenu",
        data: {
          department: ts.department,
          devLv: lvs,
          upper_array: [],
          createUnitData: { unit_id: "", name: "", lv: 0, upper: {} },
          display: true
        },
        methods: {
          openDepartment: function (loc) {
            ts.vues.DepartmentDetail.show(loc.id);

            function goStaffListShow() {
              ts.vues.StaffList.show(loc.id);
            }

            ts.vues.DepartmentDetail.getDepartmentLeaderGroup(loc.id, goStaffListShow);

            // ts.vues.StaffList.show(loc.id)

            ts.vues.StaffDetail.hide();
            ts.q(".rv-setting").addClass("zoom");
            ts.vues.DepartmentDetail._data.showBlock = 1;
            setTimeout(function () {
              ts.q(".dropdown-button").dropdown();
            }, 1);
            var area = ts.q(this.$el).q(".content-area a");
            area
              .removeClass("active")
              .filter(".department_id_" + loc.id)
              .addClass("active");
          },
          changOfUnitData: function (upper) {
            this.createUnitData.upper = upper;
          },
          addDepartmentButton: function (lv) {
            this.upper_array = [];
            for (var i in this.department) {
              var loc = this.department[i];
              if (loc.lv == lv - 1) {
                this.upper_array.push(loc);
              }
            }
            this.createUnitData.upper = this.upper_array[0];
            this.createUnitData.unit_id = "";
            this.createUnitData.name = "";
            this.createUnitData.lv = parseInt(lv);
          },
          addDepartment: function () {
            var submitData = {
              unit_id: this.createUnitData.unit_id,
              name: this.createUnitData.name,
              lv: this.createUnitData.lv,
              upper_id: this.createUnitData.upper.id
            };
            var errorMsg = "";
            if (!submitData.unit_id.match(/^[a-zA-Z]{1}[\d]{2}$/i)) {
              errorMsg += "單位代號格式錯誤.\n\r";
            }
            if (submitData.name.length == 0) {
              errorMsg += "部門名稱不能無.\n\r";
            }
            if (errorMsg.length > 0) {
              swal("Error", errorMsg, "error");
            } else {
              var needleDev = this.devLv;
              API.addDepartment(submitData)
                .then(function (e) {
                  var cot = API.format(e);
                  if (cot.is) {
                    ts.q("#CreateUnit").modal("close");
                    var newOne = cot.get();
                    addClientDepartment(newOne);
                    needleDev[newOne.lv].push(newOne);
                  } else {
                    generalFail(cot.get());
                  }
                })
                .fail(generalFail);
            }
          }
        }
      });
    }

    //單位詳情
    function buildDepartmentDetailVue() {
      var now = clone(ts.department[0]);
      var upper = ts.department_map[now.id];

      ts.vues.DepartmentDetail = new Vue({
        el: "#DepartmentDetail",
        data: {
          department: ts.department,
          department_map: ts.department_map,
          department_leader_group: [],
          staff: ts.staff_map,
          managers_array: [],
          manager: { id: 0, name: "無" },
          supervisor_name: "",
          upper: upper,
          upper_array: [],
          now: now,
          showBlock: 0,
          display: true
        },
        methods: {
          show: function (id) {
            var team = (this.now = clone(this.department_map[id]));
            if (team) {
              this.manager = team.manager_staff_id
                ? this.staff[team.manager_staff_id]
                : { id: 0, name: "無" };
              this.supervisor_name = this.staff[team.supervisor_staff_id].name;
              this.upper = this.department_map[team.upper_id];

              this.refreshStaffArray(team.lv);
              this.refreshUpperArray(team.lv);

              this.submitData = { id: id };

            } else {
              console.log("error department id:" + id);
            }
          },
          refreshStaffArray: function (lv) {
            this.managers_array = [];
            //console.log('this.staff', this.staff);
            for (var i in this.staff) {
              var loc = this.staff[i];
              //存在該組 且 lv 夠 沒離職
              //if (loc.lv <= lv && loc.department_id == this.now.id && loc.status_id != 4) {
              if (loc.lv > 0 && loc.lv <= lv && loc.status_id != 4) {
                this.managers_array.push(loc);
              }
            }
          },
          updateDepartment: function () {
            var vuet = this;
            var sd = this.submitData;
            var now = this.now;
            var manager = this.manager;
            var managers = this.managers;
            if ((typeof sd.manager_staff_id != undefined && sd.manager_staff_id == 0) && (vuet.department_leader_group.length > 0 && vuet.department_leader_group.find(function (item) { return item.status == 1 }) != undefined)) {
              vuet.changeManager(vuet.managers_array.find(function (item) { return item.id == now.manager_staff_id }));
              return swal(
                "請先清空此單位管理群",
                "【單位主管】若要為空時，管理群也必須清空。",
                "warning"
              );
            }

            API.updateDepartment(sd)
              .then(function (data) {
                var cot = API.format(data);
                if (!cot.is) {
                  return generalFail(cot.get());
                }

                swal("Success", "已為您更新成功!", "success");

                var dmap = ts.department_map[now.id];
                for (var i in sd) {
                  var loc = sd[i];
                  dmap[i] = loc;
                }
                vuet.now = dmap;
                ts.vues.StaffList.refresh();
                vuet.submitData = { id: now.id };
              })
              .fail(generalFail);
          },
          mapDepartmentLeaderGroup(staff_id) {
            var vm = this,
              mapres = {},
              department_leader_group = vm.department_leader_group;
            console.log(vm.department_leader_group);
            if (department_leader_group.length > 0) {
              mapres = department_leader_group.find(function (item) {
                return item.staff_id == staff_id;
              });
            }
            return mapres;
          },
          getDepartmentLeaderGroup(id, callback) {
            var vm = this;
            vm.department_leader_group = [];
            API.getDepartmentLeadership({ department_id: id }).then(function (e) {
              var r = API.format(e);
              if (r.is) {
                vm.department_leader_group = e.result;
                ts.vues.StaffList.refresh();
              }

              if (!!callback) {
                callback();
              }
            });
          },
          changeManager: function (val) {
            this.manager = val || { id: 0, name: "無" };
            this.submitData["manager_staff_id"] = val.id || 0;
          },
          changeUpper: function (val) {
            this.upper = val;
            this.submitData["upper_id"] = val.id;
          },
          refreshUpperArray: function (lv) {
            this.upper_array = [];
            lv--;
            for (var i in this.department) {
              var loc = this.department[i];
              if (loc.lv == lv) {
                this.upper_array.push(loc);
              }
            }
          }
        }
      });
    }

    //人員清單
    function buildStaffList() {
      var innerStaffData = function () {
        var no = "R000";
        for (var i in ts.staff) {
          var loc = ts.staff[i];
          if (no < loc.staff_no) {
            no = loc.staff_no;
          }
        }

        return {
          staff_no: no.replace(/[\d]+$/i, function ($m) {
            return Number($m) + 1;
          }),
          account: "",
          passwd: "",
          name: "",
          name_en: "",
          email: "",
          first_day: "",
          last_day: "",
          stay_start_day: "0000-00-00",
          stay_end_day: "0000-00-00",
          update_date: "",
          status: ts.staff_status[0],
          title: ts.staff_title[0],
          post: ts.staff_post[0]
        };
      };

      ts.vues.StaffList = new Vue({
        el: "#StaffList",
        data: {
          staff: ts.staff,
          department: ts.department,
          department_map: ts.department_map,
          staff_list: [],
          department_list: [],
          staff_post: ts.staff_post,
          staff_title: ts.staff_title,
          staff_status: ts.staff_status,
          onDuty: false,
          team: {},
          newStaffData: innerStaffData(),
          display: true
        },
        mounted: function () {
          var vuethis = this;
        },
        methods: {
          show: function (team_id) {
            this.team = this.department_map[team_id];
            this.refresh();
          },
          refresh: function () {
            this.staff_list = [];
            for (var i in this.staff) {
              var loc = this.staff[i];


              if (!this.onDuty && loc.status_id == 4) {
                continue;
              }
              if (loc.department_id == this.team.id) {

                loc.is_main_leader = false;
                if (ts.vues.DepartmentDetail.now.manager_staff_id == loc.id) {
                  loc.is_main_leader = true;
                }

                var mapres = ts.vues.DepartmentDetail.mapDepartmentLeaderGroup(loc.id);
                if (mapres != undefined && mapres.status == 1) {
                  loc.is_leader_group = true;
                  loc.in_leader_group = true;
                  loc.in_leader_group_id = mapres.id;
                } else if (mapres != undefined && mapres.status == 0) {
                  loc.is_leader_group = false;
                  loc.in_leader_group = true;
                  loc.in_leader_group_id = mapres.id;
                } else if (mapres == undefined) {
                  loc.is_leader_group = false;
                  loc.in_leader_group = false;
                }
                this.staff_list.push(loc);
              }
            }
            this.department_list = [];
            for (var i in this.department) {
              var loc = this.department[i];
              if (loc.lv == this.team.lv) {
                this.department_list.push(loc);
              }
            }
          },
          refreshStaffData: innerStaffData,
          openCreate: function () { },
          showDetail: function (staff) {
            var items = ts.q(this.$el).q(".wrapper .rv-item");
            items
              .removeClass("active")
              .filter(".staff_id_" + staff.id)
              .addClass("active");

            ts.vues.StaffDetail.show(staff.id);
          },
          addStaff: function () {
            var sdate = clone(this.newStaffData);
            delete sdate.post && delete sdate.status && delete sdate.title;
            sdate["post_id"] = this.newStaffData.post.id;
            sdate["status_id"] = this.newStaffData.status.id;
            sdate["title_id"] = this.newStaffData.title.id;
            sdate["department_id"] = this.team.id;

            var errorMsg = "";
            if (!sdate["staff_no"].match(/^[A-Z]{1}[\d]{2,4}$/i)) {
              errorMsg += "人員編號格式錯誤 \n\r";
            }
            if (sdate["account"].length == 0) {
              errorMsg += "帳號不能為空 \n\r";
            }
            if (sdate["passwd"].length == 0) {
              errorMsg += "密碼不能為空 \n\r";
            }
            if (sdate["name"].length == 0) {
              errorMsg += "名字不能為空 \n\r";
            }
            if (sdate["name_en"].length == 0) {
              errorMsg += "英文名字不能為空 \n\r";
            }
            if (!sdate["email"].match(/^[\w\d\.\_\-]+\@.+$/i)) {
              errorMsg += "Email格式錯誤 \n\r";
            }
            if (errorMsg.length > 0) {
              return swal("Error", errorMsg, "error");
            }

            var vts = this;
            API.addStaff(sdate)
              .then(function (e) {
                var collect = API.format(e);
                if (collect.is) {
                  var newclassmate = collect.get();
                  newclassmate.head = newclassmate.name_en.charAt(0);

                  addClientStaff(newclassmate);
                  vts.refresh();
                  ts.q("#AddStaff").modal("close");

                  //等待vue做好內容 在 callback 新的 staff
                  ts.q(vts.$el).animate(
                    { scrollTop: vts.$el.scrollHeight + 50 },
                    50,
                    function () {
                      vts.showDetail(newclassmate);
                    }
                  );
                  //更新主管選單
                  ts.vues.DepartmentDetail.refreshStaffArray(vts.team.lv);

                  vts.newStaffData = vts.refreshStaffData();
                } else {
                  generalFail(collect.get());
                }
              })
              .fail(generalFail);
          }
        }
      });
    }

    //人員詳細
    function buildStaffDetail() {
      var now = clone(ts.staff[0]);

      var nowDepartment = clone(ts.department[0]);

      ts.vues.StaffDetail = new Vue({
        el: "#StaffDetail",
        data: {
          staff: ts.staff_map,
          department: ts.department,
          department_map: ts.department_map,
          staff_post: ts.staff_post,
          staff_title: ts.staff_title,
          staff_title_map: ts.staff_title_map,
          staff_status: ts.staff_status,
          staff_return_day: "", // if has return day
          staff_return_arr: [],
          ableChangeStatus: 1,
          oldStaff: {},
          now: now,
          isAddLog: false,
          nowDepartment: nowDepartment,
          isAdmin: 1,
          display: true,
          changeEndDay: 0,
          itemShow: {
            haveToAddNewlog: false,
            stay_day_range: false,
            return_day: false,
            last_day: false
          },
          monthConfig: monthConfigDate,
          minDate: "", // 限制最min 天數
          lastReturnEndDate: "",
        },
        watch: {
          staff_return_day: function () {
            var vs = this;
            vs.StartMinDay();
          },
          now: {
            handler: function () {
              var vs = this;
              if (vs.now.id) {
                vs.oldStaff = vs.staff[vs.now.id];

              }
            },
            deep: true
          }
        },
        created: function () { },
        mounted: function () {
          var vs = this;
          var lastEndDay = 0;
          vs.checkEndReturnDay();
          //月考評結束後，留停修改不可在月考評區間前
          if (vs.minDate) {
            var day = Number(vs.minDate.format("DD"));
            var month = Number(vs.minDate.format("MM")) - 1;
            var year = Number(vs.minDate.format("YYYY"));

            ts.q("#ReturnEndDay").datepicker({
              minDate: new Date(year, month, day)
            });
          }
          // if(vs.staff_return_day){
          //   var DateA = moment(vs.staff_return_day,'YYYY-MM-DD').add('days',1);
          //   var dd = Number(DateA.format("DD"));
          //   var mm = Number(DateA.format("MM")) - 1;
          //   var yy = Number(DateA.format("YYYY"));
          //   console.log(yy, mm, dd)
          //   ts.q("#ReturnStartDate").datepicker( "option", "minDate", new Date(yy, mm, dd) );
          // }
        },
        methods: {
          checkUnitChange: function (dep) {
            var vs = this;
            vs.nowDepartment = dep;
            if (vs.oldStaff.department_id == vs.nowDepartment.id) {
              vs.now.update_date = vs.oldStaff.update_date;
            }
          },
          afterToday: function () {
            // 留停結束日，在今日後才可改狀態
            var vs = this,
              EndDayIsBeforeToday,
              EndDay;
            var nowDate = moment().format("YYYY-MM-DD");
            if (vs.now.stay_end_day == "0000-00-00") {
              EndDayIsBeforeToday = 1;
            } else {
              EndDay = moment(vs.now.stay_end_day);
              if (vs.now.stay_end_day == nowDate) {
                EndDayIsBeforeToday = 1;
              } else if (moment(nowDate) > EndDay) {
                EndDayIsBeforeToday = 1;
              } else {
                EndDayIsBeforeToday = 0;
              }
            }
            // console.log('EndDayIsBeforeToday',EndDayIsBeforeToday)
            vs.ableChangeStatus = EndDayIsBeforeToday;
          },
          checkEndReturnDay: function () {
            var vs = this;
            var nowDate = moment().format("YYYY-MM-DD");
            var monthConfigEndDate = vs.monthConfig.cut_off_date;
            var cannotBeforeEnd = judge();
            function judge() {
              var judgeChange;

              if (nowDate > monthConfigEndDate & monthConfigEndDate !== '0000-00-00') {
                //考評結束時間
                judgeChange = 1;
              } else if (vs.monthConfig.overDate) {
                judgeChange = 1;
              } else if (monthConfigEndDate == '0000-00-00') {
                judgeChange = 0;
              } else {
                judgeChange = 0;
              }
              return judgeChange;
            }

            if (cannotBeforeEnd) {
              // 不能在考評已結束的時間前
              var newDate = moment(vs.monthConfig.RangeEnd, "YYYY-MM-DD").add(
                "days",
                1
              );
              vs.minDate = newDate;
            }
          },
          judgeStatus: function () {
            var vs = this;
            for (var item in vs.itemShow) {
              vs.itemShow[item] = false;
            }
            function hasReturnDay() {
              if (
                typeof vs.now.return_day == "undefined" ||
                vs.now.return_day == "0000-00-00"
              ) {
                vs.itemShow.return_day = false;
              } else {
                vs.itemShow.return_day = true;
              }
            }
            switch (vs.staff[vs.now.id].status_id) {
              case 1: // 正式
                break;
              case 2: // 約聘
                hasReturnDay();
                break;
              case 3: // 試用
                hasReturnDay();
                break;
              case 4: // 離職
                break;
              case 5: // 留停
                vs.itemShow.stay_day_range = true;
                vs.isAddLog = false; // 新增留停記錄

                break;
            }
          },
          StartMinDay: function () {
            var vs = this,
              lastEndDay;
            vs.lastReturnEndDate = '';
            if (vs.staff_return_day) {
              var End = moment(vs.staff_return_day, "YYYY-MM-DD").add(
                "days",
                1
              );
              var day = Number(End.format("DD"));
              var month = Number(End.format("MM")) - 1;
              var year = Number(End.format("YYYY"));
              lastEndDay = new Date(year, month, day);
            } else {
              lastEndDay = "";
            }
            vs.lastReturnEndDate = lastEndDay;
            ts.q("#ReturnStartDate").datepicker("option", "minDate", vs.lastReturnEndDate);
          },
          changeStatus: function (value) {
            var vs = this;

            vs.itemShow.stay_day_range = false;
            vs.itemShow.return_day = false;

            function toStop() {
              vs.itemShow.haveToAddNewlog = true;
              vs.itemShow.stay_day_range = true;
              vs.isAddLog = true; // 新增留停記錄
              vs.now.stay_start_day = "0000-00-00";
              vs.now.stay_end_day = "0000-00-00";
            }

            function endStop(status_id) {
              if (status_id == 4) {
                // 離職
                vs.itemShow.stay_day_range = false;
              } else {
                vs.itemShow.return_day = true;
                vs.now.stay_start_day = vs.staff[vs.now.id].stay_start_day;
                vs.now.stay_end_day = vs.staff[vs.now.id].stay_end_day;
                // 計算復職日
                var endDay = new Date(vs.now.stay_end_day),
                  nextDate = new Date(endDay.setDate(endDay.getDate() + 1));
                nextDay =
                  nextDate.getFullYear() +
                  "-" +
                  (parseInt(nextDate.getMonth()) + 1) +
                  "-" +
                  nextDate.getDate();
                vs.now.return_day = nextDay;
              }
            }

            function endleave() {
              vs.now.last_day = "0000-00-00";
            }

            switch (
            vs.staff[vs.now.id].status_id // 原始狀態
            ) {
              case 1:
                switch (value.id) {
                  case 5: // 正式轉留停
                    toStop();
                    break;
                }
                break;
              case 2:
                switch (value.id) {
                  case 5: // 約聘轉留停
                    toStop();
                    break;
                }
                break;
              case 3:
                switch (value.id) {
                  case 5: // 試用轉留停
                    toStop();
                    break;
                }
                break;
              case 4:
                switch (value.id) {
                  case 1:
                    endleave();
                    break;
                  case 2:
                    endleave();
                    break;
                  case 3:
                    endleave();
                    break;
                  case 4:
                    vs.now.last_day = vs.staff[vs.now.id].last_day;
                    break;
                  case 5: // 離職轉留停
                    toStop();
                    break;
                }
                break;
              case 5:
                vs.itemShow.stay_day_range = true;
                vs.isAddLog = false; // 新增留停記錄
                switch (value.id) {
                  case 1: // 留停轉正式
                    endStop();
                    break;
                  case 2: // 留停轉約聘
                    endStop();
                    break;
                  case 3: // 留停轉試用
                    endStop();
                    break;
                  case 4: // 留停轉離職
                    endStop(value.id);
                    break;
                }
                break;
            }

            vs.now.status_id = value.id;
            vs.now.status = value.name;

            // 由離職改為其他status，離職日復原
            if (vs.now.status !== '離職') {
              vs.now.last_day = "0000-00-00";
            }
          },
          getLastReturnDay: function (staffId) {
            var vs = this;
            var StaffEvent = [];
            var returnEvent = [];
            vs.staff_return_day = "";
            vs.staff_return_arr = [];

            API.getStaffLog({ id: staffId })
              .then(function (e) {
                var r = API.format(e);
                if (r.is) {
                  var StaffLog = r.get();
                  // console.log(StaffLog)
                  StaffEvent = StaffLog.events;
                  vs.staff_return_arr = StaffLog.status;
                  if (StaffEvent.length !== 0) {
                    for (var i in StaffEvent) {
                      if (StaffEvent[i].event == "復職") {
                        returnEvent.push(StaffEvent[i]);
                      }
                    }
                  }

                  if (returnEvent.length !== 0) {
                    vs.staff_return_day = returnEvent[0].date;
                  }
                }
              })
              .fail(generalFail);
          },
          goStaffLog: function () {
            var vm = this,
              staff = clone(this.staff[vm.now.id]);

            if (!staff) {
              return console.log("Error Staff Id.");
            }

            var staff_departmentObj = vm.department_map[staff.department_id];
            staff.departments = [];
            staff.departments.push(staff_departmentObj);
            if (parseInt(staff_departmentObj.lv) > 2) {
              for (var i = 1; i < staff_departmentObj.lv; i++) {
                staff_departmentObj =
                  vm.department_map[staff_departmentObj.upper_id];
                staff.departments.push(staff_departmentObj);
              }
              staff.departments.reverse();
            }

            $("body").css({ overflow: "hidden" });
            ts.vues["currentStaff"] = staff;

            // API: 取得被選員工的在職紀錄
            var data = {
              id: vm.now.id
            };
            API.getStaffLog(data)
              .then(function (e) {
                var cot = API.format(e);
                if (cot.is) {
                  // console.log(e.result);
                  ts.vues["staffLog"] = e.result;
                  if (typeof ts.vues.staffLogVM === "undefined") {
                    staffLog();
                  } else {
                    ts.vues.staffLogVM.$data.staff = ts.vues.currentStaff;
                    ts.vues.staffLogVM.$data.log_status = e.result.status;
                    ts.vues.staffLogVM.$data.log_post = e.result.post;
                    ts.vues.staffLogVM.$data.log_department =
                      e.result.department;
                    ts.vues.staffLogVM.$data.log_event = e.result.events;
                    ts.q("#staffLog").animate({ left: 0 }, 800);
                  }
                } else {
                  generalFail(cot.get());
                }
              })
              .fail(generalFail);
            // ts.vues['staffLog'] = {
            //   status: [
            //     // { id: 6, status_id: 5, status: '留停', start: '2018-09-21', end: '2018-10-21' },
            //     // { id: 5, status_id: 1, status: '正式', start: '2017-10-21', end: '2018-09-20' },
            //     { id: 4, status_id: 5, status: '留停', start: '2017-08-10', end: '2017-10-20' },
            //     { id: 3, status_id: 5, status: '留停', start: '2017-08-10', end: '2017-10-10' },
            //     // { id: 2, status_id: 1, status: '正式', start: '2015-09-01', end: '2017-08-09' },
            //     // { id: 1, status_id: 3, status: '試用', start: '2015-06-01', end: '2015-08-31' },
            //   ],
            //   post: [
            //     //  { id: 1, post_id: 1, post: '網頁程式設計師', title_id: 1, title: '一般人員(行政/專技)', start: '2015-06-01', end: '0000-00-00' }
            //   ],
            //   department: [
            //     // { id: 2, departments: [ { id: 1, name: '營運系統部', upper_id: 1 }, { id: 2, name: '開發處', upper_id: 2 }, { id: 3, name: '開發組', upper_id: 3 } ], start: '2017-06-01', end: '0000-00-00' },
            //     // { id: 1, departments: [ { id: 1, name: '客戶服務部', upper_id: 1 }, { id: 2, name: '開發處', upper_id: 2 }, { id: 3, name: '開發組', upper_id: 3 } ], start: '2015-06-01', end: '2017-05-30' }
            //   ],
            //   events: [
            //     // { id: 9, event: '預計留停結束', status_id: 5, status: '留停', departments: [ { id: 1, name: '營運系統部', upper_id: 1 }, { id: 2, name: '開發處', upper_id: 2 }, { id: 3, name: '開發組', upper_id: 3 } ], post_id: 1, post: '網頁程式設計師', title_id: 1, title: '一般人員(行政/專技)', date: '2018-10-21', created_at: '2018-10-11 09:10:11', updated_at: '2018-10-11 09:10:11' },
            //     // { id: 8, event: '留停開始', status_id: 5, status: '留停', departments: [ { id: 1, name: '營運系統部', upper_id: 1 }, { id: 2, name: '開發處', upper_id: 2 }, { id: 3, name: '開發組', upper_id: 3 } ], post_id: 1, post: '網頁程式設計師', title_id: 1, title: '一般人員(行政/專技)', date: '2018-09-21', created_at: '2018-10-11 09:10:11', updated_at: '2018-10-11 09:10:11' },
            //     { id: 7, event: '復職', status_id: 1, status: '正式', departments: [ { id: 1, name: '營運系統部', upper_id: 1 }, { id: 2, name: '開發處', upper_id: 2 }, { id: 3, name: '開發組', upper_id: 3 } ], post_id: 1, post: '網頁程式設計師', title_id: 1, title: '一般人員(行政/專技)', date: '2017-10-21', created_at: '2018-10-11 09:10:11', updated_at: '2018-10-11 09:10:11' },
            //     { id: 6, event: '留停結束延遲', status_id: 5, status: '留停', departments: [ { id: 1, name: '營運系統部', upper_id: 1 }, { id: 2, name: '開發處', upper_id: 2 }, { id: 3, name: '開發組', upper_id: 3 } ], post_id: 1, post: '網頁程式設計師', title_id: 1, title: '一般人員(行政/專技)', date: '2017-10-20', created_at: '2018-10-11 09:10:11', updated_at: '2018-10-11 09:10:11' },
            //     { id: 5, event: '預計留停結束', status_id: 5, status: '留停', departments: [ { id: 1, name: '營運系統部', upper_id: 1 }, { id: 2, name: '開發處', upper_id: 2 }, { id: 3, name: '開發組', upper_id: 3 } ], post_id: 1, post: '網頁程式設計師', title_id: 1, title: '一般人員(行政/專技)', date: '2017-10-10', created_at: '2018-10-11 09:10:11', updated_at: '2018-10-11 09:10:11' },
            //     { id: 4, event: '留停開始', status_id: 5, status: '留停', departments: [ { id: 1, name: '營運系統部', upper_id: 1 }, { id: 2, name: '開發處', upper_id: 2 }, { id: 3, name: '開發組', upper_id: 3 } ], post_id: 1, post: '網頁程式設計師', title_id: 1, title: '一般人員(行政/專技)', date: '2017-08-10', created_at: '2018-10-11 09:10:11', updated_at: '2018-10-11 09:10:11' },
            //     // { id: 3, event: '換單位', status_id: 1, status: '正式', departments: [ { id: 1, name: '營運系統部', upper_id: 1 }, { id: 2, name: '開發處', upper_id: 2 }, { id: 3, name: '開發組', upper_id: 3 } ], post_id: 1, post: '網頁程式設計師', title_id: 1, title: '一般人員(行政/專技)', date: '2017-06-01', created_at: '2018-10-11 09:10:11', updated_at: '2018-10-11 09:10:11' },
            //     // { id: 2, event: '考核通過', status_id: 1, status: '正式', departments: [ { id: 1, name: '客戶服務部', upper_id: 1 }, { id: 2, name: '開發處', upper_id: 2 }, { id: 3, name: '開發組', upper_id: 3 } ], post_id: 1, post: '網頁程式設計師', title_id: 1, title: '一般人員(行政/專技)', date: '2015-09-09', created_at: '2018-10-11 09:10:11', updated_at: '2018-10-11 09:10:11' },
            //     // { id: 1, event: '到職', status_id: 3, status: '試用', departments: [ { id: 1, name: '客戶服務部', upper_id: 1 }, { id: 2, name: '開發處', upper_id: 2 }, { id: 3, name: '開發組', upper_id: 3 } ], post_id: 1, post: '網頁程式設計師', title_id: 1, title: '一般人員(行政/專技)', date: '2015-06-01', created_at: '2018-10-11 09:10:11', updated_at: '2018-10-11 09:10:11' }
            //   ]
            // };
          },
          goEditPost: function () {
            ts.vues.Department._data.display = false;
            ts.vues.DepartmentDetail._data.display = false;
            ts.vues.StaffDetail._data.display = false;
            ts.vues.StaffList._data.display = false;

            ts.vues.EditPostVM._data.display = true;
            setTimeout(function () {
              ts.q(".dropdown-button").dropdown();
              ts.q(".tabs").tabs();
              ts.q(".modal").modal();
            }, 10);
          },
          show: function (staff_id) {
            // var staff = this.staff[staff_id];
            var res = ts.vues.StaffList.$data.staff_list.find(function (item) { return item.id == staff_id }),
              staff = res;
            if (!staff) {
              return console.log("Error Staff Id.");
            }

            this.now = clone(staff);
            this.nowDepartment = this.department_map[staff.department_id];
            this.isAdmin = staff.is_admin == 1;
            ts.q(this.$el).css("opacity", 1);

            // this.checkLastEndDate();
            this.judgeStatus();
            this.getLastReturnDay(staff_id);
            this.afterToday();
          },
          hide: function () {
            ts.q(this.$el).css("opacity", 0);
          },
          onDateChoose: function (map, evt) {
            this.choiceDate = e;
          },
          updateLeaders(currStaff, nowIsLeaderGroup) {
            var vm = this;
            if (currStaff.rank >= 6) {
              // 執行api
              var data = {
                is_leader_group: currStaff.is_leader_group ? 0 : 1
              };
              if (currStaff.in_leader_group) {
                data.id = currStaff.in_leader_group_id;
              }

              data.staff_id = currStaff.id;
              data.department_id = currStaff.department_id;

              API.upsertDepartmentLeadership(data).then(function (e) {
                // console.log(e);
                var res = API.format(e);

                if (res.is) {
                  function goShow() {
                    vm.show(data.staff_id)
                  }
                  ts.vues.DepartmentDetail.getDepartmentLeaderGroup(vm.now.department_id, goShow);
                  let text = data.is_leader_group == 1 ? "加入" : "退出";
                  text += "管理群";
                  return swal(
                    "info",
                    text,
                    "success"
                  );
                } else {
                  return swal(
                    "error",
                    e.msg,
                    "error"
                  );
                }

              });
            } else {
              return swal(
                "error",
                "該人員職務等級不符合成為單位管理群條件。",
                "error"
              );
            }

          },
          updateAdmin: function () {
            this.now.is_admin = this.isAdmin ? 1 : 0;
          },
          updateStaff: function () {
            var now = this.now;
            if (
              (now.status_id == 4 || now.status_id == 5 || now.department_id != this.nowDepartment.id)
            ) {
              if (this.nowDepartment.manager_staff_id == now.id) {
                return swal(
                  "請先將該員工從單位主管中移除",
                  "【單位主管】狀態不能離職/留停且不能掉離現單位。",
                  "warning"
                );
              }

              if (this.now.is_leader_group) {
                return swal(
                  "請先將該員工退出管理群",
                  "【單位管理群】成員，狀態不可為離職/留停且不能調離現單位。",
                  "warning"
                );
              }

            }

            var haveToRefreshList =
              !(this.nowDepartment.id == now.department_id) ||
              now.status_id == 4;
            var olderStaff = this.staff[now.id];
            var submitData = {
              id: now.id,
              department_id: this.nowDepartment.id
            };
            now.department_id = this.nowDepartment.id;
            var gogo = false,
              staysd = false,
              stayed = false,
              ungoType = 0;
            for (var i in now) {
              if (i == "title" || i == "status" || i == "post") {
                continue;
              }
              var loc = now[i];
              if (loc != olderStaff[i]) {
                submitData[i] = loc;
                switch (i) {
                  case "stay_start_day":
                    staysd = true;
                    break;
                  case "stay_end_day":
                    stayed = true;
                    break;
                }
                gogo = true;
              }
            }
            if (gogo) {
              if (now.status_id == 5) {
                if (!staysd && !stayed) {
                  ungoType = 1;
                  gogo = false;
                } else if (staysd || stayed) {
                  if (
                    now.stay_start_day == "0000-00-00" ||
                    now.stay_end_day == "0000-00-00"
                  ) {
                    ungoType = 1;
                    gogo = false;
                  } else {
                    var sds = Date.parse(now.stay_start_day).valueOf(),
                      eds = Date.parse(now.stay_end_day).valueOf();
                    if (eds < sds) {
                      ungoType = 2;
                      gogo = false;
                    } else {
                      submitData["addNewStopRecord"] = this.isAddLog;
                      // if (submitData['addNewStopRecord']) {
                      submitData["stay_start_day"] = now.stay_start_day;
                      submitData["stay_end_day"] = now.stay_end_day;
                      // }
                    }
                  }
                }
              } else {
                if (staysd || stayed) {
                  if (staysd) {
                    delete now.stay_start_day;
                  }
                  if (stayed) {
                    delete now.stay_end_day;
                  }
                } else {
                  if (this.staff[this.now.id].status_id == 5) {
                    submitData["return_day"] = now.return_day;
                  }
                }
              }
            }

            if (!gogo) {
              switch (ungoType) {
                case 1:
                  return swal(
                    "日期注意",
                    "留停日期不可為0000-00-00.",
                    "warning"
                  );
                  break;
                case 2:
                  return swal(
                    "日期注意",
                    "留停日期【結束日】不可小於【開始日】.",
                    "warning"
                  );
                  break;
                default:
                  return swal("注意!!", "未更改任何數據.", "warning");
              }
            }

            if (
              olderStaff.department_id != now.department_id &&
              !submitData["update_date"]
            ) {
              var currentDate = new Date();
              submitData["update_date"] = now["update_date"] =
                currentDate.getFullYear() +
                "-" +
                ("0" + (currentDate.getMonth() + 1)).slice(-2) +
                "-" +
                ("0" + currentDate.getDate()).slice(-2);
            }
            switch (now.status_id) {
              case 4:
                if (
                  !submitData["last_day"] &&
                  !new Date(olderStaff.last_day).getTime()
                ) {
                  var currentDate = new Date();
                  submitData["last_day"] = now["last_day"] =
                    currentDate.getFullYear() +
                    "-" +
                    ("0" + (currentDate.getMonth() + 1)).slice(-2) +
                    "-" +
                    ("0" + currentDate.getDate()).slice(-2);
                }
                break;
            }

            var vuethis = this;

            API.updateStaff(submitData)
              .then(function (e) {
                var cnt = API.format(e);
                if (cnt.is) {
                  ts.vues.StaffList.refresh();
                  swal("Success", "更新成功!", "success");

                  now.department_id = vuethis.nowDepartment.id;
                  var updatedStaff = vuethis.staff[submitData.id];
                  for (var i in now) {
                    if (i == "title_id") {
                      var afLv = vuethis.staff_title_map[now[i]].lv;
                      now.lv = afLv;
                      updatedStaff["lv"] = afLv;

                    }
                    updatedStaff[i] = now[i];
                  }

                  ts.vues.DepartmentDetail.refreshStaffArray(
                    vuethis.nowDepartment.lv
                  );

                  if (haveToRefreshList) {
                    ts.vues.StaffList.refresh();
                  }

                  for (var key in submitData) {
                    var cc = submitData[key];
                    vuethis.staff[now.id][key] = cc;
                  }
                  vuethis.judgeStatus();
                  vuethis.afterToday();
                  vuethis.show(vuethis.now.id);

                  setTimeout(function () {
                    vuethis.itemShow.return_day = false;
                  }, 100);
                } else {
                  generalFail(cnt.get());
                }
              })
              .fail(generalFail);
          },
        }
      });
    }

    // 員工在職紀錄
    function staffLog() {
      ts.vues.staffLogVM = new Vue({
        el: "#staffLog",
        data: {
          staff: ts.vues.currentStaff,
          log_status: ts.vues.staffLog.status,
          log_post: ts.vues.staffLog.post,
          log_department: ts.vues.staffLog.department,
          log_event: ts.vues.staffLog.events
        },
        methods: {
          closeStaffLog: function () {
            var vm = this;
            ts.q("#staffLog").animate({ left: "100vw" }, 800);
            $("body").removeAttr("style");
          }
        },
        mounted: function () {
          var vm = this;
          ts.q("#staffLog").animate({ left: 0 }, 800);
        }
      });
    }

    //職務&職務類別編輯
    function buildEditPost() {
      ts.vues.EditPostVM = new Vue({
        el: "#EditPost",
        data: {
          id: 0,
          type: "post",
          enable: 0,
          addLv: 6,
          addNewName: "",
          oldName: "",
          newName: "",
          staffPost: {},
          staffTitle: {},
          display: false,
          sortPost: ""
        },
        methods: {
          backSetting: function () {
            ts.vues.Department._data.display = true;
            ts.vues.DepartmentDetail._data.display = true;
            ts.vues.StaffDetail._data.display = true;
            ts.vues.StaffList._data.display = true;
            ts.vues.EditPostVM._data.display = false;
            ts.vues.DepartmentDetail._data.showBlock = 0;
            ts.q(".rv-setting").removeClass("zoom");
            setTimeout(function () {
              ts.q(".dropdown-button").dropdown();
              ts.q(".tabs").tabs();
              ts.q(".modal").modal();
            }, 1);
          },
          clickColumn: function (obj, e) {
            this.id = obj.id;
            this.oldName = obj.name;
            this.enable = obj.enable;
            this.sortPost = obj.orderby;
            this.newName = "";

            ts.q(e.target)
              .parent()
              .find(".title")
              .removeClass("active");
            ts.q(e.target).addClass("active");
          },
          addEdit: function () {
            var vm = this;
            if (this.type == "post") {
              if (vm.addNewName == "") {
                swal("", "尚未填寫新的職務名稱", "error");
                return;
              }
              API.addSettingPost({
                name: this.addNewName,
                orderby: this.sortPost
              }).then(function (e) {
                var result = API.format(e);
                if (result.is) {
                  swal(
                    "新增成功",
                    "已為您新增職務：" + vm.addNewName + "。",
                    "success"
                  );
                  vm.init();
                  vm.addNewName = "";
                  $("#AddPost").modal("close");
                }
              });
            } else {
              if (vm.addNewName == "") {
                swal("", "尚未填寫新的類別名稱", "error");
                return;
              }
              API.addSettingTitle({
                name: this.addNewName,
                lv: this.addLv
              }).then(function (e) {
                var result = API.format(e);
                if (result.is) {
                  swal(
                    "新增成功",
                    "已為您新增類別：" + vm.addNewName + "。",
                    "success"
                  );
                  vm.init();
                  vm.addNewName = "";
                  $("#AddTitle").modal("close");
                }
              });
            }
          },
          updateEdit: function () {
            var vm = this;
            if (this.type == "post") {
              var data = {
                post_id: this.id,
                // name: this.newName,
                orderby: this.sortPost,
                enable: this.enable
              };
              // if (this.newName == '') {
              if (false) {
                delete data["name"];
                swal(
                  {
                    title: "確認是否要更新資料?!",
                    text: "您的新名稱是空白的，確認是否要更新!!!",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#ff972c",
                    confirmButtonText: "更新",
                    closeOnConfirm: false
                  },
                  function () {
                    vm.apiUpdatePost(data);
                  }
                );
              } else {
                vm.apiUpdatePost(data);
              }
            } else {
              var data = {
                title_id: this.id,
                // name: this.newName,
                enable: this.enable
              };
              // if (this.newName == '') {
              if (false) {
                delete data["name"];
                swal(
                  {
                    title: "確認是否要更新資料?!",
                    text: "您的新名稱是空白的，確認是否要更新!!!",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#ff972c",
                    confirmButtonText: "更新",
                    closeOnConfirm: false
                  },
                  function () {
                    vm.apiUpdateTitle(data);
                  }
                );
              } else {
                vm.apiUpdateTitle(data);
              }
            }
          },
          apiUpdateTitle: function (data) {
            var vm = this;
            API.updateSettingTitle(data).then(function (e) {
              var result = API.format(e);
              if (result.is) {
                vm.init();
                swal("更新成功", "您的變更已為您更新。", "success");
                if (!vm.newName) {
                  return;
                }
                vm.oldName = vm.newName;
                vm.newName = "";
              } else {
                swal("發生錯誤!!", result.get(), "error");
              }
            });
          },
          apiUpdatePost: function (data) {
            var vm = this;
            API.updateSettingPost(data).then(function (e) {
              var result = API.format(e);
              if (result.is) {
                vm.init();
                swal("更新成功", "您的變更已為您更新。", "success");
                if (!vm.newName) {
                  return;
                }
                vm.oldName = vm.newName;
                vm.newName = "";
              } else {
                swal("注意!!", result.get(), "error");
              }
            });
          },
          init: function () {
            var vm = this;
            API.getAllStaffPost().then(function (e) {
              var result = API.format(e);
              if (result.is) {
                vm.staffPost = result.get();
                ts.staff_post.splice(0, ts.staff_post.length);
                for (var i in vm.staffPost) {
                  ts.staff_post.push(vm.staffPost[i]);
                }
              }
            });
            API.getAllStaffTitleLv().then(function (e) {
              var result = API.format(e);
              if (result.is) {
                vm.staffTitle = result.get();
                ts.staff_title.splice(0, ts.staff_title.length);
                for (var i in vm.staffTitle) {
                  ts.staff_title.push(vm.staffTitle[i]);
                }
              }
            });
          },
          tabClick: function (type) {
            this.type = type;
            this.oldName = "";
          }
        },
        created: function () {
          this.init();
        }
      });
    }

    //預處理資料
    function prepareData() {
      for (var i in ts.staff) {
        var loc = ts.staff[i];
        loc.head = loc.name_en.charAt(0);
      }
    }

    function addClientDepartment(newone) {
      ts.department.push(newone);
      ts.department_map[newone.id] = newone;
    }

    function addClientStaff(newone) {
      ts.staff.push(newone);
      ts.staff_map[newone.id] = newone;
    }

    //發生必須重新讀取的錯誤
    function reloadFail() {
      swal("Error", "發生錯誤!!請重新嘗試", "error");
      // location.reload();
      ts.$.remove();
    }

    function generalFail(e) {
      // if(e='update_data too over.'){
      //   e='換單位日期，不可為考評期間日期'
      // }
      swal("Fail", e ? e : "", "error");
    }

    this.onLogin(function () {
      var current = new Date();

      API.getCycleConfig({
        year: current.getFullYear(),
        month: current.getMonth() + 1
      })
        .then(function (e) {
          var cnt = API.format(e);
          if (cnt.is) {
            var data = cnt.get();
            monthConfigDate = data;
            if (data.settingAllow != true) {
              ts.$.hide();
              swal(
                {
                  title: "注意!!",
                  text: "月考評啟動期間，不能修改組織!",
                  type: "warning",
                  showConfirmButton: true
                },
                function () {
                  API.go("/Setting-MonthEvaluation");
                }
              );
            } else {
              gogoPowerRanger();
              ts.$.show();
            }
          } else {
            reloadFail();
          }
        })
        .fail(reloadFail);
    });

    function gogoPowerRanger() {
      //加載資料
      var getArray = [
        API.getAllDepartment(),
        API.getAllStaff(),
        API.getAllStaffPost(),
        API.getAllStaffTitleLv(),
        API.getAllStaffStatus(),
      ];
      $.when
        .all(getArray)
        .then(function (all) {
          var contain = [];
          for (var i in all) {
            var cot = API.format(all[i][0]);
            if (!cot.is) {
              return reloadFail();
            }
            contain.push(cot);
          }
          ts.department = contain[0].get();
          ts.department_map = contain[0].map();
          ts.staff = contain[1].get();
          ts.staff_map = contain[1].map();
          ts.staff_post = contain[2].get();
          ts.staff_title = contain[3].get();
          ts.staff_title_map = contain[3].map();
          ts.staff_status = contain[4].get();

          //開始
          goLauncher();
        })
        .fail(reloadFail);
    }
  }
);
