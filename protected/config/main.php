<?php

Yii::setPathOfAlias('application', APP_PATH . '/protected');
Yii::import('application.components.*');

$config = array(
	'defaultController' => 'carpool/publics/index' ,
	'basePath'=>dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
	'name'=>'carpool china',
	'language'=>'zh_cn',
	// preloading 'log' component
	'preload'=>array(
		'log',
	),
	// autoloading model and component classes
	'import'=>array(
		'application.models.*',
		// 'application.models.carpool.*',
		'application.forms.*',
		'application.widgets.*',
		'application.extensions.debugtb.*',
		'application.extensions.mail.*',
	),
	'modules'=>array(
		'carpool'=>array(
			'defaultController'=>'index',
		)
	),
	// application components
	'components'=>array(

		/*'user'=>array(
			'class'=>'WebUser',
			// enable cookie-based authentication
			'allowAutoLogin'=>true,
		),*/

		


		'urlManager'=>array(
			'urlFormat'=>'path',
			'rules'=>array(
				''=>'carpool/publics/index',
				'<action:login|logout|register|forgetPassword|resetPassword|activate|reactivate|banned>'=>'site/<action>',
				'<view:about|contact|links|disclaimer|please-update-your-browser>'=>array(
					'site/page',
					'urlSuffix'=>'.html'
				),
				'<controller:\w+>'=>'<controller>/index',
				'<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
			),
			'appendParams'=>false,
			'showScriptName'=>false,
		),
		'cache'=>array(
      'class'=>'system.caching.CFileCache',
	 ),

		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning',
					'logFile'=>'application.error.log',
					'maxFileSize'=>102400,
				),
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'info, trace, profile',
					'logFile'=>'application.access.log',
					'maxFileSize'=>102400,
				),
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'pay',
					'logFile'=>'application.pay.log',
					'maxFileSize'=>102400,
				),
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'ws',
					'logFile'=>'application.ws.log',
					'maxFileSize'=>102400,
				),
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'git',
					'logFile'=>'application.git.log',
					'maxFileSize'=>102400,
				),

			),
		),


	),

	'params'=>array(


	),
);


return $config;
