SPA_RESOLVE_INIT = function(transition) {

 /****** DOM *******/
 var  pageTempates = {
   //主體
    content : function(){
      var that = this;
      var viewTitleData = {
        title       : pageDatas.title,
        rightContent: that.searchBox(),
      }
      var html = '\
      <div class="page-view  cp-overhide " id="'+pageDatas.pageID+'">\
      '+cParseTemplates().pageViewTitle(viewTitleData)+'\
        <div class="page-view-main" >\
          <div class="cp-scroller-wrap" >\
            <div class="cp-scroller cp-pdt"  id="J-wallList-refresh"  >\
                <div class="cp-list-wrap" id="J-getWallLists"  data-page="0"  ></div>\
            </div>\
          </div>\
        </div>\
      </div>\
      '
      return html;
    },
    //搜索盒
    searchBox : function(){
      return '<div>\
          <div class="cp-search-box hidden"><input name="keyword" class="form-control form-control-line" placeholder="请输入关键字查找" onkeyup="pageMethods.findData(this)" autocomplete="off" ></div>\
          <div class="cp-btn-wrapper">\
            <button class="cp-btn-search" onclick="pageMethods.showSearchBox()"><i class="fa fa-search"></i></button>\
            <button class="cp-btn-close hidden" onclick="pageMethods.closeSearchBox()"><i class="fa fa-times"></i></button>\
          </div>\
        </div>\
      ';
    },
    //列表單元
    listItem: function(data,options){
      var defaults = {
        userDatas : data.owner_info,
        typeLabel : '司机',
        dataFrom  : 'wall',
        tipsWrapTemp:cParseTemplates().routeCardBtns(data),
        clickAction:'pageMethods.goWallView('+data.id+',this)'
      }
      var opt = $.extend({},defaults,options);
      return cParseTemplates().routeCardItem(data,opt);
    }
  }

 var pageDatas = {
   pageID:'Page-wall-lists',
   title : '墙上空座位',
   keyword :'',
   timer:{},
 }

 /****** 页面方法  *******/
 pageMethods = {
    /**
     * 取得空座位列表数据
     * @param page 页码
     * @param options 设置
     */
    getWallLists:function(page,options){
      options = options || {}
      var data_p = {page:parseInt(page)+1};
      var data ={}
      if(typeof(options.data)=='object'){
        data = $.extend({}, data_p, options.data);
      }else{
        data = data_p;
      }
      cGetLists({
        data        :data,
        target      :'#J-getWallLists',
        url         :cApiUrl.getWallLists,
        listType    :'wall',
        // templateFun :cParseTemplates().routeCard,
        templateFun : pageTempates.listItem,
        complete    :function(XMLHttpRequest, textStatus){
          if(typeof(options.complete)=='function'){
            options.complete(XMLHttpRequest, textStatus);
          }
        },
        success     :function(rs){
          if(typeof(options.success)=='function'){
            options.success(rs);
          }
        }
      })
    },

    /**
     * 跳转到细览页
     * @param  int id  目标ID
     * @param  object obj 按钮对象
     */
    goWallView:function(id,obj){
      redirect('#/wallView?id='+id)
    },

    /**
     * 按赞
     * @param  int id  目标ID
     * @param  object obj 按钮对象
     */
    likeRoute:function(id,obj){
      event.stopPropagation(); //禁止冒泡
      var $btn        = $(obj);
      var $btnWraper  = $btn.closest('.cp-fabBtn-wrap');
      var $numWraper  = $btnWraper.find('.num');
      var numLike     = parseInt($numWraper.text());
      if($btnWraper.hasClass('hasLike')){
        return false;
      }
      $btnWraper.addClass('doLike hasLike');

      $numWraper.text(numLike+1);
      $btn.removeClass('btn-primary').addClass('btn-danger').html('<i class="fa fa-heart"></i>');

      cAccessAjax({
        type:'post',
        dataType:'json',
        url:cApiUrl.likeRoute,
        data:{id:id},
        success:function(rs){
          if(!cCheckLoginByCode(rs.code)){return false;}
        },
      });
    },

    /**
     * 显示搜索
     */
    showSearchBox:function(){
      $('#'+pageDatas.pageID+' .cp-search-box').removeClass('hidden').find('input').focus();;
      $('#'+pageDatas.pageID+' .cp-title').addClass('hidden');
      $('#'+pageDatas.pageID+' .cp-btn-search').addClass('hidden');
      $('#'+pageDatas.pageID+' .cp-btn-close').removeClass('hidden');
    },
    /**
     * 关闭搜索按钮
     */
    closeSearchBox:function(){
      var keyword = $.trim($('#'+pageDatas.pageID+' .cp-search-box').addClass('hidden').find('input').val());

      $('#'+pageDatas.pageID+' .cp-search-box').addClass('hidden').find('input').val('');
      $('#'+pageDatas.pageID+' .cp-title').removeClass('hidden');
      $('#'+pageDatas.pageID+' .cp-btn-search').removeClass('hidden');
      $('#'+pageDatas.pageID+' .cp-btn-close').addClass('hidden');
      if(keyword==''){
        return false;
      }else{
        pageMethods.getWallLists(0);
      }

    },
    /**
     * 搜索
     */
    findData:function(obj){
      var $ipt = $(obj);
      var keyword = $.trim($ipt.val());
      clearTimeout(pageDatas.timer);
      pageDatas.timer = setTimeout(function(){
        console.log(keyword)
        console.log(pageDatas.keyword)
        if(keyword==pageDatas.keyword){
          return false;
        }
        if(keyword==''){
          pageMethods.getWallLists(0);
          pageDatas.keyword = keyword;
          return false;
        }
        pageDatas.keyword = keyword;
        $('#J-getWallLists').html('');
        var opt = {
          data:{keyword:keyword},
          success:function(rs){
            // console.log(rs)
          }
        }
        pageMethods.getWallLists(0,opt);
      },800)
    }

   }


   /****** 渲染页面  *******/
   GB_VAR['temp']['wallViewData'] = ''
   GB_VAR['temp']['commentsTotal'] = 0;
  //断定是否已加载有，是则不重新加载
  if($('#Page-wall-lists').length>0){
   $('#Page-wall-lists').siblings('.page-view').remove();
  }else{
    $('#Page-wall-view').remove();
   // document.getElementById("app").innerHTML = html;
   document.getElementById("app").innerHTML += pageTempates.content();
   pageMethods.getWallLists(0);
  }
  // console.log("首页回调" + JSON.stringify(transition))

  /****** 加载完成行执行页面  *******/
  //下拉刷新需求页
  pullRefresh("#J-wallList-refresh", 61, function (e) {
      var that = this;
      pageMethods.getWallLists(0);
      setTimeout(function () {
          that.back.call();
      }, 500);
  });

  //滚到底加载更多
  cLoadMoreList('#J-getWallLists',pageMethods.getWallLists,function(){
    opt = {
      data: {keyword : $("#"+pageDatas.pageID).find('input[name=keyword]').val()}
    }
    return opt;
  });

}
