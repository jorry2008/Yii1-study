<?php
require_once dirname(__FILE__).'/../../framework/vendors/firePHPCore/fb.php';
ob_start();

//一个全局方法
function redirectToDeniedMethod($params)
{
	fb('认证失败方法');
	fb($params);
	
	return true;
}

// change the following paths if necessary
$yii=dirname(__FILE__).'/../../framework/yii.php';
$config=dirname(__FILE__).'/protected/config/main.php';

// remove the following line when in production mode

//开启全局调试
defined('YII_DEBUG') or define('YII_DEBUG',true);

require_once($yii);
//Yii::createWebApplication($config)->run();//价于
Yii::createWebApplication($config);

//向框架中载入一个类，或者覆盖框架中的一个原有的类
//Yii::$classMap = array('id'=>'class path');

Yii::app()->run();


fb(Yii::app()->clientScript);



