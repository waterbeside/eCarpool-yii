SPA_RESOLVE_INIT = function(transition) {
  /****** DOM *******/
  var  pageTempates = {
    content : function(data){
      return '<div class="page-view cp-page-viewInfo" id="Page-info-view" data-id="'+transition.query.id+'">\
        <div class="page-view-main"  >\
          <a class="cp-btn-goback" onclick="cGoBack(this);"> <i class="cp-iconfont fa fa-angle-left"></i> </a>\
          <div class="cp-scroller-wrap" >\
            <div class="cp-scroller">\
              <div class="cp-map-content" id="map-info-content" > ... </div>\
              <div class="cp-content">\
                <div class="cp-heading " >\
                    <img class="cp-avatar  btn-ripple " src="'+cConfig.defaultAvatar+'"  onerror="this.src=\''+cConfig.defaultAvatar+'\';return false">\
                    <div class="cp-txt">\
                      <h3> - </h3>\
                    </div>\
                    <h8>乘客</h8>\
                    <h4 class="department"> - </h4>\
                </div>\
                <div class="cp-heading-bg"></div>\
                <div class="alert ">\
                  <p>正等待被搭载</p>\
                </div>\
                <div class="cp-cell cp-cell-time">\
                    <div class="la"><i class="fa fa-clock-o"></i></div>\
                    <span class="cp-time">0000-00-00 00:00</span>\
                </div>\
                <div class="cp-btns-wrap">\
                  <a class="cp-btn  cp-btn-phone " href="tel:000"><i class="cp-icon fa fa-phone"></i>电 话</a>\
                  <a class="cp-btn cp-btn-back " onclick="cGoBack()"><i class="cp-icon fa fa-arrow-left"></i>返 回</a>\
                  <a class="cp-btn cp-btn-pickup " onclick="pageMethods.acceptDemand('+data.infoid+',this)"><i class="cp-icon fa fa-car"></i>接受请求</a>\
                  <a class="cp-btn cp-btn-cancel "  onclick="pageMethods.cencelRoute('+data.infoid+',\'info\',this)"><i class="cp-icon fa fa-times"></i>取 消</a>\
                  <a class="cp-btn cp-btn-ok "  onclick="pageMethods.finishRoute('+data.infoid+',\'info\',this)"><i class="cp-icon fa fa-check"></i>完 成</a>\
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
        <div class="cp-route-line"><div class="cp-line"></div></div>\
        <div class="cp-route-content">\
          <div class="cp-item cp-start">\
            <b class="cp-p"></b>\
            <h4>'+data.start_info.addressname+'</h4>\
            <label>出发 FROM</label>\
          </div>\
          <div class="cp-item  cp-end">\
            <b class="cp-p"></b>\
            <h4>'+data.end_info.addressname+'</h4>\
            <label>结束 TO</label>\
          </div>\
        </div>\
      </div>'
    },


  }

  /****** 页面婁據  *******/
  var pageDatas = {
    infoid:transition.query.id,
    iscarown:transition.query.iscarown,


  }

  /****** 页面方法  *******/
  pageMethods = {

    /**
     * 跳到我的行程页
     */
    goMyRouteLists:function(){
      GB_VAR['jumpTo']= '#/myRoute';
      redirect('#/index');
    },

    /**
     * 取得需求信息
     */
    loadInfoView:function(){
      var that          = this;
      var $wallViewWrap = $('#Page-wall-view');
      var $userBar      =  $wallViewWrap.find('.cp-user-bar');
      var $userSubBar   =  $wallViewWrap.find('.cp-user-subbar');
      //加载空座位数据
      cAccessAjax({
        type:'get',
        dataType:'json',
        url:cApiUrl.getInfoView,
        data:{id:pageDatas.infoid},
        success:function(rs){
          if(rs.code===0){
            var data = rs.data;
            that.showInfoView(data)
          }else{
            setTimeout(function(){
              toast.fail({title:rs.desc, duration:2000});
            },300)
            setTimeout(function(){
              cGoBack();
            },1500)
          }
        },
        complete:function(XMLHttpRequest, textStatus){
          toast.hide()
        }
      });
    },

    showInfoView: function(data){
      console.log(data)
      GB_VAR['temp']['wallViewData'] = data;
      var $infoViewWrapper  = $('#Page-info-view');
      var $userWrapper      = $infoViewWrapper.find('.cp-heading');
      var $btnsWrapper      = $infoViewWrapper.find('.cp-btns-wrap');
      var $alertBox         = $infoViewWrapper.find('.alert');
      var theDateArray      = data.time_format.split(' ');


      // 渲染用户信息栏和路线图
      $userData = pageDatas.iscarown == 1 && typeof(data.owner_info)!='undefined' ?  data.owner_info : data.passenger_info;
      $infoViewWrapper.find('.cp-cell-time').after(pageTempates.routeLine(data));
      $infoViewWrapper.find('.cp-cell-time').find('.cp-time').text(data.time_format);
      $userWrapper.find('.cp-avatar').attr('src', cConfig.avatarPath + $userData.imgpath);


      $userWrapper.find('h3').html($userData.name);
      $userWrapper.find('h8').html(pageDatas.iscarown ? '司机' : '乘客');

      if(pageDatas.iscarown){
        $userWrapper.find('.alert').hide();
      }




      $userWrapper.find('.department').html($userData.Department);
      $userWrapper.find('.time');

      if(data.uid==data.passenger_info.uid){
        $btnsWrapper.find('.cp-btn-phone').hide()
        $btnsWrapper.find('.cp-btn-pickup').hide()
      }else{
        if(data.uid!=data.owner_info.uid){
          $btnsWrapper.find('.cp-btn-ok').hide()
          $btnsWrapper.find('.cp-btn-cancel').hide()
        }
        if( typeof(data.owner_info.uid)!='undefined'){
          $btnsWrapper.find('.cp-btn-pickup').hide()
        }

      }
      $btnsWrapper.find('.cp-btn-back').hide()
      if(data.status> 1 ){
        $btnsWrapper.find('.cp-btn-pickup').hide()
        $btnsWrapper.find('.cp-btn-cancel').hide()
        $btnsWrapper.find('.cp-btn-ok').hide()
      }
      if(data.status == 0){
        $alertBox.addClass('alert-warning').find('p').text('正等待被搭载...')
      }
      if(data.status == 1){
        $alertBox.addClass('alert-info').find('p').text('已被搭载.')
      }
      if(data.status == 2){
        $alertBox.addClass('alert-danger').find('p').text('本行程已经取消.')
      }
      if(data.status == 3){
        $alertBox.addClass('alert-success').find('p').text('本行程已完结')
      }
      // $userBar =  $wallViewWrap.find('.cp-user-bar');
      // $userSubBar =  $wallViewWrap.find('.cp-user-subbar');


      //绘制路线并计算用时及距离
      if(typeof(AMap)=='object'){
        drawRouteLine(new AMap.LngLat(data.start_info.longtitude,data.start_info.latitude),new AMap.LngLat(data.end_info.longtitude,data.end_info.latitude),GB_VAR['map_viewInfo'],function(status,result){
          // console.log(status);
          if(status == 'complete'){
            var distance = result.routes[0].distance; //计出的距离
            var distanceObj = formatDistance(distance,1);
            var distanceStr = distanceObj.distance + distanceObj.unit;

            var dtTime = result.routes[0].time;
            var dtTimeStr = formatRouteTime(dtTime);
            var endTime = data.time + dtTime;
            var endDateObj = new Date(endTime*1000);
          }
        });
      }
    },


    /**
     * 接受约车需求
     */
    acceptDemand:function(id,obj){
      var $btn = $(obj);
      dialog.alert({
          title:"提示",
          msg:'您确定要搭载该乘客吗？',
          buttons:['取消','确定']
      },function(ret){
        if(ret.buttonIndex==1){
          return false;
        }
        if(ret.buttonIndex==2){
          $btn.button('loading');
          cAccessAjax({
            type:'post',
            dataType:'json',
            url:cApiUrl.acceptDemand,
            data:{id:id},
            success:function(rs){
              if(!cCheckLoginByCode(rs.code)){return false;}
              if(rs.code === 0){
                toast.success({title:rs.desc,  duration:2000});
                pageMethods.goMyRouteLists()
              }else{
                toast.fail({title:rs.desc,  duration:2000});
              }
            },
            complete:function(XHR){
              $btn.button('reset');
            }
          });
        }
      })
    },
    /**
     * 取消行程
     */
    cencelRoute:function(id,from,obj){
      var options = {
        btnObj: obj,
        url: cApiUrl.cancelRoute,
        data: {id:id,from:from},
        msg:'您确定要取消吗？'
      }
      this.cancelOrFinish(options);
    },
    /**
     * 完成行程
     */
    finishRoute:function(id,from,obj){
      var options = {
        btnObj: obj,
        url: cApiUrl.finishRoute,
        data: {id:id,from:from},
        msg:'您确定要完结本次行程吗？'
      }
      this.cancelOrFinish(options);
    },

    /**
     * 完成和取消行程的ajax提交
     */
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
                   $('.cp-routeCard-item[data-id='+options.data.id+'][data-from=info]').remove();
                   cGoBack($("#Page-info-view").find('.cp-btn-goback')[0]);
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



  }

  /****** 渲染页面  *******/
  // document.getElementById("app").innerHTML = html;
  $('.cp-page-viewInfo').remove();
  // 构建地图
  if($('#Page-info-view').length>0 && GB_VAR['temp']['infoViewData'] != ''){
    if(typeof(AMap)=='object'){
      GB_VAR['map_viewInfo'] =  showMap('map-info-content');
    }else{
      cLoadScript(cApiUrl.aMapScript,function(){
        GB_VAR['map_viewInfo'] =  showMap('map-info-content');
      });
    }
    pageMethods.showInfoView(GB_VAR['temp']['infoViewData']);

  }else{
    $('#app').append(pageTempates.content(pageDatas));
    if(typeof(AMap)=='object'){
      GB_VAR['map_viewInfo'] =  showMap('map-info-content');
    }else{
      cLoadScript(cApiUrl.aMapScript,function(){
        GB_VAR['map_viewInfo'] =  showMap('map-info-content');
      });
    }
    toast.loading({title:"加载中",duration:1000},function(ret){});
    pageMethods.loadInfoView();
  }
  // pageMethods.getCommentsCount();




  // console.log("首页回调" + JSON.stringify(transition))


}
