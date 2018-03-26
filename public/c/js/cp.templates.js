/***** 各html模板 ******/
function cParseTemplates(){

  //行程卡片
  this.routeCardItem = function(data,options){
    var defaults = {
      type:'info',
      uid:0,
      clickAction:'return true'
    }
    var theDateArray  = data.time.split(' ');
    var opt           = $.extend({},defaults,options);
    var userDatas     = opt.userDatas;
    userDatas.imgpath = $.trim(userDatas.imgpath)!='' ? cConfig.avatarPath + userDatas.imgpath : cConfig.defaultAvatar;
    var html = '\
      <div class="cp-routeCard-item col-sm-6 col-md-4 " data-id="'+data.id+'" data-from="'+opt.dataFrom+'">\
        <div class="cp-routeCard-item-inner" onclick="'+opt.clickAction+'">\
          <div class="cp-avatar-wrap">\
            <img class="cp-avatar" src="'+userDatas.imgpath+'" onerror="this.src=\''+cConfig.defaultAvatar+'\';return false">\
          </div>\
          <div class="cp-user-wrapper">\
            <div class="cp-name-bar">\
              <h4>'+userDatas.name+'</h4>\
              <span class="cp-phone"><a href="tel:'+userDatas.phone+'" onclick="event.stopPropagation();"><i class="fa fa-phone"></i></a></span>\
              <h8>'+opt.typeLabel+'</h8>\
            </div>\
            <div class="cp-userInfo-bar">\
              <span class="cp-dept">'+userDatas.Department+'</span>\
              <b class="cp-carnumber">'+userDatas.carnumber+'</b>\
            </div>\
          </div>\
          <div class="cp-body">\
            <div class="cp-route-wrap">\
              <div class="cp-route-line">\
                <div class="cp-line"></div>\
                <b class="cp-point cp-point-s"></b>\
                <b class="cp-point cp-point-e"></b>\
              </div>\
              <div class="cp-start"><h4>'+data.start_info.addressname+'</h4></div>\
              <div class="cp-date-wrap">\
                <b class="cp-time">'+theDateArray[1]+'</b>\
                <span class="cp-date">'+theDateArray[0]+'</span>\
              </div>\
              <div class="cp-end"><h4>'+data.end_info.addressname+'</h4></div>\
            </div>\
            '+opt.tipsWrapTemp+'\
          </div>\
        </div>\
      </div>\
    '
    return html;
  }

  //卡片按鈕
  this.routeCardBtns = function(data){
    var surSeatCount = (parseInt(data.seat_count)-parseInt(data.took_count));
        surSeatCount = surSeatCount < 0 ? 0 : surSeatCount;
    // 你是否搭过该行程，已搭图标以不同色显示
    var hasTakeBtn   = {hasClass:'',btnType:'primary',icon:'fa-user'}
    if( data.hasTake == 1){
        hasTakeBtn   = {hasClass:'hasTake',btnType:'danger',icon:'fa-user'}
    }
    var hasLikeBtn   = {hasClass:'', btnType:'primary',icon:'fa-heart'}
    if( data.hasLike == 1){
        hasLikeBtn   = {hasClass:'hasLike',btnType:'danger',icon:'fa-heart'}
    }
    return '<div class="tips-wrap text-center">\
          <div class="cp-fabBtn-wrap  '+hasLikeBtn.hasClass+'"><b class="t">点赞</b><a href="javascript:void(0);" class="btn btn-'+hasLikeBtn.btnType+'  btn-fab " onclick="pageMethods.likeRoute('+data.id+',this)"><i class="fa '+hasLikeBtn.icon+'"></i></a><b class="num">'+data.like_count+'</b></div>\
          <div class="cp-fabBtn-wrap"><b class="t">空位</b><a href="javascript:void(0);" class="btn btn-primary btn-fab "><i class="fa fa-car"></i></a><b class="num">'+surSeatCount+'</b></div>\
          <div class="cp-fabBtn-wrap '+hasTakeBtn.hasClass+'"><b class="t">已搭</b><a href="javascript:void(0);" class="btn btn-'+hasTakeBtn.btnType+' btn-fab "><i class="fa '+hasTakeBtn.icon+'"></i></a><b class="num">'+data.took_count+'</b></div>\
        </div>'
  }


  //网络出错开载更败时提示
  this.failLoadTips = function(data){
    var defaults = {
      tips:'网络君开了小猜，请稍候再试',
    }
    var opt = $.extend({},defaults,data);
    return '<div class="cp-error-tips"><p>'+opt.tips+'</p><a class="btn btn-sm btn-default btn-refresh btn-ripple">重加载</a></div>'
  }

  // 无数据时提示
  this.noDataTips = function(data){
    var defaults = {
      tips:'暂时没有数据⁽⁽ƪ(ᵕ᷄≀ ̠˘᷅ )ʃ⁾⁾ᵒᵐᵍᵎᵎ',
    }
    var opt = $.extend({},defaults,data);
    return '<div class="cp-nodata-tips"><p>'+opt.tips+'</p></div>'
  }


  //标题栏
  this.pageViewTitle = function(data){
    var defaults = {
      title:'溢起拼车',
      goBackAction:'cGoBack(this);',
      goBackIcon :'fa fa-angle-left',
      goBack : 1,
      className : '',
      rightContent:''
    }
    var opt = $.extend({},defaults,data);
    var html  = '<div class="page-view-title '+opt.className+'">'
        html += opt.goBack == 1 ? '<a class="cp-btn-goback" onclick="'+opt.goBackAction+'"> <i class="cp-iconfont '+opt.goBackIcon+'"></i> </a>' :'';
        html += '<div class="cp-title">'+opt.title+'</div>'+opt.rightContent;
        html += '</div>'
    return html;
  }



  // 乘客列表
  this.wallViewPassengerItem = function(data){
    var avatar = data.imgpath ? cConfig.avatarPath + data.imgpath : cConfig.defaultAvatar;
    return '<li class="cp-item ">\
      <img class="cp-avatar pull-left img-circle img-responsive " src="'+avatar+'" alt="Avatar"  onerror="this.src=\''+cConfig.defaultAvatar+'\';return false">\
      <div class="cp-txt">\
        <h4 class="media-heading">'+data.name+'</h4>\
        <p>'+data.Department+'</p>\
      </div>\
      <div class="cp-btns-wrap">\
        <a href="tel:'+data.phone+'" class="btn  btn-fab btn-fab-mini"><i class="fa fa-phone"></i></a>\
      </div>\
   </li>'
  }

  // 详细页的数据统计项组件
  this.statisItem = function(data){
    var className = data.className ? data.className : '';
    return '<div class="cp-statis-item '+className+'">\
      <b class="t">'+data.title+'</b>\
      <i class="cp-iconfont fa fa-'+data.icon+'"></i>\
      <b class="num">'+data.num+'</b>\
    </div>'
  }

  //地址项
  this.addressItem = function(data){
    var className =    "cp-item ";
    var icon = '<i class="fa fa-map-pin"></i>'
    if( typeof(data.address_type) != 'undefined'){
      className = "cp-item cp-type-"+data.address_type;
      if(data.address_type=='Home'){
        icon = '<i class="fa fa-home"></i><h6>家</h6>'
      }else if(data.address_type=='Work'){
        icon = '<i class="fa fa-suitcase"></i><h6>公司</h6>'
      }
    }
    var addressHtml = typeof(data.address)=='string' ? '<p class="address">'+data.address+'</p>' : '';
    return '<li class="'+className+'" data-id="'+data.addressid+'" data-latitude="'+data.latitude+'" data-longtitude="'+data.longtitude+'" onclick="pageMethods.selectAddressPoint(this)">\
      '+icon+'<b class="name">'+data.addressname+'</b>\
      '+addressHtml+'\
    </li>';
  }

  //评论列表
  this.commentItem = function(data,options){
    var loading = options.loading ? options.loading : 0;
    var className   = data.className ? data.className : '';
    var avatar      = data.avatar ? data.avatar : (data.imgpath ? cConfig.avatarPath + data.imgpath : cConfig.defaultAvatar);
    var html_loading    = loading ? '<i class="cp-loadingIcon fa fa-circle-o-notch fa-spin "></i>' : '';
    return '<li class="cp-comment-item '+className+'" data-id="'+data.id+'">\
      <div class="cp-avatarbox">\
        <img class="cp-avatar" src="'+avatar+'" alt="Avatar"  onerror="this.src=\''+cConfig.defaultAvatar+'\';return false">\
      </div>\
      <div class="cp-mainbox">\
        <div class="cp-title">\
          <b class="name">'+data.name+'</b>\
          <span class="time">'+data.time+'</span>\
        </div>\
        <div class="cp-content">'+data.content+html_loading+'</div>\
      </div>\
    </li>\
    '
  }

  //加载更多的loading
  this.listLoading = function(){
    return '<div class="cp-loading-l"><span></span><span></span><span></span><span></span><span></span></div>';
  }

  this.popPanel = function(header,content){
    return  '<div class="cp-poppanel">\
  		<div class="cp-poppanel-header">'+header+'\
  			<div class="cp-poppicker-clear"></div>\
  		</div>\
  		<div class="cp-poppicker-body">'+content+'</div>\
  	</div>'
  }
  this.picker = function(datas){
    var itemLists = '';
    $.each(datas,function(index,item){
      itemLists += '<li class="visible" data-value="'+item.value+'"><span>'+item.name+'</span></li>'
    })
    console.log(itemLists)
    return '<div class="cp-picker">\
  		<div class="cp-picker-inner">\
  			<div class="cp-picker-rule cp-picker-rule-ft"></div>\
  			<ul class="cp-picker-list">'+itemLists+'</ul>\
  			<div class="cp-picker-rule cp-picker-rule-bg"></div>\
  		</div>\
  	</div>'
  }

  /* 模态框 */
  this.modal =function(datas){
    var defaults = {
      id : 'J-modal',
      title   : '',
      className : '',
      footer : 0 ,
      clickOK : '',
      okBtnTxt :'提交',
      okBtnLoading :'提交中..',
      content:''

    }
    var opt = $.extend({},defaults,datas);
    var footerHtml = ''
    if(opt.clickOK || opt.footer ){
      if(typeof(opt.footer) == 'string'){
        footerHtml = '<div class="modal-footer">'+opt.footer+'</div>'
      }else{
        var okBtn = '<button type="button" class="btn btn-primary" onclick="'+opt.clickOK+'" data-loading-text="'+opt.okBtnLoading+'">'+opt.okBtnTxt+'</button>'
          footerHtml = '<div class="modal-footer">\
          <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>\
          '+okBtn+'\
        </div>'
      }
    }

    return '\
    <div class="modal fade cp-modal in '+opt.className+' " tabindex="-1" role="dialog" id="'+opt.id+'">\
      <div class="modal-dialog" role="document">\
        <div class="modal-content">\
          <div class="modal-header">\
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>\
            <h4 class="modal-title">'+opt.title+'</h4>\
          </div>\
          <div class="modal-body">\
            '+opt.content+'\
          </div>\
          '+footerHtml+'\
        </div>\
      </div>\
    </div>\
    '
  },

  this.backdrop = function(){
    return '<div class="modal-backdrop fade in"></div>'
  }

  this.footerBar = function(activeIndex){
    var btnDatas = {
      // 'webim':{icon:'fa-comment',txt:'溢信',url:'#/webim'},
      'car':{icon:'fa-car',txt:'拼车',url:'#/index'},
      'user':{icon:'fa-user',txt:'我的',url:'#/user'},
    }

    var html = '<div class="cp-bar cp-bar-tab cp-footer" id="footer">';
    for(var key in btnDatas){
      var isActive = activeIndex == key ? 'active' :'';
      /*if(key == 'comment' && GB_VAR['webim_access_token']==''){
        continue;
      }*/
      html += '<div class="cp-bar-tab-item '+isActive+'" >\
      <a class="btn-ripple" href="'+btnDatas[key].url+'">\
        <i class="cp-iconfont fa '+btnDatas[key].icon+'"></i>\
        <div class="cp-bar-tab-label">'+btnDatas[key].txt+'</div>\
        </a>\
      </div>'
    }
    return  html;
  }

  return this;
}
