<?php
/**
 * CAccessControlFilter class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CAccessControlFilter performs authorization checks for the specified actions.
 *
 * By enabling this filter, controller actions can be checked for access permissions.
 * When the user is not denied by one of the security rules or allowed by a rule explicitly,
 * he will be able to access the action.
 *
 * For maximum security consider adding
 * <pre>array('deny')</pre>
 * as a last rule in a list so all actions will be denied by default.
 *
 * To specify the access rules, set the {@link setRules rules} property, which should
 * be an array of the rules. Each rule is specified as an array of the following structure:
 * <pre>
 * array(
 *   'allow',  // or 'deny'
 * 
 *   // optional, list of action IDs (case insensitive) that this rule applies to
 *   // if not specified or empty, rule applies to all actions
 *   'actions'=>array('edit', 'delete'),
 * 
 *   // optional, list of controller IDs (case insensitive) that this rule applies to
 *   'controllers'=>array('post', 'admin/user'),
 * 
 *   // optional, list of usernames (case insensitive) that this rule applies to
 *   // Use * to represent all users, ? guest users, and @ authenticated users
 *   'users'=>array('thomas', 'kevin'),
 * 
 *   // optional, list of roles (case sensitive!) that this rule applies to.
 *   'roles'=>array('admin', 'editor'),
 * 
 *   // since version 1.1.11 you can pass parameters for RBAC bizRules
 *   'roles'=>array('updateTopic'=>array('topic'=>$topic))
 * 
 *   // optional, list of IP address/patterns that this rule applies to
 *   // e.g. 127.0.0.1, 127.0.0.*
 *   'ips'=>array('127.0.0.1'),
 * 
 *   // optional, list of request types (case insensitive) that this rule applies to
 *   'verbs'=>array('GET', 'POST'),
 * 
 *   // optional, a PHP expression whose value indicates whether this rule applies
 *   // The PHP expression will be evaluated using {@link evaluateExpression}.
 *   // A PHP expression can be any PHP code that has a value. To learn more about what an expression is,
 *   // please refer to the {@link http://www.php.net/manual/en/language.expressions.php php manual}.
 *   'expression'=>'!$user->isGuest && $user->level==2',
 * 
 *   // optional, the customized error message to be displayed
 *   // This option is available since version 1.1.1.
 *   'message'=>'Access Denied.',
 * 
 *   // optional, the denied method callback name, that will be called once the
 *   // access is denied, instead of showing the customized error message. It can also be
 *   // a valid PHP callback, including class method name (array(ClassName/Object, MethodName)),
 *   // or anonymous function (PHP 5.3.0+). The function/method signature should be as follows:
 *   // function foo($user, $rule) { ... }
 *   // where $user is the current application user object and $rule is this access rule.
 *   // This option is available since version 1.1.11.
 *   'deniedCallback'=>'redirectToDeniedMethod',
  * )
 * </pre>
 *
 * @property array $rules List of access rules.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web.auth
 * @since 1.0
 */
class CAccessControlFilter extends CFilter
{
	/**
	 * @var string the error message to be displayed when authorization fails.
	 * This property can be overridden by individual access rule via {@link CAccessRule::message}.
	 * If this property is not set, a default error message will be displayed.
	 * @since 1.1.1
	 */
	public $message;

	//这个会在总控制器中初始化
	//这是一个规则集，规则中的每个数组元素对应一个CAccessRule对象
	private $_rules=array();

	/**
	 * @return array list of access rules.
	 */
	public function getRules()
	{
		return $this->_rules;
	}

	/**
	 * @param array $rules list of access rules.
	 */
	public function setRules($rules)
	{
		foreach($rules as $rule)
		{
			if(is_array($rule) && isset($rule[0]))
			{
				$r=new CAccessRule;//规则类就在当前
				$r->allow=$rule[0]==='allow';//必须是首位
				
				foreach(array_slice($rule,1) as $name=>$value)
				{
					//数组类型和非数组类型的处理
					if($name==='expression' || $name==='roles' || $name==='message' || $name==='deniedCallback')
						$r->$name=$value;
					else
						$r->$name=array_map('strtolower',$value);
				}
				
				//fb($r);//一个规则对应一个AccessRule对象
				$this->_rules[]=$r;
			}
		}
	}

