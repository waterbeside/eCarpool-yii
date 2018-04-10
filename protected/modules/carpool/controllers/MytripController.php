<?php
class MytripController extends CarpoolBaseController {
  public function init() {
		parent::init();
	}


    /**
     * 我的行程
     * @return string json
     */
    public function actionIndex(){

        $uid = $this->userBaseInfo->uid;
        $limit = $this->iRequest('limit');
        /* 读取 info 数据表 */
        $model_info = new Info();
        $criteria_info = new CDbCriteria();
        $criteria_info->addCondition('t.passengerid='.$uid);
        $criteria_info->addCondition('t.carownid='.$uid,'or');
        // $criteria_info->addCondition('love_wall_ID = "" or ISNULL(love_wall_ID)');
        $criteria_info->addCondition('t.status < 2 ');
        if(!$limit){
          $criteria_info->addCondition('t.time > '.(date('YmdHi',strtotime("-1 hour"))));
          $criteria_info->addCondition('t.time < 210000000000');
        }else{
          $criteria_info->limit = $limit;
        }

        // $criteria->addBetweenCondition('time', 1, 4);

        $criteria_info->select = array('infoid','startpid','endpid','time','subtime','cancel_time','type','status','carownid','passengerid','love_wall_ID');
        $criteria_info->order = 't.time desc , t.subtime desc, infoid desc';
        $criteria_info->with = array('user','carowner','start','end');
        $results = $model_info->findAll($criteria_info);

        // $results = json_decode(CJSON::encode($results),true);
        $lists_info_o = $this->formatListDatas($results,$criteria_info->select,'infoid','info');

        //如果我是司机，并且存在love_wall_ID,则取消此条显示，由love_wall表读取；
        $lists_info = array();
        foreach ($lists_info_o as $key => $value) {
          if( !($value['carownid']==$uid && !empty($value['love_wall_ID'])) ){
            $lists_info[] = $value;
          }
        }
        unset($results);

        /* 读取 wall 数据表 */
        $criteria_wall = new CDbCriteria();
        $model_wall = new Wall();
        $criteria_wall->addCondition('carownid='.$uid);
        $criteria_wall->addCondition('status < 2 ');
        if(!$limit){
          $criteria_wall->addCondition('time > '.(date('YmdHi',strtotime("-1 hour"))));
          $criteria_wall->addCondition('time < 210000000000');
        }else{
          $criteria_wall->limit = $limit;
        }
        $criteria_wall->with = array('user','start','end');
        $criteria_wall->select = array('love_wall_ID','startpid','endpid','time','subtime','cancel_time','type','status','carownid','seat_count');
        $criteria_wall->order = 'time desc , subtime desc, love_wall_ID desc';
        $results = $model_wall->findAll($criteria_wall);


        // $results = json_decode(CJSON::encode($results),true);
        $lists_wall = $this->formatListDatas($results,$criteria_wall->select,'love_wall_ID','wall');
        unset($results);


        //合并两表读出的数据
        $lists = array_merge($lists_info,$lists_wall);

        //设置以时间排罗的数组
        $lists_sort = array();
        foreach($lists as $v){
            $lists_sort[] = $v['time'];
        }
        //重新排列
        array_multisort($lists_sort, SORT_ASC, $lists);

        $data = array('lists'=>$lists,'uid'=>$uid);
        $this->ajaxReturn(0,$data);
        // exit(json_encode(array('code'=>0,'desc'=>'','data'=>$data)));
    }


