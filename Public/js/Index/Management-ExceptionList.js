var $ManageExceptionList = $("#Management-ExceptionList").generalController(
  function() {
    var ts = this,
      current = $.ym.get();
    var getYear = ts.q("#getYear").val();
    var yearSelect = ts.q("#getYear").empty();
    yearSelect.yearSet();

    ts.onLogin(function(member) {
      var vm = new Vue({
        el: ".rv-admin",
        data: {
          year: current.year,
          month: [
            "Jan",
            "Feb",
            "Mar",
            "Apr",
            "May",
            "Jun",
            "Jul",
            "Aug",
            "Sep",
            "Oct",
            "Nov",
            "Dec"
          ],
          exceptionalStaffs: []
        },
        methods: {
          initData: function() {
            var vm = this;
            current.year = vm.year;
            $.ym.save();
            API.getYearlySpecialStaff({ year: vm.year }).then(function(e) {
              var result = API.format(e);
              if (result.is) {
                ts.q("#NoData").hide();

                var resultAPI = result.res();
                resultAPI.forEach(function(e) {
                  // 處理狀態：
                  switch (e.staff_status) {
                    // 1 正式，2約聘，3試用，4離職 ,0 為預設
                    case "正式":
                      e.staff_status = 1;
                      break;
                    case "約聘":
                      e.staff_status = 2;
                      break;
                    case "試用":
                      e.staff_status = 3;
                      break;
                    case "離職":
                      e.staff_status = 4;
                      break;
                    case "留停":
                      e.staff_status = 5;
                      break;

                    default:
                      e.exceptionalStaffs.staff_status = 0;
                  }
                  // 處理原因：
                  e.exception_reason_list = e.exception_reason_list.replace(
                    /\|/g,
                    ","
                  );
                  // 處理不計分的月份資料：
                  e.dictionaryReason = {};
                  e.exceptions.forEach(function(ex) {
                    e.dictionaryReason[ex.month] = ex.reason;
                  });
                });
                vm.exceptionalStaffs = resultAPI;
              } else {
                ts.q("#NoData").show();
                vm.exceptionalStaffs = [];
              }
            });
          },
          numberToMonthEN: function(num) {
            if ($.isArray(num)) {
              var tmp = [];
              for (var i in num) {
                var inn = this.numberToMonthEN(num[i]);
                tmp[i] = inn;
              }
              return tmp;
            } else {
              return this.parseMonth(num);
            }
          },
          parseMonth: function(n) {
            return this.month[n - 1];
          }
        },
        created: function() {
          this.initData();
        },
        updated: function() {
          ts.q(".tooltipped").tooltip({ delay: 50 });
        }
      });
    });
  }
);
