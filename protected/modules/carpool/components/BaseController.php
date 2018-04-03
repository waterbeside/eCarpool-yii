<?php

// use EasyWeChat\Foundation\Application;

/**
 * Controller is the customized base controller class.
 * All controller classes for this application should extend from this base class.
 */
class BaseController extends CController {
	public $userBaseInfo;
	public $userJwt;
	private $_user;

	public function init() {

		parent::init();
		header('Access-Control-Allow-Headers: authorization,x-requested-with,content-type,Content-Length');
		header('Access-Control-Allow-Origin: *');
		if($_SERVER['REQUEST_METHOD']=='OPTIONS'){
			exit;
		}
	}

	/**
	 * （JWT）方式验证是否登入
	 * @return [type] [description]
	 */
	public function checklogin(){

		//取得jwt
		$Authorization = isset($_SERVER['HTTP_AUTHORIZATION'])?$_SERVER['HTTP_AUTHORIZATION']:'';
		$temp_array = explode('Bearer ',$Authorization);
		$Authorization = count($temp_array)>1 ? $temp_array[1] : '';
		$Authorization = $Authorization ? $Authorization : (isset($_COOKIE['CP_U_TOKEN'])?$_COOKIE['CP_U_TOKEN']:'');
		$Authorization = $Authorization ? $Authorization : $this->sRequest('user_token');
		$loginUrl = $this->createUrl('/carpool/publics/login');
		if(!$Authorization){
			return $this->ajaxReturn(10004,array(),'您尚未登入');
			/*if(Yii::app()->getRequest()->isAjaxRequest ){
				return $this->ajaxReturn(10004,array(),'您尚未登入');
				// return $this->error('您尚未登入',$loginUrl,array('code'=>-1));
			}else{
				$this->redirect($loginUrl);
				exit;
			}*/
		}else{
			$jwtDecode = Yii::app()->JWT->decode($Authorization);
			$this->userJwt = $jwtDecode;
			if(isset($jwtDecode->iss) && isset($jwtDecode->loginname) && isset($jwtDecode->uid)){
				$now = time();
				if( $now  > $jwtDecode->exp){
					$this->ajaxReturn(10004,array(),'登入超时，请重新登入');
					/*if(Yii::app()->getRequest()->isAjaxRequest){
						$this->ajaxReturn(10004,array(),'登入超时，请重新登入');
						// return $this->error('登入超时，请重新登入',$loginUrl,array('code'=>-1));
					}else{
						$this->redirect($loginUrl);
						exit;
					}*/

				}

				$this->userBaseInfo  = (object)array(
					'loginname' => $jwtDecode->loginname,
					// 'iss' => $jwtDecode->iss,
					'uid' => $jwtDecode->uid,
					"name" => $jwtDecode->name,
					"company_id" => $jwtDecode->company_id,
					"client" => $jwtDecode->client,
					// "avatar" => $jwtDecode->avatar
				);
				if(!isset($_COOKIE['CP_U_TOKEN']) || $_COOKIE['CP_U_TOKEN']=='' ){
					setcookie('CP_U_TOKEN', $Authorization, $jwtDecode->exp , '/');
				}

				return true;
			}else{
				return $this->ajaxReturn(10004,array(),'您尚未登入');
				/*if(Yii::app()->getRequest()->isAjaxRequest){
					$this->ajaxReturn(10004,array(),'您尚未登入');
					// return $this->error('您尚未登入',$loginUrl,array('code'=>-1));
				}else{
					$this->redirect($loginUrl);
					exit;
				}*/
			}
		}
	}

	public function getUser() {
		if ($this->_user !== null) {
			return $this->_user;
		}
		$userData =  CP_User::model()->findByPk($this->userBaseInfo->uid);
		if($userData){
			$this->_user = $userData;
			return $userData;
		}else{
			return $this->ajaxReturn(10004,array(),'请重新登入');
		}

	}



  public function generate_code($length = 4) {
    return rand(pow(10,($length-1)), pow(10,$length)-1);
  }

	/**
	 * 产生随机字符串
	 * 产生一个指定长度的随机字符串,并返回给用户
	 * @access public
	 * @param int $len 产生字符串的位数
	 * @return string
	 */
	public function genRandomString($len = 6) {
	    $chars = array(
	        "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k",
	        "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v",
	        "w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G",
	        "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R",
	        "S", "T", "U", "V", "W", "X", "Y", "Z", "0", "1", "2",
	        "3", "4", "5", "6", "7", "8", "9"
	    );
	    $charsLen = count($chars) - 1;
	    shuffle($chars);    // 将数组打乱
	    $output = "";
	    for ($i = 0; $i < $len; $i++) {
	        $output .= $chars[mt_rand(0, $charsLen)];
	    }
	    return $output;
	}


