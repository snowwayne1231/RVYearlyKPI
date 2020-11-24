<?php
// echo $_SERVER["REQUEST_URI"];exit;
include_once(__DIR__.'/../../Global.php');
include_once(BASE_PATH."/Model/SessionCenter.php");
$SC = new Model\SessionCenter();
ob_start();
?>
var Site_Root  = '<?=WEB_ROOT?>';
(function ($) {
  'use strict';
  var self = window.API = {};
  self.$ = $(self);

  var API_PATH = '<?=dirname($_SERVER["REQUEST_URI"])?>'.replace(/\/[^\/]+$/,"/");
  var Member_PATH = API_PATH+"Member/";
  var Data_PATH = API_PATH+"Data/";
  var Absence_PATH = Data_PATH+"Absence/";
  var Setting_PATH = API_PATH+"Setting/";
  var Excel_PATH = API_PATH+"Excel/";
  self.ROOT = Site_Root;

  self.member = <?=$SC->getJSON()?>;

  <?php 
    require('_v1/common.js');
    require('_v1/month.js');
    require('_v1/year.js');
  ?>

  //混淆 壓縮
  var code_array = '0123456789@ABCDEFGHIJKLMNOPQRSTUVWXYZ/_-abcdefghijklmnopqrstuvwxyz'.split('');
  var code_array_2 = 'D7jklYmIqJ034M/8Ncde_Wfg1GzHhiOUno-abPprQRSTs5KL6tXuAvwVxEy9@BFZC2'.split('');
  var code_map={},code_map_2={};
  var TemplateKey = self.ROOT+'/Template/';
  var temp = localStorage.getItem('rv-template');
  temp = temp? JSON.parse(temp) : {};

  for(var i in code_array){
    var loc = code_array[i];
    var loc_2 = code_array_2[i];
    code_map[loc] = loc_2;
    code_map_2[loc_2] = loc;
  }
  self.encode = function(code){
    var ary = code.replace(TemplateKey,'').split('');
    for(var i in ary){
      var loc = ary[i];
      ary[i] = code_map[loc];
    }
    var result = ary.join('');
    temp[result]=1;
    localStorage.setItem('rv-template',JSON.stringify(temp));
    return result;
  }

  self.decode = function(code){
    if(!temp[code]){return false;}
    var ary = code.replace(TemplateKey,'').split('');
    for(var i in ary){
      var loc = ary[i];
      ary[i] = code_map_2[loc];
    }
    return (ary.length>0) ? TemplateKey+ary.join('') : false;
  }
  self.getCode = function(){
    var hash = location.hash.replace('#','');
    var code = self.decode(hash);
    return code;
  }

  //URL移動
  self.go = function(position){
    location.href = self.ROOT+''+position;
  }
  
  //
  self.reload = function(){
    if(location.hash.length<=1){
      location.reload();
    }else{
      $(window).trigger('hashchange');
    }
  }

   // ajax setting
  self.ajaxPassenger = [];

  self.clearPassenger = function(){
    var ab = API.ajaxPassenger.length-1;
    while(ab>=0){
      var ajax = API.ajaxPassenger.splice(ab,1)[0].a;
      // ajax.then(function(){});
      ajax.abort() && ajax==null;
      ab--;
    }
    return self;
  }

  $.ajaxSetup({
    beforeSend:function(a,set){
      // console.log(a);
      // console.log(set);
      var stamp = set.url + (typeof set.data=='string'?set.data:'');
      for(var i in self.ajaxPassenger){
        var loc = self.ajaxPassenger[i].u;
        if(stamp==loc){ a.abort();return console.log('The URL Not Over [ '+loc+' ]'); }
      }
      self.ajaxPassenger.push({u:stamp,a:a});
    },
    complete:function(a,set){
      for(var i in self.ajaxPassenger){
        var loc = self.ajaxPassenger[i].a;
        if(loc==a){
          // console.log(a);
          self.ajaxPassenger.splice(i,1);break;
        }
      }
    }
  });
  var hook_data = {}, hook_pointer;
  self.hook = function(name, fn){
    if(typeof self[name]!='function'){return console.log('Fail Hook Name : '+name);}
    if(!hook_data[name]){
      hook_data[name] = [self[name]]; 
      self[name] = function(){
        hook_pointer = hook_data[name].length;
        // console.log(hook_data);
        var ret, p=0;
        do{
          ret = (hook_data[name][ p ].apply(ret, arguments)) || ret;
          p++;
        }while(p<hook_pointer);
        return ret;
      };
    }
    if(typeof fn == 'function'){ hook_data[name].push(fn); }
  }

  isRefresh(self);
  setting(self);

  function isRefresh(a){
    var code = a.getCode();
    if(code){
      var cc = code.replace(TemplateKey,'');
      location.href= self.ROOT+"/"+cc;
    }
  }

})(jQuery);

