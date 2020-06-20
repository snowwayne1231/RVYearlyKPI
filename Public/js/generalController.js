;(function($,api){
  "use strict";
  var event_shown = "__Shown",
      event_hidden = "__Hhown",
      event_login = "__Login";
  var _gc_ = "general-controller";
  function generalController($self,$,o){
    var config = $.extend({},{
      init : function(){},
      name : ''
    },o);
    var self = this;
    self.$ = $self;
    $self.data(_gc_,this).attr(_gc_,config.name);
    if(typeof config.init=="function" && $self.length>0){
      // api.startLoading($self);
      $(function(){ config.init.apply(self,[$self,o]); });
    }
    return this;
  }
  var gc = generalController;
  gc.prototype = {
    q : function(selector){
      return this.$.find(selector);
    },
    trigger : function(evt,ary){
      this.$.trigger(evt,ary);
      return this;
    },
    on : function(evt,b,c){
      this.$.on(evt,b,c);
      return this;
    },
    off : function(evt){
      this.$.off(evt);
      return this;
    },
    dispatchLogin : function(json){
      api.member = json;
      return api.$.trigger(event_login,[json]);
    },
    onLogin : function(fn){
      if(typeof fn!="function"){return false;}
      if(!api.member){
        api.$.one(event_login,function(){
          fn.apply(this,[api.member]);
        });
      }else{
        fn.apply(this,[api.member]);
      }
      return this;
    },
    dispatchShown : function($target){
      this.dispatchDOMEvent(event_shown,$target);
    },
    onShown : function(fn){
      this.onDOMEvent(event_shown,fn);
    },
    dispatchHidden : function($target){
      this.dispatchDOMEvent(event_hidden,$target);
    },
    onHidden : function(fn){
      this.onDOMEvent(event_hidden,fn);
    },
    dispatchDOMEvent : function(key,$target){
      if(typeof $target=="string"){$target=$($target);}
      $target.map(function(){
        var fn = $(this).data(key);
        if(!!fn){fn.apply(this);}
      });
    },
    onDOMEvent : function(key,fn){
      if(typeof fn!="function"){return false;}
      this.$.data(key,fn);
    },
    startLoading : function(){
      this.$.addClass("rv-loading");
    },
    endLoading : function(){
      this.$.removeClass("rv-loading");
    },
    hide : function(){
      this.$.hide();
      this.dispatchDOMEvent(event_hidden,this.$);
    },
    show : function(data){
      if(typeof data==="object"){
        for(var i in data){
          this.$.data(i,data[i]);
        }
      }
      this.$.show();
      this.dispatchDOMEvent(event_shown,this.$);
    },
    remove : function(){
      this.$.removeData();
      this.$.remove();
      for(var i in this){
        this[i] = null;
      }
    },
    parent : function(){
      var pp = this.$.parents('['+_gc_+']');
      if(pp.length>0){
        return pp.data(_gc_);
      }else{
        return false;
      }
    },
    children : function(only){
      var ary = [];
      var cc = this.$.q('['+_gc_+']').map(function(){
        ary.push($(this).data(_gc_));
      });
      return only?(ary[0]||this):ary;
    }
  }
  
  $.fn.gc = $.fn.generalController = function(opts) {
    if(opts){
      switch(typeof opts){
        case 'function': opts = {init:opts}; break;
        case 'string': opts = {init:function(){},name:opts}; break;
        case 'object': default :
      }
      if(this.length==1){
        return new gc(this,$,opts);
      }else{
        throw 'GeneralController Only Can Select One Element.';
      }
    }else{
      return this.data(_gc_);
    }
  }
  $.fn.q = $.fn.find;
  
})(jQuery,API);