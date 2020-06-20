var $assessForm = $('#Assess').generalController(function() {
    var ts = this;
    ts.templateArray = [];
    ts.vuesObj = {};
    ts.onLogin(function(member) {
        // fix();
        var today = new Date();
        var currentYear = today.getFullYear();
        var currentMonth = today.getMonth() + 1;
        var init = {
            // year: currentYear,
            staff_id: member.id
        }
        API.getMonthlyProcessWithOwner(init).then(function(json) {
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
                            result[r]['current_viewer'] = member.id;
                            var getMonthlyData = result[r];
                            //切換_report 結構 從陣列 轉成 物件
                            var report_transform = function(reports) {
                              var transform = {};
                              for (var i = reports.length - 1; i >= 0; i--) {
                                transform[reports[i]['id']] = reports[i];
                              }
                              return transform;
                            }
                            getMonthlyData._reports = report_transform(getMonthlyData._reports);
                            callVueRender(getMonthlyData);
                        }
                    }
                    contentMunu();
                    // 人事要移除滾輪加減分的功能
                    //mouseWheel();
                });
            } else {
                ts.q('#NoData').show();
            }

            function callVueRender(param) {
                var rand = 'row' + (count++);
                var tmp1 = null
                ts.q('#AssessForm').append('<div id="' + rand + '" ></div>');
                // console.log(JSON.parse(JSON.stringify(param)));
                var next = param.path_staff_id.indexOf(param.owner_staff_id) + 1;
                param._next_staff = param._path_staff[param.path_staff_id[next]];
                var tmp1 = new Vue({
                    template: '#template-1',
                    el: '#' + rand,
                    data: {
                        year: param.year,
                        month: param.month,
                        rand: rand,
                        member: member,
                        recvice: param,
                        changed: {},
                        totalScore:'',
                        modal: _vue_modal,
                        state:'wait',  //wait :可以進行任何操作， running :正在執行前一個動作，不能執行
                        toScoreKey: 'should_count',
                        departmentScoreLeaderNumberKey: '_owner_department_leader_number',
                    },
                    mounted: function() {
                        ts.q(this.$el).q('table').each(function(i) {
                            var table = ts.q(this);
                            var trs = table.q('tr').length;
                            if (trs > 4) { table.fixMe(); }
                        })
                    },
                    methods: {
                        checkScoreState(score) {
                            var vm = this,
                                res = true;
                            score.mistake == 0 && vm.total(score) == 0 ? res = false : res = true;
                            return res;
                        },
                        stateCheck:function() {
                          if (this.state == 'wait') {
                            return true;
                          } else {
                            swal("操作失敗", "請先等待上一個操作完成", "error");
                            return false;
                          }
                        },
                        stateChange:function(state) {
                          this.state = state; // wait or running
                        },
                        reportChange:function() {

                        },
                        extends: function(res,to) {
                          if (typeof(res) != "string") {
                            this.recvice = $.extend(this.recvice, res);
                          }
                          //修改權限
                          switch(to){
                            case 1:
                              this.recvice._authority.commit  =false;
                              this.recvice._authority.comment =false;
                              this.recvice._authority.editor  =false;
                              this.recvice._authority.drawing =true;
                              this.recvice._authority.return  =false;
                            break;
                            case -1:
                              this.recvice._authority.commit  =true;
                              this.recvice._authority.comment =true;
                              this.recvice._authority.editor  =true;
                              this.recvice._authority.drawing =false;
                              this.recvice._authority.return  =this.recvice.created_staff_id!=this.recvice.owner_staff_id;
                            break;
                            default:
                          }


                        },
                        save: function() {

                            if (!this.stateCheck()) {
                              return false;
                            }

                            var vss = this;
                            vss.stateChange('running');
                            var def = $.Deferred();
                            def.then(function(){ vss.stateChange('wait'); });
                            if (!this.isEmptyObject(this.changed)) {

                                var changedObj = JSON.parse(JSON.stringify(this.changed))
                                var insertData = {
                                    report: changedObj
                                };
                                // 確認是否存至新表
                                API.saveReport(insertData).then(function(e) {
                                    var success = API.format(e);
                                    if (success.is) {
                                      if (typeof(e.result) != "string") {
                                        for (var key in e.result) {
                                          if (vss.recvice._reports[key] != undefined) {
                                            vss.recvice._reports[key] = Object.assign(vss.recvice._reports[key], e.result[key]);
                                          }
                                        }
                                      }
                                      Materialize.toast('已儲存完畢您的變更', 2000);
                                      vss.changed = {};
                                    } else {
                                      Materialize.toast('儲存錯誤，原因:'+e.msg, 2000);
                                    }
                                    // vss.stateChange('wait');
                                    def.resolve();
                                })
                            } else {
                                Materialize.toast('資料尚未變更', 2000);
                                def.resolve();
                            }

                            return def;
                        },
                        collectBackData: function(report, e, field) {
                            if (field == 'bonus' || field == 'should_count') {
                                if (report[field]) { //true
                                    report[field] = 1;
                                } else { //false
                                    report[field] = 0;
                                }
                            } else {
                                var tar = e.target;
                                var val = report[field] = parseInt(report[field]);
                                if (!val) {
                                    val = 0;
                                }
                                if (tar.min) {
                                    val = Math.max(val, tar.min);
                                }
                                if (tar.max) {
                                    val = Math.min(val, tar.max);
                                }
                                report[field] = val;
                            }

                            if (!this.changed[report.id]) {
                                this.changed[report.id] = {
                                    id: report.id,
                                    processing_id: report.processing_id
                                };
                            }
                            this.changed[report.id][field] = report[field];
                        },
                        commit: function() {
                            if (!this.stateCheck()) {
                              return false;
                            }
                            var vss = this;
                            var commitId = { processing_id: this.recvice.id }
                            var isAllDone = 1;
                            var noCheckedBonus = [];
                            var nonZeroAddedValue = [];
                            var nonZeroMistake = [];
                            var nonZeroShouldnotCount = [];
                            var report = this.recvice._reports;

                            for (var r in report) {
                                
                                if (!report[r].should_count) {
                                    nonZeroShouldnotCount.push(report[r].name_en);
                                    isAllDone = 0;
                                }

                                if (report[r].bonus == 0) {
                                    noCheckedBonus.push(report[r].name_en);
                                    isAllDone = 0;
                                }

                                if (report[r].addedValue != 0) {
                                    nonZeroAddedValue.push(report[r].name_en);
                                    isAllDone = 0;
                                }

                                if (report[r].mistake != 0) {
                                    nonZeroMistake.push(report[r].name_en);
                                    isAllDone = 0;
                                }
                            }

                            if (isAllDone) {
                                commitMonthly();
                            } else {

                                if (nonZeroShouldnotCount.length != 0) {
                                    var nameShouldCount = nonZeroShouldnotCount.join("、");
                                    var shouldCountHTML = "<li style='text-align: left;'>不評分名單：" + nameShouldCount + "</li>";
                                } else {
                                    var shouldCountHTML = "";
                                }

                                if (noCheckedBonus.length != 0) {
                                    var nameBonus = noCheckedBonus.join("、");
                                    var bonusHTML = "<li style='text-align: left;'>不發放獎金名單：" + nameBonus + "</li>";
                                } else {
                                    var bonusHTML = "";
                                }
                                if (nonZeroAddedValue.length != 0) {
                                    var nameAddedValue = nonZeroAddedValue.join("、");
                                    var addedValueHTML = "<li style='text-align: left;'>特殊貢獻名單：" + nameAddedValue + "</li>";
                                }else {
                                    var addedValueHTML = "";
                                }
                                if (nonZeroMistake.length != 0) {
                                    var nameMistake = nonZeroMistake.join("、");
                                    var mistakeHTML = "<li style='text-align: left;'>重大缺失名單：" + nameMistake + "</li>";
                                }else {
                                    var mistakeHTML = "";
                                }
                                swal({
                                    title: "確認是否要送出?",
                                    text: "<div> <p style='color:#d16a00;margin-bottom: 10px;'>請確認是否有添加說明。</p> <ol>" + bonusHTML + addedValueHTML + mistakeHTML + shouldCountHTML + "</ol></div>",
                                    type: "info",
                                    html: true,
                                    showCancelButton: true,
                                    confirmButtonColor: "#DD6B55",
                                    confirmButtonText: "確認",
                                    cancelButtonText: "取消",
                                    closeOnConfirm: false,
                                    closeOnCancel: false
                                },
                                function(isConfirm) {
                                    if (isConfirm) {
                                        commitMonthly();
                                        swal("送審", "您已經成功提交", "success");
                                    } else {
                                        swal("取消", "協助檢查是否已完成填寫評語", "error");
                                    }
                                });
                            }

                            function commitMonthly() {
                                //等待儲存完畢
                                vss.save().then(function(){
                                  API.commitMonthly(commitId).then(function(e) {
                                      var success = API.format(e);
                                      if (success.is) {
                                        if (e.result == 'Already Done.') {
                                          $(vss.$el).remove();
                                        } else {
                                          vss.extends(e.result,1);
                                        }
                                          $(vss.$el).remove();
                                          Materialize.toast('已提交送審', 2000)
                                      }else{
                                        Materialize.toast('提交失敗', 2000)
                                      }
                                  });
                                });
                            }
                        },
                        open: function(param) {
                            
                            ts.q('#ReJectModal-' + rand).modal({
                                dismissible: false
                            });
                            var processingId = {
                                processing_id: param.id
                            }
                            API.getMonthlyRejectList(processingId).then(function(e) {
                                var result = API.format(e);
                                if (result.is) {
                                    var list = result.get();
                                    var rj = ts.q('#ReJectModal-' + rand).find("select").empty();
                                    for (var l in list) {
                                        rj.append('<option value="' + list[l].id + '">' + list[l].department_name + '</option>');
                                    }
                                }
                            })
                        },
                        reject: function(param) {
                            var def = this.save();
                            var ownerId = param.id
                            var backId = ts.q('#ReJectModal-' + rand + ' option:selected').val()
                            var backReason = ts.q('#ReJectModal-' + rand + ' textarea').val()
                            var rejectData = {
                                processing_id: ownerId,
                                staff_id: backId,
                                reason: backReason
                            }
                            if (backReason != '') {

                                if (backId != undefined) {
                                    ts.q('#ReJectModal-' + rand).modal("close")
                                    $(this.$el).remove();
                                    //
                                    def.then( function(){
                                      API.rejectMonthly(rejectData).then(function(e) {
                                            var success = API.format(e);
                                            if (success.is) {
                                                Materialize.toast('已退回該表單', 2000)
                                            }
                                        });

                                    } );

                                } else {
                                    ts.q('#ReJectModal-' + rand).modal("close")
                                    Materialize.toast('此單您無法執行退回動作', 2000)
                                }
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
                        leaderSumScore:function(report){
                            var score_total = (report.target * 2) + (report.quality * 2) + (report.method * 2) + (report.error * 2) + (report.backtrack * 2) + (report.planning * 2) + (report.execute * 1.4) + (report.decision * 1.4) + (report.resilience * 1.2) + (report.attendance * 2) + (report.attendance_members * 2);
                            score_total = Math.min(score_total, 100) ;
                            return score_total;
                        },
                        staffSumScore:function(report){
                            if (report.duty_shift == 0) {
                                // 一般員工的總分
                                var score_total = (report.quality * 5) + (report.completeness * 5) + (report.responsibility * 5) + (report.cooperation * 3) + (report.attendance * 2);

                            } else {
                                // 值班員工的總分
                                var score_total = (report.quality * 5) + (report.completeness * 5) + (report.responsibility * 3) + (report.cooperation * 3) + (report.attendance * 4);
                            }
                            score_total = Math.min(score_total, 100);
                            return Math.round(score_total);
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
                                }else {
                                    return Math.round(score_total)
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
                        isDisabled:function() {
                          return !this.isAuthority('editor');
                        },
                        isAuthority:function(key){
                          return this.recvice['_authority'][key] || false;
                        },
                        draw: function(param) {
                            var ownerId = param.id;
                            var vss = this;
                            var backReason = ts.q('#DrawModal-' + rand + ' textarea').val()
                            //清空
                            ts.q('#DrawModal-' + rand + ' textarea').val('');
                            var rejectData = {
                                processing_id: ownerId,
                                reason: backReason
                            }
                            if (backReason != '') {
                                var that = this;
                                API.drawSingle(rejectData).then(function(e) {
                                    var success = API.format(e);
                                    if (success.is) {
                                        Materialize.toast('已抽回該表單', 2000);
                                        vss.extends(e.result,-1);
                                    } else {
                                      Materialize.toast('抽單失敗 ，原因:' + e.msg, 2000)
                                    }
                                })
                                ts.q('#DrawModal-' + rand).modal("close");
                            } else {
                                swal("Hi", "請輸入退回原因!");
                            }
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

                var contextmenu = $('<div class="content-menu"> <li class="save">儲存</li> <li class="absence">出缺席記錄</li> <li class="history">歷史記錄</li> <li class="top">回到此單頂部</li> </div>').on('click', 'li', function() {
                    var vue = contextmenu.targetVue;
                    switch (this.className) {
                        case "save":
                            vue.save.apply(vue);
                            break;
                        case "absence":
                            vue.absence.apply(vue);
                            break;
                        case "history":
                            vue.history.apply(vue);
                            break;
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
                    if ($input.length == 0) {
                        return;
                    }
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
});