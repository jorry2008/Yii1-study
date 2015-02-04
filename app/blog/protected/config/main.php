<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
/*
拓展目录：
application
webroot//不可修改
ext
 */
return array(
	//定义开发程序所在目录，默认是当前访问下的protected目录
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',//就是application
	//定义拓展所在目录，默认为application.extensions//就是ext
	//'extensionPath'=>'application.extensions',
	
	/*
	 * //配置维护模式
	'catchAllRequest'=>array(
	    'offline/notice',
	    'param1'=>'value1',
	    'param2'=>'value2',
	),
	*/
	
	'name'=>'Yii Blog Demo',

	// preloading 'log' component
	'preload'=>array('log'),

	// autoloading model and component classes
	'import'=>array(
		'application.models.*',
		'application.components.*',
	),
	
	'defaultController'=>'post',
	//配置文件里可以这样配置，但如果没有对应的布局等文件，系统会自动取protected目录下的views文件
	'theme' => 'classic',
	'layout' => 'column1',//基础布局，通常不取，优先级最低
	'sourceLanguage' => 'en_us',//设置系统默认源语言
	'language'=>'zh_cn',//设置系统指定翻出语言
	
	//GII应用程序，代码生成工具，module就是一个独立的应用
	'modules'=>array(
		'gii'=>array(
			'class'=>'system.gii.GiiModule',//声明一个名为gii的模块，它的类是GiiModule。
			'password'=>'asdasd',//为这个模块设置了密码，访问Gii时会有一个输入框要求填写这个密码。
			'ipFilters'=>array('*'),// 默认情况下只允许本机访问Gii
		),
		
		//前台模块
		'frontend'=>array(
			'modules'=>array(
				'site',
				'account',
			),
		),
		
		//后台模块
		'backend'=>array(
			'modules'=>array(
				'user',
				'cms',
			),
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
			
		//这里可以指定也可以默认以优先的方式获取
		'errorHandler'=>array(
			// use 'site/error' action to display errors
			//'errorAction'=>'site/error',//默认的内容，非常专业
			'adminInfo' => '980522557@qq.com',
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
				array(
					'class'=>'CWebLogRoute',
				),
			),
		),
		
		//此组件可以任意指定当前module的主题所在位置
		'themeManager' => array(//主题工厂类管理下面所有主题类
			'class'=>'CThemeManager',
			//'themeClass'=>'ext.MyTheme',//此类的配置及初始化交由CThemeManager工厂来统一管理，默认为CTheme类
			//'basePath'=>Yii::getPathOfAlias('webroot.'.CThemeManager::DEFAULT_BASEPATH),//可以自定义所在主题目录，默认来自index.php相对路径
			//'baseUrl'=>'/'.CThemeManager::DEFAULT_BASEPATH,//web请求路径，是相对于域名的，用来获取与主题相关的资源，默认值：Yii::app()->getBaseUrl().'/'.self::DEFAULT_BASEPATH
		),
		
		//配置视图模板引擎（模板渲染器），它会将源模板文件解析为临时的php模板
		//其目的就是为了实现多种模板代码开发风格统一解析为php类型模板
		'viewRenderer'=>array(
			'class'=>'CPradoViewRenderer',//默认
			'useRuntimePath'=>true,//使用runtime存储，临时存放解析后的php模板文件
			'filePermission'=>0755,//解析后的php模板文件的操作权限
			'fileExtension'=>'.tpl',//配置模板的后缀
		),
		
		//客户端对象，就是进一步处理DOM对象模型。这是一个继承CApplicationComponent的对象，具有特殊性
		'clientScript'=>array(
			'class'=>'CClientScript',//默认
			'enableJavaScript'=>true,//是否开启javascript
			//这个属性用来替换所有指定名称的资源文件
			//If an array key is '*.js' or '*.css', the corresponding URL will replace all JavaScript files or CSS files, respectively.
			'scriptMap'=>array('jquery.js'=>'http://code.jquery.com/jquery-1.8.3.min.js'),//外部添加的脚本，包括array('*.js'=>'','*.css'=>'','path'=>'');最后整合进脚本文件
			//为框架添加新的集成包，即注册一个新的类库到框架中
			'packages'=>array(
				//引入一个slider包
				'bxslider'=>array(
					//注意：baseUrl有值则忽略basePath，其中：basePath基于目录访问，baseUrl基于http请求
					'basePath'=>'application.sourceAssets.bxslider',//基于访问路径，可以是alias路径
				 	'baseUrl'=>'http://bxslider.com/lib/',//基于请求路径，也可以基于系统自身的http请求
				 	'js'=>array(YII_DEBUG ? 'jquery.bxslider.js' : 'jquery.bxslider.min.js'),//基于basePath或baseUrl
				 	'css'=>array('jquery.bxslider.css'),//基于basePath或baseUrl
				 	'depends'=>array('jquery'),//依赖包
				 ),
			 ),
			//这是集成在yii中的核心脚本，如果想要更改框架集成，直接修改它即可
			//'corePackages'=>'',//核心包，默认取'framework/web/js/packages.php'依赖数组，更新核心集成包时才更改
			//在指定位置上注入代码片段，全局有效
			//'scripts'=>array(CClientScript::POS_HEAD=>array('abc'=>'alert("abc");')),//@var array the registered JavaScript code blocks (position, key => code)???
			'coreScriptPosition'=>CClientScript::POS_HEAD,
			'defaultScriptFilePosition'=>CClientScript::POS_HEAD,
			'defaultScriptPosition'=>CClientScript::POS_READY,
		),
	),

	// application-level parameters that can be accessed是程序级别可以访问的参数
	// using Yii::app()->params['paramName']
	'params'=>require(dirname(__FILE__).'/params.php'),//yii的params组件对象参数
);
