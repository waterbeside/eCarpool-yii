SPA_RESOLVE_INIT = function(transition) {
  /****** DOM *******/
  var  pageTempates = {
    content : function(){
      return '\
      <div class="page-view cp-page-points  " id="'+pageDatas.pageID+'" >\
        <div class="cp-searchbar-wrap"  >\
          <div class="cp-searchbar">\
            <div class="cp-searchbar-input"  >\
                <i class=" fa fa-search"></i>\
                <form onsubmit="return false;" >\
                    <input type="search" name="keyword" placeholder="请输入地点关键词" id="Input-searchAddress" onkeyup="pageMethods.findAddress(this)"  >\
                </form>\
            </div>\
            <div class="cp-searchbar-cancel" onclick="cGoBack(this)">取消</div>\
          </div>\
        </div>\
        <div class="page-view-main " >\
          <div class="cp-scroller-wrap" >\
            <div class="cp-scroller cp-pdt"    id="J-addressList-refresh" >\
                <ul id="J-getAddress" class="cp-list-wrap cp-list-points "   data-page="0" ></ul>\
                <ul id="J-list-mapAddress" class="cp-list-wrap cp-list-points hidden"   data-page="0" ></ul>\
                <div class="cp-createAddress-box hidden" onclick="pageMethods.goCreateAddress()">\
                  <p>没找到您想要的站点？</p>\
                  <p><i class="fa fa-plus"></i> 创建站点：<b class="cp-keyword"></b></p>\
                </div>\
            </div>\
          </div>\
        </div>\
      </div>\
      '
    },
    mapAddressItem : function(data){
      var className =    "cp-item ";
      var icon = '<i class="fa fa-map-marker"></i> '
      if( typeof(data.address_type) != 'undefined'){
        className = "cp-item cp-type-"+data.address_type;
      }
      if(data.location.lat && data.location.lng){
        return '<li class="cp-item cp-type-frommap" data-id="0" data-city="'+data.district+'" data-latitude="'+data.location.lat+'" data-longtitude="'+data.location.lng+'" onclick="pageMethods.selectAddressPoint(this)">\
          '+icon+'<b class="name">'+data.name+'</b>\
          <p class="address">'+data.address+'</p>\
        </li>';
      }
      return '';

    }

  };

  /****** 页面婁據  *******/
  var pageDatas = {
    pageID : 'Page-address-select',
    keyword :'',
    from:transition.query.from
  };

  /****** 页面方法  *******/
  pageMethods = {
    /**
     * 取得地址列表数据
     * @param page 页码
     * @param options 设置
     */
    getAddressLists: function(refresh){
      refresh = refresh || 0;
      var nowTimestamp = new Date().getTime();
      function getDataByAjax(){
        cGetLists({
          data:{},
          target:'#J-getAddress',
          url:cApiUrl.getMyAddress,
          templateFun:cParseTemplates().addressItem,
          success:function(rs){
            // alert(rs);
            cModel.myAddress('clear');
            // console.log(rs.data.lists)
            if(rs.code === 0){
              $.each(rs.data.lists,function(i,item){
                item.listorder = i;
                item.address = '';

                if(item.address_type!='Home' && item.address_type!='Work'){
                  if(item.addressid==rs.data.lists[0].addressid || item.addressid==rs.data.lists[1].addressid){
                    return true;
                  }
                }
                cModel.myAddress('add',{data:item});
                // server.my_address.add(item);
              })
              window.localStorage.setItem('CP_'+GB_VAR['userBaseInfo'].loginname+'_addressOverTime',nowTimestamp);

              //通高德地图以地坐标取得地址信息，并写入本地数据库
              AMap.plugin('AMap.Geocoder',function(){
                var geocoder = new AMap.Geocoder({
                     radius: 1000,
                     extensions: "all"
                 });
                 $.each(rs.data.lists,function(i,item){
                   if(item.address_type!='Home' && item.address_type!='Work'){
                     if(item.addressid==rs.data.lists[0].addressid || item.addressid==rs.data.lists[1].addressid){
                       return true;
                     }
                   }
                   geocoder.getAddress([item.longtitude,item.latitude], function(status, result) {
                       if (status === 'complete' && result.info === 'OK') {
                         item.listorder = i;
                         item.address = result.regeocode.formattedAddress;
                         $('#J-getAddress .cp-item[data-id='+item.addressid+']').append('<p class="address">'+item.address+'</p>');
                         cModel.myAddress('update',{data:item});
                         // server.my_address.update(item);
                       }
                   });
                 });
              });
              GB_VAR['flags']['hasGetMyAddress']  = 1
            }

          },
          complete:function(XMLHttpRequest, textStatus){

          }
        })
      }
      if(refresh){
        getDataByAjax();
        return false;
      }
      //打开本地数据库 查询地址列表
      cModel.myAddress('getAll',{
        orderBy:'listorder',
        success:function(results,server){
          var overTime = window.localStorage.getItem('CP_'+GB_VAR['userBaseInfo'].loginname+'_addressOverTime'); //上次记录本地数据的时间
          overTime = overTime ? overTime : 0;
          //当本地数据为空，或者数据过期时，重新获取
          if(!results || !results.length || (!GB_VAR['flags']['hasGetMyAddress'] && (nowTimestamp - overTime) > 7*24*60*60*1000) ){
            getDataByAjax();
          }else{
            pageMethods.showLists(results);
          }
        }

      });

    },

    /**
     * 显示列表
     */
     showLists : function(datas){
      //  console.log(datas)
       var html = '';
       $.each(datas,function(i,item){
         html += cParseTemplates().addressItem(item);
       });
       $('#J-getAddress').append(html);
     },

    /**
     * 点击地址进行选择，把数据反回地图
     */
    selectAddressPoint: function(obj){
      var $item = $(obj);
      var  data = {
        latitude:$item.attr('data-latitude'),
        longtitude:$item.attr('data-longtitude'),
        aid:$item.attr('data-id'),
        name:$item.find('.name').text()
      }
      if(typeof($item.attr('data-city'))!='undefined'){
        data.city = $item.attr('data-city');
      }
      data.address = $item.find('.address') ? $item.find('.address').text() : '';

      var from            = pageDatas.from;
      var home_o_aid      = $('#'+pageDatas.pageID).find('.cp-type-Home').attr('data-id');
      var company_o_aid   = $('#'+pageDatas.pageID).find('.cp-type-Work').attr('data-id');
      var type            = 0;
      // console.log(data);
      switch (from) {
        case 'start':
          type = 1 ;
          $target = $('#J-startPoint');
          $other  = $('#J-endPoint');
          GB_VAR['addRoute_datas']['start'] = data;
          break;
        case 'end':
          type = 1 ;
          $target = $('#J-endPoint');
          $other  = $('#J-startPoint');
          GB_VAR['addRoute_datas']['end'] = data;
          break;
        case 'home':
          if(data.aid==home_o_aid){
            toast.success({title:'更新成功',  duration:700});
            cGoBack();
            return false;
          }
          if(data.aid==company_o_aid){
            toast.fail({title:'公司和家不能是同一个地方',  duration:700});
            return false;
          }
          type = 2 ;
          break;
        case 'company':
          if(data.aid==company_o_aid){
            toast.success({title:'更新成功',  duration:700});
            cGoBack();
            return false;
          }
          if(data.aid==home_o_aid){
            toast.fail({title:'公司和家不能是同一个地方',  duration:700});
            return false;
          }
          type = 2 ;
          break;
        default:
          return false;
      }

      if(type === 1){  //來自添加行程頁
        $target.addClass('cp-selected').attr('data-aid',data.aid).attr('data-latitude',data.latitude).attr('data-longtitude',data.longtitude).find('.cp-text').show().text(data.name).siblings('.cp-ph').hide();
        pageMethods.showAddRouteBtn();
        if($other.hasClass('cp-selected')){
          var data_other = {latitude:$other.attr('data-latitude'),longtitude:$other.attr('data-longtitude'),name:$other.find('.cp-text').text()}
          var data_start = from =='start' ? data : data_other;
          var data_end   = from =='start' ? data_other : data;
          GB_VAR['doMenthods'] = 'computeRoute';
        }else{

          if(GB_VAR['map_addRoute']){
            addMarker([data.longtitude,data.latitude],GB_VAR['map_addRoute']);
          }
        }
        cGoBack($('#Page-address-select .cp-searchbar-cancel')[0]);
      }else if(type === 2){ //來自更改個人信息頁
        data.from = from;
        cAccessAjax({
          type     :'post',
          dataType :'json',
          url      : cApiUrl.editProfileAdress,
          data     : data,
          success:function(rs){
            if(!cCheckLoginByCode(rs.code)){return false;}
            if(rs.code === 0){
              var data_n = data;
              data_n.addressid = data.aid;
              data_n.addressname = data.name;
              if(from=='home'){
                data_n.listorder = 0;
                data_n.address_type = 'Home';
                // data_o.addressid = home_o_aid
              }else if(from == 'company'){
                data_n.listorder = 1;
                data_n.address_type = 'Work';
                // data_o.addressid = company_o_aid
              }
              //把旧的移出home或work位置
              cModel.myAddress('only',{
                data:{'address_type':data_n.address_type},
                success:function(result,server){
                  var data_o = result;
                  data_o.listorder = 3;
                  data_o.address_type = 'Often'
                  cModel.myAddress('update',{data:data_o});
                  //把新的写进home或work位置
                  if(typeof(rs.data.createAddress.addressid)!='undefined'){
                     data_n.addressid = rs.data.createAddress.addressid;
                     cModel.myAddress('add',{data:data_n});
                  }else{
                     cModel.myAddress('update',{data:data_n});
                  }
                  // console.log(GB_VAR['user_info']);
                  GB_VAR['user_info'][from+'_address']    = data.name;
                  GB_VAR['user_info'][from+'_address_id'] = data.aid;
                  setTimeout(function(){
                    toast.success({title:rs.desc,  duration:700});
                  },300);
                  cGoBack();
                }
              })
            }else{
              setTimeout(function(){
                toast.fail({title:rs.desc,  duration:1000});
              },300);
            }
          },
          error:function(){
            setTimeout(function(){
              toast.custom({html:'',title:'网络不畅通，请稍候再试',  duration:1000});
            },300);
          },
          complete:function(XHR){
            toast.hide();
          }
        });
      }

    },
    /**
     * 发布页显示提交按钮
     */
    showAddRouteBtn : function(){
      if($('#J-needTime').hasClass('cp-selected') && $('#J-startPoint').hasClass('cp-selected') && $('#J-endPoint').hasClass('cp-selected') ){
        if(!$('#Page-route-add').hasClass('cp-modal-add-wall') || $('#J-pick-seatCount').hasClass('cp-selected') ){
          $('.cp-map-form .cp-btn-submit').addClass('in');
        }

      }
    },

    /**
     * 查找地址
     */
    findAddress : function(obj){
      var $ipt = $(obj);
      var keyword = $.trim($ipt.val());
      var $listWrap = $('#J-getAddress');
      $(".cp-createAddress-box").find('.cp-keyword').text(keyword);
      if(keyword==''){
        $listWrap.removeClass('doSearch').find('li').show();
        $("#J-list-mapAddress").addClass('hidden');
        $(".cp-createAddress-box").addClass('hidden')
        return false;
      }
      if(keyword==pageDatas.keyword){
        return false;
      }
      pageDatas.keyword = keyword
      $listWrap.addClass('doSearch').find('li').show();
      $listWrap.find('li').each(function(){
        if($(this).text().indexOf(keyword)===-1){
          $(this).hide();
        }
      });

      // 檢查是存在本地城市信息
      var local_city = typeof(GB_VAR.local_city.city) == 'string' ? GB_VAR.local_city.city :'';
      //使用高德地圖自動無成插件
      AMap.plugin('AMap.Autocomplete',function(){//回调函数
          //实例化Autocomplete
          var autoOptions = {
              city: local_city, //城市，默认全国
          };
          autocomplete= new AMap.Autocomplete(autoOptions);
          autocomplete.search(keyword, function(status, result){
            if(status == 'complete'){
              $("#J-list-mapAddress").html('');
              var listHtml = '';
              $.each(result.tips,function(i,item){
                // console.log(item);
                listHtml += pageTempates.mapAddressItem(item);
              })
              $("#J-list-mapAddress").append(listHtml);
            }

          });
      });

      $("#J-list-mapAddress").removeClass('hidden');
      if(pageDatas.from !='home' && pageDatas.from !='company' ){
        $(".cp-createAddress-box").removeClass('hidden')
      }
    },

    /**
     * 跳转到添加地址页
     */
    goCreateAddress: function(){
      var keyword = pageDatas.keyword;
      console.log(encodeURI(keyword));
      GB_VAR['temp']['openCreateAddress'] = 1 ;
      redirect('#/createAddress?from='+pageDatas.from+'&keyword='+encodeURI(keyword));
    }

  };
  /****** 渲染页面  *******/

  if(!$("#Page-route-add").length && !$("#Page-user-profile").length   ){
    cGoBack();
    return false;
  }
  if(GB_VAR['temp']['openCreateAddress'] == 1 ){
    $('#Page-address-add').remove();
    GB_VAR['temp']['openCreateAddress'] = 0 ;
    pageDatas.keyword = $('#Input-searchAddress').val();
  }else{
    if($("#Page-address-select").length > 0 ){
      $("#Page-address-select").remove();
    }
    $('#app').append(pageTempates.content());
    pageMethods.getAddressLists(0);
  }


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
  //下拉刷新地址
  pullRefresh("#J-addressList-refresh", 61, function (e) {
      var that = this;
      pageMethods.getAddressLists(1);
      setTimeout(function () {
          that.back.call();
      }, 500);
  });


}
