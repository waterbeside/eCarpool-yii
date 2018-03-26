if(typeof(auiToast)!='function'){
	toast = {}
	toast.fail = toast.success = function(opt){
		alert(opt.title);
	}
	toast.hide = function(){return false;}
}

//取得ie浏览器版本号
function getBrowserVersion(){
	var agent = navigator.userAgent.toLowerCase() ;
	var regStr_ie = /msie [\d.]+;/gi ;
	if(agent.indexOf("msie") > 0){
		return  parseInt((agent.match(regStr_ie)+"").split("msie")[1].split(";")[0])
	}else{
		return "" ;
	}
}

//停止冒泡
function stopP(e){
	if ( e && e.stopPropagation )
	//因此它支持W3C的stopPropagation()方法
	e.stopPropagation();
	else
	//否则，我们需要使用IE的方式来取消事件冒泡
	window.event.cancelBubble = true;
	return false;
}

function getBodySize(){
	gbl_bodyHeight=document.documentElement.clientHeight;
 	gbl_bodyWidth=document.documentElement.clientWidth;
 	return {'height':gbl_bodyHeight,'width':gbl_bodyWidth} ;
}

 //获取字符长度
function getByteLen(val) {
	var len = 0;
	return val.replace(/[^\x00-\xff]/g,'**').length;
}


//检测是否移动端
function checkMobile(){
	var isiPad = navigator.userAgent.match(/iPad/i) != null;
	if(isiPad){
		return false;
	}
	var isMobile=navigator.userAgent.match(/iphone|android|phone|mobile|wap|netfront|x11|java|operamobi|operamini|ucweb|windowsce|symbian|symbianos|series|webos|sony|blackberry|dopod|nokia|samsung|palmsource|xda|pieplus|meizu|midp|cldc|motorola|foma|docomo|up.browser|up.link|blazer|helio|hosin|huawei|novarra|coolpad|webos|techfaith|palmsource|alcatel|amoi|ktouch|nexian|ericsson|philips|sagem|wellcom|bunjalloo|maui|smartphone|iemobile|spice|bird|zte-|longcos|pantech|gionee|portalmmm|jig browser|hiptop|benq|haier|^lct|320x320|240x320|176x220/i)!= null;
	if(isMobile){
		return true;
	}
	return false;
}

// getRequest
function getRequest(cx){
	var cxo =""
	var url=window.location.search;
	if(url.indexOf("?")!=-1)
	{
	  var str   =   url.substr(1)
	  strs = str.split("&");
	  for(i=0;i<strs.length;i++)
	  {
		if([strs[i].split("=")[0]]==cx) cxo=unescape(strs[i].split("=")[1]);
	  }
	}
	return cxo;
}


//確定跳轉
function confirmurl(url,message) {
    if(confirm(message)) redirect(url);
}

function redirect(url,win) {
    var lct = typeof(win)!="undefined" ? win.location : location;
    //console.log(lct);
    lct.href = url;
}

//回頂
function goTop(target){
	target = target || "html,body";
	$(target)[0].scrollTop = 0;
}

//动态调用方法
function cDoCall(fn,args){
    return fn.apply(this, args);
}

//十位补0
function fixZero(num){
	num = num >= 0 && num <= 9 ?   "0" + num : num;
	return num;
}

//取得地理位置
function getLocation(){
    if (navigator.geolocation){
        navigator.geolocation.getCurrentPosition(showLocationPosition,showLocationError);
    }else{
        alert("浏览器不支持地理定位。");
    }
}
function showLocationError(error){
    switch(error.code) {
        case error.PERMISSION_DENIED:
            alert("定位失败,用户拒绝请求地理定位");
            break;
        case error.POSITION_UNAVAILABLE:
            alert("定位失败,位置信息是不可用");
            break;
        case error.TIMEOUT:
            alert("定位失败,请求获取用户位置超时");
            break;
        case error.UNKNOWN_ERROR:
            alert("定位失败,定位系统失效");
            break;
    }
}
function showLocationPosition(position){
    var lat = position.coords.latitude; //纬度
    var lag = position.coords.longitude; //经度
    alert('纬度:'+lat+',经度:'+lag);
}

function returnPosition(position){
    var lat = position.coords.latitude; //纬度
    var lag = position.coords.longitude; //经度
    return [lat,lag];
}

