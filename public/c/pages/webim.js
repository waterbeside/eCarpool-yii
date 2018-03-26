SPA_RESOLVE_INIT = function(transition) {
  /****** DOM *******/
  var  pageTempates = {
    imFrame : function(){
      var html = '<div class="page-view-index" id="Page-webim-index">'
      // html += cParseTemplates().pageViewTitle({title:"溢信",goBack:0})
      html += '\
      <div class="page-view-main" >\
        <iframe class="cp-iframe-main" src="/webim/#/contact?username='+GB_VAR['userBaseInfo'].loginname+'&v=1" frameborder="0" style="height:100%; width:100%"></iframe>\
      </div>\
      '+ cParseTemplates().footerBar('webim') +'\
    '
     html +='</div>'
     return html;
   },
   noIm : function(){

   }
  }


 /****** 页面方法  *******/
 pageMethods = {
   /**
    * 跳转到指定页
    * @param  string url 路转的地址
    */
   goPage : function(url){
     redirect(url);
   },




 }


 /****** 渲染页面  *******/
 document.getElementById("app").innerHTML = pageTempates.imFrame();
 if(GB_VAR['jumpTo']!=''){
   var url = GB_VAR['jumpTo'];
   redirect(url);
 }
 var $ifame = $('#Page-webim-index .cp-iframe-main');
 var footerHeight = $('#footer').height();
 console.log(footerHeight);
 $ifame.height($('body').height()-footerHeight);

 /****** 执行页面  *******/








}
