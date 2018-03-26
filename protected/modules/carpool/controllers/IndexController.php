<?php
class IndexController extends CarpoolBaseController {
  public function init() {
		parent::init();
	}
  //拼车首页
    public function actionIndex()
  	{
      $userInfo = $this->userBaseInfo;
      $webim_access_token = '';
      if(!isset($_COOKIE['web_im_'.$userInfo->loginname]) || empty($_COOKIE['web_im_'.$userInfo->loginname])){
        $webIm  = $this->loginWebim();
        if($webIm){
          if(isset($webIm['access_token'])){
            $webim_access_token = $webIm['access_token'];
          }
        }
      }
      if(Yii::app()->getRequest()->isAjaxRequest){
        $this->ajaxOK('已登入',$userInfo);
      }else{
        // $this->renderPartial('index',array('userInfo'=>$userInfo,'webim_access_token'=>$webim_access_token));
      }
  	}

    public function actionTest(){
      $str = $this->sGet('str');
      $data = md5($str);
      var_dump($data);
    }

    public function actionCheck_login(){
      $isMore = $this->iRequest('more');
      $userInfo = json_decode(CJSON::encode($this->userBaseInfo),true);
      if($isMore == 1 ){
        $userInfo_ex = $this->getUser();
        $userInfo['avatar'] = $userInfo_ex->imgpath;
        $userInfo['Department'] = $userInfo_ex->Department;
      }
  			$this->ajaxOK('已登入',$userInfo);
  	}





}