Object.defineProperty(Array.prototype, "getColumn", {
  enumerable: false,
  value: function(col) {
    return this.map(function(el) {
       if (el.hasOwnProperty(col)) {
         return el[col];
       } else {
         return null;
       }
    });
  }
});

//初始設定
function setting(s){


  var dm = s.developMode = <?php echo (IS_DEBUG_MODE)?'true':'false';?>;

  s.when = $.when.all = function(deferreds){
    var deferred = new $.Deferred();
    $.when.apply($, deferreds).then(
      function(){
        deferred.resolve(Array.prototype.slice.call(arguments));
      },
      function(){
        deferred.fail(Array.prototype.slice.call(arguments));
      });
    return deferred;
  }
  
  var cache_data, cache_key = 'HR_API_CACHE';
  s.cache = function(key,val){
    if(!window.localStorage){return null;}
    if(val){//set
      cache_data[key] = val;
      var time = new Date().getTime();
      cache_data.update_time = time;
      localStorage.setItem(cache_key, JSON.stringify(cache_data) );
    }else{//get
      return cache_data[key];
    }return this;
  }
  try{
    cache_data= localStorage.getItem(cache_key); cache_data= JSON.parse(cache_data) || {};
    if((cache_data.update_time + (1000*60*30)) < new Date().getTime()){ throw ''; }
  }catch(e){console.log(e);cache_data={};s.cache('update_time',1);}
  
  s.create={
    year : 2017,
    month : 4,
    day : 20
  }

}

//針對後端的API資料 解析統一格式
function grenalJSONFormat(json){
  switch(typeof json){
    case "undefined": json={}; break;
    case "string":
      try{
        json = JSON.parse(json);
      }catch(e){
        json = {};
      }
    break;
    case "function":
      try{
        json = json.apply(this);
      }catch(e){
        json = {};
      }
    break;
    case "default":
  }
  var contain = {
    result : json.result,
    status : json.status,
    msg : json.msg
  }
  var successCode = 200;
  if(typeof contain.status==="undefined"){
    contain.status = 0;
    contain.msg = "Error, Format Not Match.";
    contain.result = null;
  }

  if(Object.defineProperty){
    Object.defineProperty(this,"is",{
      value:contain.status==successCode,
      writable:false,
      configurable:false
    });
  }else{
    this.is = contain.status==successCode;
  }

  this.get = function(param){
    var res;
    switch(param){
      case "msg": res=contain.msg; break;
      case "status": res=contain.status; break;
      default:
      if(contain.status!=successCode){
        res=contain.msg;
      }else if(contain.result){
        if(contain.result.length == 1 && contain.result[0]){
          res=contain.result[0];
        }else{
          res=contain.result;
        }
      }
    }
    return res;
  }
  this.res = function(){
    return contain.result;
  }
  this.set = function(data){
    contain.result = data;
  }
  var maps;
  this.map = function(){
    if(!maps){
      maps={};
      for(var i in contain.result){
        var loc = contain.result[i];
        if(loc.id){maps[loc.id]=loc;}else{break;}
      }
    }
    return maps;
  }
}