	public function setIsAjaxRequest($isAjaxRequest = true) {
		if ($isAjaxRequest) {
			$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
		} else {
			unset($_SERVER['HTTP_X_REQUESTED_WITH']);
		}
	}


	public function getCacheKey() {
		$args = func_get_args();
		array_unshift($args, $this->action->id);
		array_unshift($args, $this->id);
		return implode('_', $args);
	}

	public function getAttributeName($name = 'name') {
		if (Yii::app()->language{0} == 'z' && Yii::app()->language{1} == 'h') {
			$name .= '_zh';
		}
		return $name;
	}

	/**
	 * 设置来源
	 * @param [type] $section  [description]
	 * @param [type] $referrer [description]
	 */
	public function setReferrer($section = null, $referrer = null) {
		if ($referrer === null) {
			$referrer = Yii::app()->request->getUrlReferrer();
		}
		$referrer = CHtml::normalizeUrl($referrer);
		if ($section === null) {
			$section = md5(serialize(array(
				$this->id,
				$this->action->id,
				$_GET,
			)));
		}
		if (!isset($_SESSION['referrer'][$section])) {
			$_SESSION['referrer'][$section] = $referrer;
		}
	}


	//取得来源页
	public function getReferrer($section = null, $destroy = true) {
		if ($section === null) {
			$section = md5(serialize(array(
				$this->id,
				$this->action->id,
				$_GET,
			)));
		}
		$referrer = isset($_SESSION['referrer'][$section]) ? $_SESSION['referrer'][$section] : Yii::app()->request->getUrlReferrer();
		if ($destroy) {
			unset($_SESSION['referrer'][$section]);
		}
		return $referrer;
	}





	public function setLanguage($language, $setCookie = false) {
		if (!in_array($language, Yii::app()->params->languages)) {
			return;
		}
		Yii::app()->language = $language;
		if ($setCookie) {
			$_COOKIE['language'] = $language;
			setcookie('language', $language, time() + 365 * 86400, '/', DEV ? null : '');
		}
	}

	public function getIsCN() {
		return Yii::app()->language == 'zh_cn' || Yii::app()->language == 'zh_tw';
	}

	public function getIEClass() {
		if ($this->_IEVersion !== null) {
				return 'ie' . intval($this->_IEVersion);
		}
		return '';
	}

	public function getLangUrl($lang = 'zh_cn') {
		$params = $_GET;
		$params['lang'] = $lang;
		return $this->createUrl('/'.$this->route, $params);
	}

	public function translateTWInNeed($data) {
		if (Yii::app()->language !== 'zh_tw') {
			return $data;
		}
		if ($this->zh2Hant === null) {
			include APP_PATH . '/protected/data/ZhConversion.php';
			$this->zh2Hant = $zh2Hant;
		}
		if (is_string($data)) {
			return strtr($data, $this->zh2Hant);
		} elseif (is_array($data)) {
			$data = var_export($data, true);
			$data = strtr($data, $this->zh2Hant);
			$data = eval('return ' . $data . ';');
			return $data;
		}
	}


	public function setDescription($description) {
		$description = strip_tags($description);
		$description = preg_replace('{[\r\n]+}', ' ', $description);
		$this->_description = $description;
	}

	public function getDescription() {
		if ($this->_description === null) {
			$this->_description = Yii::t('common', Yii::app()->params->description);
		}
		return $this->_description;
	}

	public function setKeywords($keywords) {
		if (is_array($keywords)) {
			$keywords = implode(',', array_map(function($keyword) {
				return Yii::t('common', $keyword);
			}, $keywords));
		}
		$this->_keywords = $keywords;
	}

	public function getKeywords() {
		if ($this->_keywords === null) {
			$this->setKeywords(Yii::app()->params->keywords);
		}
		return $this->_keywords;
	}

	public function appendKeywords($keywords) {
		$oldKeywords = explode(',', $this->getKeywords());
		if (!is_array($keywords)) {
			$keywords = array($keywords);
		}
		foreach ($keywords as $keyword) {
			$oldKeywords[] = $keyword;
		}
		$this->setKeywords($oldKeywords);
	}

	public function setTitle($title) {
		$this->_title = Yii::t('common', $title);
	}

	public function getTitle() {
		// if ($this->_title === null) {
		// 	$this->_title = Yii::t('common', Yii::app()->name);
		// }
		return $this->_title;
	}

	public function setPageTitle($pageTitle) {
		if (is_string($pageTitle)) {
			return parent::setPageTitle(Yii::t('common', $pageTitle));
		} elseif (is_array($pageTitle)) {
			$pageTitle[] = Yii::t('common', Yii::app()->name);
			return parent::setPageTitle(implode(' - ', array_map(function($s) {
				return Yii::t('common', strip_tags($s));
			}, $pageTitle)));
		}
	}

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


