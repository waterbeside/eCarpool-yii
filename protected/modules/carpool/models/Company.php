<?php

/**
 * This is the model class for table "info".

 */
class Company extends CActiveRecord {



	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'company';
	}

  /**
 * @return array validation rules for model attributes.
 */
  public function rules()
  {
  	// NOTE: you should only define rules for those attributes that
  	// will receive user inputs.
  	return array(

      array('company_name,admin_telephone,admin_name,create_date,country,ad_words,logo_path,short_name,status','safe'),
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


}
