<?php

/*服务控制器*/

class StatementController extends BaseController {

  protected $companynames = array(
    '东莞中集' => 'CIMC',
    '深圳中集' => 'CIMC',
    '中集远望谷' => 'CIMC',
    'AEA' => 'XJE',
    'AEC' => 'XJE',
    'CJE' => 'XJE',
    'TPE' => 'XJE',
    'XJE' => 'XJE',
    'GLE' => 'GLE',
    'GLS' => 'GLE',
    'GLG' => 'GLE',
    'CEK' => '常州宁波',
    'CEG' => '常州宁波',
    'FEG' => '常州宁波',
    'NBO' => '常州宁波',
    'TEG' => '常州宁波',
    'EAP' => 'GET',
    'EBD' => 'GET',
    'EE' => 'GET',
    'GEG' => 'GET',
    'GEK' => 'GET',
    'GES' => 'GET',
    'GET' => 'GET',
    'GEW' => 'GET',
    'R and D' => 'GET',
    'TDC' => 'GET',
    'YMG' => 'GET',
    'GWH' => '桂林政府',
    'GM' => '李宁',
    'EEL' => 'EEL',
    '北元化工' => '北元化工',
    '康远' => '康远',
    'censtar' => '正星科技',

  );


  public function init() {
    parent::init();
  }

  public function actionIndex() {

    if(Yii::app()->getRequest()->isAjaxRequest){
      $this->ajaxOK('success', array());
    }else{
      $this->renderPartial('index',array());
    }

  }

