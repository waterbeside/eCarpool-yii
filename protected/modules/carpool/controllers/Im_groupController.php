<?php

/*Nim*/

class Im_groupController extends CarpoolBaseController {





  protected $NIM_OBJ ;
  protected $baseInvitationUrl = "http://gitesite.net/j/i.php" ;
  protected $placeholder_users = array("ph_f272e017046d1b26","ph_2c2351e0b9e80029","ph_7e88149af8dd32b2"); //联系人，微信好友，facebook好友。
  protected $inventMsg = "{{username}}邀请你加入溢起拼车群聊组,点击链接完善资料即刻加入"; //邀请返回的信息

  protected $nimSetting;

  public function init() {
    parent::init();
    require(Yii::app()->basePath.'/extensions/NIM/Nim.php');
    $this->nimSetting = Yii::app()->params['nimSetting'];
    // $this->NIM_OBJ = new Nim($this->nimSetting['appKey'],$this->nimSetting['appSecret']);
    $this->NIM_OBJ = new Nim($this->nimSetting['appKey'],$this->nimSetting['appSecret'],'fsockopen');
  }


 //取得占位用户列表
 public function actionPlaceholder_users(){
   $Placeholder_users = $this->placeholder_users;
   $this->ajaxReturn(0,array("user_list"=>$Placeholder_users),'success');
 }

 /**
  * 查出邀请连接的相关邀请信息。
  */
 public  function actionGet_invitation(){
   $link_code = $this->sGet('link_code');
   $now           = time();
   $row  = ImGroupInvitation::model()->findByAttributes(array('link_code' => $link_code ));
   if($row){
     // $data = json_decode(CJSON::encode($row),true);
     $inviterData = CP_User::model()->findByPk($row->inviter_uid);
     $data['inviter'] = array(
       'uid'=>$inviterData->uid,
       'name'=>$inviterData->name,
       'imgpath'=>$inviterData->imgpath,
     );
     $data['status'] = $row->status;
     if($now > $row->expiration_time){
       $data['status'] = 0;
     }
     $data['type'] = $row->type;

     $this->ajaxReturn(0,$data,'success');
   }else{
     $this->ajaxReturn(-1,[],'fail');
   }
 }


 /**
  * 应用外邀请用户
  */
 public function actionExternal_invite(){
   $uid           = $this->userBaseInfo->uid;
   $owner         = $this->sRequest('owner');

   $type          = $this->iRequest('type');
   $source        = $this->iRequest('source');
   $identifier    = $this->sRequest('identifier');
   $signature     = $this->sRequest('signature');
   $group         = $this->sRequest('group');
   $duration      = $this->iRequest('duration');
   // $dev           = $this->iRequest('dev');
   $now           = time();
   $baseInvitationUrl = $this->baseInvitationUrl;
   $userData      = $this->getUser();
   $inventMsg     = str_replace("{{username}}",$userData->name,$this->inventMsg);

   if(!isset($_REQUEST['identifier'])){
     $this->ajaxReturn(-10001,[],'empty identifier');
   }

   if(!isset($_REQUEST['type'])){
     $this->ajaxReturn(-10001,[],'empty type');
   }

   if(!isset($_REQUEST['source'])){
     $this->ajaxReturn(-10001,[],'empty source');
   }

   if(!$signature && !$group){
     $this->ajaxReturn(-10001,[],'empty signature or group');
   }

   //参数分钟为单位，现改为s为单位
   if(!$duration){
     $duration = $type === 1 ? 7*24*60*60 : ($type === 0 ? 3*24*60*60 : 0);
   }else{
     $duration = $duration*60;
   }

   $expiration_time = $now + $duration;

   $link_code = $this->create_link_code(6) ;
   $connection = Yii::app()->carpoolDb;
   $returnData = array(
     'placeholder'=> $this->placeholder_users[$source],
     'url'=> $baseInvitationUrl.'?lc='.$link_code,
     'link_code'=>$link_code,
     'desc'=>$inventMsg,
   );


   // ---------- start 检查是否已有重复数据；
   $sql_where = " inviter_uid = ".$uid." ";
   $sql_where .= " AND  identifier = '".$identifier."' AND source = '".$source."' ";
   if($signature){
     $sql_where .= " AND signature = '".$signature."' ";
   }
   if($group){
     $sql_where .= " AND im_group = '".$group."' ";
   }
   $sql_check = "SELECT * FROM im_group_invitation as t WHERE ".$sql_where."";


   $data_check = ImGroupInvitation::model()->find($sql_where);

   // $connection->createCommand($sql_check)->query()->readAll();
   if($data_check){  //如果有重复，

     if(empty($data_check['link_code'])){
       $rowCount = ImGroupInvitation::model()->updateByPk($data_check['id'],array('link_code'=>$link_code,'expiration_time'=>($expiration_time)));
       // $rowCount = $connection->createCommand("UPDATE im_group_invitation SET link_code = '".$link_code."' WHERE ".$sql_where." ")->execute();
       if($rowCount){
         return $this->ajaxReturn(0,$returnData,'success');
       }else{
         return $this->ajaxReturn(-1,[],'fail');
       }
     }else{
       $rowCount = ImGroupInvitation::model()->updateByPk($data_check['id'],array('expiration_time'=>($expiration_time)));
       $returnData['url'] = $baseInvitationUrl.'?lc='.$data_check['link_code']  ;
       $returnData['link_code'] = $data_check['link_code'] ;
       return $this->ajaxReturn(0,$returnData,'success');
     }
   }
   // ---------- end 检查是否已有重复数据；

   // 创建记录；
   $model = new ImGroupInvitation();
   $model->inviter_uid  = $uid;
   $model->create_time  = $now;
   $model->link_code    = $link_code;
   $model->status       = 1;
   $model->identifier   = $identifier;
   $model->source       = $source;
   $model->type         = $type;
   $model->expiration_time     = $now + $duration;
   // $model->attributes =  array('identifier'=>$identifier);

   if($group){
     $model->im_group   = $group;
   }else if($signature){
     $model->signature  = $signature;
   }



   if($owner){  //如果传了群主id，则由服务端拉占位用户入群。
     $resNim = $this->NIM_OBJ->addIntoGroup($group,$owner,[$this->placeholder_users[$source]]);
   }else{

   }
   $result = $model->save();
   if($result){
     $this->ajaxReturn(0,$returnData,'success');
   }else{
     $this->ajaxReturn(-1,[],'fail');
   }

 }

