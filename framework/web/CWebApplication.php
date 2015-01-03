<?php
/**
 * CWebApplication class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CWebApplication extends CApplication by providing functionalities specific to Web requests.
 *
 * CWebApplication manages the controllers in MVC pattern, and provides the following additional
 * core application components:
 * <ul>
 * <li>{@link urlManager}: provides URL parsing and constructing functionality;</li>
 * <li>{@link request}: encapsulates the Web request information;</li>
 * <li>{@link session}: provides the session-related functionalities;</li>
 * <li>{@link assetManager}: manages the publishing of private asset files.</li>
 * <li>{@link user}: represents the user session information.</li>
 * <li>{@link themeManager}: manages themes.</li>
 * <li>{@link authManager}: manages role-based access control (RBAC).</li>
 * <li>{@link clientScript}: manages client scripts (javascripts and CSS).</li>
 * <li>{@link widgetFactory}: creates widgets and supports widget skinning.</li>
 * </ul>
 *
 * User requests are resolved as controller-action pairs and additional parameters.
 * CWebApplication creates the requested controller instance and let it to handle
 * the actual user request. If the user does not specify controller ID, it will
 * assume {@link defaultController} is requested (which defaults to 'site').
 *
 * Controller class files must reside under the directory {@link getControllerPath controllerPath}
 * (defaults to 'protected/controllers'). The file name and the class name must be
 * the same as the controller ID with the first letter in upper case and appended with 'Controller'.
 * For example, the controller 'article' is defined by the class 'ArticleController'
 * which is in the file 'protected/controllers/ArticleController.php'.
 *
 * @property IAuthManager $authManager The authorization manager component.
 * @property CAssetManager $assetManager The asset manager component.
 * @property CHttpSession $session The session component.
 * @property CWebUser $user The user session information.
 * @property IViewRenderer $viewRenderer The view renderer.
 * @property CClientScript $clientScript The client script manager.
 * @property IWidgetFactory $widgetFactory The widget factory.
 * @property CThemeManager $themeManager The theme manager.
 * @property CTheme $theme The theme used currently. Null if no theme is being used.
 * @property CController $controller The currently active controller.
 * @property string $controllerPath The directory that contains the controller classes. Defaults to 'protected/controllers'.
 * @property string $viewPath The root directory of view files. Defaults to 'protected/views'.
 * @property string $systemViewPath The root directory of system view files. Defaults to 'protected/views/system'.
 * @property string $layoutPath The root directory of layout files. Defaults to 'protected/views/layouts'.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web
 * @since 1.0
 */
class CWebApplication extends CApplication
{
	/**
	 * @return string the route of the default controller, action or module. Defaults to 'site'.
	 */
	public $defaultController='site';
	/**
	 * @var mixed the application-wide layout. Defaults to 'main' (relative to {@link getLayoutPath layoutPath}).
	 * If this is false, then no layout will be used.
	 */
	public $layout='main';
	/**
	 * @var array mapping from controller ID to controller configurations.
	 * Each name-value pair specifies the configuration for a single controller.
	 * A controller configuration can be either a string or an array.
	 * If the former, the string should be the class name or
	 * {@link YiiBase::getPathOfAlias class path alias} of the controller.
	 * If the latter, the array must contain a 'class' element which specifies
	 * the controller's class name or {@link YiiBase::getPathOfAlias class path alias}.
	 * The rest name-value pairs in the array are used to initialize
	 * the corresponding controller properties. For example,
	 * <pre>
	 * array(
	 *   'post'=>array(
	 *      'class'=>'path.to.PostController',
	 *      'pageTitle'=>'something new',
	 *   ),
	 *   'user'=>'path.to.UserController',
	 * )
	 * </pre>
	 *
	 * Note, when processing an incoming request, the controller map will first be
	 * checked to see if the request can be handled by one of the controllers in the map.
	 * If not, a controller will be searched for under the {@link getControllerPath default controller path}.
	 */
	public $controllerMap=array();
	/**
	 * @var array the configuration specifying a controller which should handle
	 * all user requests. This is mainly used when the application is in maintenance mode
	 * and we should use a controller to handle all incoming requests.
	 * The configuration specifies the controller route (the first element)
	 * and GET parameters (the rest name-value pairs). For example,
	 * <pre>
	 * array(
	 *     'offline/notice',
	 *     'param1'=>'value1',
	 *     'param2'=>'value2',
	 * )
	 * </pre>
	 * Defaults to null, meaning catch-all is not effective.
	 */
	public $catchAllRequest;//by jorry维护模式属性