  /**
   * 当月活跃用户列表
   * @return [type] [description]
   */
  public function actionScore_lists(){
    $month      = $this->sRequest('month');
    $admin      = $this->sRequest('admin');
    $page       = $this->iRequest('page');
    $pagesize   = $this->iRequest('pagesize');
    $isJson   = $this->iRequest('json');
    $recache   = $this->iRequest('recache');
    $page       = $page ? $page : 1;
    $pagesize   = $pagesize ? $pagesize : 2000;
    $yearMonth = empty($month) ? date("Y-m") : $month;
    $period = $this->getMonthPeriod($yearMonth.'-01',"YmdHi");
    $uids = array();
    $connection = Yii::app()->carpoolDb;
    $isGrads = strtotime($yearMonth) >= strtotime("2018-02") ? 0 :  1 ;
    $isGrads = strtotime($yearMonth) >= strtotime("2018-03") ? 2 : $isGrads;

    //取出缓存 :如果有缓存，直接反缓存，以免重复计算
    $returnDatas = array('month'=>$yearMonth,'admin'=>$admin,'companynames'=>$this->companynames,'pagesize'=>$pagesize);
    $cacheDatasKey= "statement_".$yearMonth."_datas";
    $cacheDatas = Yii::app()->cache->get($cacheDatasKey);

    if($cacheDatas && !$recache){
      $returnDatas['lists']=$cacheDatas;
      if($isJson){
        // $this->addTemp($cacheDatas);
        echo json_encode($cacheDatas);
        exit;
      }
      $this->renderPartial('score_lists',$returnDatas);
      exit;
    }

    //取出缓存 :活跃用户ID数组
    $cacheKey= "statement_".$yearMonth."_activeUser_ids";
    $cacheUidsData_o = Yii::app()->cache->get($cacheKey);
    if($cacheUidsData_o && !$recache){
      $uids = $cacheUidsData_o;
      // var_dump($uids);
    }else{
      $sql['get_month_info_uids'] = "SELECT DISTINCT infoid , carownid, passengerid, status FROM info
        WHERE  time >=  ".$period[0]." AND time < ".$period[1]."
      ";
      $datas['get_month_info_uids'] = $connection->createCommand($sql['get_month_info_uids'])->query()->readAll();
      foreach ($datas['get_month_info_uids'] as $key => $value) {
        if($value['passengerid']){
          $uids[] = $value['passengerid'];
        }
        if($value['carownid']){
          $uids[] = $value['carownid'];
        }
      }
      $sql['get_month_wall_uids'] = "SELECT DISTINCT love_wall_ID, carownid,status FROM love_wall
        WHERE  time >=  ".$period[0]." AND time < ".$period[1]."
      ";
      $datas['get_month_wall_uids'] = $connection->createCommand($sql['get_month_wall_uids'])->query()->readAll();
      foreach ($datas['get_month_wall_uids'] as $key => $value) {
        if($value['carownid']){
          $uids[] = $value['carownid'];
        }
      }
      $uids = $this->arrayUniq($uids);
      sort($uids);
      Yii::app()->cache->set($cacheKey, $uids ,900);

    }
    unset($datas);



    //从info表取约车情况
    $sql['get_month_info'] = "SELECT DISTINCT t.infoid, t.love_wall_ID , t.carownid, t.passengerid, t.status, t.time, t.subtime,
    	start.addressname as start_addressname,
    	start.latitude as start_latitude,
    	start.longtitude as start_longtitude,
    	end.addressname as end_addressname,
    	end.latitude as end_latitude,
    	end.longtitude as end_longtitude
    FROM
      info as t
    LEFT JOIN
    	address as start
    ON
    	t.startpid = start.addressid
    LEFT JOIN
    	address as end
    ON
      t.endpid = end.addressid
    WHERE
      t.status <> 2
      AND t.time >=  ".$period[0]." AND t.time < ".$period[1]."
    ";
    $datas['get_month_info'] = $connection->createCommand($sql['get_month_info'])->query()->readAll();

    //从love_wall表取空座位情况
    $sql['get_month_wall'] = "SELECT DISTINCT   t.love_wall_ID , t.carownid, t.status, t.time, t.subtime,
    	start.addressname as start_addressname,
    	start.latitude as start_latitude,
    	start.longtitude as start_longtitude,
    	end.addressname as end_addressname,
    	end.latitude as end_latitude,
    	end.longtitude as end_longtitude
    FROM
      love_wall as t
    LEFT JOIN
    	address as start
    ON
    	t.startpid = start.addressid
    LEFT JOIN
    	address as end
    ON
      t.endpid = end.addressid
    WHERE
      t.status <> 2
      AND t.time >=  ".$period[0]." AND t.time < ".$period[1]."
    ";
    $datas['get_month_wall'] = $connection->createCommand($sql['get_month_wall'])->query()->readAll();


    //查出所有活跃用户
    // $page_start = ($page-1)*$pageSize;
    $sql['get_month_active_users'] = "SELECT DISTINCT uid,company_id,companyname,sex,phone,name,loginname,carnumber,Department FROM user
      WHERE  uid in('".implode("','",$uids)."')
    ";
    $datas['get_month_active_users'] = $connection->createCommand($sql['get_month_active_users'])->query()->readAll();
    // var_dump(count($datas['get_month_active_users']));
    $listDatas = [];

    $temp_row_u = [] ;// 记录已经执行过的用户 ID
    foreach ($datas['get_month_active_users'] as $key => $value) {

      $value['test'] = '' ;
      $totals['wall_all'] = 0; //发布的总空座位
      $totals['carowner_all'] = 0;
      $totals['carowner_passengers'] = 0;
      $totals['carowner_passenger_empty'] = 0;
      $totals['carowner_passenger_has'] =0;
      $totals['passenger_all'] = 0;
      $totals['passenger_picked'] = 0;
      $totals['passenger_unpicked'] = 0;

      $temp_row['carowner_passengers'] = []; //记录的司机的有客行程。
      $temp_row['carowner_passengers_no_wall'] = []; //记录司机有客行程，但无必空位的
      $temp_row['love_wall_ID_in_wall'] = []; //记录存在于wall表的 love_wall_ID
      $temp_row['love_wall_ID_in_info'] = []; //记录存在于info表的 love_wall_ID

      $temp_row['passenger_carowner_has_wallID'] = []; //记录乘客有love_wall_ID的行程
      $temp_row['passenger_carowner_no_wallID'] = []; // 记录乘客无love_wall_ID但有司机的行程


      $temp_row['passenger_unpicked_time'] = []; // 记录乘客无被搭的行程
      $temp_row['info_has_carowner_time'] = []; // 记录需求 有司机时，并无wallID 的出发时间。
      $temp_row['info_has_carowner_has_wallID_time'] = []; // 记录需求 有司机时，并有wallID 的出发时间。


      $temp_row['info_has_passenger_time'] = []; // 记录司机 有客时，的出发时间。
      $temp_row['info_has_passenger_time_no_wall'] = []; // 记录司机 有客时，的出发时间。

      $temp_row_u[] = $value['uid'];

      $rate = isset($this->companynames[$value['companyname']]) ? ( $this->companynames[$value['companyname']] == 'GET' ? 1 : 2 ) : 0 ;
      // $rate = isset($this->companynames[$value['companyname']]) && $this->companynames[$value['companyname']] == 'GET' ? 1 : 2 ;

      $listDatas[$key] = $value;
      //历遍空座位
      foreach ($datas['get_month_wall'] as $wKey => $wValue) {

        if($wValue['carownid'] == $value['uid'] && $wValue['status'] != 2 ){
          $temp_row['love_wall_ID_in_wall'][] = $wValue['love_wall_ID'];
          $totals['wall_all'] += 1;  //发布的总空座位
        }
        if($wValue['status'] == 2 || $wValue['carownid'] == $value['uid']){
          unset($datas['get_month_wall'][$wKey]); //删除已读行，以立减少下次循环次数
        }
      }
      $totals['carowner_all'] = $totals['wall_all'];


      //历遍info数据
      foreach ($datas['get_month_info'] as $iKey => $iValue) {
        // $iValue['xTime'] = substr($iValue['time'],0,11); //只算到时间分钟的十位数
        $iValue['xTime'] = $iValue['time'];

        //查出当司机时的乘客数；
        if($iValue['carownid'] == $value['uid'] && $iValue['status'] != 2 ){
          if(!empty($iValue['love_wall_ID']) ){
            if(!in_array($iValue['love_wall_ID'],$temp_row['love_wall_ID_in_info'])){
              $temp_row['love_wall_ID_in_info'][] = $iValue['love_wall_ID'];
              if(in_array($iValue['love_wall_ID'],$temp_row['love_wall_ID_in_wall'])){
                  $totals['carowner_passenger_has'] +=1;
              }
            }
          }
          if(!empty($iValue['love_wall_ID'])){ //如果存在 love_wall_ID
            if(!in_array($iValue['love_wall_ID']."_".$iValue['passengerid'],$temp_row['carowner_passengers']) && !in_array($iValue['passengerid']."_t:".$iValue['xTime'],$temp_row['info_has_passenger_time'])){
              $temp_row['carowner_passengers'][] = $iValue['love_wall_ID']."_".$iValue['passengerid'];
              $temp_row['info_has_passenger_time'][] = $iValue['passengerid']."_t:".$iValue['xTime'];

              $totals['carowner_passengers'] += 1;
              if(in_array($iValue['passengerid']."_t:".$iValue['xTime'],$temp_row['info_has_passenger_time_no_wall'])){
                $totals['carowner_passengers'] -= 1;
                $totals['carowner_all'] -=1;
              }
            }
          }else{ //查出没有发布空位，但接受约车需求的次数
            // $temp_row['info_has_passenger_time_no_wall'][]
            if(!in_array($iValue['passengerid']."_t:".$iValue['xTime'],$temp_row['info_has_passenger_time_no_wall']) && !in_array($iValue['passengerid']."_t:".$iValue['xTime'],$temp_row['info_has_passenger_time'])){
              $temp_row['info_has_passenger_time_no_wall'][] = $iValue['passengerid']."_t:".$iValue['xTime'];
              $totals['carowner_passengers'] += 1;
              $totals['carowner_all'] +=1;
            }
          }
        }


        //查出当乘客时的情况
        if($iValue['passengerid'] == $value['uid'] && $iValue['status'] != 2 ){
          // $value['test'] =1;
          if(!empty($iValue['love_wall_ID'])){ //如果存在 love_wall_ID
            if(!in_array($iValue['love_wall_ID'],$temp_row['passenger_carowner_has_wallID'])){
              $temp_row['passenger_carowner_has_wallID'][] = $iValue['love_wall_ID'];
              $temp_row['info_has_carowner_has_wallID_time'][] = $iValue['xTime'];
              $totals['passenger_picked'] += 1;
              $totals['passenger_all'] += 1;
              if(in_array($iValue['xTime'],$temp_row['info_has_carowner_time'])){
                $totals['passenger_all'] -=1;
                $totals['passenger_picked'] -=1;
              }
            }
          }else{ //查出不存在love_wall_ID时
            if(!in_array($iValue['xTime'],$temp_row['info_has_carowner_has_wallID_time'])){
              if(!empty($iValue['carownid'])){ //查出有司机的情况
                if(!in_array($iValue['carownid']."_t:".$iValue['xTime'],$temp_row['passenger_carowner_no_wallID'])){
                  $temp_row['passenger_carowner_no_wallID'][] = $iValue['carownid']."_t:".$iValue['xTime'];
                  $temp_row['info_has_carowner_time'][] = $iValue['xTime'];

                  $totals['passenger_picked'] += 1;
                  $totals['passenger_all'] +=1;

                  if(in_array($iValue['xTime'],$temp_row['passenger_unpicked_time'])){
                    $totals['passenger_all'] -=1;
                    $totals['passenger_unpicked'] -=1;
                  }
                }
              }else{ //查出无司机的情况
                if(!in_array($iValue['xTime'],$temp_row['passenger_unpicked_time']) ){
                  $temp_row['passenger_unpicked_time'][] =  $iValue['xTime'];
                  $totals['passenger_unpicked']  += 1;
                  $totals['passenger_all'] +=1;
                  if(in_array($iValue['xTime'],$temp_row['info_has_carowner_time'])){
                    $totals['passenger_all'] -=1;
                    $totals['passenger_unpicked'] -=1;
                  }
                }
                unset($datas['get_month_info_uids'][$iKey]); //删除已读行，以立减少下次循环次数

              }
            }
          }
        }


      /*  if(!empty($iValue['carownid']) && $this->inArray($iValue['carownid'],$temp_row_u) && $this->inArray($iValue['passengerid'],$temp_row_u)){
          unset($datas['get_month_info_uids'][$iKey]); //删除已读行，以立减少下次循环次数
        }*/

      }
      // 查出无乘客的空座位数
      // $temp_row['love_wall_ID_in_info']   = $this->arrayUniq($temp_row['love_wall_ID_in_info']);
      // var_dump($totals['passenger_picked']);exit;


      $totals['carowner_passenger_empty'] = $totals['wall_all'] - $totals['carowner_passenger_has'] ;

      //汇总

      $listDatas[$key]['companyname_format'] = isset($this->companynames[$value['companyname']]) ? $this->companynames[$value['companyname']] : 'others' ;
      $listDatas[$key]['monthStatement'] = $totals;
      /*if($value['uid'] == 7240){
        var_dump($totals);
      }*/

      $listDatas[$key]['monthScores'] = $this->computeScore($totals,$rate,$isGrads);
      unset($value);
      unset($temp_row);
      unset($totals);
    }


    unset($datas);
    unset($temp_row_u);
    $cacheExpiration = strtotime($month) >= strtotime(date('Y-m',strtotime("now"))) ? 900 : 3600*24*60 ;

    Yii::app()->cache->set($cacheDatasKey, $listDatas ,$cacheExpiration);
    $returnDatas['lists']=$listDatas;
    // var_dump($returnDatas['lists'][0]);
    // exit;
    if($isJson){
      echo json_encode($listDatas);
      exit;
    }
    $this->renderPartial('score_lists',$returnDatas);
    exit;

  }


