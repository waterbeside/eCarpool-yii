SPA_RESOLVE_INIT = function(transition) {
  /****** DOM *******/
  var html = '\
  <div class="page-view cp-page-points" id="Page-user-address-select" >\
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
    <div class="page-view-main " >\
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
      if(from!='home' || from!='company'){

      }


      // console.log(data);
      cAccessAjax({
        type     :'post',
        dataType :'json',
        url      : cApiUrl.editProfileAdress,
        data     : {from:from,aid:data.aid},
        success:function(rs){
          if(!cCheckLoginByCode(rs.code)){return false;}
          if(rs.code === 0){
            console.log(GB_VAR['user_info']);
            GB_VAR['user_info'][from+'_address']    = data.name;
            GB_VAR['user_info'][from+'_address_id'] = data.aid;
            setTimeout(function(){
              toast.success({title:rs.desc,  duration:700});
            },300);
            cGoBack($('#Page-user-address-select .cp-searchbar-cancel')[0]);
          }else{
            setTimeout(function(){
              toast.fail({title:rs.desc,  duration:700});
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



    },


   }
  /****** 渲染页面  *******/
  // document.getElementById("app").innerHTML = html;
  if(!$("#Page-user-profile").length  ){
    cGoBack({});
    return false;
  }
  if($("#Page-user-address-select").length > 0 ){
    $("#Page-user-address-select").remove();
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