	/**
	 * @var string Namespace that should be used when loading controllers.
	 * Default is to use global namespace.
	 * @since 1.1.11
	 */
	public $controllerNamespace;

	private $_controllerPath;
	private $_viewPath;
	private $_systemViewPath;
	private $_layoutPath;
	private $_controller;
	private $_theme;


	/**
	 * Processes the current request.
	 * It first resolves the request into controller and action,
	 * and then creates the controller to perform the action.
	 * processRequest()启动前端控制器
	 */
	public function processRequest()
	{
		//by jorry这是一个public属性，即可以通过直接外部配置，比如让程序处于维护模式，具有最高优先级
		if(is_array($this->catchAllRequest) && isset($this->catchAllRequest[0]))
		{
			$route=$this->catchAllRequest[0];
			foreach(array_splice($this->catchAllRequest,1) as $name=>$value)
				$_GET[$name]=$value;
		}
		else
		{
			//by jorry 启动request通过$this->getRequest();
			//完成url映射处理$this->getUrlManager()->parseUrl();
			$route=$this->getUrlManager()->parseUrl($this->getRequest());
		}
		$this->runController($route);//by jorry仅仅为了获取一个route，其它的参数和数据都在request对象中！！
	}

	/**
	 * Registers the core application components.
	 * This method overrides the parent implementation by registering additional core components.
	 * @see setComponents
	 */
	protected function registerCoreComponents()
	{
		parent::registerCoreComponents();//CApplication优先注册核心组件

		$components=array(
			'session'=>array(
				'class'=>'CHttpSession',
			),
			'assetManager'=>array(
				'class'=>'CAssetManager',
			),
			'user'=>array(
				'class'=>'CWebUser',
			),
			'themeManager'=>array(
				'class'=>'CThemeManager',
			),
			'authManager'=>array(
				'class'=>'CPhpAuthManager',
			),
			'clientScript'=>array(
				'class'=>'CClientScript',
			),
			'widgetFactory'=>array(
				'class'=>'CWidgetFactory',
			),
		);

		$this->setComponents($components);
	}

	/**
	 * @return IAuthManager the authorization manager component
	 */
	public function getAuthManager()
	{
		return $this->getComponent('authManager');
	}

	/**
	 * @return CAssetManager the asset manager component
	 */
	public function getAssetManager()
	{
		return $this->getComponent('assetManager');
	}

	/**
	 * @return CHttpSession the session component
	 */
	public function getSession()
	{
		return $this->getComponent('session');
	}

	/**
	 * @return CWebUser the user session information
	 */
	public function getUser()
	{
		return $this->getComponent('user');
	}

	/**
	 * Returns the view renderer.
	 * If this component is registered and enabled, the default
	 * view rendering logic defined in {@link CBaseController} will
	 * be replaced by this renderer.
	 * @return IViewRenderer the view renderer.
	 */
	public function getViewRenderer()
	{
		return $this->getComponent('viewRenderer');
	}

	/**
	 * Returns the client script manager.
	 * @return CClientScript the client script manager
	 */
	public function getClientScript()
	{
		return $this->getComponent('clientScript');
	}

	/**
	 * Returns the widget factory.
	 * @return IWidgetFactory the widget factory
	 * @since 1.1
	 */
	public function getWidgetFactory()
	{
		return $this->getComponent('widgetFactory');
	}

	/**
	 * @return CThemeManager the theme manager.
	 */
	public function getThemeManager()
	{
		return $this->getComponent('themeManager');
	}