//进入全屏
function cRequestFullScreen() {
    var de = document.documentElement;
    if (de.requestFullscreen) {
        de.requestFullscreen();
    } else if (de.mozRequestFullScreen) {
        de.mozRequestFullScreen();
    } else if (de.webkitRequestFullScreen) {
        de.webkitRequestFullScreen();
    }
}
//退出全屏
function cExitFullscreen() {
    var de = document;
    if (de.exitFullscreen) {
        de.exitFullscreen();
    } else if (de.mozCancelFullScreen) {
        de.mozCancelFullScreen();
    } else if (de.webkitCancelFullScreen) {
        de.webkitCancelFullScreen();
    }
}


/**
 * 设置或取得缓存
 * @param  string 				key
 * @param  string||object data   		内容
 * @param  int  					duration  有效期
 * @return string||object
 */
function cCache(key,data,duration){
	key = key || ''
	if(key==''){
		return false;
	}
	var nowTime = new Date().getTime();
	nowTime = Math.round(nowTime/1000);
	// console.log(nowTime);
	if(typeof(data)=='undefined'){
		var str_datas = localStorage.getItem(key);
		if(!str_datas){
			return '';
		}else{
			datas = JSON.parse(str_datas);
			if(datas.expiration && nowTime < datas.expiration){
				return datas.data;
			}else{
				return '';
			}
			// JSON.stringify
		}
	}else{
		duration = duration || 3600*24*7  //如果不设置时间，默认7天
		var expiration = nowTime + duration;
		var datas = {
			data: data,
			expiration :expiration
		}
		var str_datas = JSON.stringify(datas);
		localStorage.setItem(key,str_datas);
		return data;
	}
	// localStorage.setItem('CP_U_TOKEN',json.data.token);



	return data;
}

/**
 * 动态加载JS
 * @param  {String}   url      URL
 * @param  {Function} callback 回调函数
 */
function cLoadScript(url, callback) {
  var script = document.createElement("script");
  script.type = "text/javascript";
  if(typeof(callback) != "undefined"){
    if (script.readyState) {
      script.onreadystatechange = function () {
        if (script.readyState == "loaded" || script.readyState == "complete") {
          script.onreadystatechange = null;
          callback();
        }
      };
    } else {
      script.onload = function () {
        callback();
      };
    }
  }
  script.src = url;
  document.body.appendChild(script);
}

/********* start 用户验证相关 ***********/

function cCheckLoginByCode(code,showToast){
	showToast = showToast || 1
	if(code===10004){
		if(showToast){
			setTimeout(function(){
				toast.fail({title:'请先登入',  duration:2000});
			},200);
			setTimeout(function(){
				// redirect(cRouter.login);
				cGoLogin()
			},600);
		}else{
			cGoLogin()
		}
		return false;
	}
	return true;
}

/**
	* 跳到登入页
 */
function cGoLogin(){
	redirect('#/login');
}

/**
 * 登出
 * @return {[type]} [description]
 */
function cLogout(){

	toast.loading({title:"正在登出",duration:1000},function(ret){});
	$.ajax({
		type:'get',
		dataType:'json',
		url:cApiUrl.logout,
		success:function(rs){
			window.localStorage.removeItem('CP_U_TOKEN');
			GB_VAR['userBaseInfo'] = '';
	    GB_VAR['webim_access_token'] = '';
	    GB_VAR['user_info'] = '';
	    GB_VAR['userAvatar'] = '';
			cGoLogin();
		},
		complete:function(){
			setTimeout(function(){toast.hide()},500);
		}
	})
	// redirect(cRouter.logout);
}

//cAccessAjax
function cAccessAjax(options){
	var defaults ={
		beforeSend: function(xhr) {
			token = window.localStorage.getItem('CP_U_TOKEN');
			xhr.setRequestHeader("Authorization", "Bearer " + token);
		},
	}
	var opt = $.extend({}, defaults, options);
	$.ajax(opt);
}

/********* end 用户验证相关 ***********/


/********* start 列表相关 ***********/

/* 显示列表的loading */
function cListsLoading(target,action){
  action = action || 0;
  var html = cParseTemplates().listLoading;
  if(target=='returnHtml'){
    return html;
  }
  $target = $(target);
  if(action==1){
    $target.html(html).addClass('show');
  }else{
    $target.removeClass('show');
  }
}

