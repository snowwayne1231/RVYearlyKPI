var $overView = $("#Overview").generalController(function() {
  var ts = this;
  var current = $.ym.get();
  var year = ts.q("#getYear").empty();
  var month = ts.q("#getMonth").empty();
  var getStaffCate = ts.q("#getStaffCate");

  function initYM() {
    year.yearSet(); // yearSet() in header.js

    for (i = 1; i <= 12; i++) {
      month.append('<option value="' + i + '">' + i + "月</option>");
    }

    // year.val(current.year).attr('selected');
    year.change(function() {
      current.year = this.value;
      $.ym.save();
    });
    month.change(function() {
      console.log(this.value);
      if ($.isNumberic(this.value) && this.value > 0) {
        current.month = this.value;
        $.ym.save();
      }
    });
    month.val(current.month).attr("selected");
  }



  initYM();


  getStaffCate.change(function() {
    if (this.val() == 0) {
      console.log("單位" + this.val());
    } else {
      console.log("全公司");
    }
  });

  ts.onLogin(function(member) {
    month.prepend('<option value="0">全年</option>');



    var vm = new Vue({
      el: ".rv-overview",
      data: {
        viewData: {
          leader: [],
          staff: []
        },
        year: current.year,
        month: current.month,
        select_staff_id: 0,
        under_staff: "",
        personName: "",
        passReports: ts
          .q("#getPassReports")
          .find("option:selected")
          .val(),
        departmentID: "",
        comments: [],
        member: member,
        staffCateNo: 1,
        underIdArray: [], // under Staff id array
        depShow: 1,
        allDep: []// allDep: alldepData
      },
      created: function() {
        $(window).on("click", function(e) {
          if (
            e.target.id != "autoContent" &&
            !ts.q(".search-input-article").find(e.target).length
          ) {
            ts.q(".autocomplete-content").addClass("off");
          }
        });
        var vm = this;
        vm.Selected();
        // vm.getUnderIdArr();
        // console.log("getUnderIdArr2") // 重覆

       // if(vm.member.is_leader || vm.member.is_admin) vm.getAlldepart(); 
        if (vm.month != 0) {
          //vm.getAlldepart();
        } else {
          console.log("全年");
          ts.q("#selectDepartmentID").remove();
        }
      },
      methods: {
        getAlldepart: function() {
          var vm = this;
          API.getLowerDepartmentList({'year':vm.year, 'month':vm.month}).then(function(e) {
            var result = API.format(e);
            if (result.is) {
              vm.allDep = result.res();
            }
          });
        },
        getUnderIdArr: function() {
          var vm = this;
          vm.underIdArray = [];
          vm.getUnder("department");
          console.log('getUnder1 department')
          var underlist = vm.under_staff;
          for (var i in underlist) {
            vm.underIdArray.push(underlist[i].id);
          }
        },
        getUnder: function(modelname) {
          var vm = this;
          API.getUnderStaff({ model: modelname, 'year':vm.year, 'month':vm.month}).then(function(e) {
            var f = API.format(e);
            vm.under_staff.length = 0;
            if (f.is) {
              var res = f.res();
              if (!member.is_ceo) {
                res.push(member);
              }
              vm.under_staff = res;
            }
          });
        },
        Selected: function() {
          var data = {
            year: this.year,
            month: this.month,
            release: this.passReports,
            department_id: this.departmentID,
            select_staff_id: this.select_staff_id
          };
          var vm = this;
          if (this.month == 0) {
            vm.depShow = 0;

            // admin(&leader)本單位及全公司的切換
            if (vm.member.is_admin) {
              vm.getUnder("admin");
              // if (vm.staffCateNo == 0) {
              //   vm.getUnder("department");
              // } else {
              //   vm.getUnder("admin");
              // }
            } else {
              vm.getUnder("department");
            }

            $.ym.save({ year: vm.year });
            if (vm.member.is_leader && vm.select_staff_id == 0) {
              return Materialize.toast("請選擇單一人員", 2000);
            }
          } else {
            if(vm.member.is_leader || member.is_admin){
              vm.depShow = 1;
            }else{
              vm.depShow = 0;
            }
            $.ym.save(data);
            data.select_staff_id = 0;
          }

          if(vm.member.is_leader || vm.member.is_admin) {
            vm.getAlldepart();
            //vm.getUnderIdArr();
          }
          API.getMonthlyReportWhenRelease(data).then(function(e) {
            var result = API.format(e);
            if (result.is) {
              var list = result.get();
              var successMsg = result.msg;
              //console.log(list)
              if((list.leader.length + list.staff.length) ==0){
                ts.q('.no-data').css('display','block');
              }else{
                ts.q('.no-data').css('display','none');
              }
              vm.viewData = list;
              //console.log(list);

              if (vm.member.is_admin && vm.member.is_leader) {
                if (vm.staffCateNo == 0) {
                  var newleader = [];
                  //vm.getUnder("department");
                  //vm.getUnderIdArr();

                  for (le in vm.viewData.leader) {
                    if (
                      vm.underIdArray.indexOf(
                        vm.viewData.leader[le].staff_id
                      ) != -1
                    ) {
                      newleader.push(vm.viewData.leader[le]);
                    }
                  }
                  vm.viewData.leader = newleader;
                  var newStaffArr = [];
                  for (s in vm.viewData.staff) {
                    if (
                      vm.underIdArray.indexOf(vm.viewData.staff[s].staff_id) !=
                      -1
                    ) {
                      newStaffArr.push(vm.viewData.staff[s]);
                    }
                  }
                  vm.viewData.staff = newStaffArr;
                } else {
                  vm.viewData = list;            
                }
              }

              vm.timeout && clearTimeout(vm.timeout);
              vm.timeout = setTimeout(function() {
                ts.q(vm.$el).q(".modal").modal();
                ts.q(".card table").fixMe();
              }, 1);
            }else{
              swal('Something Wrong:'+e.msg);
            }
          });
        },

        SelectedStaff: function(staff) {
          this.select_staff_id = staff.id;
          this.Selected();
          this.personName = staff.name + " " + staff.name_en;
          ts.q(".autocomplete-content").addClass("off");
        },
        getPerosonDate: function(isclear) {
          // var vthis = this;
          if (isclear) this.personName = "";

          ts.q(".autocomplete-content").removeClass("off");
          ts.q(".autocomplete-content").addClass("on");
        },
        comment: function(id, month) {
          var vm = this;
          this.comments = [];

          API.getComment({ staff_id: id, year: this.year, month: month }).then(
            function(e) {
              var result = API.format(e);
              if (result.is) {
                var comment = result.res().comments || [];
                for (var loc in comment) {
                  comment[loc]["name_head"] = comment[
                    loc
                  ]._created_staff_name.charAt(0);
                  comment[loc]["no"] = loc++;
                }
                vm.comments = comment.reverse();
              }
            }
          );
        },
        downloadExcel: function() {
          if (this.month == 0) {
            return Materialize.toast("未選定月份不能下載", 2000);
          }
          API.downloadMonthlyReportWhenRelease({
            year: this.year,
            month: this.month,
            release: this.passReports == 1 ? true : false,
            department_id: this.departmentID
          });
          Materialize.toast("月份績效開始下載中...", 2000);
        },
        onChange: function(type, id, e) {
          var vm = this;
          var score_data = {
            report_type: type,
            report_id: id,
            exception: 0,
            reason: ""
          };
          if (e.target.checked) {
            // checked 勾選後跳出填寫原因視窗
            score_data.exception = 1;
            swal(
              {
                title: "請填寫原因",
                text: "此為必填項目",
                type: "input",
                closeOnConfirm: false,
                showCancelButton: true,
                animation: "slide-from-top",
                inputPlaceholder: "請填入"
              },
              function(inputValue) {
                if (inputValue === false) {
                  e.target.checked = false;
                }
                if (inputValue === "") {
                  swal.showInputError("您需要輸入一些原因");
                  return false;
                }
                if (inputValue !== "" && inputValue !== false) {
                  score_data.reason = inputValue;
                  API.updateMonthlyNoScore(score_data).then(function(e) {
                    var result = API.format(e);
                    if (result.is) {
                      vm.Selected();
                      swal(
                        "非常好！",
                        "您輸入的原因：" + inputValue,
                        "success"
                      );
                    } else {
                      swal("發生錯誤", result.get(), "error");
                    }
                  });
                }
              }
            );
          } else {
            //將不計分取消時
            swal(
              {
                title: "您確定要刪除不計分？",
                text: "將刪除不計分原因內文",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "确定取消不計分！",
                cancelButtonText: "Cancle",
                closeOnConfirm: false,
                closeOnCancel: false
              },
              function(isConfirm) {
                if (isConfirm) {
                  score_data.reason = "";
                  score_data.exception = 0;
                  API.updateMonthlyNoScore(score_data).then(function(e) {
                    var result = API.format(e);
                    if (result.is) {
                      vm.Selected();
                      swal("已取消！", "已刪除不計分原因", "success");
                    } else {
                      swal("發生錯誤", result.get(), "error");
                    }
                  });
                } else {
                  e.target.checked = true;
                  swal("沒有更動", "維持原本設定", "error");
                }
              }
            );
          }
        }
      },
      updated: function() {
        ts.q(".tooltipped").tooltip({ delay: 50 });
      }
    });
  });
});
