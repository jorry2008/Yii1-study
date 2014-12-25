<?php
//请参考CCaptcha类
class UpdateAction extends CAction
{
	public $property1;
	public function run()
	{
		header("Content-Type:text/html;charset=utf-8");
		// place the action logic here
		echo __METHOD__.$this->property1;
	}
}





