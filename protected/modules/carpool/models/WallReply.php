<?php

/**
 * This is the model class for table "love_wall_like".

 */
class WallReply extends CActiveRecord {



	/**
	 * @return string the associated database table name
	 */
	public function tableName(){
		return 'love_wall_reply';
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
	      'user' => array(self::BELONGS_TO, 'CP_User', '' ,'on'=>'t.uid = u.uid','alias'=>'u','select'=>'name,loginname,phone,deptid,Department,carnumber,imgpath'),

	  );
	}
}
