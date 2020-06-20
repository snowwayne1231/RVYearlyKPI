var $assessForm = $('#DrawAssess').generalController(function() {
    var ts = this;
    ts.templateArray = [];
    ts.vuesObj = {};
    ts.onLogin(function(member) {
        fix();
        var today = new Date();
        var currentYear = today.getFullYear()
        var currentMonth = today.getMonth() + 1
        var init = {
            year: currentYear,
            staff_id: member.id
        }
        API.getDrawSingle(init).then(function(json) {
            var collectHasForm = API.format(json);
            var getMonthlyData = 0;
            var count = 1;
            if (collectHasForm.is) {
                var result = collectHasForm.res();
                var apiArray = [];
                for (var id in result) {
                    var formId = result[id].id;
                    var data = {
                        processing_id: formId
                    }
                    apiArray.push(API.getMonthlyReport(data));
                }
                $.when.all(apiArray).then(function(data) {
                    var newData = (typeof data[1] == "string") ? [data] : data;
                    for (var i in newData) {
                        var loc = newData[i];
                        var result = API.format((loc[0] || loc)).get();
                        for (var r in result) {
                            var getMonthlyData = result[r];
                            callVueRender(getMonthlyData);
                        }
                    }
                    contentMunu();
                });
            } else {
                ts.q('#NoData').show();
            }

            function callVueRender(param) {
                var rand = 'row' + (count++);
                var tmp1 = null
                ts.q('#DrawAssessForm').append('<div id="' + rand + '" ></div>');

                var next = param.path_staff_id.indexOf(param.owner_staff_id) + 1;
                param._next_staff = param._path_staff[param.path_staff_id[next]];
                var tmp1 = new Vue({
                    template: '#template-DrawAssess',

                    el: '#' + rand,
                    data: {
                        year: param.year,
                        month: param.month,
                        rand: rand,
                        member: member,
                        recvice: param,
                        changed: {},
                        modal: _vue_modal
                    },
                    mounted: function() {
                        ts.q(this.$el).q('table').each(function(i) {
                            var table = ts.q(this);
                            var trs = table.q('tr').length;
                            if (trs > 4) { table.fixMe(); }
                        })
                    },
                    methods: {
                        open: function(param) {
                            ts.q('#ReJectModal-' + rand).modal({
                                dismissible: false
                            });
                        },
                        reject: function(param) {
                            var ownerId = param.id
                            var backReason = ts.q('#ReJectModal-' + rand + ' textarea').val()
                            var rejectData = {
                                processing_id: ownerId,
                                reason: backReason
                            }
                            if (backReason != '') {
                                var that = this;
                                API.drawSingle(rejectData).then(function(e) {
                                    var success = API.format(e);
                                    if (success.is) {
                                        Materialize.toast('已抽回該表單', 2000)
                                    }
                                    delete ts.vuesObj[that.rand];
                                    if (Object.getOwnPropertyNames(ts.vuesObj).length == 0) {
                                        ts.q('#NoData').show();
                                    }
                                })
                                ts.q('#ReJectModal-' + rand).modal("close")
                                $(this.$el).remove();

                            } else {
                                swal("Hi", "請輸入退回原因!");
                            }
                        },
                        history: function() {
                            this.modal.monthly_history.show(this.recvice.id);
                        },
                        absence: function() {
                            var after;
                            if (this.recvice.type == 1) {
                                after = "&staff=";
                                var ary = [];
                                for (var i in this.recvice._reports) {
                                    ary.push(this.recvice._reports[i].staff_id);
                                }
                                after += ary.join(',');
                            } else {
                                after = "&team=" + this.recvice.created_department_id;
                            }
                            window.open("None/Frame/absence?year=" + this.year + "&month=" + this.month + after);
                        },
                        comment: function(report) {
                            this.modal.monthly_review.show(report, 1);
                        },
                        total: function(report) {
                            if (this.recvice.type == 1) {
                                // 主管們的總分
                                var score_total = (report.target * 2) + (report.quality * 2) + (report.method * 2) + (report.error * 2) + (report.backtrack * 2) + (report.planning * 2) + (report.execute * 1.4) + (report.decision * 1.4) + (report.resilience * 1.2) + (report.attendance * 2) + (report.attendance_members * 2);
                                score_total = Math.min(score_total, 100) + report.addedValue - report.mistake;
                                if (score_total < 0) {
                                    return score_total = 0
                                } else {
                                    return Math.round(score_total)
                                }
                            } else {
                                // 員工們的總分
                                if (report.duty_shift == 0) {
                                    // 一般員工的總分
                                    var score_total = (report.quality * 5) + (report.completeness * 5) + (report.responsibility * 5) + (report.cooperation * 3) + (report.attendance * 2);

                                } else {
                                    // 值班員工的總分
                                    var score_total = (report.quality * 5) + (report.completeness * 5) + (report.responsibility * 3) + (report.cooperation * 3) + (report.attendance * 4);
                                }
                                score_total = Math.min(score_total, 100) + report.addedValue - report.mistake;
                                if (score_total < 0) {
                                    return score_total = 0
                                } else {
                                    return score_total
                                }
                            }
                        },
                        isEmptyObject: function(obj) {
                            for (var name in obj) {
                                if (obj.hasOwnProperty(name)) {
                                    return false;
                                }
                            }
                            return true;
                        },
                        decideFloat: function(e, pnumber) {
                            if (!/^\+?[0-5]*$/.test(pnumber)) {
                                e.value = /\+?[0-5]*/.exec(e.value);
                                swal("!", "請輸入0~5的整數");
                            }
                            return false;
                        }
                    }
                });
                ts.vuesObj[rand] = tmp1;
                ts.templateArray.push(tmp1);
                var ele = tmp1.$el;
                ts.q(".modal").modal();
                ts.q(ele).q('.collapsible').collapsible();
                ts.q("#CommentText").focus(function() {
                    ts.q("#CommentText" + (index + 1) + "-" + rand).siblings().show();
                });
            }


            function contentMunu() {
                ts.$.on('contextmenu', '.rv-assess >div.row', function(e) {
                    e.preventDefault();
                    $t = ts.q(this);
                    var vueKey = $t.data('vue');
                    var vue_object = ts.vuesObj[vueKey];
                    contextmenu.appendTo(document.body).show().css({ left: e.pageX, top: e.pageY });
                    contextmenu.targetVue = vue_object;
                }).parents(window).on('click', function() { contextmenu.detach(); });

                var contextmenu = $('<div class="content-menu">  <li class="top">回到此單頂部</li> </div>').on('click', 'li', function() {
                    var vue = contextmenu.targetVue;
                    switch (this.className) {
                        case "top":
                            var header = ts.q(vue.$el).q('.collapsible-header');
                            var top = header.position().top - header.height();
                            $('body,html').animate({ scrollTop: top }, 500);
                            break;
                    }
                });
            }

            function mouseWheel() {
                var inputEvent = new Event('input');
                var changeEvent = new Event('change');
                ts.$.on('mousewheel', '.rv-assess .card-cell', function(e) {
                    var $t = ts.q(this);
                    var $input = $t.q('input[type=number]'),
                        input = $input[0];
                    if ($input.length == 0) { return; }
                    var value = Number($input.val());

                    e.preventDefault();
                    if (e.originalEvent.deltaY > 0) {
                        var res = Math.max(value - 1, input.min || 0);
                    } else {
                        var res = input.max ? Math.min(value + 1, input.max) : value + 1;
                    }
                    $input.val(res);
                    input.dispatchEvent(inputEvent);
                    input.dispatchEvent(changeEvent);
                });
            }
        });
    });

    function fix() {
        $.fn.fixMe = function() {
            return this.each(function() {
                var $this = $(this),
                    $t_fixed;

                function init() {
                    $this.wrap('<div class="staff-table">');
                    $t_fixed = $this.clone();
                    $t_fixed.find("tbody").remove().end().addClass("fixedTable").insertBefore($this);
                    resizeFixed();
                }

                function resizeFixed() {
                    var thead = $this.find('thead');
                    var tWidth = thead.outerWidth();

                    $t_fixed.find('thead').css('width', tWidth + 'px');
                    thead.find('th').each(function(i) {
                        var thw = $(this).width();
                        $t_fixed.find('th').eq(i).width(thw);
                    });
                }

                function scrollFixed() {
                    if ($this.is(":visible")) {
                        var offset = $(this).scrollTop(),
                            tableOffsetTop = $this.offset().top,
                            tableOffsetBottom = tableOffsetTop + $this.height() - $this.find("thead").height();

                        if (offset < tableOffsetTop || offset > tableOffsetBottom) {
                            $t_fixed.hide();
                        } else if (offset >= tableOffsetTop && offset <= tableOffsetBottom && $t_fixed.is(":hidden")) {
                            $t_fixed.show();
                        }
                        resizeFixed();
                    }
                }
                $(window).resize(resizeFixed);
                $(window).scroll(scrollFixed);
                init();
            });
        };
    }
});