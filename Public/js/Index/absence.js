
var $Absence = $('#Absence').generalController(function() {
    var ts = this;
    var dataBlock = ts.q('.data-block');
    var noData = ts.q('.no-data');
    var dtime = ts.q('.dtime');

    var year = getParameterByName('year');
    var month = getParameterByName('month');
    var team = getParameterByName('team');
    var staff = getParameterByName('staff');
    dtime.append(year + '年' + month + '月');

    // get name function
    function getParameterByName(name, url) {
        if (!url) {
            url = window.location.href;
        }
        name = name.replace(/[\[\]]/g, "\\$&");
        var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
            results = regex.exec(url);
        if (!results) return null;
        if (!results[2]) return '';
        return decodeURIComponent(results[2].replace(/\+/g, " "));
    }

    var $window = $(window),
        bind = true;

    function gogo(table) {
        if(!$window.pos){
          $window.pos={ x: 0, y: 0, moveX: 0, now: 0 };
        }
        var pos = $window.pos;
        var scroll = ts.q('.scroll-x');

        var tw = table.width();
        var sw = scroll.width();
        pos.max = table.find('tr:first td[pos]').length - Math.floor( (sw-100) / 300);

        var rTimer;
        $window.off('resize',subResize).on('resize',subResize);
        function subResize(){
          if (rTimer) {
              clearTimeout(rTimer);
          }
          rTimer = setTimeout(function() {
              gogo(table);
          }, 150);
        }

        if (tw <= sw) {
            return;
        }
        
        if(!$window.mouseData){
          $window.mouseData={ can: false, barWidth:0, scrollWidth:0, sbar:0 };
        }
        var mouseData = $window.mouseData;
        mouseData.barWidth = Math.floor((sw / tw) * 100)
        mouseData.scrollWidth = 100 - mouseData.barWidth;
        mouseData.sbar = scroll.show().find('span').width(mouseData.barWidth + "%");
        
        var carry = 35;

        bind && table.find('tbody').on('mousedown', function(e) {
                mouseData.can = true;
                pos.x = e.pageX;
                pos.y = e.pageY;
                e.preventDefault();
            })
            .on('mouseup', function(e) {
                mouseData.can = false;
                // console.log(pos);
            })
            .on('mousemove', function(e) {
                if (!mouseData.can) {
                    return;
                }
                pos.moveX = e.pageX - pos.x;
                if (pos.moveX < -(carry)) {
                    pos.x = e.pageX;
                    if (pos.now >= pos.max) {
                        return;
                    }
                    table.find('td[pos=' + pos.now + ']').hide();
                    pos.now++;
                    mouseData.sbar.css('left', ((pos.now / pos.max) * mouseData.scrollWidth) + '%');
                    console.log(mouseData.scrollWidth);
                } else if (pos.moveX > carry) {
                    pos.x = e.pageX;
                    if (pos.now <= 0) {
                        return;
                    }
                    table.find('td[pos=' + (pos.now - 1) + ']').show();
                    pos.now--;
                    mouseData.sbar.css('left', ((pos.now / pos.max) * mouseData.scrollWidth) + '%');
                }
            }).css({
                'cursor': 'e-resize'
            });
        table.css({
            'min-width': '100%'
        });
        bind = false;

    }
    
    
    var submit = {
        year: year,
        month: month
    };
    if (team) {
        submit.team_id = team;
    }
    if (staff) {
        submit.staff_id = staff;
    }

    API.getAbsence(submit).then(function(data) {
        var rec = API.format(data).get();
        if (rec) {
            console.log(rec);
            
            //先找到所有日期 與 所有員工
            var time_1 = new Date();

            var allDate = {};
            var allStaff = [];
            var dayMapping = {
                0: '周日',
                1: '周一',
                2: '周二',
                3: '周三',
                4: '周四',
                5: '周五',
                6: '周六',
                7: '周日',
            }
            for (var i in rec) {
                var loc = rec[i];
                for (var n in loc._staff) {
                    var loc2 = loc._staff[n];
                    var attendance = loc2._attendance;
                    loc2.unit_code = i;
                    loc2.unit_name = loc.unit_name;

                    var attendance_map = {};
                    var x = 0;
                    while (attendance[x]) {
                        var date = attendance[x].date, worktime;
                        if (date && !allDate[date]) {
                            allDate[date] = {
                                mDate: date.replace(/^[\d]{3,4}\-/, '').replace('-', '/'),
                                day: dayMapping[new Date(date).getDay()]
                            }
                        }
                        var checkin = attendance[x]['checkin_hours']?parseInt(attendance[x]['checkin_hours'].replace(/\:.*/,'')):null;
                        var checkout = attendance[x]['checkout_hours']?parseInt(attendance[x]['checkout_hours'].replace(/\:.*/,'')):null;
                        if(checkin!=null && checkout!=null){
                          worktime = checkout - checkin;
                          if(worktime<0){worktime+=24;}
                          // console.log( date + ' | ' + loc2['name_en'] + ' : ' + worktime);
                          if(worktime<8 && attendance[x]['remark']==''){ attendance[x]['_sig']=1; }  //刷卡時間未滿 8小時
                        }
                        
                        attendance_map[date] = attendance[x];
                        x++;
                    }
                    loc2._attendance_map = attendance_map;
                    allStaff.push(loc2);

                }
            }
            // console.log(allDate);
            // console.log(allStaff);

            var time_2 = new Date();
            // console.log('Loop Parse Data Waste Time: '+(time_2 - time_1));
            var html = dataBlock.html();
            dataBlock.empty();

            html = html.replace(/\<.+?class\=\"(.?template)\"/gi, function($m, $a) {
                return '<' + $a;
            });

            ts.vue = new Vue({
                el: dataBlock[0],
                template: html,
                data: {
                    date: allDate,
                    staff: allStaff,
                    submitData: submit
                },
                methods: {
                    downloadExcel: function() {
                        API.downloadAbsence(this.submitData);

                    }
                },
                mounted: function() {
                    var time_3 = new Date();
                    console.log('Vue Render DOM Waste Time: '+(time_3 - time_1));
                    this.table = $(this.$el).find('table');
                    //this.table.css('min-width',this.table.width());
                    
                    this.table.q('.td-remark').each(function(i){
                      var $t = $(this);
                      $t.attr('title',$t.text());
                    });
                    
                    var time_4 = new Date();
                    console.log('Remark Title Attr Waste Time: '+(time_4 - time_3));
                    noData.hide();
                    dataBlock.show();
                    
                    gogo(this.table);
                    var time_5 = new Date();
                    console.log('At All Go Waste Time: '+(time_5 - time_4));
                }
            });
        } else {

            dataBlock.hide();
            noData.show();
        }
    });
});