  /**
   * 用户分数报表
   * @return [type] [description]
   */
  public function actionUser_score(){
    $uid        = $this->iRequest('uid');
    $loginname  = $this->sRequest('loginname');
    $month      = $this->sRequest('month');
    $admin      = $this->sRequest('admin');
    $form       = $this->iGet('form');
    $isGrads = strtotime($month) >= strtotime("2018-02") ? 0 :  1 ;
    $isGrads = strtotime($month) >= strtotime("2018-03") ? 2 : $isGrads;

    if($form){
      $this->renderPartial('user_score_form',array());
      exit;
    }

    if($uid){
      $user = CP_User::model()->findByPk($uid);
    }elseif($loginname){
      $user = CP_User::model()->findByAttributes(array(
        'loginname'=>$loginname,
      ));
    }else{
      $this->error('请输入工号');
    }
    if(!$user){
      $this->error('用户不存在');
    }

    $monthData = $this->getTotalMonth($user['uid'],$month);

    $rate = $this->companynames[$user['companyname']] == 'GET' ? 1 : 2 ;
    $scoreData = $this->computeScore($monthData['totals'],$rate,$isGrads);

    $returnData = array(
      'scores'=>$scoreData,
      'user'=>$user,
      'admin'=>$admin,
      'month' => $month ? $month : date('Y-m'),
      'totals'=>$monthData['totals'],
      'datas'=>$monthData['datas'],

    );

    if(Yii::app()->getRequest()->isAjaxRequest){
      $this->ajaxOK('success', $returnData);
    }else{
      $this->renderPartial('user_score',$returnData);
    }
  }