	public function ajaxReturn($code, $data, $message = '',$extra = array()) {
		// header('content-type:application:json;charset=utf8');
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Headers:*');
		if($_SERVER['REQUEST_METHOD']=='OPTIONS'){
			exit;
		}
		// header('Access-Control-Allow-Methods:*');
		// header('Access-Control-Allow-Headers:x-requested-with,content-type');
		// $status = isset($data['code'])? $data['code'] : $status;
		$data = array(
			'code'=>$code,
			'desc'=>$message,
			'data'=>$data,
			'date'=>date("Y-m-d H:i:s",time()),
			'extra'=>$extra
		);
		// echo CJSON::encode($data);
		// Yii::app()->end();
		echo json_encode($data);
		exit;

	}

	public function ajaxOK($msg = '',$data = array()) {
		return $this->ajaxReturn(0, $data,$msg );
	}

	public function ajaxError( $msg = null, $data = array(), $code = -1) {
		if ($msg === null) {
			$msg = Constant::getAjaxMessage($code);
		}
		return $this->ajaxReturn($code, $data, $msg);
	}

	/**
	 * Returns the named GET parameter value.
	 * If the GET parameter does not exist, the second parameter to this method will be returned.
	 * @param string $name the GET parameter name
	 * @param int $defaultValue the default parameter value if the GET parameter does not exist.
	 * @return int the intvaled GET parameter value
	 */
	public function iGet($name, $defaultValue = 0) {
		return isset($_GET[$name]) ? intval($_GET[$name]) : $defaultValue;
	}

	/**
	 * Returns the named GET parameter value.
	 * If the GET parameter does not exist, the second parameter to this method will be returned.
	 * @param string $name the GET parameter name
	 * @param string $defaultValue the default parameter value if the GET parameter does not exist.
	 * @return string the strvaled GET parameter value
	 */
	public function sGet($name, $defaultValue = '') {
		return isset($_GET[$name]) ? trim(strval($_GET[$name])) : $defaultValue;
	}

	/**
	 * Returns the named GET parameter value.
	 * If the GET parameter does not exist, the second parameter to this method will be returned.
	 * @param string $name the GET parameter name
	 * @param array $defaultValue the default parameter value if the GET parameter does not exist.
	 * @return array the strvaled GET parameter value
	 */
	public function aGet($name, $defaultValue = array()) {
		return isset($_GET[$name]) ? (array)$_GET[$name] : $defaultValue;
	}

	/**
	 * Returns the named POST parameter value.
	 * If the POST parameter does not exist, the second parameter to this method will be returned.
	 * @param string $name the GET parameter name
	 * @param int $defaultValue the default parameter value if the POST parameter does not exist.
	 * @return int the intvaled POST parameter value
	 */
	public function iPost($name, $defaultValue = 0) {
		return isset($_POST[$name]) ? intval($_POST[$name]) : $defaultValue;
	}

	/**
	 * Returns the named POST parameter value.
	 * If the POST parameter does not exist, the second parameter to this method will be returned.
	 * @param string $name the POST parameter name
	 * @param string $defaultValue the default parameter value if the POST parameter does not exist.
	 * @return string the strvaled POST parameter value
	 */
	public function sPost($name, $defaultValue = '') {
		return isset($_POST[$name]) ? trim(strval($_POST[$name])) : $defaultValue;
	}

	/**
	 * Returns the named POST parameter value.
	 * If the POST parameter does not exist, the second parameter to this method will be returned.
	 * @param string $name the POST parameter name
	 * @param array $defaultValue the default parameter value if the POST parameter does not exist.
	 * @return array the strvaled POST parameter value
	 */
	public function aPost($name, $defaultValue = array()) {
		return isset($_POST[$name]) ? (array)$_POST[$name] : $defaultValue;
	}

	/**
	 * Returns the named REQUEST parameter value.
	 * If the POST parameter does not exist, the second parameter to this method will be returned.
	 * @param string $name the REQUEST parameter name
	 * @param int $defaultValue the default parameter value if the REQUEST parameter does not exist.
	 * @return int the intvaled REQUEST parameter value
	 */
	public function iRequest($name, $defaultValue = 0) {
		return isset($_REQUEST[$name]) ? intval($_REQUEST[$name]) : $defaultValue;
	}

	/**
	 * Returns the named REQUEST parameter value.
	 * If the POST parameter does not exist, the second parameter to this method will be returned.
	 * @param string $name the REQUEST parameter name
	 * @param string $defaultValue the default parameter value if the REQUEST parameter does not exist.
	 * @return string the strvaled REQUEST parameter value
	 */
	public function sRequest($name, $defaultValue = '') {
		return isset($_REQUEST[$name]) ? trim(strval($_REQUEST[$name])) : $defaultValue;
	}

