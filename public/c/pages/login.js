SPA_RESOLVE_INIT = function(transition) {
  /****** DOM *******/
  var  pageTempates = {
    main : function(){
      var html = '<div class="page-view-login " id="Page-login">'
      html += '<div class="cp-pagebg "></div>'
      html += '\
      <div class="page-view-content">\
      <div class="page-view-header">\
        <div class="cp-loginlogo-wrapper">\
          <img class="cp-logo"  src="../images/login_logo.png">\
        </div>\
      </div>\
      <div class="container">\
        <div class="cp-login-wrap" id="login">\
          <div class="cp-form-wrap">\
            <form class="form form-login form-horizontal"　 method="post" onsubmit="return pageMethods.submitForm();">\
              <div class="cp-form-group ">\
                <label  class="control-label "  for="username"><i class="fa fa-id-card"></i> </label>\
                <input type="text" class="form-control   input-lg" name="username" placeholder="用户名">\
                <!-- <div class="help-block">可使用手机号、邮箱、帐号名登入</div> -->\
              </div>\
              <div class="cp-form-group ">\
                <label  class="control-label"  for="password"><i class="fa fa-key"></i></label>\
                <input type="password" class="form-control   input-lg" name="password" placeholder="密码">\
              </div>\
              <div class="cp-tips-disclaimer">点击“登入”按钮即代表阅读并同意<a href="#/disclaimer">《使用协议》</a></div>\
              <div class="text-danger" id="callback-tips"></div>\
              <input type="hidden" name="client" value="h5">\
              <button class="btn btn-primary btn-lg J-btn-submit" type="submit" data-loading-text="登 入 中...">登 入</button>\
              <!-- <p class="cp-register"><a  href="register.html">注册carpool帐号</a></p> -->\
            </form>\
          </div>\
        </div>\
      </div>\
      </div>\
    '
     html +='</div>'
     return html;
   },

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

   goIndex : function(){
     redirect('#/index');
   },

  submitForm: function(){
     var $form = $('.form-login');

     var datas = $form.serializeArray();
     var cookpf = ''
     $('.J-btn-submit').button('loading');

     $.ajax({
       type:'POST',
       dataType:'json',
       url: cApiUrl.login,
       data:datas,
       success:function(json){
         if(json.code===0){
           // setCookie(cookpf+'user_token',json.token);
           // $('#callback-tips').show().html('<i class="fa fa-check-circle"></i> '+json.msg).addClass('text-success').removeClass('text-danger');
           $('#login').addClass('cp-login-success');
           localStorage.setItem('CP_U_TOKEN',json.data.token);

           // GB_VAR['username'] = json.data.user.loginname.toLowerCase() ;
           json.data.user.loginname = json.data.user.loginname.toLowerCase();
           GB_VAR['userBaseInfo'] =  json.data.user;

           // localStorage.setItem('HAS_LOGIN',1);
           toast.success({title:'登入成功',  duration:2000});
           setTimeout(function(){
             // cGoBack();
             pageMethods.goIndex();
           },200);
         }else{
           toast.fail({title:json.desc,  duration:2000});
           // $('#callback-tips').show().html('<i class="fa fa-times-circle"></i> '+json.msg).addClass('text-danger').removeClass('text-success');
         }
       },
       complete:function(XHR){
         $('.J-btn-submit').button('reset');
       }
     })
     return false;
   },

   checkLogin : function(){
     var token = window.localStorage.getItem('CP_U_TOKEN');
     if(token && token!=''){
       cAccessAjax({
         type:'get',
         dataType:'json',
         url:cApiUrl.checkLogin,
         success:function(rs){
           if(rs.code === 0){
             var userDatas = rs.data;
             userDatas.loginname = userDatas.loginname.toLowerCase();
             GB_VAR['userBaseInfo'] =  userDatas;
             // GB_VAR['username'] = userDatas['loginname'].toLowerCase() ;
             // GB_VAR['user_info'] = userDatas;
             pageMethods.goIndex();
           }

         }
       })
     }

   }

 }


 /****** 渲染页面  *******/
 document.getElementById("app").innerHTML = pageTempates.main();
 if(GB_VAR['jumpTo']!=''){
   var url = GB_VAR['jumpTo'];
   redirect(url);
 }


 /****** 执行页面  *******/
 pageMethods.checkLogin();




}
