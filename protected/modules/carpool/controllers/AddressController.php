<?php
// namespace app\controllers;

class AddressController extends  CarpoolBaseController {
  public function init() {
		parent::init();
	}


  public function actionIndex()
	{
    $this->render('index');
	}

  /**
   * 通过经纬查找地址id，无则添加
   */
  public function createAddressID($data=array()){
    if(empty($data)){
      return false;
    }
    $lng = isset($data['longitude']) ? $data['longitude'] : $data['longtitude'];
    //如果id为空，通过经纬度查找id.
    $address =  Address::model()->getDataByCoord($lng,$data['latitude']);

    if(!$address ){
      //如果已有地址中查找不到，则自动创建一个
      $success = $this->addAddress($data);
      return $success;
    }else{
      $returnID = 0 ;
      foreach ($address as $key => $value) {
        if($value['addressname'] == $data['name']){
          $returnID = $value['addressid'];
        }
      }
      if($returnID){
        return $returnID;
      }else{
        $success = $this->addAddress($data);
        return $success;
      }
    }
  }

  /**
   * 添加一个地址
   */
  public function addAddress($data=array()){
    if(empty($data)){
      return false;
    }
    $address = new Address;

    $address->company_id  = $data['company_id'];
    $address->latitude    = $data['latitude'];
    $address->longtitude  = $data['longitude'] ? $data['longitude'] : $data['longtitude'] ;
    $address->addressname = $data['name'];
    $address->city        = $data['city'] ? $data['city'] : '-';
    $address->address_type = 2;
    if($address->save()>0){
        return $address->primaryKey;
    }else{
        return false;
    }
  }

  /**
   * 接口：添加一个地址
   */
  public function actionAdd(){
    $data['name']       = $this->sPost('addressname');
    $data['latitude']   = $this->sPost('latitude');
    $data['longtitude'] = $this->sPost('longtitude');
    $data['city']       = $this->sPost('city');
    $data['company_id'] = $this->getUser()->company_id;
    if(empty($data['name'])){
      $this->ajaxReturn(-10001,[],'站点名称不能为空');
    }
    if(empty($data['latitude']) || (empty($data['longitude']) && empty($data['longtitude'])  )){
      // $this->error('网络出错');
      $this->ajaxReturn(500,[],'网络出错');

    }
    $primaryKey = $this->createAddressID($data);
    if($primaryKey){
      $this->ajaxReturn(0,array('aid'=>$primaryKey),"站点创建成功");
      // $this->success('站点创建成功','',array('aid'=>$primaryKey));
    }
  }

  /**
	 * 取得我的地址列表
	 */
	public function actionGet_myaddress() {
    $uid = $this->userBaseInfo->uid;
		$command = Yii::app()->carpoolDb->createCommand('call get_my_address('.$uid.')');
		$data = $command->query()->readAll();

    if($data){
      foreach ($data as $key => $value) {
        $data[$key]['longitude'] = $value['longtitude'];
        $data[$key]['addressid'] = intval($value['addressid']);
        unset($data[$key]['longtitude']);
      }
      $returnData  = array(
        'lists' => $data,
        'total'=> count($data)
      );
      $this->ajaxReturn(0,$returnData,"success");
      // $this->success('加载成功','',$returnData);
    }else{
      $this->ajaxReturn(-1,"","fail");
    }
  }



}
