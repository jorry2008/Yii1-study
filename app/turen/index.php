<?php
define('START', microtime());

$fb = $yii=dirname(__FILE__).'/../../framework/vendors/FirePHPCore/fb.php';
require_once($fb);
ob_start();//开始缓冲区

// change the following paths if necessary
$yii=dirname(__FILE__).'/../../framework/yii.php';
$config=dirname(__FILE__).'/protected/config/main.php';

// remove the following line when in production mode
defined('YII_DEBUG') or define('YII_DEBUG',true);


require_once($yii);//by jorry 引入yii.php本质就是调用框架YiiBase.php
Yii::createWebApplication($config)->run();

fb(Yii::getLogger());

// fb('以下include_paths：');
// fb(Yii::get_includePaths());

// fb('以下命名空间：');
// fb(Yii::get_aliases());

// fb('以下手动加载：');
// fb(Yii::get_imports());
/**
 * fb(Yii::get_includePaths());
 * array(
[0] =>'D:\xampp\www\me\jorryApps\app\turen\protected\components'
[1] =>'D:\xampp\www\me\jorryApps\app\turen\protected\models'
[2] =>'D:\xampp\php\PEAR'
)
 */
fb('time:'.(microtime()-START).'s');
fb('memory:'.(memory_get_usage(true)/1024/1024).'M');