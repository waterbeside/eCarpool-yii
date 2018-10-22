<?php
// namespace app\controllers;

class InfoController extends  CarpoolBaseController {
  public function init() {
		parent::init();
	}

  /**
   *
   */
  public function actionIndex()
	{
    $this->render('index');
	}


  /**
   * 取得约车需求列表
   * @return string 返回json格式数据
   */
  public function actionGet_lists(){
    $userInfo = $this->getUser();
    $company_id = $userInfo->company_id;
    $keyword      = $this->sGet('keyword');

    $model = new Info();
    $criteria = new CDbCriteria();
    $criteria->addCondition('status = 0');
    $criteria->addCondition('endpid IS NOT NULL');
    $criteria->addCondition('startpid IS NOT NULL');
    $criteria->addCondition('u.company_id = '.$company_id);
    // $criteria->addCondition('time > '.(date('YmdHi',strtotime("-1 day"))));
    $criteria->addCondition('time > '.(date('YmdHi',strtotime("-1 hour"))));
    $criteria->addCondition('time < 210000000000');
    if($keyword){
      $criteria->addCondition("u.name like '%".$keyword."%' or s.addressname like '%".$keyword."%' or e.addressname like '%".$keyword."%' or t.time like '%".$keyword."%' ");
    }
    $selectArray_info = array('infoid','startpid','endpid','time','subtime','cancel_time','type','status','carownid','passengerid');
    // $SelectArray_user = array('u.name','u.loginname','u.deptid','u.phone');
    // $criteria->select = array_merge($selectArray_info,$SelectArray_user);
    // $criteria->distinct = false;
    $criteria->order = 'time ASC , subtime ASC, infoid ASC';

    $criteria->with = array('user','start','end');

    $count = $model->count($criteria);
    $page = new CPagination($count);
    $page->pageSize = 20;
    $page->applyLimit($criteria);

    $pageReturn  = array(
      'pageSize' => $page->getPageSize(),
      'pageCount' => $page->getPageCount(),
      'currentPage' =>  $page->getCurrentPage(),
      'total' =>  $page->getItemCount(),
      'params' =>  $page->params,
    );


    if(isset($_GET[$page->pageVar]) && $_GET[$page->pageVar] > $page->getPageCount()){
      $results = array();
      $this->ajaxReturn(20002,['lists'=>$results,'page'=>$pageReturn],'No data');

    }else{
      $results = $model->findAll($criteria);
    }



    $lists = array();
    foreach ($results as $key => $value) {
      $valueArray = json_decode(CJSON::encode($value),true);
      foreach($selectArray_info as $field){
        $lists[$key][$field] = $valueArray[$field];
      }
      $lists[$key]['id'] = $valueArray['infoid'];
      $lists[$key]['time'] = date('Y-m-d H:i',strtotime($valueArray['time'].'00'));
      $lists[$key]['subtime'] = date('Y-m-d H:i',strtotime($valueArray['subtime'].'00'));
      // var_dump($value->user);
      $lists[$key]['start_info']    = $value->start ? json_decode(CJSON::encode($value->start),true):["addressid"=>NULL,"addressname"=>"-"];
      $lists[$key]['end_info']      = $value->end ? json_decode(CJSON::encode($value->end),true):["addressid"=>NULL,"addressname"=>"-"];
      $lists[$key]['passenger_info']  = json_decode(CJSON::encode($value->user),true);
    }
    unset($resulst);
    $data = array('lists'=>$lists,'page'=>$pageReturn);
    $this->ajaxReturn(0,$data);
    exit;
    // exit(json_encode(array('code'=>200,'msg'=>'','data'=>$data)));
  }

