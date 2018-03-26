<?php

/*Nim*/

class NimController extends CarpoolBaseController {





  protected $NIM_OBJ ;
  protected $nimSetting; /*= array(
    //网易云信分配的账号，请替换你在管理后台应用下申请的Appkey
    'appKey' => '2632a5c9b931d5bff981699373190909',
    //网易云信分配的账号，请替换你在管理后台应用下申请的appSecret
    'appSecret' => '2a4229b1da20',
  );*/


  public function init() {
    parent::init();
    require(Yii::app()->basePath.'/extensions/NIM/Nim.php');
    $this->nimSetting = Yii::app()->params['nimSetting'];
    $this->NIM_OBJ = new Nim($this->nimSetting['appKey'],$this->nimSetting['appSecret']);
    // $this->NIM_OBJ = new Nim($this->nimSetting['appKey'],$this->nimSetting['appSecret'],'fsockopen');
  }



  /**
   * 创健用户
   * @param  [type] $phone [description]
   * @param  [type] $code  [description]
   * @param  [type] $usage [description]
   * @return [type]        [description]
   */
  public function actionCreate_user(){
    exit;
    $limit = $this->iGet('limit');
    $page = $this->iGet('page');

    // http://carpoolchina.test/carpool/nim/create_user
    $limit = $limit > 0 ? $limit :10 ;
    $page = $page  ? $page : 1 ;
    $connection = Yii::app()->carpoolDb;
    $sql = "SELECT u.* FROM (SELECT uid FROM event_log GROUP BY uid) AS t LEFT JOIN user AS u ON t.uid = u.uid WHERE im_md5password IS NULL AND u.uid IS NOT NULL  ORDER BY uid LIMIT  ".$limit;
    $datas= $connection->createCommand($sql)->query()->readAll();

    // var_dump(count($datas));
    // echo "<br />";
    // exit;
    // exit;
    $list_ok = [];
    $list_already = [];
    $list_error = [];
    if(!count($datas)>0){
      exit("ok all");
    }
    if(is_array($datas)){
      $lastID =  '';
      foreach ($datas as $key => $value) {
        $lastID = $value['uid'];
      }

      foreach ($datas as $key => $value) {
        $icon = $value['imgpath'] ? Yii::app()->params['avatarPath'].$value['imgpath'] : Yii::app()->params['avatarPath'].'im/default.png' ;
        $datas[$key]['icon'] = $icon;
        $rs = $this->NIM_OBJ->createUserId($value['loginname'],$value['name'],'',$icon);
        if(is_array($rs)){
          var_dump($rs);
          if($rs['code']==200){
            $status = CP_User::model()->updateByPk($value['uid'],array('im_md5password'=>$rs['info']['token']));

            echo($value['uid']."_".$value['name'].":is ok");
            echo "<br />";
            $list_ok[] = $value['uid'];
            if($lastID== $value['uid'] ){
              $this->success("ok",$this->createUrl("/carpool/nim/create_user",array("page"=>$page+1)),1);
            }
          }else if($rs['code']==414){

            $rs_r = $this->NIM_OBJ->updateUserToken($value['loginname']);
            if(is_array($rs_r)){
              var_dump($rs_r);
              if($rs_r['code']==200){
                $status = CP_User::model()->updateByPk($value['uid'],array('im_md5password'=>$rs_r['info']['token']));
                echo($value['uid']."_".$value['name'].":is redo ok");

              }else{
                echo($value['uid']."_".$value['name'].":is already");
                $list_already[] = $value['uid'];
              }
              if($lastID== $value['uid']){
                $this->success("ok",$this->createUrl("/carpool/nim/create_user",array("page"=>$page+1)),1);
              }
            }
          }else{
            echo($value['uid']."_".$value['name'].":is error");
            $list_error[] = $value['uid'];
            if($lastID== $value['uid'] ){
              $this->success("ok",$this->createUrl("/carpool/nim/create_user",array("page"=>$page+1)),1);
            }

          }
        }
        echo "<br />";

      }
      // $this->success("ok",$this->createUrl("/carpool/nim/create_user",array("page"=>$page+1)),1);


    }


    // return  $rs;
  }


  //查看用主有名片
  public function actionTest_user(){
    $uid = $this->iGet('uid');
    if(!$uid){
      $this->error("id error");
    }
    $user = CP_User::model()->findByPk($uid);
    if(!$user){
      $this->error("user in not exist");
    }

    $rs_r = $this->NIM_OBJ->getUinfos([$user['loginname']]);
    $this->ajaxReturn(0,array('user'=>$rs_r));

  }

