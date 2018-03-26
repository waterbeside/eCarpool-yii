<?php

/*本控制器免登入操作*/

class PublicsController extends CarpoolBaseController {

	public function actions() {
		return array_merge(parent::actions(), array(
			// captcha action renders the CAPTCHA image displayed on the contact page
			'captcha'=>array(
				'class'=>'CCaptchaAction',
				'foreColor'=>0x6091ba,
				'backColor'=>0xFFFFFF,
				'testLimit'=>1,
			),
		));
	}

  public function init() {
    parent::init();
  }


	public function actionIndex(){
		if(Yii::app()->getRequest()->isAjaxRequest){
			if($this->checklogin()){
				return  $this->ajaxReturn(0,[],'success');
			}else{
				return  $this->ajaxReturn(-1,[],'fail');
			};
		}
		// $this->renderPartial('index');
		exit;

	}



	/**
	 * 通过JWT令牌自动登入
	 */
  public function actionAutologin(){
      $Authorization =  $this->sRequest('token');
			$jwt = $Authorization;
      if(!$Authorization){
        $this->error('令牌为空');
      }

      $jwtDecode = Yii::app()->JWT->decode($Authorization);
      if(!$jwtDecode){
        $this->error('令牌有误');
      }
      if(time()  > $jwtDecode->exp){
        $this->error('令牌超时，请重新登入');
      }
      $userData  = array(
        'loginname' => $jwtDecode->loginname,
        'iss' => $jwtDecode->iss,
				'uid' => $jwtDecode->uid,
        "name" => $jwtDecode->name,
        "company_id" => $jwtDecode->company_id,
      );
      /*$model = new CarpoolLoginForm();
      $jwt = $model->createJwt($userData);*/
      setcookie('CP_U_TOKEN', $Authorization, $jwtDecode->exp , '/');
      $this->renderPartial('autologin',['jwt'=>$jwt]);
  }

	/**
	 * 登入和登入提交
	 */
  public function actionLogin(){
    if(Yii::app()->request->isPostRequest){

			 if($this->iPost('dev')){
				 echo('get <br />');
				 var_dump($_GET);
				 echo('post <br />');
				 var_dump($_POST);
			 }
      $model = new CarpoolLoginForm();
      $formData = array(
        'username' => $this->sPost('username'),
        'password' => $this->sPost('password'),
				'client' => $this->sPost('client'),
      );
			if(!in_array($formData['client'],array('ios','android','h5','web','third'))){
				return  $this->ajaxReturn(-1,[],'client error');
			};
  		// collect user input data
			$model->attributes = $formData;
			// validate user input and redirect to the previous page if valid

			if(!$model->validate()){
				foreach ($model->getErrors() as $key => $value) {
					return $this->ajaxReturn(-10002,[],$value[0]);
        }
			}
			$data = $model->login();
			if ($data) {
				return $this->ajaxReturn(0,$data,"success");
        // return $this->success('登入成功',$this->createUrl('index/index'),$data);
				// $this->redirect(Yii::app()->user->returnUrl);
			}else{
					return $this->ajaxReturn(10001,[],"帐号或密码错误");
      }
      exit;
    }
    $this->renderPartial('login');
  }

	/**
	 * 登出
	 */
  public function actionLogout(){
    // Yii::app()->user->logout();
    setcookie('CP_U_TOKEN',null,time(),'/');
		$this->ajaxOK('登出成功');

  }


	/**
	 * 取得地址列表
	 */
	public function actionGet_address() {
    $pageSize = 30;
    $keyword = $this->sGet('keyword');
    $model = new Address();
    $criteria = new CDbCriteria();

    if(!empty($keyword)){
        // $criteria->addCondition($value);
        $criteria->addSearchCondition('addressname',$keyword);
    }
    $criteria->addCondition('address_type = 0','and');
    // $criteria->select ='*';
    $criteria->order = 'addressid asc';
    $count = $model->count($criteria);
    $page = new CPagination($count);
    $page->pageSize = $pageSize;
    $page->applyLimit($criteria);
    $lists = $model->findAll($criteria);
    $lists =  json_decode(CJSON::encode($lists),true);
    foreach ($lists as $key => $value) {
      unset($lists[$key]['pre2']);
      unset($lists[$key]['ordernum']);
    }
    $pageReturn  = array(
      'pageSize' => $page->getPageSize(),
      'pageCount' => $page->getPageCount(),
      'currentPage' =>  $page->getCurrentPage(),
      'total' =>  $page->getItemCount(),
      'params' =>  $page->params,
    );

    $data = array('lists'=>$lists,'page'=>$pageReturn);
		$this->ajaxReturn(0,$data,'');
		exit;
    // exit(json_encode(array('code'=>0,'desc'=>'','data'=>$data)));

  }

	public function actionTest_ajax(){
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Headers:x-requested-with,content-type');
		// var_dump($this->userBaseInfo);
		// $userInfo = json_decode(CJSON::encode($this->userBaseInfo),true);
			// $this->ajaxOK('已登入',$userInfo);
		// $arrayName = array('test' => 123, );
		 // echo json_encode($arrayName);
		 exit;

	}
}