  /**
   * 计算月各种情况人次统计
   * @param  integer $uid         要计算的用户id
   * @param  string  $yearMonth   要计算的年月 格式 Y-m
   * @return array
   */
  public function getTotalMonth($uid,$yearMonth='',$onlyTotal=0){


    $yearMonth = empty($yearMonth) ? date("Y-m") : $yearMonth;
    $period = $this->getMonthPeriod($yearMonth.'-01',"YmdHi");

    $connection = Yii::app()->carpoolDb;

    $totals['wall_all'] = 0; //发布的总空座位
    $totals['carowner_all'] = 0;
    $totals['carowner_passengers'] = 0;
    $totals['carowner_passenger_empty'] = 0;
    $totals['carowner_passenger_has'] =0;
    $totals['passenger_all'] = 0;
    $totals['passenger_picked'] = 0;
    $totals['passenger_unpicked'] = 0;

    $temp_row['carowner_passengers'] = []; //记录的司机的有客行程。
    $temp_row['carowner_passengers_no_wall'] = []; //记录司机有客行程，但无必空位的
    $temp_row['love_wall_ID_in_wall'] = []; //记录存在于wall表的 love_wall_ID
    $temp_row['love_wall_ID_in_info'] = []; //记录存在于info表的 love_wall_ID

    $temp_row['passenger_carowner_has_wallID'] = []; //记录乘客有love_wall_ID的行程
    $temp_row['passenger_carowner_no_wallID'] = []; // 记录乘客无love_wall_ID但有司机的行程
    $temp_row['passenger_unpicked_time'] = []; // 记录乘客无被搭的行程
    $temp_row['info_has_carowner_time'] = []; // 记录需求 有司机时，的出发时间。
    $temp_row['info_has_carowner_has_wallID_time'] = []; // 记录需求 有司机时，并有wallID 的出发时间。

    $temp_row['info_has_passenger_time'] = []; // 记录司机 有客时，的出发时间。
    $temp_row['info_has_passenger_time_no_wall'] = []; // 记录司机 有客时，的出发时间。

    /*********/
    //查出当月所有空座位
    $sql['wall_all']  = "SELECT DISTINCT love_wall_ID,
        co.uid as co_uid, co.name as co_name, co.loginname as co_loginname,
        co.Department as co_Department, co.companyname as co_companyname,
        t.carownid,
        t.time ,
        t.subtime,
        s.addressname as s_addressname,
        s.latitude as s_latitude,
        s.longtitude as s_longtitude,
        e.addressname as e_addressname,
        e.latitude as  e_latitude,
        e.longtitude as e_longtitude,
        t.status,
        (select count(infoid) from info as i where i.love_wall_ID = t.love_wall_ID AND i.status <> 2 ) as pa_num
      FROM love_wall as t
      LEFT JOIN
        user as co
      ON
        t.carownid = co.uid
      LEFT JOIN
        address as s
      ON
        t.startpid = s.addressid
      LEFT JOIN
        address as e
      ON
        t.endpid = e.addressid
      WHERE  time >=  ".$period[0]." AND time < ".$period[1]."  AND carownid = ".$uid." AND status <> 2
      ORDER BY time
    ";
    $datas['wall_all'] = $connection->createCommand($sql['wall_all'])->query()->readAll();


    foreach ($datas['wall_all'] as $key => $value) {
      $temp_row['love_wall_ID_in_wall'][] = $value['love_wall_ID'];
      $totals['wall_all'] += 1;  //发布的总空座位
      $totals['carowner_all'] = $totals['wall_all'];
    }
    //查出该用户在info表做司机的情况
    $sql['info_carowner_all'] = "SELECT DISTINCT infoid, love_wall_ID,
        co.uid as co_uid, co.name as co_name, co.loginname as co_loginname,
        co.Department as co_Department, co.companyname as co_companyname,
        pa.uid as pa_uid, pa.name as pa_name, pa.loginname as pa_loginname,
        pa.Department as pa_Department, pa.companyname as pa_companyname,
        t.carownid , t.passengerid,
        t.time ,
        t.subtime,
        start.addressname as s_addressname,
        start.latitude as s_latitude,
        start.longtitude as s_longtitude,
        end.addressname as e_addressname,
        end.latitude as  e_latitude,
        end.longtitude as e_longtitude,
        t.status
      FROM
        info  as t
      LEFT JOIN
        user as co
      ON
        t.carownid = co.uid
      LEFT JOIN
        user as pa
      ON
        t.passengerid = pa.uid
      LEFT JOIN
        address as start
      ON
        t.startpid = start.addressid
      LEFT JOIN
        address as end
      ON
        t.endpid = end.addressid
      WHERE  time >=  ".$period[0]." AND time < ".$period[1]."  AND carownid = ".$uid." AND status <> 2
      ORDER BY time , infoid
    ";
    $datas['info_carowner_all'] = $connection->createCommand($sql['info_carowner_all'])->query()->readAll();
    foreach ($datas['info_carowner_all'] as $key => $value) {
      // $iValue['xTime'] = substr($value['time'],0,11); //只算到时间分钟的十位数
      $value['xTime'] = $value['time'];

      if(!empty($value['love_wall_ID']) ){
        if(!in_array($value['love_wall_ID'],$temp_row['love_wall_ID_in_info'])){
          $temp_row['love_wall_ID_in_info'][] = $value['love_wall_ID'];
          if(in_array($value['love_wall_ID'],$temp_row['love_wall_ID_in_wall'])){
              $totals['carowner_passenger_has'] +=1;
          }
        }
      }


      if(!empty($value['love_wall_ID'])){ //如果存在 love_wall_ID
        if(!in_array($value['love_wall_ID']."_".$value['passengerid'],$temp_row['carowner_passengers']) && !in_array($value['passengerid']."_t:".$value['xTime'],$temp_row['info_has_passenger_time'])){
          $temp_row['carowner_passengers'][] = $value['love_wall_ID']."_".$value['passengerid'];
          $temp_row['info_has_passenger_time'][] = $value['passengerid']."_t:".$value['xTime'];

          $totals['carowner_passengers'] += 1;
          if(in_array($value['passengerid']."_t:".$value['xTime'],$temp_row['info_has_passenger_time_no_wall'])){
            $totals['carowner_passengers'] -= 1;
            $totals['carowner_all'] -=1;
          }
        }
      }else{ //查出没有发布空位，但接受约车需求的次数
          // $temp_row['info_has_passenger_time_no_wall'][]
          if(!in_array($value['passengerid']."_t:".$value['xTime'],$temp_row['info_has_passenger_time_no_wall']) && !in_array($value['passengerid']."_t:".$value['xTime'],$temp_row['info_has_passenger_time'])){
            $temp_row['info_has_passenger_time_no_wall'][] = $value['passengerid']."_t:".$value['xTime'];
            $totals['carowner_passengers'] += 1;
            $totals['carowner_all'] +=1;
          }
      }

    }
    //查出该用户在info表做司机的情况
    $sql['info_passenger_all'] = "SELECT DISTINCT
      infoid, love_wall_ID,
    	co.uid as co_uid, co.name as co_name, co.loginname as co_loginname,
    	co.Department as co_Department, co.companyname as co_companyname,
    	pa.uid as pa_uid, pa.name as pa_name, pa.loginname as pa_loginname,
    	pa.Department as pa_Department, pa.companyname as pa_companyname,
      t.carownid , t.passengerid,
    	t.time ,
    	t.subtime,
    	start.addressname as s_addressname,
    	start.latitude as s_latitude,
    	start.longtitude as s_longtitude,
    	end.addressname as e_addressname,
    	end.latitude as  e_latitude,
    	end.longtitude as e_longtitude,
    	t.status
      FROM
        info  as t
      LEFT JOIN
      	user as co
      ON
      	t.carownid = co.uid
      LEFT JOIN
      	user as pa
      ON
      	t.passengerid = pa.uid
      LEFT JOIN
      	address as start
      ON
      	t.startpid = start.addressid
      LEFT JOIN
      	address as end
      ON
      	t.endpid = end.addressid
      WHERE  time >=  ".$period[0]." AND time < ".$period[1]."  AND passengerid = ".$uid." AND status <> 2
      ORDER BY time , infoid
    ";

    $datas['info_passenger_all'] = $connection->createCommand($sql['info_passenger_all'])->query()->readAll();
    foreach ($datas['info_passenger_all'] as $key => $value) {
      // $value['xTime'] = substr($value['time'],0,11);
      $value['xTime'] = $value['time'];
      # code...
      if(!empty($value['love_wall_ID'])){ //如果存在 love_wall_ID
        if(!in_array($value['love_wall_ID'],$temp_row['passenger_carowner_has_wallID'])){
          $temp_row['passenger_carowner_has_wallID'][] = $value['love_wall_ID'];
          $temp_row['info_has_carowner_has_wallID_time'][] = $value['xTime'];
          $totals['passenger_picked'] += 1;
          $totals['passenger_all'] += 1;
          if(in_array($value['xTime'],$temp_row['info_has_carowner_time'])){
            $totals['passenger_all'] -=1;
            $totals['passenger_picked'] -=1;
          }
        }
      }else{ //查出不存在love_wall_ID时
        if(!in_array($value['xTime'],$temp_row['info_has_carowner_has_wallID_time'])){
          if(!empty($value['carownid'])){ //查出有司机的情况
            if(!in_array($value['carownid']."_t:".$value['xTime'],$temp_row['passenger_carowner_no_wallID'])){
              $temp_row['passenger_carowner_no_wallID'][] = $value['carownid']."_t:".$value['xTime'];
              $temp_row['info_has_carowner_time'][] = $value['xTime'];
              $totals['passenger_picked'] += 1;
              $totals['passenger_all'] +=1;
              if(in_array($value['xTime'],$temp_row['passenger_unpicked_time'])){
                $totals['passenger_all'] -=1;
                $totals['passenger_unpicked'] -=1;
              }
            }
          }else{ //查出无司机的情况
            if(!in_array($value['xTime'],$temp_row['passenger_unpicked_time']) ){
              $temp_row['passenger_unpicked_time'][] =  $value['xTime'];
              $totals['passenger_unpicked']  += 1;
              $totals['passenger_all'] +=1;
              if(in_array($value['xTime'],$temp_row['info_has_carowner_time'])){
                $totals['passenger_all'] -=1;
                $totals['passenger_unpicked'] -=1;
              }
            }
          }
        }
      }
    }
    $totals['carowner_passenger_empty'] = $totals['wall_all'] - $totals['carowner_passenger_has'] ;

    // $totals['carowner_all'] = $totals['wall_all'];

    //查出未发空座位，但搭了客的行程
    /*$sql['carowner_no_wall']  = "SELECT DISTINCT * FROM info
      WHERE  time >=  ".$period[0]." AND time < ".$period[1]."  AND carownid = ".$uid." AND status <> 2 AND ( ISNULL(love_wall_ID) OR love_wall_ID ='' )
    ";
    $datas['carowner_no_wall'] = $connection->createCommand($sql['carowner_no_wall'])->query()->readAll();
    $totals['carowner_passengers'] +=  count($datas['carowner_no_wall']);
    $totals['carowner_all'] +=  count($datas['carowner_no_wall']);

    //作为乘客
    $sql['passenger_all']  = "SELECT DISTINCT * FROM info
      WHERE  time >=  ".$period[0]." AND time < ".$period[1]."  AND passengerid = ".$uid." AND status <> 2
    ";
    $datas['passenger_all'] = $connection->createCommand($sql['passenger_all'])->query()->readAll();
    $totals['passenger_all'] = count($datas['passenger_all']);
    $totals['passenger_picked'] = 0;
    $totals['passenger_unpicked'] = 0;
    $temp_loveWallIds = [];
    foreach ($datas['passenger_all'] as $key => $value) {
      if($value['love_wall_ID']){
        if(!in_array($value['love_wall_ID'],$temp_loveWallIds)){
          $temp_loveWallIds[] = $value['love_wall_ID'];
          $totals['passenger_picked'] += 1;
        }
      }else{
        $totals['passenger_unpicked'] += 1;
      }
      // $sql_temp = "SELECT DISTINCT passengerid FROM info WHERE love_wall_ID = ".$value['love_wall_ID']." AND status <> 2 AND carownid = ".$uid."  ";

    }*/
    // var_dump($datas['passenger_all']);
    if($onlyTotal){
      unset($datas);
      return array('totals'=>$totals);
    }else{
      return array('totals'=>$totals,'datas'=>$datas);
    }

  }

