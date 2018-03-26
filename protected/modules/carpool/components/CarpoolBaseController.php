<?php
session_start();
// use EasyWeChat\Foundation\Application;

/**
 * Controller is the customized base controller class.
 * All controller classes for this application should extend from this base class.
 */
class CarpoolBaseController extends BaseController {
	/**
	 * @var string the default layout for the controller view. Defaults to '//layouts/column1',
	 * meaning using a single column layout. See 'protected/views/layouts/column1.php'.
	 */
	public $layout = '/layouts/main';
	/**
	 * @var array the breadcrumbs of the current page. The value of this property will
	 * be assigned to {@link CBreadcrumbs::links}. Please refer to {@link CBreadcrumbs::links}
	 * for more details on how to specify this property.
	 */
	public $breadcrumbs 	= array();
	public $easemobToken = 'https://a1.easemob.com/1108170901115546/esquelcarpool/token'; //环信登入接口
	protected $captchaAction = '/carpool/publics/captcha';
	protected $zh2Hant;
	protected $logAction = true;
	protected $minIEVersion = '8.0';
	private $_IEVersion;

	private $_description;
	private $_keywords;
	private $_title;

	private $_weiboShareDefaultText;
	private $_weiboSharePic;
	private $_wechatApplication;

	public $topbarTitle ;
	public $goBackUrl ;

	public $isGuest = true;


/*
	public function filters() {
		return array(
			'accessControl',
		);
	}

	public function accessRules() {
		return array(
			array(
				'allow',
				'users'=>array('@'),
			),
			array(
				'deny',
				// 'allow',
				'users'=>array('*'),
			),
		);
	}*/

	public function init() {
		parent::init();

		$unCheckControllers  = array('publics'); //不用进行用户验证的控制器
		$unCheckActions  = array('carpool/nim/test',"carpool/im_group/get_invitation"); //不用进行用户验证的路由地址，

		$ctr = Yii::app()->controller->id;
		$pathInfo = Yii::app()->request->getPathInfo();
		// var_dump(Yii::app()->request->queryString);
		$r = $this->sGet('r');

		$ac = strrpos($pathInfo,"carpool/")!==false ? $pathInfo : $r  ;
		// var_dump($pathInfo);exit;
		if(!in_array($ctr,$unCheckControllers) && !in_array($ac,$unCheckActions)){
			if($this->checkLogin()){
				$this->isGuest = false;
			}else{
				$this->isGuest = true;
			}
		}

		/*if(isset($_REQUEST['lang']) && $_REQUEST['lang'] != '') {
			$this->setLanguage($_REQUEST['lang'], true);
		} else if(isset($_COOKIE['language']) && $_COOKIE['language'] != '') {
			$this->setLanguage($_COOKIE['language']);
		} else if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
			$languages = Yii::app()->params->languages;
			$acceptLanguage = strtolower(str_replace('-', '_', $_SERVER['HTTP_ACCEPT_LANGUAGE']));
			$pos = strlen($acceptLanguage);
			$userLanguage = false;
			foreach ($languages as $language) {
				$temp = strpos($acceptLanguage, $language);
				if ($temp !== false && $temp < $pos) {
					$pos = $temp;
					$userLanguage = $language;
				}
			}
			if ($userLanguage !== false) {
				$this->setLanguage($userLanguage);
			}
		}*/

	}



	protected function beforeAction($action) {


		$userAgent = Yii::app()->request->getUserAgent();
		if (preg_match('{MSIE ([\d.]+)}', $userAgent, $matches) && version_compare($this->_IEVersion = $matches[1], $this->minIEVersion, '<')
			&& !($this->id == 'site' && $action->id == 'page' && $this->sGet('view') == 'please-update-your-browser')
		) {
			// $this->redirect(array('/site/page', 'view'=>'please-update-your-browser'));
		}
		if ($this->logAction) {
			$params = array(
				'get'=>$_GET,
				'post'=>$_POST,
				'cookie'=>$_COOKIE,
				'session'=>$_SESSION,
				'server'=>$_SERVER,
			);
			Yii::log(json_encode($params), 'test', $this->id . '.' . $action->id);
		}

		return parent::beforeAction($action);
	}

/*
	//使用curl方问环信登入接口
	public function loginWebim(){
			$url = $this->easemobToken;
			$userInfo = $this->userBaseInfo;
			$datas  = array(
				'timestamp' => time().'000',
				'grant_type' => "password",
				'username'  => strtolower($userInfo->loginname),
				'password' => 'carpool666666',
			);
			// $rs = Yii::app()->curl->post($url, $datas);
			$rs = $this->curl_request($url,$datas);
			if($rs){
				$datas = json_decode($rs,true);
				if(isset($datas['access_token']) && !empty($datas['access_token']) ){
					setcookie('web_im_'.strtolower($userInfo->loginname), $datas['access_token']);
				}
				return $datas;
			}else{
				return false;
			}
			return $rs;
	}*/

	protected function getCaptchaAction() {
		if(($captcha = Yii::app()->getController()->createAction($this->captchaAction)) === null) {
			if(strpos($this->captchaAction,'/') !== false) {
				if(($ca = Yii::app()->createController($this->captchaAction)) !== null) {
					list($controller,$actionID) = $ca;
					$captcha = $controller->createAction($actionID);
				}
			}
			if($captcha === null) {
				throw new CException(Yii::t('yii','CCaptchaValidator.action "{id}" is invalid. Unable to find such an action in the current controller.',
						array('{id}'=>$this->captchaAction)));
			}
		}
		return $captcha;
	}








}
