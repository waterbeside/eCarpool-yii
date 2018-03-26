<?php

/**
 * LoginForm class.
 * LoginForm is the data structure for keeping
 * user login form data. It is used by the 'login' action of 'SiteController'.
 */
class CarpoolLoginForm extends CFormModel {
	public $username;
	public $password;
	public $client;
	private $_identity;

	/**
	 * Declares the validation rules.
	 * The rules state that email and password are required,
	 * and password needs to be authenticated.
	 */
	public function rules() {
		return array(
			// email and password are required
			array('username, password', 'required'),
			// password needs to be authenticated
			// array('password', 'authenticate'),
			array('client', 'safe'),

		);
	}



	/**
	 * Declares attribute labels.
	 */
	public function attributeLabels() {
		return array(
			'username'=>Yii::t('carpool', 'User name'),
			'password'=>Yii::t('carpool', 'Password'),
			'client'=>Yii::t('carpool', 'Client'),
		);
	}


	/**
	 * Authenticates the password.
	 * This is the 'authenticate' validator as declared in rules().
	 */
	public function authenticate($attribute,$params) {
		if(!$this->hasErrors()) {
			$this->_identity = new CarpoolUserIdentity($this->username, $this->password);
			if(!$this->_identity->authenticate()) {
				$this->addError('password', '用户名或密码错误');
			}
		}
	}



	/**
	 * Logs in the user using the given email and password in the model.
	 * @return boolean whether login is successful
	 */
	public function login() {

		// if($this->_identity === null) {
		// 	$this->_identity = new CarpoolUserIdentity($this->username,$this->password);
		// 	$this->_identity->authenticate();
		// }
		$userDataObj = CP_User::model()->findByAttributes(array('loginname'=>$this->username));
		if(!$userDataObj){
			$userDataObj = CP_User::model()->findByAttributes(array('phone'=>$this->username));
			if(!$userDataObj){
				$this->addError('password', '用户名或密码错误');
				return false;
			}
		}

		if($userDataObj->md5password != $this->hashPassword($this->password)){
				$this->addError('password', '用户名或密码错误');
				// var_dump($this->getError('password'));
			 return false;
		}

		// if($this->_identity->errorCode === CarpoolUserIdentity::ERROR_NONE) {
			/*$duration = 3600 * 24 ; // 30 days
			Yii::app()->user->login($this->_identity, $duration);*/

			// $uid = $this->_identity->getId();
			// $userDataObj = CP_User::model()->findByPk($uid);
			// if(!$userDataObj){
			// 	return false;
			// }
			$userData = json_decode(CJSON::encode($userDataObj),true);

			$isAllUserData = in_array($this->client,['ios','android']) ? 1 : 0;
			$data = $this->returnLoginData($userData,$isAllUserData);

			return $data;
		/*} else {
			return false;
		}*/
	}


	public function loginByPhone($phone,$isAllUserData = 0){
		$userDataObj = CP_User::model()->findByAttributes(array(
			'phone'=>$phone
		));
		if(!$userDataObj){
			return array('code'=>10002,'desc'=>'user does not exist');
		}
		$userData = json_decode(CJSON::encode($userDataObj),true);
		if(!$userData['is_active']){
			return array('code'=>10003,'desc'=>'user does not active');
		}


		$data = $this->returnLoginData($userData,$isAllUserData);


		return $data;

	}

	public function returnLoginData($userData,$isAllUserData = 0 ){
			$temp_array = array(
				'iss'=>'carpool',
				'uid'=> $userData['uid'],
				'loginname' => $userData['loginname'],
				'name'=> $userData['name'],
				'company_id'=>$userData['company_id'],
				'avatar' => $userData['imgpath'],
				'client' => $this->client,
			);


			$jwt = self::createJwt($temp_array);

			$data = array(
				'user' => array(
					'uid'=> $userData['uid'],
					'loginname' => $userData['loginname'],
					'name'=> $userData['name'],
					'company_id'=>$userData['company_id'],
					'avatar' => $userData['imgpath'],
				),
				'token'	=> $jwt
			);
			if($isAllUserData){
				$data['user'] = $userData;
				if(isset($data['user']['md5password'])){
					$data['user']['md5password'] = '';
				}
				if(isset($data['user']['passwd'])){
					$data['user']['passwd'] = '';
				}
			}
			return $data;

	}



	//设置jwt
	public function createJwt($data,$setCookie = 1){
		$exp = in_array($this->client,['ios','android']) ? (time() + 36* 30 * 86400) : (time() + 30 * 86400);
		$exp = isset($data['exp']) ? $data['exp'] : $exp;
		$jwtData  = array(
			'exp'=> $exp, //过期时间
			'iat'=> time(), //发行时间
			'iss'=> $data['iss'], //发行者，值为固定carpool
			'uid'=> $data['uid'],
			'loginname' => $data['loginname'],
			'name'=> $data['name'],
			'company_id'=>$data['company_id'],
			// 'avatar' => $data['avatar'],
			'client' => $data['client'], //客户端
		);
		$jwt = Yii::app()->JWT->encode($jwtData);

		// headers: {'Authorization': 'Basic '+authKey},
		if($setCookie){setcookie('CP_U_TOKEN', $jwt, $exp , '/');}
		return $jwt;
	}

	public function hashPassword($password){
		return md5($password);
	}


}
