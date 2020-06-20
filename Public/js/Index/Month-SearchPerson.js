var $MonthSearchPerson = $('#MonthSearchPerson').generalController(function() {
    var ts = this;
    var current = $.ym.get();
    var getYear = current.year;
    var thisyear = new Date().getFullYear();
    var thismonth = new Date().getMonth() + 1;
    var yearStart = ts.q("#getYearStart").empty();
    // var yearEnd = ts.q("#getYearEnd").empty();
    var monthStart = ts.q("#getMonthStart");
    var monthEnd = ts.q("#getMonthEnd");
    var monthBlock = ts.q('.month-pinfo');
    var monthScoreArr = [];
    var monthScoreData = [];
    var leader_analysis = [];
    var leader_analysis_score = [];
    var normal_analysis = [];
    var normal_analysis_score = [];
    // bar chart color set
    var barColor = {
        1: 'rgba(255, 99, 132, 0.2)', // red
        2: 'rgba(54, 162, 235, 0.2)', // blue
        3: 'rgba(255, 206, 86, 0.2)', // yellow
        4: 'rgba(75, 192, 192, 0.2)', // green
        5: 'rgba(153, 102, 255, 0.2)', //purple
        6: 'rgba(255, 159, 64, 0.2)', //orange
        7: 'rgba(245, 22, 177, 0.2)', // pink
        8: 'rgba(9, 158, 15, 0.2)', // dark green
        9: 'rgba(0,0,255,0.2)', // blue purple
        10: 'rgba(255,255,0,0.3)', // bright yellow
        11: 'rgba(14, 224, 214, 0.2)', // light green
        12: 'rgba(169, 4, 210, 0.3)' // purple2
    };
    var barColorBorder = {
        1: 'rgba(255,99,132,1)',
        2: 'rgba(54, 162, 235, 1)',
        3: 'rgba(255, 206, 86, 1)',
        4: 'rgba(75, 192, 192, 1)',
        5: 'rgba(153, 102, 255, 1)',
        6: 'rgba(255, 159, 64, 1)',
        7: 'rgba(255,99,132,1)',
        8: 'rgba(54, 162, 235, 1)',
        9: 'rgba(255, 206, 86, 1)',
        10: 'rgba(75, 192, 192, 1)',
        11: 'rgba(153, 102, 255, 1)',
        12: 'rgba(255, 159, 64, 1)'
    }
    //主管能力分析百分率
    var anaLeader = {
        quality: '目標達成率',
        target: '工作品質',
        method: '工作方法',
        error: '出錯率',
        backtrack: '進度追蹤',
        planning: '企劃能力',
        execute: '執行力',
        decision: '判斷力',
        resilience: '應變能力',
        attendance: '出勤率',
        attendance_members: '組員出勤'
    };
    var anaNormal = {
        quality: '工作品質',
        completeness: '工作績效',
        responsibility: '責任感',
        cooperation: '配合度',
        attendance: '出席率'
    }

    function init() {
        yearStart.yearSet();
        yearStart.change(function() {
            current.year = this.value;
            tmpYM[0] = current.year;
            $.ym.save();
        });
        for (var m = 1; m <= 12; m++) {
            monthStart.append('<option value="' + m + '">' + m + '月</option>');
            monthEnd.append('<option value="' + m + '">' + m + '月</option>');
        }
    }
    init();
    //20171113  存到 storange

    var tmpYM = window.localStorage ? localStorage.getItem('MonthSearchPerson_YM') : null;
     //console.log(tmpYM)
   // check tmpYM 是否為old 4 NUMBER
   if(tmpYM != null){
    //console.log(tmpYM.split('|'))
    tmpYM=tmpYM.replace(tmpYM.split('|')[0],current.year);
     tmpYM = (tmpYM.toString().split('|').length ==3 ? tmpYM.split('|') : [thisyear, thismonth, thismonth]);
   }else{
    tmpYM =  [thisyear, thismonth, thismonth];
   }
   current.year = tmpYM[0] ;
   console.log(tmpYM[0])
   $.ym.save();

    ts.onLogin(function(member) {
        // console.log(JSON.parse(JSON.stringify(member)))
        var vm = new Vue({
            el: '.rv-month-search',
            data: {
                member: member,
                year_start:current.year ||  tmpYM[0],
                //year_end: tmpYM[1] || thisyear,
                month_start: tmpYM[1] || thismonth,
                month_end: tmpYM[2] || thismonth,
                personName: '',
                personID: '',
                under_staff: '',
                leader_ana: [],
                leader_ana_score: [],
                ExceptionData: 1, // 是否有非正常上班時間的值
                thePerson: {
                    staff_info: {
                        staff_no: '',
                        name: '', //名稱
                        name_en: '',
                        department_id: '', //單位
                        department_name: '',
                        department_code: '',
                        title: '', //職類
                        post: '',
                        status: '',
                        staff_stay: [],
                        first_day: '' //到職日
                    },
                    attendance_info: {
                        basic: {
                            late: {},
                            early: {},
                            nocard: {},
                            forgetcard: {},
                            leave: {},
                            paysick: {},
                            sick: {},
                            absent: {},
                            overtime: {},
                            relax: {},
                            working: {
                                total_hours: {},
                                total_days: {},
                                card_time_hours: {}
                            }
                        },
                        exception_date: {
                            date: {},
                            checkin_hours: {},
                            checkout_hours: {},
                            early: {},
                            late: {},
                            minute: {},
                            vocation_hours: {},
                            work_hours_total: {},
                            remark: {}
                        },
                    },
                    monthly_every: {},
                    monthly_info: {
                        addedValue: {},
                        analysis_leader: {
                            quality: '',
                            target: '',
                            method: '',
                            error: '',
                            backtrack: '',
                            planning: '',
                            execute: '',
                            decision: '',
                            resilience: '',
                            attendance: '',
                            attendance_members: ''
                        },
                        analysis_normal: {
                            quality: '',
                            completeness: '',
                            responsibility: '',
                            cooperation: '',
                            attendance: ''
                        },
                        average: '',
                        mistake: ''
                    },
                },
                monthEvery: {},
                monthdata: '',
            },
            created: function() {
                var vthis = this;
                // get under staff
                var data = member.is_admin == 1 ? { model: 'admin' } : {};
                API.getUnderStaff(data).then(function(e) {
                    var result = API.format(e).res();
                    // console.log(result);
                    vthis.under_staff = [];
                    // for (var i in result) {
                    //     var curr = result[i];
                    //     console.log(curr);
                    //     if (curr.lv > member.lv) {
                    //         vthis.under_staff.push(curr);
                    //     }
                    // }
                    vthis.under_staff = result;
                    vthis.under_staff.unshift(member);
                })

                $(window).on("click", function(e) {
                    if (e.target.id != "autoContent" && !ts.q('.search-input-article').find(e.target).length) {
                        ts.q('.autocomplete-content').addClass('off');
                    }
                })
            },
            methods: {
                getPerosonDate: function(isclear) {
                    // var vthis = this;
                    if (isclear) { this.personName = ''; }
                    ts.q('.autocomplete-content').removeClass('off');
                    ts.q('.autocomplete-content').addClass('on');

                },
                checkYM: function() {
                    var vthis = this;
                    var starD = vthis.year_start + '-' + vthis.month_start;
                    var endD = vthis.year_start + '-' + vthis.month_end;
                    var starDtime = new Date(starD).getTime();
                    var endDtime = new Date(endD).getTime();

                    if (typeof vthis.personName != 'string') { vthis.personName = ''; }

                    if (starDtime > endDtime) {
                        return sweetAlert("Error", "起迄時間錯誤", "error");
                    }

                    //20171113
                    if (window.localStorage) {
                        localStorage.setItem('MonthSearchPerson_YM', [vthis.year_start, vthis.month_start, vthis.month_end].join('|'));
                    }

                    if (vthis.personName.length != 0) { vthis.SelectPerson(); } else {
                        ts.q('.month-pinfo').removeClass('moveleft');
                    }
                },
                showMonth: function() {
                    ts.q('.mcard').show();
                    //this.smallLeft();
                    //ts.q('#monChart').addClass('m100');
                    //ts.q('#radarChartNormal').addClass('m100');
                    //ts.q('.chartjs-render-monitor').css({ 'width': '100%', 'height': '100%' });
                },
                clickLeft: function(e) {
                    if (e.target.id != 'monChart') {
                        this.largeLeft();
                    }
                },
                smallLeft: function() {
                    ts.q('.left-chart').addClass('small');
                    ts.q('.right-content').addClass('large');
                },
                largeLeft: function() {
                    ts.q('.left-chart').removeClass('small')
                    ts.q('.right-content').removeClass('large');
                },
                SelectPerson: function(pid, name, name_en) {
                    var vthis = this;
                    if (pid) {
                        vthis.personName = name + ' ' + name_en;
                        vthis.personID = pid;
                    }

                    ts.q('.autocomplete-content').addClass('off');
                    //ts.q('.month-pinfo').addClass('on');
                    var data = {
                        year_start: vthis.year_start,
                        year_end: vthis.year_start,
                        month_start: vthis.month_start,
                        month_end: vthis.month_end,
                        staff_id: vthis.personID
                    }

                    API.getDetailMonthlyByPerson(data).then(function(e) {
                        var result = API.format(e);
                        if (result.is) {
                            var person = result.res();
                            ts.q('#NoData').hide();
                            vthis.thePerson.staff_stay =person.staff_stay;
                            vthis.thePerson.staff_info = person.staff_info;
                            vthis.thePerson.attendance_info = person.attendance_info;
                            vthis.thePerson.monthly_every = person.monthly_every;
                            vthis.thePerson.monthly_info = person.monthly_info;
                            // console.log(JSON.parse(JSON.stringify(person)))


                            function hasNull(obj) {
                                for (var i in obj) {
                                    if (obj[i] == null) {
                                        obj[i] = '';
                                    }
                                    return false;
                                }
                            }

                            // 將出缺勤中的null的值改為空值
                            hasNull(vthis.thePerson.attendance_info.exception_date);

                            // 判斷是否有非正常上班時間的資料
                            if (vthis.thePerson.attendance_info.exception_date == null || vthis.thePerson.attendance_info.exception_date.length == 0) {
                                vthis.ExceptionData = 0;
                            } else {
                                vthis.ExceptionData = 1;
                            }

                            // 每月資料整理 monthly_every
                            // console.log(person.monthly_every)
                            var monthly = person.monthly_every;
                            var sortedMonth = {};
                            var monLabel = [];
                            var addValue = [];
                            var monTotal = [];

                            for (var m in monthly) {
                                var mon = monthly[m];
                                // var mm = sortedMonth[mon];
                                if (sortedMonth[mon['month']]) {
                                    mon['month'] = '_' + mon['month'];
                                    sortedMonth[mon['month']] = mon;
                                } else {
                                    sortedMonth[mon['month']] = mon;
                                }
                            }

                            vthis.monthEvery = sortedMonth;
                            // console.log(JSON.parse(JSON.stringify(vthis.monthEvery)))
                            // console.log(Object.keys(sortedMonth))
                            for (i in sortedMonth) {
                                if (sortedMonth[i]['releaseFlag'] == 'Y' && sortedMonth[i]['exception'] == 0) {
                                    monLabel.push(sortedMonth[i]['month']) //圖表月份
                                    monTotal.push(sortedMonth[i]['total']); // 圖表月分數
                                    addValue.push(sortedMonth[i]['addedValue']); //圖表特殊貢獻
                                }
                            }
                            // console.log(monLabel);
                            vthis.monthdata = sortedMonth.length;

                            //能力分析
                            // leader 能力分析資料
                            leader_analysis.length = 0;
                            leader_analysis_score.length = 0;
                            leader_analysis = (Object.keys(person.monthly_info.analysis_leader));
                            leader_analysis_score = (Object.values(person.monthly_info.analysis_leader));
                            // 測試資料
                            // leader_analysis_score =['80','60','56','89','60','80','70','55','40','60','85'];

                            // normal 能力分析資料
                            normal_analysis.length = 0;
                            normal_analysis_score.length = 0;
                            normal_analysis = (Object.keys(person.monthly_info.analysis_normal));
                            normal_analysis_score = (Object.values(person.monthly_info.analysis_normal));

                            // 測試資料
                            // normal_analysis_score =['80','60','56','89','60','80'];

                            function radarChartLeader() {
                                ts.q('#radarChartLeader').remove();
                                ts.q('#radarLeader').append('<canvas id="radarChartLeader" style="width:50vw;height:50vh"></canvas>');

                                var presets = window.chartColors;
                                var utils = Samples.utils;
                                var inputs = {
                                    min: 8,
                                    max: 16,
                                    count: 11,
                                    decimals: 2,
                                    continuity: 1
                                };

                                var data = {
                                    labels: Object.values(anaLeader), // 放置leader的能力分析項目
                                    datasets: [{
                                        backgroundColor: utils.transparentize(presets.purple),
                                        borderColor: presets.purple,
                                        data: leader_analysis_score, // 放罝leader的能力分數
                                        label: 'Leader Analysis',
                                        pointBorderWidth: 5
                                    }]
                                };

                                var options = {
                                    maintainAspectRatio: true,
                                    spanGaps: false,
                                    elements: {
                                        line: {
                                            tension: 0.000001
                                        }
                                    },
                                    scale: {
                                        ticks: {
                                            beginAtZero: true,
                                            max: 100
                                        }
                                    },
                                    plugins: {
                                        filler: {
                                            propagate: false
                                        },
                                        samples_filler_analyser: {
                                            target: 'chart-analyser'
                                        }
                                    }
                                };

                                var chart = new Chart('radarChartLeader', {
                                    type: 'radar',
                                    data: data,
                                    options: options
                                });
                            }

                            function radarChartNormal() {
                                ts.q('#radarChartNormal').remove();
                                ts.q('#radarNormal').append('<canvas id="radarChartNormal" style="width:50vw;height:50vh"></canvas>');
                                var presets = window.chartColors;
                                var utils = Samples.utils;
                                var inputs = {
                                    min: 8,
                                    max: 16,
                                    count: 11,
                                    decimals: 2,
                                    continuity: 1
                                };

                                var data = {
                                    labels: Object.values(anaNormal), // 放置normal的能力分析項目
                                    datasets: [{
                                        backgroundColor: utils.transparentize(presets.blue),
                                        borderColor: presets.blue,
                                        data: normal_analysis_score, // 放罝normal的能力分數
                                        label: '一般人員能力分數',
                                    }]
                                };

                                var options = {
                                    maintainAspectRatio: true,
                                    spanGaps: false,
                                    scale: {
                                        ticks: {
                                            beginAtZero: true,
                                            max: 100
                                        }
                                    },
                                    elements: {
                                        line: {
                                            tension: 0.000001
                                        }
                                    },
                                    plugins: {
                                        filler: {
                                            propagate: false
                                        },
                                        samples_filler_analyser: {
                                            target: 'chart-analyser'
                                        }
                                    }
                                };

                                var chart = new Chart('radarChartNormal', {
                                    type: 'radar',
                                    data: data,
                                    options: options
                                });
                            }

                            if (leader_analysis_score != '') {
                                radarChartLeader();
                                ts.q('#radarChartLeader').fadeIn(500);
                            } else {
                                ts.q('#radarChartLeader').fadeOut(500);
                            }
                            if (normal_analysis_score != '') {
                                radarChartNormal();
                                ts.q('#radarChartNormal').fadeIn(500);
                            } else {
                                ts.q('#radarChartNormal').fadeOut(500);
                            }

                            function showScoreChart() {
                                ts.q('#monChart').remove();
                                ts.q('#mChart').append('<canvas id="monChart" style="width:100vw;height:100vh"></canvas>');
                                if (vthis.monthdata != 0) {
                                    var ctx = document.getElementById("monChart").getContext('2d');
                                    var myChart = new Chart(ctx, {
                                        type: 'bar',
                                        data: {
                                            labels: monLabel,
                                            datasets: [{
                                                label: '月總分',
                                                data: monTotal,
                                                backgroundColor: [
                                                    barColor['2'],
                                                    barColor['2'],
                                                    barColor['2'],
                                                    barColor['2'],
                                                    barColor['2'],
                                                    barColor['2'],
                                                    barColor['2'],
                                                    barColor['2'],
                                                    barColor['2'],
                                                    barColor['2'],
                                                    barColor['2'],
                                                    barColor['2'],
                                                ],
                                                borderColor: [
                                                    barColorBorder['2'],
                                                    barColorBorder['2'],
                                                    barColorBorder['2'],
                                                    barColorBorder['2'],
                                                    barColorBorder['2'],
                                                    barColorBorder['2'],
                                                    barColorBorder['2'],
                                                    barColorBorder['2'],
                                                    barColorBorder['2'],
                                                    barColorBorder['2'],
                                                    barColorBorder['2'],
                                                    barColorBorder['2'],
                                                ],
                                                borderWidth: 1
                                            }, { // second bar info
                                                label: '特殊貢獻',
                                                data: addValue,
                                                backgroundColor: [
                                                    barColor['5'],
                                                    barColor['5'],
                                                    barColor['5'],
                                                    barColor['5'],
                                                    barColor['5'],
                                                    barColor['5'],
                                                    barColor['5'],
                                                    barColor['5'],
                                                    barColor['5'],
                                                    barColor['5'],
                                                    barColor['5'],
                                                    barColor['5'],
                                                ],
                                                borderColor: [
                                                    barColorBorder['5'],
                                                    barColorBorder['5'],
                                                    barColorBorder['5'],
                                                    barColorBorder['5'],
                                                    barColorBorder['5'],
                                                    barColorBorder['5'],
                                                    barColorBorder['5'],
                                                    barColorBorder['5'],
                                                    barColorBorder['5'],
                                                    barColorBorder['5'],
                                                    barColorBorder['5'],
                                                    barColorBorder['5'],
                                                ],
                                                borderWidth: 1
                                            }, ]
                                        },
                                        options: {
                                            scales: {
                                                yAxes: [{
                                                    ticks: {
                                                        beginAtZero: true,
                                                        max: 100,
                                                        min: 0
                                                    },
                                                    gridLines: {
                                                        offsetGridLines: true
                                                    }
                                                }]
                                            }
                                        }
                                    });

                                    document.getElementById("monChart").onclick = function(evt) {
                                        var activePoints = myChart.getElementsAtEvent(evt);
                                        var firstPoint = activePoints[0];
                                        if (!firstPoint) { return; }
                                        var label = myChart.data.labels[firstPoint._index];
                                        var value = myChart.data.datasets[firstPoint._datasetIndex].data[firstPoint._index];
                                        if (firstPoint !== undefined) {
                                            ts.q('.mcard').hide();
                                            ts.q("#mm" + label).show();
                                            //vthis.smallLeft();
                                            //ts.q('#monChart').addClass('m100');
                                        }

                                    };
                                }

                            }
                            showScoreChart();
                            if (person.monthly_every.length == 0) {
                                ts.q('.month-pinfo').removeClass('moveleft');
                                ts.q('#NoData').show();
                            } else {
                                ts.q('.month-pinfo').addClass('moveleft');
                            }
                            // if (!!person.staff_info) {
                            //     ts.q('.month-pinfo').addClass('moveleft');
                            // } else {
                            //     ts.q('.month-pinfo').removeClass('moveleft');
                            //     ts.q('#NoData').show();
                            // }

                            // 無月份資料時顯示empty
                            if (person.monthly_every.length == 1 && person.monthly_every[0].releaseFlag == 'N') {
                                ts.q('.month-pinfo').removeClass('moveleft');
                                ts.q('#NoData').show();
                            }

                        } else {
                            Materialize.toast('錯誤，原因:' + e.msg, 2000);
                            ts.q('.month-pinfo').removeClass('moveleft');
                            ts.q('#NoData').show();
                        }
                    });
                },
                collapsible: function() {
                    var tm = this,
                        el = ts.q(tm.$el);
                    el.q('.collapsible').collapsible({
                        onOpen: function(e) {
                            // console.log(e);
                            return;
                            var card = e.closest('.mcard');
                            if (card.length == 0) { return; }
                            // var top = card.offset().top - el.offset().top;
                            var top = card.position().top;
                            tm.hb.animate({
                                scrollTop: top
                            }, 500);
                        }
                    });
                }
            },
            mounted: function() {
                ts.q(this.$el).q('ul.tabs').tabs();
                this.hb = ts.q('.detail-content');
                this.collapsible();
                ts.q('#monChart').addClass('m100');

            },
            updated: function() {
                this.collapsible();
                //ts.q(this.$el).q('.mcard').hide();
                ts.q('#monChart').addClass('m100');
            }
        });
    });

});