//加载ajax分页列表
function cGetLists(options){
  var defaults = {
		autoScrollTop:1,
    target:'',
		no_data_text:'暂时没有数据  ⁽⁽ƪ(ᵕ᷄≀ ̠˘᷅ )ʃ⁾⁾',
		fail_text:'网络君开了小猜，请稍候再试',
    url:'#',
    type:'get',
    dataType:'json',
    data : {},
    templateFun : function(data){
      return '<li>'+data.id+'</li>'
    }
  }
  var opt = $.extend({}, defaults, options);
  page = parseInt(opt.data.page)-1 || 0;
  page = page < 0 ?  0 : page;
  var $listWrapper = $(opt.target);

  if(page>0){
    var pagecount =  parseInt($listWrapper.attr('data-pagecount'));
    if(page>=pagecount){
      return false;
    }
  }
	if($listWrapper.attr('data-loading')==1 || $listWrapper.attr('data-error')==1 ){
		return false;
	}
  $listWrapper.attr('data-loading',1);
	$('.cp-error-tips').remove();
	function loading(type){
		type = type || 0;
		var $loading = $listWrapper.siblings('.cp-loading-l');
		if($loading.length>0){
			return type ? $loading.removeClass('hide') : $loading.addClass('hide');
		}else if(type==1){
			$listWrapper.after(cParseTemplates().listLoading());
		}
	}
  loading(1);
  cAccessAjax({
    type:opt.type,
    dataType:opt.dataType,
    url:opt.url,
    data:opt.data,
    success:function(result){
			if(!cCheckLoginByCode(result.code)){return false;}
			if(typeof(opt.successStart)=='function'){
        opt.successStart(result)
      }
      var html = '';
			var uid = result.uid ? result.uid : 0
			if(result.code === 0){
				if(opt.templateFun){
					$.each(result.data.lists,function(i,item){
		        html += opt.templateFun(item,{type:opt.listType,uid:uid});
		      })
		      if(page === 0){
		        $listWrapper.html('');
						if(opt.autoScrollTop){
							setTimeout(function(){
								$listWrapper.closest('.cp-scroller-wrap')[0].scrollTop = 0;
							},400);
						}
		      }
		      $listWrapper.append(html);
				}
				if(result.data.lists.length < 1){
					$listWrapper.html(cParseTemplates().noDataTips({tips:opt.no_data_text}));
				}
				if(result.data.page){
					$listWrapper.attr('data-page',result.data.page.currentPage).attr('data-pagecount',result.data.page.pageCount);
				}
			}

      if(typeof(opt.success)=='function'){
        return opt.success(result)
      }
      return true;
    },
    error:function(XMLHttpRequest, textStatus, errorThrown) {
			$listWrapper.attr('data-loading',0);
			$listWrapper.attr('data-error',1);
			$listWrapper.append(cParseTemplates().failLoadTips({tips:opt.fail_text}));
			$listWrapper.find('.btn-refresh').click(function(){
				$listWrapper.attr('data-error',0);
				cGetLists(options);
			});
      if(typeof(opt.error)=='function'){
        return opt.error(XMLHttpRequest,textStatus,errorThrown)
      }
    },
    complete: function(XMLHttpRequest, textStatus) {
      $listWrapper.attr('data-loading',0);
			loading(0);
      // setTimeout(function(){loading(0)}, 100);
      if(typeof(opt.complete)=='function'){
        return opt.complete(XMLHttpRequest,textStatus)
      }
    }
  })
}

function cFormatDate(date,fmt){
	var o = {
			 "m+": date.getMonth() + 1, //月份
			 "d+": date.getDate(), //日
			 "h+": date.getHours(), //小时
			 "i+": date.getMinutes(), //分
			 "s+": date.getSeconds(), //秒
			 "q+": Math.floor((date.getMonth() + 3) / 3), //季度
			 "S": date.getMilliseconds() //毫秒
	 };
	 if (/(y+)/.test(fmt)) {
			 fmt = fmt.replace(RegExp.$1, (date.getFullYear() + "").substr(4 - RegExp.$1.length));
	 }
	 for (var k in o)
			 if (new RegExp("(" + k + ")").test(fmt))
					 fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
	 return fmt;

}
//检查是不是两位数字，不足补全
function cFixNum(str){
    str=str.toString();
    if(str.length<2){
        str='0'+ str;
    }
    return str;
}

