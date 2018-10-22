<?php
// namespace app\controllers;

class WallController extends  CarpoolBaseController {

  public function init() {
		parent::init();
	}


//行程首页
  public function actionIndex()
	{
    $this->render('index');
	}


  /**
   * 取得空座位详细数据
   * @return string  以json返回空座位数据
   */
  public function actionGet_lists(){
    //取得自己所属公司的id
    $userInfo = $this->getUser();
    $uid          = $userInfo->uid;
    $company_id   = $userInfo->company_id;
    // $time_horizon = strtotime("-1 day"); //展示多少天前至今的数据
    $time_horizon = strtotime("-1 hour");
    $keyword      = $this->sGet('keyword');
    //先查找自己已搭的车
    $criteria_hasTake = new CDbCriteria();
    $criteria_hasTake->addCondition('passengerid = '.$uid);
    $criteria_hasTake->addCondition('love_wall_ID >0 ');
    $criteria_hasTake->addCondition('status < 2');
    $criteria_hasTake->addCondition('time < 210000000000');
    $criteria_hasTake->addCondition('time > '.(date('YmdHi',$time_horizon)));

    $hasTake = Info::model()->findAll($criteria_hasTake);
    $hasTake = json_decode(CJSON::encode($hasTake),true);
    $hasTakeIDs = array();
    foreach ($hasTake as $key => $value) {
      $hasTakeIDs[$key] = $value['love_wall_ID'];
    }

    //再查自己已点过赞的空座位
    $criteria_hasLike = new CDbCriteria();
    $criteria_hasLike->addCondition('uid = '.$uid);
    // $criteria_hasLike->addCondition('time < 210000000000');
    $criteria_hasLike->addCondition('like_time > "'.(date('Y-m-d H:i:s',$time_horizon)).'"');
    $hasLike = WallLike::model()->findAll($criteria_hasLike);

    $hasLike = json_decode(CJSON::encode($hasLike),true);
    $hasLikeIDs = array();
    foreach ($hasLike as $key => $value) {
      $hasLikeIDs[$key] = $value['love_wall_id'];
    }


    //检出空座位列表
    $model = new Wall();
    $criteria = new CDbCriteria();

    $criteria->addCondition('status < 2');
    $criteria->addCondition('endpid IS NOT NULL');
    $criteria->addCondition('startpid IS NOT NULL');
    $criteria->addCondition('u.company_id = '.$company_id);
    $criteria->addCondition('time < 210000000000');
    $criteria->addCondition('time > '.(date('YmdHi',$time_horizon)));
    if($keyword){
      $criteria->addCondition("u.name like '%".$keyword."%' or s.addressname like '%".$keyword."%' or e.addressname like '%".$keyword."%' or t.time like '%".$keyword."%' ");
      // $criteria->compare('u.name',$keyword,true);
      // $criteria->compare('s.addressname',$keyword,true,'OR');
    }
    $selectArray_info = array('love_wall_ID','startpid','endpid','time','subtime','cancel_time','type','status','carownid','seat_count');
    $criteria->order = 'time ASC , subtime ASC, love_wall_ID ASC';
    $criteria->with = array('user','start','end');
    // $criteria->join = 'left join address as s on t.startpid = s.addressid left join address as e on t.endpid = e.addressid ';
    // $criteria->with = 'end';
    $count = $model->count($criteria);
    $page = new CPagination($count);
    $page->pageSize = 15;
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

    // $results = json_decode(CJSON::encode($results),true);

    $lists = array();
    $model_Address = Address::model();
    foreach ($results as $key => $value) {
      $valueArray = json_decode(CJSON::encode($value),true);
      foreach($selectArray_info as $field){
        $lists[$key][$field] = $valueArray[$field];
      }
      if(in_array($value['love_wall_ID'],$hasTakeIDs)){
        $lists[$key]['hasTake'] = 1 ;
      }else{
        $lists[$key]['hasTake'] = 0 ;
      }
      //  var_dump(!empty($hasLikeIDs));
      if(!empty($hasLikeIDs) && in_array($value['love_wall_ID'],$hasLikeIDs)){
        $lists[$key]['hasLike'] = 1 ;
      }else{
        $lists[$key]['hasLike'] = 0 ;
      }
      $lists[$key]['id'] = $value['love_wall_ID'];
      $lists[$key]['time'] = date('Y-m-d H:i',strtotime($value['time'].'00'));
      $lists[$key]['subtime'] = date('Y-m-d H:i',strtotime($value['subtime'].'00'));

      // $lists[$key]['start_info'] = $value['startpid'] ? $model_Address->getDataById($value['startpid'],['addressid','addressname','latitude','longtitude','city']):array('addressname'=>'-');
      // $lists[$key]['end_info'] =  $value['endpid'] ?  $model_Address->getDataById($value['endpid'],['addressid','addressname','latitude','longtitude','city']):array('addressname'=>'-');
      // $lists[$key]['owner_info'] =  $value['carownid'] ?  CP_User::model()->getDataById($value['carownid'],['uid','name','loginname','deptid','phone','Department','carnumber']):array('name'=>'-');
      $lists[$key]['start_info']    = $value->start ? json_decode(CJSON::encode($value->start),true):["addressid"=>NULL,"addressname"=>"-"];
      $lists[$key]['end_info']      = $value->end ? json_decode(CJSON::encode($value->end),true):["addressid"=>NULL,"addressname"=>"-"];
      $lists[$key]['owner_info']    = array('name'=>$value->user->name,'loginname'=>$value->user->loginname,
                                        'Department'=>$value->user->Department,'carnumber'=>$value->user->carnumber,'uid'=>$value->user->uid,
                                        'imgpath'=>$value->user->imgpath,'phone'=>$value->user->phone,'mobile'=>$value->user->mobile);

      //取点赞数
      $lists[$key]['like_count']    = WallLike::model()->count('love_wall_ID='.$value['love_wall_ID']);
      //取已坐数
      $lists[$key]['took_count']    = Info::model()->count('love_wall_ID='.$value['love_wall_ID'].' and status < 2');
    }
    unset($resulst);
    $data = array('lists'=>$lists,'page'=>$pageReturn,'hasTakeIDs'=>$hasTakeIDs);
    $this->ajaxReturn(0,$data);
    exit;
    // exit(json_encode(array('code'=>0,'msg'=>'','data'=>$data)));
  }

