var $yearEvaluationOverview = $('#Year-EvaluationOverview').generalController(function() {
    var ts = this;
    var current = $.ym.get();
    var getYear = current.year;
    var staffId;
    var year = ts.q("#getYear").empty();
    var leaderEvaluationItem = {
        'titleArray': [{
            'id': 1,
            'title': '工作績效',
            'itemBlock': 3
        }, {
            'id': 2,
            'title': '知識技能',
            'itemBlock': 3
        }, {
            'id': 3,
            'title': '溝通協調能力',
            'itemBlock': 2
        }, {
            'id': 4,
            'title': '品德及工作態度',
            'itemBlock': 5
        }, {
            'id': 5,
            'title': '管理能力',
            'itemBlock': 4
        }]
    };
    var staffEvaluationItem = {
        'titleArray': [{
            'id': 1,
            'title': '工作績效',
            'itemBlock': 3
        }, {
            'id': 2,
            'title': '知識技能',
            'itemBlock': 3
        }, {
            'id': 3,
            'title': '溝通協調能力',
            'itemBlock': 2
        }, {
            'id': 4,
            'title': '品德及工作態度',
            'itemBlock': 5
        }]
    };

 year.yearSet();  // yearSet() in header.js

    ts.onLogin(function(member) {
        // console.log(JSON.parse(JSON.stringify(member)));
        // console.log($.fn.fixMe);
        var vm = new Vue({
            el: '.rv-year-overview',
            data: {
                viewData: {
                    leader: [],
                    staff: []
                },
                isShow: 0,
                reverse: false,
                scoreRange: [],
                memberLv: member.lv,
                year: current.year,
                isleader: member.is_leader,
                isAdmin: member.is_admin,
                isCEO: member.id,
                devno: ts.q('#getDep').val(),
                is_over: ts.q('#getOver').val(),
                w_ass: 1,
                personalInfo: {
                    attendance_json: { absent: {}, early: {}, late: {}, leave: {}, nocard: {}, paysick: {}, physiology: {}, sick: {} },
                    assessment_json: {
                        1: { percent: {}, total: {}, score: {} },
                        2: { percent: {}, total: {}, score: {} },
                        3: { percent: {}, total: {}, score: {} },
                        4: { percent: {}, total: {}, score: {} },
                        self: { percent: {}, total: {}, score: {} },
                        under: {
                            percent: {},
                            total: {},
                            score: {}
                        }
                    },
                    upper_comment: {
                        1: { content: {}, staff_id: {} },
                        2: { content: {}, staff_id: {} },
                        3: { content: {}, staff_id: {} },
                        4: { content: {}, staff_id: {} }
                    }
                },
                leaderThead: leaderEvaluationItem,
                staffThead: staffEvaluationItem,
                personName: '',
                underComment: [], // 未分類的下屬意見
                underComm:{ // 將下屬的意見分類
                    adv:[], // 優點
                    impro:[],// 改善
                    sugg:[]// 建議
                },
                otherComment: [],
                leaderTopic: {},
                staffTopic: {},
                authCom: 1, // 判斷可否看部屬回饋
                rangeLength: 0,
                report_sort: { 'division_code': 1, 'division_name': 1, 'department_name': 1, 'staff_no': 1, 'staff_post': 1, 'staff_title': 1, 'staff_first_day': 1, 'staff_name': 1, 'assessment_total': 1, 'assessment_total_division_change': 1, 'assessment_total_ceo_change': 1, 'assessment_total_final': 1, 'level': 1 },
                report_sort_now: 'division_code',
                staff_sort: { 'division_code': 1, 'division_name': 1, 'department_name': 1, 'staff_no': 1, 'staff_post': 1, 'staff_title': 1, 'staff_first_day': 1, 'staff_name': 1, 'assessment_total': 1, 'assessment_total_division_change': 1, 'assessment_total_ceo_change': 1, 'assessment_total_final': 1, 'level': 1 },
                staff_sort_now: 'division_code',
                feedback: {}
            },
            created: function() {
                var vthis = this;
                API.getYearlyTopic({ year: vthis.year }).then(function(e) {
                    var gyt = API.format(e);
                    if (gyt.is) {
                        var result = gyt.res();
                        vthis.leaderTopic = result.leader;
                        vthis.staffTopic = result.normal;
                    }
                });
            },
            mounted: function() {
                this.DataInit();
                ts.q(this.$el).q('.dropdown').dropdown();

            },
            computed: {
                orderedLeaders: function() {
                    return ts.q(this.$el).orderBy(this.viewData.leader, 'assessment_total_final')
                }
            },
            methods: {
                DataInit: function() {
                    var vthis = this;
                    var person = [];
                    if (vthis.w_ass == false) {
                        vthis.w_ass = 0
                    } else {
                        vthis.w_ass = 1
                    }
                    var data = {
                        year: this.year,
                        department_level: vthis.devno,
                        with_assignment: vthis.w_ass,
                        is_over: vthis.is_over
                    }
                    $.ym.save(data);
                    API.getYearlyReportTotal(data).then(function(e) {
                        var result = API.format(e);
                        if (result.is) {
                            var list = result.res();
                            var leaderno = 0;
                            var staffno = 0;
                            var leaderlistAble = [];
                            var stafflistAble = [];
                            vthis.viewData.leader = list.assessment.leader;
                            vthis.viewData.staff = list.assessment.staff;
                            vthis.scoreRange = list.distribution;

                            // 作廢的單子不算
                            for (var i in list.assessment.leader) {
                                if (list.assessment.leader[i].enable != 0) {
                                    leaderno++;
                                    leaderlistAble.push(list.assessment.leader[i]);
                                }
                            }
                            for (var s in list.assessment.staff) {
                                if (list.assessment.staff[s].enable != 0) {
                                    staffno++;
                                    stafflistAble.push(list.assessment.staff[s]);
                                }
                            }

                            vthis.rangeLength = leaderno + staffno;
                            if (vthis.devno == 0) {
                                vthis.rangeLength = vthis.rangeLength - 1; // 扣掉CEO的單子
                            }
                            if (vthis.devno == 1) {
                                vthis.rangeLength = vthis.rangeLength - 1; // 扣掉CEO的單子
                            }

                            ts.q('#NoData').hide();
                            ts.q('#year-resultBlock').show();

                            //等 vue 渲染完
                            var timer = setTimeout(function() {
                                var t1 = new Date().getTime();
                                ts.q('.card table').fixMe();
                                ts.q('.fixedTable').on('click', function(e) {
                                        var top = $(this).parent().offset().top - 67;
                                        $('body,html').stop(true).animate({ scrollTop: top }, 500);
                                    })
                                    .q('.sort-this').removeClass('sort-this');
                            }, 50);

                        } else {
                            console.log('no data')
                            ts.q('#NoData').show();
                            ts.q('#year-resultBlock').hide();
                        }
                    })

                    //判斷可否看部屬回饋
                },
                LeaderSortBy: function(key) {
                    var vm = this;
                    if (isNaN(vm.report_sort[key])) { return console.log('not found leader sort key : ' + key); }
                    vm.report_sort[key] = (vm.report_sort[key] + 1) % 2;
                    vm.report_sort_now = key;
                    var asc = vm.report_sort[key] == 1;
                    return vm.viewData.leader.sort(function(a, b) {
                        var ai = isNaN(a[key]) ? a[key].charCodeAt(0) : a[key];
                        var bi = isNaN(b[key]) ? b[key].charCodeAt(0) : b[key];
                        switch (key) {
                            case 'staff_no':
                                ai = a[key].substr(1);
                                bi = b[key].substr(1);
                                break;
                            case 'staff_first_day':
                                ai = new Date(a[key]);
                                bi = new Date(b[key]);
                                break;
                            case 'level':
                                ai = a[key].charCodeAt(0);
                                bi = b[key].charCodeAt(0);
                                break;
                        }
                        return asc ? ai - bi : bi - ai;
                    });
                },
                StaffSortBy: function(key) {
                    var vm = this;
                    if (isNaN(vm.staff_sort[key])) { return console.log('not found staff sort key : ' + key); }
                    vm.staff_sort[key] = (vm.staff_sort[key] + 1) % 2;
                    vm.staff_sort_now = key;
                    var asc = vm.staff_sort[key] == 1;
                    return vm.viewData.staff.sort(function(a, b) {
                        var ai = isNaN(a[key]) ? a[key].charCodeAt(0) : a[key];
                        var bi = isNaN(b[key]) ? b[key].charCodeAt(0) : b[key];
                        switch (key) {
                            case 'staff_no':
                                ai = a[key].substr(1);
                                bi = b[key].substr(1);
                                break;
                            case 'staff_first_day':
                                ai = new Date(a[key]);
                                bi = new Date(b[key]);
                                break;
                            case 'level':
                                ai = a[key].charCodeAt(0);
                                bi = b[key].charCodeAt(0);
                                break;
                        }
                        return asc ? ai - bi : bi - ai;
                    });
                },
                rateLimit: function(r) {
                    return Math.ceil(this.rangeLength * (r.rate_limit / 100));
                },
                rateLeast: function(r) {
                    return Math.floor(this.rangeLength * (r.rate_least / 100));
                },
                expoYearlyExcel: function() {
                    var vthis = this;
                    if (vthis.w_ass == false) {
                        vthis.w_ass = 0
                    } else {
                        vthis.w_ass = 1
                    }
                    var data = {
                        year: this.year,
                        department_level: vthis.devno,
                        with_assignment: vthis.w_ass,
                        is_over: vthis.is_over
                    }
                    API.downloadYearlyAssessmentTotal(data)
                    Materialize.toast('年度績效考評單下載中...', 2000)
                },
                totalScore: function(scoreData) {
                    var total = 0;
                    for (var score in scoreData) {
                        total += parseInt(scoreData[score]);
                    }
                    return total;
                },
                personalReport: function(report, isleader) {
                    $('body').css({ 'overflow': 'hidden' });
                    ts.q('.sidenav').addClass('moveleft');
                    var vthis = this;

                    var data = {
                        year: this.year,
                        department_level: vthis.devno,
                        with_assignment: vthis.w_ass
                    }
                    $.ym.save(data);
                    API.getYearlyReportTotal(data).then(function(e) {
                        var result = API.format(e);
                        var sheetlist = result.res();
                        var list = {};
                        var person = {};
                        if (isleader == 1) {
                            list = sheetlist.assessment.leader;

                        } else {
                            list = sheetlist.assessment.staff;
                        }

                        for (var i in list) {
                            person[list[i].id] = list[i];
                        }

                        vthis.personalInfo = person[report.id];
                        // console.log(vthis.personalInfo)
                        var assessment = person[report.id].assessment_json;
                        for (var prop in assessment) {
                            for (var score in assessment[prop].score) {
                                if (assessment[prop].score[score] == -1) { assessment[prop].score[score] = 0 }
                            }
                        }

                        getUnderComm();
                    });
                    this.feedback = {}
                    this.getFeedbackDetail(report.staff_id);
                    // get 部屬回饋資料
                    function getUnderComm() {
                        API.getYearlyAllReportWord({ assessment_id: report.id }).then(function(e) {
                            var resultCom = API.format(e).res();
                            var fbcomment = resultCom.question;
                            var underCom = [];
                            var reUnderCom=[];
                            var otherCom = [];
                            for (var i in fbcomment) {
                                switch (fbcomment[i].from_type) {
                                    case 1:
                                        underCom.push(fbcomment[i]);
                                        break;
                                    case 2:
                                        otherCom.push(fbcomment[i]);
                                }
                            }
                            //console.log(underCom) // 未分類前

                            // 優點
                            vthis.underComm.adv = underCom.filter(function(e){
                                return e.question_id ==1;
                            })
                            // 改善
                            vthis.underComm.impro = underCom.filter(function(e){
                                return e.question_id ==2;
                            })
                            // 建議
                            vthis.underComm.sugg = underCom.filter(function(e){
                                return e.question_id ==4;
                            })
                            //console.log(vthis.underComm)      // 分類後的回饋


                            vthis.underComment = underCom;
                            vthis.otherComment = otherCom;
                        })

                        // 判斷是否可以看部屬回饋

                        if (vthis.personalInfo.staff_id == member.id || member.id == 1 || member.is_admin == 1) {
                            vthis.authCom = 1;
                        } else {
                            vthis.authCom = 0;
                        }
                    }
                },
                getFeedbackDetail: function(id) {
                    var vm = this;
                    API.getFeedbackDetailByStaff({ year: this.year, staff_id: id }).then(function(e) {
                        var result = API.format(e);
                        var size = Math.pow(10, 2);
                        if (result.is) {
                            var detail = result.get();
                            var detailTotal = 0;
                            for (var d in detail) {
                                detailTotal += detail[d].point;
                            }
                            vm.feedback = detail;
                            vm.feedback['total'] = Math.round(detailTotal * size) / size;
                        }
                    });
                },
                closeSheet: function() {
                    ts.q('.sidenav').removeClass('moveleft');
                    $('body').css({ 'overflow': '' });
                },
                downloadExcel: function(id) {
                    var vm = this;
                    API.exportYearlyAssessmentExcel({ year: vm.year, staff_id: id }).then(function(e) {
                        var result = API.format(e);
                        if (result.is) {
                            swal('開始下載', '正在為您下載考評單', 'success');
                        } else {
                            swal('下載失敗', result.get(), 'error');
                        }
                    });
                },
            },
            updated: function() {},
            downloadExcel: function(id) {
                API.exportYearlyAssessmentExcel({ year: this.currentYear, staff_id: id }).then(function(e) {
                    var result = API.format(e);
                    if (result.is) {
                        swal('下載成功', '已為您下載考評單', 'success');
                    } else {
                        swal('下載失敗', result.get(), 'error');
                    }
                });
            }
        }) // end of  vm
    });
});