	/**
	 * Performs the pre-action filtering.
	 * @param CFilterChain $filterChain the filter chain that the filter is on.
	 * @return boolean whether the filtering process should continue and the action
	 * should be executed.
	 */
	protected function preFilter($filterChain)
	{
		$app=Yii::app();
		$request=$app->getRequest();
		$user=$app->getUser();
		$verb=$request->getRequestType();//动作_GET,_POST
		$ip=$request->getUserHostAddress();
		
		//依次执行所有规则
		foreach($this->getRules() as $rule)
		{
			//-1认证失败
			if(($allow=$rule->isUserAllowed($user,$filterChain->controller,$filterChain->action,$ip,$verb))>0) // allowed
			{
				break;
			}
			//认证失败
			elseif($allow<0) // denied
			{
				if(isset($rule->deniedCallback))
				{
					//调用指定的当前方法，处理认证失败后的过程
					//注意：这个方法是全局方法，与index.php平行的方法
					call_user_func($rule->deniedCallback, $rule);
				}
				else
				{
					//认证失败
					//在这里执行例如操作！！！
					$this->accessDenied($user,$this->resolveErrorMessage($rule));
				}
				
				return false;
			}
		}

		return true;
	}

	/**
	 * Resolves the error message to be displayed.
	 * This method will check {@link message} and {@link CAccessRule::message} to see
	 * what error message should be displayed.
	 * @param CAccessRule $rule the access rule
	 * @return string the error message
	 * @since 1.1.1
	 */
	protected function resolveErrorMessage($rule)
	{
		if($rule->message!==null)
			return $rule->message;
		elseif($this->message!==null)
			return $this->message;
		else
			return Yii::t('yii','You are not authorized to perform this action.');
	}

	/**
	 * Denies the access of the user.
	 * This method is invoked when access check fails.
	 * @param IWebUser $user the current user
	 * @param string $message the error message to be displayed
	 */
	protected function accessDenied($user,$message)
	{
		//如果是游客则返回登录进一步认证，即留一个允许访问的余地
		//如果用户是登录状态，那么就注定是真正的没有权限了
		if($user->getIsGuest())
			$user->loginRequired();
		else
			throw new CHttpException(403,$message);
	}
}


/**
 * CAccessRule represents an access rule that is managed by {@link CAccessControlFilter}.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web.auth
 * @since 1.0
 */
class CAccessRule extends CComponent
{
	/**
	 * @var boolean whether this is an 'allow' rule or 'deny' rule.
	 */
	public $allow;
	/**
	 * @var array list of action IDs that this rule applies to. The comparison is case-insensitive.
	 * If no actions are specified, rule applies to all actions.
	 */
	public $actions;
	/**
	 * @var array list of controller IDs that this rule applies to. The comparison is case-insensitive.
	 */
	public $controllers;
	/**
	 * @var array list of user names that this rule applies to. The comparison is case-insensitive.
	 * If no user names are specified, rule applies to all users.
	 * * to represent all users代表所有用户，包括游客用户
	 * ? guest users代表游客用户
	 * @ authenticated users代表已验证用户
	 * 
	 */
	public $users;
	/**
	 * @var array list of roles this rule applies to. For each role, the current user's
	 * {@link CWebUser::checkAccess} method will be invoked. If one of the invocations
	 * returns true, the rule will be applied.
	 * Note, you should mainly use roles in an "allow" rule because by definition,
	 * a role represents a permission collection.
	 * @see CAuthManager
	 */
	public $roles;
	/**
	 * @var array IP patterns.
	 */
	public $ips;
	/**
	 * @var array list of request types (e.g. GET, POST) that this rule applies to.
	 */
	public $verbs;
	/**
	 * @var string a PHP expression whose value indicates whether this rule should be applied.
	 * In this expression, you can use <code>$user</code> which refers to <code>Yii::app()->user</code>.
	 * The expression can also be a valid PHP callback,
	 * including class method name (array(ClassName/Object, MethodName)),
	 * or anonymous function (PHP 5.3.0+). The function/method signature should be as follows:
	 * <pre>
	 * function foo($user, $rule) { ... }
	 * </pre>
	 * where $user is the current application user object and $rule is this access rule.
	 *
	 * The PHP expression will be evaluated using {@link evaluateExpression}.
	 *
	 * A PHP expression can be any PHP code that has a value. To learn more about what an expression is,
	 * please refer to the {@link http://www.php.net/manual/en/language.expressions.php php manual}.
	 */
	public $expression;
	/**
	 * @var string the error message to be displayed when authorization is denied by this rule.
	 * If not set, a default error message will be displayed.
	 * @since 1.1.1
	 */
	public $message;
	/**
	 * @var mixed the denied method callback that will be called once the
	 * access is denied. It replaces the behavior that shows an error message.
	 * It can be a valid PHP callback including class method name (array(ClassName/Object, MethodName)),
	 * or anonymous function (PHP 5.3.0+). For more information, on different options, check
	 * @link http://www.php.net/manual/en/language.pseudo-types.php#language.types.callback
	 * The function/method signature should be as follows:
	 * <pre>
	 * function foo($rule) { ... }
	 * </pre>
	 * where $rule is this access rule.
	 *
	 * @since 1.1.11
	 */
	public $deniedCallback;


