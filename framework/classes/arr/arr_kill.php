<?php defined('SYSPATH') or die('No direct script access.');
/**
 * 数组辅助类
 * 
 * @author shendegang
 * @copyright  (c) 2011-2012 Shendegang 
 * @license    http://www.php.com/
 */
class Rookie_Arr_Kill {
	
	/**
	 * @var	string 默认的分隔符号 path()
	 */
	public static $delimiter = '.';
	/**
	 * 判断数组是否关联
	 * @param	array $array
	 * @return 	boolean
	 */
	public static function is_assoc(array $array) 
	{
		$keys = array_keys($array);
		
		return array_keys($keys) !== $keys;
	}
	
	/**
	 * 判断是否是数组
	 * @param 	array $array
	 * @return	boolean
	 */
	public static function is_array(array $array)
	{
		if (is_array($array))
		{
			return TRUE;
		}
		else
		{
			return is_object($array);
		}
	}
	
	/**
	 * 获取使用一个点分隔的路径的数组值。
	 * @param	array	$array		要搜索的数组
	 * @param	string 	$path		关键路径字符串（分隔符分隔）或键数组
	 * @param   string 	$default	如果路径没有设置默认值
	 * @param   string 	$delimiter 关键路径分隔符
	 * @return  mixed
	 */
	public static function path(array $array, $path, $default = NULL, $delimiter = NULL)
	{
		if ( ! self::is_array($array))
		{
			return $array;
		}
		
		if (is_array($path))
		{
			$keys = $path;
		}
		else 
		{
			if (array_key_exists($path, $array))
			{
				return $array[$path];
			}
			
			if ($delimiter === NULL)
			{
				$delimiter = self::$delimiter;
			}
			
			$path = ltrim($path, "{$delimiter} ");
			$path = rtrim($path, "{$delimiter} *");
			$path = explode($delimiter, $path);
		}
		
	}
}
?>
