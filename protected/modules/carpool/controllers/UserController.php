<?php


class UserController extends  CarpoolBaseController {
  public function init() {
		parent::init();
	}



  /**
   * 取得当前登入的用户数据，并返回ajax
   */
  public function actionGet_user(){
    $userInfo_temp = $this->getUser();
    if(!$userInfo_temp){
      $this->ajaxReturn(-1,array(),'请重新登入');
      // $this->error('请重新登入','',array('code'=>'-1'));
    }
    $userInfo = json_decode(CJSON::encode($userInfo_temp),true);
    $unsetField = array('passwd','client_id','can_overload','md5password','is_changepassword','like');
    $userInfo['home_address'] = '';
    if($userInfo['home_address_id']>0){
      $home_adress_data = Address::model()->getDataById($userInfo['home_address_id']);
      $userInfo['home_address'] = $home_adress_data ?  $home_adress_data['addressname'] : "";
    }

    $userInfo['company_address'] = '';
    if($userInfo['company_address_id']>0){
      $company_address_data = Address::model()->getDataById($userInfo['company_address_id']);
      $userInfo['company_address'] = $company_address_data ?  $company_address_data['addressname'] : "";
    }

    foreach ($unsetField as  $value) {
      if(isset($userInfo[$value])){
        unset($userInfo[$value]);
      }
    }

    $this->ajaxOK('加载成功',$userInfo);
  }


  /**
   * 取得当前登入的用户数据，并返回ajax
   */
  public function actionGet_user_statis(){
    $uid = $this->userBaseInfo->uid; //取得用户id
    $command = Yii::app()->carpoolDb->createCommand('call load_userinfo_by_uid('.$uid.')');
    $data = $command->query()->readAll();
    if($data){
      $this->ajaxOK('加载成功',$data[0]);
    }else{
      $this->ajaxError('加载失败');
    }
  }

  /**
   * 改变地址
   */
  public function actionChange_address(){
    $datas['aid']       = $this->iPost('aid');
    $data['name']       = $this->sPost('name');
    $data['latitude']   = $this->sPost('latitude');
    $data['longtitude'] = $this->sPost('longtitude');
    $data['city']       = $this->sPost('city');
    $data['company_id'] = $this->userBaseInfo->company_id;
    $from               = $this->sPost('from');
    if($from=="work"){
      $from = "company";
    }
    $uid = $this->userBaseInfo->uid;
    if(!in_array($from,array('home','company'))){
      $this->ajaxReturn(-10002,[],'参数有误');
      // return $this->error('参数有误');
    }
    $createAddress = array();
    if(!$datas['aid']){

      Yii::import('application.modules.carpool.controllers.AddressController');
      $AddressCtr = new AddressController('Address');

      //处理起点
        if(!empty($data['longtitude']) && !empty($data['latitude']) && !empty($data['name'])){
          //如果id为空，通过经纬度查找id.无则创建一个并返回id;
          $data['company_id'] = $this->userBaseInfo->company_id;
          $createID = $AddressCtr->createAddressID($data);

          if($createID){
            $createAddress = $data;
            $createAddress['addressid'] = $createID;
            $datas['aid'] = $createID;

          }else{
            $this->ajaxReturn(500,[],'网络出错，请稍后再试 -1');
          }
        }else{
          $this->ajaxReturn(414,[],'网络出错，请稍后再试 -2');
        }
    }
    $address_data = Address::model()->findByPk($datas['aid']);
    if(!$address_data){
      $this->ajaxReturn(500,[],'网络出错，请稍后再试 -3');
      // return $this->error('网络出错，请稍候再试，-3');
    }
    $status = CP_User::model()->updateByPk($uid,array($from.'_address_id'=>$datas['aid']));
    // var_dump($status);
    if($status!==false){
      return $this->ajaxReturn(0,array('createAddress'=>$createAddress),"success");
      // $this->success('修改成功','',array('createAddress'=>$createAddress));
    }else{
      $this->ajaxReturn(-1,[],'修改失败');
      // $this->error('修改失败');
    }

  }

