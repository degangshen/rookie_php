<?php
/**
 * CModule class file
 * <pre>
 * 'cacheFile' => array(
 *		'className' => 'Rookie_Cache_File',
 *		'param'		=> array()
 *	),
 * </pre>
 * @author shendegang
 */
interface Module
{
	function setInit($param=array());
}

class ModuleRunner
{
	private $configData = array();
	
	private $modules = array();
	
	/**
	 * 初始化
	 * @param array $configData 配置文件
	 * @throws Rookie_Exception
	 */
	public function init($configData)
	{
		
		$this->configData = array($configData['className'] => array('init' => $configData['param']));
		$interface = new ReflectionClass('Module');
		foreach ($this->configData as $modulename => $params)
		{
			Rookie_Core::loadSys($modulename,'', false);
			$module_class = new ReflectionClass( $modulename );
			
			if (!$module_class->isSubclassOf($interface))
			{
				throw new Rookie_Exception("Unknown modules tye:$modulename");
			}
			$module = $module_class->newInstance();
			foreach ($module_class->getMethods() as $method)
			{
				$this->handleMethod($module, $method, $params);
			}
			return $module;
		}
	}
	
	/**
	 * 执行方法
	 * @param Module $module
	 * @param ReflectionMethod $method
	 * @param array $params
	 */
	public function handleMethod(Module $module, ReflectionMethod $method, $params)
	{
		$name = $method->getName();
		$args = $method->getParameters();
		
		if(count($args) != 1 ||
			substr($name, 0,3) != 'set') {
			return false;		
		}
		$property = strtolower(substr($name, 3));
		if(!isset($params[$property]))
		{
			return false;
		}
		$arg_class = $args[0]->getClass();
		if(empty($arg_class))
		{
			$method->invoke($module, $params[$property]);
		}else{
			$method->invoke($module, $arg_class->newInstance($params[$property]));
		}
	}
}
