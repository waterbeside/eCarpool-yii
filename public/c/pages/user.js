SPA_RESOLVE_INIT = function(transition) {
  /****** DOM *******/
  var  pageTempates = {
    main : function(){
      var html = '<div class="page-view-index" id="Page-user-index">'
      html += '\
      <div class="page-view-header">\
        <div class="cp-heading btn-ripple" onclick="pageMethods.goPage(\'#/userAvatar\')">\
            <img class="cp-avatar   " src="'+cConfig.defaultAvatar+'" onerror="this.src=\''+cConfig.defaultAvatar+'\';return false">\
            <div class="cp-txt">\
              <h3> - </h3>\
              <p> - </p>\
            </div>\
        </div>\
      </div>\
      <div class="page-view-content"  >\
          <div class=" cp-statis-list">\
          </div>\
          <ul class="cp-options-list">\
            <li><a class="btn btn-ripple" href="javascript:void(0);" onclick="pageMethods.goPage(\'#/profile\')"><i class="fa fa-cog"></i>个人信息</a></li>\
            <li><a class="btn btn-ripple" href="javascript:void(0);" onclick="pageMethods.goPage(\'#/disclaimer\')"><i class="fa fa-legal"></i>免责声明</a></li>\
            <li><a class="btn btn-ripple" href="javascript:void(0);" onclick="pageMethods.logout()"><i class="fa fa-sign-out"></i>退出登录</a></li>\
          </ul>\
      </div>\
      '+ cParseTemplates().footerBar('user') +'\
    '

     html +='</div>'
     return html;
   },
   //
   statisItem : function(data){
     var className = data.className ? data.className : '';
     return '<div class="cp-statis-item col-xs-4'+className+'">\
       <i class="cp-iconfont fa fa-'+data.icon+'"></i>\
       <b class="num">'+data.num+'</b>\
       <b class="t">'+data.unit+'</b>\
     </div>'
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
   /**
    * 加载用户信息
    * @param  string url
    */
   loadUserInfo : function(){
     var that = this;
     cAccessAjax({
       type:'get',
       dataType:'json',
       url:cApiUrl.getUserInfo,
       success:function(rs){
         if(!cCheckLoginByCode(rs.code)){return false;}
         if(rs.code === 0){
           console.log(rs.data)
           var userDatas = rs.data;
           GB_VAR['user_info'] = userDatas;
           that.showUserInfo();
         }
       }
     })
   },


   /**
    * 显示用户信息
    */
   showUserInfo : function(){
     var $pageWrap = $("#Page-user-index");
     var $headWrap = $pageWrap.find(".cp-heading");
     var $avatar   = $headWrap.find(".cp-avatar");
     $headWrap.find('h3').text(GB_VAR["user_info"]["name"]);
     $headWrap.find('p').text(GB_VAR['user_info']['loginname']);

     if(typeof(GB_VAR['userAvatar'])=='undefined' || GB_VAR['userAvatar']=='' ){
       if(GB_VAR['user_info']['imgpath']!='' && GB_VAR['user_info']['imgpath']!='null' && GB_VAR['user_info']['imgpath']!=null ){
         GB_VAR['userAvatar'] = cConfig.avatarPath + GB_VAR['user_info']['imgpath'];
       }else{
         GB_VAR['userAvatar'] = cConfig.defaultAvatar;
       }
       $avatar.attr('src',GB_VAR['userAvatar']+'?v='+GB_VAR['rv']);
     }
     if(GB_VAR['userAvatar'].indexOf('base64') > 0 && GB_VAR['userAvatar'].indexOf('data:') !== -1 ){
       $avatar.attr('src',GB_VAR['userAvatar']);
     }else{
       $avatar.attr('src',GB_VAR['userAvatar']+'?v='+GB_VAR['rv']);
     }

   },

   /**
    *  取得用户统计数据
    */
   getUserStatis : function(){
     var that = this;
     cAccessAjax({
       type:'get',
       dataType:'json',
       url:cApiUrl.getUserStatis,
       success:function(rs){
         if(!cCheckLoginByCode(rs.code)){return false;}
         if(rs.code === 0){
           var html = ''
           that.showUserStatis(rs.data);
         }
       }
     })
   },

   /**
    * 显示用户统计数
    */
    showUserStatis : function(data){
      var defaults = {
        "total_trips"   : {"unit":"人次", "icon":"male","num":"-"},
        "total_distance": {"unit":"公里", "icon":"map","num":"-"},
        "total_carbon"  : {"unit":"千克碳","icon":"leaf","num":"-"}
      }
      var html = ''
      for(var key in defaults) {
        if(data && typeof(data[key])!='undefined'){
          defaults[key].num = data[key];
        }
        html += pageTempates.statisItem(defaults[key]);
      }
      $('#Page-user-index').find('.cp-statis-list').html(html);
    },

   /**
    * 登出
    */
  logout : function(){
    dialog.alert({
        title:"提示",
        msg:'是否确定退出登录',
        buttons:['取消','确定']
    },function(ret){
      if(ret.buttonIndex==1){
        return false;
      }
      if(ret.buttonIndex==2){
        cLogout();
      }
    });
   },



 }


 /****** 渲染页面  *******/
 document.getElementById("app").innerHTML = pageTempates.main();
 pageMethods.showUserStatis();
 if(GB_VAR['jumpTo']!=''){
   var url = GB_VAR['jumpTo'];
   redirect(url);
 }
 if(GB_VAR['user_info']=='' || typeof(GB_VAR['user_info']['loginname'])=='undefined'){
   pageMethods.loadUserInfo()
 }else{
   pageMethods.showUserInfo()
 }
pageMethods.getUserStatis();
 /****** 执行页面  *******/








}