	/**
	 * @return CTheme the theme used currently. Null if no theme is being used.
	 */
	public function getTheme()
	{
		//这样做，可以在一次进程当中多次调用主题时，重复使用这个属性
		if(is_string($this->_theme))
		{
			//返回的是一个主题管理对象管理的一个主题对象
			$this->_theme=$this->getThemeManager()->getTheme($this->_theme);
		}
		return $this->_theme;
	}

	/**
	 * @param string $value the theme name
	 */
	public function setTheme($value)
	{
		$this->_theme=$value;
	}

	/**
	 * Creates the controller and performs the specified action.
	 * @param string $route the route of the current request. See {@link createController} for more details.
	 * @throws CHttpException if the controller could not be created.
	 */
	public function runController($route)
	{
		//返回指定的“控制器对象”和action方法的id
		if(($ca=$this->createController($route))!==null)
		{
			list($controller,$actionID)=$ca;
			//备份原来的controller对象，执行当前controller对象
			$oldController=$this->_controller;
			$this->_controller=$controller;
			//fb($controller);
			//到目前为止，有多少个与应用的结构有关系的对象：webapp,moduleapp,controller三个对象
			$controller->init();
			$controller->run($actionID);
			$this->_controller=$oldController;
		}
		else
			throw new CHttpException(404,Yii::t('yii','Unable to resolve the request "{route}".',
				array('{route}'=>$route===''?$this->defaultController:$route)));
	}

