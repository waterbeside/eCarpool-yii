<?php

/*服务控制器*/

class ServiceController extends BaseController {



  //sms_obj
  protected $SMS_OBJ ;


  // usage 与对应的模板id
  protected $SmsTemplate  = array(
    // 'u_000'   => '4022147',
    'u_100' => '4072148', //用于登录
    'u_101' => '4022223', //用于注册
    'u_102' => '4022224', //用于重置
    'u_103' => '3892309', //用于绑定手机号
    'u_200' => '4022147', //用于确认通用操作
    'u_201' => '4022147', //用于确认支付

    'u_300' => '4012242', //用于邀请下载
    'u_301' => '4012242', //用于邀请建立好友关系
    'u_302' => '4012426', //用于邀请进群
  );

  public function init() {
    parent::init();
  }

  public function requireSms(){
    if(!$this->SMS_OBJ){
      require(Yii::app()->basePath.'/extensions/SMS/NimSms.php');
      $nimSmsSetting = Yii::app()->params['nimSetting'];
      // $S_SMS = Yii::app()->SMS;
      $this->SMS_OBJ = new NimSms($nimSmsSetting['appKey'],$nimSmsSetting['appSecret'],'fsockopen');     //fsockopen伪造请求
    }
    return $this->SMS_OBJ;
  }

  /**
   * action for 发送短信验证码
   * @return [type] [description]
   */
	public function actionSend_code(){
    $dev   = $this->iRequest('dev');

    // $dev   = 0;
    $phones = isset($_REQUEST['phone']) ? $_REQUEST['phone'] : ''; //取得手机号
    $usage = $this->iRequest('usage'); //取得应用场境
    /*var_dump($usage);
    var_dump($phones);
    exit;*/
    if(!$usage){
      $this->ajaxReturn(-10001,[],'usage empty');
      exit;
    }

    if(!isset($this->SmsTemplate['u_'.$usage])){
      $this->ajaxReturn(-10002,[],'usage error');
      exit;
    }
    /*
    解释手机号
    当手机号为字符串时，处理为数组.多个手机号以','格开。
    例如 phone=[13112345678,13212345678] 或 phone=13112345678,13212345678 或 phone=13112345678有形式皆可
     */
    if(is_string($phones)){
      if(trim($phones)==''){
        $this->ajaxReturn(-10001,[],'phone empty');
        exit;
      }
      if(strpos($phones,'[')!==false){
        $phones = preg_replace('/\[||\]/i','',$phones);
      }
      $phones = explode(',',$phones);
    }

    if(is_array($phones)){
      $phones = $this->arrayUniq(array_filter($this->trimArray($phones))); //去电话号除空制和重复值。
      // var_dump($phones);
    }else{
      $this->ajaxReturn(-10002,[],'phone error');
      exit;
    }
    if(!$phones || trim($phones[0])==''){
      $this->ajaxReturn(-10001,[],'phone empty');
      exit;
    }
    // var_dump($phones);
    $sendCallBack = array();
    $isSuccess = 0;
    if(in_array($usage,array(100,101,102,103,201,200))){
      $userDataHas = CP_User::model()->findByAttributes(array(
        'phone'=>$phones[0]
      ));
      if(in_array($usage,array(100,102))){ //登入和重置 验证手机号是否存在。
        if(!$userDataHas){
          $this->ajaxReturn(10002,[],'用户不存在');
        }
      }

      if(in_array($usage,array(103,200,201))){ // 验证是否登入
        $this->checklogin();
        $userInfo = $this->getUser();
      }

      switch ($usage) {
        case 101:
          if($userDataHas){ //注册 验证手机号是否存在。
            $this->ajaxReturn(10006,[],'用户已存在');
          }
          break;
        case 103: //重绑定
          if($userInfo->phone == $phones[0]){
            $this->ajaxReturn(10100,[],'请输入新的手机号');
          }
          if($userDataHas && $userDataHas->phone == $phones[0]){
            $this->ajaxReturn(10006,[],'该手机号已绑定其它帐号');
          }
          break;

        default:
          # code...
          break;
      }
      foreach ($phones as $key=>$phone) {
        $sendCallBack[$phone] = $this->sendCode($phone,$usage,6,900,$dev);
        if($sendCallBack[''.$phone]['code'] == 200){
          $isSuccess = 1;
        }
        break;
      }
      if($isSuccess){
        if(count($phones)==1){
          $this->ajaxReturn(0,[],'success');
        }else{
          $this->ajaxReturn(0,$sendCallBack,'success');
        }
      }else{
        if($sendCallBack[''.$phone]['code'] == 10200){
          $this->ajaxReturn(10200,$sendCallBack,'too often');
        }
        if($sendCallBack[''.$phone]['code'] == 414){
          $this->ajaxReturn(-10002,$sendCallBack,'bad format');
        }
        $this->ajaxReturn(-1,$sendCallBack,'fail');

      }
    }else if(in_array($usage,array(300,301,302))){
      $this->checklogin();
      $sendCallBack = $this->sendTemplate($phones,$usage);

      if($sendCallBack['code'] == 200){
        $this->ajaxReturn(0,$sendCallBack,'success');
      }else{
        $this->ajaxReturn(-1,$sendCallBack,'fail');
      }
    }

	}