function cMyAvatar(){
	if(typeof(GB_VAR['userAvatar'])=='undefined' || GB_VAR['userAvatar']=='' ){
		if(GB_VAR['user_info']['imgpath']!='' && GB_VAR['user_info']['imgpath']!='null' && GB_VAR['user_info']['imgpath']!=null ){
			return  cConfig.avatarPath + GB_VAR['user_info']['imgpath'];
		}else if(GB_VAR['userBaseInfo'].avatar!=''){
			return cConfig.avatarPath + GB_VAR['userBaseInfo'].avatar;
		}else{
			return cConfig.defaultAvatar;
		}
	}else{
		return GB_VAR['userAvatar'];
	}
}


/* 列表，加载更多 */
function cLoadMoreList(target,func,setOptions){
  var $target = $(target);
  var $wrapper = $target.closest('.cp-scroller-wrap')
	// var $inner = $wrapper.find('.cp-scroller');
  var $inner = $wrapper.find('.cp-list-wrap');

  $wrapper[0].addEventListener('scroll',function(){
		// console.log($wrapper.scrollTop()+','+$inner.height()+','+$wrapper.height())
    if($wrapper.scrollTop()>=$inner.height()-$wrapper.height()-30){
      var currentPage = parseInt($target.attr('data-page'));
      var isLoading = $target.attr('data-loading');
      if(isLoading==1){
        return false;
      }else{
				var opt = {};
				if(typeof(setOptions)=='function'){
          opt = setOptions();
        }
        if(typeof(func)=='function'){
          func(currentPage+1,opt)
        }
      }
    }
  },false)
}
/********* end :列表相关 ***********/

/***** s:时间选择相关 *******/
/*
创建时间选择数据数组
type 0~3， 默认为0全选，参数为1取日，参数为2取时，参数为3取分
onlyNow 0 or 1 , 1时：创建时分之时，不创建现时之前的时间数据，0创建所有时间
*/
function returnNeedTimeDatas(type,onlyNow){
  type = type || 0;
  onlyNow = onlyNow || 0;
  var nowDate = new Date();
	// console.log(nowDate.getTimezoneOffset());
	// console.log(nowDate.getUTCHours());
  var month = nowDate.getMonth() + 1;
  var howManyDay = 7;
  var data_dates = [];
  var data_hours = [];
  var data_min = [];
  //取得n天后的Date对像。
  function getNextDate(n){
    var nextDate = new Date();
    nextDate.setDate(nowDate.getDate()+n);
    return nextDate;
  }
  //个位数补充0
  function fixZero(num){
    num = num >= 0 && num <= 9 ?   "0" + num : num;
    return num;
  }
  //格式化日期数据
  function formatDayItemData(date,text){
    text = text || '';
    var month = date.getMonth() + 1;
    month = fixZero(month);
    var day = date.getDate();
    day = fixZero(day);
    text = $.trim(text)=='' ? month + '月' + day + '日' : text;
    return {"value":(month+''+day),"text":text}
  }
  //取日期数据数组
  if(type==1 || type == 0 ){
    for(i=0; i<howManyDay;i++){
      var date = getNextDate(i);
      var text = '';
      if(i==0){text='今天';}
      if(i==1){text='明天';}
      if(i==2){text='后天';}
      data_dates[i] = formatDayItemData(date,text);
    }
    if(type>0){return data_dates;}
  }
  //时数组
  if(type==2 || type == 0 ){
    var hour_start = onlyNow ? nowDate.getHours() : 0 ;

    for(i=hour_start;i<24;i++){
      data_hours[i-hour_start] = {"value":fixZero(i),"text":(i)+'点'}
    }
    if(type>0){return data_hours;}
  }
  //分数组
  if(type==3 || type == 0 ){
    var min_start = onlyNow ? nowDate.getMinutes() : 0 ;
    for(i=min_start;i<60;i++){
      data_min[i-min_start] = {"value":fixZero(i),"text":(i)+'分'}
    }
    if(type>0){return data_min;}
  }
  return [data_dates,data_hours,data_min]
}

