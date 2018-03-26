SPA_RESOLVE_INIT = function(transition) {


  /****** DOM *******/
  var  pageTempates = {
    wrapper : function(data,con){
      return '<div class="page-view " id="'+data.pageID+'">'+con+'</div>';
    },
    inner : function(data){
      var html = '\
      <div class="page-view-inner" >\
      '+cParseTemplates().pageViewTitle({title:data.title})+'\
        <div class="page-view-main cp-pdt" >\
        <div class="cp-map-wap cp-map-addAddress-wap">\
          <div class="cp-map-content" id="map-address-content" style="width:100%; height:100%; min-height:500px;">地图加载中</div>\
          <div class="cp-address-tips-box">\
            <div class="cp-inner"><p>请点击地图任意位置，以选择。</p></div>\
          </div>\
        </div>\
        <!-- /cp-map-wrap -->\
        </div>\
      </div>\
    '
      return html;
    },

    infoBox:function(data){
      console.log(data)
      var html = '\
        <div class="cp-markInfo-box">\
          <div class="form-horizontal">\
            <div class="cp-form-group ">\
              <label  class="control-label"  for="addressname"><i class="fa fa-map-marker"></i>站点名称</label>\
              <input class="form-control form-control-line" type="text" name="addressname" value="'+data.keyword+'" onkeyup="pageMethods.changeKeyword(this)"/>\
            </div>\
            <div class="">\
              地址：'+data.pointData.regeocode.formattedAddress+'\
            </div>\
            <button class="btn btn-primary btn-block btn-submit" onclick="return pageMethods.submitAddress(this)">确 定</button>\
          </div>\
        </div>\
      '
      return html;
    }


  }


  /****** 页面婁據  *******/
  var pageDatas = {
    pageID : 'Page-address-add',
    title:'新增地址',
    from: transition.query.from.toLowerCase(),
    city :typeof(GB_VAR.local_city.city) == 'string' ? GB_VAR.local_city.city :'',
    keyword : decodeURI(transition.query.keyword),
    keyword_o: '',
    pointData : {},
    markers:[],
    marker : {},
    geocoder: {},
    infoWindow:{}
  }

  /****** 页面方法  *******/
  pageMethods = {
    /**
     * 构建信息窗体中显示的内容
    */
    openInfo:function() {
       infoWindow = new AMap.InfoWindow({
           content: pageTempates.infoBox(pageDatas),  //使用默认信息窗体框样式，显示信息内容
           offset: new AMap.Pixel(0, -25)
       });
       infoWindow.open(GB_VAR['map_address'], pageDatas.pointData.lnglat);
    },

    /**
     * 取得坐标的地址信息，并弹窗
    */
    getMarkerInfo:function(){
      var geocoder = pageDatas.geocoder;
      // var marker = pageDatas.marker;
      var lnglat = pageDatas.pointData.lnglat;
      // marker.setPosition(lnglat);
      geocoder.getAddress(lnglat,function(status,result){
        // console.log(result);
        console.log(lnglat);
        pageDatas.pointData = result;
        pageDatas.pointData.lnglat = lnglat;
        if(status=='complete'){
          pageMethods.openInfo();
          $('#'+pageDatas.pageID).find('input').focus();
        }else{
           // message.innerHTML = '无法获取地址'
        }
      })
    },
    /**
     * 构建信息窗体中显示的内容
    */
    openInfo:function() {
      if(typeof(pageDatas.infoWindow.Pg)!='undefined'){
        var infoWindow = pageDatas.infoWindow;
      }else{
        infoWindow = new AMap.InfoWindow({
            // content: pageTempates.infoBox(pageDatas),  //使用默认信息窗体框样式，显示信息内容
            offset: new AMap.Pixel(0, -25)
        });
        pageDatas.infoWindow = infoWindow ;
      }
      infoWindow.setContent(pageTempates.infoBox(pageDatas));
       infoWindow.open(GB_VAR['map_address'], pageDatas.pointData.lnglat);
    },

    /**
     * 更新关键词
    */
    changeKeyword: function(obj){
      var $input = $(obj);
       pageDatas.keyword = $.trim($input.val());
       pageMethods.searchMap();
    },

    /**
     * 创建标注点
     */
     setMarker: function(lnglat){
       var marker = new AMap.Marker({
          map:GB_VAR['map_address'],
          bubble:false, //是否冒泡
          /*icon: new AMap.Icon({
            size: new AMap.Size(40, 50),  //图标大小
            // image: "http://webapi.amap.com/theme/v1.3/images/newpc/btn.png",
            imageOffset: new AMap.Pixel(0, -60)
          })*/
       });
       if(typeof(lnglat)!="undefined" && lnglat ){
         marker.setPosition(lnglat);
       }

       //鼠标点击marker弹出自定义的信息窗体
       AMap.event.addListener(marker, 'click', function(e) {
         pageDatas.pointData.lnglat = [marker.Pg.position.lng,marker.Pg.position.lat]
           pageMethods.getMarkerInfo();
       });
      //  console.log(marker);
       return marker
     },

     /**
      * 清除搜索项目
      */
      removeAllMarker: function(){
        console.log(pageDatas.markers.length)
        for(i=0;i < pageDatas.markers.length;i++){
          if(pageDatas.markers[i]){
            pageDatas.markers[i].setMap(null)
            pageDatas.markers[i] = null;
          }
          // console.log(ppageDatas.markers[i])
        }
      },

    /**
     * 通过关键词添加标注点
     */
    searchMap : function(autoShow){
      autoShow = autoShow  || 0;
      AMap.service('AMap.PlaceSearch',function(){//回调函数
       //实例化PlaceSearch
       var placeSearch = new AMap.PlaceSearch({ //构造地点查询类
            pageSize: 15,
            pageIndex: 1,
            city: pageDatas.city//城市
        });
        if(pageDatas.keyword_o == pageDatas.keyword || pageDatas.keyword == ''){
          return false;
        }

        pageDatas.keyword_o = pageDatas.keyword;
        console.log(pageDatas.keyword)
        //关键字查询
        placeSearch.search(pageDatas.keyword, function(status, result) {
          if( typeof(result.poiList)!='undefined' && result.poiList.pois.length>0){
            pageMethods.removeAllMarker();
            for(i=0;i<result.poiList.pois.length;i++){
              var datas = result.poiList.pois[i]
              var lnglat = [datas.location.lng,datas.location.lat]
              // console.log(0)
              pageDatas.markers[i] = pageMethods.setMarker(lnglat);
              // console.log(pageDatas.markers[i])
            }
            if(autoShow){ //自动显示第一个的信息框
              pageDatas.pointData.lnglat = [pageDatas.markers[0].Pg.position.lng,pageDatas.markers[0].Pg.position.lat]
              pageDatas.marker = pageMethods.setMarker(pageDatas.pointData.lnglat);
              pageMethods.getMarkerInfo();

            }
          }else{
            if(autoShow){ //自动中央标注点信息
              pageDatas.marker = pageMethods.setMarker();
              pageDatas.pointData.lnglat = [pageDatas.marker.Pg.position.lng,pageDatas.marker.Pg.position.lat]
              pageMethods.getMarkerInfo();
            }
          }

        });
       })
    },

    /**
     * 提交站点
    */
    submitAddress: function(){
      var pointData = pageDatas.pointData;

      var datas = {
        longtitude:pointData.lnglat[0],
        latitude:pointData.lnglat[1],
        addressname: pageDatas.keyword,
        address:pointData.regeocode.formattedAddress,
        from:pageDatas.from,
        city:pointData.regeocode.addressComponent.city
      }
      console.log(datas)
      toast.loading({title:"提交中",duration:1000});
      cAccessAjax({
        type:'post',
        dataType:'json',
        data:datas,
        url:cApiUrl.createAddress,
        success:function(rs){
          if(!cCheckLoginByCode(rs.code)){return false;}
          if(rs.code === 0){
            var inDatas = {
              addressid:rs.data.aid,
              listorder:3,
              addressname: datas.addressname,
              latitude: datas.latitude,
              longtitude: datas.longtitude,
              address:datas.address
            };
            // pageMethods.addAddressToDB(inDatas);
            cModel.myAddress('add',{data:inDatas,success:function(result){console.log(result)}})
            setTimeout(function(){
              toast.success({title:'完成',  duration:2000});
            },300)
              if(pageDatas.from == 'start' || pageDatas.from == 'end' ){
                GB_VAR['addRoute_datas'][pageDatas.from] = {
                  aid:rs.data.aid,
                  latitude:datas.latitude,
                  longtitude:datas.longtitude,
                  name:datas.addressname
                };
                GB_VAR['doMenthods'] = 'selectFromAddress';
                GB_VAR['temp']['openCreateAddress'] = 0 ;
                setTimeout(function(){
                  cGoBack(-2);
                },300)
              }else if( pageDatas.from == 'home' || pageDatas.from == 'company' ){

              }
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
    },


   }





   /****** 渲染页面  *******/
   //断定是否已加载有，是则不重新加载

   if(!$("#Page-route-add").length && !$("#Page-user-profile").length  ){
     cGoBack();
     return false;
   }
   if($("#"+pageDatas.pageID).length > 0 ){
     $("#"+pageDatas.pageID).remove();
   }

    $('#app').append(pageTempates.wrapper(pageDatas,pageTempates.inner(pageDatas)));
    if(typeof(AMap)=='object'){
        GB_VAR['map_address'] = showMap('map-address-content');
    }else{
      cLoadScript(cApiUrl.aMapScript,function(){
        GB_VAR['map_address'] = showMap('map-address-content');
      });
    }
    AMap.plugin(['AMap.ToolBar','AMap.Scale','AMap.OverView'],function(){
          GB_VAR['map_address'].addControl(new AMap.ToolBar());
          GB_VAR['map_address'].addControl(new AMap.Scale());
          // GB_VAR['map_address'].addControl(new AMap.OverView({isOpen:true}));
    });


   /****** 加载完成行执行页面  *******/
   //檢查是否已取得當前城市 ，如果沒有，則通過高德地圖獲取一次。
   if(typeof(GB_VAR.local_city.city) != 'string' || GB_VAR.local_city.city==''){
     var map = new AMap.Map('cp-map-hidden', {
         resizeEnable: true,
     });
     map.getCity(function(data) {
         if (data['province'] && typeof data['province'] === 'string') {
           GB_VAR.local_city =  data ;
         }
     });
   }
   pageMethods.searchMap(1);

   /*
   点击地图，取得标注点信息
    */
   AMap.plugin('AMap.Geocoder',function(){
       var geocoder = new AMap.Geocoder({
           city:pageDatas.city//城市，默认：“全国”
       });
       pageDatas.geocoder = geocoder;
      //  pageDatas.marker = marker;
      //  pageDatas.pointData.lnglat = [marker.Pg.position.lng,marker.Pg.position.lat];
      //  console.log(pageDatas.pointData.lnglat)
      //  pageMethods.getMarkerInfo();
      // pageMethods.getMarkerInfo(eDatas);
       GB_VAR['map_address'].on('click',function(e){
         var marker = {}
         if(typeof(pageDatas.marker.Pg)=='object'){
           marker = pageDatas.marker
         }else{
           marker = pageMethods.setMarker(e.lnglat);
           pageDatas.marker = marker;
         }
         pageDatas.pointData.lnglat = e.lnglat
         marker.setPosition(e.lnglat);
         pageMethods.getMarkerInfo();
        // pageMethods.getMarkerInfo();
       })
   });



 }
