var $SettingYearEva = $('#SettingYearEva').generalController(function() {
    var ts = this;
    ts.vm = {};
    var current = $.ym.get(),
        config, config_Deferred = $.Deferred();
    var minYear = 2017;
    var selectDate = ts.q('#EvaluationYear');
    var startYear = '';

    function initDate() {
         ts.q('#startYear').yearSet();
         ts.q('#EndYear').yearSet();
        selectDate.yearSet();
        minYear = parseInt(current.year);
        selectDate.change(function() {
            current.year = this.value;
            startYear = current.year;
            $.ym.save();
            API.reload();
        });
    }

    function getApiConfig() {
        API.getYearlyConfig({ year: minYear }).then(function(e) {
            var result = API.format(e);
            if (result.is) {
                config = result.get();
                config_Deferred.resolve();
                ts.q(".rv-readysetting").show();
                ts.q(".rv-yearsetting").show();
                ts.q("#NoData").hide();
            } else {
                // throw 'Year Data Error.';
                ts.q(".rv-readysetting").hide();
                ts.q(".rv-yearsetting").hide();
                ts.q("#NoData").show();
            }
        });
    }

    initDate();
    getApiConfig();
    console.log(config)

    ts.onLogin(function(member) {
        var today = new Date(member.now);
        var date = new Date();
        var thisYear = date.getFullYear();

        function buildReadySetting() {
            var yearList = [];
            var monthList = [];
            var dayList = [];
            for (var i = thisYear; i >= API.create.year; i--) {
                yearList.push(i);
            }
            for (var m = 1; m < 13; m++) {
                monthList.push(m);
            }

            ts.vm.readySetting = new Vue({
                el: '.rv-readysetting',
                data: {
                    processing: 0,
                    startYear:startYear,
                    year: yearList,
                    month: monthList,
                    currentYear: minYear,
                    dayLength: { start: [], end: [] },
                    StartDate: { year: startYear, month: '1', day: '1' },
                    EndDate: { year: startYear, month: '12', day: '1' },
                    doCtoB: 1,
                    config: {},
                    activeHeader: { headerOne: 1, headerTwo: 0, headerThree: 0 },
                    statistics: {},
                    organizationConstruct: {},
                    organizatioStaff: {},
                    controlStaff: {},
                    allDepartment: {},
                    updateStaffData: [],
                    dictionary: {},
                    unitTotal: 0,
                    feedbackTotal: 0,
                    assessmentTotal: 0
                },
                methods: {
                    initSetting: function() {
                        var vm = this;
                        vm.config = config;
                        var sd = new Date(vm.config.date_start);
                        var ed = new Date(vm.config.date_end);

                        vm.processing = vm.config.processing;
                        vm.doCtoB = vm.config.promotion_c_to_b;

                        vm.StartDate.year = sd.getFullYear();

                        vm.StartDate.month = sd.getMonth() + 1;
                        vm.StartDate.day = sd.getDate();
                        vm.EndDate.year = ed.getFullYear();
                        vm.EndDate.month = ed.getMonth() + 1;
                        vm.EndDate.day = ed.getDate();

                        this.getConstruct();

                        API.getAllDepartment().then(function(e) {
                            var result = API.format(e);
                            if (result.is) {
                                vm.allDepartment = result.get();
                            }
                        });
                        var record = localStorage.getItem("activeHeader_Record");
                        if (record) {
                            this.activeHeader = JSON.parse(record);
                            if (this.activeHeader.headerTwo) {
                                $(".stepper-content.one").css('display', 'none');
                                $(".stepper-content.two").css('display', 'block');
                            }
                            if (this.activeHeader.headerThree) {
                                $(".stepper-content.one").css('display', 'none');
                                $(".stepper-content.three").css('display', 'block');
                            }
                        }
                    },
                    readyStepOne: function(element) {
                        var data = {
                            year: this.currentYear,
                            date_start: this.StartDate.year + '-' + this.StartDate.month + '-' + this.StartDate.day,
                            date_end: this.EndDate.year + '-' + this.EndDate.month + '-' + this.EndDate.day,
                            promotion_c_to_b: this.doCtoB ? 1 : 0
                        }
                        var vm = this;
                        API.updateYearlyConfig(data).then(function(e) {
                            var result = API.format(e);
                            if (result.is || result.get() == 'Nothing Change.') {
                                let date_start = result.get().date_start,
                                    date_end = result.get().date_end;
                                if (result.get() == 'Nothing Change.') {
                                    date_start = data.date_start;
                                    date_end = data.date_end;
                                }
                                
                                vm.config['date_start'] = date_start;
                                vm.config['date_end'] = date_end;
                                swal('設定成功', '已為您區間設定完畢', 'success');
                                vm.nextBtn(element, 'headerOne', 'headerTwo');
                            } else {
                                swal('設定失敗', result.get(), 'error');
                            }
                        });
                        vm.updateConstruct(); // 預先先重整組織
                    },
                    getConstruct: function() {
                        var vm = this;
                        API.getYearlyConstruct({ year: this.currentYear }).then(function(e) {
                            var result = API.format(e);
                            if (result.is) {
                                vm.organizationConstruct = result.get();
                                vm.calculation();
                            }
                        });
                    },
                    calculation: function() {
                        var vm = this;
                        for (var c in vm.organizationConstruct) {
                            var calcFeedback = 0;
                            var calcAssessment = 0;
                            for (var staff in vm.organizationConstruct[c].staff) {
                                if (vm.organizationConstruct[c].staff[staff]._can_feedback) {
                                    calcFeedback++;
                                }
                                if (vm.organizationConstruct[c].staff[staff]._can_assessment) {
                                    calcAssessment++;
                                }
                            }
                            vm.organizationConstruct[c]['feedback_num'] = calcFeedback;
                            vm.organizationConstruct[c]['assessment_num'] = calcAssessment;
                        }

                        vm.unitTotal = 0;
                        vm.feedbackTotal = 0;
                        vm.assessmentTotal = 0;
                        // 計算人數統計
                        for (var c in vm.organizationConstruct) {
                            vm.unitTotal += vm.organizationConstruct[c].staff.length;
                            vm.feedbackTotal += vm.organizationConstruct[c].feedback_num;
                            vm.assessmentTotal += vm.organizationConstruct[c].assessment_num;
                        }
                    },
                    downloadPeopleList: function() {
                        API.downloadYearlyAssessmentPeopleList({ year: this.currentYear }).then(function(e) {
                            var result = API.format(e);
                            if (result.is) {
                                swal('開始下載', '正在為您下載年考評人員名單', 'success');
                            } else {
                                swal('下載失敗', result.get(), 'success');
                            }
                        });
                    },
                    getGroupStaff: function(index, unit, $select) {
                        var vm = this;
                        ts.q($select.target).parents('tbody').find('tr').removeClass('active');
                        ts.q($select.target).parent().addClass('active');
                        API.getYearlyConstruct({ year: this.currentYear }).then(function(e) {
                            var result = API.format(e);
                            if (result.is) {
                                var organization = result.get();
                                for (var os in organization[index].staff) {
                                    organization[index].staff[os]['unit'] = unit;
                                }
                                vm.organizatioStaff = organization[index].staff;
                                // 複製一份做資料對照組
                                vm.controlStaff = JSON.parse(JSON.stringify(organization[index].staff));
                            }
                        });
                    },
                    updateConstruct: function() {
                        var vm = this;
                        API.getYearlyConstruct({ year: this.currentYear, reset: 1 }).then(function(e) {
                            var result = API.format(e);
                            if (result.is) {
                                vm.organizationConstruct = result.get();
                                vm.calculation();
                                // swal('更新組織', '已為您更新取得當年度組織', 'success');
                                Materialize.toast('已為您更新取得當年度組織!', 1000);
                            } else {
                                swal('更新失敗', result.get(), 'error');
                            }
                        });
                    },
                    nextBtn: function(element, current, go) {
                        ts.q(element.target).parents('.stepper-content').addClass('stepper-content-leave');
                        setTimeout(function() {
                            ts.q(element.target).parents('.stepper-content').css('display', 'none').removeClass('stepper-content-leave');
                            ts.q(element.target).parents('.stepper-content').next().css('display', 'block');
                        }, 400);
                        this.activeHeader[current] = 0;
                        this.activeHeader[go] = 1;
                        localStorage.setItem("activeHeader_Record", JSON.stringify(this.activeHeader));
                    },
                    backBtn: function(element, current, back) {
                        ts.q(element.target).parents('.stepper-content').addClass('stepper-content-back');
                        setTimeout(function() {
                            ts.q(element.target).parents('.stepper-content').css('display', 'none').removeClass('stepper-content-back');
                            ts.q(element.target).parents('.stepper-content').prev().css('display', 'block');
                        }, 400);
                        this.activeHeader[back] = 1;
                        this.activeHeader[current] = 0;
                        localStorage.setItem("activeHeader_Record", JSON.stringify(this.activeHeader));
                    },
                    transaction: function(staff) {
                        var vm = this;
                        var data = {
                            year: this.currentYear,
                            staff_id: staff.id,
                            department_id: staff.department_id,
                            feedback: staff._can_feedback,
                            assessment: staff._can_assessment
                        }
                        API.updateYearlyConstructStaff(data).then(function(e) {
                            var result = API.format(e);
                            if (result.is) {
                                Materialize.toast('已為您變更「' + staff.name + staff.name_en + '」的資料', 2000);
                                vm.getConstruct();
                            } else {
                                swal('變更失敗', result.get(), 'error');
                            }
                        });
                    },
                    finsh: function() {
                        var vm = this;
                        swal({
                                title: "建立部屬回饋問卷",
                                text: "問卷產生後，設定值將不可改變，確認要執行?!",
                                type: "warning",
                                showCancelButton: true,
                                confirmButtonColor: "#DD6B55",
                                confirmButtonText: "執行",
                                cancelButtonText: "取消",
                                closeOnConfirm: false
                            },
                            function() {
                                API.checkYearlyFeedback({ year: vm.currentYear }).then(function(e) {
                                    var result = API.format(e);
                                    if (result.is) {
                                        swal("完成", "已完成產生問卷", "success");
                                        vm.show = 0;
                                        ts.vm.yearlySetting._data.show = 1;
                                        API.reload();
                                    }
                                });
                            });
                    }
                },
                watch: {
                    currentYear: function(val) {
                        this.currentYear = val;
                        this.initSetting();
                    },
                    StartDate: {
                        deep: true,
                        handler: function(val) {
                            var vm = this;
                            var daysLength = new Date(val.year, val.month, 0).getDate();
                            vm.dayLength.start = [];
                            for (var d = 1; d <= daysLength; d++) {
                                vm.dayLength.start.push(d);
                            }
                        }
                    },
                    EndDate: {
                        deep: true,
                        handler: function(val) {
                            var vm = this;
                            var daysLength = new Date(val.year, val.month, 0).getDate();
                            vm.dayLength.end = [];
                            for (var d = 1; d <= daysLength; d++) {
                                vm.dayLength.end.push(d);
                            }
                        }
                    },
                },
                created: function() {
                    this.initSetting();
                },
                mounted: function() {
                    var ele = this.$el;
                    ts.q(ele).q('.dropdown-button').dropdown();
                }
            });
        }

        function buildYearSetting() {
            ts.vm.yearlySetting = new Vue({
                el: '.rv-yearsetting',
                data: {
                    config: {},
                    processing: 0,
                    currentYear: ts.vm.readySetting._data.currentYear,
                    activeYearly: { headerOne: 1, headerTwo: 2, headerThree: 3, headerFour: 4, headerFive: 5, headerSix: 6, headerSeven: 7, headerEight: 8, headerNine: 9 },
                    setFeedbackDate: 7,
                    setYealyDate: 10,
                    fbkStartDate: date.getFullYear() + '年' + (date.getMonth() + 1) + '月' + date.getDate() + '日',
                    fbkEndDate: '',
                    fbkStatistics: {},
                    yearAssStartDate: date.getFullYear() + '年' + (date.getMonth() + 1) + '月' + date.getDate() + '日',
                    yearAssEndDate: '',
                },
                methods: {
                    setDateSetting: function() {
                        var nowDate = date.getFullYear() + '-' + (date.getMonth() + 1) + '-' + date.getDate();
                        var endDateFbk = new Date(nowDate);
                        var endDateYearly = new Date(nowDate);
                        var feedbackDateFormat = endDateFbk.setDate(endDateFbk.getDate() + parseInt(this.setFeedbackDate));
                        var yearlyDateFormat = endDateYearly.setDate(endDateYearly.getDate() + parseInt(this.setYealyDate));
                        var feedbackDay = new Date(feedbackDateFormat);
                        var yearlyDay = new Date(yearlyDateFormat);
                        this.fbkEndDate = feedbackDay.getFullYear() + "年" + (feedbackDay.getMonth() + 1) + "月" + feedbackDay.getDate() + "日";
                        this.yearAssEndDate = yearlyDay.getFullYear() + "年" + (yearlyDay.getMonth() + 1) + "月" + yearlyDay.getDate() + "日";
                    },
                    setFBK: function() {
                        var vm = this;
                        if (this.setFeedbackDate < 0) { return swal('不能負數', '請輸入大於0的數字', 'error'); }
                        // 設置問卷時間 - Mail提醒
                        API.updateYearlyConfig({ year: this.currentYear, feedback_addition_day: this.setFeedbackDate }).then(function(e) {
                            var result = API.format(e);
                            if (result.is) {
                                console.log('設置問卷時間成功：' + result.is);
                            } else {
                                console.log('設置問卷時間失敗：' + result.get());
                            }
                        });
                        this.startFBK();
                    },
                    startFBK: function() {
                        var vm = this;
                        API.launchYearlyFeedback({ year: this.currentYear }).then(function(e) {
                            var result = API.format(e);
                            if (result.is) {
                                swal('設定成功', '已為您重新啟動部屬回饋問卷流程', 'success');
                                vm.processing = result.get().processing;
                            } else {
                                swal('設定失敗', result.get(), 'error');
                            }
                        });
                    },
                    stopFBK: function() {
                        var vm = this;
                        API.closeYearlyFeedback({ year: this.currentYear }).then(function(e) {
                            var result = API.format(e);
                            if (result.is) {
                                swal('設定成功', '已為您關閉部屬回饋問卷流程', 'success');
                                vm.processing = result.get().processing;
                            } else {
                                swal('設定失敗', result.get(), 'error');
                            }
                        });
                    },
                    resetFBK: function() {
                        var vm = this;
                        swal({
                                title: "清除所有部屬回饋問卷",
                                text: "執行後，清除的資料將無法恢復，確認要執行?!",
                                type: "warning",
                                showCancelButton: true,
                                confirmButtonColor: "#DD6B55",
                                confirmButtonText: "執行",
                                cancelButtonText: "取消",
                                closeOnConfirm: false
                            },
                            function() {
                                API.checkYearlyFeedback({ year: vm.currentYear, reset: 1 }).then(function(e) {
                                    var result = API.format(e);
                                    if (result.is) {
                                        swal('清除成功', '已為您清除部屬回饋問卷', 'success');
                                        ts.vm.readySetting._data.processing = result.get().processing;
                                        vm.processing = result.get().processing;
                                    } else {
                                        swal('清除失敗', result.get(), 'error');
                                    }
                                });
                            });
                    },
                    getFBKStatistics: function() {
                        var vm = this;
                        API.getYearlyFeedBackStatistics({ year: this.currentYear }).then(function(e) {
                            var result = API.format(e);
                            if (result.is) {
                                vm.fbkStatistics = result.get();
                            }
                        });
                    },
                    createYearlyAssessment: function() {
                        var vm = this;
                        swal({
                                title: "產生年度考評單",
                                text: "執行後，部屬問卷將無法在進行任何調整，確認要執行?!",
                                type: "warning",
                                showCancelButton: true,
                                confirmButtonColor: "#DD6B55",
                                confirmButtonText: "執行",
                                cancelButtonText: "取消",
                                closeOnConfirm: false
                            },
                            function() {
                                API.checkYearlyAssessment({ year: vm.currentYear }).then(function(e) {
                                    var result = API.format(e);
                                    if (result.is) {
                                        swal('設定成功', '已為您產生年度考評單', 'success');
                                        vm.processing = result.get().processing;
                                    } else {
                                        swal('設定失敗', result.get(), 'error');
                                    }
                                });
                            });
                    },
                    setYearlyAssessment: function() {
                        var vm = this;
                        if (this.setYealyDate < 0) { return swal('不能負數', '請輸入大於0的數字', 'error'); }
                        API.updateYearlyConfig({ year: this.currentYear, assessment_addition_day: this.setYealyDate }).then(function(e) {
                            var result = API.format(e);
                            if (result.is) {

                                console.log('設置年度考評單時間成功：' + result.is);
                            } else {
                                console.log('設置年度考評單時間失敗：' + result.get());
                            }
                        });
                        this.operatYearlyProcess(1);
                    },
                    operatYearlyProcess: function(status) {
                        // 1:START , 0:RESTART
                        var vm = this;
                        var MSG_START = '已為您產生年度考評單';
                        var MSG_RESTART = '已為您重新年度考評單';
                        var MSG_STATUS = status ? MSG_START : MSG_RESTART;
                        API.launchYearlyAssessment({ year: this.currentYear }).then(function(e) {
                            var result = API.format(e);
                            if (result.is) {
                                swal('設定成功', MSG_STATUS, 'success');
                                vm.processing = result.get().processing;
                            } else {
                                swal('設定失敗', result.get(), 'error');
                            }
                        });
                    },
                    stopYearlyAssessment: function() {
                        var vm = this;
                        API.closeYearlyAssessment({ year: this.currentYear }).then(function(e) {
                            var result = API.format(e);
                            if (result.is) {
                                swal('設定成功', '已為您停止考評流程', 'success');
                                vm.processing = result.get().processing;
                            } else {
                                swal('設定失敗', result.get(), 'error');
                            }
                        });
                    },
                    resetYearlyAssessment: function() {
                        var vm = this;
                        API.checkYearlyAssessment({ year: this.currentYear, reset: 1 }).then(function(e) {
                            var result = API.format(e);
                            if (result.is) {
                                swal('清除成功', '已為您清除年考評單', 'success');
                                vm.processing = result.get().processing;
                            } else {
                                swal('清除成功', result.get(), 'error');
                            }
                        });
                    },
                    finishYearlyAssessment: function() {
                        var vm = this;
                        API.finishYearly({ year: this.currentYear }).then(function(e) {
                            var result = API.format(e);
                            if (result.is) {
                                swal('結束考評', '正式結束該年度考評', 'success');
                                vm.processing = result.get().processing;
                            } else {
                                swal('結束失敗', result.get(), 'error');
                            }
                        });
                    }
                },
                watch: {
                    setFeedbackDate(val) {
                        let vm = this,
                            num = Number(val);
                        if (num < 0 || num > 99) {
                            swal('超出允許範圍', "0 ~ 99", 'error');
                            vm.setFeedbackDate = num > 99 ? 99 : 0;
                        } else {
                            vm.setDateSetting();
                        }
                    },
                    setYealyDate: function(val) {
                        let vm = this,
                            num = Number(val);
                        if (num < 0 || num > 99) {
                            swal('超出允許範圍', "0 ~ 99", 'error');
                            vm.setYealyDate = num > 99 ? 99 : 0;
                        } else {
                            vm.setDateSetting();
                        }
                    }
                },
                created: function() {
                    this.setDateSetting();
                    this.getFBKStatistics();
                    this.config = config;
                    this.processing = config.processing;
                },
                mounted: function() {
                    var ele = this.$el;
                    ts.q(ele).q('.dropdown-button').dropdown();
                }
            });
        }

        config_Deferred.done(function() {
            buildReadySetting();
            buildYearSetting();
        });
    });
});