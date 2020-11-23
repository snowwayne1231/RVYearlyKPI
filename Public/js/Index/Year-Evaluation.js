var $YearEvaluationPage = $('#Year-Evaluation').generalController(function () {
    var ts = this,
        myself;
    var current = $.ym.get(),
        y_codition = { year: undoYear || current.year };
    ts.q('.rv-title span').text(undoYear || current.year); //設定年
    var deferred = [];
    var DEPARTMENT_CENTER = 1;
    var TOPIC_KEY = "Year_Evaluation_TOPIC";
    var topics = API.cache(TOPIC_KEY),
        config, division_data; //取得題目主題
    // console.log(y_codition) // from header.js


    //樣版
    var TEMPLATE_REPORT = $('#template-evaluation');
    var TEMPLATE_SIDENAV = $('#template-sidenav-personal');
    var TEMPLATE_DIVISION = $('#template-division-zone');
    var TEMPLATE_HTML_CARD_BODY = $('#template-yearreport-card-body').html();
    $('#template-yearreport-card-body').remove();

    //公開物件
    ts.evaluations = {};
    ts.modal = {};
    ts.DivisionZone = {};
    ts.personal = {};

    //區域物件
    var $YearEvaluationForm = ts.q('#YearEvaluationForm');

    //取得年設定
    deferred[0] = API.getYearlyConfig(y_codition);
    if (!topics) { //快取主題
        deferred[1] = API.getYearlyTopic(y_codition);
        deferred[1].then(function (e) { var f = API.format(e); if (f.is) { topics = f.res(); } });
    } else {
        deferred[1] = $.Deferred();
        deferred[1].resolve();
    }

    //有登入
    ts.onLogin(function (member) {
        myself = member;
        API.when(deferred).then(function (a) {
            config = API.format(a[0][0]).get();
            // console.log(config);
            if (!topics) { console.log(topics); return generalFail(); }
            init();
        });
    });

    if (myself.is_leader == 0) {
        ts.q('.LeaderBtn').hide();
    } else {
        ts.q('.LeaderBtn').show();
    }

    //初始
    function init() {
        //整理資料
        if (!topics.thead) {
            var thead = { 'leader': [], 'normal': [] };
            for (var k in topics) {
                var loc = topics[k],
                    tt = {};
                for (var i in loc) {
                    var ob = loc[i];
                    if (!tt[ob.type]) {
                        tt[ob.type] = [];
                        thead[k].push({ type: ob.type, name: ob.type_name });
                    }
                    tt[ob.type].push(Number(i));
                    delete ob.type;
                    delete ob.type_name;
                }
                for (var kk in thead[k]) { thead[k][kk].item = tt[thead[k][kk].type]; }
            }
            topics.thead = thead;
            API.cache(TOPIC_KEY, topics);
        }
        launchByProcessing(config['processing']);
    }

    //彈出 modal
    function initModal() {
        ts.modal.word = buildModal($('#ReportWordPersonal')[0], {
            'staff_is_leader': true,
            'staff_is_ceo': false,
            'opinionFeedback': { upper_comment: { 1: {}, 2: {}, 3: {}, 4: {} }, question: { 'question_1': [], 'question_2': [], 'question_4': [], 'question_5': [] } },
            'upperCommentForm': [
                { key: 1, title: "運維中心" },
                { key: 2, title: "部層級" },
                { key: 3, title: "處層級" },
                { key: 4, title: "組層級" },
            ]
        });

        ts.modal.reject = buildModal($('#ReJectModalPersonal')[0], {
            'rejectReason': '',
            'assessment_id': 1
        });

        ts.modal.history = buildModal($('#HistoryModalPersonal')[0], {
            'historyRecords': []
        });

        $('.modal').modal();
        $('.tabs').tabs();
    }
    //
    function buildModal(el, data) {
        // console.log(data);
        return new Vue({
            el: el,
            data: data,
            methods: {
                setData: function (data) { for (var i in data) { this._data[i] = data[i]; }; },
                rejectReport: function (id, reason) {
                    generalRejectReport(id, reason);
                    $(this.$el).modal('close');
                }
            }
        });
    }

    //頭上主管詳細資訊
    function initLeaderDetail(reports) {
        var meta = { info: { total: 0, finished: 0, overme: 0 }, team: {} },
            mylv = myself['_department_lv'];

        for (var i in reports) {
            var loc = reports[i];
            if (loc['staff_id'] == myself['id']) { meta.info.department = loc.department_code + loc.department_name; }
            meta.info.total++;
            if (loc['processing_lv'] == 0) { meta.info.finished++; }
            if (!meta.team[loc.department_code]) { meta.team[loc.department_code] = { name: loc.department_code + loc.department_name, overme: 0, total: 0, sub: {} }; }
            meta.team[loc.department_code].total++;

            var _AEJinMyLv = loc['assessment_evaluating_json'][mylv];
            if (_AEJinMyLv) {
                var leader_idx = _AEJinMyLv['leaders'].indexOf(myself['id']);
                if (leader_idx >= 0) {
                    var commited = _AEJinMyLv['commited'][leader_idx];
                    overme(commited, loc);
                    continue;
                }
            }
            overme(loc['processing_lv'] < mylv, loc);
        }

        function overme(isgood, report) {
            if (isgood) {
                meta.info.overme++;
                meta.team[report.department_code].overme++;
            } else {
                meta.team[report.department_code].sub[report.id] = report;
            }
        }
        ts.leaderDetail = new Vue({
            el: '#YearLeaderDetail',
            data: meta,
            methods: {
                totalNoCommit: function (team) {
                    var tmp = [];
                    for (var i in team) { tmp.push(team[i]['staff_name_en'] + ' / ' + team[i]['staff_name']); }
                    return tmp.length > 0 ? ('未提交人員：\r\n' + (tmp.join(' \r\n'))) : '完成';
                }
            }
        });

        // API.hook('saveYearlyAssessment',updateOverme);
        API.hook('commitYearlyAssessment', updateOverme);

        function updateOverme(a) {
            this.then(function (e) {
                var f = API.format(e);
                if (!f.is) { return; }
                var id = a.assessment_id;
                var data = f.get();
                var department_id = ts.evaluations[id].main['department_code'];
                if (!department_id) { return this; }
                if (data.processing_lv == 0) { meta.info.finished++; }
                meta.info.overme++;
                meta.team[department_id].overme++;
                delete meta.team[department_id].sub[id];
                if (meta.info.overme >= meta.info.total) {
                    API.reload();
                }
            });
        }
    }

    //共用  退回報表
    function generalRejectReport(id, reason) {
        var api = API.rejectYearlyAssessment({ assessment_id: id, reason: reason });
        api.then(function (e) {
            var result = API.format(e);
            if (result.is) {
                swal('退回成功', '已為您退回考評單', 'success');
                ts.evaluations[id].shutdown();
            } else {
                swal('退回失敗', result.get(), 'error');
            }
        });
        return api;
    }

    //依照年進度 啟動
    function launchByProcessing(p) {
        if (myself['is_leader'] == 1) { y_codition.mode = 'leader'; } else { ts.q('#YearProcessingBar').remove(); }
        switch (p) {
            case 5:
            case 6: //個人單
                ts.q('#YearProcessingBar').q('.inactive').eq(0).addClass('active').removeClass('inactive');
                API.getYearlyAssessment(y_codition).then(function (e) {
                    var report_data = API.format(e).res();
                    if (report_data.length == 0) { return generalFail(); }
                    for (var i in report_data) {
                        var loc = report_data[i];
                        // if(report_data[i].division_name == report_data[i].department_name ){
                        //       report_data[i].department_name=''; //部門主管沒有處組，直接留空值
                        // }
                        if (loc['_authority'] && loc['_authority'].edit) { ts.evaluations[loc.id] = callVueRenderReport(loc, TEMPLATE_REPORT); }
                    }
                    initModal();
                    if (myself['is_leader'] == 1) { initLeaderDetail(report_data); }

                }).fail(generalFail);
                break;
            case 7: //部門單
                ts.q('#YearProcessingBar').q('.stepper-step').eq(1).addClass('active').removeClass('inactive');
                ts.q('#YearLeaderDetail').remove();
                API.getYearlyDivisionZone(y_codition).then(function (e) {
                    var report_data = API.format(e).res();
                    if (report_data.length == 0) { return generalFail(); }
                    division_data = report_data;
                    for (var i in report_data) {
                        var loc = report_data[i];
                        // if(loc['_authority'] && loc['_authority'].edit){ ts.evaluations[loc.id] = callVueRenderReport(loc, TEMPLATE_REPORT); }
                        //
                        loc = adjDivisionData(loc);
                        if (loc.processing == 5) { continue; }
                        ts.DivisionZone[loc.id] = callVueRenderDivisionZone(loc);
                    }
                    initModal();

                }).fail(generalFail);
                break;
            default:
                generalFail();
        }
    }
    //共用  資料來源無
    function generalFail() {
        ts.q('#NoData').show();
        ts.q('#YearProcessingBar').hide();
    }
    // 前端資料初始化
    var aj_name_map = { 'under': '部屬', 'self': '自評', '1': '運維評核', '2': '部門評核', '3': '處評核', '4': '組評核' };
    var uc_name_map = { '1': '運維主管評語：', '2': '部門主管評語：', '3': '處主管評語：', '4': '組長評語：' };

    function adjReportData(data) {
        var can_fix_lv = parseInt(myself['_department_lv']) + (myself['is_leader'] == 1 ? 0 : 1);
        var my_edit_lv_alloweds = getAllowedEditLvevls();

        function getAllowedEditLvevls() {
            var _my_id = myself['id'];
            var _pll = data.path_lv_leaders;
            var _pl = data.path_lv;
            var resultary = [];
            for (var _lv in _pl) {
                var _leaders = _pll[_lv];
                var _manager = _pl[_lv][1];
                if (_manager == _my_id) {
                    resultary.push(_lv);
                } else if (_leaders && _leaders.includes(_my_id)) {
                    resultary.push(_lv);
                }
            }
            return resultary;
        }


        for (var pd in data.assessment_json) {
            var aj = data.assessment_json[pd];

            aj.name = aj_name_map[pd];
            if (pd == 'under') { continue; }

            aj.view = pd == 'self' ? true : parseInt(pd) >= can_fix_lv;
            aj.edit = data['_authority'].edit && (pd == 'self' ? myself['id'] == data['staff_id'] : my_edit_lv_alloweds.includes(pd));
            // 如果還能編輯又有多主管 取assessment_evaluating_json
            if (aj.edit && data.assessment_evaluating_json && data.assessment_evaluating_json[pd]) {
                var aej = data.assessment_evaluating_json[pd];
                var my_idx = aej.leaders.indexOf(myself['id']);
                if (my_idx >= 0) {
                    var score = aej.scores[my_idx];
                    var total = aej.totallist[my_idx];
                    aj.score = score;
                    aj.total = total;
                }
            }

            for (var s in aj.score) {
                if (aj.score[s] < 0) { aj.score[s] = 0; }
            }

            aj.preAbove = aj.edit && (aj.total == 0);
        }
        for (var u in data.upper_comment) {
            var uc = data.upper_comment[u];
            var intu = parseInt(u);
            uc.name = uc_name_map[u] || '';
            uc.view = intu >= can_fix_lv;
            uc.edit = intu == can_fix_lv && data['_authority'].edit_comment;
            if (Array.isArray(uc.staff_id)) {
                uc.my_edit_idx = uc.edit ? uc.staff_id.indexOf(myself['id']) : -1;
            } else {
                uc.my_edit_idx = 0;
                us.staff_id = [us.staff_id];
                us.content = [us.content];
            }

        }
        // console.log('adjReportData: ', JSON.parse(JSON.stringify(data)));
        return data;
    }
    var level_map = {},
        origin_fix_map = {};

    function adjDivisionData(report) {

        if (report.division == DEPARTMENT_CENTER) { report = collectCenter(report); }

        for (var i in report._distribution) {
            level_map[report._distribution[i].name] = i;
            report._distribution[i].focus = false;
        }
        for (var i in report._reports) {
            origin_fix_map[report._reports[i].id] = report._reports[i].total;
            report._reports[i].show = true;
            if (report.processing == 5) { report._reports[i].done = true; }
        }
        return report;
    }
    //個人報表 產生 Vue
    function callVueRenderReport(data, template) {
        data = adjReportData(data);
        var personal, underStaff, division;
        if (myself.id == data.staff_id) {
            personal = 1;
        } else {
            underStaff = 1;
        }
        var rand = 'row-assessment-' + data.id,
            topic, topic_map = {},
            thead;
        $YearEvaluationForm.append('<div id="' + rand + '" ></div>');
        template.q('.card-body').text().length == 0 && template.q('.card-body').append(TEMPLATE_HTML_CARD_BODY);

        if (data.staff_is_leader) {
            topic = topics.leader;
            thead = topics.thead.leader;
        } else {
            topic = topics.normal;
            thead = topics.thead.normal;
        }
        for (var t in topic) {
            var loc = topic[t];
            topic_map[loc.id] = loc;
        }
        // console.log(template);
        // create vue
        var vm = new Vue({
            template: template.html(),
            el: '#' + rand,
            data: {
                currUser: myself,
                processing: config.processing,
                currentYear: current.year,
                main: data,
                personal: personal,
                underStaff: underStaff,
                division: 0,
                lv_sort: ['self', 4, 3, 2, 1],
                topic: topic,
                topic_map: topic_map,
                topicHead: thead,
                historyRecords: {},
                feedback: {},
                rejectReason: '',
                opinionFeedback: { upper_comment: { 1: {}, 2: {}, 3: {}, 4: {} }, question: { 'question_1': [], 'question_2': [], 'question_4': [], 'question_5': [] } },
                qb1Length: 0,
                qb2Length: 0,
                qbLengthMax: 500
            },
            // beforeUpdate(){
            //     var st = new Date().getTime();
            //     this.st = st;
            // },
            // updated() {
            //     var spt = new Date().getTime() - this.st;
            //     console.log('on updated vue: ', spt);
            // },
            methods: {
                checkLength(event, key) {
                    console.log(event);
                    let curr = event.target
                        length = curr.textLength;
                    vm[key] = length;
                },
                saveReport(id) {
                    // self=自己, 4=組, 3=處, 2=部, 1=中心
                    var vm = this;
                    return new Promise(function (resolve, reject) {
                        var data = {
                            assessment_id: id,
                            assessment_json: {},
                            comment: {}
                        };
                        let go = true;
                        for (var i in vm.main.assessment_json) {
                            var loc = vm.main.assessment_json[i];
                            if (!loc.edit) { continue; }
                            data.assessment_json[i] = loc.score;
                        }
                        for (var i in vm.main.upper_comment) {
                            var loc = vm.main.upper_comment[i];
                            if (!loc.edit) { continue; }
                            var my_content = loc.content[loc.my_edit_idx];
                            data.comment[i] = String(my_content);
                        }
                        // 自評儲存
                        if (vm.main.staff_id == vm.main.owner_staff_id) {
                            data['self_contribution'] = vm.main.self_contribution;
                            data['self_improve'] = vm.main.self_improve;
                        } else {
                            // console.log(data);
                            let res = data.assessment_json;
                            for (var i in res) {
                                let curr = res[i],
                                    vals = Object.values(curr);
                                const reducer = (accumulator, currentValue) => accumulator + currentValue;

                                let total = vals.reduce(reducer);

                                if (total == 0 && vm.main["_should_count"]) { go = false }
                            }

                        }
                        if (go) {
                            data["should_count"] = vm.main["_should_count"] ? 1 : 2;
                            API.saveYearlyAssessment(data).then(function (e) {
                                var result = API.format(e);
                                if (result.is || result.get() == "Nothing Changed.") {
                                    Materialize.toast('已為您儲存當前資料', 2000);
                                    resolve('ok');
                                } else {
                                    swal('儲存失敗', result.get(), 'error');
                                }
                            });
                        } else { 
                            swal('無法儲存', "總分必須大於 0", 'error');
                        }
                    });
                },
                copyData: function (value, e) {
                    // $(e.target).closest('tr').q('select').eq(0).focus();
                    var $e = $(e.target).closest('.copy-group');
                    // if($e.hasClass('active')){
                    $e.q('.copy-button-div').hide();
                    // }
                    var aj = this.main.assessment_json;
                    var prev = !aj[value + 1] ? 'self' : value + 1;
                    for (var i in aj[prev]['score']) {
                        aj[value]['score'][i] = aj[prev]['score'][i]
                    }
                    aj[value]['total'] = aj[prev]['total'];
                },
                commitReport: function (id) {
                    var vm = this;
                    if (vm.main.self_contribution.length < 2 || vm.main.self_improve.length < 2) {
                        return swal('資料錯誤', '問答題( 主要貢獻, 加強改進之處 )不能為空', 'error');
                    }
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
                        function () {
                            vm.saveReport(id).then(function (e) {
                                API.commitYearlyAssessment({ assessment_id: id }).then(function (e) {
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
                rejectReport: function (id) {
                    var vm = this;
                    // console.log(id);
                    ts.modal.reject.setData({ 'rejectReason': '', 'assessment_id': id });
                    // console.log(ts.modal.reject);
                    $(ts.modal.reject.$el).modal('open');
                },
                totalScore: function (scoreData) {
                    var total = 0;
                    for (var score in scoreData) {
                        total += parseInt(scoreData[score]);
                    }
                    // console.log(scoreData);
                    // console.log(total);
                    return total;
                },
                getFeedbackDetail: function () {
                    var vm = this;
                    API.getFeedbackDetailByStaff({ year: this.currentYear, staff_id: this.main.staff_id }).then(function (e) {
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
                getRecords: function (id) {
                    var vm = this;
                    API.getYearlyHistoryRecord({ assessment_id: id }).then(function (e) {
                        var result = API.format(e);
                        if (result.is) {
                            var record = result.res();
                            for (var r in record) {
                                var separateDate = record[r].date.split(" ");
                                record[r]['date'] = separateDate[0];
                                record[r]["time"] = separateDate[1];
                            }
                            vm.historyRecords = record;
                            ts.modal.history.setData({ 'historyRecords': record });
                            $(ts.modal.history.$el).modal('open');
                        }
                    });
                },
                getReportWord: function (id) {
                    var vm = this;
                    API.getYearlyAllReportWord({ assessment_id: id }).then(function (e) {
                        var result = API.format(e);
                        if (result.is) {
                            vm.opinionFeedback = result.get();
                            if (vm.opinionFeedback.question) {
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
                            }
                            ts.modal.word.setData({ 'staff_is_leader': vm.main['staff_is_leader'], 'staff_is_ceo': vm.main['staff_is_leader'] && vm.main['division_id'] == 1, 'opinionFeedback': vm.opinionFeedback });

                            // console.log(vm.opinionFeedback);
                            $(ts.modal.word.$el).modal('open');
                            var timer = setTimeout(function () {
                                $(ts.modal.word.$el).q('.tab a').eq(0).click();
                                // $(ts.modal.word.$el).q('.tabs').tabs();
                            }, 100);
                        }
                    });
                },
                closeReport: function () {
                    $(this.$el).q('.rv-assess').removeClass('moveleft');
                },
                openReport: function () {
                    $(this.$el).q('.rv-assess').addClass('moveleft');
                },
                updateMainData: function (data) {
                    this.main = data;
                    // console.log(this);
                    // for(var i in data){
                    // if(typeof this.main[i]!='undefined'){
                    // this[i] = data[i];
                    // }
                    // }
                },
                shutdown: function () {
                    $(this.$el).remove();
                }
            },
            created: function () {
                this.getFeedbackDetail();
            },
            mounted: function () {
                var ele = this.$el;
                ts.q(ele).q('.collapsible').collapsible();
                ts.q(ele).q('textarea').attr('maxlength', 2000);
            }
        });
        return vm;
    }
    //部門單  產生 Vue
    function callVueRenderDivisionZone(report) {
        var rand = 'row-division-' + (report.id);
        $YearEvaluationForm.append('<div id="' + rand + '" ></div>');
        TEMPLATE_DIVISION.q('.card-body').text().length == 0 && TEMPLATE_DIVISION.q('.card-body').append(TEMPLATE_HTML_CARD_BODY);


        // console.log(report)

        return new Vue({
            template: TEMPLATE_DIVISION.html(),
            el: '#' + rand,
            data: {
                member: myself,
                currentYear: current.year,
                processing: config.processing,
                divisionData: report,
                topics: topics,
                level_map: level_map,
                origin_fix_map: origin_fix_map,
                report_sort: { 'department_code': 1, 'staff_name': 1, 'staff_post': 1, '_status': 1, 'assessment_total': 1, 'assessment_total_division_change': 1, 'assessment_total_ceo_change': 1, 'total': 1 },
                report_sort_now: 'department_code',
                canSubmit: true,
            },
            methods: {
                plusScore: function (v, m) {
                    m = m == 1 ? 'assessment_total_division_change' : 'assessment_total_ceo_change';
                    if (isNaN(v[m])) { v[m] = 0; } else { v[m]++; }
                    this.countTotal(v);
                },
                minusScore: function (v, m) {
                    m = m == 1 ? 'assessment_total_division_change' : 'assessment_total_ceo_change';
                    if (isNaN(v[m])) { v[m] = 0; } else { v[m]--; }
                    this.countTotal(v);
                },
                countError: function (msg) {
                    this.canSubmit = false;
                    swal('分數錯誤', msg, 'error');
                },
                countTotal: function (v) {
                    if (v && !isNaN(v.total)) {
                        v.assessment_total_ceo_change = parseInt(v.assessment_total_ceo_change);
                        v.assessment_total_division_change = parseInt(v.assessment_total_division_change);
                        if (isNaN(v.assessment_total_ceo_change) || isNaN(v.assessment_total_division_change)) { return this.countError('分數必須為數字'); }
                        v.total = v.assessment_total + v.assessment_total_ceo_change + v.assessment_total_division_change;
                        if (v.total > 100) { v.total = 100; return this.countError('總分不能大於100'); }
                        if (v.total < 0) { v.total = 0; return this.countError('總分不能小於0'); }
                        this.canSubmit = true;
                    }

                    // var t1 = new Date().getTime();
                    for (var i in this.divisionData._distribution) {
                        var loc = this.divisionData._distribution[i];
                        var count = 0;
                        for (var r in this.divisionData._reports) {
                            var re = this.divisionData._reports[r];
                            if (re.total >= loc.score_least && re.total <= loc.score_limit) {
                                count++;
                                re.level = loc.name;
                            }
                        }
                        loc.count = count;
                    }
                    // var t2 = new Date().getTime();
                    // console.log(t2-t1);
                },
                rateLimit: function (r) {
                    return Math.ceil(this.divisionData._reports.length * (r.rate_limit / 100));
                },
                rateLeast: function (r) {
                    return Math.floor(this.divisionData._reports.length * (r.rate_least / 100));
                },
                totalOverByKey: function (type) {
                    // var total = this.divisionData._reports.length;
                    var count = 0;
                    for (var i in this.divisionData._distribution) {
                        var loc = this.divisionData._distribution[i];
                        var over = (type == 1) ? this.rateLimit(loc) : this.rateLeast(loc);
                        if (type == 1) {
                            count += Math.max(loc.count - over, 0);
                        } else {
                            count += Math.max(over - loc.count, 0);
                        }
                    }
                    return count;
                },
                saveDivisionZone: function (id) {
                    var vm = this;
                    if (!vm.canSubmit) { return swal('錯誤', '分數尚未修正不能儲存', 'error'); }
                    return new Promise(function (resolve, reject) {
                        var data = { division_id: id, assessment_change: {} },
                            data_tmp = { has: false, totals: {} };
                        for (var ddr in vm.divisionData._reports) {
                            var key = (vm.divisionData._canfix_ceo) ? 'assessment_total_ceo_change' : 'assessment_total_division_change';
                            var loc = vm.divisionData._reports[ddr];
                            if (vm.origin_fix_map[loc.id] == loc.total) { continue; }
                            data['assessment_change'][loc.id] = loc[key];
                            data_tmp.has = true;
                            data_tmp.totals[loc.id] = loc.total;
                        }
                        if (data_tmp.has) {
                            API.setFinallyScoreFix(data).then(function (e) {
                                var result = API.format(e);
                                if (result.is) {
                                    swal('儲存成功', '已為您儲存數據', 'success');
                                    for (var i in data_tmp.totals) {
                                        vm.origin_fix_map[i] = data_tmp.totals[i];
                                    }
                                    resolve('ok');
                                } else {
                                    swal('儲存失敗', result.get(), 'error');
                                }
                            });
                        } else {
                            Materialize.toast('資料未變更', 2000);
                            resolve('ok');
                        }
                    });
                },
                commitDivisionZone: function (id) {
                    var vm = this;
                    this.saveDivisionZone(id).then(function (e) {
                        API.commitDivisionZone({ division_id: id }).then(function (e) {
                            var result = API.format(e);
                            if (result.is) {
                                swal('送出成功', '已為您送出數據', 'success');
                                vm.reRenderThisReport();
                            } else {
                                swal('儲存失敗', result.get(), 'error');
                            }
                        });
                    });
                },
                rejectDivisionZone: function (id) {
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
                        function () {
                            API.rejectDivisionZone({ division_id: id }).then(function (e) {
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
                downloadExcelDivisionZone: function (id) {
                    API.exportYearlyAssessmentExcel({ year: this.currentYear, division_id: id }).then(function (e) {
                        var result = API.format(e);
                        if (result.is) {
                            swal('下載成功', '已為您下載部門考評單', 'success');
                        } else {
                            swal('下載失敗', result.get(), 'error');
                        }
                    });
                },
                reRenderThisReport: function () {
                    var vm = this;
                    $(vm.$el).q('.collapsible-header.active').trigger('click.collapse');
                    API.getYearlyDivisionZone({ year: vm.currentYear }).then(function (e) {
                        var result = API.format(e);
                        // console.log(result);
                        if (result.is) {
                            var pk = result.res();
                            refreshDivision(pk);
                        } else {
                            refreshDivision([]);
                        }
                    });
                },
                personalReport: function (id) {

                    API.getYearlyAssessment({ 'year': this.currentYear, 'assessment_id': id }).then(function (e) {
                        var data = API.format(e).get();
                        console.log(data);
                        if (!ts.personal.$el) { ts.personal = callVueRenderReport(data, TEMPLATE_SIDENAV); } else { ts.personal.updateMainData(adjReportData(data)); }
                        ts.personal.openReport();
                    });

                },
                totalScore: function (scoreData) {
                    var total = 0;
                    for (var score in scoreData) {
                        total += parseInt(scoreData[score]);
                    }
                    return total;
                },
                sortReportBy: function (key) {
                    var vm = this;
                    if (isNaN(vm.report_sort[key])) { return console.log('not found sort key : ' + key); }
                    vm.report_sort[key] = (vm.report_sort[key] + 1) % 2;
                    vm.report_sort_now = key;
                    var asc = vm.report_sort[key] == 1;
                    return vm.divisionData._reports.sort(function (a, b) {
                        var ai = isNaN(a[key]) ? a[key].charCodeAt(0) : a[key];
                        var bi = isNaN(b[key]) ? b[key].charCodeAt(0) : b[key];
                        return asc ? ai - bi : bi - ai;
                    });
                },
                focusLevel: function (dlv, e) {
                    var vm = this,
                        name = (dlv || {}).name,
                        all = !name || name == vm.cfn;
                    vm.cfn = all ? '' : name;
                    vm.divisionData._distribution.forEach(function (i) {
                        i.focus = all ? false : i.name == name;
                    });

                    if (!vm.levelObj) {
                        vm.levelObj = {
                            timer: 0,
                            n: 0,
                            fn: function () {
                                var lt = this;
                                lt.timer && clearTimeout(lt.timer);
                                while (lt.n > 0) {
                                    lt.n--;
                                    var i = vm.divisionData._reports[lt.n];
                                    var b = (i.level == lt.name || lt.all);
                                    if (b != i.show) {
                                        i.show = b;
                                        if (!b) { continue; }
                                        lt.timer = setTimeout(function () { lt.fn(); }, 30);
                                        break;
                                    }
                                }
                            },
                            start: function (na, al) {
                                this.n = vm.divisionData._reports.length;
                                this.name = na;
                                this.all = al;
                                this.fn();
                            }
                        }
                    };
                    vm.levelObj.start(name, all);

                }
            },
            mounted: function () {
                frameworkInit(ts.q(this.$el), this);
                if (this.divisionData._reports.length > 5) {
                    ts.q(this.$el).q('#division-table-header').css('width', '99.5%');
                    // console.log(ts.q('#division-table-header'));
                }
            },
        });

        //
        function frameworkInit($selector, vueo) {
            // $selector.q('.modal').modal();
            $selector.q('.collapsible').collapsible({
                onOpen: function (el) {
                    $subli = $selector.siblings().q('.collapsible-header.active').trigger('click.collapse');
                    vueo.countTotal();
                    if (!vueo.tableY) { vueo.tableY = $(vueo.$el).q('table').TableScrollbarY({}); }
                }
            });
        }

    }

    //運維中心 要收集滿滿
    function collectCenter(center_data) {
        var center_commit = center_data._authority.commit;
        for (var i in division_data) {
            var loc = division_data[i];
            if (loc == center_data) { continue; }
            if (loc.processing < 3) { center_commit = false; continue; }
            var report = loc._reports;
            var distri = loc._distribution;
            for (var r in report) {
                center_data._reports.push(report[r]);
            }
            for (var d in distri) {
                center_data._distribution[d].count += distri[d].count;
            }
            loc.noShow = false;
        }
        center_data._authority.commit = center_commit;
        if (center_data._reports.length == 0 || !center_commit) {
            center_data.noShow = true;
            //不能核准的話 把其他的單位鎖住
            for (var i in division_data) {
                loc = division_data[i];
                if (loc.processing >= 3) {
                    loc._authority.edit = false;
                    loc._authority.commit = false;
                }
            }
        } else {
            //可以執行長
            for (var i in division_data) {
                loc = division_data[i];
                if (loc != center_data) { loc.noShow = true; }
            }
            //執行長評核改變燈號
            ts.q('#YearProcessingBar').q('.stepper-step').addClass('inactive').removeClass('active')
                .eq(2).addClass('active').removeClass('inactive');

        }
        return center_data;
    }
    //重新整理部門單
    function refreshDivision(data) {
        division_data = data;
        var map = {};
        for (var i in data) {
            var loc = data[i];
            loc = adjDivisionData(loc);
            map[loc.id] = loc;
        }
        for (var id in ts.DivisionZone) {
            var dv = ts.DivisionZone[id];
            var newData = map[id];
            if (newData) {
                dv.divisionData = newData;
            } else {
                dv.divisionData.noShow = true;
                $(dv.$el).remove();
            }
        }
    }

});