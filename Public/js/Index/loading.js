(function($, w, d) {
    var inner = {};
    var ajax = { count: 0, loaded: 0, first: true };
    var main;

    function startLoading() {
        // inner.loadingBlock && inner.loadingBlock.stop(true).fadeIn(500);
        !ajax.first && main.children(true).startLoading();
    }

    function endLoading() {
        !ajax.first && main.children(true).endLoading();
        // inner.loadingBlock && inner.loadingBlock.stop(true).fadeOut(500);
        // if(inner.timer){w.clearTimeout(inner.timer);}
        // inner.timer = w.setTimeout(function() {
        // inner.loadingBlock.stop(true).fadeOut(250);
        // }, 50);
    }

    function setting() {
        inner.loadingBlock = $('.loading-blcok').fadeOut(750, function() {

            main = (window.$Main) ? window.$Main : $('body').generalController(function() {});
            ajax.first = false;
        });
    }

    $(d).ajaxStart(function() {
        ajax.count++;
        startLoading();
    }).ajaxComplete(function() {
        ajax.loaded++;
        if (ajax.loaded >= ajax.count) {
            ajax.first && setting();
            endLoading();
        }
    });

    $(w).one('load', function() {
        ajax.count == 0 && setting();
        endLoading();
    });

})(jQuery, window, document);