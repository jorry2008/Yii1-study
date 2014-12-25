<?php
/**
 * CFilterChain class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */


/**
 * CFilterChain represents a list of filters being applied to an action.
 *
 * CFilterChain executes the filter list by {@link run()}.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web.filters
 * @since 1.0
 */
class CFilterChain extends CList
{
	/**
	 * @var CController the controller who executes the action.
	 */
	public $controller;
	/**
	 * @var CAction the action being filtered by this chain.
	 */
	public $action;
	/**
	 * @var integer the index of the filter that is to be executed when calling {@link run()}.
	 */
	public $filterIndex=0;


	/**
	 * Constructor.
	 * @param CController $controller the controller who executes the action.
	 * @param CAction $action the action being filtered by this chain.
	 */
	public function __construct($controller,$action)
	{
		$this->controller=$controller;
		$this->action=$action;
	}

	/**
	 * CFilterChain factory method.
	 * This method creates a CFilterChain instance.
	 * @param CController $controller the controller who executes the action.
	 * @param CAction $action the action being filtered by this chain.
	 * @param array $filters list of filters to be applied to the action.
	 * @return CFilterChain
	 */
	public static function create($controller,$action,$filters)
	{
		//创建一个过滤器链管理器对象
		$chain=new CFilterChain($controller,$action);
		//fb($chain);
		
		$actionID=$action->getId();
		
		//遍历，将所有的过滤器对象add到管理器中
		foreach($filters as $filter)
		{//fb($filter);// 有一种可能accessControl
			//简单过滤器
			if(is_string($filter))  // filterName [+|- action1 action2]
			{//fb($filter);// productList + index
				if(($pos=strpos($filter,'+'))!==false || ($pos=strpos($filter,'-'))!==false)
				{//有+-
					$matched=preg_match("/\b{$actionID}\b/i",substr($filter,$pos+1))>0;
					//匹配成功返回true
					if(($filter[$pos]==='+')===$matched)
					{
						//权限成功
						$filter=CInlineFilter::create($controller,trim(substr($filter,0,$pos)));
					}
				}
				else
				{
					//没有+-即表示拥有当前所有权限
					//accessControl和CInlineFilter整合了
					$filter=CInlineFilter::create($controller,$filter);
				}
				//fb($filter);exit;//CInlineFilter对象并初始化productList为name属性值
			}
			
			//复杂过滤器
			elseif(is_array($filter))  // array('path.to.class [+|- action1, action2]','param1'=>'value1',...)
			{
				//fb($filter);//array([0] =>'application.filters.MyFilter + index')
				if(!isset($filter[0]))
					throw new CException(Yii::t('yii','The first element in a filter configuration must be the filter class.'));
				
				$filterClass=$filter[0];//'application.filters.MyFilter + index'
				unset($filter[0]);
				if(($pos=strpos($filterClass,'+'))!==false || ($pos=strpos($filterClass,'-'))!==false)
				{
					$matched=preg_match("/\b{$actionID}\b/i",substr($filterClass,$pos+1))>0;
					if(($filterClass[$pos]==='+')===$matched)
						$filterClass=trim(substr($filterClass,0,$pos));
					else
						continue;
				}
				$filter['class']=$filterClass;
				//引入并实例化返回
				$filter=Yii::createComponent($filter);
			}

			//将所有的过滤规则对应生成对象，再并入到过滤器链中
			//$filter为过滤器链对象
			if(is_object($filter))
			{
				$filter->init();//初始化一个filter后要执行的第一filter对象方法，同controller一样
				$chain->add($filter);
			}
		}
		//相互抽象产生的结果，哈哈
		//fb($action->controller->action->controller->action->controller->action->controller->action);
		//返回链管理器对象CFilterChain
		//fb($chain);//普通类的形式是没有任何属性的
		return $chain;
	}

	/**
	 * Inserts an item at the specified position.
	 * This method overrides the parent implementation by adding
	 * additional check for the item to be added. In particular,
	 * only objects implementing {@link IFilter} can be added to the list.
	 * @param integer $index the specified position.
	 * @param mixed $item new item
	 * @throws CException If the index specified exceeds the bound or the list is read-only, or the item is not an {@link IFilter} instance.
	 */
	public function insertAt($index,$item)
	{
		if($item instanceof IFilter)
			parent::insertAt($index,$item);
		else
			throw new CException(Yii::t('yii','CFilterChain can only take objects implementing the IFilter interface.'));
	}

	/**
	 * Executes the filter indexed at {@link filterIndex}.
	 * After this method is called, {@link filterIndex} will be automatically incremented by one.
	 * This method is usually invoked in filters so that the filtering process
	 * can continue and the action can be executed.
	 * 如果系统带过滤器，则此为整个系统中最终执行的方法体
	 * 
	 * 非常巧妙的设计
	 * 这里是一个多对象递归，巧妙的将对象以数组的形式完成对filter链的消耗
	 * 直到对所有filter按照指定的顺序一一执行为止
	 */
	public function run()
	{//fb('至少调用两次，最后一次就要与action见面了');
		//$this->filterIndex为当前执行顺序
		if($this->offsetExists($this->filterIndex))
		{
			$filter=$this->itemAt($this->filterIndex++);
			Yii::trace('Running filter '.($filter instanceof CInlineFilter ? get_class($this->controller).'.filter'.$filter->name.'()':get_class($filter).'.filter()'),'system.web.filters.CFilterChain');
			//按顺序执行一个filter
			$filter->filter($this);
		}
		else
			$this->controller->runAction($this->action);
	}
}