  /**
   * 计算分数
   * @param  [type]  $totalsData  人次数据
   * @param  integer $rate        乘率
   * @return [type]              [description]
   */
  public function computeScore($totals,$rate=1,$isGrads=0){
    $score['total'] = 0 ;
    $score['pick'] = 0;
    $score['pick_empty'] = 0;
    $score['picked'] = 0;
    $score['picked_empty'] = 0;

    if($isGrads == 2){
      $score['pick'] = $totals['carowner_passengers']*1;
      $score['picked'] = $totals['passenger_picked']*1;
      $score['total'] = $score['pick'] + $score['picked'];
      return $score;
    }


    // print_r($totals);

    //作为司机 统计搭乘客人次的分
    if($isGrads || $rate == 2){
      if($totals['carowner_passengers']>=40){
        $score['pick'] = $totals['carowner_passengers']*2;
      }elseif($totals['carowner_passengers'] >= 20 && $totals['carowner_passengers'] < 40){
        $score['pick'] = $totals['carowner_passengers']*1.5;
      }elseif($totals['carowner_passengers']< 20){
        $score['pick'] = $totals['carowner_passengers']*1;
      }
      $score['pick_empty'] = $totals['carowner_passenger_empty']*0.5;
    }else{
      $score['pick'] = $totals['carowner_passengers']*1;
      $score['pick_empty'] = 0;
    }
    //

    //作为乘客 统计搭之分
    if($isGrads || $rate == 2 ){
      if($totals['passenger_picked']>=20){
        $score['picked'] = $totals['passenger_picked']*2;
      }elseif($totals['passenger_picked'] >= 10 && $totals['passenger_picked'] < 20){
        $score['picked'] = $totals['passenger_picked']*1.5;
      }elseif($totals['passenger_picked']< 10){
        $score['picked'] = $totals['passenger_picked']*1;
      }
      $score['picked_empty'] = $totals['passenger_unpicked']*0.5;
    }else{
      $score['picked'] = $totals['passenger_picked']*1;
      $score['picked_empty'] = 0;

    }

    $score['pick']          = $score['pick']*$rate;
    $score['pick_empty']    = $score['pick_empty']*$rate;
    $score['picked']        = $score['picked']*$rate;
    $score['picked_empty']  = $score['picked_empty']*$rate;


    $score['total'] = $score['pick'] + $score['pick_empty'] + $score['picked'] + $score['picked_empty'];

    return $score;
  }

