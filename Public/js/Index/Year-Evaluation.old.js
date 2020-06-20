var $yearEvaluationPage = $('#Year-Evaluation').generalController(function() {
    var ts = this;
    ts.onLogin(function(member) {
        ts.vm = {};
        var today = new Date(member.now);
        var todayYear = today.getFullYear();
        var leaderEvaluationItem = { 'titleArray': [{ 'id': 1, 'title': '工作績效', 'itemBlock': 3 }, { 'id': 2, 'title': '知識技能', 'itemBlock': 3 }, { 'id': 3, 'title': '溝通協調能力', 'itemBlock': 2 }, { 'id': 4, 'title': '品德及工作態度', 'itemBlock': 5 }, { 'id': 5, 'title': '管理能力', 'itemBlock': 4 }] };
        var staffEvaluationItem = { 'titleArray': [{ 'id': 1, 'title': '工作績效', 'itemBlock': 3 }, { 'id': 2, 'title': '知識技能', 'itemBlock': 3 }, { 'id': 3, 'title': '溝通協調能力', 'itemBlock': 2 }, { 'id': 4, 'title': '品德及工作態度', 'itemBlock': 5 }] };

        // 判斷部長身份
        var assessmentData = member._is_division_leader ? { year: todayYear, self: 1 } : { year: todayYear };

        // 下拉選單分數上限值
        // leader: [工作績效, 知識技能, 溝通協調能力, 品德及工作態度, 管理能力], staff: [工作績效, 知識技能, 溝通協調能力, 品德及工作態度]
        var optionScore = { leader: [15, 4, 4, 3, 5], staff: [20, 4, 4, 4] };
        var topic = {};
        var config = {};
        var assessmentCount = 1;
        var divisionCount = 1;
        ts.evaluation = { 1: true, 2: true };

        // 獲取processing進度
        API.getYearlyConfig({ year: todayYear }).then(function(e) {
            var result = API.format(e);
            if (result.is) {
                config = result.get();
            }
        });

        // 年度的年考績主題
        function getTopicSave() {
            return new Promise(function(resolve) {
                API.getYearlyTopic({ year: todayYear }).then(function(e) {
                    var gyt = API.format(e);
                    if (gyt.is) {
                        topic = gyt.res();
                        resolve('ok');
                    }
                });
            });
        }

        getTopicSave().then(function(e) {
            API.getYearlyAssessment(assessmentData).then(function(e) {
                var cnt = API.format(e);

                if (cnt.is) {
                    var assessment = cnt.res();
                    for (var a in assessment) {
                        for (var aj in assessment[a].assessment_json) {
                            for (var s in assessment[a].assessment_json[aj].score) {
                                // 前端資料初始化為零
                                if (assessment[a].assessment_json[aj].score[s] < 0) { assessment[a].assessment_json[aj].score[s] = 0; }
                            }
                        }
                        callVueRenderAssessment(assessment[a]);
                    }
                } else {
                    ts.evaluation[1] = false;
                }
            });
        });

        if (member._is_division_leader) {
            API.getYearlyDivisionZone({ year: todayYear }).then(function(e) {
                var result = API.format(e);
                ts.vm.DivisionZone = {};
                if (result.is) {
                    var divisionReports = result.res();
                    for (var report in divisionReports) {
                        callVueRenderDivisionZone(divisionReports[report]);
                    }
                } else {
                    ts.evaluation[2] = false;
                }
            });
        } else {
            ts.evaluation[2] = false;
        }

        setTimeout(function() {
            if (ts.evaluation[1] || ts.evaluation[2]) {
                ts.q('#NoData').hide();
            } else {
                ts.q('#NoData').show();
            }
        }, 200);


        function callVueRenderAssessment(data) {
            var rand = 'row-assessment' + (assessmentCount++);
            ts.q('#YearEvaluationForm').append('<div id="' + rand + '" ></div>');
            ts.vm.Assessment = new Vue({
                template: '#template-evaluation',
                el: '#' + rand,
                data: {
                    processing: config.processing,
                    currentYear: todayYear,
                    rand: rand,
                    evaluation: data,
                    leaderTopic: topic.leader,
                    staffTopic: topic.normal,
                    leaderThead: leaderEvaluationItem,
                    staffThead: staffEvaluationItem,
                    optionScore: optionScore,
                    historyRecords: {},
                    rejectReason: '',
                    opinionFeedback: { upper_comment: { 1: {}, 2: {}, 3: {}, 4: {} }, question: { 'question_1': [], 'question_2': [], 'question_4': [], 'question_5': [] } },
                    checkReport: 0
                },
                methods: {
                    saveYearlyReport: function(id) {
                        // self=自己, 4=組, 3=處, 2=部, 1=中心
                        var vm = this;
                        return new Promise(function(resolve, reject) {
                            var data = {
                                assessment_id: id,
                                assessment_json: {}
                            }
                            // 自評儲存
                            if (vm.evaluation.staff_id == vm.evaluation.owner_staff_id) {
                                data['assessment_json']['self'] = vm.evaluation.assessment_json.self.score;
                                data['self_contribution'] = vm.evaluation.self_contribution;
                                data['self_improve'] = vm.evaluation.self_improve;
                            }
                            // 組長儲存
                            if (vm.evaluation.assessment_json[4] && vm.evaluation.path_lv[4][1] == vm.evaluation.owner_staff_id) {
                                data['assessment_json']['4'] = vm.evaluation.assessment_json[4].score;
                                if (vm.evaluation.upper_comment[4].content) { data['comment'] = { 4: vm.evaluation.upper_comment[4].content } }
                            }
                            // 處長儲存
                            if (vm.evaluation.assessment_json[3] && vm.evaluation.path_lv[3][1] == vm.evaluation.owner_staff_id) {
                                data['assessment_json']['3'] = vm.evaluation.assessment_json[3].score;
                                if (vm.evaluation.upper_comment[3].content) { data['comment'] = { 3: vm.evaluation.upper_comment[3].content } }
                            }

                            API.saveYearlyAssessment(data).then(function(e) {
                                var result = API.format(e);
                                if (result.is || result.get() == "Nothing Changed.") {
                                    Materialize.toast('已為您儲存當前資料', 2000);
                                    resolve('ok');
                                } else {
                                    swal('儲存失敗', result.get(), 'error');
                                }
                            });
                        });
                    },
                    copyData: function(value) {
                        if (!this.evaluation.assessment_json[value + 1]) {
                            for (var i in this.evaluation.assessment_json['self']['score']) {
                                this.evaluation.assessment_json[value]['score'][i] = this.evaluation.assessment_json['self']['score'][i]
                            }
                            this.evaluation.assessment_json[value]['total'] = this.evaluation.assessment_json['self']['total'];
                        } else {
                            for (var j in this.evaluation.assessment_json[value + 1]['score']) {
                                this.evaluation.assessment_json[value]['score'][j] = this.evaluation.assessment_json[value + 1]['score'][j];
                            }
                            this.evaluation.assessment_json[value]['total'] = this.evaluation.assessment_json[value + 1]['total'];
                        }
                    },
                    commitYearlyReport: function(id) {
                        var vm = this;
                        swal({
                                title: "送審年度績效單",
                                text: "送審後，將不可變更，確認要執行?!",
                                type: "warning",
                                showCancelButton: true,
                                confirmButtonColor: "#DD6B55",
                                confirmButtonText: "執行",
                                cancelButtonText: "取消",
                                closeOnConfirm: false
                            },
                            function() {
                                vm.saveYearlyReport(id).then(function(e) {
                                    API.commitYearlyAssessment({ assessment_id: id }).then(function(e) {
                                        var result = API.format(e);
                                        if (result.is) {
                                            swal('送審成功', '已為您送審考評單', 'success');
                                            $(vm.$el).remove();
                                        } else {
                                            swal('送審失敗', result.get(), 'error');
                                        }
                                    });
                                });
                            });
                    },
                    rejectYearlyReport: function(id) {
                        var vm = this;
                        API.rejectYearlyAssessment({ assessment_id: id, reason: this.rejectReason }).then(function(e) {
                            var result = API.format(e);
                            if (result.is) {
                                swal('退回成功', '已為您退回考評單', 'success');
                                ts.q('#ReJectModal-' + rand).modal("close");
                                $(vm.$el).remove();
                            } else {
                                swal('退回失敗', result.get(), 'error');
                            }
                        });
                    },
                    totalScore: function(scoreData) {
                        var total = 0;
                        for (var score in scoreData) {
                            total += parseInt(scoreData[score]);
                        }
                        return total;
                    },
                    getRecords: function(id) {
                        var vm = this;
                        API.getYearlyHistoryRecord({ assessment_id: id }).then(function(e) {
                            var result = API.format(e);
                            if (result.is) {
                                var record = result.res();
                                for (var r in record) {
                                    var separateDate = record[r].date.split(" ");
                                    record[r]['date'] = separateDate[0]
                                    record[r]["time"] = separateDate[1]
                                }
                                vm.historyRecords = record;
                            }
                        });
                    },
                    getReportWord: function(id) {
                        var vm = this;
                        API.getYearlyAllReportWord({ assessment_id: id }).then(function(e) {
                            var result = API.format(e);
                            if (result.is) {
                                vm.opinionFeedback = result.get();
                                if (vm.opinionFeedback.question.length) {
                                    var questionObj = { 'question_1': [], 'question_2': [], 'question_4': [], 'question_5': [] };
                                    for (var i in vm.opinionFeedback.question) {
                                        if (vm.opinionFeedback.question[i].from_type) {
                                            switch (vm.opinionFeedback.question[i].question_id) {
                                                case 1:
                                                    questionObj.question_1.push(vm.opinionFeedback.question[i]);
                                                    break;
                                                case 2:
                                                    questionObj.question_2.push(vm.opinionFeedback.question[i]);
                                                    break;
                                                case 4:
                                                    questionObj.question_4.push(vm.opinionFeedback.question[i]);
                                                    break;
                                                case 5:
                                                    questionObj.question_5.push(vm.opinionFeedback.question[i]);
                                                    break;
                                            }
                                        }
                                    }
                                    vm.opinionFeedback.question = questionObj;
                                    console.log(questionObj.question_5)
                                }
                            }
                        });
                    }
                },
                mounted: function() {
                    var ele = this.$el;
                    ts.q(ele).q('.collapsible').collapsible();
                    ts.q(ele).q('.modal').modal();
                    ts.q(ele).q('.tabs').tabs();
                }
            });
        }

        function callVueRenderDivisionZone(report) {
            var rand = 'row-division' + (divisionCount++);
            ts.q('#YearEvaluationForm').append('<div id="' + rand + '" ></div>');
            ts.vm.DivisionZone[report['division']] = new Vue({
                template: '#template-division-zone',
                el: '#' + rand,
                data: {
                    rand: rand,
                    member: member,
                    currentYear: todayYear,
                    processing: config.processing,
                    divisionData: report,
                    leaderThead: leaderEvaluationItem,
                    staffThead: staffEvaluationItem,
                    attendance: {},
                    reportScore: { 1: {}, 2: {}, 3: {}, 4: { score: {} }, self: {} },
                    hasMove: 0
                },
                methods: {
                    plusScore: function(obj, identity) {
                        // 0 = division, 1 = ceo
                        if (identity) {
                            obj.assessment_total_ceo_change += 1;
                            obj.total += 1;
                        } else {
                            obj.assessment_total_division_change += 1;
                            obj.total += 1;
                        }
                    },
                    minusScore: function(obj, identity) {
                        // 0 = division, 1 = ceo
                        if (identity) {
                            obj.assessment_total_ceo_change -= 1;
                            obj.total -= 1;
                        } else {
                            obj.assessment_total_division_change -= 1;
                            obj.total -= 1;
                        }
                    },
                    saveDivisionZone: function(id) {
                        var vm = this;
                        return new Promise(function(resolve, reject) {
                            var data = { division_id: id, assessment_change: {} }
                            for (var ddr in vm.divisionData._reports) {
                                if (vm.divisionData._canfix_division) {
                                    data["assessment_change"][vm.divisionData._reports[ddr].id] = vm.divisionData._reports[ddr].assessment_total_division_change
                                }
                                if (vm.divisionData._canfix_ceo) {
                                    data["assessment_change"][vm.divisionData._reports[ddr].id] = vm.divisionData._reports[ddr].assessment_total_ceo_change
                                }
                            }

                            if (vm.divisionData._reports.length != 0) { //待測試..
                                API.setFinallyScoreFix(data).then(function(e) {
                                    var result = API.format(e);
                                    if (result.is) {
                                        swal('儲存成功', '已為您儲存數據', 'success');
                                        resolve('ok');
                                        API.getYearlyDivisionZone({ year: vm.currentYear }).then(function(e) {
                                            var result = API.format(e);
                                            if (result.is) {
                                                var pk = result.res();
                                                for (var c in pk) {
                                                    if (pk[c].id == vm.divisionData.id) {
                                                        vm.divisionData = pk[c];
                                                    }
                                                }
                                            }
                                        });
                                    } else {
                                        swal('儲存失敗', result.get(), 'error');
                                    }
                                });
                            } else {
                                resolve('ok');
                            }
                        });
                    },
                    commitDivisionZone: function(id) {
                        var vm = this;
                        // 2 = 送到架構事業發展部過目狀態..
                        if (this.divisionData.processing == 2) {
                            API.commitDivisionZone({ division_id: id }).then(function(e) {
                                var result = API.format(e);
                                if (result.is) {
                                    swal('送出成功', '已為您送出數據', 'success');
                                    $(vm.$el).remove();
                                } else {
                                    swal('儲存失敗', result.get(), 'error');
                                }
                            });
                        } else {
                            this.saveDivisionZone(id).then(function(e) {
                                API.commitDivisionZone({ division_id: id }).then(function(e) {
                                    var result = API.format(e);
                                    if (result.is) {
                                        swal('送出成功', '已為您送出數據', 'success');
                                        vm.reRenderThisReport();
                                    } else {
                                        swal('儲存失敗', result.get(), 'error');
                                    }
                                });
                            });
                        }
                    },
                    rejectDivisionZone: function(id) {
                        var vm = this;
                        swal({
                                title: "退回部門單",
                                text: "即將為您退回部門單，確認要執行?!",
                                type: "warning",
                                showCancelButton: true,
                                confirmButtonColor: "#DD6B55",
                                confirmButtonText: "執行",
                                cancelButtonText: "取消",
                                closeOnConfirm: false
                            },
                            function() {
                                API.rejectDivisionZone({ division_id: id }).then(function(e) {
                                    var result = API.format(e);
                                    if (result.is) {
                                        swal('退回成功', '已為您退回部門單', 'success');
                                        vm.reRenderThisReport();
                                    } else {
                                        swal('儲存失敗', result.get(), 'error');
                                    }
                                });
                            });
                    },
                    downloadExcelDivisionZone: function(id) {
                        API.exportYearlyAssessmentExcel({ year: this.currentYear, division_id: id }).then(function(e) {
                            var result = API.format(e);
                            if (result.is) {
                                swal('下載成功', '已為您下載部門考評單', 'success');
                            } else {
                                swal('下載失敗', result.get(), 'error');
                            }
                        });
                    },
                    reRenderThisReport: function() {
                        var vm = this;
                        $(vm.$el).hide();
                        API.getYearlyDivisionZone({ year: vm.currentYear }).then(function(e) {
                            var result = API.format(e);
                            if (result.is) {
                                var pk = result.res();
                                for (var c in pk) {
                                    if (pk[c].id == vm.divisionData.id) {
                                        $(vm.$el).show();
                                        vm.divisionData = pk[c];
                                    }
                                }
                            }
                        });
                    },
                    personalReport: function(id) {
                        callVueRenderPersonal(id, 1);
                        $('body').css({ 'overflow': 'hidden' });
                    },
                    totalScore: function(scoreData) {
                        var total = 0;
                        for (var score in scoreData) {
                            total += parseInt(scoreData[score]);
                        }
                        return total;
                    }
                },
                mounted: function() {
                    frameworkInit(ts.q(this.$el));
                }
            });

            function frameworkInit($selector) {
                $selector.q('.modal').modal();
                $selector.q('.collapsible').collapsible({
                    onOpen: function(el) {
                        var element = $selector.q(".people-" + rand);
                        element.each(function() {
                            $(this).prop('Counter', 0).animate({
                                Counter: $(this).val()
                            }, {
                                duration: 1000,
                                easing: 'swing',
                                step: function(now) {
                                    $(this).val(Math.ceil(now));
                                }
                            });
                        });
                    }
                });
            }
        }

        function callVueRenderPersonal(id, move) {
            API.getYearlyAssessment({ year: todayYear, assessment_id: id }).then(function(e) {
                var result = API.format(e);
                if (result.is) {
                    var personalData = result.get();
                    for (var pd in personalData.assessment_json) {
                        for (var s in personalData.assessment_json[pd].score) {
                            // 前端資料初始化為零
                            if (personalData.assessment_json[pd].score[s] < 0) { personalData.assessment_json[pd].score[s] = 0; }
                        }
                    }

                    ts.vm.Personal = new Vue({
                        // template: '#template-personal',
                        el: '#mySidenav',
                        data: {
                            currentYear: todayYear,
                            personalInfo: personalData,
                            hasMove: move,
                            leaderTopic: topic.leader,
                            staffTopic: topic.normal,
                            leaderThead: leaderEvaluationItem,
                            staffThead: staffEvaluationItem,
                            optionScore: optionScore,
                            rejectReason: '',
                            historyRecords: {},
                            opinionFeedback: { upper_comment: { 1: {}, 2: {}, 3: {}, 4: {} }, question: { 'question_1': [], 'question_2': [], 'question_4': [], 'question_5': [] } },
                            actionCopyButton: 1
                        },
                        watch: {
                            hasMove: function(move) {
                                if (move) {
                                    $('body').css({ 'overflow': 'hidden' });
                                } else {
                                    $('body').css({ 'overflow': 'auto' });
                                }
                            }
                        },
                        methods: {
                            closeReport: function() {
                                this.hasMove = 0;
                                $('body').css({ 'overflow': 'auto' });
                            },
                            totalScore: function(scoreData) {
                                var total = 0
                                for (var score in scoreData) {
                                    total += parseInt(scoreData[score]);
                                }
                                return total;
                            },
                            savePersonalReport: function(id) {
                                var vm = this;
                                return new Promise(function(resolve, reject) {
                                    var data = {
                                        assessment_id: id,
                                        assessment_json: {}
                                    }

                                    // 組長儲存
                                    if (vm.personalInfo.assessment_json[4] && vm.personalInfo.path_lv[4][1] == vm.personalInfo.owner_staff_id) {
                                        data['assessment_json']['4'] = vm.personalInfo.assessment_json[4].score;
                                    }
                                    // 處長儲存
                                    if (vm.personalInfo.assessment_json[3] && vm.personalInfo.path_lv[3][1] == vm.personalInfo.owner_staff_id) {
                                        data['assessment_json']['3'] = vm.personalInfo.assessment_json[3].score;
                                    }

                                    // 部長儲存
                                    if (vm.personalInfo.assessment_json[2] && vm.personalInfo.path_lv[2][1] == vm.personalInfo.owner_staff_id) {
                                        data['assessment_json']['2'] = vm.personalInfo.assessment_json[2].score;
                                        if (vm.personalInfo.upper_comment[2]) { data['comment'] = { 2: vm.personalInfo.upper_comment[2].content } }
                                    }
                                    // 執行長儲存
                                    if (vm.personalInfo.assessment_json[1] && vm.personalInfo.path_lv[1][1] == vm.personalInfo.owner_staff_id) {
                                        if (vm.personalInfo._authority.edit == false) {
                                            data['comment'] = { 1: vm.personalInfo.upper_comment[1].content }
                                        } else {
                                            data['assessment_json']['1'] = vm.personalInfo.assessment_json[1].score;
                                            data['comment'] = { 1: vm.personalInfo.upper_comment[1].content }
                                        }
                                    }

                                    if (vm.personalInfo._authority.edit_comment && vm.personalInfo.path_lv[1][1] == vm.personalInfo.owner_staff_id) {
                                        data['comment'] = { 1: vm.personalInfo.upper_comment[1].content }
                                    }

                                    API.saveYearlyAssessment(data).then(function(e) {
                                        var result = API.format(e);
                                        if (result.is || result.get() == 'Nothing Changed.') {
                                            resolve('ok');
                                            vm.reRenderBigReport();
                                            Materialize.toast('已為您儲存當前資料', 2000);
                                        } else {
                                            swal('儲存失敗', result.get(), 'error');
                                        }
                                    });
                                });
                            },
                            commitPersonalReport: function(id) {
                                var vm = this;
                                swal({
                                        title: "送審年度績效單",
                                        text: "送審後，將不可變更，確認要執行?!",
                                        type: "warning",
                                        showCancelButton: true,
                                        confirmButtonColor: "#DD6B55",
                                        confirmButtonText: "執行",
                                        cancelButtonText: "取消",
                                        closeOnConfirm: false
                                    },
                                    function() {
                                        vm.savePersonalReport(id).then(function(e) {
                                            API.commitYearlyAssessment({ assessment_id: id }).then(function(e) {
                                                var result = API.format(e);
                                                if (result.is) {
                                                    vm.hasMove = 0;
                                                    vm.reRenderBigReport();
                                                    swal('送審成功', '已為您送審考評單', 'success');
                                                } else {
                                                    swal('送審失敗', result.get(), 'error');
                                                }
                                            });
                                        });
                                    });
                            },
                            rejectPersonalReport: function(id) {
                                var vm = this;
                                swal({
                                        title: "退回年度績效單",
                                        text: "即將為您退回年度績效單，確認要執行?!",
                                        type: "warning",
                                        showCancelButton: true,
                                        confirmButtonColor: "#DD6B55",
                                        confirmButtonText: "執行",
                                        cancelButtonText: "取消",
                                        closeOnConfirm: false
                                    },
                                    function() {
                                        API.rejectYearlyAssessment({ assessment_id: id, reason: vm.rejectReason }).then(function(e) {
                                            var result = API.format(e);
                                            if (result.is) {
                                                vm.hasMove = 0;
                                                vm.reRenderBigReport();
                                                ts.q('#ReJectModalPersonal').modal("close");
                                                swal('退回成功', '已為您退回考評單', 'success');
                                            } else {
                                                swal('退回失敗', result.get(), 'error');
                                            }
                                        });
                                    });
                            },
                            reRenderBigReport: function() {
                                var vm = this;
                                // 部長單 DivisionZone 更新資料..
                                API.getYearlyDivisionZone({ year: vm.currentYear }).then(function(e) {
                                    var result = API.format(e);
                                    if (result.is) {
                                        var comparison = result.res();
                                        for (var c in comparison) {
                                            if (comparison[c].id == ts.vm.DivisionZone[vm.personalInfo['division_id']].$data['divisionData']['id']) {
                                                ts.vm.DivisionZone[vm.personalInfo['division_id']].$data['divisionData'] = comparison[c]
                                            }
                                        }
                                    }
                                });
                            },
                            getRecords: function(id) {
                                var vm = this;
                                API.getYearlyHistoryRecord({ assessment_id: id }).then(function(e) {
                                    var result = API.format(e);
                                    if (result.is) {
                                        var record = result.res();
                                        for (var r in record) {
                                            var separateDate = record[r].date.split(" ");
                                            record[r]['date'] = separateDate[0]
                                            record[r]["time"] = separateDate[1]
                                        }
                                        vm.historyRecords = record;
                                    }
                                });
                            },
                            getReportWord: function(id) {
                                var vm = this;
                                API.getYearlyAllReportWord({ assessment_id: id }).then(function(e) {
                                    var result = API.format(e);
                                    if (result.is) {
                                        vm.opinionFeedback = result.get();
                                        if (vm.opinionFeedback.question.length) {
                                            var questionObj = { 'question_1': [], 'question_2': [], 'question_4': [], 'question_5': [] };
                                            for (var i in vm.opinionFeedback.question) {
                                                if (vm.opinionFeedback.question[i].from_type) {
                                                    switch (vm.opinionFeedback.question[i].question_id) {
                                                        case 1:
                                                            questionObj.question_1.push(vm.opinionFeedback.question[i]);
                                                            break;
                                                        case 2:
                                                            questionObj.question_2.push(vm.opinionFeedback.question[i]);
                                                            break;
                                                        case 4:
                                                            questionObj.question_4.push(vm.opinionFeedback.question[i]);
                                                            break;
                                                    }
                                                } else {
                                                    questionObj.question_5.push(vm.opinionFeedback.question[i]);
                                                }
                                            }
                                            vm.opinionFeedback.question = questionObj;
                                        }
                                    }
                                });
                            },
                            copyData: function(value) {
                                if (!this.personalInfo.assessment_json[value + 1]) {
                                    for (var i in this.personalInfo.assessment_json['self']['score']) {
                                        this.personalInfo.assessment_json[value]['score'][i] = this.personalInfo.assessment_json['self']['score'][i]
                                    }
                                    this.personalInfo.assessment_json[value]['total'] = this.personalInfo.assessment_json['self']['total'];
                                } else {
                                    for (var j in this.personalInfo.assessment_json[value + 1]['score']) {
                                        this.personalInfo.assessment_json[value]['score'][j] = this.personalInfo.assessment_json[value + 1]['score'][j]
                                    }
                                    this.personalInfo.assessment_json[value]['total'] = this.personalInfo.assessment_json[value + 1]['total'];
                                }
                            },
                        },
                        mounted: function() {
                            var ele = this.$el;
                            ts.q(ele).q('.modal').modal();
                            ts.q(ele).q('.tabs').tabs();
                        }
                    });
                }
            });
        }
    });
});