/* 显示时间选择器 */
function selectNeedTime(target,callback){
  var data = returnNeedTimeDatas(0,1)
  if(typeof(GB_VAR['dt_picker'])!='object'){
    GB_VAR['dt_picker'] = new Picker({
    	data: data,
    	selectedIndex: [0, 0, 0],
    	title: '请选择时间'
    });
  }
  GB_VAR['dt_picker'].show(function(){
    /*GB_VAR['dt_picker'].refillColumn(1, returnNeedTimeDatas(2,0));
    GB_VAR['dt_picker'].refillColumn(2, returnNeedTimeDatas(3,0));*/
  });

  var $target = $(target);
  GB_VAR['dt_picker'].on('picker.change', function (colIndex, selectedIndex) {

    if(colIndex==0){
      if(selectedIndex>0){
        GB_VAR['dt_picker'].refillColumn(1, returnNeedTimeDatas(2,0));
        GB_VAR['dt_picker'].refillColumn(2, returnNeedTimeDatas(3,0));
      }else{
        GB_VAR['dt_picker'].refillColumn(1, returnNeedTimeDatas(2,1));
        GB_VAR['dt_picker'].refillColumn(2, returnNeedTimeDatas(3,1));
      }
    }
    if(colIndex==1){

      if(selectedIndex==0 && GB_VAR['dt_picker'].data[1][0].value!='00' ){

        GB_VAR['dt_picker'].refillColumn(2, returnNeedTimeDatas(3,1));
      }else{
        GB_VAR['dt_picker'].refillColumn(2, returnNeedTimeDatas(3,0));
      }
    }
  })
  GB_VAR['dt_picker'].on('picker.valuechange', function (selectedVal, selectedIndex) {
	console.log(selectedVal);
	console.log(selectedIndex);
  });
  GB_VAR['dt_picker'].on('picker.select', function (selectedVal, selectedIndex) {
    var text = selectedVal[0].substring(0,2) +'月'+ selectedVal[0].substring(2,4)+'日 '+selectedVal[1]+'点'+selectedVal[2]+'分';
    var textFixArray = ['今天','明天','后天'];
    var dataVal = selectedVal[0]+''+selectedVal[1]+selectedVal[2];
    if(selectedIndex[0]<3){
      text += ' (' + textFixArray[selectedIndex[0]] +')';
    }
		var datas = {
			selectedIndex : selectedIndex,
			val : dataVal,
			selectedVal: selectedVal,
			text: text,
		}
  	$target.addClass('cp-selected').attr('data-selectedIndex',selectedIndex.join(',')).attr('data-val',dataVal).attr('data-date',selectedVal[0]).attr('data-hour',selectedVal[1]).attr('data-min',selectedVal[2]).find('.cp-text').show().text(text).siblings('.cp-ph').hide();
    if(typeof(pageMethods)=='object'&& pageMethods.showAddRouteBtn()){
			pageMethods.showAddRouteBtn();
		}
		if(typeof(callback)=='function'){
			callback(datas,'time');
		}
  })

}
/***** e:时间选择相关 *******/


/* 空座位数择器 */
function selectSeatCount(target,callback){
	var data = [];
	for(i=0;i<10;i++){
		data[i] = {"value":i+1,"text":(i+1)+'个'}
	}
  if(typeof(GB_VAR['seat_picker'])!='object'){
    GB_VAR['seat_picker'] = new Picker({
    	data: [data],
    	selectedIndex: [0],
    	title: '请选择空座位数'
    });
  }
  GB_VAR['seat_picker'].show(function(){
    /*GB_VAR['dt_picker'].refillColumn(1, returnNeedTimeDatas(2,0));
    GB_VAR['dt_picker'].refillColumn(2, returnNeedTimeDatas(3,0));*/
  });

  var $target = $(target);

  GB_VAR['seat_picker'].on('picker.select', function (selectedVal, selectedIndex) {
		var datas = {
			selectedIndex : selectedIndex,
			val : selectedVal[0],
			selectedVal: selectedVal,
			text: selectedVal[0]+'个',
		}
  	$target.addClass('cp-selected').attr('data-selectedIndex',selectedIndex.join(',')).attr('data-val',selectedVal[0]).find('.cp-text').show().text(selectedVal[0]+'个').siblings('.cp-ph').hide();
    if(typeof(pageMethods)=='object'&& pageMethods.showAddRouteBtn()){
			pageMethods.showAddRouteBtn();
		}
		if(typeof(callback)=='function'){
			callback(datas,'seat_count');
		}
  })
}

