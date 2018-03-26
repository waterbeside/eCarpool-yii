SPA_RESOLVE_INIT = function(transition) {
  var btnItemArray = '';
  /****** DOM *******/
  var  pageTempates = {
    main : function(){
      var html = '<div class="page-view" id="Page-user-profile">'
      // html += cParseTemplates().pageViewTitle({title:'修改个人资料'});
      html += '\
      <div class="page-view-main "    >\
        <div class="cp-scroller-wrap" >\
          <div class="cp-scroller "   >\
            <div class="page-view-header">\
              <a class="cp-btn-close" onclick="cGoBack(this);"> <i class="cp-iconfont fa fa-times"></i> </a>\
              <div class="cp-heading " >\
                  <img class="cp-avatar  btn-ripple " src="'+cConfig.defaultAvatar+'" onclick="pageMethods.goPage(\'#/userAvatar\')" onerror="this.src=\''+cConfig.defaultAvatar+'\';return false">\
                  <label>修改</label>\
                  <div class="cp-txt">\
                    <h3> - </h3>\
                  </div>\
              </div>\
            </div>\
            <div class="cp-container">\
              <ul class="cp-list-profile">\
                '+cParseTemplates().listLoading()+'\
              </ul>\
            </div>\
          </div>\
        </div>\
      </div>\
      '
      html +='</div>'
      return html;
    },

    /** 个人资料项目按钮 **/
    profileItem: function(data){
      var defaults = {
        title   : '',
        action  : 'pageMethods.editProfile("",this)',
        icon    : 'fa fa-circle-o',
        editIcon: 'fa fa-pencel',
        txt     : '',
        val     : '',
        className : '',
      }
      var opt = $.extend({},defaults,data);
      var editIconBtn = opt.action ? '<b><i class="pull-right fa  fa-angle-right"></i></b>' :'';
      var isRippel = opt.action ? 'btn-ripple' : '';
      return '<li class="'+opt.className+'" data-val="'+opt.val+'">\
        <a class="btn '+isRippel+'" href="javascript:void(0);" onclick="'+opt.action+'">\
          <div class="la"><i class="'+opt.icon+'"></i>'+opt.title+'</div>\
          <span>'+opt.txt+'</span>\
          '+editIconBtn+'\
        </a>\
      </li>'
    },


  /** 表单内容设定  **/
  formContent: function(data){
    var defaults = {
      label   : '', //标题
      icon    : 'fa fa-circle-o', //图标
      inputType   : 'text',
      inputName  : '',
      inputVal   : '',
      inputPlaceholder:"",
      moreInput : '',
      moreHtml :'',
      type :'' //指定要修改的字段（或字段简写）
    }
    var opt = $.extend({},defaults,data);
    return '<form class="cp-form-profile form-horizontal">\
    <input type="hidden" name="type" value="'+opt.type+'">\
    <div class="cp-form-group">\
      <label for="'+opt.inputName+'" class="control-label"><i class="'+opt.icon+'"></i> '+opt.label+'</label>\
      <input type="'+opt.inputType+'" name="'+opt.inputName+'" class="form-control form-control-line" value="'+opt.inputVal+'" placeholder="'+opt.inputPlaceholder+'"">\
      '+opt.moreInput+'\
    </div>\
    '+opt.moreHtml+'\
    </form>'
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
     var $pageWrap = $('#Page-user-profile');
     var $headWrap = $pageWrap.find('.cp-heading');
     var userDatas = GB_VAR['user_info'];
     $headWrap.find('h3').text(userDatas['name']);
     $headWrap.find('p').text(userDatas['loginname']);
     if(typeof(GB_VAR['userAvatar'])=='undefined' || GB_VAR['userAvatar']=='' ){
       if(GB_VAR['user_info']['imgpath']!='' && GB_VAR['user_info']['imgpath']!='null' && GB_VAR['user_info']['imgpath']!=null ){
         GB_VAR['userAvatar'] = cConfig.avatarPath + GB_VAR['user_info']['imgpath'];
       }else{
         GB_VAR['userAvatar'] = cConfig.defaultAvatar;
       }
     }
     $headWrap.find('.cp-avatar').attr('src',GB_VAR['userAvatar']);

     btnItemArray = [
       {icon:'fa fa-bank',  title:'部门',   val:userDatas.Department,txt:userDatas.Department,action:false},
       // {icon:'fa fa-phone', title:'电话',   val:userDatas.phone,txt:userDatas.phone, action:'pageMethods.editProfile(\'phone\',this)'},
       {icon:'fa fa-vcard', title:'车牌',   val:userDatas.carnumber,txt:userDatas.carnumber, action:'pageMethods.editProfile(\'carnumber\',this)'},
       {icon:'fa fa-car',   title:'车型号', val:userDatas.cartype,txt:userDatas.cartype, action:'pageMethods.editProfile(\'cartype\',this)'},
       {icon:'fa fa-home',  title:'家',    val:userDatas.home_address_id,txt:userDatas.home_address, action:'pageMethods.addressSelect(\'home\',this)'},
       {icon:'fa fa-building', title:'公司',val:userDatas.company_address_id,txt:userDatas.company_address, action:'pageMethods.addressSelect(\'company\',this)'},
       {icon:'fa fa-lock',  title:'密码',   val:'',txt:'******', action:'pageMethods.editProfile(\'password\',this)'},

     ];
     var btnItemListHtml = '';
     for(i=0;i<btnItemArray.length;i++){
       btnItemListHtml += pageTempates.profileItem(btnItemArray[i]);
     }
     $pageWrap.find('.cp-list-profile').html(btnItemListHtml)
    //  console.log(GB_VAR['user_info']);
   },

   /**
    * 显示用户信息
    */
   editProfile : function(type,obj){
     var modalID = 'J-modal-profileForm';
     $('#'+modalID).remove();
     $('.modal-backdrop').remove();

     // var typeArray = ['department','phone','carnumber','cartype','home_address_id','company_address_id','password']
     var typeArray = ['department','carnumber','cartype','home_address_id','company_address_id','password']
     var ido = typeArray.indexOf(type);

     if(ido>0){
       var formContent = {
         label:'',
         icon:btnItemArray[ido].icon,
         inputVal:btnItemArray[ido].val,
         inputName:type,
         type:type,
       }
       if(type=='password'){
         formContent.inputPlaceholder = "请输入旧密码";
         formContent.inputType = "password";
         formContent.icon = 'fa fa-key';
         formContent.moreHtml = '<div class="cp-form-group label-floating">\
           <label for="pw_new" class="control-label"><i class="fa fa-lock"></i> </label>\
           <input type="password" name="pw_new" class="form-control form-control-line" value="" placeholder="新密码" >\
         </div>\
         <div class="cp-form-group label-floating">\
           <label for="pw_confirm" class="control-label"><i class="fa fa-lock"></i> </label>\
           <input type="password" name="pw_confirm" class="form-control form-control-line" value="" placeholder="再次输入新密码" >\
         </div>\
         '
       }
      //  console.log(formContent);
       var modalData = {
         id : "J-modal-profileForm",
         title:'修改'+btnItemArray[ido].title,
         clickOK: 'pageMethods.submitEdit(\''+type+'\',this)'
       }
       if(type=='home_address_id' || type == 'company_address_id'){
         modalData.title ='修改'+btnItemArray[ido].title+'地址'
       }

       modalData.content =  pageTempates.formContent(formContent);

       //  $('body').addClass('modal-open').append(cParseTemplates().modal()).append(cParseTemplates().backdrop());
       $('body').append(cParseTemplates().modal(modalData));
       $('#'+modalID).modal('show');
     }else{
       return false;
     }
   },

   /* 提交编辑表单 */
   submitEdit: function(type,obj){
     toast.loading({title:"提交中",duration:1000});
     var $btn = $(obj);
     var $form = $('.cp-form-profile');
     var data = $form.serialize();
     var val = $form.find('[name='+type+']').val();
     cAccessAjax({
       type     :'post',
       dataType :'json',
       url      : cApiUrl.editProfile,
       data     : data,
       success:function(rs){
         if(!cCheckLoginByCode(rs.code)){return false;}
         if(rs.code === 0 ){
           if(type!='password'){
             GB_VAR['user_info'][type] = val;
           }
          //  console.log(GB_VAR['user_info'])
           pageMethods.showUserInfo();
           setTimeout(function(){
             toast.success({title:rs.desc,  duration:700});
           },300);
           $('#J-modal-profileForm').modal('hide');
         }else{
           setTimeout(function(){
             toast.fail({title:rs.desc,  duration:1500});
           },300)
         }
       },
       error:function(){
         setTimeout(function(){
           toast.custom({html:'',title:'网络不畅通，请稍候再试',  duration:1000});
         },300)

       },
       complete:function(XHR){
         toast.hide();
         $btn.button('reset');
       }
     });

   },
   /* 选择地址 */
   addressSelect : function(from,obj){
       from = from || '0';
       redirect('#/selectMyAddress?from='+ from);
   }
 }


  /****** 渲染页面  *******/
  if($('#Page-user-profile').length>0){
    $('#Page-user-profile').siblings('.page-view').remove();
  }else{
    document.getElementById("app").innerHTML = pageTempates.main();
  }

  var $pageWrap =  $('#Page-user-profile');

  if(GB_VAR['jumpTo']!=''){
   var url = GB_VAR['jumpTo'];
   redirect(url);
  }
  if(GB_VAR['user_info']=='' || typeof(GB_VAR['user_info']['loginname'])=='undefined'){
   pageMethods.loadUserInfo();
  }else{
   pageMethods.showUserInfo();
  }

   /****** 执行页面  *******/








}