    /**
     * 历史行程
     */
    public function actionHistory(){
      $connection = Yii::app()->carpoolDb;
      $uid = $this->userBaseInfo->uid;
      // $sql = "SELECT * FROM info AS i WHERE passengerid = uid OR carownid = uid UNION ALL select * from wall as w where car"

      // 从info表取得数据
      $viewSql_u1 = "SELECT
        a.infoid, (case when a.love_wall_ID IS NULL  then '0' else a.love_wall_ID end) as  love_wall_ID ,'0' as trip_type,
        a.startpid,a.endpid,a.time,a.status, a.passengerid, a.carownid,
        '0' as seat_count,
        '0' as liked_count,
        '0' as hitchhiked_count
      FROM
        info AS a
      WHERE
        (a.carownid=$uid OR a.passengerid=$uid)
        AND status <>2
        AND (a.love_wall_ID is null OR  a.love_wall_ID not in (select lw.love_wall_ID  from love_wall AS lw where lw.carownid=$uid and lw.status<>2 ) )
        ORDER BY a.time desc";

      // 从love_wall表取得数据
      $viewSql_u2 = "SELECT '0' AS infoid, a.love_wall_ID AS love_wall_ID,'1' AS trip_type,
        a.startpid,a.endpid,a.time,a.status, '0' as passengerid, a.carownid,
        a.seat_count,
        (select count(*) from love_wall_like as cl where cl.love_wall_id=a.love_wall_ID) as liked_count,
        (select count(*)  from info as ci where ci.love_wall_id=a.love_wall_ID and ci.status  <>2) as hitchhiked_count
      FROM
        love_wall as a
      WHERE
        a.status<>2
        AND carownid=$uid
      ORDER BY  a.time desc";

      $viewSql  =  "($viewSql_u1 ) union all ($viewSql_u2 )";

      $datas_total = $connection->createCommand("SELECT count(*)  from ($viewSql) as t")->queryColumn();
      $total = $datas_total[0];

      $pages = new CPagination($total);
      $pages->pageSize = 20;
      $sql_limit = " LIMIT ".$pages->offset." , ".$pages->limit." ";
      $pageReturn  = array(
        'pageSize' => $pages->getPageSize(),
        'pageCount' => $pages->getPageCount(),
        'currentPage' =>  $pages->getCurrentPage(),
        'page' =>  $pages->getCurrentPage()+1,
        'total' =>  $pages->getItemCount(),
      );

      $sql = "SELECT
          t.infoid , t.love_wall_ID , t.time, t.trip_type ,t.startpid, t.endpid, t.time, t.status, t.passengerid, t.carownid , t.seat_count , t.liked_count , t.hitchhiked_count,
          u1.uid as passenger_uid,u1.im_id as passenger_im_id, u1.name as passenger_name, u1.imgpath as passenger_imgpath, u1.sex as passenger_sex, u1.companyname as passenger_company, u1.Department as passenger_department, u1.phone as passenger_phone,
          u2.uid as driver_uid,u2.im_id as driver_im_id, u2.name as driver_name, u2.imgpath as driver_imgpath, u2.sex as driver_sex, u2.companyname as driver_company, u2.Department as driver_department, u2.phone as driver_phone,
          a1.addressid as from_address_id,a1.addressname as from_address_name,a1.longtitude as from_longtitude,a1.Latitude as from_latitude,
          a2.addressid as to_address_id,a2.addressname as to_address_name,a2.longtitude as to_longtitude,a2.Latitude as to_latitude
        FROM
          ($viewSql) as t
          LEFT JOIN user u1 on t.passengerid = u1.uid
          LEFT JOIN user u2 on t.carownid = u2.uid
          LEFT JOIN address a1 on t.startpid = a1.addressid
          LEFT JOIN address a2 on t.endpid = a2.addressid
        ORDER BY
          t.time DESC, t.infoid DESC, t.love_wall_id DESC
        $sql_limit
      ";
      $datas = $connection->createCommand($sql)->query()->readAll();

      // var_dump($datas);exit;
      foreach ($datas as $key => $value) {
        $datas[$key]['time'] = date('Y-m-d H:i',strtotime($value['time'].'00'));

      }

      $data = array('lists'=>$datas,'page'=>$pageReturn);
      $this->ajaxReturn(0,$data,'success');
      exit;

    }



