SPA_RESOLVE_INIT = function(transition) {
  /****** DOM *******/
  var  pageTempates = {
    //主體
     content : function(){
       var html = '\
       <div class="page-view cp-overhide " id="Page-myRoute-lists">\
       '+cParseTemplates().pageViewTitle({title:'我的行程'})+'\
         <div class="page-view-main "     >\
           <div class="cp-scroller-wrap" >\
             <div class="cp-scroller cp-pdt" id="J-myrouteList-refresh" >\
                 <div class="cp-list-wrap" id="J-getMyrouteLists"  data-page="0" ></div>\
             </div>\
           </div>\
         </div>\
       </div>\
     '
     return html;
     },
     listItem: function(data,options){
       var defaults = {}
       if(data.from == 'wall'){

         defaults = {
           userDatas : data.owner_info,
           typeLabel : '司机',
           dataFrom  : 'wall',
           tipsWrapTemp:cParseTemplates().routeCardBtns(data),
           clickAction:'pageMethods.goWallView('+data.id+',this)'
         }
       }else if(data.from =='info'){

         defaults = {
           userDatas : data.show_owner == 1 ? data.owner_info : data.passenger_info ,
           typeLabel : data.show_owner == 1 ? '司机' : '乘客',
           dataFrom  : 'info',
           tipsWrapTemp: data.status == 1 ? '<div class="tips-wrap cp-btns-wrap">\
             <a href="javascript:void(0);" onclick="pageMethods.cencelRoute('+data.id+',\''+data.from+'\',this)"  data-loading-text="..." class="cp-btn  btn-ripple  pull-right"><i class="fa fa-times"></i></a>\
             <a href="javascript:void(0);" onclick="pageMethods.finishRoute('+data.id+',\''+data.from+'\',this)"  data-loading-text="..." class="cp-btn   btn-ripple pull-right"><i class="fa fa-check"></i></a>\
             <a href="tel:'+ (data.show_owner == 1 ? data.owner_info.phone : data.passenger_info.phone)+'" onclick="event.stopPropagation();"  class="cp-btn  btn-ripple pull-right"><i class="fa fa-phone"></i></a>\
           </div>' : '<div class="tips-wrap cp-btns-wrap">\
             <a href="javascript:void(0);" onclick="pageMethods.cencelRoute('+data.id+',\''+data.from+'\',this)"  data-loading-text="..." class="cp-btn  btn-ripple   pull-right"><i class="fa fa-times"></i>取 消</a>\
             <span class="tips-text">等待车主接受</span>\
           </div>',
           clickAction:'pageMethods.goInfoView('+data.id+','+data.show_owner+',this)'

         }
         if(data.love_wall_ID){
           defaults['clickAction'] = 'pageMethods.goWallView('+data.love_wall_ID+',this)';
         }
         defaults.userDatas.carnumber = data.show_owner == 1 ? defaults.userDatas.carnumber : '';
       }
       var opt = $.extend({},defaults,options);
       return cParseTemplates().routeCardItem(data,opt);
     }
   }






  pageMethods = {
    /**
     * 取得我的行程列表数据
     * @param page 页码
     * @param options 设置
     */
    getMyrouteLists:function(page,options){
      options = options || {}
      cGetLists({
        data:{page:parseInt(page)+1},
        target:'#J-getMyrouteLists',
        url:cApiUrl.getMyroute,
        listType:'myroute',
        templateFun:pageTempates.listItem,
        complete:function(XMLHttpRequest, textStatus){
          if(typeof(options.complete)=='function'){
            options.complete(XMLHttpRequest, textStatus);
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
      redirect('#/wallView?id='+id);
    },

    /**
     * 跳转到细览页
     * @param  int id  目标ID
     * @param  object obj 按钮对象
     */
    goInfoView:function(id,iscarown,obj){
      var str_iscarown = iscarown ? '&iscarown='+iscarown : ''; 
      redirect('#/infoView?id='+id+str_iscarown);
    },
    /**
     * 取消行程
     */
    cencelRoute:function(id,from,obj){
      event.stopPropagation(); //禁止冒泡
      var options = {
        btnObj: obj,
        url: cApiUrl.cancelRoute,
        data: {id:id,from:from},
        msg:'您确定要取消吗？'
      }
      this.cancelOrFinish(options);
    },
    /**
     * 完成行程
     */
    finishRoute:function(id,from,obj){
      event.stopPropagation(); //禁止冒泡
      var options = {
        btnObj: obj,
        url: cApiUrl.finishRoute,
        data: {id:id,from:from},
        msg:'您确定要完结本次行程吗？'
      }
      this.cancelOrFinish(options);
    },

    /**
     * 完成和取消行程的ajax提交
     */
    cancelOrFinish:function(options){
      var $btn = $(options.btnObj);
      dialog.alert({
          title:"提示",
          msg:options.msg,
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
            url:options.url,
            data:options.data,
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
      })

    }
   }

   /****** 渲染页面  *******/
  //断定是否已加载有，是则不重新加载
  if($('#Page-myRoute-lists').length>0){
   $('#Page-myRoute-lists').siblings('.page-view').remove();
  }else{
   // document.getElementById("app").innerHTML = html;
   document.getElementById("app").innerHTML += pageTempates.content();

   pageMethods.getMyrouteLists(0);
  }


  //下拉刷新行程页
  pullRefresh("#J-myrouteList-refresh", 61, function (e) {
      var that = this;
      pageMethods.getMyrouteLists(0);
      setTimeout(function () {
          that.back.call();
      }, 600);
  });

}
