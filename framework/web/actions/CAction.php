<?php
/**
 * CAction class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CAction is the base class for all controller action classes.
 *
 * CAction provides a way to divide a complex controller into
 * smaller actions in separate class files.
 *
 * Derived classes must implement {@link run()} which is invoked by
 * controller when the action is requested.
 *
 * An action instance can access its controller via {@link getController controller} property.
 *
 * @property CController $controller The controller who owns this action.
 * @property string $id Id of this action.
 *
 * @method run() executes action
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web.actions
 * @since 1.0
 */
abstract class CAction extends CComponent implements IAction
{
	private $_id;
	private $_controller;

	/**
	 * Constructor.
	 * @param CController $controller the controller who owns this action.
	 * @param string $id id of the action.
	 */
	public function __construct($controller,$id)
	{
		$this->_controller=$controller;
		$this->_id=$id;
	}

	/**
	 * @return CController the controller who owns this action.
	 */
	public function getController()
	{
		return $this->_controller;
	}

	/**
	 * @return string id of this action
	 */
	public function getId()
	{
		return $this->_id;
	}

	/**
	 * Runs the action with the supplied request parameters.
	 * This method is internally called by {@link CController::runAction()}.
	 * @param array $params the request parameters (name=>value)
	 * @return boolean whether the request parameters are valid
	 * @since 1.1.7
	 */
	public function runWithParams($params)
	{
		$method=new ReflectionMethod($this, 'run');
		if($method->getNumberOfParameters()>0)
			return $this->runWithParamsInternal($this, $method, $params);
		else
		{
			return $this->run();
		}
	}

	/**
	 * Executes a method of an object with the supplied named parameters.
	 * This method is internally used.
	 * @param mixed $object the object whose method is to be executed
	 * @param ReflectionMethod $method the method reflection
	 * @param array $params the named parameters
	 * @return boolean whether the named parameters are valid
	 * @since 1.1.7
	 * 这个方法就是处理从url传递的所有参数与最终请求的那个action方法的参数一一对应，实现
	 * 参数的自动赋值
	 * 这里的核心东西就是反射，以下这个action核心也就是反射的最佳实践
	 * 
	 * 以下的整个过程是这样的：
	 * 通过反射，取出当前使用的那个action方法的所有信息，
	 * 再将传递过来的参数一一赋值过来，如果有参数没有值，则会获取默认值，
	 * 如果没有默认值又没有值传递过来，则返回false，最终会抛出参数400错误的异常
	 */
	protected function runWithParamsInternal($object, $method, $params)
	{
		//fb($method);//反射的方法对象，这个对象极其了解当前指向的那个对象
		$ps=array();
		foreach($method->getParameters() as $i=>$param)//每个参数都是一个对象
		{
			$name=$param->getName();
			if(isset($params[$name]))
			{
				//注意：这里如果是数组则就会把url的参数打包成数组
				if($param->isArray())
					$ps[]=is_array($params[$name]) ? $params[$name] : array($params[$name]);
				elseif(!is_array($params[$name]))
					$ps[]=$params[$name];
				else
					return false;
			}
			
			//被反射的那个方法的参数是否有默认值
			elseif($param->isDefaultValueAvailable())
			{
				$ps[]=$param->getDefaultValue();
			}
			else
				return false;
		}
		
		//最终以反射的方式带上参数，调用并执行此方法
		//这一步已经完成了action的正常执行，并且可以显示所有的内容，而不需要再执行下去了
		$method->invokeArgs($object,$ps);//以其控制器对象和方法参数为参数调用这个方法
		
		return true;
	}
}
