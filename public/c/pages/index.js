SPA_RESOLVE_INIT = function(transition) {
  /****** DOM *******/
  var html = '<div class="page-view-index">'
  html += '\
  '+cParseTemplates().pageViewTitle({title:"溢起拼车",goBack:0})+'\
  <div class="page-view-content"   style="padding-top:60px;" >\
    <div class="cp-scroller-wrap" >\
      <div class="cp-scroller"   >\
        <div class="container">\
          <dl class="cp-index-btn-item">\
            <dt>行程</dt>\
            <dd><a class="btn btn-ripple" href="javascript:void(0);" onclick="pageMethods.goPage(\'#/myRoute\')"><i class="fa fa-map"></i>我的行程</a></dd>\
          </dl>\
          <dl class="cp-index-btn-item">\
            <dt>载客</dt>\
            <dd><a class="btn btn-ripple" href="javascript:void(0);" onclick="pageMethods.goPage(\'#/infoList\')"><i class="fa fa-car"></i>乘客约车需求</a></dd>\
          </dl>\
          <dl class="cp-index-btn-item">\
            <dt>搭车</dt>\
            <dd><a class="btn btn-ripple" href="javascript:void(0);" onclick="pageMethods.goPage(\'#/wallList\')"><i class="fa fa-heart"></i>墙上的空座位</a></dd>\
          </dl>\
        </div>\
      </div>\
    </div>\
  </div>\
  '+ cParseTemplates().footerBar('car') +'\
'
//start:发布按钮
html += '\
<div class="cp-mask"></div>\
 <div class="cp-fixed-btn-wrap" id="J-index-fixedBtnWrap">\
   <a class="btn btn-primary btn-fab btn-fab-mini   cp-btn-control btn-ripple" id="J-btn-showAdd" onclick="return pageMethods.showAddBtns(this);" href="javascript:void(0);"><i class="fa fa-plus"></i></a>\
   <div class="cp-btn-options-wrap">\
     <a href="javascript:void(0);" onclick="pageMethods.routeAdd(\'wall\');" class="cp-btn-car btn btn-primary  btn-fab btn-ripple " ><i class="fa fa-car"></i><p>发布空座位</p></a>\
     <a href="javascript:void(0);" onclick="pageMethods.routeAdd(\'info\');" class="cp-btn-need btn btn-primary  btn-fab  btn-ripple"><i class="fa fa-user-o"></i><p>我要约车</p></a>\
   </div>\
 </div>\
 '
 html +='</div>'

 /****** 页面方法  *******/
 pageMethods = {
   goPage : function(url){
     $('.cp-fixed-btn-wrap').hide();
     redirect(url);
   },
   /**
    * 展开发布行程按钮
    * @param obj 按钮对象
    */
   showAddBtns:function(obj){
     var $obj = $(obj);
     var $btnsWrap = $obj.siblings('.cp-btn-options-wrap');
     var $wrap = $obj.closest('.cp-fixed-btn-wrap');
     var $mask = $('.cp-mask');
     if($btnsWrap.is(':hidden')){
       $wrap.addClass('isshow');
       $mask.show();
       $btnsWrap.show();
     }else{
       $wrap.removeClass('isshow').addClass('hiding');

       $wrap.removeClass('hiding')
       $btnsWrap.hide();
       $mask.hide();

     }
   },
   /**
    * 发布行程页面
    * @param  来自wall还是info (发布空座位还是需求)
    */
   routeAdd:function(from){
     $('.cp-fixed-btn-wrap').hide();
     this.showAddBtns($('#J-btn-showAdd')[0]);
     redirect('#/addRoute?from='+from);

   }
 }
 /****** 渲染页面  *******/
 document.getElementById("app").innerHTML = html;
 if(GB_VAR['jumpTo']!=''){
   var url = GB_VAR['jumpTo'];
   redirect(url);
 }
 GB_VAR['addRoute_datas']={};
 GB_VAR['seat_picker'] = '';
 GB_VAR['dt_picker']='';
 GB_VAR['doMenthods'] = '';


}