  /*计算期间*/
  public function getMonthPeriod($date,$format = 'Y-m-d'){
    $firstday = date("Y-m-01",strtotime($date));
    $lastday = date("Y-m-d",strtotime("$firstday +1 month"));
    return array(date($format,strtotime($firstday)),date($format,strtotime($lastday)));
  }


  /***** 接口  *****/

  /**
   * count number of times 取得某段时间内（月为单位）的拼车人次数据
   */
  public function actionGet_months_notimes(){
    $month_current      = $this->sRequest('month');
    $recache            = $this->sRequest('recache');
    $nums               = $this->sRequest('nums');
    $yearMonth_current = empty($month_current) ? date("Y-m") : $month_current;

    $nums_month = $nums ? $nums : 25;
    $connection = Yii::app()->carpoolDb;

    $months = array();

    for ($i=1; $i<=$nums_month; $i++) {
       $months[] = date("Y-m",strtotime("$yearMonth_current -".($nums_month-$i)." month"));
    }

    $listData = array();
    $totalData = [
      'passengers'=>0,
      'carbon'=>0,
    ];
    foreach ($months as $key => $value) {
      $cacheDatasKey= "statement_".$value."_counts";
      $cacheDatas = Yii::app()->cache->get($cacheDatasKey);

      if($cacheDatas && !$recache  ){
        $listData[] = $cacheDatas;
      }else{

        $period = $this->getMonthPeriod($value.'-01',"YmdHi");
        $where_base =  " i.status IN(1,3)  AND carownid IS NOT NULL AND carownid <> '' AND time >=  ".$period[0]." AND time < ".$period[1]." ";
        //取得该月乘客人次
        // $from['count_p'] = "SELECT love_wall_ID FROM info as i  where  $where_base  GROUP BY carownid, passengerid, love_wall_ID, time";
        // $from = "SELECT * FROM info as i  where  i.status <> 2  AND time >=  ".$period[0]." AND time < ".$period[1]." ";
        $from['count_p'] = "SELECT distinct startpid,endpid,time,carownid,passengerid FROM info as i  where  $where_base ";
        $sql['count_p']  = "SELECT  count(*) as c
          FROM
           (".$from['count_p']." ) as p_info
        ";
        $datas['count_p'] = $connection->createCommand($sql['count_p'])->query()->readAll();


        //从info表取得非空座位的乘搭的司机数
        $from['count_c'] = "SELECT carownid FROM info as i  where  $where_base AND love_wall_ID is Null   GROUP BY carownid  , time";
        $sql['count_c']  = "SELECT  count(*) as c  FROM  (".$from['count_c']." ) as p_info ";
        $datas['count_c'] = $connection->createCommand($sql['count_c'])->query()->readAll();




        //从love_wall表取得非空座位的乘搭的司机数
        $from['count_c1'] = "SELECT love_wall_ID , (select count(infoid) from info as i where i.love_wall_ID = t.love_wall_ID AND i.status <> 2 ) as pa_num FROM love_wall as t  where  t.status <> 2  AND t.time >=  ".$period[0]." AND t.time < ".$period[1]."   ";
        $sql['count_c1']  = "SELECT  count(*) as c   FROM (".$from['count_c1']." ) as ta   WHERE pa_num > 0   ";
        $datas['count_c1'] = $connection->createCommand($sql['count_c1'])->query()->readAll();


        $listItem = array(
          "o"=> $datas['count_c'][0]['c']+$datas['count_c1'][0]['c'],
          "p"=> $datas['count_p'][0]['c'] + 0,
          "month"=> $value,
        );
        $listItem['carbon'] = $listItem['p']*7.6*2.3/10;
        $listData[] =  $listItem;
        $totalData['passengers'] +=   $datas['count_p'][0]['c'];
        $cacheExpiration = strtotime($value) >= strtotime(date('Y-m',strtotime("now"))) ? 900 : 3600*24*60 ;
        Yii::app()->cache->set($cacheDatasKey, $listItem ,$cacheExpiration);

      }

      // exit;
    }
    $totalData['carbon'] = $totalData['passengers']*7.6*2.3/10;
    $returnData= array(
      "lists"=> $listData,
      "months"=> $months,
      "total"=> $totalData
    );
    $this->ajaxReturn(0,$returnData,"success");
  }


