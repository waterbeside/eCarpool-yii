SPA_RESOLVE_INIT = function(transition) {
  /****** DOM *******/
  var html = '\
  <div class="page-view cp-page-points" id="Page-address-select" >\
    <div class="cp-searchbar-wrap"  >\
      <div class="cp-searchbar">\
        <div class="cp-searchbar-input"  >\
            <i class=" fa fa-search"></i>\
            <form onsubmit="return false;" >\
                <input type="search" name="keyword" placeholder="请输入地点关键词" id="Input-searchAddress" onkeyup="pageMethods.getAddressLists(0)" >\
            </form>\
        </div>\
        <div class="cp-searchbar-cancel" onclick="cGoBack(this)">取消</div>\
      </div>\
    </div>\
    <div class="page-view-content no-padding" >\
      <div class="cp-scroller-wrap" >\
        <div class="cp-scroller"  style="padding-top:40px" id="J-addressList-refresh" >\
            <ul id="J-getAddress" class="cp-list-wrap cp-list-points"   data-page="0" ></ul>\
        </div>\
      </div>\
    </div>\
  </div>\
'

  /****** 页面方法  *******/
  pageMethods = {
    /**
     * 取得地址列表数据
     * @param page 页码
     * @param options 设置
     */
    getAddressLists: function(page,options){
      options = options || {}
      var $input = $('#Input-searchAddress');
      var keyword = $.trim($input.val());
      if(page==0 && keyword!=''){
        var oldval = $input.attr('data-old');
        if(oldval==keyword){ //判定前后keyword是相同，同则不请求。
          return false;
        }
        $input.attr('data-old',keyword);
      }
      var data = $.trim(keyword) == '' ? {page:parseInt(page)+1} : {page:parseInt(page)+1,keyword:keyword};
      cGetLists({
        data:data,
        target:'#J-getAddress',
        url:cApiUrl.getAddress,
        templateFun:cParseTemplates().addressItem,
        complete:function(XMLHttpRequest, textStatus){
          if(typeof(options.complete)=='function'){
            options.complete(XMLHttpRequest, textStatus);
          }
        }
      })
    },

    /**
     * 点击地址进行选择，把数据反回地图
     */
    selectAddressPoint: function(obj){
      var $item = $(obj);
      var data = {latitude:$item.attr('data-latitude'),longtitude:$item.attr('data-longtitude'),aid:$item.attr('data-id'),name:$item.find('.name').text()}

      var  from = transition.query.from;
      // console.log(data);
      switch (from) {
        case 'start':
          $target = $('#J-startPoint');
          $other = $('#J-endPoint');
          GB_VAR['addRoute_datas']['start'] = data;
          break;
        case 'end':
          $target = $('#J-endPoint');
          $other = $('#J-startPoint');
          GB_VAR['addRoute_datas']['end'] = data;
          break;
        default:
          return false;
      }

      console.log(data.aid);
      console.log(data.latitude);
      $target.addClass('cp-selected').attr('data-aid',data.aid).attr('data-latitude',data.latitude).attr('data-longtitude',data.longtitude).find('.cp-text').show().text(data.name).siblings('.cp-ph').hide();
      pageMethods.showAddRouteBtn();
      if($other.hasClass('cp-selected')){
        var data_other = {latitude:$other.attr('data-latitude'),longtitude:$other.attr('data-longtitude'),name:$other.find('.cp-text').text()}
        var data_start = from =='start' ? data : data_other;
        var data_end   = from =='start' ? data_other : data;
        // if(GB_VAR['map_addRoute']){
        //   drawRouteLine(new AMap.LngLat(data_start.longtitude,data_start.latitude),new AMap.LngLat(data_end.longtitude,data_end.latitude),GB_VAR['map_addRoute']);
        // }
        GB_VAR['doMenthods'] = 'computeRoute';
      }else{
        if(GB_VAR['map_addRoute']){
          addMarker([data.longtitude,data.latitude],GB_VAR['map_addRoute']);
        }
      }
      console.log(GB_VAR['addRoute_datas'])
      cGoBack($('#Page-address-select .cp-searchbar-cancel')[0]);
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

   }
  /****** 渲染页面  *******/
  // document.getElementById("app").innerHTML = html;
  if(!$("#Page-route-add").length  ){
    cGoBack({});
    return false;
  }
  if($("#Page-address-select").length > 0 ){
    $("#Page-address-select").remove();
  }
  $('#app').append(html);
  /****** 加载完成行执行页面  *******/
  pageMethods.getAddressLists(0);

  //下拉刷新地址
  pullRefresh("#J-addressList-refresh", 61, function (e) {
      var that = this;
      pageMethods.getAddressLists(0);
      setTimeout(function () {
          that.back.call();
      }, 500);
  });
  //滚到底加载更多
  cLoadMoreList('#J-getAddress',pageMethods.getAddressLists);

}
