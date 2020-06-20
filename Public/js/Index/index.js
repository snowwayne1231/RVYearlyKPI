var $Html = $('html').generalController(function() {
    var ts = this;
    var require = {
        main: $Main
    }

    this.onLogin(function() {
        //每日第一次登入的 admin 檢查組織結構
        // var adminCheckDate = localStorage.getItem('rv-checkout');
        // var day = new Date().getDate();
        // if(adminCheckDate!=day){
        // API.checkDepartment();
        // localStorage.setItem('rv-checkout',day);
        // }
        // location.hash="";
        $(window).on('hashchange', hashPointOut);

    });

    function hashPointOut() {
        var code = API.getCode();
        if (code) {
            API.clearPassenger();
            $.get(code).then(function(htm) {
                require.main.receive(htm);
            }).fail(function() {
                swal("Error", "加載錯誤!");
            });
        }
    }


    //test
    // var textarea = $('<textarea></textarea>').appendTo('body').css({margin:'5px 250px'});
    // $(window).keydown(function(a){

    // if(a.key=='v'){
    // textarea.focus();
    // if(textarea.delay){clearTimeout(textarea.delay);}
    // textarea.delay = setTimeout(function(){
    // var val = textarea.val();
    // console.log(val);
    // textarea.delay=null;
    // },50);
    // }else if(a.key=='c'){
    // var r =document;
    // textarea.val('test qqq');
    // textarea.focus();
    // textarea.select();
    // if (!r.execCommand) return;

    // r.execCommand('copy');
    // }
    // });
});




var $Main = $('#main').generalController(function() {
    var ts = this;

    this.receive = function(html) {
        //可能加點轉場
        ts.$.hide(0).html(html).fadeIn(300);
        // location.href = location.href.replace(/\#.?/gi,'');
        // location.hash = ''+new Date().getTime();
    }
});