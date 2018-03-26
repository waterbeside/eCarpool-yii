<?php

/**
 * This is the model class for table "info".

 */
class Address extends CActiveRecord {

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'address';
	}



	/**
	 * 通过主键查找数据
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

	/**
	 * 通过经纬度查找数据
	 */
		public function getDataByCoord($longtitude=0,$latitude=0) {
			$criteria = new CDbCriteria();
			$criteria->addCondition('longtitude ='.$longtitude);
			$criteria->addCondition('latitude ='.$latitude);
			// $criteria->select ='*';
			$criteria->order = 'addressid asc';
			$results = $this->findAll($criteria);
			// $results = $this->findAllByAttributes(array('longtitude'=>$longtitude, 'latitude'=>$latitude));
      $data = json_decode(CJSON::encode($results),true);
			return $data;
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
