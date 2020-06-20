var $Review = $('#Review').generalController(function() {
    var ts = this;
    var current = $.ym.get();
    var getYear = current.year;
    var getMonth = current.month;
    var year = ts.q("#getYear").empty();
    var month = ts.q("#getMonth").empty();
    var autocomplete = ts.q('#autocomplete-input');
    var reviewBlock = ts.q('.rv-review .search-area');
    var reviewPeople = ts.q('.comment-title');
    var staff_account_map = {};
    var staffObj = {};
    //var leaderNo = false;// 判斷是否為主管
    var isAdmin = false; // 判斷是否為系統管理者

    function initYM() {
        for (i = 1; i <= 12; i++) {
            month.append('<option value="' + i + '">' + i + '月</option>');
        }
        year.yearSet();
        year.change(function() {
            current.year = this.value;
            $.ym.save();
        });

        month.change(function() { current.month = this.value;
            $.ym.save(); });
        month.val(current.month).attr('selected');

    }
    initYM();

    ts.onLogin(function(member) {
        //var leaderNo = member.is_leader; // 判斷是否為主管
        this.isAdmin = member.is_admin; // 判斷是否為系統管理者

        var vm = new Vue({
            el: '#Review .rv-review',
            data: {
                inputData: autocomplete.val(),
                year: current.year,
                month: current.month,
                staffObj: staffObj,
                currentStaff: {},
                modal: _vue_modal
            },
            methods: {
                selected: function() {
                    var vs = this;

                    vs.setUnderStaff();

                    $.ym.save(this.$data);
                    if (this.currentStaff.id) {
                        var data = {
                            staff_id: this.currentStaff.id,
                            year: this.year,
                            month: this.month
                        }
                        this.modal.monthly_review.show(data, 0);
                    } else {
                        console.log("沒有評論目標");
                    }
                },
                setUnderStaff: function(){
                    var vs = this;

                    var modelname = (ts.isAdmin) ? 'admin' : 'department';

                    API.getUnderStaff({'model': modelname, 'year': vs.year, 'month': vs.month}).then(function(e) {
                        var result = API.format(e);

                        if (result.is) {
                            var list = ts.staff_list = result.res();
                            var staffArray = [];
                            var staffObj = {};
                            // ts.staff_map = result.map();
                            ts.staff_account_map = {};

                            // 資料做成dictionary，[key:value]→["liz.teng 鄧幼華" : Object]。
                            for (var i in list) {
                                var loc = list[i].account + ' ' + list[i].name;
                                staffArray.push(loc);
                                ts.staff_account_map[loc] = list[i];
                            }

                            for (var staff in staffArray) {
                                var key = staffArray[staff]
                                staffObj[key] = null
                            }

                            ts.staffObj = staffObj;
                            vs.staffObj = staffObj;

                            ts.q('input.autocomplete').autocomplete({
                                data: vs.staffObj,
                                onAutocomplete: function(value) {
                                    var targetStaff = ts.staff_account_map[value];
                                    vs.currentStaff = targetStaff
                                    var staff = {
                                        staff_id: vs.currentStaff.id,
                                        year: vs.year,
                                        month: vs.month
                                    }
                                    $.ym.save(staff);
                                    vs.modal.monthly_review.show(staff, 0);
                                    ts.q('input.autocomplete').val('')
                                }
                            });
                        }else{
                            console.log("Message:" + result.get())
                            ts.reviewBlock.css('display', 'none');
                            ts.reviewPeople.css('display', 'none');
                            ts.q('#NoData').show();
                        }
                    });
                }
            },
            mounted: function() {
                var vs = this;
                vs.setUnderStaff();
            }
        })
    });

    /*
    ts.onLogin(function(member) {
        var leaderNo = member.is_leader; // 判斷是否為主管
        API.getUnderStaff().then(function(e) {
            var result = API.format(e);

            if (result.is) {
                var list = ts.staff_list = result.res();
                var staffArray = [];
                var staffObj = {}
                // ts.staff_map = result.map();
                ts.staff_account_map = {}

                // 資料做成dictionary，[key:value]→["liz.teng 鄧幼華" : Object]。
                for (var i in list) {
                    var loc = list[i].account + ' ' + list[i].name;
                    staffArray.push(loc);
                    ts.staff_account_map[loc] = list[i];
                }

                for (var staff in staffArray) {
                    var key = staffArray[staff]
                    staffObj[key] = null
                }

                var vm = new Vue({
                    el: '#Review .rv-review',
                    data: {
                        inputData: autocomplete.val(),
                        year: current.year,
                        month: current.month,
                        staffObj: staffObj,
                        currentStaff: {},
                        modal: _vue_modal
                    },
                    methods: {
                        selected: function() {
                            $.ym.save(this.$data);
                            if (this.currentStaff.id) {
                                var data = {
                                    staff_id: this.currentStaff.id,
                                    year: this.year,
                                    month: this.month
                                }
                                this.modal.monthly_review.show(data, 0);
                            } else {
                                console.log("沒有評論目標");
                            }
                        }
                    },
                    mounted: function() {
                        var vss = this;
                        ts.q('input.autocomplete').autocomplete({
                            data: vss.staffObj,
                            onAutocomplete: function(value) {
                                var targetStaff = ts.staff_account_map[value];
                                vss.currentStaff = targetStaff
                                var staff = {
                                    staff_id: vss.currentStaff.id,
                                    year: vss.year,
                                    month: vss.month
                                }
                                $.ym.save(staff);
                                vss.modal.monthly_review.show(staff, 0);
                                ts.q('input.autocomplete').val('')
                            }
                        })
                    }
                })
            } else {
                console.log("Message:" + result.get())
                reviewBlock.css('display', 'none');
                reviewPeople.css('display', 'none');
                ts.q('#NoData').show();
            }
        });
    });
    */
});