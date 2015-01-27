<?php
/**
 * yii是如何配置的：
 * 首先确认的是，yii的前端管制器CWebApplication对整个应用起到一个最基础的管理和设置，
 * 所有其它组件都或多或少的直接继承与应用对应的main.php配置文件。
 * 
 * 首先，那些直接在main.php的一维元素下配置的内容，我们称之为基础配置，这些配置通常会延伸到module,controller,action,model,theme等
 * 它们都有一定的层级关系，最具体的那层关系优先级最高，有时CWebApplication会提供一个默认的值，但有时是没有的，这些都是正确合理的。
 * 例如：layout布局文件，它可以被CWebApplication直接默认提供，也可以在module中单独提供，在controller重写，在控制器子类中重写，
 * 甚至在最终的action体中重写。
 * 同样CWebApplication::theme属性可以默认指定主题名，而主题启动的时间是CController::render开始，所以如果想动态控制主题对CWebApplication::theme重写
 * 那么一定要在控制这里动态修改即可，最好的方法是在自定义总控制器中设置。
 * 
 * 其次，yii框架是基于组件开发的，那么，我们同样可以在main.php配置文件中对任何组件的id进行单独配置，典型的例子就是数据库配置，
 * 和用户认证系统。但不是所有的组件都是可配置的，即使组件本身已经提供了public属性，
 * 比如：主题管理对象CThemeManager就是不可配置的，尽管它是一个工厂类，管理了CTheme主题对象，CTheme主题对象管理着所有的主题包
 * 尽量它是组件开发，也尽量它提供了属性可控，但有时候对于web系统来说，功能已经足够了，拓展已经到了极限需求。这时
 * 对应组件的id就没有给对应组件的set方法，因而，组件只读。
 * 
 * 综合所述：
 * 1.基本延伸性配置都在CWebApplication中的所有public属性中定义了，在配置文件中处于一维元素。
 * 2.可配置组件是以组件id配置在main文件中的二维数据中。
 * 3.注意在CWebApplication中不可配置组件则只提供了get方法。
 * 
 * 现在来说说组件是如何创建的？
 * 在CWebApplication中registerCoreComponents首先以数组的方式注册一套核心组件，这套组件是系统的基础配置，
 * 其次由外部提供的main配置文件的components元素提供的多维数组覆写即可生成一个全新的组件配置数组。
 * 通过执行这个数组，系统将组件依次创建并初始化。
 * 系统核心配置如下：
 * 注意：CApplication继承CModule继承CComponent
 * $components=array(
			'coreMessages'=>array(
				'class'=>'CPhpMessageSource',
				'language'=>'en_us',
				'basePath'=>YII_PATH.DIRECTORY_SEPARATOR.'messages',
			),
			'db'=>array(
				'class'=>'CDbConnection',
			),
			'messages'=>array(
				'class'=>'CPhpMessageSource',
			),
			'errorHandler'=>array(
				'class'=>'CErrorHandler',
			),
			'securityManager'=>array(
				'class'=>'CSecurityManager',
			),
			'statePersister'=>array(
				'class'=>'CStatePersister',
			),
			'urlManager'=>array(
				'class'=>'CUrlManager',
			),
			'request'=>array(
				'class'=>'CHttpRequest',
			),
			'format'=>array(
				'class'=>'CFormatter',
			),
		);

		//by jorry这里只将默认的核心组件的配置引入到CModule类的属性_componentConfig中去了
		$this->setComponents($components);
 * 
 * 具体的执行过程如下：
 * //举例：当有一个请求，我捕获了，经过检查发现这个请求来自一个被我屏蔽的国家，此时直接返回无权访问的状态。
		//by jorry这是Module的构造器，此时没有做任何工作，整个框架还未开始动作
		$this->preinit();

		//异常是以阶梯的形式一级一级的向后传递，直接最后由系统获取并处理为止
		//by jorry初始化各种handler，给捕获异常及未捕获错误注册处理句柄
		$this->initSystemHandlers();
		
		//by jorry注册核心“组件”，只引入不加载最初的_componentConfig
		$this->registerCoreComponents();
		
		//by jorry从外部更新原有的“组件”配置，形成一份新的_componentConfig
		$this->configure($config);
		
		* 通过以上核心注册和后面的main文件注册，生成一个全新的CModule::_componentConfig配置属性。
		* CModule::_components用于存储当前系统已经生成的组件对象，是个存储对象的动态容器。
		
// 		fb('当前已经生成的组件对象:');
// 		fb($this->get_components());
		fb('当前系统最终组件配置如下：');
		fb($this->get_componentConfig());
		
		$this->attachBehaviors($this->behaviors);//?
		
		//载入'preload'=>array('log'),前面只是加载了配置文件，此时是执行组件初始化
		$this->preloadComponents();//启动日志组件
		
		//by jorry核心系统已经准备就绪了，整个框架准备好后，首次执行的方法，
		//整个系统最接近开发都的那一层的根是module，即Moudle::init()这个方法是开发都可操作的最早执行的一个方法，
		//常用于加载模块相关的文件
		$this->init();
 * 
 * 
 * 以下两个方法是任何继承module或者application的类都首次执行的方法。
 * protected function preinit(){}
 * protected function init(){}
 * 
 * 
 * 通常来讲此时系统中生成的对象只有一个$this->preloadComponents();日志对象，那其它对象如何生成并使用呢？
 * 这将涉及一个yii系统的另一个技巧，__get(),__set()方法。
 * CComponent就实现了这两个魔术方法
 * 在CModule中
 * public function __get($name)
	{
		//检测是否有组件对象，或者是否有组件配置数组
		if($this->hasComponent($name))
			return $this->getComponent($name);//by jorry返回对象
		else
			return parent::__get($name);
	}
	
	public function __isset($name)
	{
		if($this->hasComponent($name))
			return $this->getComponent($name)!==null;//by jorry返回注册结果
		else
			return parent::__isset($name);
	}
 * CModule对象只开放了只读属性，__get()和检测触发__isset();
 * 两个魔术方法原理一样，我们可以这样
 * if(isset(Yii::app()->db) 
 * 	$db = Yii::app()->db;
 * 调用这一步之后，系统要走三步：
 * 1.$this->hasComponent($name);检测是否有组件对象，或者是否有组件配置数组
 * 2.$this->getComponent($name);如果有则开始返回组件对象
 * 3.通过组件$config实例化并初始化组件返回对象。
 * 
 * 
 * 当取application对象的属性时如：Yii::app()
 * 那么Yii::app()->db
 * public function getComponent($id,$createIfNull=true)//id='db'
	{
		//by jorry已经有的对象，这里就是单例返回
		if(isset($this->_components[$id])) {
			return $this->_components[$id];
		} 
		//by jorry有引入的相关对象参数，可以用来创建对象
		elseif(isset($this->_componentConfig[$id]) && $createIfNull)
		{
			$config=$this->_componentConfig[$id];
			if(!isset($config['enabled']) || $config['enabled'])
			{
				Yii::trace("Loading \"$id\" application component",'system.CModule');
				unset($config['enabled']);
				//组件的具体配置$config
				$component=Yii::createComponent($config);
				//每个组件初始化后，马上执行init();
				$component->init();
				//by jorry单例写入
				return $this->_components[$id]=$component;
			}
		}
	}
 * 先说说核心组件的实例化是如何进行的！！！
 *  'coreMessages'//CApplication::getCoreMessages();
	'db',//CApplication::getDb();
	'messages',//CApplication::getMessages();
	'errorHandler',//CApplication::getErrorHandler()
	'securityManager',//CApplication::getSecurityManager()
	'statePersister',//CApplication::getStatePersister()
	'urlManager',//CApplication::getUrlManager()
	'request',//CApplication::getRequest()
	'format',//这是一个特例
 * 我们在开发的时候通过Yii::app()->组件id,的方式获取组件对象时，系统的核心组件并没有调用上述方法，
 * 而是通过CModule::getComponent();工厂方法通过组件$config配置获得的一个组件对象。
 * 这个细节很容易与CComponent::__get()方法混淆，（其实CComponent::__get()是用来处理行为方法的，与组件无关，那些get属性名()这种方法都与组件无关）
 * 
 * 问题来了，那么与核心组件id对应的get方法有什么意思呢？
 * 首先我们要明白，为什么要核心组件，核心组件有一个特征：它即是构成yii系统本身所需要的组件，同样开发一个yii的app也需要这些组件，
 * 开发app时获取这些组件直接Yii::app()->组件id，即可，此时yii系统已经是完整的，而当在构建yii系统的时候，核心组件又必须要使用，但又不能保证这个组件功能的完整性。
 * 那么这些核心组件的实例化一定得专门通过一个对应的方法才行，最后整个yii构建完成时，就可以统一使用Yii::app()->组件id这种统一的方式了。
 * 因而，我们看到在yii系统级别获取以上核心组件时都是通过对应的方法获取，而在开发app时获取组件的方式可以包含以上两种，本质上是一样的，都是单例返回！！！！
 * 
 * 经过以上的分析，我们区别开了核心组件和普通组件。
 * 关于组件对象是如何获取的这个问题就解决了。
 * 
 * 
 * 重点：如何引入第三方类并集成到yii组件？（这里有行为相关的内容）
 * 我们知道组件是Yii的app对象即CApplication的实例的外围对象，这些组件对象都必须依赖app对象来生存。
 * 而且CApplication继承CModule继承CComponent
 * 而CModule和CComponent都有自己的__get和__isset()方法，且两者的实现方式完全不一样，
 * 而且在CModule中是这样实现的
 * public function __get($name)
	{
		//检测是否有组件对象，或者是否有组件配置数组
		if($this->hasComponent($name))
			return $this->getComponent($name);//by jorry返回对象
		else
			return parent::__get($name);
	}
 * 其中parent::__get($name);说的就是CComponent实例，
 * 如果没有找到要获取的那个组件配置，那么就到CComponent中去找，而CComponent只给出了两种形式
 * $getter='get'.$name;和$method='on'.$name;指的就是CComponent子类的get方法，和子类的行为方法。
 * 到这里我就回答了今天的主题同时也描述的yii行为的执行部分。
 * 
 * 注：CComponent所有想要支持yii行为特性的类都应该继承这个类，而组件是一个完全独立的功能完整的实体，不需要特别的继承。
 * 
 * 
 * 
 * 
 * 多层类组件如何配置组件子类的配置元素？
 * 多级工厂模式
 * 
 * 
 * 
 * 
 * 

//CWebApplication属性解析？
（注意：app对象也是组件的一种，整个yii都是由组件组成，包括module也是组件之一）
它们的继承关系如下：
CWebApplication->CApplication->CModule->CComponent

CComponent:组件基类
它利用__get、__set实现了属性和事件的读写逻辑
$value=$component->propertyName;
$handlers=$component->eventName;
并定义了框架的事件和行为。
为整个框架的所有组件提供了两个全新的特性，属性读写控制和事件行为的支持。
它实现了框架的特性！

CModule:模块基类
由继承关系可知，模块也是组件之一
它使得所有继承它的子类都具有模块的特性，比如模块独立性，模块嵌套性
public $preload=array();//系统预加载组件的id
public $behaviors=array();//系统的行为容器
private $_parentModule;//维护模块的父子关系
private $_basePath;//app路径
private $_modulePath;//模块主路径
private $_params;//参数容器对象，main配置文件中的params参数，支持几乎所有类型
private $_modules=array();//模块对象容器
private $_moduleConfig=array();//模块配置数据
private $_components=array();//组件对象容器
private $_componentConfig=array();//组件配置数据
通过对这些属性的分析，CModule是整个框架的数据中心，是组织者，它是整个组织的交织点。
所有组件对象都是在这里创建的，并实现单例。
CModule决定框架的整个框架，它是框架实现模块化的灵魂。
它实现了框架的数据整合！

CApplication:应用app基类
它是一个abstract类，到目前为止，框架对象app基本就差不多实现了
它完成了对系统中各个组件的相互整合，比如：
数据库、国际本地化、多语言、时区、安全、拓展路径等
具体在此实现的组件如下：
'coreMessages'//CApplication::getCoreMessages();
'db',//CApplication::getDb();
'messages',//CApplication::getMessages();
'errorHandler',//CApplication::getErrorHandler()
'securityManager',//CApplication::getSecurityManager()
'statePersister',//CApplication::getStatePersister()
'urlManager',//CApplication::getUrlManager()
'request',//CApplication::getRequest()
'format',//经过惰性加载处理过的组件
为什么使用get组件id这种方式，而不是Yii::app()->组件id直接获取呢？
上面已经解释过了，当使用Yii::app()->组件id的方式，组件的配置由main控制。
而当使用get组件id这种方式，实现了进一步的继承拓展。
其实这里就实现了：Yii中的一切都是独立的可被配置，可重用，可扩展的组件。
它只负责加载通用应用的部分组件，并提供这些组件的调用接口，并且以get的方式，实现了在继承的层面的拓展性。
它实现了可拓展通用app，包括web应用和console控制台应用或更多！

CWebApplication:
与CApplication完全一样，它就是app的具体实现，与之对应的还有一个CConsoleApplication















回到正题，如何来理解CWebApplication的属性？
本质来讲，这里的属性应该把CWebApplication和CApplication放到一起讲，两者基本原理是一样的（整合组件提供调用接口）。
一、首先是隐性属性，即以__get和__set的形式得到的与组件id相捆绑的属性，每个属性对应一个组件对象。
所有的组件包括核心组件和第三方的配置组件都是这样捆绑的。其中
核心组件来自get+组件id，
	
普通组件来自


两者有何关系？
public function getUser()
{fb('如果mian配置了user则此方法不被调用');
	return $this->getComponent('user');
}
可以推测getDb();方法是完全用不到的，哈哈

如何拓展组件（除了事件行为）？



二、





三、


















*/