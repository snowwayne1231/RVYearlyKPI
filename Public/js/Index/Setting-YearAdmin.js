var $settingAdmin = $('#SettingAdminYearly').generalController(function () {
    var ts = this;
    ts['topics'] = {};
    ts['staffMap'] = {};

    var start = 0,
        end = 0;

    ts.onLogin(function (member) {
        let vm,
            myself = member;
        var current = $.ym.get();

        var year = ts.q("#getYear").empty();
        year.yearSet();

        var getData = function (year, callback, obj) {

            API.getYearlyAssessmentScoreDetailByAdmin({ year: year }).then(function (e) {
                let res = API.format(e);
                if (res.is) {
                    let data = res.res(),
                        reports = data.reports;
                    obj['staffMap'] = data.staff_map;
                    obj['topics']['tbody'] = processData(reports);
                    if (callback) {
                        callback();
                    }

                } else {
                    console.log(res.res);
                }

            });
        }


        var processData = function (data) {
            let newData = {
                leader: [],
                normal: []
            }
            for (var i in data) {
                let curr = data[i],
                    key = 'leader';

                if (curr.staff_is_leader != 1) {
                    key = 'normal';
                }
                let evaluating = Object.keys(curr['assessment_evaluating_json']),
                    evaluatingLeaderCount = 0;
                if (evaluating.length != 0) {
                    for (var k in evaluating) {
                        let currEvaluating = evaluating[k];
                        curr.assessment_json[currEvaluating]['_f_hasEvluating'] = true;
                        curr.assessment_json[currEvaluating]['_f_evluating'] = curr['assessment_evaluating_json'][currEvaluating];
                        
                        Object.keys(curr.assessment_json[currEvaluating]).indexOf('_f_rowspanNum') == -1 && (curr.assessment_json[currEvaluating]['_f_rowspanNum'] = 1);
                        curr.assessment_json[currEvaluating]['_f_rowspanNum'] += curr['assessment_evaluating_json'][currEvaluating]['leaders'].length;

                        evaluatingLeaderCount += curr['assessment_evaluating_json'][currEvaluating]['leaders'].length;
                    }
                }
                curr['_f_rowspanNum'] = 1;
                curr['_f_rowspanNum'] += Object.keys(curr['assessment_json']).length;
                curr['_f_rowspanNum'] += evaluatingLeaderCount;
                newData[key].push(curr);
            }

            return newData;

        }

        var newVue = function () {
            vm = new Vue({
                el: ts.q('.rv-admin')[0],
                data: {
                    currUser: myself,
                    year: ts.q("#getYear").find("option:selected").val(),
                    filterDepartment: "",
                    filterStaff: "",
                    staffMap: ts.staffMap,
                    stepItemMap: {
                        under: { name: "部屬" },
                        self: { name: "自評" },
                        4: { name: "組評" },
                        3: { name: "處評" },
                        2: { name: "部評" },
                        1: { name: "決策" },
                    },
                    topics: ts.topics,
                    blockArea: [
                        { key: "leader", title: "LEADER" },
                        { key: "normal", title: "MEMBER" }
                    ],
                    reviewerTableTh: [
                        { title: "受評者" },
                        { title: "項目" },
                        { title: "評核主管" },
                    ]
                },
                computed: {
                    deps() {
                        var vm = this,
                            deps = [];
                        for (var i in vm.departments) {
                            let curr = vm.departments[i];
                            deps.push(curr.unit_id + ' ' + curr.name);
                        }
                        return deps;
                    },
                    titleObj() {
                        var vm = this,
                            titles = vm.topics.thead,
                            newObj = {};
                        for (var i in titles) {
                            let title = titles[i];
                            for (var k in title) {
                                let curr = title[k];
                                newObj[curr['id']] = curr['name'];
                            }
                        }
                        return newObj;
                    },
                    tbody() {
                        var vm = this;
                        return vm.topics['tbody'];
                    },
                    thead() {
                        var vm = this;
                        return vm.topics['thead'];
                    },
                },
                methods: {
                    getData() {
                        var vm = this;
                        getData(vm.year, false, vm);
                    },
                    filter(dep, name) {
                        var vm = this;
                        if (dep.toLowerCase().indexOf(vm.filterDepartment.toLowerCase()) != -1 && name.toLowerCase().indexOf(vm.filterStaff.toLowerCase()) != -1) {
                            return true;
                        } else {
                            return false;
                        }
                    },
                    change(events) {
                        var vm = this,
                            target = events.target,
                            value = target.value;
                        // if () {}
                        console.log(target, value);
                    },
                    getItemName(items, id) {
                        for (var i in items) {
                            let curr = items[i];
                            if (curr.id == id) {
                                return curr.name;
                            }
                        }
                    },
                    getStaff(report, lv) {
                        let leadersArr = report.path_lv[lv],
                            staff_id = !leadersArr ? 0 : leadersArr[1],
                            staff_name_en;
                        if (staff_id == 0) {
                            staff_name_en = "-";
                        } else {
                            staff_name_en = this.staffMap[staff_id].name_en;
                        }
                        return staff_name_en;
                    },
                    getStepItemRowsapn(report, lv) {
                        let leadersArr = report.path_lv_leaders[lv],
                            number = !leadersArr || leadersArr.length == 1 ? 1 : (leadersArr.length + 1);
                        return number;
                    },
                    getStaffRowsapn(report) {
                        let leadersArrs = report.assessment_evaluating_json.length != 0 ? Object.values(report.path_lv_leaders) : [],
                            number = Object.keys(report.assessment_json).length;
                        for (var i in leadersArrs) {
                            let curr = leadersArrs[i];
                            if (curr.length > 1) {
                                number += curr.length;
                            }
                        }
                        return number;
                    }
                },
                mounted() {
                    end = new Date().getTime();
                    console.log((end - start) / 1000 + "sec");
                },
            });
        }

        var TOPIC_KEY = "Year_Admin_TOPIC";
        ts.topics = API.cache(TOPIC_KEY);
        start = new Date().getTime();
        if (!ts.topics) {
            // Topics is undefined
            ts.topics = { thead: {}, tbody: {} };
            let y_codition = { year: undoYear || current.year };
            API.getYearlyTopic(y_codition).then(function (e) {
                var f = API.format(e);
                if (f.is) {
                    ts.topics.thead = f.res();
                    for (var key in ts.topics.thead) {
                        ts.topics.tbody[key] = [];
                    }
                    API.cache(TOPIC_KEY, ts.topics);
                    getData(current.year, newVue, ts);

                }
            });
        } else {
            getData(current.year, newVue, ts);
        }


    });




});