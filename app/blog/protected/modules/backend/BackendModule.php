<?php
/**
 * 
 * @author xia.q
 *
 */
class BackendModule extends CWebModule
{
	
	public function init()
	{
		//导入后台模块相关组件和模型
		$this->setImport(array(
			'backend.models.*',
			'backend.components.*',
		));
		
		//设置后台模板
		Yii::app()->theme = 'Tadmin';//目前后台只准备一套模板
		Yii::app()->homeUrl = Yii::app()->createUrl('backend/manage/default/index');
		
		//独立配置后台模块初始信息
		$this->layout = 'column-21';//独立模块的布局文件
		$this->defaultController = 'default';//独立模块中的默认控制器
	}
	
	
	public function beforeControllerAction($controller, $action)
	{
		if(parent::beforeControllerAction($controller, $action))
		{
			$assetManager = Yii::app()->assetManager;
			$clientScript = Yii::app()->clientScript;
			//$clientScript->registerMetaTag($content,$name=null,$httpEquiv=null,$options=array(),$id=null)
			//$clientScript->registerLinkTag($relation=null,$type=null,$href=null,$media=null,$options=array())
			
			//初始化加载bootstrap框架，已经依赖了yii的jquery库
			$bootstrap = Yii::app()->bootstrap;
			
			$assetsThemeUrl = $assetManager->publish(Yii::app()->theme->basePath.DIRECTORY_SEPARATOR.'assets', true, -1, true);//强制复制
			$clientScript->registerCssFile($assetsThemeUrl.'/css/'.'bootstrap-theme.css');
			//$clientScript->registerCssFile($assetsThemeUrl.'/css/'.'non-responsive.css');
			$clientScript->registerCssFile($assetsThemeUrl.'/css/'.'web.css');
			$clientScript->registerScriptFile($assetsThemeUrl.'/js/'.'web.js');

			//设置Meta
			$clientScript->registerMetaTag(Yii::app()->language,'language');
			$clientScript->registerMetaTag('text/html',null,'Content-Type',array('charset'=>Yii::app()->charset));
			$clientScript->registerMetaTag('IE=edge',null,'X-UA-Compatible');
			//无响应设计
			//$clientScript->registerMetaTag('width=device-width, initial-scale=1.0, user-scalable=no','viewport');
			$clientScript->registerMetaTag('webkit','renderer');
			$clientScript->registerMetaTag('jorry 980522557@qq.com','author');
			
			//导入全局bootstrap插件
			//$bootstrap->registerAssetCss();
			
			
			
			
			//其余的js插件，在使用时动态导入
			/*
			$clientScript->registerCoreScript('jquery')
				->registerCoreScript('jquery.ui')
				->registerScriptFile($bsAssetsUrl.'/js/bootstrap.min.js',CClientScript::POS_END)
				->registerScript('tooltip',
						"$('[data-toggle=\"tooltip\"]').tooltip();
						$('[data-toggle=\"popover\"]').tooltip()"
						,CClientScript::POS_READY);
			
			*/
			
			return true;
		}
		else
			return false;
	}
}
