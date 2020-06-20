var $ManagementFeedbackCheck = $('#Management-FeedbackCheck').generalController(function() {
    var ts = this;
    var current = $.ym.get();
    var YearSelect = ts.q("#getYear");
    YearSelect.yearSet();

    ts.onLogin(function(member) {
        var vm = new Vue({
            el: '.rv-feeback-check',
            data: {
                currentYear: current.year,
                alleader: {},
                config: {}
            },
            computed: {
                isNotFinished: function() {
                    return parseInt(this.config.processing || 0) < 8;
                }
            },
            methods: {
                init: function() {
                    var vm = this;
                    current.year = vm.currentYear;
                    $.ym.save();

                    API.getYearlyConfig({ year: this.currentYear }).then(function(e) {
                        var result = API.format(e);
                        if (result.is) {
                            vm.config = result.get();
                        }
                    });
                    vm.alleader = {};
                    API.getAllLeader().then(function(e) {
                        var cnt = API.format(e);
                        if (cnt.is) {
                            var apiLeader = cnt.res();
                            if (member.id == '2') {
                                apiLeader.splice(1, 0, member);
                            }

                            API.getYearlyOtherLeaderSuggestions({ year: vm.currentYear }).then(function(e) {
                                var result = API.format(e);
                                if (result.is) {
                                    var dataLeader = result.res();
                                    for (var d in dataLeader) {
                                        dataLeader[d] = dataLeader[d]['questions']
                                        for (var dd in dataLeader[d]) {
                                            dataLeader[d][dd]['target_id'] = 1;
                                        }
                                    }
                                    for (var leader in apiLeader) {
                                        apiLeader[leader]['questions'] = dataLeader[apiLeader[leader].id];
                                    }
                                    vm.alleader = apiLeader;
                                }
                            });

                        }
                    });
                },
                moveGoGo: function(qid, targetId) {
                    var vm = this;
                    API.moveYearlyQuestionToLeader({ question_id: qid, target_staff_id: targetId, year: vm.currentYear }).then(function(e) {
                        var result = API.format(e);
                        if (result.is) {
                            swal('轉移成功', '已為您轉移數據', 'success');
                            vm.init();
                        } else {
                            swal('轉移失敗', result.get(), 'error');
                        }
                    });
                }
            },
            created: function() {
                var vm = this;
                this.init();
            },
            watch: {
                currentYear: function(val) {
                    this.currentYear = val;
                    this.init();
                }
            },
        });
    });
});