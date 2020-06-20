var $overView = $('#Management-YearChart').generalController(function() {
    var ts = this;
    var current = $.ym.get();
    var year = ts.q('#ChartYear');
    var minYear = 2017;

    function initDate() {
        year.yearSet(); // yearSet() in header.js
        year.change(function() {
            current.year = this.value;
            $.ym.save();
            API.reload();
        });
    }
    initDate();

    var template = this.q('#cell-1').html();
    var selectDate = ts.q('#SelectDate').find('select');
    var contextmenu = $('<div class="year-chat-menu fast-animated snowRubberBand"></div>');

    ts.onLogin(function(member) {
        if (member.is_admin == 0) { $('.fbkManagement').hide(); }
        var dictionaryUnitStaff = {};
        var getAPI = {
            chatYear: current.year,
            config: {},
            unitList: {},
            lv: {},
            stepProcessing: {},
            getYearlyOrganization: function() {
                var apithis = this;
                return new Promise(function(resolve) {
                    API.getYearlyOrganization({ year: apithis.chatYear }).then(function(e) {
                        var cnt = API.format(e);
                        if (cnt.is) {
                            apithis.config = cnt.get().config;
                            apithis.unitList = cnt.get().unit_map;
                            apithis.stepProcessing = {
                                feedback: apithis.config.processing <= 3,
                                personal: apithis.config.processing >= 4 && apithis.config.processing <= 6,
                                division: apithis.config.processing == 7,
                                ceo: apithis.config.processing == 8,
                                finish: apithis.config.processing == 9
                            }
                            console.log(apithis.config.processing)
                            if (apithis.config.processing == 0) {
                                ts.q('.year-map').hide();
                                ts.q('#not-start').show();
                            } else {
                                ts.q('#not-start').hide();
                                ts.q('.year-map').show();
                            }
                            // 組別資料處理
                            apithis.lv = {};
                            for (var ul in apithis.unitList) {
                                var unit = apithis.unitList[ul];
                                unit.manager_name = (unit.manager_staff_name) ? unit.manager_staff_name : '暫缺';
                                unit.manager_name_en = (unit.manager_staff_name_en) ? unit.manager_staff_name_en : '';

                                var classify = apithis.lv[unit['lv']];
                                if (classify) {
                                    classify.push(unit);
                                } else {
                                    apithis.lv[unit['lv']] = [unit];
                                }
                            }
                            resolve('ok');
                            ts.q('#NoData').hide();
                        } else {
                            ts.q('#RvChart').empty();
                            ts.q('#NoData').show();
                        }
                    });
                });
            }
        }


        // selectDate.on('change', function(e) {
        // current.year = year.val();
        // getAPI.chatYear = current.year;
        // $.ym.save();
        // initial();
        // });

        // 部門單下載
        contextmenu.on('click', '.division-downexcel', function() {
            var divisionId = $(this).data('division-id');
            exportYearlyAssessmentExcel(divisionId, 'division');
        });
        // 組單位單下載
        contextmenu.on('click', '.department-downexcel', function() {
            var departmentId = $(this).data('department-id');
            exportYearlyAssessmentExcel(departmentId, 'department');
        });
        // 個人單下載功能
        contextmenu.on('click', '.personal-downexcel', function() {
            var staffId = $(this).data('staff-id');
            exportYearlyAssessmentExcel(staffId, 'staff');
        });
        //
        contextmenu.on('click', '.filter.feedback button', function() {
            var filterStatus = $(this).data('status');
            if (filterStatus == undefined) { $(contextmenu).find('li').show(); return; }
            $(contextmenu).find('li').hide();
            $(contextmenu).find('li [data-status=' + filterStatus + ']').parents('li').show();
        });

        contextmenu.on('click', '.filter.report button', function() {
            var filterStatus = $(this).data('status');
            if (filterStatus == undefined) { $(contextmenu).find('li').show(); return; }
            $(contextmenu).find('li').hide();
            $(contextmenu).find('li [data-status=' + filterStatus + ']').parents('li').show();
        });

        function exportYearlyAssessmentExcel(id, type) {
            var data = { year: getAPI.chatYear };

            if (type == 'division') {
                data['division_id'] = id;
            }

            if (type == 'department') {
                data['department_id'] = id;
            }

            if (type == 'staff') {
                data['staff_id'] = id;
            }
            API.exportYearlyAssessmentExcel(data).then(function(e) {
                var result = API.format(e);
                if (result.is) {
                    swal('下載成功', '開始為您下載Excel', 'success');
                } else {
                    swal('下載失敗', result.get(), 'error');
                }
            });
        }

        function initial() {
            getAPI.getYearlyOrganization().then(function(e) {
                loadMap();
                console.log(getAPI.stepProcessing)
            });
        }
        initial();

        var feedbackFN = function(block, unit) {
            if (member.is_admin) {
                $(block).find('.team-leader').after('<div class="record">( ' + unit['_feedback_finished'] + ' / ' + unit['_feedback_total'] + ' )</div>');
            }
            // 整組送出組別樣式變更，沒單子的組不給樣式
            if (unit['_feedback_total'] == 0) {
                $(block).css('background', 'linear-gradient(#e0e0e0, #FFF)').css('color', '#bdbdbd');
            }
            if (unit['_feedback_total'] != 0 && unit['_feedback_finished'] == unit['_feedback_total']) {
                $(block).addClass('gofeedback');
            }
        }

        var personalFN = function(block, unit) {
            if (member.is_admin) {
                $(block).find('.team-leader').after('<div class="record">( ' + unit['_report_this_finished'] + ' / ' + unit['_report_this_total'] + ' )</div>');
            }
            // 0= 無主管/空, 1=準備狀態, 2=初評, 3=審核中, 4=初步完成, 5=核准
            switch (unit['status_code']) {
                case 0:
                    $(block).css('background', 'linear-gradient(#e0e0e0, #FFF)');
                    break;
                case 1:
                    $(block).css('background', 'linear-gradient(#e0e0e0, #FFF)');
                    break;
                case 2:
                    $(block).css('background', 'linear-gradient(#FFF, #bbdefb)');
                    break;
                case 3:
                    $(block).css('background', 'linear-gradient(#fff4d0, #ffd54f)');
                    break;
                case 4:
                    $(block).css('background', 'linear-gradient(#ffffff, #9fe2ad)');
                    break;
                case 5:
                    $(block).css('background', 'linear-gradient(#e5f2d6, #4ac34e)');
                    break;
            }
        }

        var divisionFN = function(block, unit) {
            if (member.is_admin) {
                $(block).find('.team-leader').after('<div class="record">( ' + unit['_report_finished'] + ' / ' + unit['_report_total'] + ' )</div>');
            }
            //部門單進程 0=初始, 1=部長加減分, 2=部長提交到架構發展部, 3=架構發展確認後給執行長, 4=執行長加減分, 5=部門核准
            if (unit['lv'] > 2 || unit['_division'].status == 0) {
                $(block).css('background', 'linear-gradient(#e0e0e0, #FFF)').css('color', '#bdbdbd');
            } else {
                switch (unit['_division'].processing) {
                    case 0:
                        $(block).css('background', 'linear-gradient(to top, #dfe9f3 0%, white 100%)');
                        break;
                    case 1:
                        $(block).css('background', 'linear-gradient(to top, #ffd54f 0%, white 100%)');
                        break;
                    case 2:
                        $(block).css('background', 'linear-gradient(to top, #ffd54f 0%, white 100%)');
                        break;
                    case 3:
                        $(block).css('background', 'linear-gradient(to top, #ff9222 0%, white 100%)');
                        break;
                    case 4:
                        $(block).css('background', 'linear-gradient(to top, #ff9222 0%, white 100%)');
                        break;
                    case 5:
                        $(block).css('background', 'linear-gradient(to top, #4CAF50 0%, white 100%)');
                        break;
                }
            }
        }

        var rightClick = function(block, unit) {
            block.on('contextmenu', function(e) {
                e.preventDefault();
                var blockthis = this;
                var thisUnitInStaff = dictionaryUnitStaff[ts.q(this).data('process-id')];
                var windowWidth = $(window).width();
                if (thisUnitInStaff || unit['lv'] <= 2) {
                    contextmenu.appendTo(document.body).show().css({ left: e.pageX, top: e.pageY });
                    contextmenu.empty();
                    contextmenu.append($('<input type="text" id="search">'));

                    if (getAPI.stepProcessing.feedback) {
                        contextmenu.append($('<div class="filter feedback"><button class="overtime" data-status="-1">過期</button><button class="success" data-status="1">已交</button><button class="self" data-status="0">未交</button><button class="all">全部</button></div>'));
                    }

                    if (getAPI.stepProcessing.personal || getAPI.stepProcessing.finish) {
                        contextmenu.append($('<div class="filter report"><button class="void" data-status="0">作廢</button><button class="success" data-status="3">完成</button><button class="commit" data-status="2">審核</button><button class="self" data-status="1">自評</button><button class="all">全部</button></div>'));
                    }

                    if (unit['lv'] <= 2 && unit['_report_total'] > 0) {
                        contextmenu.append($('<li class="division-downexcel" data-division-id="' + unit.id + '">部門單位考評表 Download</li>'));
                    }
                    if (unit['_report_this_total'] > 0) {
                        contextmenu.append($('<li class="department-downexcel" data-department-id="' + unit.id + '">當前單位考評表 Download</li>'));
                    }


                    for (var staff in thisUnitInStaff) {
                        // 部屬回饋組織圖
                        var thisStaffFbk = thisUnitInStaff[staff]._feedback;
                        if (getAPI.stepProcessing.feedback) {
                            console.log(thisUnitInStaff[staff])
                            var row = contextmenu.append($('<li title='+thisUnitInStaff[staff].staff_no+' data-id=' + thisUnitInStaff[staff].id + '>' + thisUnitInStaff[staff].name + ' ' + thisUnitInStaff[staff].name_en + '</li>'));
                            var rowLi = $(row.find('[data-id=' + thisUnitInStaff[staff].id + ']'));
                            var sheet = 1;

                            for (var fbk in thisStaffFbk) {
                                var rowfbk = thisStaffFbk[fbk];
                                rowLi.prepend('<button class="btn-feedbk" data-fbk-id=' + rowfbk.id + ' data-status=' + rowfbk.status + '>' + sheet + '</button>');
                                //問卷狀態  1=交出, 0=未交, -1=不收了
                                switch (rowfbk.status) {
                                    case -1:
                                        rowLi.find('button').css('background', '#ff52528f');
                                        sheet++;
                                        continue;
                                    case 0:
                                        rowLi.find('button').addClass('nogofeedback');
                                        break;
                                    case 1:
                                        rowLi.find('button').addClass('gofeedback');
                                        break;
                                }

                                // 點擊查看個人部屬分數狀況
                                if (member.is_admin) {
                                    $(rowLi).on('click', '.btn-feedbk', function() {
                                        var thisBtn = this;
                                        var fbkId = $(this).data('fbk-id');
                                        API.getYearlyFeedbackList({ year: getAPI.chatYear, feedback_id: fbkId }).then(function(e) {
                                            var result = API.format(e);
                                            if (result.is) {
                                                var data = result.get();
                                                var fbkData = data.list[0];
                                                var fbkTitle = data.choice;

                                                $('#RvChart').parent().find('.fbk-model').remove();
                                                $('#RvChart').after(`<div class="fbk-model feedback-card"><div class="first row" style="padding: 20px 0;"></div></div>`);

                                                for (var fd in fbkTitle) {
                                                    var loc = fbkTitle[fd];
                                                    $('#RvChart').parent().find('.fbk-model .first').append('<div class="col s1 l1"><div class="score">' + fbkData['multiple_choice_json'][loc.id] + '</div><div class="subtitle">' + loc.title + '</div></div>');
                                                    if (fd == 0) { $('#RvChart').parent().find('.col').addClass('offset-s1 offset-l1') }
                                                }
                                                ts.q('.fbk-model').css({
                                                    'z-index': 1999
                                                });
                                            }
                                        });
                                    });
                                }
                                sheet++;
                            }
                        }

                        // 年考評個人單組織圖
                        if (!getAPI.stepProcessing.feedback) {
                            var personalReport = thisUnitInStaff[staff]._report;
                            var personalStatus = thisUnitInStaff[staff]._status_code;

                            if (personalReport) {
                                var row = contextmenu.append($('<li data-num=' + staff + ' data-id=' + thisUnitInStaff[staff].id + ' title=' + thisUnitInStaff[staff].name + thisUnitInStaff[staff].name_en + '>' + thisUnitInStaff[staff].name + ' ' + thisUnitInStaff[staff].name_en + '</li>'));
                                var rowLi = $(row.find('[data-id=' + thisUnitInStaff[staff].id + ']'));
                                rowLi.prepend('<div class="dots"><div class="dot" data-status="' + thisUnitInStaff[staff]._status_code + '"></div></div>');
                                rowLi.prepend('<button class="personal-downexcel" data-staff-id=' + thisUnitInStaff[staff].id + '>下載</button>');

                                // 管理者的作廢功能
                                if (member.is_admin && getAPI.config.processing < 6) {
                                    if (personalReport.enable) {
                                        rowLi.prepend('<button class="report-void" data-report-id=' + thisUnitInStaff[staff]['_report'].id + '>作廢</button>');
                                    } else {
                                        rowLi.prepend('<button class="report-recovery" data-report-id=' + thisUnitInStaff[staff]['_report'].id + '>取消作廢</button>');
                                    }

                                    $(rowLi).on('click', '.report-void', function() {
                                        var btn = this;
                                        var repId = $(this).data('report-id');
                                        API.setAssessmentCancel({ assessment_id: repId }).then(function(e) {
                                            var result = API.format(e);
                                            if (result.is) {
                                                initial();
                                                Materialize.toast('作廢成功!', 4000);
                                                var staffTarget = $(btn).parent().data('num');

                                                $(btn).after('<button class="report-recovery" data-report-id=' + repId + '>取消作廢</button>');
                                                $(btn).parent().find('.dot').attr('data-status', 0);
                                                $(btn).parent().find('.dot').css('background', 'linear-gradient(white, #ff7272)');
                                                $(btn).remove();
                                            } else {
                                                swal("作廢失敗", result.get(), "error");
                                            }
                                        });
                                    });

                                    $(rowLi).on('click', '.report-recovery', function() {
                                        var btn = this;
                                        var repId = $(this).data('report-id');
                                        API.setAssessmentCancel({ assessment_id: repId, enable: 1 }).then(function(e) {
                                            var result = API.format(e);
                                            if (result.is) {
                                                initial();

                                                var staffTarget = $(btn).parent().data('num');
                                                switch (thisUnitInStaff[staffTarget]._status_code) {
                                                    case 1:
                                                        $(btn).parent().find('.dot').css('background', 'linear-gradient(white, #bbdefb)');
                                                        break;
                                                    case 2:
                                                        $(btn).parent().find('.dot').css('background', 'linear-gradient(white, #ffd54f)');
                                                        break;
                                                    case 3:
                                                        $(btn).parent().find('.dot').css('background', 'linear-gradient(white, #2fa22d)');
                                                        break;
                                                }
                                                $(btn).after('<button class="report-void" data-report-id=' + repId + '>作廢</button>');
                                                $(btn).parent().find('.dot').attr('data-status', thisUnitInStaff[staffTarget]._status_code);
                                                $(btn).remove();
                                                Materialize.toast('取消作廢成功!', 4000);
                                            } else {
                                                swal("取消作廢失敗", result.get(), "error");
                                            }
                                        });
                                    });
                                }

                                // 個人單的狀態
                                switch (personalStatus) {
                                    case 0:
                                        $(rowLi).find('.dot').css('background', 'linear-gradient(to bottom, white, #ff7272)');
                                        break;
                                    case 1:
                                        $(rowLi).find('.dot').css('background', 'linear-gradient(to bottom, white, #bbdefb)');
                                        break;
                                    case 2:
                                        $(rowLi).find('.dot').css('background', 'linear-gradient(to bottom, white, #ffd54f)');
                                        break;
                                    case 3:
                                        $(rowLi).find('.dot').css('background', 'linear-gradient(to bottom, white, #2fa22d)');
                                        break;
                                }
                            }
                        }
                    }
                    $(function() {
                        contextmenu.q('#search').keyup(function() {
                            var matches = $(contextmenu).find('li:contains(' + $(this).val() + ') ');
                            $(contextmenu).find('li').not(matches).hide();
                            matches.show();
                        });

                    });

                    var blockWidth = $('.year-chat-menu').width();
                    if ((blockWidth + e.pageX) > windowWidth) {
                        contextmenu.removeAttr("style");
                        contextmenu.css({ left: (e.pageX - 230), top: e.pageY });
                    }
                }
            });
            $('#slide-out').off('click', clickToggle).on('click', clickToggle);
            $('#main').off('click', clickToggle).on('click', clickToggle);
        }

        function detectionDivision() {
            for (var g in getAPI.lv['2']) {
                var detec = getAPI.lv['2'][g];
                if (!detec['_division'] || detec['_division'].processing < 3) { return getAPI.stepProcessing.ceo = false; }
            }
            return getAPI.stepProcessing.ceo = true;
        }

        function loadMap() {
            var sortArrayId = [];
            ts.q('#RvChart').empty();
            ts.q('.back-btn').hide();
            detectionDivision();

            if (getAPI.stepProcessing.feedback) {
                ts.q(".stepper-header").find(".stepper-step.one").removeClass('inactive').addClass('active');
                ts.q(".rv-chart-info.one").css('display', 'block');
            }

            if (getAPI.stepProcessing.personal) {
                ts.q(".stepper-header").find(".stepper-step.two").removeClass('inactive').addClass('active');
                ts.q(".rv-chart-info.two").css('display', 'block');
                ts.q(".exclusive-admin").css('display', 'block');
            }

            if (getAPI.stepProcessing.division && !getAPI.stepProcessing.ceo) {
                ts.q(".stepper-header").find(".stepper-step.three").removeClass('inactive').addClass('active');
                ts.q(".rv-chart-info.three").css('display', 'block');
                ts.q(".exclusive-admin").css('display', 'block');
            }

            if (getAPI.stepProcessing.ceo && !getAPI.stepProcessing.finish) {
                ts.q(".stepper-header").find(".stepper-step.four").removeClass('inactive').addClass('active');
                ts.q(".rv-chart-info.four").css('display', 'block');
                ts.q(".exclusive-admin").css('display', 'block');
            }

            if (getAPI.stepProcessing.finish) {
                ts.q(".stepper-header").find(".stepper-step.five").removeClass('inactive').addClass('active');
                ts.q(".rv-chart-info.two").css('display', 'block');
                ts.q(".exclusive-admin").css('display', 'block');
            }

            for (var a in getAPI.lv) {
                var eachLv = getAPI.lv[a];
                for (var el in eachLv) {
                    var eachUnit = eachLv[el];
                    var $root = ts.q('[data-department-id=' + eachUnit['upper_id'] + ']');

                    // 產生每個樣版的DOM
                    var newDOM = template.replace(/\{([\w\d]+?)\}/ig, function(match, param1) {
                        var key = param1;
                        return eachUnit[key];
                    });

                    var dom = $(newDOM);
                    var unitBlock = dom.q('[data-unitid]');

                    // Lv3的時候換樣式
                    if (eachUnit['lv'] == 3) { dom.addClass('nav-row'); }

                    // 登入者 = 能看到的組別 or 超級管理員
                    if (eachUnit['manager_staff_id'] == member.id || member.is_admin) { dom.addClass('active'); }

                    // key:組別ID value:staff Obj
                    if (eachUnit['_staff']) {
                        dictionaryUnitStaff[eachUnit['id']] = eachUnit['_staff'];
                    }

                    $root.append(dom);
                    sortArrayId.push(eachUnit);

                    if (getAPI.stepProcessing.feedback && getAPI.config.processing != 0) {
                        feedbackFN(unitBlock, eachUnit);
                    }
                    if (getAPI.stepProcessing.personal || getAPI.stepProcessing.finish) {
                        personalFN(unitBlock, eachUnit);
                    }
                    if (getAPI.stepProcessing.division || getAPI.stepProcessing.ceo && !getAPI.stepProcessing.finish) {
                        divisionFN(unitBlock, eachUnit);
                    }
                    if (eachUnit.status_code != 0 && eachUnit['_report_total'] != 0) {
                        if (eachUnit['_staff'] || eachUnit['lv'] == 2) {
                            unitBlock.attr('title', '點擊右鍵查看');
                            unitBlock.hover(
                                function() { $(this).addClass('hover-prompt') },
                                function() { $(this).removeClass('hover-prompt') }
                            );
                        }
                        rightClick(unitBlock, eachUnit);
                    }

                    responsiveWidth();
                    // 部長單位被點擊
                    // if (eachUnit['lv'] == 2 && getAPI.config.processing < 6) {
                    //     unitBlock.click(function() {
                    //         var ubthis = this;
                    //         var pid = ts.q(ubthis).data("process-id");
                    //         Anim('#RvChart', 'zoomOut').then(function() {
                    //             $('#RvChart').hide();
                    //             localChat(pid);
                    //         });
                    //     });
                    // }
                }
            }

            for (var sai in sortArrayId) {
                var saId = sortArrayId[sai];
                departmentSort(saId);
            }
        }

        function localChat(id) {
            $('.rv-chart').append('<ul id="RvLocalChart" data-department-id="1"></ul>');
            ts.q('#RvLocalChart').empty();
            ts.q('.back-btn').show();
            ts.q('.back-btn').click(function() {
                ts.q('#RvChart').show();
                ts.q('#RvLocalChart').remove();
                ts.q('.back-btn').hide();
                loadMap();
            })
            for (var l in getAPI.lv) {
                var locEachLv = getAPI.lv[l];
                for (var locel in locEachLv) {
                    var localEachUnit = locEachLv[locel];
                    var $localRoot = ts.q('[data-department-id=' + localEachUnit['upper_id'] + ']');

                    if (localEachUnit['path_department'].indexOf(id) == -1) { continue; }

                    var localNewDOM = template.replace(/\{([\w\d]+?)\}/ig, function(match, param1) {
                        var key = param1;
                        return localEachUnit[key];
                    });

                    var localDom = $(localNewDOM);
                    var localUnitBlock = localDom.q('[data-unitid]');
                    $localRoot.append(localDom);

                    // 登入者 = 能看到的組別 or 超級管理員
                    if (localEachUnit['manager_staff_id'] == member.id || member.is_admin) { localDom.addClass('active'); }

                    if (getAPI.stepProcessing.feedback && getAPI.config.processing != 0) {
                        feedbackFN(localUnitBlock, localEachUnit);
                    }

                    if (getAPI.stepProcessing.personal || getAPI.stepProcessing.finish) {
                        personalFN(localUnitBlock, localEachUnit);
                    }

                    if (getAPI.stepProcessing.division) {
                        divisionFN(localUnitBlock, localUnitBlock);
                    }

                    if (getAPI.config.processing < 6 || getAPI.stepProcessing.finish) {
                        rightClick(localUnitBlock)
                    }
                }
            }
            $('#RvLocalChart').css('display', 'flex').css('justify-content', 'center');
        }

        // animate.css動畫特效
        function Anim(selector, x) {
            return new Promise(function(resolve) {
                $(selector).removeClass().addClass(x + ' animated').one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function() {
                    $(this).removeClass();
                    resolve('ok');
                });
            });
        }

        function clickToggle() {
            contextmenu.detach();
            $('#RvChart').parent().find('.fbk-model').remove();
        }

        // 排序功能：改為依部門id來排序
        function departmentSort(deparment) {
            var self = ts.q('[data-department-id=' + deparment.id + ']');
            var li = self.children();
            if (li.length == 0) { return; }
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

        // 自適應螢幕大小
        function responsiveWidth() {
            var unitWidth = $("#RvChart [data-department-id=1]>li");
            var totalWidth = 0;
            unitWidth.each(function(index) {
                totalWidth += parseInt($(this).width(), 10);
            });
            totalWidth += 60;
            ts.q('.rv-chart').css('min-width', totalWidth);
        }
    });
});