	/**
	 * Creates a controller instance based on a route.
	 * The route should contain the controller ID and the action ID.
	 * It may also contain additional GET variables. All these must be concatenated together with slashes.
	 *
	 * This method will attempt to create a controller in the following order:
	 * <ol>
	 * <li>If the first segment is found in {@link controllerMap}, the corresponding
	 * controller configuration will be used to create the controller;</li>
	 * <li>If the first segment is found to be a module ID, the corresponding module
	 * will be used to create the controller;</li>
	 * <li>Otherwise, it will search under the {@link controllerPath} to create
	 * the corresponding controller. For example, if the route is "admin/user/create",
	 * then the controller will be created using the class file "protected/controllers/admin/UserController.php".</li>
	 * </ol>
	 * @param string $route the route of the request.
	 * @param CWebModule $owner the module that the new controller will belong to. Defaults to null, meaning the application
	 * instance is the owner.
	 * @return array the controller instance and the action ID. Null if the controller class does not exist or the route is invalid.
	 * <pre>
	 * 完胜
	 * 综合分析：
	 * 这是一个就对当前环境创建一个正确的控制器对象并返回方法id
	 * 它解决了如下文件和url的组织模式：
	 * 1.r=模块
	 * 2.r=模块/控制器
	 * 3.r=模块/控制器/方法
	 * 4.r=模块1/模块2/控制器1/控制器2
	 * 5.r=模块1/模块2/控制器1/控制器2/方法
	 * ......
	 * <strong>它负责根据url及规则找到指定的控制器类，
	 * 并实例化返回控制器对象</strong>
	 * </pre>
	 */
	public function createController($route,$owner=null)
	{
		if($owner===null)
		{
			$owner=$this;
			//fb($owner);//webapp对象
		}
		else
		{
			//by jorry
			//fb($owner);//生成新的gii模块对象
			//fb($route);//default/index/,生成新的module路由
		}
		
		if(($route=trim($route,'/'))==='')
		{
			$route=$owner->defaultController;//处理所有程序r=''的情况，即默认controller和action
		}
		
		$caseSensitive=$this->getUrlManager()->caseSensitive;
		
		$route.='/';
		while(($pos=strpos($route,'/'))!==false)
		{
			$id=substr($route,0,$pos);//取gii，下次取出default
			if(!preg_match('/^\w+$/',$id))
			{
				return null;//直接挂了，路由错误不能为非字母字符串
			}
			
			if(!$caseSensitive)
			{
				$id=strtolower($id);
			}
			
			$route=(string)substr($route,$pos+1);//取 default/index/
			
			//这个路径，只有当把所有的module都嵌套完了才会真正处理最终的控制器和控制器方法
			if(!isset($basePath))  // first segment
			{
				//与module无关，任何开始处理控制器之前都先处理controllerMap，以实现优先处理配置文件的业务逻辑
				//即主论是webapp还是moduleapp,都是完全一样的逻辑，即mian文件中对module的配置其实与mian对webapp的总配置完全一样！！！
				//controllerMap[$id]是用来配置当前应用对象的子模块用的
				if(isset($owner->controllerMap[$id]))//webapplication没有定义controllerMap
				{
					//返回the controller instance and the action ID
					return array(
						Yii::createComponent($owner->controllerMap[$id],$id,$owner===$this?null:$owner),//??
						$this->parseActionParams($route),
					);
				}

				//转移应用对象由webapp转到module
				if(($module=$owner->getModule($id))!==null)//此module是由配置文件main指定的，当然也可以由module对象绕过webapp自身配置
				{
					//重新递归，以新的应用对象来执行当前程序
					//route => default/index/
					//也就是这种机制使得module可以无限制嵌套
					return $this->createController($route,$module);
				}

				//moduleapp对象所在路径+controllers
				$basePath=$owner->getControllerPath();// C:\xampp\htdocs\test\yii\framework\gii\controllers
				$controllerID='';
			}
			else
			{
				//$controllerID可以实现:控制器1/控制器2/控制器3，这种url和目录的对应关系
				$controllerID.='/';
			}
			
			$className=ucfirst($id).'Controller';
			$classFile=$basePath.DIRECTORY_SEPARATOR.$className.'.php';
			
			//命名空间$owner->controllerNamespace的用法
			//即在webapp或moduleapp中直接指定控制器所在命名空间的位置即可，
			//参阅：http://www.yiiframework.com/doc/guide/1.1/zh/basics.namespace#namespace
			if($owner->controllerNamespace!==null)
			{
				$className=$owner->controllerNamespace.'\\'.$className;
			}
			
			if(is_file($classFile))
			{
				//为什么要class_exists($className,false)不检查autoload()，很重要，每个module已经实现了命名空间的概念
				//那么这个类必须是在当前空间下，如在gii下，否则可能出现同名类而引起错误！！
				//这样做后，我们每创建一个webapp或者moduleapp都将创建一个对应的应用名称空间，好处你懂得。
				if(!class_exists($className,false))
				{
					require($classFile);
				}
				
				if(class_exists($className,false) && is_subclass_of($className,'CController'))//测定是否为CController的子类，这便是硬性规范
				{
					//准备开始处理控制器了
					$id[0]=strtolower($id[0]);//首字母小写
					//实例化一个控制器
					return array(
						//所有的控制器在new的时候，如果是module都会带上moduleapp对象，使得控制器完成初始化
						new $className($controllerID.$id,$owner===$this?null:$owner),//模块是一个全新的app对象
						$this->parseActionParams($route),
					);
				}
				return null;
			}
			
			//没有找到默认位置的控制器类
			//这就实现了多层级控制器路径的访问模式，如控制器1/控制器2....
			$controllerID.=$id;
			$basePath.=DIRECTORY_SEPARATOR.$id;
// 			fb($controllerID);//default
// 			fb($basePath);//C:\xampp\htdocs\test\yii\framework\gii\controllers\default
		}
	}

	/**
	 * Parses a path info into an action ID and GET variables.
	 * @param string $pathInfo path info
	 * @return string action ID
	 */
	protected function parseActionParams($pathInfo)
	{
		if(($pos=strpos($pathInfo,'/'))!==false)
		{
			$manager=$this->getUrlManager();
			$manager->parsePathInfo((string)substr($pathInfo,$pos+1));
			$actionID=substr($pathInfo,0,$pos);
			return $manager->caseSensitive ? $actionID : strtolower($actionID);
		}
		else
			return $pathInfo;
	}

	/**
	 * @return CController the currently active controller
	 */
	public function getController()
	{
		return $this->_controller;
	}

	/**
	 * @param CController $value the currently active controller
	 */
	public function setController($value)
	{
		$this->_controller=$value;
	}

	/**
	 * @return string the directory that contains the controller classes. Defaults to 'protected/controllers'.
	 */
	public function getControllerPath()
	{
		if($this->_controllerPath!==null)
			return $this->_controllerPath;
		else
			return $this->_controllerPath=$this->getBasePath().DIRECTORY_SEPARATOR.'controllers';
	}