    /**
     * 取消行程
     * @return string 返回是否成功的json格式
     */
    public function actionCancel_route(){
      $id = $this->iRequest('id',0); // 行程id
      $from = $this->sRequest('from',0); // [ info || wall ] 来自info表，还是love_wall表
      $uid = $this->userBaseInfo->uid; //取得用户id
      if(!$id || !$from){
        $this->ajaxReturn(-10001,[],'参数错误');
        // $this->error('参数错误');
      }

      switch ($from) {
        case 'info': // 来自info表的行程
          $model = Info::model()->findByPk($id);
          if(!$model || !($model->passengerid == $uid || $model->carownid == $uid ) ){
            $this->ajaxReturn(-1,[],'无此数据');
            // return $this->error('无此数据');
          }
          if($model->passengerid == $uid){ // 如果是乘客自己取消，直接变取消状态
            $datas = array(
              'status' => 2 ,
              'cancel_user_id'=>$uid,
              'cancel_time' => date('YmdHi',time()),
            );
          }elseif($model->carownid == $uid){ // 如果是车主取消，重置乘客约车需求状态
            if($model->love_wall_ID > 0){ // 如果存在love_wall_ID，车主需从容座位页入口方可取消
              $this->ajaxReturn(-1,[],'fail');
              // return $this->error('参数有误');
            }
            $datas = array(
              'status' => 0 ,
              'carownid' => '',
              'cancel_user_id'=>$uid,
              'cancel_time' => date('YmdHi',time()),
            );
          }else{
            $this->ajaxReturn(-1,[],'无此数据');
            // return $this->error('无此数据');
          }

          $model->attributes = $datas;
          $result = $model->save();
          if($result){
            return $this->ajaxReturn(0,array(),'取消成功');
          }else{
            $this->ajaxReturn(-1,[],'取消失败，请稍候再试');
            // return $this->error('取消失败，请稍候再试');
          }
          break;

        case 'wall': // 来自love_wall表的行程
          $model = Wall::model()->findByPk($id);
          if(!$model || $model->carownid != $uid  ){
            //如果行程不是自己发布，查找该行程下，我是否有搭此车，有则取消。
            $infoNewData =  array(
              'status' => 2,
              'cancel_user_id'=>$uid,
              'cancel_time' => date('YmdHi',time()),
            );
            Info::model()->updateAll($infoNewData,'love_wall_ID='.$id.' AND passengerid ='.$uid.' AND  status <> 2 ');
            return $this->ajaxReturn(0,array(),'取消成功');
            // $this->ajaxReturn(-1,[],'无此数据');
            exit;
          }
          $datas = array(
            'status' => 2,
            'cancel_time' => date('YmdHi',time()),
          );
          $model->attributes = $datas;
          $result = $model->save(); // 先从love_wall表取消空座位
          if($result){  //成功后，再取消info表上的乘客行程
            $infoNewData =  array(
              'status' => 2,
              'cancel_user_id'=>$uid,
              'cancel_time' => date('YmdHi',time()),
            );
            Info::model()->updateAll($infoNewData,'love_wall_ID='.$id);
            return $this->ajaxReturn(0,array(),'取消成功');
            // return $this->success('取消成功');
          }else{
            $this->ajaxReturn(-1,[],'取消失败，请稍候再试');

            // return $this->error('取消失败，请稍候再试');
          }
          break;

        default:
          # code...
          break;
      }

    }


    public function ActionGet_ofent_trips(){
      $from = $this->sRequest('from',0); // [ info || wall ] 来自info表，还是love_wall表
      $uid = $this->userBaseInfo->uid; //取得用户id
      if(!$from){
        $this->ajaxReturn(-10001,[],'参数有误');
      }

      switch ($from) {
        case 'info':
          $command = Yii::app()->carpoolDb->createCommand('call get_often_trips_by_passengerid('.$uid.')');
      		$data = $command->query()->readAll();
          break;
        case 'wall':
          $command = Yii::app()->carpoolDb->createCommand('call get_often_trips_by_driverid('.$uid.')');
      		$data = $command->query()->readAll();
          break;
        default:
          # code...
          break;
      }
      if($data){
        $this->ajaxOK('加载成功',$data);
      }else{
        $this->ajaxError('加载失败');
      }

    }