  /**
   * 取得空座位乘客列表
   * @return [type] [description]
   */
  public function actionGet_passengers(){
    $wallid = $this->iGet('wallid');
    if(!$wallid){
      $this->ajaxReturn(0,[],"Lost id");
      // $this->error('Lost id');
    }
    $model = new Info();
    $criteria = new CDbCriteria();
    $criteria->addCondition('love_wall_ID = '.$wallid);
    $criteria->addCondition('status <> 2');
    $selectArray_info = array('infoid','type','status','carownid');
    $selectArray_user = array('Department','loginname','phone','imgpath','name','uid','mobile');
    $criteria->order = 'time asc , subtime asc, infoid asc';

    $criteria->with = 'user';
    $results = $model->findAll($criteria);
    $lists = array();
    foreach ($results as $key => $value) {
      $user_info_array = json_decode(CJSON::encode($value->user),true);
      foreach($selectArray_user as $field_u){
        $lists[$key][$field_u] = $user_info_array[$field_u];
      }
      $valueArray = json_decode(CJSON::encode($value),true);
      foreach($selectArray_info as $field){
        $lists[$key][$field] = $valueArray[$field];
      }
    }
    unset($resulst);
    $this->ajaxReturn(0,array('lists'=>$lists));
    // exit(json_encode(array('code'=>0,'desc'=>'','data'=> array('lists'=>$lists))));

  }

  /**
   * 取得需求详细数据
   * @return string  以json返回空座位数据
   */
  public function actionDetail(){
    $id = $this->iGet('id');
    if(!$id){
      $this->ajaxReturn(-1,[],'lost id');
      // return $this->error('lost id');
    }
    $modal_data = Info::model()->findByPk($id);
    if(!$modal_data){
      $this->ajaxReturn(-1,[],'数据不存在');
      // return $this->error('数据不存在');
    }
    if($modal_data->status > 1){
      // $this->ajaxReturn(-1,[],'本行程已取消或完结');
      // return $this->error('本行程已取消或完结');
    }
    $data                 = json_decode(CJSON::encode($modal_data),true); //把對像轉為數組
    $data['time']         = strtotime($data['time'].'00');
    $data['time_format']  = date('Y-m-d H:i',$data['time']);
    $data['start_info']   = $data['startpid'] ?   Address::model()->getDataById($data['startpid'],['addressid','addressname','latitude','longtitude','city']):array('addressname'=>'-');
    $data['end_info']     = $data['endpid']   ?   Address::model()->getDataById($data['endpid'],['addressid','addressname','latitude','longtitude','city']):array('addressname'=>'-');
    $data['passenger_info']   = $data['passengerid'] ?   CP_User::model()->getDataById($data['passengerid'],['uid','name','loginname','deptid','phone','Department','carnumber','imgpath','mobile']):array('name'=>'-');
    $data['owner_info']   = $data['carownid'] ?   CP_User::model()->getDataById($data['carownid'],['uid','name','loginname','deptid','phone','Department','carnumber','imgpath','mobile']):array('name'=>'-');

    $data['uid']          = $this->userBaseInfo->uid;
    // return $this->success('加载成功','',$data);
    $this->ajaxReturn(0,$data,'success');

  }

