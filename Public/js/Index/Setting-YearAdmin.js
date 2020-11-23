var $settingAdmin = $('#SettingAdminYearly').generalController(function () {
    var ts = this,
        myself,
        topics,
        staffMap,
        department;

    ts.onLogin(function (member) {
        myself = member;
        var current = $.ym.get();

        var year = ts.q("#getYear").empty();
        year.yearSet();

        API.getAllDepartment().then(function (e) {
            var res = API.format(e);
            if (res.is) {
                department = res.res();
            }
        });

        var TOPIC_KEY = "Year_Admin_TOPIC";
        topics = API.cache(TOPIC_KEY);
        if (!topics) {
            // Topics is undefined
            topics = { thead: {}, tbody: {} };
            let y_codition = { year: undoYear || current.year };
            API.getYearlyTopic(y_codition).then(function (e) {
                var f = API.format(e);
                if (f.is) {
                    topics.thead = f.res();
                    console.log(topics);
                    for (var key in topics.thead) {
                        topics.tbody[key] = [];
                    }
                    API.cache(TOPIC_KEY, topics);
                    getData(current.year);

                }
            });
        } else {
            // got Topics
            // console.log(topics);
            getData(current.year);
        }
    });

    function getData(year) {
        API.getYearlyAssessmentScoreDetailByAdmin({ year: year }).then(function (e) {
            let res = API.format(e);
            if (res.is) {
                let data = res.res(),
                    reports = data.reports;
                staffMap = data.staff_map;
                for (var i in reports) {
                    let curr = reports[i],
                        key = 'leader';
                    if (curr.staff_is_leader != 1) {
                        key = 'normal';
                    }
                    topics['tbody'][key].push(curr);
                }
                newVue();
            } else {
                console.log(res.res);
            }
        });
    }

    function newVue() {
        var vm = new Vue({
            el: ts.q('.rv-admin')[0],
            data: {
                year: ts.q("#getYear").find("option:selected").val(),
                filterDepartment: "",
                filterStaff: "",
                departments: department,
                staffMap: staffMap,
                stepItemMap: {
                    under: { name: "部屬" },
                    self: { name: "自評" },
                    4: { name: "組評" },
                    3: { name: "處評" },
                    2: { name: "部評" },
                    1: { name: "決策" },
                },
                topics: topics,
                blockArea: [
                    { key: "leader", title: "LEADER" },
                    { key: "normal", title: "MEMBER" }
                ],
                reviewerTableTh: [
                    { title: "受評者" },
                    { title: "項目" },
                    { title: "評核主管" },
                ],
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
                }
            },
            methods: {
                getData() {
                    var vm = this;
                    API.getYearlyAssessmentScoreDetailByAdmin({ year: vm.year }).then(function (e) {
                        let res = API.format(e);
                        if (res.is) {
                            let data = res.res(),
                                reports = data.reports;
                            vm.staffMap = data.staff_map;
                            vm.topics['tbody'] = {leader: [], normal: []};
                            for (var i in reports) {
                                let curr = reports[i],
                                    key = 'leader';
                                if (curr.staff_is_leader != 1) {
                                    key = 'normal';
                                }
                                vm.topics['tbody'][key].push(curr);
                            }
                        } else {
                            console.log(res.res);
                        }
                    });
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
            }
        });
    }


});