  /**
   * 改变资料
   */
  public function actionChange_profile(){
    $type = $this->sPost('type');
    $val =  $this->sPost($type);
    $uid = $this->userBaseInfo->uid;
    $type = strtolower($type);
    $fields = array('carnumber','carcolor','cartype','password','sex','company_id','loginname','department','name');
    if(!in_array($type,$fields)){
      $this->ajaxReturn(-10002,[],'参数有误');
    }
    $userData = $this->getUser();

    switch ($type) {
      case 'password':
        $old_password = trim($val);
        // $userInfo = $this->getUser();
        if( $old_password ==''/* ||  md5($old_password) != $userInfo['md5password']*/){
          $this->ajaxReturn(-10001,[],'旧密码不能为空');
        }
        if($userData->md5password != md5($old_password)){
          $this->ajaxReturn(10001,[],'请输入正确的旧密码');
        }
        $pw_new     = $this->sPost('pw_new');
        $pw_confirm = $this->sPost('pw_confirm');
        if( $pw_new  != $pw_confirm ){
          return $this->ajaxReturn(-10002,[],"两次密码不一至");
          // return $this->error('两次密码不一至');
        }
        if(strlen($pw_new) < 6){
          return $this->ajaxReturn(-10002,[],"密码不能少于6位");
          // return $this->error('密码不能少于6位');
        }
        $hashPassword = md5($pw_new); //加密后的密码
        $status = CP_User::model()->updateByPk($uid,array('md5password'=>$hashPassword));
        if($status!==false){
          return $this->ajaxReturn(0,[],"success");
          // $this->success('修改成功');
        }else{
          return $this->ajaxReturn(-1,[],"fail");
          // $this->error('修改失败');
        }
        break;
      case 'loginname':
        if(strlen($val) < 4){
          return $this->ajaxReturn(-10002,[],"长度太短");
          // return $this->error('密码不能少于6位');
        }
        $userDataHas = CP_User::model()->findBySql('select * from user where loginname=:loginname AND uid <> :uid ',array(':loginname'=>$val,':uid'=>$uid));
        if($userDataHas){
          return $this->ajaxReturn(10006,[],"该帐号已被使用，请重新输入");
        }
        $status = CP_User::model()->updateByPk($uid,array('loginname'=>$val));
        if($status!==false){
          return $this->ajaxReturn(0,[],"success");
        }else{
          return $this->ajaxReturn(-1,[],"fail");
        }
        break;
      case 'department':
        $department_id = $this->iPost('departmentid');
        $departmentData = Department::model()->findByPk($department_id);
        if(!$departmentData){
          return $this->ajaxReturn(-1,[],"fail");
        }
        $status = CP_User::model()->updateByPk($uid,array('Department'=>$departmentData->department_name));
        if($status!==false){
          return $this->ajaxReturn(0,[],"success");
        }else{
          return $this->ajaxReturn(-1,[],"fail");
        }
        break;

      default:
        if(!in_array($type,array('carnumber','carcolor'))){
          if($val==''){
            return $this->ajaxReturn(-1,[],"不能为空");
          }
        }

        $status = CP_User::model()->updateByPk($uid,array($type=>$val));
        // var_dump($status);
        if($status!==false){
          return $this->ajaxReturn(0,[],"success");
          // $this->success('修改成功');
        }else{
          return $this->ajaxReturn(-1,[],"fail");
          // $this->error('修改失败');
        }
        break;
    }

    // $this->success('修改成功');
  }

  /**
   * 查找用户
   */
  public function actionSearch_user_by_identifier(){
    $identifier = $this->sGet('identifier');
    $userInfo = $this->getUser();
    $department = $userInfo->Department;

    if(trim($identifier)==""){
      $this->ajaxReturn(-1,[],"关键字不能为空");
    }
    $model = new CP_User();
    $criteria = new CDbCriteria();
    $criteria->addCondition('is_active = 1');
    if($userInfo->company_id){
      $criteria->addCondition('company_id = '.$userInfo->company_id);
    }
    $criteria->addCondition(" im_id IS NOT NULL");
    $criteria->addCondition("name like '%".$identifier."%' or loginname = '".$identifier."' or phone = '".$identifier."'");


    $criteria->order = 'uid ASC';
    $results = $model->findAll($criteria);
    if(!$results){
      $this->ajaxReturn(0,array("user_list"=>[]),"查找不到数据");
    }
    // var_dump($results);
    $userSameDptList = [];
    $userUnSameDptList = [];

    foreach ($results as $key => $value) {
      $returnValue = array(
        "uid"=>$value->uid,
        "im_id"=>$value->im_id,
        "name"=>$value->name,
        "avatar"=>$value->imgpath ? $value->imgpath : "im/default.png",
        "department"=>$value->Department
      );
      if($department && $value->Department == $department){
        $userSameDptList[] = $returnValue;
      }else{
        $userUnSameDptList[] = $returnValue;
      }
    }

    $user_list = array_merge($userSameDptList,$userUnSameDptList);

    $this->ajaxReturn(0,array("user_list"=>$user_list),"success");
    exit;



  }

}
