<?php

/**
 * carpool的用户列表.

 */
class CP_User extends CActiveRecord {


		const ROLE_UNCHECKED = 0;
		const ROLE_CHECKED = 1;
		const ROLE_ORGANIZER = 2;
		const ROLE_DELEGATE = 3;
		const ROLE_ADMINISTRATOR = 4;


		/**
		* @return array validation rules for model attributes.
		*/
		public function rules()
		{
			// NOTE: you should only define rules for those attributes that
			// will receive user inputs.
			return array(

				array('deptid,loginname,name,sex,phone,md5password,im_id,im_md5password,company_id,is_active,carnumber,nativename,home_address_id,company_address_id','safe'),
			);
		}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'user';
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


	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function getDataById($id,$field=array()) {
      $results = $this->findByPk($id);
      $data = json_decode(CJSON::encode($results),true);
      if(empty($field)){
        return $data;
      }else{
        $data_r = array();
        foreach ($field as $key => $value) {
          if(isset($data[$value])){
            $data_r[$value] = $data[$value];
          }
        }
        return $data_r;
      }
	}

	public function checkPhoneUnique(){

	}

}
