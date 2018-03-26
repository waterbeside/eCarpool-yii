<?php


class CommentController extends  CarpoolBaseController {
  public function init() {
		parent::init();
	}

  /**
   *
   */
  public function actionIndex()
	{
    $this->success('index');
	}

  /**
   * 空坐位评论
   */
  public function actionWall()
	{
    $wallid = $this->iRequest('wid');
    if(!$wallid){
      $this->ajaxReturn(-10001,[],'empty wid');
    }
    if(Yii::app()->request->isPostRequest){ //当post时为发表评论
      $content = $this->sPost('content');
      if(trim($content)==''){
        $this->ajaxReturn(-10001,[],'内容不能为空');
      }
      $model    = new WallReply();
      $model->uid           = $this->userBaseInfo->uid;
      $model->love_wall_ID  = $wallid;
      $model->content      = $content;
      $model->reply_time    =  date('Y-m-d H:i:s',time());
      $result = $model->save();
      // $result = false;
      if ($result) {
        $newID = $model->primaryKey;
        $this->ajaxOK('评论成功',array('id'=>$newID));
      }else{
        $this->ajaxError('发送失败');
      }
    }else{ //当get时为取得列表

      $getCount = $this->iGet('getcount');
      $num = $this->iGet('num');
      $criteria = new CDbCriteria();
      $model    = new WallReply();
      $criteria->addCondition('love_wall_ID = '.$wallid);

      $criteria->order = 'reply_time ASC';
      $criteria->with = array('user');
      $count = $model->count($criteria);
      if($num>0){
        $criteria->limit = $num;
      }
      $selectArray_user = array('Department','loginname','imgpath','name','uid');

      if($getCount){ //如果getCount为true则反回评论总数
        $this->ajaxReturn(0,array('total'=>$count));
      }
      $results = $model->findAll($criteria);
      $lists = array();
      foreach ($results as $key => $value) {

        $valueArray = json_decode(CJSON::encode($value),true);
        $lists[$key] = array(
          'content' =>$valueArray['content'],
          'id' =>$valueArray['love_wall_reply_id'],
          'time' => date("Y-m-d H:i",strtotime($valueArray['reply_time'])),
        );
        $user_array = json_decode(CJSON::encode($value->user),true);
        foreach($selectArray_user as $field_u){
          $lists[$key][$field_u] = $user_array[$field_u];
        }

      }
      unset($resulst);
      $this->ajaxReturn(0,array('lists'=>$lists,'total'=>$count));

    }

	}

}