  /**
   * 取得月排名
   */
   public function actionGet_month_ranking(){
     $type = $this->iGet('type');
     $month       = $this->sRequest('month');
     $recache     = $this->sRequest('recache');
     $month_current   = date("Y-m");

     $yearMonth   = empty($month) ? date("Y-m",strtotime("$month_current -1 month")): $month;
     $period = $this->getMonthPeriod($yearMonth.'-01',"YmdHi");
     $connection = Yii::app()->carpoolDb;

     switch ($type) {
       case 0:  //取得司机排名。
          $where = " t.status <> 2 AND carownid IS NOT NULL AND carownid <> '' AND t.time >=  ".$period[0]." AND t.time < ".$period[1]."";
          $tableAll = " SELECT carownid, passengerid ,time , MAX(infoid) as infoid FROM info as t WHERE $where GROUP BY carownid , time, passengerid "; //取得当月所有，去除拼同司机同时间同乘客的数据。
          $limit = " LIMIT 50 ";
          $sql = "SELECT u.uid, u.name, u.loginname , u.companyname , count(ta.passengerid) as num FROM ( $tableAll ) as ta LEFT JOIN user as u on ta.carownid =  u.uid  GROUP BY  carownid   ORDER BY num DESC $limit";
          $datas = $connection->createCommand($sql)->query()->readAll();
          $returnData= array(
            "lists"=> $datas,
            "month"=> $yearMonth
          );
          return $this->ajaxReturn(0,$returnData,"success");

         break;
       case 1: //取得乘客排名
         $where = " t.status <> 2 AND carownid IS NOT NULL AND carownid <> '' AND t.time >=  ".$period[0]." AND t.time < ".$period[1]."";
         $tableAll = " SELECT   passengerid ,time , MAX(infoid) as infoid , MAX(carownid) as carownid FROM info as t WHERE $where GROUP BY   time, passengerid "; //取得当月所有，去除拼同司机同时间同乘客的数据。
         $limit = " LIMIT 50 ";
         $sql = "SELECT u.uid, u.name, u.loginname , u.companyname , count(ta.infoid) as num  FROM ( $tableAll ) as ta LEFT JOIN user as u on ta.passengerid =  u.uid  GROUP BY  passengerid   ORDER BY num DESC $limit";
         $datas = $connection->createCommand($sql)->query()->readAll();
         $returnData= array(
           "lists"=> $datas,
           "month"=> $yearMonth
         );
         return $this->ajaxReturn(0,$returnData,"success");
         break;

       default:
         # code...
         break;
     }
   }

