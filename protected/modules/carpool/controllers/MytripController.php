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

        $connection = Yii::app()->carpoolDb;
        $info_field = "infoid,carownid,passengerid,
        startpid,start_gid,startname,  x(start_latlng) as start_lng , y(start_latlng) as start_lat ,
        endpid,end_gid,endname, x(end_latlng) as end_lng , y(end_latlng) as end_lat ,
        time,love_wall_ID,subtime,status,type,distance,cancel_time,map_type,
        d.name as d_name, d.phone as d_phone, d.loginname as d_loginname, d.Department as d_department,d.imgpath as d_imgpath, d.mobile as d_mobile,d.carnumber as d_carnumber,
        p.name as p_name, p.phone as p_phone, p.loginname as p_loginname, p.Department as p_department,p.imgpath as p_imgpath, p.mobile as p_mobile,p.carnumber as p_carnumber,
        s.addressname as s_addressname, s.latitude as s_latitude, s.longtitude as s_longtitude,
        e.addressname as e_addressname, e.latitude as e_latitude, e.longtitude as e_longtitude
        ";

        $info_limit = "";
        $info_where = "WHERE (i.passengerid = $uid OR i.carownid= $uid) AND i.status < 2 ";
        if(!$limit){
          $info_where .= "AND i.time >".(date('YmdHi',strtotime("-1 hour")))." AND i.time < 210000000000";
        }else{
          $info_limit = " LIMIT $limit";
        }
        $info_join = "
          LEFT JOIN user as d ON carownid = d.uid
          LEFT JOIN user as p ON passengerid = p.uid
          LEFT JOIN address as s ON startpid = s.addressid
          LEFT JOIN address as e ON endpid = e.addressid
        ";
        $info_sql = "SELECT $info_field  FROM info as i $info_join $info_where $info_limit";

        $info_data = $connection->createCommand($info_sql)->query()->readAll();

        /*$model_info = new Info();
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
        $criteria_info->select = array('infoid','startpid','endpid','time','subtime','cancel_time','type','status','carownid','passengerid','love_wall_ID',
        'startname','x(start_latlng) as start_lng','y(start_latlng) as start_lat',
        'endname','x(end_latlng) as end_lng','y(end_latlng) as end_lat',
        );

        $criteria_info->order = 't.time desc , t.subtime desc, infoid desc';
        $criteria_info->with = array('user','carowner','start','end');
        $results = $model_info->findAll($criteria_info);
        var_dump($info_data);exit;

        // $results = json_decode(CJSON::encode($results),true);*/
        $lists_info_o = $this->formatListDatas($info_data,"*",'infoid','info');
        // var_dump($lists_info_o);exit;

        //如果我是司机，并且存在love_wall_ID,则取消此条显示，由love_wall表读取；
        $lists_info = array();
        foreach ($lists_info_o as $key => $value) {
          if( !($value['carownid']==$uid && !empty($value['love_wall_ID'])) ){
            $lists_info[] = $value;
          }
        }
        unset($results);

        /* 读取 wall 数据表 */
        $connection = Yii::app()->carpoolDb;
        $wall_field = "love_wall_ID,carownid,seat_count
        startpid,start_gid,startname,  x(start_latlng) as start_lng , y(start_latlng) as start_lat ,
        endpid,end_gid,endname, x(end_latlng) as end_lng , y(end_latlng) as end_lat ,
        time,love_wall_ID,subtime,status,type,distance,cancel_time,map_type,
        d.name as d_name, d.phone as d_phone, d.loginname as d_loginname, d.Department as d_department,d.imgpath as d_imgpath, d.mobile as d_mobile,d.carnumber as d_carnumber,
        s.addressname as s_addressname, s.latitude as s_latitude, s.longtitude as s_longtitude,
        e.addressname as e_addressname, e.latitude as e_latitude, e.longtitude as e_longtitude
        ";

        $wall_limit = "";
        $wall_where = "WHERE (i.carownid= $uid) AND i.status < 2 ";
        if(!$limit){
          $wall_where .= "AND i.time >".(date('YmdHi',strtotime("-1 hour")))." AND i.time < 210000000000";
        }else{
          $wall_limit = " LIMIT $limit";
        }
        $wall_join = "
          LEFT JOIN user as d ON carownid = d.uid
          LEFT JOIN address as s ON startpid = s.addressid
          LEFT JOIN address as e ON endpid = e.addressid
        ";
        $wall_sql = "SELECT $wall_field  FROM love_wall as i $wall_join $wall_where $wall_limit";

        $wall_data = $connection->createCommand($wall_sql)->query()->readAll();



