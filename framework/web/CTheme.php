<?php
/**
 * CTheme class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CTheme represents an application theme.
 *
 * @property string $name Theme name.
 * @property string $baseUrl The relative URL to the theme folder (without ending slash).
 * @property string $basePath The file path to the theme folder.
 * @property string $viewPath The path for controller views. Defaults to 'ThemeRoot/views'.
 * @property string $systemViewPath The path for system views. Defaults to 'ThemeRoot/views/system'.
 * @property string $skinPath The path for widget skins. Defaults to 'ThemeRoot/views/skins'.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web
 * @since 1.0
 * 主题对象完成是：描述主题包文件的目录结构和所有相关的路径，它决定了主题包的文件组织规范。
 * 所以说，主题对象就告诉我们主题包的各种文件要怎么组织。
 * 
 * 整个主题的目录构建于，以index.php为起始的基础目录，+views+themes+layout布局+main.php主题主文件
 */
class CTheme extends CComponent
{
	private $_name;
	private $_basePath;
	private $_baseUrl;

	/**
	 * Constructor.
	 * @param string $name name of the theme
	 * @param string $basePath base theme path
	 * @param string $baseUrl base theme URL
	 */
	public function __construct($name,$basePath,$baseUrl)
	{
		$this->_name=$name;
		$this->_baseUrl=$baseUrl;
		$this->_basePath=$basePath;
	}

	/**
	 * @return string theme name
	 */
	public function getName()
	{
		return $this->_name;
	}

	/**
	 * @return string the relative URL to the theme folder (without ending slash)
	 */
	public function getBaseUrl()
	{
		return $this->_baseUrl;
	}

	/**
	 * @return string the file path to the theme folder
	 */
	public function getBasePath()
	{
		return $this->_basePath;
	}

	/**
	 * @return string the path for controller views. Defaults to 'ThemeRoot/views'.
	 * 目录来自：index.php所在同一个目录下为主题的起始目录
	 */
	public function getViewPath()
	{
		//fb($this->_basePath);exit;// C:\xampp\htdocs\test\turen\app\blog\themes\classic
		return $this->_basePath.DIRECTORY_SEPARATOR.'views';
	}

	/**
	 * @return string the path for system views. Defaults to 'ThemeRoot/views/system'.
	 */
	public function getSystemViewPath()
	{
		return $this->getViewPath().DIRECTORY_SEPARATOR.'system';
	}

	/**
	 * @return string the path for widget skins. Defaults to 'ThemeRoot/views/skins'.
	 * @since 1.1
	 */
	public function getSkinPath()
	{
		return $this->getViewPath().DIRECTORY_SEPARATOR.'skins';
	}

	/**
	 * Finds the view file for the specified controller's view.
	 * @param CController $controller the controller
	 * @param string $viewName the view name
	 * @return string the view file path. False if the file does not exist.
	 */
	public function getViewFile($controller,$viewName)
	{
		$moduleViewPath=$this->getViewPath();
		//fb($moduleViewPath);exit;//C:\xampp\htdocs\test\turen\app\blog\themes\classic\views
		if(($module=$controller->getModule())!==null)
		{
			$moduleViewPath.='/'.$module->getId();
		}
		
		//从这里看出，模板只有按照层级来处理，不能变更
// 		fb('----------');
// 		fb($viewName);
// 		fb($this->getViewPath().'/'.$controller->getUniqueId());
// 		fb($this->getViewPath());
// 		fb($moduleViewPath);
		
// 		fb($module->getId());// frontend/site
// 		fb($moduleViewPath);// C:\xampp\htdocs\test\turen\app\blog\themes\classic\views/frontend/site
// 		fb($controller->getUniqueId());// frontend/site/post
		
		return $controller->resolveViewFile($viewName,$this->getViewPath().'/'.$controller->getUniqueId(),$this->getViewPath(),$moduleViewPath);
	}

	/**
	 * Finds the layout file for the specified controller's layout.
	 * @param CController $controller the controller
	 * @param string $layoutName the layout name
	 * @return string the layout file path. False if the file does not exist.
	 * 处理具体的主题目录结构，也考虑到module的情况
	 * 优先级如下：
	 * 1.取当前控制器layout属性
	 * 2.取当前控制器父layout属性
	 * 3.取当前模块layout属性
	 * 4.取当前模块外层模块layout属性，直到尽头
	 * 5.取配置文件main的layout值
	 * 最终的原则是：谁定义了layout，那么布局文件的路径就跟谁
	 */
	public function getLayoutFile($controller,$layoutName)
	{
		$moduleViewPath=$basePath=$this->getViewPath();
		//fb($moduleViewPath);// C:\xampp\htdocs\test\turen\app\blog\themes\classic\views
		
		$module=$controller->getModule();
		//fb($module);
		
		//控制器中没有指定，则由module指定
		if(empty($layoutName))
		{
			//嵌套递归查找，直到查到完所有的嵌套module为止
			while($module!==null)
			{
				if($module->layout===false)
					return false;
				
				if(!empty($module->layout))
					break;
				
				$module=$module->getParentModule();
			}
			
			
			//module没有指定，则由main配置文件指定
			if($module===null)
			{
				$layoutName=Yii::app()->layout;
			}
			else
			{
				$layoutName=$module->layout;
				$moduleViewPath.='/'.$module->getId();//frontend/site
			}
		}
		
		//正常情况路径
		elseif($module!==null)
		{
			$moduleViewPath.='/'.$module->getId();//frontend/site
			//C:\xampp\htdocs\test\turen\app\blog\themes\classic\views/frontend/site
		}
		
// 		fb($layoutName);
// 		fb($basePath);
// 		fb($moduleViewPath);
// 		fb($moduleViewPath.'/layouts');
// 		column2
// 		C:\xampp\htdocs\test\turen\app\blog\themes\classic\views
// 		C:\xampp\htdocs\test\turen\app\blog\themes\classic\views/frontend
// 		C:\xampp\htdocs\test\turen\app\blog\themes\classic\views/frontend/layouts

		//重点，module可以自成一个独立的网站，如admin后台，布局文件等所有的都可以独立。
		return $controller->resolveViewFile($layoutName,$moduleViewPath.'/layouts',$basePath,$moduleViewPath);
	}
}







