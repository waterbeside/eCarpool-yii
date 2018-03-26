SPA_RESOLVE_INIT = function(transition) {


  /****** DOM *******/
  var  pageTempates = {
    wrapper : function(data,con){
      return '<div class="page-view cp-modal-add-'+data.from+'" id="'+data.pageID+'">'+con+'</div>';
    },
    inner : function(data){
      var html = '\
      <div class="page-view-inner" >\
      '+cParseTemplates().pageViewTitle({title:data.title})+'\
        <div class="page-view-main cp-pdt"    >\
        <div class="cp-map-wap">\
          <div class="cp-map-content" id="map-add-content" style="width:100%; height:100%; min-height:500px;">地图加载中</div>\
          <div class="cp-map-form"></div>\
        </div>\
        <!-- /cp-map-wrap -->\
        </div>\
      </div>\
    '
      return html;
    },

    /* 最近使用的行程 */
    recentlyBox: function(data){
      var html = '<div class="cp-recently-wrapper">\
        <div class="cp-recently-inner">\
          <h5>常用路线</h5>\
          <div class="cp-content">'
          for(i=0; i<data.length || i<2 ; i++){
            html += '   <div class="cp-item" onclick="pageMethods.putOftenDatas('+i+')">\
                <b>'+data[i].trip_time.substr(0,2)+':'+data[i].trip_time.substr(2,4)+'</b>\
                <span class="cp-start">'+data[i].from_address+'</span>\
                <span class="cp-to"><i class="fa fa-long-arrow-right"></i></span>\
                <span class="cp-end">'+data[i].to_address+'</span>\
              </div>'
          }
          html += ' </div>\
        </div>\
      </div>'
      return html;
    },

    /* 计算路程和用时后显示的内容 */
    computeBox: function(data){
      var content = '';
      if(typeof(data)=='string'){
        content = '<h4>'+data+'</h4>'
      }else{
        content = '<h5>预计</h5>\
        <div class="cp-content">\
          <div class="cp-item"><i class="fa fa-map-signs"></i><b>'+data.distance+'</b></div>\
          <div class="cp-item"><i class="fa fa-clock-o"></i><b>'+data.time+'</b></div>\
        </div>'
      }
      var html = '<div class="cp-computebox-wrapper">\
        <div class="cp-computebox-inner">\
          '+content+'\
        </div>\
      </div>\
      '
      return html;
    },

    /* 表单 */
    form:function(data){
      return '<form   method="post" onsubmit="return false;">\
        <div class="cp-map-form-inner">\
          <div class="cp-selectbtn-wrap cp-needTime-sWrap" id="J-needTime">\
            <a class="btn datetime-picker-btn btn-ripple"   href="javascript:void(0);" onclick="selectNeedTime(\'#J-needTime\',pageMethods.putData);">\
              <i class="fa fa-clock-o"></i>\
              <span class="cp-ph" >请选择时间</span>\
              <span class="cp-text">'+data.text_time+'</span>\
            </a>\
          </div>\
          <div class="cp-selectbtn-wrap cp-seatCount-sWrap" id="J-pick-seatCount">\
            <a class="btn seat-picker-btn btn-ripple"   href="javascript:void(0);" onclick="selectSeatCount(\'#J-pick-seatCount\',pageMethods.putData);">\
              <i class="fa fa-car"></i>\
              <span class="cp-ph" >空座位数</span>\
              <span class="cp-text">'+data.text_seat+'</span>\
            </a>\
          </div>\
          <div class="cp-selectbtn-wrap cp-startp-wrap" id="J-startPoint">\
            <a class="btn btn-ripple" onclick="return  pageMethods.addressSelect(\'start\');" href="javascript:void(0);">\
              <i class="fa fa-map-marker"></i>\
              <span class="cp-ph">起点</span>\
              <span class="cp-text">'+data.text_start+'</span>\
            </a>\
          </div>\
          <div class="cp-selectbtn-wrap cp-endp-wrap"  id="J-endPoint">\
            <a class="btn" onclick="return pageMethods.addressSelect(\'end\');" href="javascript:void(0);">\
              <i class="fa fa-map-marker"></i>\
              <span class="cp-ph">终点</span>\
              <span class="cp-text">'+data.text_end+'</span>\
            </a>\
          </div>\
        </div>\
        <button class="btn btn-warning btn-block cp-btn-submit " onclick="return pageMethods.submitInfoAdd()"><i class="fa fa-paper-plane"></i> 发布</button>\
      </form>'
    },



  }


  /****** 页面婁據  *******/
  var pageDatas = {
    pageID : 'Page-route-add',
    title:'约车需求',
    from :'',
    formData:{},
    text_time:'',
    text_seat:'',
    text_start:'',
    text_end:'',
    oftenData:[]
  }

  /****** 页面方法  *******/
  pageMethods = {
    //站点选择模态框
    addressSelect : function(from){
      from = from || '0';
      redirect('#/selectMyAddress?from='+ from);
    },
    /**
     * 发布页显示提交按钮
     */
    showAddRouteBtn : function(){
      if($('#J-needTime').hasClass('cp-selected') && $('#J-startPoint').hasClass('cp-selected') && $('#J-endPoint').hasClass('cp-selected') ){
        if(!$('#'+pageDatas.pageID).hasClass('cp-modal-add-wall') || $('#J-pick-seatCount').hasClass('cp-selected') ){
          $('.cp-map-form .cp-btn-submit').addClass('in');
        }
      }
    },
    /**
     * 添加数据到GB_VAR
     */
    putData:function(data,key){
      if(key=='time'){
        GB_VAR['addRoute_datas']['time']        = data.val;
      }
      if(key=='seat_count'){
        GB_VAR['addRoute_datas']['seat_count']  = data.val;
      }
      pageDatas.formData[key] = data;
      console.log(pageDatas);
    },

    /**
     * 点击常用行程后自行添加数据
     */
    putOftenDatas:function(dataIndex){
       var data
       var datas = pageDatas.oftenData[dataIndex];
       /**处理时间**/
       var nowDate  = new Date();
       //个位数补充0
       function fixZero(num){
         num = num >= 0 && num <= 9 ?   "0" + num : num;
         return num;
       }
       var month    = fixZero(nowDate.getMonth()+1);
       var toDay    = fixZero(nowDate.getDate());
       var dataTime = {text:month+'月'+toDay+'日 '+datas.trip_time.substr(0,2)+'点'+datas.trip_time.substr(2,4)+'分 (今天)',val:''+month+toDay+datas.trip_time}
       pageMethods.putData(dataTime,'time');
       $('#J-needTime').addClass('cp-selected').attr('data-val',dataTime.val).attr('data-date',''+month+toDay).attr('data-hour',datas.trip_time.substr(0,2)).attr('data-min',datas.trip_time.substr(2,4)).find('.cp-text').show().text(dataTime.text).siblings('.cp-ph').hide();

       /**处理起点和终点**/
       GB_VAR['addRoute_datas']['start'] = {
         aid:datas.startpid,
         latitude:datas.from_latitude,
         longtitude:datas.from_longitude,
         name:datas.from_address,
       };
       GB_VAR['addRoute_datas']['end'] = {
         aid:datas.endpid,
         latitude:datas.to_latitude,
         longtitude:datas.to_longitude,
         name:datas.to_address,
       };
       $('#J-startPoint').addClass('cp-selected').attr('data-aid',datas.startpid).attr('data-latitude',datas.from_latitude).attr('data-longtitude',datas.from_longtitude).find('.cp-text').show().text(datas.from_address).siblings('.cp-ph').hide();
       $('#J-endPoint').addClass('cp-selected').attr('data-aid',datas.endpid).attr('data-latitude',datas.to_latitude).attr('data-longtitude',datas.to_longitude).find('.cp-text').show().text(datas.to_address).siblings('.cp-ph').hide();
       pageMethods.computeRoute();

       //填写座位数
       if(pageDatas.from=='wall'){
         var dataSeatCount = {text:datas.seat_count+'个',val:datas.seat_count} ;
         pageMethods.putData(dataSeatCount,'seat_count');
         $('#J-pick-seatCount').addClass('cp-selected').attr('data-val',datas.seat_count).find('.cp-text').show().text(datas.seat_count+'个').siblings('.cp-ph').hide();

       }
       pageMethods.showAddRouteBtn();

      //  console.log(pageDatas.formData);
    },



    /**
     * 跳到我的行程页
     */
    goMyRouteLists:function(){
      GB_VAR['jumpTo']= '#/myRoute';
      redirect('#/index');
    },

    /**
     * 取得常用行程
     */
    getOfentTrips: function(){
      cAccessAjax({
        type:'post',
        dataType:'json',
        data:{from:pageDatas.from},
        url:cApiUrl.getOfentTrips,
        success:function(rs){
          if(!cCheckLoginByCode(rs.code)){return false;}
          if(rs.code === 0){
            var $pageWrap =  $('#'+pageDatas.pageID);
            pageDatas.oftenData = rs.data;
            GB_VAR['temp']['oftenData'] = pageDatas.oftenData;
            $pageWrap.append(pageTempates.recentlyBox(rs.data));
          }
        }
      });
    },

    /**
     * 提交发布
     */
    submitInfoAdd: function(){
      var datas = {}
      datas= GB_VAR['addRoute_datas'];
      datas.startpid = datas.start.aid;
      datas.endpid = datas.end.aid;
      if(pageDatas.from == 'wall'){
        var url =  cApiUrl.addWall;
        // datas.seat_count = GB_VAR['addRoute_datas']['seat_count'];
      }else if(pageDatas.from == 'info'){
        var url =  cApiUrl.addInfo;
      }else{
        return false;
      }

      toast.loading({title:"提交中",duration:1000});

      cAccessAjax({
        type:'post',
        dataType:'json',
        data:datas,
        url:url,
        success:function(rs){
          if(!cCheckLoginByCode(rs.code)){return false;}
          if(rs.code === 0){
            if(rs.data.createAddress.length>0){
              // console.log(rs.data.createAddress)
              var newDatas = rs.data.createAddress;
              for(i=0;i<newDatas.length;i++){
                newDatas[i].addressname = newDatas[i].name;
                newDatas[i].listorder = 3;
                newDatas[i].address_type = 'new';
                cModel.myAddress('add',{data:newDatas[i]});
                // pageMethods.addAddressToDB(newDatas[i]);
              }
            }
            setTimeout(function(){
              toast.success({title:'发布成功',  duration:2000});
            },300)
            setTimeout(function(){
              pageMethods.goMyRouteLists();
            },1000)
          }else{
            var msg = rs.desc!='' ? rs.desc : '发布失败';
            dialog.alert({
                title:"提交失败",
                msg:msg,
                buttons:['确定']
            },function(ret){
                console.log(ret)
            })
          }
        },
        complete:function(XMLHttpRequest, textStatus){
          toast.hide();
        }
      })
      console.log(datas);
    },

    /**
     * 绘制路线后显示用时与路程
     */
    computeRoute: function(){
      var datas = GB_VAR['addRoute_datas'];
      // console.log(datas);
      // return false;
      if(GB_VAR['map_addRoute']){
        if(typeof(GB_VAR['addRoute_datas'].start)!='undefined' && typeof(GB_VAR['addRoute_datas'].end)!='undefined')
        drawRouteLine(new AMap.LngLat(datas.start.longtitude,datas.start.latitude),new AMap.LngLat(datas.end.longtitude,datas.end.latitude),GB_VAR['map_addRoute'],function(status,result){
          var $pageWrap =  $('#'+pageDatas.pageID);
          $pageWrap.find('.cp-computebox-wrapper').remove();
          if(status == 'complete'){
            var distance = result.routes[0].distance; //计出的距离
            var distanceStr = formatDistance(distance);
            var dtTime = result.routes[0].time;
            var dtTimeStr = formatRouteTime(dtTime);
            var data = {distance:distanceStr,time:dtTimeStr}
            GB_VAR['addRoute_datas']['distance'] = result.routes[0].distance;
          }else{
            var data= '路线过长，无法预测行程时间';
            GB_VAR['addRoute_datas']['distance'] = 0;
          }
          $pageWrap.find('.cp-recently-wrapper').hide();
          $pageWrap.append(pageTempates.computeBox(data));
        });
      }
    },

    selectFromAddress: function(){
      if(typeof(GB_VAR.addRoute_datas.start)!='undefined'){
        var data_s = GB_VAR.addRoute_datas.start;
        $('#J-startPoint').addClass('cp-selected').attr('data-aid',data_s.aid).attr('data-latitude',data_s.latitude).attr('data-longtitude',data_s.longtitude).find('.cp-text').show().text(data_s.name).siblings('.cp-ph').hide();
      }
      if(typeof(GB_VAR.addRoute_datas.end)!='undefined'){
        var data_e = GB_VAR.addRoute_datas.end;
        $('#J-endPoint').addClass('cp-selected').attr('data-aid',data_e.aid).attr('data-latitude',data_e.latitude).attr('data-longtitude',data_e.longtitude).find('.cp-text').show().text(data_e.name).siblings('.cp-ph').hide();
      }
      pageMethods.computeRoute();
    },

    /**
     * 添加地址到数据库中
     */
  /*  addAddressToDB: function(data){
      db.open({
        server: 'carpool_'+ GB_VAR['username'],
        version: 1,
        schema: {
          my_address: {
            key: {keyPath: 'addressid', autoIncrement: true},
            // Optionally add indexes
            indexes: {
                listorder:{},
                address_type: {},
                addressname: {},
                latitude: {},
                longtitude: {},
                address:''
            }
          }
        }
      }).then(function (s) {
        // $data.listorder  = 1;
        server.my_address.add(data).then(function(rs){
          console.log(rs)
        });
      })
    }*/


   }

   //重置所有已选数据；
   function clearSelected(target,type){
     var $target = $(target);
     type = type || 0;
     $(target).removeClass('cp-selected').find('.cp-ph').show().siblings('.cp-text').hide().text('');
     switch (type) {
       case 'time':
         $(target).attr('data-val','').attr('data-selectedindex','').attr('data-date','').attr('data-hour','').attr('data-min','');
         break;
       case 'seat':
         $(target).attr('data-val','').attr('data-selectedindex','');
         break;
       default:
         $(target).attr('data-latitude','').attr('data-longtitude','');
     }
   }


   var from = transition.query.from;
   pageDatas.from = from;
   pageDatas.title = from == 'wall' ? '发布空座位' : '我要约车';


   /****** 渲染页面  *******/
   //断定是否已加载有，是则不重新加载
   if($('#'+pageDatas.pageID).length>0){
    $('#'+pageDatas.pageID).siblings('.page-view').remove();
    var $pageWrap =  $('#'+pageDatas.pageID);
    if(typeof(GB_VAR['temp']['oftenData'])=="object"){
      pageDatas.oftenData = GB_VAR['temp']['oftenData'] ;
    }
    if(GB_VAR['doMenthods']!='' && typeof(pageMethods[GB_VAR['doMenthods']])=='function'){
      pageMethods[GB_VAR['doMenthods']]();
      GB_VAR['doMenthods']=''
    }

   }else{
    // document.getElementById("app").innerHTML = html;
    $('#app').append(pageTempates.wrapper(pageDatas,pageTempates.inner(pageDatas)));
    $('#app').find('.cp-map-form').html(pageTempates.form(pageDatas));
    toast.loading({title:"加载中",duration:1000},function(ret){setTimeout(function(){toast.hide();}, 1000)});
    clearSelected('#J-needTime','time');
    clearSelected('#J-pick-seatCount','seat');
    clearSelected('#J-startPoint',0);
    clearSelected('#J-endPoint',0);
    var $pageWrap =  $('#'+pageDatas.pageID);
    $pageWrap.find('.cp-btn-submit').removeClass('in');
    // 构建地图
    if(typeof(AMap)=='object'){
        GB_VAR['map_addRoute'] = showMap('map-add-content');
    }else{
      cLoadScript(cApiUrl.aMapScript,function(){
        GB_VAR['map_addRoute'] = showMap('map-add-content');
      });
    }

    pageMethods.getOfentTrips();
   }

   // console.log("首页回调" + JSON.stringify(transition))

   /****** 加载完成行执行页面  *******/
   if(from=='info'){
     $('#J-pick-seatCount').hide();
   }else if(from=='wall'){
     $('#J-pick-seatCount').show();
   }




 }
