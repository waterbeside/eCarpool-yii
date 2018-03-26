SPA_RESOLVE_INIT = function(transition) {

   /****** DOM *******/
   var  pageTempates = {
      content : function(){
        var that = this;
        var viewTitleData = {
          title       : pageDatas.title,
          rightContent: that.searchBox(),
        }
        var html = '\
        <div class="page-view cp-overhide " id="'+pageDatas.pageID+'">\
        '+cParseTemplates().pageViewTitle(viewTitleData)+'\
          <div class="page-view-main cp-pdt"   >\
            <div class="cp-scroller-wrap" >\
              <div class="cp-scroller"  id="J-infoList-refresh" >\
                  <div class="cp-list-wrap" id="J-getInfoLists"  data-page="0" ></div>\
              </div>\
            </div>\
          </div>\
        </div>\
        '
        return html;
      },
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

      listItem: function(data,options){
        var defaults = {
          userDatas : data.passenger_info,
          typeLabel : '乘客',
          dataFrom  : 'from',
          tipsWrapTemp: '<div class="tips-wrap text-center" >\
           <a href="javascript:void(0);" onclick="pageMethods.acceptDemand('+data.id+',this)"  data-loading-text="提交中..." class="btn btn-warning btn-ripple btn-accept " ><i class="fa fa-check"></i> 接 受</a>\
          </div>',
          clickAction:'pageMethods.goInfoView('+data.id+',this)'
        }
        defaults.userDatas.carnumber = '';
        var opt = $.extend({},defaults,options);
        return cParseTemplates().routeCardItem(data,opt);
      }
    }

   var pageDatas = {
     pageID:'Page-info-lists',
     title : '约车需求',
     keyword :'',
     timer:{},
   }

  /****** 页面方法  *******/
  pageMethods = {
    /**
     * 取得需求列表数据
     * @param page 页码
     * @param options 设置
     */
     getInfoLists:function(page,options){
       options = options || {}
       var data_p = {page:parseInt(page)+1};
       var data ={}
       if(typeof(options.data)=='object'){
         data = $.extend({}, data_p, options.data);
       }else{
         data = data_p;
       }
       cGetLists({
         data:data,
         target:'#J-getInfoLists',
         url:cApiUrl.getInfoLists,
         listType:'info',
         // templateFun:cParseTemplates().routeCard,
         templateFun:pageTempates.listItem,
         complete:function(XMLHttpRequest, textStatus){
           if(typeof(options.complete)=='function'){
             options.complete(XMLHttpRequest, textStatus);
           }
         }
       });
     },
     /**
      * 跳转到细览页
      * @param  int id  目标ID
      * @param  object obj 按钮对象
      */
     goInfoView:function(id,obj){
       redirect('#/infoView?id='+id)
     },
     /**
      * 接受约车需求
      */
     acceptDemand:function(id,obj){
       event.stopPropagation(); //禁止冒泡

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
                 var $listItem = $btn.closest('.cp-routeCard-item');
                 $listItem.addClass('cancel');
                 setTimeout(function(){
                    $listItem.remove();
                 },800);
               }else{
                 toast.fail({title:rs.desc,  duration:2000});
               }
             },
             complete:function(XHR){
               $btn.button('reset');
             }
           });
         }
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
         pageMethods.getInfoLists(0);
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
           pageMethods.getInfoLists(0);
           pageDatas.keyword = keyword;
           return false;
         }
         pageDatas.keyword = keyword;
         $('#J-getInfoLists').html('');
         var opt = {
           data:{keyword:keyword},
           success:function(rs){
             // console.log(rs)
           }
         }
         pageMethods.getInfoLists(0,opt);
       },800)
     }
   }
  /****** 渲染页面  *******/
  //断定是否已加载有，是则不重新加载
  if($('#Page-info-lists').length>0){
    $('#Page-info-lists').siblings('.page-view').remove();
  }else{
    $('#Page-info-view').remove();
    document.getElementById("app").innerHTML += pageTempates.content();
    pageMethods.getInfoLists(0);
  }
  /****** 加载完成行执行页面  *******/

  //下拉刷新需求页
  pullRefresh("#J-infoList-refresh", 61, function (e) {
      var that = this;
      pageMethods.getInfoLists(0);
      setTimeout(function () {
          that.back.call();
      }, 500);
  });
  //滚到底加载更多
  cLoadMoreList('#J-getInfoLists',pageMethods.getInfoLists,function(){
    opt = {
      data: {keyword : $("#"+pageDatas.pageID).find('input[name=keyword]').val()}
    }
    return opt;
  });

}