  //为前100建群
  public function actionTest_create_group(){
    $firstday = "201712010000";
    $lastday =  "201802010000";
    $sql_where = "AND  time >=  ".$firstday." AND time < ".$lastday."";
    $connection = Yii::app()->carpoolDb;

    $sql_info_pa = "SELECT * from (SELECT count(passengerid) as count_num , passengerid as uid FROM info WHERE  carownid <> '' AND carownid is not null AND status <> 2 ".$sql_where."   GROUP BY passengerid  LIMIT 100 ) as  t ORDER BY count_num DESC ";
    $datas_info_pa= $connection->createCommand($sql_info_pa)->query()->readAll();

    $sql_info_co = "SELECT * from (SELECT count(carownid) as count_num , carownid as uid FROM info WHERE  passengerid <> '' AND passengerid is not null AND status <> 2  ".$sql_where." GROUP BY carownid  LIMIT 100 ) as  t ORDER BY count_num DESC";
    $datas_info_co= $connection->createCommand($sql_info_co)->query()->readAll();

    $sql_wall_co = "SELECT * from (SELECT count(carownid) as count_num , carownid as uid FROM info WHERE  passengerid <> '' AND passengerid is not null AND status <> 2  ".$sql_where." GROUP BY carownid  LIMIT 100 ) as  t ORDER BY count_num DESC";
    $datas_wall_co= $connection->createCommand($sql_wall_co)->query()->readAll();

    var_dump($datas_info_co);

  }

  //创建占位用户
  public function actionTest_create_placeholder(){

    // $rs_01 = $this->NIM_OBJ->createUserId('PH_F272E017046D1B26','联系人','{"isPlaceholder":1}'); //0c135863f6e62b881aeb5e94b02a45c3
    // $rs_02 = $this->NIM_OBJ->createUserId('PH_2C2351E0B9E80029','微信好友','{"isPlaceholder":1}');//d5e7bd9b6e207e7c7982dcc172284c05
    // $rs_03 = $this->NIM_OBJ->createUserId('PH_7E88149AF8DD32B2','Facebook好友','{"isPlaceholder":1}'); //16ca249e652c96d495e6730d74be339d


    // var_dump($rs_01);
    // var_dump($rs_02);

  }


  //生成im帐号
  public function actionCreate_imid(){
    $loginname = $this->sGet('loginname');
    $userData  =  CP_User::model()->find(" loginname = '$loginname'");

    if($userData){
      $imid       = $userData['im_id'] ? $userData['im_id'] : $loginname ;
      $imid       = strtolower($imid);
      $icon       = $userData['imgpath'] ? Yii::app()->params['avatarPath'].$value['imgpath'] : Yii::app()->params['avatarPath'].'im/default.png' ;
      $rs         = $this->NIM_OBJ->createUserId($imid,$userData['name'],'',$icon);
      if($rs['code']==200){
        $status = CP_User::model()->updateByPk($userData['uid'],array('im_md5password'=>$rs['info']['token'],'im_id'=>$imid));
        $this->ajaxReturn(0,$rs);
      }
      if($rs['code']==414){
        $rs_u         = $this->NIM_OBJ->updateUserToken($imid);
        if($rs_u['code']==200){
          $status = CP_User::model()->updateByPk($userData['uid'],array('im_md5password'=>$rs_u['info']['token'],'im_id'=>$imid));
          $this->ajaxReturn(0,$rs_u);
        }else{
          $this->ajaxReturn(-1);
        }
      }
    }else{
      $this->ajaxReturn(-1,'','no this user');
    }

    // $rs = $this->NIM_OBJ->createUserId('get0179335','陆剑飞');
    // $status = CP_User::model()->updateByPk($value['uid'],array('im_md5password'=>$rs['info']['token']));
    // $rs = $this->NIM_OBJ->updateUserToken('get0179335');
    // var_dump($rs);

  }

  public function actionP_test(){

    // var_dump($atr);
  }

  public function actionGet_groupdata(){
    $group_ids = $this->sRequest('group');
    if(is_string($group_ids)){
      if(trim($group_ids)==''){
        $this->ajaxReturn(-10001,[],'group empty');
        exit;
      }
      if(strpos($group_ids,'[')!==false){
        $group_ids = preg_replace('/\[||\]/i','',$group_ids);
      }
      $group_ids = explode(',',$group_ids);
    }

    if(is_array($group_ids)){
      $group_ids = $this->arrayUniq(array_filter($this->trimArray($group_ids))); //去电话号除空制和重复值。
      // var_dump($phones);
    }else{
      $this->ajaxReturn(-10002,[],'group error');
      exit;
    }
    if(!$group_ids || trim($group_ids[0])==''){
      $this->ajaxReturn(-10001,[],'group empty');
      exit;
    }
    $groupData = $this->NIM_OBJ->queryGroup($group_ids);
    if(!$groupData['code']){
      $owner = $groupData['tinfos'][0]['owner'];
      var_dump($owner);
    }else{
      var_dump($groupData);
    }
    // var_dump($groupData);
    // echo("<br />");
    // echo("<br />");
    // echo("<br />");
    // echo("<br />");
    // var_dump($groupData['tinfos'][0]['owner']);
    exit;
  }

}
