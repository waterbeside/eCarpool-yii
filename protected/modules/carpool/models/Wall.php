<?php

/**
 * This is the model class for table "info".

 */
class Wall extends CActiveRecord {



	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'love_wall';
	}

  /**
 * @return array validation rules for model attributes.
 */
  public function rules()
  {
  	// NOTE: you should only define rules for those attributes that
  	// will receive user inputs.
  	return array(
  		// array('time,startpid, endpid','required'),
  		array('startpid, endpid','numerical'),
      array('startpid,startname,endpid,endname,time,subtime,type,status,carownid,seat_count,remarks,cancel_time,company_id,update_time,distance,start_latlng,end_latlng','safe'),
    );
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
	public function lists($where=[],$pageSize=20,$orderby='time desc , love_wall_ID desc') {
		// @todo Please modify the following code to remove attributes that should not be searched.

    $criteria = new CDbCriteria();
    if(is_array($where)){
      foreach ($where as $key => $value) {
        $criteria->addCondition($value);
      }
    }
    $criteria->select ='*';
    $criteria->order = $orderby;
    $count = $this->count($criteria);
    $page = new CPagination($count);
    $page->pageSize = $pageSize;
    $page->applyLimit($criteria);
    $info = $this->findAll($criteria);
    return array('page'=>$page,'lists'=>$info);
	}

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
	      'user' => array(self::BELONGS_TO, 'CP_User', '' ,'on'=>'t.carownid = u.uid','alias'=>'u','select'=>'name,loginname,phone,deptid,Department,carnumber,imgpath,mobile'),
				'start' => array(self::BELONGS_TO, 'Address', '' ,'on'=>'t.startpid = s.addressid','alias'=>'s','select'=>'addressid,addressname,latitude,longtitude'),
				'end' => array(self::BELONGS_TO, 'Address', '' ,'on'=>'t.endpid = e.addressid','alias'=>'e','select'=>'addressid,addressname,latitude,longtitude'),
	  );
	}

}
