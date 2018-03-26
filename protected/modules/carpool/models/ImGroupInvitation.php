<?php

/**
 * This is the model class for table "love_wall_like".

 */
class ImGroupInvitation extends CActiveRecord {



	/**
	 * @return string the associated database table name
	 */
	public function tableName(){
		return 'im_group_invitation';
	}


	/**
	* @return array validation rules for model attributes.
	*/
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('id,im_id,im_group,inviter_uid,identifier,status,type,source,signature,link_code,create_time,duration','safe'),
		);
	}

  /**
	 * @return CDbConnection the database connection used for this class
	 */
	public function getDbConnection() {
		return Yii::app()->carpoolDb;
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Competitions the static model class
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}

  public function relations(){
	  // NOTE: you may need to adjust the relation name and the related
	  // class name for the relations automatically generated below.
	  return array(
	      'user' => array(self::BELONGS_TO, 'CP_User', '' ,'on'=>'t.inviter_uid = u.uid','alias'=>'u','select'=>'name,loginname,phone,deptid,Department,carnumber,imgpath'),

	  );
	}
}
