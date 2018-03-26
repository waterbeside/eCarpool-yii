SPA_RESOLVE_INIT = function(transition) {
  /****** DOM *******/
  var  pageTempates = {
    main : function(){
      var html = '<div class="page-view" id="Page-user-avatar">'
      html += cParseTemplates().pageViewTitle({title:'修改头像'});
      var avatar = GB_VAR['userAvatar'] !="" ? GB_VAR['userAvatar'] :cConfig.defaultAvatar;

      html += '\
      <div class="page-view-main cp-pdt "  > \
            <div class="avatar-body">\
              <!-- Crop and preview -->\
              <div class="row">\
                <div class="col-lg-9">\
                  <div class="avatar-wrapper"></div>\
                </div>\
                <div class="col-lg-3">\
                  <label class="cp-label-avatar-preview">预览：</label>\
                  <div class="avatar-preview preview-lg"></div>\
                  <div class="avatar-preview preview-md"></div>\
                  <div class="avatar-preview preview-sm"></div>\
                  <div class="avatar-preview preview-md preview-circle"></div>\
                  <div class="cp-avatar-preview"></div>\
                  <div class="cp-avatar-preview preview-circle"></div>\
                </div>\
              </div>\
              <!-- Upload image and data -->\
              <div class="cp-avatarBtns-wrapper">\
                  <div class="cp-avatarBtns-inner">\
                    <button class="btn btn-warning  btn-block avatar-save" type="submit" onclick="pageMethods.submit()" data-loading-text="提交中...">完 成</button>\
                  </div>\
              </div>\
            </div>\
            <div class="cp-avatarSelectBox-wrapper">\
              <div class="cp-avatarSelectBox-inner">\
                <div class="avatar-view" title="Change the avatar" >\
                  <img src="'+avatar+'" alt="Avatar"  onerror="this.src=\''+cConfig.defaultAvatar+'\';return false">\
                </div>\
                <div class="avatar-upload">\
                  <form class="avatar-form" enctype="multipart/form-data" method="post" name="fileinfo">\
                    <input class="avatar-loginname" name="loginname" value="'+GB_VAR['userBaseInfo'].loginname+'" type="hidden">\
                    <input class="avatar-data" name="avatar_data" type="hidden">\
                    <label for="avatarInput" class="btn btn-success">选择图片</label>\
                    <input class="avatar-input" id="avatarInput" name="avatar_file" type="file" onchange="pageMethods.picSelect(this);">\
                    <button  class="btn btn-danger btn-goback" onclick="cGoBack()">取消</button>\
                  </form>\
                </div>\
              </div>\
        </div>\
      </div>'
      html +='</div>'
      return html;
    }


}


 /****** 页面方法  *******/
 pageMethods = {
   support: {
      fileList: !!$('<input type="file">').prop('files'),
      formData: !!window.FormData
    },
   init : function(){
     window.URL = (window.URL) ? window.URL : (window.webkitURL ? window.webkitURL : false);
     if(!window.URL){
       cGoBack();
     }
     this.$container = $("#Page-user-avatar");
     this.$avatarView = this.$container.find('.avatar-view');
     this.$avatar = this.$avatarView.find('img');
     // this.$avatarModal = this.$container.find('#avatar-modal');
     this.$loading = this.$container.find('.loading');

     this.$avatarForm = this.$container.find('.avatar-form');
     this.$avatarUpload = this.$avatarForm.find('.avatar-upload');
     this.$avatarSrc = this.$avatarForm.find('.avatar-src');
     this.$avatarData = this.$avatarForm.find('.avatar-data');
     this.$avatarInput = this.$avatarForm.find('.avatar-input');
     this.$avatarSave = this.$container.find('.avatar-save');
     this.$avatarBtns = this.$container.find('.avatar-btns');
     this.$avatarSelectBox = this.$container.find('.cp-avatarSelectBox-wrapper');
     this.$avatarWrapper = this.$container.find('.avatar-wrapper');
     this.$avatarPreview = this.$container.find('.avatar-preview');
     // console.log(this)
   },
   picSelect: function(ipt){
     var URL = window.URL;
     var files = $(ipt).prop('files');
     if (files.length > 0) {
       file = files[0];
       // console.log(file)
       if (this.isImageFile(file)) {
         if (this.url) {
           URL.revokeObjectURL(this.url);
         }
         this.url = URL.createObjectURL(file);
         this.$avatarSelectBox.addClass('hide')
         this.startCropper();
       }
     }
   },
   isImageFile: function (file) {
     if (file.type) {
       return /^image\/\w+$/.test(file.type);
     } else {
       return /\.(jpg|jpeg|png|gif)$/.test(file);
     }
   },
   startCropper: function () {
     var _this = this;
     if (this.active) {
       this.$img.cropper('replace', this.url);
     } else {
       this.$img = $('<img src="' + this.url + '">');
       this.$avatarWrapper.empty().html(this.$img);
       this.$img.cropper({
         aspectRatio: 1,
         preview: this.$avatarPreview.selector,
         strict: false,
         crop: function (data) {
           // console.log(data)

           var json = [
                 '{"x":' + data.x,
                 '"y":' + data.y,
                 '"height":' + data.height,
                 '"width":' + data.width,
                 '"rotate":' + data.rotate + '}'
               ].join();


           _this.$avatarData.val(json);
           pageMethods.convertToData(data,function(base64){
             _this.base64 =base64;
             // _this.$avatarSrc[0].files[0] = base64;
             // _this.$avatarSrc.val(GB_VAR['username']);
             $('.cp-avatar-preview').html('<img src="'+base64+'" />');
             // _this.$avatarPreview.attr('src',base64)
             // console.log(base64);
           })
         }
       });

       this.active = true;
     }
   },

   stopCropper: function () {
     if (this.active) {
       this.$img.cropper('destroy');
       this.$img.remove();
       this.active = false;
     }
   },

  convertToData:function(cutData,callback) {
    // console.log(cutData);

        var canvas = document.createElement("canvas");
        var ctx = canvas.getContext('2d');
        canvas.width = cutData.width
        canvas.height = cutData.height
        var img = new Image();
        img.src = this.url;

        img.onload = function() {
                // 这里主要是懂得canvas与图片的裁剪之间的关系位置
            ctx.drawImage(this, -cutData.x, -cutData.y, img.width, img.height);
            var base64 = canvas.toDataURL('image/jpeg', 1);  // 这里的“1”是指的是处理图片的清晰度（0-1）之间，当然越小图片越模糊，处理后的图片大小也就越小
            callback && callback(base64)      // 回调base64字符串
        }
    },
    /**
    * 将以base64的图片url数据转换为Blob
    * @param urlData
    *            用url方式表示的base64图片数据
    */
    convertBase64UrlToBlob:function(urlData){
       var bytes=window.atob(urlData.split(',')[1]);        //去掉url的头，并转换为byte
       //处理异常,将ascii码小于0的转换为大于0
       var ab = new ArrayBuffer(bytes.length);
       var ia = new Uint8Array(ab);
       for (var i = 0; i < bytes.length; i++) {
           ia[i] = bytes.charCodeAt(i);
       }
       return new Blob( [ab] , {type : 'image/jpeg'});
   },

   rotate: function (e) {
     // console.log(e)
     var data;
     if (this.active) {
       data = $(e.target).data();
       if (data.method) {
         this.$img.cropper(data.method, data.option);
       }
     }
   },

   submit: function () {
     /*if (!this.$avatarSrc.val() && !this.$avatarInput.val()) {
       alert(1)
       return false;
     }*/
     if (this.support.formData) {
       // this.$avatarSrc.val(this.url);
       this.ajaxUpload();
       return false;
     }
   },

   ajaxUpload: function () {
     var url = cApiUrl.uploadAvatar;
     // var   data = new FormData(this.$avatarForm[0]);
     var   data = new FormData();
     var   _this = this;
     // data.append('upload', _this.$avatarSrc.val());
     data.append('loginname',GB_VAR['userBaseInfo'].loginname);
     // data.append('from','H5');
     // console.log(_this.$avatarSrc.val());
     // console.log(data)
     _this.newBlobURL = _this.convertBase64UrlToBlob(_this.base64)
     data.append("upload",_this.newBlobURL)
     this.$avatarSave.button('loading');

     $.ajax(url, {
       type: 'post',
       data: data,
       dataType: 'text',
       processData: false,
       contentType: false,
       success: function (data) {
         _this.submitDone(data);
       },
       error: function (XMLHttpRequest, textStatus, errorThrown) {
         alert('上传失败，网络错误，请稍候再试');
         cGoBack();
       },
       xhr:function(){            //在jquery函数中直接使用ajax的XMLHttpRequest对象
           var xhr = new XMLHttpRequest();
           xhr.upload.addEventListener("progress", function(evt){
               if (evt.lengthComputable) {
                   var percentComplete = Math.round(evt.loaded * 100 / evt.total);
                   console.log("正在提交."+percentComplete.toString() + '%');        //在控制台打印上传进度
               }
           }, false);
           return xhr;
       },
       complete: function () {
         // _this.submitEnd();
       }
     });
   },
   submitStart: function () {
      this.$loading.fadeIn();
   },
   submitDone: function (data) {
      // console.log(data);
      var username = GB_VAR['userBaseInfo'].loginname;
      if( data == username+'.jpg' || data == username+'.png' || data == username+'.JPG' || data == username+'.PNG' ){
        GB_VAR['userAvatar'] = this.base64;
        GB_VAR['user_info']['imgpath'] = data;
        cGoBack();
      }else{
        alert('上传失败，请上传正确的图片格式');
        cGoBack();
      }
    },
 }





  /****** 渲染页面  *******/
  document.getElementById("app").innerHTML = pageTempates.main();

   /****** 执行页面  *******/
   pageMethods.init()

// cLoadScript('../js/crop_avatar.js')





}
