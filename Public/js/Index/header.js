 var current = $.ym.get();
 var undoYear = '';

 if (!$.fn.clickToggle) {
     $.fn.clickToggle = function(func1, func2) {
         var funcs = [func1, func2];
         this.data('toggleclicked', 0);
         this.click(function() {
             var data = $(this).data();
             var tc = data.toggleclicked;
             $.proxy(funcs[tc], this)();
             data.toggleclicked = (tc + 1) % 2;
         });
         return this;
     };
 }

 // 選擇year

 $.fn.yearSet = function() {
     var thisYear = new Date().getFullYear();
     var thisMonth = new Date().getMonth()+1;
     if(thisMonth ==12){thisYear = thisYear+1;}
     for (i = thisYear ; i >= API.create.year; i--) {
         $(this).append('<option value="' + i + '">' + i + '年</option>');
     }
     $(this).val(current.year).attr('selected');
 }


 var $Header = $('#Header').generalController(function() {
     var ts = this;
     var mySidenav = ts.q('mySidenav');
     var slideout = ts.q('#slide-out');

     ts.onLogin(function(member) {
         //登出按鈕
         ts.on('click', '[data-toggle=logout]', function(e) {
             e.preventDefault();
             API.logout().then(function() {
                 // location.reload();
                 API.go('/');
             });
         });
         //增加用到的資料
         member['undo'] = [];
         member['undo_info'] = { count: 0, nosee: 0 };
         member['is_ceo'] = member['_department_lv'] == 1 && member['is_leader'] == 1;
         //會員資訊樣板
         var vu = new Vue({
             el: ts.q('[data-template="header-right"]')[0],
             data: member,
             methods: {
                 toggleNotification: function() {
                     this.undo_info.nosee = 0;
                     if (this.undo && this.undo.length > 0) {
                         $(this.$el).q('.right-notification').toggleClass('show');
                     }
                 },
                 go: function(undo_value) {
                     var uri = undo_value.uri;
                     slideout.q('a[href]').each(function() {
                         var $t = $(this);
                         if ($t.attr('href').match(uri)) {
                             $t.click();
                             return false;
                         }
                     });
                     $(this.$el).q('.right-notification').removeClass('show');
                 }
             }
         });


         var vusilde = new Vue({
             el: slideout[0],
             data: member,
             mounted: function() {
                 slideout = $(this.$el);
                 hanberMenu();
                 ts.q(this.$el).q('.collapsible').collapsible();
             }
         })

         //左邊連結

         ts.on('click', '#slide-out a[href]', function(e) {
             var $t = $(this),
                 href = $t.attr('href').replace(/\?.*/i, '');
             ts.q(".collapsible-body li").removeClass("active");
             // 移除overflow
             $('body').css({ 'overflow': '' });
             $t.parent().addClass("active");
             // var $header = $t.closest('.collapsible').q('.collapsible-header');
             // if(!$header.hasClass('active')){ $header.trigger('click.collapse'); }

             if (slideout.hasClass('slide-small')) { slideout.in(); }

             if (href.match(/Template\//i)) {
                console.log(href);
                 location.hash = API.encode(href);
                 e.preventDefault();
             };
         });
         //第一次讀取 active
         $(function() {
             var path = location.pathname.replace(API.ROOT, '');
             if (path.length <= 2) { path = '/index'; }
             var isMatch = false;
             ts.q(".collapsible-body li a").each(function(e) {
                 var $t = $(this),
                     href = $t.attr('href');
                 if (href && href.match(new RegExp(path))) {
                     $t.closest('.collapsible').q('.collapsible-header').trigger('click.collapse');
                     $t.parent().addClass("active");
                     isMatch = true;
                     return false;
                 }
             });
             if (!isMatch && member.is_admin != 1) { //沒有符合的 就跳第一個
                 var $t = ts.q(".collapsible-body li a").eq(0).click();
             }
         });
         //hook 這些 api
         API.hook('commitMonthly', function() { removeUndo('monthly'); });
         API.hook('rejectMonthly', function() { removeUndo('monthly'); });
         API.hook('commitYearlyFeedback', function() { removeUndo('feedback'); });
         API.hook('commitYearlyAssessment', function() { removeUndo('yearly_assessment'); });
         API.hook('rejectYearlyAssessment', function() { removeUndo('yearly_assessment'); });

         API.hook('commitDivisionZone', function() { removeUndo('yearly_division'); });
         API.hook('rejectDivisionZone', function() { removeUndo('yearly_division'); });

         function removeUndo(key) {
             for (var i in member['undo']) {
                 var loc = member.undo[i];
                 if (loc.key != key) { continue; }
                 loc.length--;
                 if (member['undo_info'].nosee > 0) { member['undo_info'].nosee--; };
                 if (loc.length == 0) { member['undo'].splice(i, 1); }
             }
         }

         //取得沒做的事情

         var myindex;
         var undothing = {};
         getIndex();

         function getIndex() { myindex = API.getMyIndex().then(myIndexSuccess).fail(getIndex); }


         function myIndexSuccess(e) {
             var f = API.format(e),
                 data = f.res(),
                 map = {
                     'feedback': ['問卷回饋', '/Year-Feedback'],
                     'monthly': ['月績效', '/index'],
                     'yearly_assessment': ['年度考評單', '/Year-Evaluation'],
                     'yearly_division': ['年度部門評核', '/Year-Evaluation']
                 };
             if (f.is) {
                 var undo = data.undo || {},
                     total = 0,
                     show = [],
                     count = 0;
                 for (var i in undo) {
                     var loc = undo[i],
                         tmp = {};
                     if (loc.length == 0) { continue; }
                     count += loc.length;
                     tmp.ary = loc;
                     tmp.title = map[i][0];
                     tmp.uri = map[i][1];
                     tmp.length = loc.length;
                     tmp.key = i;
                     show.push(tmp);
                 }
                 member['undo'] = show;
                 member['undo_info'].count = count;
                 member['undo_info'].nosee = count;

                 if (data.undo) {
                     if (['feedback', 'yearly_assessment', 'yearly_division'].indexOf(i) != -1) {
                         undoYear = data.ym.year ? data.ym.year : current.year;
                        // console.log(undoYear)
                     } else {
                         undoYear = current.year;
                         //console.log(undoYear)
                     }
                 } else {
                     undoYear = current.year;
                 }

                 // console.log(vu);
                 //有年沒完成 把年設到該年
                 var this_ym = $.ym.get();
                 if (data.ym.year && data.ym.year != this_ym.year) {
                     this_ym.year = data.ym.year;
                     $.ym.save();
                 }
             } else {
                 undoYear = current.year;
                 console.log(undoYear);
             }
         }

     });

     // 左側menu小於1280，變成小漢堡menu，click則打開menu
     function hanberMenu() {
         var slideMenu = slideout.css('display', 'block');
         var btnCollapse = ts.q('.button-collapse');
         slideMenu.in = function() {
             slideMenu.css('transform', 'translateX(-100%)');
             btnCollapse.data('toggleclicked', 0);
         }
         slideMenu.out = function() {
             slideMenu.css('transform', 'translateX(0%)');
         }
         btnCollapse.clickToggle(slideMenu.out, slideMenu.in);
         var timer,
             $w = $(window).resize(function() {
                 timer && clearTimeout(timer);
                 timer = setTimeout(resize, 200);
             });
         resize();

         function resize() {
             if ($w.width() >= 1281) {
                 slideMenu.out();
                 slideMenu.removeClass('slide-small');
             } else {
                 slideMenu.in();
                 slideMenu.addClass('slide-small');
             }
         }
     }


     ts.onShown(function() {
         // console.log(this);
     });

     ts.onHidden(function() {
         // console.log(this);
     });
 });