/*
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

*/
        $lists_wall = $this->formatListDatas($wall_data,"*",'love_wall_ID','wall');

        unset($wall_data);
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
      $userData = $this->getUser();
      $whereUser = " a.carownid=$uid OR a.passengerid=$uid ";
      $extra_info = json_decode($userData['extra_info'],true);
      $merge_ids = isset($extra_info['merge_id']) && is_array($extra_info['merge_id'])  ? $extra_info['merge_id'] : [];


      if(count($merge_ids)>0){
        foreach ($merge_ids as $key => $value) {
          $whereUser .= " OR a.carownid=$value OR a.passengerid=$value  ";
        }
      }
      // $sql = "SELECT * FROM info AS i WHERE passengerid = uid OR carownid = uid UNION ALL select * from wall as w where car"

      // 从info表取得数据
      $viewSql_u1 = "SELECT
        a.infoid, (case when a.love_wall_ID IS NULL  then '0' else a.love_wall_ID end) as  love_wall_ID ,'0' as trip_type,
        a.startpid,a.endpid,a.time,a.status, a.passengerid, a.carownid,
        a.startname, a.start_gid, x(a.start_latlng) as start_lng , y(a.start_latlng) as start_lat ,
        a.endname, a.end_gid,  x(a.end_latlng) as end_lng , y(a.end_latlng) as end_lat ,
        '0' as seat_count,
        -- '0' as liked_count,
        '0' as hitchhiked_count
      FROM
        info AS a
      WHERE
        ( $whereUser )
        AND status <>2
        AND (a.love_wall_ID is null OR  a.love_wall_ID not in (select lw.love_wall_ID  from love_wall AS lw where lw.carownid=$uid and lw.status<>2 ) )
        ORDER BY a.time desc";

      // 从love_wall表取得数据
      $viewSql_u2 = "SELECT '0' AS infoid, a.love_wall_ID AS love_wall_ID,'1' AS trip_type,
        a.startpid,a.endpid,a.time,a.status, '0' as passengerid, a.carownid,
        a.startname, a.start_gid, x(a.start_latlng) as start_lng , y(a.start_latlng) as start_lat ,
        a.endname, a.end_gid,  x(a.end_latlng) as end_lng , y(a.end_latlng) as end_lat ,
        a.seat_count,
        -- (select count(*) from love_wall_like as cl where cl.love_wall_id=a.love_wall_ID) as liked_count,
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
      $pages->pageSize = 15;
      $sql_limit = " LIMIT ".$pages->offset." , ".$pages->limit." ";

      $pageReturn  = array(
        'pageSize' => $pages->getPageSize(),
        'pageCount' => $pages->getPageCount(),
        'currentPage' =>  $pages->getCurrentPage(),
        'page' =>  $pages->getCurrentPage()+1,
        'total' =>  $pages->getItemCount(),
      );
      if(isset($_GET[$pages->pageVar]) && $_GET[$pages->pageVar] > $pages->getPageCount()){
        $datas = array();
        $this->ajaxReturn(20002,$data,'No data');

      }else{
        $whereTime = date('YmdHi',strtotime('+15 minute'));
        $sql = "SELECT
            t.infoid , t.love_wall_ID , t.time, t.trip_type ,t.startpid, t.endpid, t.time, t.status, t.passengerid, t.carownid , t.seat_count ,  t.hitchhiked_count,
            t.start_lat, t.start_lng, t.startname , t.start_gid,
            t.end_lat, t.end_lng, t.endname , t.end_gid,
            u1.uid as passenger_uid,u1.im_id as passenger_im_id, u1.name as passenger_name, u1.imgpath as passenger_imgpath, u1.sex as passenger_sex, u1.companyname as passenger_company, u1.Department as passenger_department, u1.phone as passenger_phone,u1.mobile as passenger_mobile,
            u2.uid as driver_uid,u2.im_id as driver_im_id, u2.name as driver_name, u2.imgpath as driver_imgpath, u2.sex as driver_sex, u2.companyname as driver_company, u2.Department as driver_department, u2.phone as driver_phone,u2.mobile as driver_mobile,
            a1.addressid as from_address_id,a1.addressname as from_address_name,a1.longtitude as from_longitude,a1.Latitude as from_latitude,
            a2.addressid as to_address_id,a2.addressname as to_address_name,a2.longtitude as to_longitude,a2.Latitude as to_latitude
          FROM
            ($viewSql) as t
            LEFT JOIN user u1 on t.passengerid = u1.uid
            LEFT JOIN user u2 on t.carownid = u2.uid
            LEFT JOIN address a1 on t.startpid = a1.addressid
            LEFT JOIN address a2 on t.endpid = a2.addressid
          WHERE
            t.time < $whereTime
          ORDER BY
            t.time DESC, t.infoid DESC, t.love_wall_id DESC
          $sql_limit
        ";
        $datas = $connection->createCommand($sql)->query()->readAll();
      }

      // var_dump($datas);exit;
      foreach ($datas as $key => $value) {
        $datas[$key]['time'] = date('Y-m-d H:i',strtotime($value['time'].'00'));
        if(isset($value['passenger_uid']) && in_array($value['passenger_uid'],$merge_ids)){
          $datas[$key]['passenger_uid'] = $uid;
          $datas[$key]['passengerid'] = $uid;
          $datas[$key]['passenger_name'] = $userData['name'];
        }
        if(isset($value['driver_uid']) && in_array($value['driver_uid'],$merge_ids)){
          $datas[$key]['driver_uid'] = $uid;
          $datas[$key]['carownid'] = $uid;
          $datas[$key]['driver_name'] = $userData['name'];
        }
        if(!is_numeric($value['startpid']) || $value['startpid'] < 1 ){
          $datas[$key]['from_address_id'] = $value['start_gid'];
          $datas[$key]['from_address_name'] = $value['startname'];
          $datas[$key]['from_latitude'] = $value['start_lat'];
          $datas[$key]['from_longitude'] = $value['start_lng'];
        }
        if(!is_numeric($value['endpid']) || $value['endpid'] < 1 ){
          $datas[$key]['to_address_id'] = $value['end_gid'];
          $datas[$key]['to_address_name'] = $value['endname'];
          $datas[$key]['to_latitude'] = $value['end_lat'];
          $datas[$key]['to_longitude'] = $value['end_lng'];
        }
        $datas[$key]['from_longtitude'] = $datas[$key]['from_longitude'];
        $datas[$key]['to_longtitude'] = $datas[$key]['to_longitude'];
      }

      $data = array('lists'=>$datas,'page'=>$pageReturn);
      $this->ajaxReturn(0,$data,'success');
      exit;

    }



    /**
     * 取消行程
     * @return string 返回是否成功的json格式
     */
    public function actionCancel(){
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
            $res = Info::model()->updateAll($infoNewData,'love_wall_ID='.$id.' AND passengerid ='.$uid.' AND  status <> 2 ');
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
            $res = Info::model()->updateAll($infoNewData,'love_wall_ID='.$id);
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


    /**
     * 结束行程
     * @return string 返回是否成功的json格式
     */
    public function actionFinish(){
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
          $time = strtotime($model->time.'00');
          $now           = time();
          if($now < $time){
            $this->ajaxReturn(-1,[],'行程未开始，无法结束');
          }
          // $model->time
          if($model->passengerid == $uid){ // 如果是乘客自己完成，直接变完成状态
            $datas = array(
              'status' => 3 ,
              'cancel_time' => date('YmdHi',time()),
            );
          }elseif($model->carownid == $uid){ // 如果是车主完成，重置乘客约车需求状态
            if($model->love_wall_ID > 0){ // 如果存在love_wall_ID，车主需从容座位页入口方可点完成
              $this->ajaxReturn(-1,[],'fail');
              // return $this->error('参数有误');
            }
            $datas = array(
              'status' => 3 ,
              'cancel_time' => date('YmdHi',time()),
            );
          }else{
            $this->ajaxReturn(-1,[],'无此数据');
            // return $this->error('无此数据');
          }

          $model->attributes = $datas;
          $result = $model->save();
          if($result){
            return $this->ajaxReturn(0,array(),'结束成功');
          }else{
            $this->ajaxReturn(-1,[],'结束失败，请稍候再试');
            // return $this->error('取消失败，请稍候再试');
          }
          break;


        case 'wall': // 来自love_wall表的行程
          $model = Wall::model()->findByPk($id);
          if(!$model){
            $this->ajaxReturn(-1,[],'无此数据');
          }
          $time = strtotime($model->time.'00');
          $now           = time();
          if($now < $time){
            $this->ajaxReturn(-1,[],'行程未开始，无法结束');
          }

          if( $model->carownid != $uid  ){
            //如果行程不是自己发布，查找该行程下，我是否有搭此车，有则完成。
            $infoNewData =  array(
              'status' => 3,
              'cancel_time' => date('YmdHi',time()),
            );
            Info::model()->updateAll($infoNewData,'love_wall_ID='.$id.' AND passengerid ='.$uid.' AND  status <> 2 ');
            return $this->ajaxReturn(0,array(),'结束成功');
            // $this->ajaxReturn(-1,[],'无此数据');
            exit;
          }
          $datas = array(
            'status' => 3,
            'cancel_time' => date('YmdHi',time()),
          );
          $model->attributes = $datas;
          $result = $model->save(); // 先从love_wall表取消空座位
          if($result){  //成功后，再取消info表上的乘客行程
            $infoNewData =  array(
              'status' => 3,
              'cancel_time' => date('YmdHi',time()),
            );
            Info::model()->updateAll($infoNewData,'love_wall_ID='.$id);
            return $this->ajaxReturn(0,array(),'结束成功');
            // return $this->success('取消成功');
          }else{
            $this->ajaxReturn(-1,[],'结束失败，请稍候再试');

            // return $this->error('取消失败，请稍候再试');
          }
          break;

        default:
          # code...
          break;
      }

    }



    /**
     * 取得常用路线
     */
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
      $userInfo = $this->getUser();
      $createAddress = array();
      //处理起点
      if(!$datas['startpid']){
        $startDatas = $datas['start'];
        if(!empty($startDatas['longtitude']) && !empty($startDatas['latitude']) && !empty($startDatas['addressname'])){
          $startDatas['name'] = $startDatas['addressname'];
          //如果id为空，通过经纬度查找id.无则创建一个并返回id;
          $startDatas['company_id'] = $userInfo->company_id;
          $createID = $AddressCtr->createAddressID($startDatas);
          if($createID){
            $createAddress[0] = $startDatas;
            $createAddress[0]['addressid'] = $createID;
            $datas['startpid'] = $createID;
          }else{
            $this->ajaxReturn(-1,[],Yii::t("carpool","The point of departure must not be empty"));
          }
        }else{
          $this->ajaxReturn(-1,[],Yii::t("carpool","The point of departure must not be empty"));
        }
      }

      //处理终点
      if(!$datas['endpid']){
        $endDatas = $datas['end'];
        if(!empty($endDatas['longtitude']) && !empty($endDatas['latitude']) && !empty($endDatas['addressname'])){
          $endDatas['name'] = $endDatas['addressname'];
          //如果id为空，通过经纬度查找id.无则创建一个并返回id;
          $endDatas['company_id'] = $userInfo->company_id;
          $createID = $AddressCtr->createAddressID($endDatas);
          if($createID){
            $createAddress[1] = $endDatas;
            $createAddress[1]['addressid'] = $createID;
            $datas['endpid'] = $createID;
          }else{
            $this->ajaxReturn(-1,[],Yii::t("carpool","The destination cannot be empty"));
            // $this->error('终点不能为空');
          }
        }else{
          $this->ajaxReturn(-1,[],Yii::t("carpool","The destination cannot be empty"));
          // $this->error('终点不能为空');
        }
      }

      //要提交的行程时间
      $datas['time'] = date('YmdHi',strtotime($datetime.":00"));
      if(date('YmdHi',time()) > $datas['time']){
        $this->ajaxReturn(-1,[],Yii::t("carpool","The departure time has passed. Please select the time again"));
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
        $this->ajaxReturn(-1,[],Yii::t("carpool","You have already made one trip at {time}, should not be published twice within the same time",["{time}"=>date('Y-m-d H:i',strtotime($checkData['info'][0]['time'].'00'))]));
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
        $this->ajaxReturn(-1,[],Yii::t("carpool","You have already made one trip at {time}, should not be published twice within the same time",["{time}"=>date('Y-m-d H:i',strtotime($checkData['wall'][0]['time'].'00'))]));
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
      $uArray = array('name'=>'','phone'=>'','loginname'=>'','Department'=>'','carnumber'=>'','uid'=>'','imgpath'=>'','mobile'=>'','department_id');
      if($from == 'info'){
        $uid = $this->userBaseInfo->uid;
      }
      $lists = array();

      foreach ($datas as $key => $value) {

        // $valueArray = json_decode(CJSON::encode($value),true);
        if(is_array($fields)){
          foreach($fields as $field){
            $lists[$key][$field] = $value[$field];
          }
        }else{
          $lists[$key] = $value;
        }
        // var_dump($datas);exit;

        $lists[$key]['from'] = $from;
        $lists[$key]['id'] = $value[$primaryKey];
        $lists[$key]['time'] = date('Y-m-d H:i',strtotime($value['time'].'00'));
        $lists[$key]['subtime'] = date('Y-m-d H:i',strtotime($value['subtime'].'00'));
        // $lists[$key]['start_info'] = $value['startpid'] ? Address::model()->getDataById($value['startpid'],['addressid','addressname','latitude','longtitude','city']):array('addressname'=>'-');
        // $lists[$key]['end_info'] =  $value['endpid'] ?  Address::model()->getDataById($value['endpid'],['addressid','addressname','latitude','longtitude','city']):array('addressname'=>'-');
        // $lists[$key]['owner_info'] =  $value['carownid'] ?  CP_User::model()->getDataById($value['carownid'],['uid','name','loginname','deptid','phone','Department','carnumber']):array('name'=>'-');
        $lists[$key]['start_info']      = $value['startpid'] > 0 && $value['s_addressname']?
                                            [ "addressname" => $value['s_addressname'],
                                              "latitude" => $value['s_latitude'],
                                              "longtitude" => $value['s_longtitude'],
                                              "longitude" => $value['s_longtitude'],
                                            ] :
                                            [ "addressname" => $value['startname'],
                                              "latitude" => $value['start_lat'],
                                              "longtitude" => $value['start_lng'],
                                              "longitude" => $value['start_lng'],
                                            ] ;
        $lists[$key]['end_info']      = $value['endpid'] > 0 && $value['e_addressname']?
                                            [ "addressname" => $value['e_addressname'],
                                              "latitude" => $value['e_latitude'],
                                              "longtitude" => $value['e_longtitude'],
                                              "longitude" => $value['e_longtitude'],
                                            ] :
                                            [ "addressname" => $value['endname'],
                                              "latitude" => $value['end_lat'],
                                              "longtitude" => $value['end_lng'],
                                              "longitude" => $value['end_lng'],
                                            ] ;
        $lists[$key]['owner_info']      = $value['carownid'] ?
                                            array('name'=>$value['d_name'],
                                                  'phone'=>$value['d_phone'],
                                                  'loginname'=>$value['d_loginname'],
                                                  'Department'=>$value['d_department'],
                                                  'carnumber'=>$value['d_carnumber'],
                                                  'uid'=>$value['carownid'],
                                                  'imgpath'=>$value['d_imgpath'],
                                                  'mobile'=>$value['d_mobile']
                                              ) : $uArray ;

        // $lists[$key]['end_info']        = $value['endpid'] > 0 ? json_decode(CJSON::encode($value->end),true) :array('addressname'=>'-') ;
        if($from=='info'){
          $lists[$key]['show_owner']      = $uid == $value['passengerid']  &&  $value['carownid'] > 0  ?  1 : 0;
          $lists[$key]['passenger_info']  = $value['passengerid'] ?
                                              array('name'=>$value['p_name'],
                                                    'phone'=>$value['p_phone'],
                                                    'loginname'=>$value['p_loginname'],
                                                    'Department'=>$value['p_department'],
                                                    'carnumber'=>$value['p_carnumber'],
                                                    'uid'=>$value['passengerid'],
                                                    'imgpath'=>$value['p_imgpath'],
                                                    'mobile'=>$value['p_mobile']
                                                ) : $uArray ;

          // $lists[$key]['passenger_info'] =  $value['passengerid'] ?  CP_User::model()->getDataById($value['passengerid'],['uid','name','loginname','deptid','phone']):array('name'=>'-');

        }


        if($from=='wall'){
          //取点赞数
          $lists[$key]['like_count'] = WallLike::model()->count('love_wall_ID='.$value['love_wall_ID']);
          //取已坐数
          $lists[$key]['took_count'] = Info::model()->count('love_wall_ID='.$value['love_wall_ID'].' and status <> 2');
        }

      }
      return $lists;
    }





}
