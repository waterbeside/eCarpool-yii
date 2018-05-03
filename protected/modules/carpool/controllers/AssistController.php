<?php

/*本控制器免登入操作*/

class AssistController extends BaseController {


  protected $SMS_OBJ ;
  protected $NIM_OBJ ;

  public function actionInvite_register(){
    $this->renderPartial('invite_register');
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


  public function requireNim(){
    if(!$this->NIM_OBJ){
      require(Yii::app()->basePath.'/extensions/NIM/Nim.php');
      $nimSetting = Yii::app()->params['nimSetting'];
      $this->NIM_OBJ = new Nim($nimSetting['appKey'],$nimSetting['appSecret'],'fsockopen');
    }
    return $this->NIM_OBJ ;

  }

	/**
	 * 邀请注册提交
	 */
   public function actionSignup_invitation(){
     $code                = $this->sPost('code');
     $link_code           = $this->sPost('link_code');
     $data['phone']       = $this->sPost('phone');
     // $data['loginname']   = $this->sPost('loginname');
     $data['name']        = $this->sPost('name');
     $password            = $this->sPost('password');
     $data['company_id']  = $this->iPost('company');
     $data['sex']         = $this->iPost('sex');
     $data['is_active']   = 1;
     $data['md5password'] = md5($password);


     if(!$code){
       $this->ajaxReturn(-10001,[],'Empty code');
     }
     // if(!$link_code){
     //   $this->ajaxReturn(-10001,[],'Empty link_code');
     // }
     if(!$password){
       $this->ajaxReturn(-10001,[],'请输入密码');
     }
     if(mb_strlen($password)<6){
       $this->ajaxReturn(-10002,[],'密码过短');
     }
     if(!$data['sex']){
       $this->ajaxReturn(-10001,[],'请选择性别');
     }
     if(!isset($_POST['company']) || trim($_POST['company'])==='' ){
       $this->ajaxReturn(-10001,[],'请选择公司');
     }
     /*if(!$data['loginname']){
       $this->ajaxReturn(-10001,[],'请设定帐号名');
     }*/
     if(!$data['name']){
       $this->ajaxReturn(-10001,[],'请输入姓名');
     }
     /*//验证用户名是否被注册
     $hadSignup = CP_User::model()->count(" loginname = '".$data['loginname']."' ");
     if($hadSignup>0){
       // $this->ajaxReturn(10006,[],'该用户名/工号已被注册');
     }*/
     //验证电话是否被使用
     $hadSignup = CP_User::model()->count(" phone = '".$data['phone']."' ");
     if($hadSignup>0){
       $this->ajaxReturn(-10002,[],'手机已被注册');
     }
     //证验手机注册码
     $SMS = $this->requireSms();
     if(!$SMS->checkSMSCode($data['phone'],$code,101)){
       $this->ajaxReturn(-10002,[],'手机验证码不正确');
     }


     $NIM =  $this->requireNim();

     $data['loginname'] = $data['phone'];
     // $imid       = $loginname+'_'+time() ;
     $imid       = strtolower($data['loginname']);
     $icon       =  Yii::app()->params['avatarPath'].'im/default.png' ;
     // $result     = $this->createUserFromInvitation($data,$link_code);
     // exit;

     $rs         = $NIM->createUserId($imid,$data['name'],'',$icon);
     if(!is_array($rs)){
       $this->ajaxReturn(-1,[],'create im_id fail');
     }
     if($rs['code']==414){ //创建云信帐号失败，则尝试看是否已存在此im_id，如果是，更新一次im_md5password.
       $rs_r = $NIM->updateUserToken($imid);
       if($rs_r['code']==200){
          $data['im_id'] = $imid;
          $data['im_md5password'] = $rs_r['info']['token'];
          $result = $this->createUserFromInvitation($data,$link_code,1);
          if ($result) {
            $this->ajaxReturn(0,['uid'=>$result],'success');
          }else{
            $this->ajaxReturn(-1,[],'create user fail');
          }
       }else{
         $this->ajaxReturn(-1,[],'create im_id fail');
       }
     }

     if($rs['code']==200){
       $data['im_id'] = $imid;
       $data['im_md5password'] = $rs['info']['token'];
       $result = $this->createUserFromInvitation($data,$link_code,1);
       if ($result) {
         $this->ajaxReturn(0,['uid'=>$result],'success');
       }else{
         $this->ajaxReturn(-1,[],'create user fail');
       }
     }

   }

   /**
 	 * 邀请注册提交
 	 */
    public function actionSignin_invitation(){

      $link_code           = $this->sPost('link_code');


      $model = new CarpoolLoginForm();
      $formData = array(
        'username' => $this->sPost('username'),
        'password' => $this->sPost('password'),
        'client' => 'h5',
      );

      // collect user input data
      $model->attributes = $formData;
      // validate user input and redirect to the previous page if valid
      $data = $model->login();

      if ($model->validate() && $data) {
        $NIM =  $this->requireNim();
        $uid = $data['user']['uid'];

        $userData = CP_User::model()->findByPk($uid);
        $cData = array(
          "loginname"=>$userData->loginname,
          "im_id"=>$userData->im_id,
          "name" => $userData->name,
        );
        if(!$userData->im_id || !$userData->im_md5password){
          $imid       = strtolower($userData->loginname);
          $icon       =  $userData->imgpath ? Yii::app()->params['avatarPath'].$userData->imgpath  : Yii::app()->params['avatarPath'].'im/default.png' ;
          $rs         =  $NIM->createUserId($imid,$userData->name,'',$icon);
          $cData["im_id"] = $imid;
          if($rs['code']==414){ //创建云信帐号失败，则尝试看是否已存在此im_id，如果是，更新一次im_md5password.
            $rs_r = $NIM->updateUserToken($imid);
            if($rs_r['code']==200){
               CP_User::model()->updateByPk($uid,array('im_id'=>$imid,'im_md5password'=>$rs_r['info']['token']));
               $result = $this->createUserFromInvitation($cData,$link_code,2);
               if ($result) {
                 $this->ajaxReturn(0,$data,'success');
               }else{
                 $this->ajaxReturn(-1,[],'create user fail');
               }
            }else{
              $this->ajaxReturn(-1,[],'create im_id fail');
            }
          }

          if($rs['code']==200){
            CP_User::model()->updateByPk($uid,array('im_id'=>$imid,'im_md5password'=>$rs_r['info']['token']));
            $result = $this->createUserFromInvitation($cData,$link_code,2);
            if ($result) {
              $this->ajaxReturn(0,$data,'success');
            }else{
              $this->ajaxReturn(-1,[],'create user fail');
            }
          }

        }else{
          $result = $this->createUserFromInvitation($cData,$link_code,2);
          if ($result) {
            $this->ajaxReturn(0,$data,'success');
          }else{
            $this->ajaxReturn(-1,[],'create user fail');
          }
        }

      }else{
        foreach ($model->getErrors() as $key => $value) {
          return $this->ajaxReturn(-10002,[],$value[0]);
          // return $this->error($value[0],$this->createUrl('index/index'));
        }
      }
      exit;



    }


   //从邀请连接创件用户
  public function createUserFromInvitation($datas,$link_code=false,$type=1,$doSave=0){

    if($doSave==1){
      if($type==2){
        return true;
      }
      $model = new CP_User();
      $model->attributes = $datas;
      $result = $model->save();
      if($result){
        $uid = $model->attributes['uid'];
        return $uid;
      }else{
        return false;
      }
    }

    $NIM =  $this->requireNim();

    if($link_code){ //如果存在link_code，则查出邀请连接的相关数据。
      $row  = ImGroupInvitation::model()->findByAttributes(array('link_code' => $link_code ));
    }

    if(!$link_code || !$row  || $row->expiration_time < time() || $row->status == 0 ){
      return $this->createUserFromInvitation($datas,false,$type,1);
    }else{
      $group = $row->im_group; //群id
      $groupData = $NIM->queryGroup([$group]); // 查出群信息
      if($groupData && $groupData['code']==200){ //如果存在群。
        $owner = $groupData['tinfos'][0]['owner']; //查出群主。
        $resAddGroup = $NIM->addIntoGroup($group,$owner,[$datas['im_id']],0,'请你加入','{"is_external":"1"}'); //加人入群

        // $magree='0',$msg='请您入伙',$attach="";

        $uid = $this->createUserFromInvitation($datas,false,$type,1);
        if($uid){
          if($row->type === '0' || $row->type === 0){
            ImGroupInvitation::model()->updateByPk($row->id,array('last_signup_time'=>time(),'status'=>0));
          }
        }
        return $uid;
      }else{ //如果查群失败
        ImGroupInvitation::model()->updateByPk($row->id,array('last_signup_time'=>time(),));
        $result = $this->createUserFromInvitation($datas,false,$type,1);
        return $result;
      }
    }
  }


  /**
   * 取得行程详程
   */
  public function actionGet_trip_detail(){
    $type = $this->sGet('type');
    $id   =   $this->iGet('id');
    if($type=="info"){
      $modal_data = Info::model()->findByPk($id);
      if(!$modal_data){
        $this->ajaxReturn(-1,[],'数据不存在');
        // return $this->error('数据不存在');
      }
      $data                 = json_decode(CJSON::encode($modal_data),true); //把對像轉為數組
      $data['time']         = strtotime($data['time'].'00');
      $data['time_format']  = date('Y-m-d H:i',$data['time']);
      $data['start_info']   = $data['startpid'] ?   Address::model()->getDataById($data['startpid'],['addressid','addressname','latitude','longtitude','city']):array('addressname'=>'-');
      $data['end_info']     = $data['endpid']   ?   Address::model()->getDataById($data['endpid'],['addressid','addressname','latitude','longtitude','city']):array('addressname'=>'-');
      $data['passenger_info']   = $data['passengerid'] ?   CP_User::model()->getDataById($data['passengerid'],['uid','name','loginname','deptid','Department','carnumber','imgpath']):array('name'=>'-');
      $data['owner_info']   = $data['carownid'] ?   CP_User::model()->getDataById($data['carownid'],['uid','name','loginname','deptid','Department','carnumber','imgpath']):array('name'=>'-');
      // return $this->success('加载成功','',$data);
      $this->ajaxReturn(0,$data,'success');
    }

    if($type=="wall"||$type=="lovewall"){
      $modal_data = Wall::model()->findByPk($id);
      if(!$modal_data){
        $this->ajaxReturn(-1,[],"数据不存在");
        // return $this->error('数据不存在');
      }
      if($modal_data->status > 1){
        // $this->ajaxReturn(-1,[],"本行程已取消或完结");
        // return $this->error('本行程已取消或完结');
      }
      $data                 = json_decode(CJSON::encode($modal_data),true); //把對像轉為數組
      $data['time']         = strtotime($data['time'].'00');
      $data['time_format']  = date('Y-m-d H:i',$data['time']);
      $data['start_info']   = $data['startpid'] ?   Address::model()->getDataById($data['startpid'],['addressid','addressname','latitude','longtitude','city']):array('addressname'=>'-');
      $data['end_info']     = $data['endpid']   ?   Address::model()->getDataById($data['endpid'],['addressid','addressname','latitude','longtitude','city']):array('addressname'=>'-');
      $data['owner_info']   = $data['carownid'] ?   CP_User::model()->getDataById($data['carownid'],['uid','name','loginname','deptid','Department','carnumber','imgpath']):array('name'=>'-');
      $data['took_count']   = Info::model()->count('love_wall_ID='.$data['love_wall_ID'].' and status < 2'); //取已坐数


      $this->ajaxReturn(0,$data,'success');
    }


  }


  public function actionGet_disclaimer(){
    $content = " <p>  &nbsp;&nbsp;&nbsp;&nbsp;溢达Carpool拼车APP是一款非经营性和公益性的APP，本APP免费对公众开放使用。本APP为开车人、想拼车上下班或出行的人提供信息发布和配对平台。</p>
    <p>  &nbsp;&nbsp;&nbsp;&nbsp;我们的宗旨是：减少交通拥堵，缓解车位紧张，提高汽车的利用效率，减少环境污染，倡导环保绿色低炭出行；让您上下班或出行更加方便省心，出行更加便捷愉快。</p>
    <p>  &nbsp;&nbsp;&nbsp;&nbsp;任何使用本APP的用户均应仔细阅读本声明，用户可选择不使用溢达Carpool拼车APP，用户使用本APP的行为将被视为对本声明全部内容的认可。任何使用本APP的用户均应遵守拼车所在地国家的法律法规，不得侵犯他人的合法权益。 溢达Carpool拼车APP用户的基本义务包括：</p>
    <p>（1）承诺绝不为任何非法目的或以任何非法方式使用溢达Carpool拼车APP，并承诺遵守拼车所在地国家的相关法律法规。</p>
    <p>（2）基于溢达Carpool拼车APP所提供的服务的重要性，用户同意：
              <br>  &nbsp;&nbsp; &nbsp;&nbsp;A)提供详尽、准确的个人资料。
              <br>  &nbsp;&nbsp;&nbsp;&nbsp; B)根据需要更新注册资料，符合及时、详尽、准确的要求。本APP不会对注册的车主进行信息审核（如驾照是否有效、车辆手续是否齐全等），不保证注册车主有营运资格，因使用本平台服务而产生的任何法律纠纷（包括但不限于交通事故纠纷、人身损害赔偿纠纷、生命权纠纷等），本APP及其所有者不承担任何责任，由用户自行与拼车车主协商解决或通过诉讼途径解决。
    </p>
    <p>（3）承诺在车上不吸烟，不吐痰，不携带危险品。</p>
    <p>（4）承诺无传染病、心脏病、严重晕车以及不适宜乘坐车辆出行的疾病与缺陷。</p>
    <p>（5）拼车是公益行为，双方不得引发商业行为，如：索要费用、发票等。 </p>
    <p>  &nbsp;&nbsp;&nbsp;&nbsp;超豐科技服務有限公司作为本APP所有者及发布者保留下述权利：随时修改、删除在本APP发布的任何信息；随时停止本APP提供的服务；当用户违反了基本义务时，溢达Carpool拼车APP的系统记录有可能作为其违反法律的证据；同时，本APP所有者及发布者有权作出独立判断，立即取消该用户帐号，而用户亦应对自己在APP上的行为独立承担法律后果。 </p>
    <p>  &nbsp;&nbsp;&nbsp;&nbsp;溢达Carpool拼车APP提醒您：拼车时为保障车主和乘客双方权益，建议要求对方出示相关身份证明，签署免责协议或声明，并在启程前协商好各项事宜。          APP内各项拼车信息均为用户自行发布，本APP平台无法核实信息真伪，溢达Carpool拼车APP提醒您拼车有风险，在实际拼车过程中，请您务必保持警惕！本APP平台无法了解车主是否是合格司机及车辆手续是否齐全，车主是否购买乘客险或其他相关保险，及保险额度，同时，也无法判断保险公司是否会因非营业性私有车辆进行拼车服务而拒绝赔偿，因此请用户使用本APP平台时了解相关风险，如果使用本平台，请自行承担相关风险，本APP所有者及发布者不负任何责任。</p> ";
    $this->ajaxReturn(0,['content'=>$content]);
  }

  /**
   * [actionGet_companys description]
   * @return [type] [description]
   */
  public function actionGet_companys(){
    $companys = Company::model()->findAll("status = 1 ");
    if($companys){
      $lists = array();
      foreach ($companys as $key => $value) {
        $lists[$key] = array(
          'company_id' => $value['company_id'],
          'company_name' => $value['company_name'],
          'short_name' => $value['short_name'],
        ) ;
      }
      $this->ajaxReturn(0,['lists'=>$lists],'success');

    }else{
      $this->ajaxReturn(-1,[],'fail');

    }
  }
  /**
   * [actionGet_companys description]
   * @return [type] [description]
   */
  public function actionGet_departments(){
    $company_id = $this->iGet('company_id');
    $criteria = new CDbCriteria();
    $criteria->condition = "is_active = 1 AND company_id = ".$company_id;
    $criteria->order = "department_name ASC";


    $department = Department::model()->findAll($criteria);

    if($department!==false){

      $department = json_decode(CJSON::encode($department),true);

      $this->ajaxReturn(0,['lists'=>$department],'success');
    }else{
      $this->ajaxReturn(-1,[],'fail');
    }
}

}
