var $settingAdmin = $('#SettingAdmin').generalController(function () {
    var ts = this;
    var current = $.ym.get();
    var year = ts.q("#getYear").empty();
    var month = ts.q("#getMonth").empty();

    function initYM() {
        // var ddate = new Date();
        // var thisyear = ddate.getFullYear() + (ddate.getMonth()==11 && ddate.getDate() >20 ? 1:0);

        // for (i = thisyear; i >= API.create.year ; i--) {
        //     year.append('<option value="' + i + '">' + i + '年</option>');
        // }
        year.yearSet();
        $.ym.save();
        for (i = 1; i <= 12; i++) {
            month.append('<option value="' + i + '">' + i + '月</option>');
        }
        //
        // year.val(current.year);
        month.val(current.month);
    }
    initYM();

    ts.onLogin(function (member) {
        var vm = new Vue({
            el: ts.q('.rv-admin')[0],
            data: {
                year: ts.q("#getYear").find("option:selected").val(),
                month: ts.q("#getMonth").find("option:selected").val(),
                reports: [],
                department_map: ts.department_map,
                openReportArray: [],
                curr_evaluating: [],
                curr_submited_staff: []
            },
            created: function () {
                this.onChangeUpdate();
            },
            methods: {
                onChangeUpdate: function () {
                    var date = {
                        year: this.year,
                        month: this.month
                    }
                    $.ym.save(date);
                    this.getAdminMonthly(date);
                },
                getAdminMonthly: function (date) {
                    var vss = this
                    API.getMonthlyProcessByAdmin(date).then(function (e) {
                        var result = API.format(e);
                        if (result.is) {
                            var adminMonthly = result.get();
                            var newReport = [];
                            for (var i in adminMonthly) {
                                for (var n in adminMonthly[i]) {
                                    newReport.push(adminMonthly[i][n]);
                                }
                            }
                            vss.reports = newReport;
                            vss.openReportArray = new Array(newReport.length).fill(false);
                        } else {
                            console.log("獲取資料失敗:" + result.get());
                        }
                    })
                },
                commit: function (id) {
                    var commitId = {
                        processing_id: id,
                        admin: 1
                    }
                    var vss = this;
                    API.commitMonthly(commitId).then(function (e) {
                        var success = API.format(e);
                        if (success.is) {
                            Materialize.toast('已提交送審', 2000)
                            vss.onChangeUpdate();
                        } else {
                            //alert("協助送審失敗:" + success.get())
                            swal("協助送審失敗", success.get());
                        }
                    });
                },
                reject: function (obj, index) {
                    var backId = $('#ReJectModal-' + (index + 1) + ' option:selected').val();
                    var rejectData = {
                        processing_id: obj.id,
                        turnback: 1,
                        admin: 1
                    }
                    var vss = this;
                    API.rejectMonthly(rejectData).then(function (e) {
                        var success = API.format(e);
                        if (success.is) {
                            Materialize.toast('已退回該表單', 2000);
                            vss.onChangeUpdate();
                        } else {
                            //alert("協助退回失敗:" + success.get())
                            swal("協助退回失敗", success.get());
                        }
                    });
                },
                decideStatus: function (status) {
                    if (status < 5 && status != 1) {
                        return true;
                    } else {
                        return false;
                    }

                    if (status == 5 && status != 1) {
                        return true;
                    } else {
                        return false;
                    }
                },
                onClickReport: function (idx) {

                },

                detailReports(ref, idx, report) {
                    var vm = this,
                        eles = vm.$refs,
                        ele = eles[ref][0];
                    if (ele.classList.contains('hide')) {
                        API.getMonthlyEvaluatingReportByProcess(report.id).then(function (e) {
                            var success = API.format(e);
                            if (success.is) {
                                vm.curr_evaluating = e.result;
                                vm.curr_submited_staff = e.result._submited_staff;
                                for (var i in eles) {
                                    if (i.indexOf('detail_report_') != -1) {
                                        eles[i][0].classList.add('hide');
                                    }
                                }
                                ele.classList.remove('hide');
                                vm.openReportArray = vm.openReportArray.map((e, i) => i === idx ? !e : false);
                            } else {
                                swal("取得數據失敗", success.get());
                            }
                        });
                    } else {
                        ele.classList.add('hide');
                    }

                }
            }
        });
    });
});