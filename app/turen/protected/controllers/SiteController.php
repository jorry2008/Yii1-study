<?php

class SiteController extends Controller
{
	public $layout='column1';
	
	/**
	 * Declares class-based actions.
	 */
	/*
	 * public function actions() { return array( // captcha action renders the
	 * CAPTCHA image displayed on the contact page 'captcha'=>array(
	 * 'class'=>'CCaptchaAction', 'backColor'=>0xFFFFFF, ), // page action
	 * renders "static" pages stored under 'protected/views/site/pages' // They
	 * can be accessed via: index.php?r=site/page&view=FileName 'page'=>array(
	 * 'class'=>'CViewAction', ), 'test'=>array(
	 * 'class'=>'application.controllers.actionTest.TestAction',
	 * 'name'=>'testXYZ', ), ); }
	 */
	
	public function actions() {
		return array(
			'action1'=>array(
	 		    	'class'=>'application.controllers.post.UpdateAction',
 				 	'property1'=>'value100',
 				 ),
			
			
			//注意：每个元素都只有处理两个子元素class和pro2.action1
			'pro3.'=>array(//一个requestActionId只有两部分组成
				'class'=>'application.controllers.PostController',
				//'pro2.action1'=>'Classfile',//如果是字符串，则是一个要加载的附加类
				'pro2.action1'=>array('property1'=>'value100000jorry'),//如果是参数，则这个参数是最终执行action的参数对
			),
		);
	}

	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError()
	{
	    if($error=Yii::app()->errorHandler->error)
	    {
	    	if(Yii::app()->request->isAjaxRequest)
	    		echo $error['message'];
	    	else
	        	$this->render('error', $error);
	    }
	}

	/**
	 * Displays the contact page
	 */
	public function actionContact()
	{
		$model=new ContactForm;
		if(isset($_POST['ContactForm']))
		{
			$model->attributes=$_POST['ContactForm'];
			if($model->validate())
			{
				$headers="From: {$model->email}\r\nReply-To: {$model->email}";
				mail(Yii::app()->params['adminEmail'],$model->subject,$model->body,$headers);
				Yii::app()->user->setFlash('contact','Thank you for contacting us. We will respond to you as soon as possible.');
				$this->refresh();
			}
		}
		$this->render('contact',array('model'=>$model));
	}

	/**
	 * Displays the login page
	 */
	public function actionLogin()
	{
		if (!defined('CRYPT_BLOWFISH')||!CRYPT_BLOWFISH)
			throw new CHttpException(500,"This application requires that PHP was compiled with Blowfish support for crypt().");

		$model=new LoginForm;

		// if it is ajax validation request
		if(isset($_POST['ajax']) && $_POST['ajax']==='login-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}

		// collect user input data
		if(isset($_POST['LoginForm']))
		{
			$model->attributes=$_POST['LoginForm'];
			// validate user input and redirect to the previous page if valid
			if($model->validate() && $model->login())
				$this->redirect(Yii::app()->user->returnUrl);
		}
		// display the login form
		$this->render('login',array('model'=>$model));
	}

	/**
	 * Logs out the current user and redirect to homepage.
	 */
	public function actionLogout()
	{
		Yii::app()->user->logout();
		$this->redirect(Yii::app()->homeUrl);
	}
}
