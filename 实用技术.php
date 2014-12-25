<?php
//获取默认文件加载路径
array_unique(explode(PATH_SEPARATOR,get_include_path()));

//以类的方式注册一个文件加载器
spl_autoload_register(array('YiiBase','autoload'));
//在过程化编程中使用的是魔术方法，系统会自动注册这个加载器
function __autoload($class){}

//register_shutdown_function注册一个函数，用来捕获错误并在程序执行完成后开始动作。
function test() {
	echo "test()";
}
register_shutdown_function(array("test"));
echo "show: ";
//输出：show: test()

//标准化请求所有前端参数
if(function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc())
{
	if(isset($_GET))
		$_GET=$this->stripSlashes($_GET);
	if(isset($_POST))
		$_POST=$this->stripSlashes($_POST);
	if(isset($_REQUEST))
		$_REQUEST=$this->stripSlashes($_REQUEST);
	if(isset($_COOKIE))
		$_COOKIE=$this->stripSlashes($_COOKIE);
}







/*
yii规律：
1.控制器都包含一个_module属性，并指向自身所属的模块对象。
因为控制器是由webapp对象创建的，并把当前运行的module对象传递给它了。

2.





*/