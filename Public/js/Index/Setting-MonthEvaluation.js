var $SettingEvaluation = $('#SettingEvaluation').generalController(function () {
    var ts = this;
    var current = $.ym.get();
    var selectYear = ts.q("#getYear").empty();

    function init() {
        selectYear.yearSet();
        selectYear.change(function () {
            current.year = this.value;
            $.ym.save();
        });
    }
    init();

    ts.onLogin(function () {
        var month = [];
        for (var i = 1; i <= 12; i++) {
            if (i < 10) {
                i = '0' + i;
            }
            month.push(i);
        }
        // 小於10的數字前加上 0
        var currentMonth = (current.month) >= 10 ? (current.month) : '0' + (current.month);

        ts.vue = new Vue({
            el: ts.q('.had-container')[0],
            data: {
                selectYear: current.year,
                // yearArray: year,
                monthArray: month,
                now: { year: current.year, month: currentMonth },
                setting: { day_start: '', day_end: '', day_cut_addition: '' },
                config_isLaunch: false,
                isLaunch: false,
                has_monthly_data: false,
                finalDay: 0,
                canUpdate: true
            },
            methods: {
                getCycleConfig: function () {
                    var vuethis = this;

                    API.getCycleConfig(this.now).then(function (e) {
                        var cnt = API.format(e);
                        if (cnt.is) {
                            var result = cnt.get();
                            var nowDate = new Date();
                            var selectDate = new Date(vuethis.now.year + '-' + vuethis.now.month);
                            var tooOver = (nowDate.getTime() - selectDate.getTime()) > (1000 * 60 * 60 * 24 * 30 * 3); //超過三個月
                            // console.log(tooOver);
                            vuethis.canUpdate = ((result['overDate'] == false) && !tooOver) || ((result['settingAllow'] == false) && !tooOver);
                            vuethis.isLaunch = result['monthly_launched'];
                            vuethis.config_isLaunch = result['monthly_launched'];
                            vuethis.setting = result;
                            vuethis.has_monthly_data = result['has_monthly_data'] == 1;

                            ts.q('#eva-month-' + vuethis.now.month).prop('checked', true);

                            vuethis.launchBotton();

                            $.ym.save({
                                year: vuethis.now.year,
                                month: parseInt(vuethis.now.month)
                            });

                        } else {
                            generalFail();
                        }
                    });
                },
                launchBotton: function () {
                    this.setting.monthly_launched = this.isLaunch ? 1 : 0;
                    //this.finalDay = parseInt(this.setting.day_end) + parseInt(this.setting.day_cut_addition);
                    //變數引用錯誤，current 並不會雙向綁定, 應該要用 this.now
                    //var dateString = current.year +"-"+ currentMonth +"-" +this.setting.day_end;
                    var dateString = this.now.year + "-" + this.now.month + "-" + this.setting.day_end;
                    var endDate = new Date(dateString);
                    finalDateFormat = endDate.setDate(endDate.getDate() + parseInt(this.setting.day_cut_addition));
                    var fDay = new Date(finalDateFormat);
                    this.finalDay = fDay.getFullYear() + "年" + (fDay.getMonth() + 1) + "月" + fDay.getDate() + "日";

                    if (this.isLaunch) {
                        ts.q('.process-date input').prop("disabled", true);
                        ts.q('.rate-days input').prop("disabled", true);
                        ts.q('.eva-end-date').show();
                    } else {
                        ts.q('.process-date input').removeAttr("disabled");
                        ts.q('.rate-days input').removeAttr("disabled");
                        ts.q('.eva-end-date').hide();
                    }
                    //this.submit();

                },
                submit() {
                    if (!this.has_monthly_data) {
                        this.createProcessing();
                    }

                    var submitData = {
                        year: this.now.year,
                        month: this.now.month,
                        day_start: this.setting.day_start,
                        day_end: this.setting.day_end,
                        day_cut_addition: this.setting.day_cut_addition,
                        monthly_launched: this.setting.monthly_launched
                    }
                    var vuethis = this;
                    API.updateCycleConfig(submitData).then(function (e) {
                        var cnt = API.format(e);
                        if (cnt.is) {
                            var result = cnt.get();
                            vuethis.setting = result;
                            if (result.hasChanged) {
                                vuethis.refreshMonthly();
                                vuethis.getCycleConfig();
                            } else {
                                swal("Success", "更新成功!");
                            }
                        } else {
                            generalFail(cnt.get());
                        }
                    });
                },
                refreshMonthly: function () {
                    var deferred = (this.setting.monthly_launched == 1) ? API.launchMonthly(this.now) : API.pauseMonthly(this.now);
                    deferred.then(function (e) {
                        var cnt = API.format(e);
                        if (cnt.is) {
                            //alert('更新成功');
                            swal("Success", "更新成功!");
                        } else {
                            generalFail(cnt.get());
                        }
                    });


                },
                createProcessing: function () {
                    var vm = this,
                        data = {
                            year: vm.now.year,
                            month: vm.now.month
                        };
                    if (Array.isArray(vm.setting.constucts) && vm.setting.constucts.length > 0) {
                        data["del"] = true;
                    };

                    API.checkDepartment(data).then(function (e) {
                        var cnt = API.format(e);
                        if (cnt.is) {
                            vm.getCycleConfig();
                            Materialize.toast('檢查完成!', 1500);
                        } else {
                            generalFail(cnt.get());
                        }
                    });
                },
                delRefreshMonthly(key) {
                    var vm = this,
                        type = {
                            recheck: {
                                msg: "將重新產生有異動單位/人員的考評單，但不影響工作評語，是否繼續？",
                                resMsg: "已重製有異動單位/人員的考評單"
                            },
                            del: {
                                msg: "所有考評單將會重新產生且不會保留已評的分數，但不影響工作評語，是否繼續？",
                                resMsg: "所有考評單已重製"
                            }
                        };
                    swal({
                        title: "確定執行嗎？",
                        text: type[key]["msg"],
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#DD6B55",
                        confirmButtonText: "確定執行！",
                        cancelButtonText: "取消！",
                        closeOnConfirm: true,
                        closeOnCancel: false
                    },
                        function (isConfirm) {
                            if (isConfirm) {
                                updateMonth();
                            } else {
                                swal("取消！", "取消更新",
                                    "error");
                            }
                        });

                    function updateMonth() {
                        let data = {year: vm.now.year, month: vm.now.month};
                        data[key] = true;
                        API.checkDepartment(data).then(function (e) {
                            var cnt = API.format(e);
                            if (cnt.is) {
                                //alert('更新成功');
                                API.checkDepartment({ year: vm.now.year, month: vm.now.month }); // 重整後check department
                                // swal("成功！", "已重新整理成功",
                                //     "success");
                                Materialize.toast(type[key]["resMsg"], 1000);
                            } else {
                                //alert('更新失敗');
                                swal("Fail", cnt.get());
                            }
                        });
                    }
                }
            },
            mounted: function () {
                this.getCycleConfig();
                this.$watch('now', this.getCycleConfig, { deep: true });
                var ele = this.$el;
                ts.q(ele).q('.dropdown-button').dropdown();
            }


        });

    });

    function generalFail(e) {
        // alert('失敗，請重試. \r\n' + (e ? e : ''));
        swal('Fail', '失敗，請重試. \r\n' + (e ? e : ''));
    }

});