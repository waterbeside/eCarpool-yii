<?php

/**
 * This is the configuration for generating message translations
 * for the Yii framework. It is used by the 'yiic message' command.
 */
return array(
	'sourcePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
	'messagePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'messages',
	'languages' => array(
		'zh_cn',
		'zh_tw',
	),
	'fileTypes' => array(
		'php'
	),
	'overwrite' => true,
	'exclude' => array(
		'.svn',
		'.gitignore',
		'yiilite.php',
		'yiit.php',
		'/messages',
		'/statistics',
		'/vendor',
	),
);
