var $Organization = $('#Organization').generalController(function() {
    var ts = this,
        current = $.ym.get();
    var month = ts.q('#ChartMonth');
    var year = ts.q('#ChartYear');

    function initYM() {
        // var ddate = new Date();
        // var thisyear = ddate.getFullYear() + (ddate.getMonth()==11 && ddate.getDate() >20 ? 1:0);
        // for (i = thisyear; i >= API.create.year; i--) {
        //     year.append('<option value="' + i + '">' + i + '年</option>');
        // }
        for (i = 1; i <= 12; i++) {
            month.append('<option value="' + i + '">' + i + '月</option>');
        }
        year.yearSet();
        month.val(current.month).attr('selected');
        month.change(function(){ current.month = this.value;$.ym.save(); });
    }
    initYM();

    // 組織圖樣版

    var cell = this.q('#cell-1').html();
    var selectMonth = ts.q('#SelectMonth').find('select');
    var historyModal = ts.q('#HistoryModal');
    var cell2 = ts.q('#cell-2').html();



    // member 的資料
    ts.onLogin(function(member) {
        console.log(JSON.parse(JSON.stringify(member)));

        setTimeout(function() {
            year.val(current.year);
            month.val(current.month).trigger('change');
        }, 50);

        ts.on('click', '*[data-process-id]', function() {
            // get btn process id number
            var $this_btn = ts.q(this);
            var pid = $this_btn.data('process-id');
            _vue_modal.monthly_history.show(pid);
        });

        // 當使用者選擇月份
        selectMonth.on('change', function() {
            current.year = year.val();
            current.month = month.val();
            $.ym.save();
            loadMap();

        });

        function loadMap() {
            var mon = current.month;
            var yn = current.year;
            var param = "year=" + yn + "&month=" + mon;

            var rvChart = ts.q('#RvChart');
            // 清空組織圖資料
            // add a loading
            rvChart.empty();

            API.getDepartmentList(param).then(function(json) {
                // 解析資料
                var collect = API.format(json);

                // 分析資料
                if (collect.is) {
                    var unitArray = collect.get();
                    var lvArray = {};

                    for (var i in unitArray) {
                        var loc = unitArray[i];
                        //console.log(loc);
                        loc.manager_name = (loc._manager) ? loc._manager.name : '暫缺';
                        loc.manager_name_en = (loc._manager) ? loc._manager.name_en : '暫缺';
                        
                        //判斷是否包含在path id 
                        //20180504 新增兼任主管功能，同時顯示兼任單位
                        loc.record_if = ($.inArray(member.department_id, loc.path_upper_department_ids) >= 0) || (member.id == loc['manager_staff_id']) ;

                        // 管理群處理
                        if (loc._leadership_staff.length > 0) {
                            for (var leader_i in loc._leadership_staff) {
                                var currLeader = loc._leadership_staff[leader_i];
                                if (member.id == currLeader.id) {
                                    loc.record_if = true;
                                }
                                if (currLeader.lv <= 4) {
                                    loc.manager_name_en += ' '+ currLeader.name_en;
                                }
                                
                            }
                        }


                        var la = lvArray[loc['lv']];
                        if (la) {
                            la.push(loc);
                        } else {
                            lvArray[loc['lv']] = [loc];
                        }
                    } //for unit_array..
                    if (unitArray) {
                        ts.q('.no-data').hide();
                        ts.q('.rv-chart-info').fadeIn('slow');
                    }

                    var sort_id_array = [];
                    for (var a in lvArray) {
                        var loc = lvArray[a];
                        for (var b in loc) {
                            var loc2 = loc[b];
                            var $root = ts.q('[data-department-id=' + loc2['upper_id'] + ']');
                            var newCell = cell.replace(/\{([\w\d]+?)\}/ig, function(match, param1) {
                                var key = param1;
                                return loc2[key];
                            });

                            // 這太浪費 by snow
                            var j_cell = $(newCell);
                            //var key = j_cell.q('[rv-if]').attr('rv-if');
                            var ablock = j_cell.q('[data-unitid]');
                            var scoreBTN = j_cell.q('.score-history');
                            var pidBtn = j_cell.q('[data-created-id]');
                            if (!loc2['_manager'] && !loc2['_staff']) {
                                ablock.addClass('noleader')
                            }
                            // 無考評單也要顯示白色
                            if (!loc2['_processing']) {
                                ablock.addClass('noleader')
                            }
                            if (loc2['lv'] == 3) {
                                j_cell.addClass('nav-row');
                            }

                            if (loc2.record_if) {
                                j_cell.addClass('active');
                            }
                            // if (loc2['manager_staff_id'] == member.id) {
                            //     j_cell.addClass('active');
                            // }
                            //20170810
                            if(loc2['_staff']){
                              var staff_ary = [];
                              for(var sa in loc2['_staff']){
                                var loc3 = loc2['_staff'][sa];
                                if(loc3.id==loc2['manager_staff_id']){continue;}
                                if (loc3.lv > 4) {
                                    staff_ary.push( loc3.staff_no +' - '+ loc3.name_en + loc3.name );
                                }
                                
                              }
                              ablock.attr('title',staff_ary.join('\n'));
                            }


                            $root.append(j_cell);
                            sort_id_array.push(loc2);
                            
                            if (!loc2.record_if && member.is_admin != 1) {
                                continue;
                            }

                            // 考評單按鈕
                            btnHis_1_non_score = '<button data-target="HistoryModal" class="score-history waves-effect waves-light btn non-score" data-process-id="{id}"">主管紀錄</button>';
                            btnHis_1_reviewing = '<button data-target="HistoryModal" class="score-history waves-effect waves-light btn reviewing" data-process-id="{id}">主管紀錄</button>';
                            btnHis_1_return = '<button data-target="HistoryModal" class="score-history waves-effect waves-light btn return" data-process-id="{id}">主管紀錄</button>';
                            btnHis_1_rated = '<button data-target="HistoryModal" class="score-history waves-effect waves-light btn rated" data-process-id="{id}">主管紀錄</button>';

                            btnHis_2_non_score = '<button data-target="HistoryModal" class="score-history waves-effect waves-light btn non-score" data-process-id="{id}">紀錄</button>';
                            btnHis_2_reviewing = '<button data-target="HistoryModal" class="score-history waves-effect waves-light btn reviewing" data-process-id="{id}">紀錄</button>';
                            btnHis_2_return = '<button data-target="HistoryModal" class="score-history waves-effect waves-light btn return" data-process-id="{id}">紀錄</button>';
                            btnHis_2_rated = '<button data-target="HistoryModal" class="score-history waves-effect waves-light btn rated" data-process-id="{id}">紀錄</button>';
                            
                            for (var id in loc2._processing) {
                                var template;
                                switch (loc2._processing[id].status_code) {
                                    // 草稿
                                    case 1:
                                        template = '';
                                        break;
                                        //初評
                                    case 2:
                                        if (loc2._processing[id] && loc2._processing[id].type == 1) {
                                            template = btnHis_1_non_score;
                                        }
                                        if (loc2._processing[id] && loc2._processing[id].type == 2) {
                                            template = btnHis_2_non_score;
                                        }
                                        ablock.addClass('non-score');
                                        break;
                                        // 審核
                                    case 3:
                                        if (loc2._processing[id] && loc2._processing[id].type == 1) {
                                            template = btnHis_1_reviewing;
                                        }
                                        if (loc2._processing[id] && loc2._processing[id].type == 2) {
                                            template = btnHis_2_reviewing;
                                        }

                                        ablock.addClass('reviewing');
                                        break;
                                        // 核準
                                    case 4:
                                        if (loc2._processing[id] && loc2._processing[id].type == 1) {
                                            template = btnHis_1_return;
                                        }
                                        if (loc2._processing[id] && loc2._processing[id].type == 2) {
                                            template = btnHis_2_return;
                                        }

                                        ablock.addClass('return');
                                        break;
                                        // 退回
                                    case 5:
                                        if (loc2._processing[id] && loc2._processing[id].type == 1) {
                                            template = btnHis_1_rated;
                                        }
                                        if (loc2._processing[id] && loc2._processing[id].type == 2) {
                                            template = btnHis_2_rated;
                                        }
                                        ablock.addClass('rated');
                                        break;
                                        defalt:
                                            template = '';
                                        break;
                                }
                                
                                var btn_1_non = j_cell.q('[rv-if]').append(template.replace('{id}', id));
                                btn_1_non.q('[data-process-id]').data('record', loc2);

                            } //for.. lloc2.processing..

                            console.log(loc2.name, loc2, btn_1_non);

                        } // for ivarray.loc..  lv

                    } // for lvarray..
                    var unitWidth = $("#RvChart [data-department-id=1]>li")
                    var totalWidth = 0;
                    unitWidth.each(function(index) {
                        totalWidth += parseInt($(this).width(), 10);
                    });
                    totalWidth += 60;
                    $('.rv-chart').css('min-width', totalWidth);

                    if (member.is_admin == 1) {
                        rvChart.children().addClass('active');
                    }

                    for (var sia in sort_id_array) {
                        var id_sa = sort_id_array[sia];
                        department_sort(id_sa);
                    }
                    // 排序功能：改為依部門id來排序
                    function department_sort(deparment) {
                        // return console.log(deparment);
                        var self = ts.q('[data-department-id=' + deparment.id + ']');
                        var li = self.children();
                        if (li.length == 0) {
                            return;
                        }
                        switch (deparment.lv) {
                            case 1:
                            case 2:
                                li.sort(sort_lib).appendTo(self);
                                break;
                            case 3:
                                li.sort(sort_lia).appendTo(self);
                                break;
                        }
                    }

                    // 正向排序
                    function sort_lia(a, b) {
                        return ($(b).data('unitid')) < ($(a).data('unitid')) ? 1 : -1;
                    }

                    // 反向排序
                    function sort_lib(a, b) {
                        return ($(b).data('unitid')) > ($(a).data('unitid')) ? 1 : -1;
                    }

                } else {
                    ts.q('.no-data').show();
                    ts.q('.rv-chart-info').hide();
                }
            });
        }
    });
})