  /**
   * 取得空座位详细数据
   * @return string  以json返回空座位数据
   */
  public function actionDetail(){
    $id = $this->iGet('id');
    $uid = $this->userBaseInfo->uid;
    if(!$id){
      $this->ajaxReturn(-1,[],"lost id");
      // return $this->error('lost id');
    }
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
    $data['owner_info']   = $data['carownid'] ?   CP_User::model()->getDataById($data['carownid'],['uid','name','loginname','deptid','phone','Department','carnumber','imgpath','mobile']):array('name'=>'-');

    $data['took_count']       = Info::model()->count('love_wall_ID='.$data['love_wall_ID'].' and status < 2'); //取已坐数
    $data['took_count_all']   = Info::model()->count('love_wall_ID='.$data['love_wall_ID'].' and status <> 2'); //取已坐数
    $data['hasTake']          = Info::model()->count('love_wall_ID='.$data['love_wall_ID'].' and status < 2 and passengerid ='.$uid.''); //查看是否已搭过此车主的车
    $data['hasTake_finish']   = Info::model()->count('love_wall_ID='.$data['love_wall_ID'].' and status = 3 and passengerid ='.$uid.''); //查看是否已搭过此车主的车
    $data['uid']              = $uid;



    return $this->ajaxReturn(0,$data,'加载成功');

  }

 /**
  * 发布空座位
  * @return string  返回是否成功的json
  */
  public function actionAdd(){

      $datas['time']        = $this->sPost('time');
      $datas['startpid']    = $this->iPost('startpid');
      $datas['endpid']      = $this->iPost('endpid');
      $datas['start']       = $this->aPost('start');
      $datas['end']         = $this->aPost('end');
      $datas['seat_count']  = $this->iPost('seat_count');
      $datas['distance']    = $this->iPost('distance');
      if(empty($datas['time'])){
        $this->ajaxReturn(-1,[],"时间不能为空");
        // $this->error('时间不能为空');
      }
      if(empty($datas['seat_count'])){
        $this->ajaxReturn(-1,[],"空座位个数不能为空");
        // $this->error('空座位个数不能为空');
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
            $this->ajaxReturn(-1,[],"起点不能为空");
            // $this->error('起点不能为空');
          }
        }else{
          $this->ajaxReturn(-1,[],"起点不能为空");
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
            $this->ajaxReturn(-1,[],"终点不能为空");
            // $this->error('终点不能为空');
          }
        }else{
          $this->ajaxReturn(-1,[],"终点不能为空");
          // $this->error('终点不能为空');
        }
      }

      //要提交的行程时间
      $datas['time'] = date('YmdHi',strtotime(date('Y',time()).$datas['time'].'00'));
      if(date('YmdHi',time()) > $datas['time']){
        $this->ajaxReturn(-1,[],"出发时间已经过了<br /> 请重选时间");
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
      };

      //
      $model = new Wall();
  		$model->carownid = $this->userBaseInfo->uid;
      $model->subtime =  date('YmdHi');
      // $datas['update_time'] = date('Y-m-d H:i:s');
      // $datas['time'] = date('YmdHi',strtotime(date('Y',time()).$datas['time'].'00'));
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
  }

  public function actionLike(){
    $id = $this->iPost('id');
    $uid = $this->userBaseInfo->uid;
    if(!$id){
      $this->ajaxReturn(-1,[],'lost id');
      // return $this->error('lost id');
    }
    //检查空座位是否存在
    $modal_wall = Wall::model()->findByPk($id);

    if(!$modal_wall){
      $this->ajaxReturn(-1,[],'空座位不存在');
      // return $this->error('空座位不存在');
    }

    //查找是否点过赞
    $like_count = WallLike::model()->count('love_wall_ID='.$id.' and uid = '.$uid);
    if($like_count>0){
      $this->ajaxReturn(10006,[],'您已点过赞');
      // return $this->ajaxReturn(417,array(),'您已点过赞');
    }

    //创建点赞
    $modal_like = new WallLike();
    $modal_like->love_wall_id = $id;
    $modal_like->uid          = $uid;
    $result = $modal_like->save();
    if($result){
      $this->ajaxReturn(0,[],'success');
      // $this->success('点赞成功');
    }else{
      $this->ajaxReturn(-1,[],'fail');
      // $this->error('点赞失败');
    }

  }

}
