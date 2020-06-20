$.fn.fixMe = function() {
      return this.each(function() {
          var $this = $(this),
              $t_fixed;

          function init() {
              var checkParent = $this.parent().hasClass('already-fix-table');
              if(checkParent){return;}
              $this.wrap('<div class="staff-table already-fix-table">');
              $t_fixed = $this.clone();
              $t_fixed.find("tbody").remove().end().addClass("fixedTable").insertBefore($this);
              resizeFixed();
              $(window).resize(resizeFixed);
              $(window).scroll(scrollFixed);
          }

          function resizeFixed() {
              // $t_fixed.find("th").each(function(index) {
              //     $(this).css("width", $this.find("th").eq(index).outerWidth() + "px");
              // });
              var thead = $this.find('thead');
              var tWidth = thead.outerWidth();
              // if(window.devicePixelRatio>0){ tWidth*=window.devicePixelRatio; }
              //$t_fixed.find('thead').css('width', tWidth + 'px');
              $t_fixed.css('width', tWidth + 'px');
              thead.find('th').each(function(i) {
                  var $t = $(this);
                  // var thw = Math.max($t.outerWidth(), $t.width());
                  // var thw = $t.outerWidth();
                  var thw = $t.width();
                  // if(window.devicePixelRatio>0){ thw= thw * window.devicePixelRatio; }
                  // if(i==0){
                    // console.log(thw);console.log([this,this.clientWidth]);
                    // console.log($(this).width());
                    // console.log($(this).outerWidth());
                    // console.log($(this).innerWidth());
                    // console.log($(this).css('width'));
                  // }
                  // $t_fixed.find('th').eq(i).width(thw);
                  $t_fixed.find('th').eq(i).css('width',thw);
              })
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

          init();
      });
  };