  /**
   * 发布约车需求
   */
    public function actionAdd(){
      if (Yii::app()->request->isPostRequest) {
        $datas['time'] = $this->sPost('time');
        $datas['startpid'] = $this->iPost('startpid');
        $datas['endpid'] = $this->iPost('endpid');
        $datas['start'] = $this->aPost('start');
        $datas['end'] = $this->aPost('end');
        $datas['distance']    = $this->iPost('distance');

        if(empty($datas['time'])){
          $this->ajaxReturn(-1,[],'时间不能为空');
          // $this->error('时间不能为空');
        }
        if(!$datas['startpid'] || !$datas['endpid']){
          Yii::import('application.modules.carpool.controllers.AddressController');
          $AddressCtr = new AddressController('Address');
        }
        $userInfo = $this->getUser();
        $createAddress = array();
        //处理起点
        if(!$datas['startpid']){
          $startDatas = $datas['start'];
          if(!empty($startDatas['longtitude']) && !empty($startDatas['latitude']) && !empty($startDatas['name'])){
            //如果id为空，通过经纬度查找id.无则创建一个并返回id;
            $startDatas['company_id'] = $userInfo->company_id;
            $createID = $AddressCtr->createAddressID($startDatas);
            if($createID){
              $createAddress[0] = $startDatas;
              $createAddress[0]['addressid'] = $createID;
              $datas['startpid'] = $createID;
            }else{
              $this->ajaxReturn(-1,[],'起点不能为空');
              // $this->error('起点不能为空');
            }
          }else{
            $this->ajaxReturn(-1,[],'起点不能为空');
            // $this->error('起点不能为空');
          }
        }

        //处理终点
        if(!$datas['endpid']){
          $endDatas = $datas['end'];
          if(!empty($endDatas['longtitude']) && !empty($endDatas['latitude']) && !empty($endDatas['name'])){
            //如果id为空，通过经纬度查找id.无则创建一个并返回id;
            $endDatas['company_id'] = $userInfo->company_id;
            $createID = $AddressCtr->createAddressID($endDatas);
            if($createID){
              $createAddress[1] = $endDatas;
              $createAddress[1]['addressid'] = $createID;
              $datas['endpid'] = $createID;
            }else{
              $this->ajaxReturn(-1,[],'终点不能为空');
              // $this->error('终点不能为空');
            }
          }else{
            $this->ajaxReturn(-1,[],'终点不能为空');
            // $this->error('终点不能为空');
          }
        }

        //要提交的行程时间
        $datas['time'] = date('YmdHi',strtotime(date('Y',time()).$datas['time'].'00'));
        if(date('YmdHi',time()) > $datas['time']){
          $this->ajaxReturn(-1,[],'出发时间已经过了<br /> 请重选时间');
          // $this->error("出发时间已经过了<br /> 请重选时间");
        }

        //计算前后范围内有没有重复行程
        $connection = Yii::app()->carpoolDb;
        $sql['info'] = "SELECT * FROM info as t
        WHERE
          ( t.carownid = ".$this->userBaseInfo->uid." OR t.passengerid = ".$this->userBaseInfo->uid." )
          AND t.status <> 2
          AND t.time >=  ".date('YmdHi',(strtotime($datas['time'].'00') - (2*60)))."
          AND t.time <= ".date('YmdHi',(strtotime($datas['time'].'00') + (2*60)))."
        ";
        $checkData['info'] = $connection->createCommand($sql['info'])->query()->readAll();
        if(count($checkData['info'])>0){
          $this->ajaxReturn(-1,[],"您在<br />".date('Y-m-d H:i',strtotime($checkData['info'][0]['time'].'00'))."<br />已有一趟行程，<br />在相近时间内请勿重复发布");
          // $this->error("您在<br />".date('Y-m-d H:i',strtotime($checkData['info'][0]['time'].'00'))."<br />已有一趟行程，<br />在相近时间内请勿重复发布");
        };

        $sql['wall'] = "SELECT * FROM love_wall as t
        WHERE
          t.carownid = ".$this->userBaseInfo->uid."
          AND t.status <> 2
          AND t.time >=  ".date('YmdHi',(strtotime($datas['time'].'00') - (20*60)))."
          AND t.time <= ".date('YmdHi',(strtotime($datas['time'].'00') + (20*60)))."
        ";
        $checkData['wall'] = $connection->createCommand($sql['wall'])->query()->readAll();
        if(count($checkData['wall'])>0){
          $this->ajaxReturn(-1,[],"您在<br />".date('Y-m-d H:i',strtotime($checkData['wall'][0]['time'].'00'))."<br />已有一趟行程，<br />在相近时间内请勿重复发布");
          // $this->error("您在<br />".date('Y-m-d H:i',strtotime($checkData['wall'][0]['time'].'00'))."<br />已有一趟行程，<br />在相近时间内请勿重复发布");
        };



        //
        $model = new Info();
    		$model->passengerid = $this->userBaseInfo->uid;
        $model->subtime =  date('YmdHi',time());

        //检查时间是否上下班时间
        $hourMin = date('Hi',strtotime($datas['time']));
        $model->type =  2 ;
        $model->status = 0;
        if( $hourMin > 400 && $hourMin < 1000 ){
          $model->type =  0 ;
        }elseif($hourMin > 1600 && $hourMin < 2200){
          $model->type =  1 ;
        }

        $model->attributes = $datas;
        $result = $model->save();
        // var_dump($model->attributes['infoid']);
  			if ($result) {
          $this->ajaxReturn(0,array('createAddress'=>$createAddress),'success');
            // $this->success('发布成功',$this->createUrl('index'),array('createAddress'=>$createAddress));
  				//  Yii::app()->user->setFlash('success', '更新行程成功');
  				// $this->redirect($this->getReferrer());
  			}else{
          $error = $model->getErrors();
          foreach ($error as $key => $value) {
            $this->ajaxReturn(-1,[],$value[0]);
            // $this->error($value[0]);
            exit;
          }
        }
  		}else{
        $this->render('add');
      }
    }

