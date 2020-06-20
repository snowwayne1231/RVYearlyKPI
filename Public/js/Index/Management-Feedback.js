var $managementFeedback = $('#Management-Feedback').generalController(function() {
    var ts = this;
    var current = $.ym.get();
    var YearSelect = ts.q('#getYear');
    var staffObj = {};

    YearSelect.yearSet();

    ts.onLogin(function(member) {
        var today = new Date(member.now);
        var thisYear = new Date().getFullYear();
        var yearList = [];

        for (i = thisYear; i >= API.create.year; i--) {
            yearList.push(i);
        }

        var vm = new Vue({
            el: '.rv-feedback',
            data: {
                year: yearList,
                currentYear: current.year,
                managementFbk: {},
                managementFbkChoices: [],
                targetName: '',
                viewList: 1,
                viewCard: 0, //  卡片顯示方式暫關閉
            },
            methods: {
                init: function() {
                    var vm = this;
                    vm.managementFbk = {};
                    current.year = vm.currentYear;
                    $.ym.save();
                    API.getYearlyFeedbackList({ year: vm.currentYear }).then(function(e) {
                        var result = API.format(e);
                        if (result.is) {
                            ts.q('#NoData').hide();
                            ts.q('#fbData').show();
                            ts.q('.fbData  thead').show();
                            ts.q('.view-style').show();
                            var staffArray = [];
                            var staffName = {};
                            ts.staff_account_map = {};
                            var data = result.res();
                            vm.managementFbk = data.list;
                            vm.managementFbkChoices = data.choice;
                            var list = result.res();
                            // console.log(JSON.parse(JSON.stringify(vm.managementFbk)))
                        } else {
                            ts.q('#fbData').hide();
                            ts.q('.fbData thead').hide();
                            ts.q('#NoData').show();
                            ts.q('.view-style').hide();
                            console.log(JSON.parse(JSON.stringify(vm.managementFbk)))
                            console.log('no data');
                        }
                    });
                },
                changeList: function() {
                    var vv = this;
                    vv.viewList = 1;
                    vv.viewCard = 0;
                },
                changeCard: function() {
                    var vv = this;
                    vv.viewList = 0;
                    vv.viewCard = 1;
                },
                downloadYearlyFeedback: function() {
                    API.downloadYearlyFeedback({ year: this.currentYear }).then(function(e) {
                        var result = API.format(e);
                        if (result.is) {
                            swal('開始下載', '開始為您下載部屬回饋Excel', 'success');
                        } else {
                            swal('下載失敗', result.get(), 'error');
                        }
                    });
                }
            },
            watch: {
                currentYear: function(val) {
                    this.currentYear = val;
                }
            },
            created: function() {
                this.init();
            },
            mounted: function() {
                ts.q(this.$el).q('.dropdown').dropdown();
            }
        });
    });
});