	/**
	 * Returns the named REQUEST parameter value.
	 * If the REQUEST parameter does not exist, the second parameter to this method will be returned.
	 * @param string $name the REQUEST parameter name
	 * @param array $defaultValue the default parameter value if the REQUEST parameter does not exist.
	 * @return array the strvaled REQUEST parameter value
	 */
	public function aRequest($name, $defaultValue = array()) {
		return isset($_REQUEST[$name]) ? (array)$_REQUEST[$name] : $defaultValue;
	}

	/**
     * 通用成功跳转
     * @param string $msg 提示信息
     * @param string $url 成功后跳转的URL
     * @param number $sec 自动跳转秒数
     * @return Ambigous <string, string>
     */
    public function success($msg='',$url= '',$sec = 3){
			if(Yii::app()->getRequest()->isAjaxRequest){
				$sec = is_array($sec) ? $sec : array();
				return $this->ajaxOk($msg,$sec);
			}else{
				$sec = is_array($sec) ? 3 :$sec;
				$this->renderPartial('/base/msg',['success'=>1,'msg'=>$msg,'gotoUrl'=>$url,'sec'=>$sec]);
			}
			exit();
    }

    /**
     * 通用错误跳转
     * @param string $msg 提示信息
     * @param string $url 成功后跳转的URL
     * @param number $sec
     * @return Ambigous <string, string>
     */
    public function error($msg='',$url= '' ,$sec = 3){
			if(Yii::app()->getRequest()->isAjaxRequest){
				$sec = is_array($sec) ? $sec : array();
				return $this->ajaxError($msg,$sec);
			}else{
				$sec = is_array($sec) ? 3 :$sec;
        $this->renderPartial('/base/msg',['success'=>0,'msg'=>$msg,'gotoUrl'=>$url,'sec'=>$sec]);
				exit();
			}
    }

		/**
	   * 数组去重
	   */
	  public function arrayUniq($arr){
	    $arr = array_unique($arr);
	    $arr = array_values($arr);
	    return $arr;
	  }

		/**
	   * 二维数组去重
	   */
		 function arrayUniqByKey($arr,$key){
         //建立一个目标数组
         $res = array();
         foreach ($arr as $value) {
            //查看有没有重复项
            if(isset($res[$value[$key]])){
                  //有：销毁
                  unset($value[$key]);
            }
            else{
                 $res[$value[$key]] = $value;
            }
         }
         return $res;
     }

		/**
	   * 清除数组内每个元素的两头空格
	   * @return array||string
	   */
		function trimArray($arr){
	    if (!is_array($arr)){
				  return trim($arr);
			}
    	return array_map("BaseController::trimArray", $arr);
		}

		/**
 * in_array is too slow when array is large
 */
public static function inArray($item, $array) {
    $str = implode(',', $array);
    $str = ',' . $str . ',';
    $item = ',' . $item . ',';
    return false !== strpos($item, $str) ? true : false;
}

/**
 * 抓取远程数据
 * @param  string  $url          地址
 * @param  string  $post         请求数据
 * @param  string  $cookie       提交的cookie
 * @param  integer $returnCookie [description]
 * 参数1：访问的URL，参数2：post数据(不填则为GET)，参数3：提交的$cookies,参数4：是否返回$cookies
 */
		function curl_request($url,$post='',$cookie='', $returnCookie=0){
        $curl = curl_init();
				curl_setopt_array(
	        $curl,
	        array(
            CURLOPT_URL => $url,
            CURLOPT_REFERER => $url,
            CURLOPT_AUTOREFERER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_CONNECTTIMEOUT => 1,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safari/537.36'
	        )
				);


				$data_string = json_encode($post);
         if($post) {
             curl_setopt($curl, CURLOPT_POST, 1);
            //  curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
         }

         if($cookie) {
             curl_setopt($curl, CURLOPT_COOKIE, $cookie);
         }
         curl_setopt($curl, CURLOPT_HEADER, $returnCookie);
         curl_setopt($curl, CURLOPT_TIMEOUT, 10);
         curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

				 curl_setopt($curl, CURLOPT_HTTPHEADER, array(
					    'Content-Type: application/json',
					    'Content-Length: ' . strlen($data_string))
					);
					// var_dump($curl);
         $data = curl_exec($curl);
         if (curl_errno($curl)) {
             return curl_error($curl);
         }
         curl_close($curl);
         if($returnCookie){
             list($header, $body) = explode("\r\n\r\n", $data, 2);
             preg_match_all("/Set\-Cookie:([^;]*);/", $header, $matches);
             $info['cookie']  = substr($matches[1][0], 1);
             $info['content'] = $body;
             return $info;
         }else{
             return $data;
         }
 		 }


}