    /**
     * 发布行程
     */
    public function ActionAdd(){
      $from = $this->sRequest('from'); // [ info || wall ] 来自info表，还是love_wall表
      $uid = $this->userBaseInfo->uid; //取得用户id
      if(!$from){
        $this->ajaxReturn(-10001,[],'参数有误');
      }
      if($from=="wall"){
        $model = new Wall();
        $model->carownid = $uid;
      }else if($from=="info"){
        $model = new Info();
        $model->passengerid = $uid;
      }else{
        $this->ajaxReturn(-10001,[],'参数错误');
      }
      $datetime     = $this->sPost('datetime');
      $datas['startpid']    = $this->iPost('startpid');
      $datas['endpid']      = $this->iPost('endpid');
      $datas['start']       = $this->aPost('start');
      $datas['end']         = $this->aPost('end');
      $datas['seat_count']  = $this->iPost('seat_count');
      $datas['distance']    = $this->iPost('distance');

      if(empty($datetime)){
        $this->ajaxReturn(-1,[],"时间不能为空");
      }
      if($from=="wall" && empty($datas['seat_count'])){
        $this->ajaxReturn(-1,[],"空座位个数不能为空");
      }


      if(!$datas['startpid'] || !$datas['endpid']){
        Yii::import('application.modules.carpool.controllers.AddressController');
        $AddressCtr = new AddressController('Address');
      }
      $createAddress = array();
      //处理起点
      if(!$datas['startpid']){
        $startDatas = $datas['start'];
        if(!empty($startDatas['longtitude']) && !empty($startDatas['latitude']) && !empty($startDatas['addressname'])){
          $startDatas['name'] = $startDatas['addressname'];
          //如果id为空，通过经纬度查找id.无则创建一个并返回id;
          $startDatas['company_id'] = $this->userBaseInfo->company_id;
          $createID = $AddressCtr->createAddressID($startDatas);
          if($createID){
            $createAddress[0] = $startDatas;
            $createAddress[0]['addressid'] = $createID;
            $datas['startpid'] = $createID;
          }else{
            $this->ajaxReturn(-1,[],"起点不能为空");
          }
        }else{
          $this->ajaxReturn(-1,[],"起点不能为空");
        }
      }

      //处理终点
      if(!$datas['endpid']){
        $endDatas = $datas['end'];
        if(!empty($endDatas['longtitude']) && !empty($endDatas['latitude']) && !empty($endDatas['addressname'])){
          $endDatas['name'] = $endDatas['addressname'];
          //如果id为空，通过经纬度查找id.无则创建一个并返回id;
          $endDatas['company_id'] = $this->userBaseInfo->company_id;
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
      $datas['time'] = date('YmdHi',strtotime($datetime.":00"));
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
			}else{
        $error = $model->getErrors();
        foreach ($error as $key => $value) {
          $this->ajaxReturn(-1,[],$value[0]);
          // $this->error($value[0]);
          exit;
        }
      }

    }



    /**
     * 格式化列表
     * @param  array  $datas      列表数据
     * @param  array  $fields    显示字段，以数组传入，否则显示所有
     * @param  string $primaryKey 主键字段名
     * @param  string $from       来自info或wall的数据
     * @return array             返回处理后的数组数据
     */
    private function formatListDatas($datas,$fields='*',$primaryKey = 'infoid',$from='info'){
      $uArray = array('name'=>'','phone'=>'','loginname'=>'','Department'=>'','carnumber'=>'','uid'=>'','imgpath'=>'');
      if($from == 'info'){
        $uid = $this->userBaseInfo->uid;
      }
      $lists = array();

      foreach ($datas as $key => $value) {

        $valueArray = json_decode(CJSON::encode($value),true);
        if(is_array($fields)){
          foreach($fields as $field){
            $lists[$key][$field] = $valueArray[$field];
          }
        }else{
          $lists[$key] = $valueArray;
        }

        $lists[$key]['from'] = $from;
        $lists[$key]['id'] = $value->$primaryKey;
        $lists[$key]['time'] = date('Y-m-d H:i',strtotime($value->time.'00'));
        $lists[$key]['subtime'] = date('Y-m-d H:i',strtotime($value->subtime.'00'));
        // $lists[$key]['start_info'] = $value['startpid'] ? Address::model()->getDataById($value['startpid'],['addressid','addressname','latitude','longtitude','city']):array('addressname'=>'-');
        // $lists[$key]['end_info'] =  $value['endpid'] ?  Address::model()->getDataById($value['endpid'],['addressid','addressname','latitude','longtitude','city']):array('addressname'=>'-');
        // $lists[$key]['owner_info'] =  $value['carownid'] ?  CP_User::model()->getDataById($value['carownid'],['uid','name','loginname','deptid','phone','Department','carnumber']):array('name'=>'-');
        $lists[$key]['start_info']      = $value->startpid ? json_decode(CJSON::encode($value->start),true) :array('addressname'=>'-') ;

        $lists[$key]['end_info']        = $value->endpid ? json_decode(CJSON::encode($value->end),true) :array('addressname'=>'-') ;
        if($from=='info'){
          $lists[$key]['show_owner']      = $uid == $value->passengerid &&  $value->carownid  ?  1 : 0;
          $lists[$key]['passenger_info']  = $value->passengerid ? array('name'=>$value->user->name,'phone'=>$value->user->phone,'loginname'=>$value->user->loginname,'Department'=>$value->user->Department,'carnumber'=>$value->user->carnumber,'uid'=>$value->user->uid,'imgpath'=>$value->user->imgpath) : $uArray ;
          $lists[$key]['owner_info']      = $value->carownid    ? array('name'=>$value->carowner->name,'phone'=>$value->carowner->phone,'loginname'=>$value->carowner->loginname,'Department'=>$value->carowner->Department,'carnumber'=>$value->carowner->carnumber,'uid'=>$value->carowner->uid,'imgpath'=>$value->carowner->imgpath): $uArray;
          // $lists[$key]['passenger_info'] =  $value['passengerid'] ?  CP_User::model()->getDataById($value['passengerid'],['uid','name','loginname','deptid','phone']):array('name'=>'-');

        }
        if($from=='wall'){
          $lists[$key]['owner_info']      = array('name'=>$value->user->name,'phone'=>$value->user->phone,'loginname'=>$value->user->loginname,'Department'=>$value->user->Department,'carnumber'=>$value->user->carnumber,'uid'=>$value->user->uid,'imgpath'=>$value->user->imgpath);
          //取点赞数
          $lists[$key]['like_count'] = WallLike::model()->count('love_wall_ID='.$value->love_wall_ID);
          //取已坐数
          $lists[$key]['took_count'] = Info::model()->count('love_wall_ID='.$value->love_wall_ID.' and status <> 2');
        }

      }
      return $lists;
    }





}