  /**
   * 验证手机短信和验证码。
   */
  public function actionCheck_code(){
    $phone = $this->sRequest('phone');
    $usage = $this->iRequest('usage');
    $code = $this->sRequest('code');
    $step = $this->sRequest('step');
    $SMS = $this->requireSms();

    if($SMS->checkSMSCode($phone,$code,$usage)){
      $returnData =  [];
      switch ($usage) {
        case 100: //当为100时，为又验证登入
          $model = new CarpoolLoginForm();
          $formData = array(
            'client' => $this->sRequest('client'),
          );
          if(!in_array($formData['client'],array('ios','android','h5','web','third'))){
            return  $this->ajaxReturn(-10002,[],'client error');
          };

          // collect user input data
          $model->attributes = $formData;
          $isAllData = in_array($formData['client'],array('ios','android')) ? 1 : 0 ;
          $data = $model->loginByPhone($phone,$isAllData);

          if (isset($data['code']) && $data['code'] > 0) {
            $this->ajaxReturn($data['code'],[],$data['desc']);
          }else if(isset($data['user'])){
            if(!$step){
              $SMS->unCacheSMSCode($phone,$code,$usage);
            }
            $this->ajaxReturn(0,$data,'success');
          }else{
            $this->ajaxReturn(-1,[],'fail');
          }
          break;
        case 103: //用于重新绑定手机号
          $this->checklogin();
          $uid = $this->userBaseInfo->uid;
          $userDataHas = CP_User::model()->findByAttributes(array(
            'phone'=>$phone
          ));
          if($userDataHas){
            $this->ajaxReturn(10006,[],'该手机号已被使用');
          }
          $update_count = CP_User::model()->updateByPk($uid,array('phone'=>$phone)); //绑定新号码
          if ($update_count!==false) {
            $this->ajaxReturn(0,[],'success');
          }else{
            $this->ajaxReturn(-1,[],'checkcode successful but update unsuccessful');
          }
          break;

        default:
          # code...
          break;
      }

      if(!$step){
        $SMS->unCacheSMSCode($phone,$code,$usage);
      }
      $this->ajaxReturn(0,$returnData,'success');
      exit;
    }else{
      $this->ajaxReturn(-1,[],'fail (code error)');
      exit;
    }
  }


  /**
   * 发送手机短信验证码
   * @param  string  $phone      电话
   * @param  integer  $usage      场境
   * @param  integer $codeLen    验证码长度
   * @param  integer  $expiration 缓存时间 默认15分钟。
   * @return [json]              []
   */
  public function sendCode($phone,$usage,$codeLen=6,$expiration=900,$dev=0){
    $templates = $this->SmsTemplate;
    $templateid =   $templates['u_'.$usage] ; //短信验证码的模板ID
    $cacheKey = "SMSCODE_".$usage."_".$phone; //用于缓存验证码的key

    $cacheData_o = Yii::app()->cache->get($cacheKey);
    if($cacheData_o && time() - $cacheData_o['time'] < 52 && !$dev){  //1分钟内不准再发。
      return array('code'=>10200,'desc'=>'too often');
    }
    $SMS = $this->requireSms();
    // var_dump($SMS);
    $phone = preg_replace('# #','',$phone);

    if($dev){
      $sendRes  = array( //test
        'code'  => 200,
        'msg'   => '',
        'obj'   => 561111
      );
    }else{
      $sendRes = $SMS->sendSmsCode($templateid,$phone,'',$codeLen);  //调用接口发送验证码
    }
    /**/
    if(isset($sendRes['obj'])){
      $cacheData = array('obj'=>$sendRes['obj'],'time'=>time());
      Yii::app()->cache->set($cacheKey, $cacheData ,$expiration);
      unset($sendRes['obj']);
    }
    return  $sendRes;
  }

  /**
   * 验证模版短信
   * @param  [type] $phone [description]
   * @param  [type] $code  [description]
   * @param  [type] $usage [description]
   * @return [type]        [description]
   */
  public function sendTemplate($phone=array(),$usage ){
    $templates = $this->SmsTemplate;
    $templateid =   $templates['u_'.$usage] ; //短信验证码的模板ID
    $SMS = $this->requireSms();


    $userInfo = $this->getUser();

    switch ($usage) {
      case 300:
        $params = [$userInfo->name];
        break;
      case 302:
        $param  = $this->sRequest('param');
        $link_code  = $this->sRequest('link_code');
        if(!$param){
          $this->ajaxReturn(-10001,[],'param empty');
        }
        $params = [$userInfo->name,$link_code];
        break;

      default:
        # code...
        break;
    }

    foreach ($phone as $key => $value) {
      $phone[$key] = preg_replace('# #','',$value);
    }
    $sendRes = $SMS->sendSMSTemplate($templateid,$phone,$params);  //调用接口发送验证码


    /*$sendRes  = array( //test
      'code'  => 200,
      'msg'   => 'sendid',
      'obj'   => 101
    );*/
    return  $sendRes;
  }








}