   /**
    * 取得今日拼车清单。
    */
    public function actionGet_today_info(){
      $today    = date("Y-m-d");
      $tomorrow = date("Y-m-d",strtotime("$today +1 day"));
      $period = array(date("Ymd0000",strtotime($today)),date("Ymd0000",strtotime($tomorrow)));
      $connection = Yii::app()->carpoolDb;
      // var_dump($period);
      $where = " i.status <> 2 AND carownid IS NOT NULL AND carownid <> '' AND i.time >=  ".$period[0]." AND i.time < ".$period[1]."";
      $whereIds = "SELECT MIN(ii.infoid) FROM  (select * from info as i where $where ) as ii GROUP BY ii.passengerid , ii.time    ";

      $sql = "SELECT i.infoid, i.carownid, i.passengerid, c.name as name_o, c.companyname as companyname_o,c.carnumber, p.name as name_p, p.companyname as companyname_p, i.time
        FROM info as i
        LEFT JOIN user AS c ON c.uid = i.carownid
        LEFT JOIN user AS p ON p.uid = i.passengerid
        WHERE   i.infoid in($whereIds)
        ORDER BY c.companyname DESC,i.carownid DESC
      ";
      $datas = $connection->createCommand($sql)->query()->readAll();
      if($datas!==false){
        foreach ($datas as $key => $value) {
          $datas[$key]['time'] = date("H:i",strtotime($value['time']));
          $datas[$key]['date_time'] = date("Y-m-d H:i",strtotime($value['time']));
        }
        return $this->ajaxReturn(0,['lists'=>$datas],"success");
      }else{
        return $this->ajaxReturn(-1,[],"fail");
      }


   }


   /*写入临时表*/
   public function addTemp($lists){
     $connection = Yii::app()->carpoolDb;
     echo count($lists);
     exit;
     foreach ($lists as $key => $value) {
       $str = json_encode(["pick"=>$value['monthScores']["pick"],"picked"=>$value['monthScores']["picked"]]);
       $time = date("Y-m-d H:i:s");
       $sql = "INSERT INTO temp_carpool_score_201805
       (carpool_uid, loginname, name , balance ,extra, create_time)
       VALUES
       ('".$value['uid']."', '".$value['loginname']."', '".$value['name']."' , '".$value['monthScores']['total']."' , '".$str."','".$time."');
       ";
       $command=$connection->createCommand($sql);
       $command->execute();
     }


   }


}