 /**
  * 创建邀请回写群号接口。
  */
 public  function actionExternal_invite_writeback(){
   $uid           = $this->userBaseInfo->uid;
   $signature     = $this->sRequest('signature');
   $group         = $this->sRequest('group');
   $baseInvitationUrl = $this->baseInvitationUrl;
   $userData      = $this->getUser();
   $inventMsg     = str_replace("{{username}}",$userData->name,$this->inventMsg);

   if(!$signature || !$group){
     $this->ajaxReturn(-10001,[],'empty signature or group');
   }
   $where = "inviter_uid = ".$uid." AND signature='".$signature."'";

   $data = ImGroupInvitation::model()->find($where);
   if($data){
     /*$returnData = array(
       'placeholder'=> $this->placeholder_users[$data->source],
       'url'=> $baseInvitationUrl.'?lc='.$data->link_code,
       'desc'=>$inventMsg,
     );
     $update_count = ImGroupInvitation::model()->updateByPk($data['id'],array('im_group'=>$group));*/

    $update_count = ImGroupInvitation::model()->updateAll(array('im_group'=>$group),$where);
     if($update_count!==false){
       $this->ajaxReturn(0,[],'success');
     }else{
       $this->ajaxReturn(-10001,[],'update group error');
     }
   }else{
     $this->ajaxReturn(-10001,[],'row is inexistence');
   }
 }


/**
 * 把占位移出群
 */
 public function actionKick_placeholder(){
   $uid       = $this->userBaseInfo->uid;
   $owner     = $this->sRequest('owner');
   $group     = $this->sRequest('group');
   $now       = time();

   $placeholder_users = $this->placeholder_users;

   if(!$group){
     $this->ajaxReturn(-10001,[],'empty group');
   }
   if(!$owner){
     $this->ajaxReturn(-10001,[],'empty owner');
   }
   $callbackData          = array(
     'inviting'  => [1,1,1],
   );

   $inviting_count_0     = $this->check_inviting($group,0);
   if(!$inviting_count_0){
     $callbackData['inviting'][0] = 0;
     $this->NIM_OBJ->kickFromGroup($group,$owner,$placeholder_users[0]);
     // $this->NIM_OBJ->leaveFromGroup($group,$placeholder_users[0]);
   }

   $inviting_count_1     = $this->check_inviting($group,1);
   if(!$inviting_count_1){
     $callbackData['inviting'][1] = 0;
     $this->NIM_OBJ->kickFromGroup($group,$owner,$placeholder_users[1]);
     // $this->NIM_OBJ->leaveFromGroup($group,$placeholder_users[1]);
   }

   $inviting_count_2     = $this->check_inviting($group,2);
   if(!$inviting_count_2){
     $callbackData['inviting'][2] = 0;
     $this->NIM_OBJ->kickFromGroup($group,$owner,$placeholder_users[2]);
     // $this->NIM_OBJ->leaveFromGroup($group,$placeholder_users[2]);
   }

   $this->ajaxReturn(0,$callbackData);
   // kickFromGroup($tid,$owner,$member)
 }

 /**
  * 验证群是否正在邀请用户
  */
 public  function check_inviting($group,$source=false,$isMy=0){
   $now = time();
   $where     = " status = 1 AND im_group='$group'   ";
   if($source!==false){
     $where .= " AND source = $source ";
   }
   if($isMy){
     $uid = $this->userBaseInfo->uid;
     $where .= " AND inviter_uid = $uid ";
   }
   $where .= " AND expiration_time > $now ";
   // var_dump($where);
   $inviting_count     = ImGroupInvitation::model()->count($where);
   return $inviting_count;

 }


 /**
  * 创建链接唯一随机码。
  */
 public  function create_link_code($len=6){
   $link_code = strtolower($this->genRandomString($len)) ;
   $count_checkHas  = ImGroupInvitation::model()->count("link_code='".$link_code."'");
   if($count_checkHas>0){
     return $this->create_link_code($len);
   }else{
     return $link_code;
   }
 }




}