	/**
	 * @param string $value the directory that contains the controller classes.
	 * @throws CException if the directory is invalid
	 */
	public function setControllerPath($value)
	{
		if(($this->_controllerPath=realpath($value))===false || !is_dir($this->_controllerPath))
			throw new CException(Yii::t('yii','The controller path "{path}" is not a valid directory.',
				array('{path}'=>$value)));
	}

	/**
	 * @return string the root directory of view files. Defaults to 'protected/views'.
	 */
	public function getViewPath()
	{
		if($this->_viewPath!==null)
			return $this->_viewPath;
		else
			return $this->_viewPath=$this->getBasePath().DIRECTORY_SEPARATOR.'views';
	}

	/**
	 * @param string $path the root directory of view files.
	 * @throws CException if the directory does not exist.
	 */
	public function setViewPath($path)
	{
		if(($this->_viewPath=realpath($path))===false || !is_dir($this->_viewPath))
			throw new CException(Yii::t('yii','The view path "{path}" is not a valid directory.',
				array('{path}'=>$path)));
	}

	/**
	 * @return string the root directory of system view files. Defaults to 'protected/views/system'.
	 */
	public function getSystemViewPath()
	{
		if($this->_systemViewPath!==null)
			return $this->_systemViewPath;
		else
			return $this->_systemViewPath=$this->getViewPath().DIRECTORY_SEPARATOR.'system';
	}

	/**
	 * @param string $path the root directory of system view files.
	 * @throws CException if the directory does not exist.
	 */
	public function setSystemViewPath($path)
	{
		if(($this->_systemViewPath=realpath($path))===false || !is_dir($this->_systemViewPath))
			throw new CException(Yii::t('yii','The system view path "{path}" is not a valid directory.',
				array('{path}'=>$path)));
	}

	/**
	 * @return string the root directory of layout files. Defaults to 'protected/views/layouts'.
	 */
	public function getLayoutPath()
	{
		if($this->_layoutPath!==null)
			return $this->_layoutPath;
		else
			return $this->_layoutPath=$this->getViewPath().DIRECTORY_SEPARATOR.'layouts';
	}

	/**
	 * @param string $path the root directory of layout files.
	 * @throws CException if the directory does not exist.
	 */
	public function setLayoutPath($path)
	{
		if(($this->_layoutPath=realpath($path))===false || !is_dir($this->_layoutPath))
			throw new CException(Yii::t('yii','The layout path "{path}" is not a valid directory.',
				array('{path}'=>$path)));
	}

	/**
	 * The pre-filter for controller actions.
	 * This method is invoked before the currently requested controller action and all its filters
	 * are executed. You may override this method with logic that needs to be done
	 * before all controller actions.
	 * @param CController $controller the controller
	 * @param CAction $action the action
	 * @return boolean whether the action should be executed.
	 */
	public function beforeControllerAction($controller,$action)
	{
		return true;
	}

	/**
	 * The post-filter for controller actions.
	 * This method is invoked after the currently requested controller action and all its filters
	 * are executed. You may override this method with logic that needs to be done
	 * after all controller actions.
	 * @param CController $controller the controller
	 * @param CAction $action the action
	 */
	public function afterControllerAction($controller,$action)
	{
	}

	/**
	 * Do not call this method. This method is used internally to search for a module by its ID.
	 * @param string $id module ID
	 * @return CWebModule the module that has the specified ID. Null if no module is found.
	 */
	public function findModule($id)
	{
		if(($controller=$this->getController())!==null && ($module=$controller->getModule())!==null)
		{
			do
			{
				if(($m=$module->getModule($id))!==null)
					return $m;
			} while(($module=$module->getParentModule())!==null);
		}
		if(($m=$this->getModule($id))!==null)
			return $m;
	}

	/**
	 * Initializes the application.
	 * This method overrides the parent implementation by preloading the 'request' component.
	 */
	protected function init()
	{
		parent::init();
		// preload 'request' so that it has chance to respond to onBeginRequest event.
		$this->getRequest();
	}
}