/***** s:地图相关 *******/
//创建地图
function showMap(targetId){
  var map = new AMap.Map(targetId, {
      // resizeEnable: true,
      //zoom:11,
      // center: [112.903921, 22.884658]
  });
	map.getCity(function(data) {
      if (data['province'] && typeof data['province'] === 'string') {
				GB_VAR.local_city =  data ;
          // document.getElementById('info').innerHTML = '城市：' + (data['city'] || data['province']);
      }
  });
  map.plugin('AMap.Geolocation', function() {
       geolocation = new AMap.Geolocation({
           enableHighAccuracy: true,//是否使用高精度定位，默认:true
           timeout: 10000,          //超过10秒后停止定位，默认：无穷大
           buttonOffset: new AMap.Pixel(10, 20),//定位按钮与设置的停靠位置的偏移量，默认：Pixel(10, 20)
           zoomToAccuracy: true,      //定位成功后调整地图视野范围使定位位置及精度范围视野内可见，默认：false
           buttonPosition:null
       });
      //  map.addControl(geolocation);
       geolocation.getCurrentPosition();
			//  console.log(geolocation)
			 AMap.event.addListener(geolocation, 'complete', function(GeolocationResult){
				 GB_VAR['local_position'] = GeolocationResult.position
			 })
			 AMap.event.addListener(geolocation, 'error', function(GeolocationError){
				//  console.log(GeolocationError)
			 })
      //  AMap.event.addListener(geolocation, 'complete', onComplete);//返回定位信息
      //  AMap.event.addListener(geolocation, 'error', onError);      //返回定位出错信息
   });
  return map;
}




//设置地图语言
function setMapLang(mapObj,lang){
  lang = lang || "zh";
  if(lang=='en'){
      mapObj.setLang(lang);
  }
}


//添加标注点
function addMarker(position,mapObj) {
  // console.log(position);
  mapObj = mapObj || map;
  mapObj.setZoomAndCenter(14, position);
  marker = new AMap.Marker({
    icon: "http://webapi.amap.com/theme/v1.3/markers/n/mark_b.png",
    // position: [116.405467, 39.907761]
    position: position,
  });
  marker.setMap(mapObj);
}



// 绘制路线
function drawRouteLine(start,end,mapObj,callBack){
  mapObj = mapObj || map;
  mapObj.clearMap();
  AMap.service('AMap.Driving',function(){//回调函数
      //实例化Driving
      GB_VAR['map_driving']= new AMap.Driving({
            map: mapObj,
            // panel: "panel"
        });
      //TODO: 使用driving对象调用驾车路径规划相关的功能
      //传经纬度
      //  driving.search(new AMap.LngLat(116.379028, 39.865042), new AMap.LngLat(116.427281, 39.903719));
      GB_VAR['map_driving'].search(start, end, function(status, result) {
           //TODO 解析返回结果，自己生成操作界面和地图展示界面
           if(typeof(callBack)=='function'){
						 callBack(status,result);
					 }
           console.log(result)
      });
  })
}

// 格式化行程距离
function formatDistance(distance,returnType){
	returnType = returnType || 0
	var distanceStr = distance + '米';
	var unit = 'M';
	var dtTimeStr = '';
	if(distance > 1000){
		distance = (distance/1000).toFixed(1);
		unit = 'KM'
		distanceStr = distance + '公里';
	}
	if(returnType){
		return {unit:unit,distance:distance};
	}else{
		return distanceStr;
	}

}

// 格式化行程用时
function formatRouteTime(dtTime){
	var dtTimeStr = '';
	if(dtTime > 3600){
		dtTimeStr = Math.floor(dtTime/3600)+'小时' + Math.floor((dtTime%3600)/60)+'分钟';
	}else if(dtTime > 60){
		dtTimeStr =  Math.floor((dtTime)/60)+'分钟';
	}
	return dtTimeStr;
}
/***** e:地图相关 *******/
