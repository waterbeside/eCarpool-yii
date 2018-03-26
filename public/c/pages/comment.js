SPA_RESOLVE_INIT = function(transition) {

   /****** DOM *******/
   var  pageTempates = {
      content : function(){
        var that = this;
        var viewTitleData = {
          title       : pageDatas.title,
        }
        var html = '\
        <div class="page-view cp-overhide " id="'+pageDatas.pageID+'">\
        '+cParseTemplates().pageViewTitle(viewTitleData)+'\
          <div class="page-view-main cp-pdt"   >\
            <div class="cp-scroller-wrap" onscroll="pageMethods.showAddBox(0)" >\
              <div class="cp-scroller" id="J-commentList-refresh"  >\
                <ul class="cp-comment-list" id="J-getComments">\
                </ul>\
              </div>\
            </div>\
            <div class="cp-comment-addbox">\
              <form class="form" onsubmit="return false;">\
              <div class="cp-input-wrapper hidden">\
                  <textarea class="form-control" name="content"></textarea>\
                  <button class="btn-submit btn btn-default" data-loading-text="..." onclick="pageMethods.submit()">提交</button>\
              </div>\
              </form>\
              <div class="cp-startBtn-wrapper">\
                <button class="btn btn-block btn-default" onclick="pageMethods.showAddBox(1)">发表评论</button>\
              </div>\
            </div>\
          </div>\
        </div>\
        '
        return html;
      },


     
    }

   var pageDatas = {
     pageID:'Page-comment-lists',
     title : '评论',
     wid:transition.query.wid,
   }

  /****** 页面方法  *******/
  pageMethods = {
    /**
     * 取得评论列表数据
     * @param options 设置
     */
     getCommentLists:function(options){
       var $commentLists = $('#J-getComments');
       cGetLists({
         data:{wid:pageDatas.wid},
         // data:{wid:11009},
         target:'#J-getComments',
         url:cApiUrl.wallComments,
         no_data_text:'还未有人评论   (´°̥̥̥̥̥̥̥̥ω°̥̥̥̥̥̥̥̥｀)',
         templateFun:false,
         successStart:function(rs){
           var html = '';
           if(rs.code === 0){
             $.each(rs.data.lists,function(i,item){
   		        html += cParseTemplates().commentItem(item,{loading:0});
   		      })
           }
           $commentLists.html('');
           setTimeout(function(){
             $commentLists.html(html);
           },500)

         },
         complete:function(XMLHttpRequest, textStatus){

         }
       })
     },

     /**
      * 显示评论输入框
      */
     showAddBox: function(show){
       show = show || 0
       var $pageWrapper = $('#'+pageDatas.pageID)
       var $addBoxwrapper = $pageWrapper.find('.cp-comment-addbox')
       var $inputWrapper = $addBoxwrapper.find('.cp-input-wrapper');
       var $showBtnWrapper = $addBoxwrapper.find('.cp-startBtn-wrapper');
       if(show){
         $inputWrapper.removeClass('hidden').find('textarea').focus();
         $showBtnWrapper.addClass('hidden');
       }else{
         $inputWrapper.addClass('hidden');
         $showBtnWrapper.removeClass('hidden');
       }
     },

     /**
      * [that description]
      * @type {[type]}
      */
     submit: function(){
       var $pageWrapper = $('#'+pageDatas.pageID);
       var $commentLists = $('#J-getComments');
       var $scrollWrap = $pageWrapper.find('.cp-scroller-wrap');
       var data = {
         content : $.trim($pageWrapper.find('[name=content]').val()),
         time : cFormatDate((new Date()),"yyyy-mm-dd hh:ii"),
         avatar : cMyAvatar(),
         name : GB_VAR['userBaseInfo'].name,
         className:'cp-newAdd'
       }
       data.id = 'tmp_'+(new Date().getTime());
       if(data.content==""){
         alert('请填写内容');
         return false;
       }
       var addHtml = cParseTemplates().commentItem(data,{loading:1});
       $pageWrapper.find('.cp-nodata-tips').remove();
       $commentLists.append(addHtml);
       $pageWrapper.find('[name=content]').val('')
       pageMethods.showAddBox(0);
       // $pageWrapper.find('.btn-submit').button('loading');


       $scrollWrap[0].scrollTop = $scrollWrap[0].scrollHeight;
       // toast.loading({title:"提交中",duration:1000});
       // console.log(cMyAvatar());
       // return false;
       var $newItem = $commentLists.find('[data-id='+data.id+']');
       cAccessAjax({
         type:'post',
         dataType:'json',
         url:cApiUrl.wallComments,
         data:{wid:pageDatas.wid,content:data.content},
         success:function(rs){
           if(!cCheckLoginByCode(rs.code)){return false;}
           // console.log(rs)
           $commentLists.find('[data-id='+data.id+']').find('.cp-loadingIcon').remove();
           if(rs.code === 0){
             $newItem.attr('data-id',rs.data.id);
             // toast.success({title:rs.desc,  duration:2000});
           }else{
             $newItem.find('.cp-content').append('<span class="cp-error">发送失败</span>');
             // toast.fail({title:rs.desc,  duration:2000});
           }

         },
         complete:function(XHR){
           // toast.hide();
           $pageWrapper.find('.btn-submit').button('reset');
         }
       });
     }


   }
  /****** 渲染页面  *******/
  GB_VAR['temp']['from'] = 'comment';
  document.getElementById("app").innerHTML += pageTempates.content();


  /****** 加载完成行执行页面  *******/
  pageMethods.getCommentLists()
  //下拉刷新地址
  pullRefresh("#J-commentList-refresh", 60, function (e) {
      var that = this;
      pageMethods.getCommentLists(1);
      setTimeout(function () {
          that.back.call();
      }, 500);
  });

}