	/**
	 * Checks whether the Web user is allowed to perform the specified action.
	 * @param CWebUser $user the user object
	 * @param CController $controller the controller currently being executed
	 * @param CAction $action the action to be performed
	 * @param string $ip the request IP address
	 * @param string $verb the request verb (GET, POST, etc.)
	 * @return integer 1 if the user is allowed, -1 if the user is denied, 0 if the rule does not apply to the user
	 * isUser可以看出，任何访问控制的前提就是用户登录
	 */
	public function isUserAllowed($user,$controller,$action,$ip,$verb)
	{
		if($this->isActionMatched($action)
			&& $this->isUserMatched($user)
			&& $this->isRoleMatched($user)
			&& $this->isIpMatched($ip)
			&& $this->isVerbMatched($verb)
			&& $this->isControllerMatched($controller)
			&& $this->isExpressionMatched($user))
			return $this->allow ? 1 : -1;
		else
			return 0;
	}

	/**
	 * @param CAction $action the action
	 * @return boolean whether the rule applies to the action
	 */
	protected function isActionMatched($action)
	{
		return empty($this->actions) || in_array(strtolower($action->getId()),$this->actions);
	}

	/**
	 * @param CController $controller the controller
	 * @return boolean whether the rule applies to the controller
	 */
	protected function isControllerMatched($controller)
	{
		return empty($this->controllers) || in_array(strtolower($controller->getUniqueId()),$this->controllers);
	}

	/**
	 * @param IWebUser $user the user
	 * @return boolean whether the rule applies to the user
	 * 首次，仅通过用户名和登录状态判断访问权限
	 */
	protected function isUserMatched($user)
	{
		if(empty($this->users))//默认对所有用户有效同*，包括游客【游客也是用户的一种，不走特例】
			return true;
		
		foreach($this->users as $u)
		{
			if($u==='*')//允许所有
				return true;
			elseif($u==='?' && $user->getIsGuest())//允许游客用户
				return true;
			elseif($u==='@' && !$user->getIsGuest())//允许已经验证用户
				return true;
			elseif(!strcasecmp($u,$user->getName()))//允许明确指定的用户
				return true;
		}
		return false;
	}

	/**
	 * @param IWebUser $user the user object
	 * @return boolean whether the rule applies to the role
	 */
	protected function isRoleMatched($user)
	{
		if(empty($this->roles))
			return true;
		
		foreach($this->roles as $key=>$role)
		{
			//角色权限配置的两种写法
			if(is_numeric($key))
			{
				if($user->checkAccess($role))
					return true;
			}
			else
			{
				if($user->checkAccess($key,$role))
					return true;
			}
		}
		return false;
	}

	/**
	 * @param string $ip the IP address
	 * @return boolean whether the rule applies to the IP address
	 */
	protected function isIpMatched($ip)
	{
		if(empty($this->ips))
			return true;
		
		foreach($this->ips as $rule)
		{
			if($rule==='*' || $rule===$ip || (($pos=strpos($rule,'*'))!==false && !strncmp($ip,$rule,$pos)))
				return true;
		}
		return false;
	}

	/**
	 * @param string $verb the request method
	 * @return boolean whether the rule applies to the request
	 */
	protected function isVerbMatched($verb)
	{
		return empty($this->verbs) || in_array(strtolower($verb),$this->verbs);
	}

	/**
	 * @param IWebUser $user the user
	 * @return boolean the expression value. True if the expression is not specified.
	 */
	protected function isExpressionMatched($user)
	{
		if($this->expression===null)
			return true;
		else
			return $this->evaluateExpression($this->expression, array('user'=>$user));
	}
}