var locale = {};
function Lang(key) {
  var split = key.split('.');
  var model = split[0];
  var item = (split[1]) ? split[1] : null;
  if (locale.model === undefined) {
    var url = Site_Root+'/Lang/tw/'+model+'.json';
    $.ajax({
        url: url,
        dataType: 'json',
        async: false, 
        success: function(json){
            locale.model =json;
        }
    });
  }
  if (item) {
    return (locale.model[item]) ? locale.model[item] : item;
  } else {
    return locale.model;
  }
}

function test100(cbFn){
  var time1 = new Date();
  for(var i = 0; i <= 10000;i++){
    cbFn();
  }
  var time2 = new Date();
  return time2 - time1;
}

function clone(inn){
  var newi;
  if(Array.isArray(inn)){
    newi = inn.slice();
  }else{
    newi = JSON.parse( JSON.stringify(inn));
  }
  return newi;
}

//cookie
(function (factory) {
  factory(jQuery);
}(function ($) {
	var pluses = /\+/g;

	function encode(s) {
		return config.raw ? s : encodeURIComponent(s);
	}
	function decode(s) {
		return config.raw ? s : decodeURIComponent(s);
	}
	function stringifyCookieValue(value) {
		return encode(config.json ? JSON.stringify(value) : String(value));
	}
	function parseCookieValue(s) {
		if (s.indexOf('"') === 0) {
			s = s.slice(1, -1).replace(/\\"/g, '"').replace(/\\\\/g, '\\');
		}
		try {
			s = decodeURIComponent(s.replace(pluses, ' '));
			return config.json ? JSON.parse(s) : s;
		} catch(e) {}
	}

	function read(s, converter) {
		var value = config.raw ? s : parseCookieValue(s);
		return $.isFunction(converter) ? converter(value) : value;
	}

	var config = $.cookie = function (key, value, options) {

		if (value !== undefined && !$.isFunction(value)) {
			options = $.extend({}, config.defaults, options);

			if (typeof options.expires === 'number') {
				var days = options.expires, t = options.expires = new Date();
				t.setTime(+t + days * 864e+5);
			}

			return (document.cookie = [
				encode(key), '=', stringifyCookieValue(value),
				options.expires ? '; expires=' + options.expires.toUTCString() : '', // use expires attribute, max-age is not supported by IE
				options.path    ? '; path=' + options.path : '',
				options.domain  ? '; domain=' + options.domain : '',
				options.secure  ? '; secure' : ''
			].join(''));
		}

		var result = key ? undefined : {};

		var cookies = document.cookie ? document.cookie.split('; ') : [];

		for (var i = 0, l = cookies.length; i < l; i++) {
			var parts = cookies[i].split('=');
			var name = decode(parts.shift());
			var cookie = parts.join('=');

			if (key && key === name) {
				result = read(cookie, value);
				break;
			}

			if (!key && (cookie = read(cookie)) !== undefined) {
				result[name] = cookie;
			}
		}

		return result;
	};

	config.defaults = {};

	$.removeCookie = function (key, options) {
		if ($.cookie(key) === undefined) {
			return false;
		}
		$.cookie(key, '', $.extend({}, options, { expires: -1 }));
		return !$.cookie(key);
	};

  var current = {
    year : $.cookie('rv-kpi-year') || new Date().getFullYear(),
    month : $.cookie('rv-kpi-month') || new Date().getMonth() + 1
  }

  $.ym = {
    get : function(){
      return current;
    },
    save : function(ym){
      if(ym){
        if(ym.year){current.year=ym.year;}
        if(ym.month){current.month=ym.month;}
      }
      $.cookie('rv-kpi-year',current.year);
      $.cookie('rv-kpi-month',current.month);
    },
    reset : function(){
      current.year = new Date().getFullYear();
      current.month = new Date().getMonth() + 1;
    }
  }

}));

if(!$.fn.TableScrollbarY){
  $.fn.TableScrollbarY = function(options){
    var set = $.extend({
      height : 500,
      rang : 8
    },options);
    var $$ = this,
      innerTR = $$.q('tbody').length==0 ? $$.q('tr') : $$.q('tbody tr'),
      count = innerTR.length;
    return this;
  };
}

<?php
$content = ob_get_contents();
$length = strlen($content);
header('Content-Length: '.$length);
?>