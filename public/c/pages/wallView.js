SPA_RESOLVE_INIT = function(transition) {
  /****** DOM *******/
  var  pageTempates = {
    content : function(data){
      return '<div class="page-view cp-page-viewWall" id="Page-wall-view" data-id="'+transition.query.id+'">\
        <div class="page-view-main"  >\
          <a class="cp-btn-goback" onclick="cGoBack(this);"> <i class="cp-iconfont fa fa-angle-left"></i> </a>\
          <div class="cp-scroller-wrap" >\
            <div class="cp-scroller">\
              <div class="cp-wallView-map" id="map-wallView-content" >地图加载中..</div>\
              <!-- {cp-user-bar} -->\
              <!-- {cp-route-wrap} -->\
              <div class="cp-wallView-statis">\
                <div class="cp-wallView-statis-inner">加载中</div>\
              </div>\
              <!-- /.cp-wallView-statis -->\
              <a class="btn btn-warning  cp-btn-submit " onclick="return pageMethods.riding();" data-loading-text="提交中.."><i class="fa fa-car"></i> 搭 车</a>\
              <div class="cp-wallView-passenger">\
                <h4 class="cp-heading"><i class="fa fa-user-o"></i> 乘客</h4>\
                <ul class="row"></ul>\
              </div>\
              <div class="cp-wallView-comment">\
                <h4 class="cp-heading">\
                  <i class="fa fa-comment-o"></i> 评论\
                  <span class="cp-comment-count"><b>-</b></span>\
                </h4>\
                <div class="cp-content">\
                  <ul class="cp-comment-list"></ul>\
                  <div class="text-center"><a class="btn btn-default" href="javascript:void(0);" onclick="pageMethods.goCommentsPage()"><i class="fa fa-edit"></i> 我要评论</a></div>\
                </div>\
              </div>\
            </div>\
          </div>\
        </div>\
      </div>';
    },

    // 详细页的行程组件
    routeLine : function(data){
      var theDateArray = data.time_format.split(' ');
      return '<div class="cp-route-wrap">\
        <div class="cp-route-line"><div class="cp-line"></div><i class="fa fa-chevron-circle-down"></i></div>\
        <div class="cp-route-content">\
          <div class="cp-item cp-start">\
            <b class="cp-p"></b>\
            <div class="time">\
              <span>'+theDateArray[0]+'</span>\
              <b>'+theDateArray[1]+'</b>\
            </div>\
            <h4>'+data.start_info.addressname+'</h4>\
            <label>出发 FROM</label>\
          </div>\
          <div class="cp-item-d fade ">\
            <b class="cp-p"></b>\
            <i class=" cp-caret-left"></i>\
            <span class="cp-text">预计--</span>\
          </div>\
          <div class="cp-item  cp-end">\
            <b class="cp-p"></b>\
            <div class="time">\
              <span>--</span>\
              <b>--</b>\
            </div>\
            <h4>'+data.end_info.addressname+'</h4>\
            <label>结束 TO</label>\
          </div>\
        </div>\
      </div>'
    },
    // 详细页的userbar组件
    userBar : function(data){
      var btnHtml = '';
      if(data.uid == data.owner_info.uid){
        btnHtml = '\
        <a href="javascript:void(0);" class="btn-ripple" onclick="pageMethods.finishRoute('+data.wid+',\'wall\',this)" data-loading-text="<i class=\'fa fa-spinner fa-spin\'></i>"><i class="fa fa-check"></i></a> \
        <a href="javascript:void(0);" class="btn-ripple" onclick="pageMethods.cencelRoute('+data.wid+',\'wall\',this)" data-loading-text="<i class=\'fa fa-spinner fa-spin\'></i>"><i class="fa fa-times"></i></a>\
        '
      }else{
        btnHtml = '<a href="tel:'+data.owner_info.phone+'" class="btn-ripple"><i class="fa fa-phone"></i></a>'
      }

      return '<div class="cp-user-bar">\
        <h2>'+data.owner_info.name+'</h2>\
        <a class=" cp-btn-getRide btn btn-warning btn-fab" onclick="pageMethods.riding()" data-loading-text="<i class=\'cp-iconfont fa fa-circle-o-notch fa-spin\'></i>"> <i class="cp-iconfont fa fa-car"></i> </a>'+
        // '<div class="cp-btns-wrap"><a href="tel:'+data.phone+'" class=""><i class="fa fa-phone"></i></a></div>'+
      '</div>'+
      '<div class="cp-user-subbar">\
        <h4 class="department">'+data.owner_info.Department+'</h4>\
        <h5 class="carnumber">'+data.owner_info.carnumber+'</h5>\
        <div class="cp-btns-wrap">'+btnHtml+'</div>\
      </div>'
    }
  }

  /****** 页面婁據  *******/
  var pageDatas = {
    wid:transition.query.id,
    owner_info:{
      name:'-',
      Department:'-',
      carnumber:'-',
      phone:'-'
    },
    uid:0,
    time_format:'000000 00:00',
    start_info:{addressname:'-'},
    end_info:{addressname:'-'},
    hasGetComments:0,
    commentsTotal:0

  }

  /****** 页面方法  *******/
  pageMethods = {

    goCommentsPage : function(){
      GB_VAR['temp']['commentsTotal'] = pageDatas.commentsTotal;
      redirect("#/comment?wid="+pageDatas.wid);
    },
    /**
     * 提交搭车
     */
    riding : function(){
      dialog.alert({
          title:"提示",
          msg:'您确定要搭该司机的车？',
          buttons:['取消','确定']
      },function(ret){
        if(ret.buttonIndex==1){
          return false;
        }
        if(ret.buttonIndex==2){
          var $pageWrap = $('#Page-wall-view');
          $('.cp-btn-getRide').button('loading');
          $('.cp-btn-submit').button('loading');
          toast.loading({title:"提交请求中",duration:2000});
          cAccessAjax({
            type:'post',
            dataType:'json',
            url:cApiUrl.riding,
            data:{wid:pageDatas.wid},
            success:function(rs){
              if(!cCheckLoginByCode(rs.code)){return false;}
              if(rs.code === 0){
                setTimeout(function(){
                  toast.success({title:rs.desc,  duration:2000});
                },300);
                setTimeout(function(){
                  pageMethods.goMyRouteLists();
                },1000);
              }else{
                setTimeout(function(){
                  toast.fail({title:rs.desc,  duration:2000});
                },300)
              }
            },
            complete:function(XMLHttpRequest, textStatus){
              $('.cp-btn-getRide').button('reset');
              $('.cp-btn-submit').button('reset');
              toast.hide()
              // setTimeout(function(){toast.hide();}, 600)
            }
          });
        }
      });
    },

    /**
     * 完成行程
     */
    finishRoute:function(id,from,obj){
      var options = {
        btnObj: obj,
        url: cApiUrl.finishRoute,
        data: {id:id,from:from},
        msg:'您确定要完结本次行程吗？',
        loadingMsg:'提交中'
      }
      this.cancelOrFinish(options);
    },

    /**
     * 取消行程
     */
    cencelRoute:function(id,from,obj){
      var options = {
        btnObj: obj,
        url: cApiUrl.cancelRoute,
        data: {id:id,from:from},
        msg:'您确定要取消本次行程吗？',
        loadingMsg:'取消中'
      }
      this.cancelOrFinish(options);
    },

    /**
     * 完成和取消行程的ajax提交
     */
    cancelOrFinish:function(options){
      var $btn = $(options.btnObj);
      dialog.alert({
          title:"提示",
          msg:options.msg,
          buttons:['取消','确定']
      },function(ret){
        if(ret.buttonIndex==1){
          return false;
        }
        if(ret.buttonIndex==2){
          toast.loading({title:options.loadingMsg,duration:2000});
          $btn.button('loading');
          cAccessAjax({
            type:'post',
            dataType:'json',
            url:options.url,
            data:options.data,
            success:function(rs){
              if(!cCheckLoginByCode(rs.code)){return false;}
              if(rs.code === 0){
                setTimeout(function(){
                  toast.success({title:rs.desc, duration:2000});
                },300);
                setTimeout(function(){
                  $('.cp-routeCard-item[data-id='+options.data.id+'][data-from=wall]').remove();
                  cGoBack($("#Page-wall-view").find('.cp-btn-goback')[0]);
                },1000);
              }else{
                setTimeout(function(){
                  toast.fail({title:rs.desc, duration:2000});
                },300)
              }

            },
            complete:function(XHR){
              $btn.button('reset');
              toast.hide()
            }
          });
        }
      })
    },

    /**
     * 跳到我的行程页
     */
    goMyRouteLists:function(){
      GB_VAR['jumpTo']= '#/myRoute';
      redirect('#/index');
    },

    /**
     * 取得空座位信息
     */
    loadWallView:function(){
      var that = this;
      var $wallViewWrap = $('#Page-wall-view');
      var $userBar =  $wallViewWrap.find('.cp-user-bar');
      var $userSubBar =  $wallViewWrap.find('.cp-user-subbar');
      //加载空座位数据
      cAccessAjax({
        type:'get',
        dataType:'json',
        url:cApiUrl.getWallView,
        data:{id:pageDatas.wid},
        success:function(rs){

          if(rs.code === 0){
            var data = rs.data;
            that.showWallView(data)
          }else{
            setTimeout(function(){
              toast.fail({title:rs.desc, duration:2000});
            },300)
            setTimeout(function(){
              cGoBack($("#Page-wall-view").find('.cp-btn-goback')[0]);
            },1500)
          }
        },
        complete:function(XMLHttpRequest, textStatus){
          toast.hide()
        }
      });
    },

    showWallView: function(data){
      GB_VAR['temp']['wallViewData'] = data;
      var $wallViewWrap = $('#Page-wall-view');
      var $userBar =  $wallViewWrap.find('.cp-user-bar');
      var $userSubBar =  $wallViewWrap.find('.cp-user-subbar');
      var left_seat = parseInt(data.seat_count) - parseInt(data.took_count);
      left_seat = left_seat < 0 ? 0 : left_seat;
      var theDateArray = data.time_format.split(' ');
      var $staticInner = $wallViewWrap.find('.cp-wallView-statis-inner');

      $staticInner.html('');
      $staticInner.append(cParseTemplates().statisItem({title:'出发时间',icon:'clock-o',num:'<p class="date">'+theDateArray[0]+'</p>'+theDateArray[1],className:'cp-time'}));
      $staticInner.append(cParseTemplates().statisItem({title:'预计路程',icon:'map-signs',num:'--',className:'cp-distance'}));
      $staticInner.append(cParseTemplates().statisItem({title:'空位',icon:'car',num:left_seat}));
      $staticInner.append(cParseTemplates().statisItem({title:'乘客',icon:'user',num:data.took_count}));

      $userBar.remove();$userSubBar.remove();$wallViewWrap.find('.cp-route-wrap').remove();
      //页面赋值
      pageDatas.uid         = data.uid;
      pageDatas.seat_count  = data.seat_count;
      pageDatas.time_format = data.time_format;
      pageDatas.carownid    = data.carownid;
      pageDatas.startpid    = data.startpid;
      pageDatas.endpid      = data.endpid;
      pageDatas.start_info  = data.start_info;
      pageDatas.end_info    = data.end_info;
      pageDatas.owner_info  = data.owner_info;

      // 渲染用户信息栏和路线图
      $('#map-wallView-content').after(pageTempates.routeLine(pageDatas)).after(pageTempates.userBar(pageDatas));
      $userBar =  $wallViewWrap.find('.cp-user-bar');
      $userSubBar =  $wallViewWrap.find('.cp-user-subbar');

      //绘制路线并计算用时及距离
      if(typeof(AMap)=='object'){
        drawRouteLine(new AMap.LngLat(data.start_info.longtitude,data.start_info.latitude),new AMap.LngLat(data.end_info.longtitude,data.end_info.latitude),GB_VAR['map_viewWall'],function(status,result){
          // console.log(status);
          if(status == 'complete'){
            var distance = result.routes[0].distance; //计出的距离
            var distanceObj = formatDistance(distance,1);
            var distanceStr = distanceObj.distance + distanceObj.unit;

            var dtTime = result.routes[0].time;
            var dtTimeStr = formatRouteTime(dtTime);
            var endTime = data.time + dtTime;
            var endDateObj = new Date(endTime*1000);


            $wallViewWrap.find('.cp-route-wrap .cp-end .time').children('span').text(endDateObj.getFullYear()+'-'+fixZero(endDateObj.getMonth()+1)+'-'+fixZero(endDateObj.getDate())).siblings('b').text(fixZero(endDateObj.getHours())+':'+fixZero(endDateObj.getMinutes()));
            $wallViewWrap.find('.cp-route-wrap .cp-item-d').addClass('in').find('.cp-text').text('大约'+ distanceStr+'，预计用时'+dtTimeStr+'。')
            $staticInner.find('.cp-distance .num').html(distanceObj.distance+'<small>'+distanceObj.unit+'</small>')
          }
        });
      }
    },

    /**
     * 读取空座位乘客列表
     */
    loadPassengers:function(){
      var $wallViewWrap = $('#Page-wall-view');
      var $passengerListWrap  = $wallViewWrap.find('.cp-wallView-passenger ul');
      //读取乘客列表
      if($passengerListWrap.attr('data-has')!=1 && $passengerListWrap.attr('data-loading')!=1){
        cGetLists({
          data:{wallid:pageDatas.wid},
          autoScrollTop:0,
          target:$passengerListWrap[0],
          url:cApiUrl.getWallViewPassengers,
          listType:'myroute',
          templateFun:cParseTemplates().wallViewPassengerItem,
          success:function(data){
              $passengerListWrap.attr('data-has',1);
          },
        });
      }
    },

    /**
     * 取得评论列表数据
     */
     getCommentLists:function(){
       var $wallViewWrap = $('#Page-wall-view');
       var $commentLists = $wallViewWrap.find('.cp-comment-list');
       pageDatas.hasGetComments = 1;
       cGetLists({
         data:{wid:pageDatas.wid,num:3},
         // data:{wid:11009},
         target:$commentLists[0],
         url:cApiUrl.wallComments,
         no_data_text:'还未有人评论   (´°̥̥̥̥̥̥̥̥ω°̥̥̥̥̥̥̥̥｀)',
         templateFun:false,
         successStart:function(rs){
           var html = '';
           if(rs.code === 0){
             $.each(rs.data.lists,function(i,item){
   		        html += cParseTemplates().commentItem(item,{loading:0});
   		      })
           }

           var total = rs.data.total ? rs.data.total : 0;
           pageDatas.commentsTotal = total;
           GB_VAR['temp']['commentsTotal'] = pageDatas.commentsTotal;
           $wallViewWrap.find('.cp-comment-count > b').html(total)
           html +="<li class=\"cp-commentLists-tips\" onclick=\"pageMethods.goCommentsPage()\">共"+rs.data.total+"条评论，查看全部</li>";
           $commentLists.html(html);

         },
         complete:function(XMLHttpRequest, textStatus){

         }
       })
     },

     /**
      * 取得评论总数
      */
     getCommentsCount:function(){
       var that = this;
       var $wallViewWrap = $('#Page-wall-view');
       //加载空座位数据
       cAccessAjax({
         type:'get',
         dataType:'json',
         url:cApiUrl.wallComments,
         data:{wid:pageDatas.wid,getcount:1},
         success:function(rs){
           if(rs.code===0){
             var total = rs.data.total ? rs.data.total : 0
             $wallViewWrap.find('.cp-comment-count > b').html(total)
           }
         },
         complete:function(XMLHttpRequest, textStatus){
         }
       });
     },

  }

  /****** 渲染页面  *******/
  // document.getElementById("app").innerHTML = html;
  // 构建地图

  if($('#Page-comment-lists').length>0){
    $('#Page-comment-lists').remove();
  }
  if($('#Page-wall-view').length>0 && GB_VAR['temp']['wallViewData'] != ''){
    pageDatas.commentsTotal =  GB_VAR['temp']['commentsTotal']

    if(typeof(AMap)=='object'){
      GB_VAR['map_viewWall'] =  showMap('map-wallView-content');
    }else{
      cLoadScript(cApiUrl.aMapScript,function(){
        GB_VAR['map_viewWall'] =  showMap('map-wallView-content');
      });
    }
    pageMethods.showWallView(GB_VAR['temp']['wallViewData']);

  }else{
    $('#app').append(pageTempates.content(pageDatas));
    // 渲染用户信息栏
    $('#map-wallView-content').after(pageTempates.userBar(pageDatas));

    toast.loading({title:"加载中",duration:1000},function(ret){});
    if(typeof(AMap)=='object'){
      GB_VAR['map_viewWall'] =  showMap('map-wallView-content');
    }else{
      cLoadScript(cApiUrl.aMapScript,function(){
        GB_VAR['map_viewWall'] =  showMap('map-wallView-content');
      });
    }
    pageMethods.loadWallView();
  }
  // pageMethods.getCommentsCount();



  var $wallViewWrap = $('#Page-wall-view');
  var $scrollWrap = $wallViewWrap.find('.cp-scroller-wrap');
  var $userBar =  $wallViewWrap.find('.cp-user-bar');
  var $userSubBar =  $wallViewWrap.find('.cp-user-subbar');
  var $goBackBtn = $wallViewWrap.find('.cp-btn-goback');
  var $commentBar =  $wallViewWrap.find('.cp-wallView-comment');
  var $commentLists = $wallViewWrap.find('.cp-comment-list');
  $scrollWrap[0].scrollTop = 1;
  // 监听滚动
  $scrollWrap[0].addEventListener('scroll',function(e){
    // console.log('scrollTop:' + e.target.scrollTop);
    // console.log('scrollHeight:' + e.target.scrollHeight);
    $userBar =  $wallViewWrap.find('.cp-user-bar');
    $userSubBar = $wallViewWrap.find('.cp-user-subbar');
    var userbarOSTop = $userSubBar.offset().top - $userBar.outerHeight();
    if(userbarOSTop <1 ){ //固定用户信息栏到顶部
      $userBar.addClass('cp-fixed').find('h2').css({'transform':'translate(50px,0)'});
      $userSubBar.css({'marginTop':$userBar.outerHeight()});
      $goBackBtn.addClass('userBarFixed');
      //读取乘客列表
      pageMethods.loadPassengers(0);
      //滚到底部跳到评论页
      var commentOSTop = $commentBar.offset().top;

      if(e.target.scrollHeight - e.target.scrollTop <= e.target.clientHeight ){
        if(!pageDatas.hasGetComments && pageDatas.commentsTotal < 3 ){
          pageMethods.getCommentLists();
        }

      }

    }else if(userbarOSTop < 60){ // 还原用户信息栏位置
      var oleft = 50 - userbarOSTop;
      oleft = oleft < 0 ? 0 : oleft;
      $userBar.removeClass('cp-fixed').find('h2').css({'transform':'translate('+oleft+'px,0)'});
      $userSubBar.attr('style','');
      $goBackBtn.removeClass('userBarFixed');
    }else{ // 还原用户信息栏位置
      $userBar.removeClass('cp-fixed').find('h2').attr('style','');
      $userSubBar.attr('style','');
      $goBackBtn.removeClass('userBarFixed');
    }
  },false);



  // console.log("首页回调" + JSON.stringify(transition))


}
