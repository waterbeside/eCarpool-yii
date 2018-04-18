<?php

class CarpoolModule extends CWebModule {
	public function init() {
		Yii::app()->setComponents(array(
				// 'user'=>array(
				//  'class'=>'CarpoolUser',
				//  'allowAutoLogin'=>true,
				//  'loginUrl' => array('carpool/publics/login'),
				//  'stateKeyPrefix'=>'carpool_user',//session前缀
			 // ),
			 'carpoolDb'=>array(
					'class'=>'system.db.CDbConnection',
					'connectionString'=>'mysql:host=localhost;dbname=carpool',
					// 	'pdoClass'=>'QueryCheckPdo',
		 			'emulatePrepare'=>true,
					'username'=>'root',
 		 			'password'=>'123456',
					'charset'=>'utf8mb4',
		 			// 'enableParamLogging'=>YII_DEBUG,
		 			// 'enableProfiling'=>YII_DEBUG,
		 			'schemaCachingDuration'=>DEV ? 0 : 10800,

		 		),
				'JWT' => array(
						'class' => 'ext.jwt.JWT',
						'key' => 'jwtkey', //设置 jwt的key
				),
		));

		$this->setImport(array(
			'carpool.models.*',
			'carpool.forms.*',
			'carpool.components.*',
		));

		Yii::app()->setParams(array(
			'nimSetting'=> array(
				//网易云信分配的账号，请替换你在管理后台应用下申请的Appkey
				'appKey' => ' ',
				//网易云信分配的账号，请替换你在管理后台应用下申请的appSecret
				'appSecret' => ' ',
			),
			//头像的url路径
			'avatarPath'=>'',

		));
		// $this->setViewPath(Yii::getPathOfAlias('application.views.carpool'));
		Yii::app()->errorHandler->errorAction = '/carpool/default/error';
		Yii::app()->language = 'zh_cn';

	}

	public function beforeControllerAction($controller, $action) {
		return parent::beforeControllerAction($controller, $action);
	}
}
