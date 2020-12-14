var $yearOpinionOverview = $('#Year-OpinionOverview').generalController(function() {
    var ts = this;
    var current = $.ym.get();
    var YearSelect = ts.q("#getYear").empty();
    YearSelect.yearSet();

    ts.onLogin(function(member) {
        var thisYear = new Date().getFullYear();
        var yearList = [];
        for (i = thisYear; i >= API.create.year; i--) {
            yearList.push(i);
        }

        var vm = new Vue({
            el: '.rv-opinion',
            data: {
                year: current.year,
                currentYear: current.year,
                questions: {},
                currYearlyConfig: {},
            },
            methods: {
                getYearlyConfig() {
                    var vm = this;
                    API.getYearlyConfig({ year: vm.year }).then(function(e) {
                        var result = API.format(e);
                        if (result.is) {
                            let res = result.res();
                            vm.currYearlyConfig = res;
                            if (res.processing >= 3) {
                                vm.initQuestions();
                            }
                        } else {
                            console.log('no data');
                        }
                    });
                },
                checkStar: function(id, action) {
                    var vm = this;
                    API.lightQuestion({ id: id, highlight: action }).then(function(e) {
                        var result = API.format(e);
                        if (result.is) {
                            vm.initQuestions();
                            if (action) {
                                return Materialize.toast('標記為重要意見', 4000);
                            }
                            return Materialize.toast('取消標記', 4000);
                        }
                    });
                },
                initQuestions: function() {
                    var vm = this;
                    vm.questions = {};
                    current.year = vm.year;
                    // console.log(vm.year)
                    $.ym.save();
                    API.getCompanyQuestions({ year: vm.year }).then(function(e) {
                        var result = API.format(e);
                        if (result.is) {
                            vm.questions = result.res();
                        } else {
                            console.log('no data');
                        }
                    });
                },
                downloadYearlyQuestion() {
                    if (vm.currYearlyConfig.processing >= 3) {
                        API.downloadYearlyQuestion({ year: this.year }).then(function(e) {
                            var result = API.format(e);
                            if (result.is) {
                                swal('開始下載', '正在為您下載評論', 'success');
                            } else {
                                swal('下載失敗', result.get(), 'success');
                            }
                        });
                    }
                }
            },
            created() {
                var vm = this;
                vm.getYearlyConfig();
            }
        });
    });
});