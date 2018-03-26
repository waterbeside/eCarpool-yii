<?php

/**
 * This is the model class for table "love_wall_like".

 */
class WallLike extends CActiveRecord {



	/**
	 * @return string the associated database table name
	 */
	public function tableName(){
		return 'love_wall_like';
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

}
