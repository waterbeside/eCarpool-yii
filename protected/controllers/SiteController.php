<?php

class SiteController extends CController {

	public function filters() {
		return array(
			'accessControl',
		
		);
	}

	public function accessRules() {
		return array(

		);
	}

	/**
	 * Declares class-based actions.
	 */
	public function actions() {
		return array_merge(parent::actions(), array(
			// captcha action renders the CAPTCHA image displayed on the contact page
			'captcha'=>array(
				'class'=>'CCaptchaAction',
				'foreColor'=>0x6091ba,
				'backColor'=>0xFFFFFF,
				'testLimit'=>1,
			),
			// page action renders "static" pages stored under 'protected/views/site/pages'
			// They can be accessed via: index.php?r=site/page&view=FileName
			'page'=>array(
				'class'=>'CViewAction',
			),
		));
	}

	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionIndex() {
		exit;
	}

	/**
	 * This is the action to handle external exceptions.
	 */

	public function actionError() {
		if ($error = Yii::app()->errorHandler->error) {
			if (Yii::app()->request->isAjaxRequest) {
				echo $error['message'];
			} else {
				$this->title = 'Error ' . $error['code'];
				$this->pageTitle = array($error['code'] === 404 ? 'Not found' : 'Something goes wrong');
				$this->render('error', $error);
			}
		} else {
			throw new CHttpException(500);
		}
	}

}