    /**
     * 接受需求
     * @return [type] [description]
     */
    public function actionAccept_demand(){
      $id = $this->iRequest('id',0);
      if(!$id){
        $this->ajaxReturn(-10001,[],'empty id');
        // $this->error('参数错误');
      }
      $model = Info::model()->findByPk($id);
      $uid = $this->userBaseInfo->uid;
      if(!$model){
        $this->ajaxReturn(-1,[],Yii::t("carpool","Without this data"));
        // return $this->error('无此数据');
      }
      if($uid == $model->passengerid ){
        $this->ajaxReturn(-1,[],Yii::t("carpool","You can't make your own"));
        // $this->ajaxReturn(-1,[],'你不能自己搭自己');

        // return $this->error('你不能自己搭自己');
      }
      if($model->status > 0 ){
        $this->ajaxReturn(-1,[],Yii::t("carpool","This requirement has been picked up or cancelled"));

        // return $this->error('此需求已被人搭载或被取消');
      }

      $datas = array(
        'status' => 1,
        'carownid'=>$uid,
      );

      $model->attributes = $datas;
      $result = $model->save();
      if($result){
        $this->ajaxReturn(0,[],'success');
        // return $this->success('搭载成功');
      }else{
        $this->ajaxReturn(-1,[],'fail');
        // return $this->error('无此数据');
      }

    }

    /**
     * 搭车
     */
      public function actionRiding(){
        $wid = $this->iPost('wid',0);
        if(!$wid){
          $this->ajaxReturn(-1,[],'lost id');
          // return $this->error('lost id');
        }
        $uid = $this->userBaseInfo->uid;
        $model_wall = Wall::model()->findByPk($wid);
        // 如果数据不存在
        if(!$model_wall || $model_wall->status == 2){
          $this->ajaxReturn(-1,[],'车主或已取消空座位，<br />请选择其它司机。');
          // return $this->error('或车主或已取消空座位，<br />请选择其它司机。');
        }else if($model_wall->status == 3){
          $this->ajaxReturn(-1,[],'该行程已结束，<br />请选择其它司机。');
        }
        // 断定是否自己搭自己
        if($model_wall->carownid == $uid){
          $this->ajaxReturn(-1,[],'你不能自己搭自己');
          // return $this->error('请不要自己搭自己');
        }

        $seat_count = $model_wall->seat_count;
        $took_count = Info::model()->count('love_wall_ID='.$wid.' and status <> 2');
        if($took_count >= $seat_count){
          $this->ajaxReturn(-1,[],'座位已满');
        }
        // var_dump($took_count);exit;

        //检查是否已经搭过本行程
        $criteria = new CDbCriteria();
        $criteria->addCondition('love_wall_ID = '.$wid);
        $criteria->addCondition('passengerid ='.$uid);
        $criteria->addCondition('status <> 2');
        $checkHasTake = Info::model()->findAll($criteria);
        if($checkHasTake){
          $this->ajaxReturn(-1,[],'您已搭乘过本行程');
          // return $this->error('您已搭乘过本行程');
        }

        //添加数据到info表
        $model = new Info();
        $datas = array(
          'passengerid'   => $uid,
          'carownid'      => $model_wall->carownid,
          'love_wall_ID'  => $wid,
          'subtime'       => date('YmdHi',time()),
          'time'          => $model_wall->time,
          'startpid'      => $model_wall->startpid,
          'endpid'        => $model_wall->endpid,
          'type'          => $model_wall->type,
          'status'        => 1,
        );

        $model->attributes = $datas;

        $result = $model->save();
        if ($result) {
          $model_wall->status = 1 ;
          $model_wall->save();
          $this->ajaxReturn(0,[],'success');
          // $this->success('搭车成功');
  			}else{
          $error = $model->getErrors();
          foreach ($error as $key => $value) {
            $this->ajaxReturn(-1,[],$value[0]);
            // $this->error($value[0]);
            exit;
          }
        }

      }


/*
    public function actionDelete(){
      $id = $this->iRequest('id');
      if(!$id){
        $this->error('Lost id');
      }
      $model=Info::model()->findByPk($id);
      $model->delete();
      $this->success('删除成功',$this->getReferrer());
    }*/



}
