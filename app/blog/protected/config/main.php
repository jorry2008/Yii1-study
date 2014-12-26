<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
	//定义开发程序所在目录，默认是当前访问下的protected目录
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'Yii Blog Demo',

	// preloading 'log' component
	'preload'=>array('log'),

	// autoloading model and component classes
	'import'=>array(
		'application.models.*',
		'application.components.*',
	),
		
//典型的只读web属性
// 	'themeManager' => array(
// 		'themeNames'=>array(),//主题列表
// 		'basePath'=>'',//绝对路径，默认："WebRootPath/themes".
// 		'baseUrl'=>'',//更改主题的路径，默认：/WebRoot/themes，这个是相对的web路径
// 	),

	'defaultController'=>'post',
	'theme' => 'classic',
	
	//GII应用程序，代码生成工具，module就是一个独立的应用
	'modules'=>array(
		'gii'=>array(
			'class'=>'system.gii.GiiModule',//声明一个名为gii的模块，它的类是GiiModule。
			'password'=>'asdasd',//为这个模块设置了密码，访问Gii时会有一个输入框要求填写这个密码。
			'ipFilters'=>array('*'),// 默认情况下只允许本机访问Gii
		),
	),

	// application components
	'components'=>array(
		'user'=>array(
			// enable cookie-based authentication
			'stateKeyPrefix'=>'x_',//身份验证cookie名称【一个专用cookie】
			//是否启用基于cookie的登录
			'allowAutoLogin'=>true,
			//持久层是否延续最新时间，使cookie保持最新
			'autoRenewCookie'=>true,
			//未验证者的名字
			'guestName'=>'游客',
			//用户登录的rul
			'loginUrl'=>array('/site/login'),
			//重点，当开始基于cookie登录时，这个数组就是初始化系列化持久的cookie初始值
			//即专为身份验证的cookie配置专用的cookie对象，以下就是对象的初始化参数
			'identityCookie'=>array('path' => '/'),//可以实现如子站点同时登录
			//登录的有效时间，也叫验证的有效时间，如果没有设置则以seesion过期时间为准
			//即，用户在登录状态下未操作的时间间隔有效为authTimeout，超过就退出
			'authTimeout'=>null,
			//设置一个绝对的登出时间
			'absoluteAuthTimeout'=>null,
			//为true时，flash消息在每页或当前都会触发消息更新，为false时，只有当getFlash才会触发
			'autoUpdateFlash'=>true,
		),
		/*
		'db'=>array(
			'connectionString' => 'sqlite:protected/data/blog.db',
			'tablePrefix' => 'tbl_',
		),
		*/
		// uncomment the following to use a MySQL database
		'db'=>array(
			'connectionString' => 'mysql:host=localhost;dbname=turen',
			'emulatePrepare' => true,
			'username' => 'root',
			'password' => '123456',
			'charset' => 'utf8',
			'tablePrefix' => 'tbl_',
		),
		'errorHandler'=>array(
			// use 'site/error' action to display errors
			'errorAction'=>'site/error',
		),
		/*
		'urlManager'=>array(
			'urlFormat'=>'path',
			'rules'=>array(
				'post/<id:\d+>/<title:.*?>'=>'post/view',
				'posts/<tag:.*?>'=>'post/index',
				'<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
			),
		),
		*/
		'session'=>array(
			'class' => 'CDbHttpSession',
			'sessionName'=>'blog',
			//'cookieMode'=>'only',
			'timeout'=>3600,
			'connectionID' => 'db',
			'sessionTableName' => '{{session}}',
		),
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning',
				),
				// uncomment the following to show log messages on web pages
				/*
				array(
					'class'=>'CWebLogRoute',
				),
				*/
			),
		),
	),

	// application-level parameters that can be accessed
	// using Yii::app()->params['paramName']
	'params'=>require(dirname(__FILE__).'/params.php'),
);