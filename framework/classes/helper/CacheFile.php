<?php define('IN_ECS', true);
/**
 * ECSHOP的缓存类
 *
 * @package    class
 * @category   CacheFile
 * @author     shendegang
 * @copyright  (c) 2011-2022 shendegang
 * @license    http://www.17ugo.com/
 */
class CacheFile {
	
	/*缓存默认配置*/
	protected $_setting = array(
								'suf' => '.cache.php',	/*缓存文件后缀*/
								'type' => 'serialize',		/*缓存格式：array数组，serialize序列化，null字符串*/
							);
	
	/*缓存路径*/
	protected $filepath = '';

	/**
	 * 构造函数
	 * @param	array	$setting	缓存配置
	 * @return  void
	 */
	public function __construct($setting = '') 
	{
		$this->get_setting($setting);
	}
	
	/**
	 * 写入缓存
	 * @param	string	$name		缓存名称
	 * @param	string	$module		所属模型
	 * @param	mixed	$data		缓存数据
	 * @param	array	$setting	缓存配置
	 * @param	string	$type		缓存类型
	 * @return  mixed				缓存路径/false
	 */

	public function set($name, $data, $module, $setting = '', $type = 'data') 
	{
		$this->get_setting($setting);
		if(empty($type)) $type = 'data';
		$filepath = CACHE_PATH.$module.'/';
		$filename = $name.$this->_setting['suf'];
	    if(!is_dir($filepath)) 
	    {
			mkdir($filepath, 0777, true);
	    }
	    
	    if($this->_setting['type'] == 'array') 
	    {
	    	$data = "<?php\nreturn ".var_export($data, true).";\n?>";
	    } 
	    elseif($this->_setting['type'] == 'serialize') 
	    {
	    	$data = serialize($data);
	    }
	    
	    //是否开启互斥锁
		if(LOCK_EX) 
		{
			$file_size = file_put_contents($filepath.$filename, $data, LOCK_EX);
			if(!$file_size) throw new  EcsException('写入缓存失败');
		} 
		else 
		{
			$file_size = file_put_contents($filepath.$filename, $data);
			if(!$file_size) throw new  EcsException('写入缓存失败');
		}
	    
	    return $file_size ? $file_size : 'false';
	}
	
	/**
	 * 获取缓存
	 * @param	string	$name		缓存名称
	 * @return  mixed	$data		缓存数据
	 * @param	array	$setting	缓存配置
	 * @param	string	$type		缓存类型
	 * @param	string	$module		所属模型
	 */
	public function get($name, $module, $setting = '', $type = 'data') 
	{
		$this->get_setting($setting);
		if(empty($type)) $type = 'data';
		if(!defined(CACHE_PATH)) define('CACHE_PATH', null);
		$filepath = CACHE_PATH.$module.'/';
		$filename = $name.$this->_setting['suf'];
		if (!file_exists($filepath.$filename)) 
		{
			return false;
		} 
		else 
		{
		    if($this->_setting['type'] == 'array') 
		    {
		    	$data = @require($filepath.$filename);
		    } 
		    elseif($this->_setting['type'] == 'serialize') 
		    {
		    	$data = unserialize(file_get_contents($filepath.$filename));
		    }
		    
		    return $data;
		}
	}
	
	/**
	 * 删除缓存
	 * @param	string	$name		缓存名称
	 * @param	string	$module		所属模型
	 * @param	array	$setting	缓存配置
	 * @param	string	$type		缓存类型
	 * @return  bool
	 */
	public function delete($name, $module, $setting = '', $type = 'data') 
	{
		$this->get_setting($setting);
		if(empty($type)) $type = 'data';
		$filepath = CACHE_PATH.$module.'/';
		$filename = $name.$this->_setting['suf'];
		if(file_exists($filepath.$filename)) 
		{
			return @unlink($filepath.$filename) ? true : false;
		} 
		else 
		{
			return false;
		}
	}
	
	/**
	 * 和系统缓存配置对比获取自定义缓存配置
	 * @param	array	$setting	自定义缓存配置
	 * @return  array	$setting	缓存配置
	 */
	public function get_setting($setting = '') 
	{
		if($setting) 
		{
			$this->_setting = array_merge($this->_setting, $setting);
		}
	}

	public function cacheinfo($name, $module, $setting = '', $type = 'data') 
	{
		$this->get_setting($setting);
		if(empty($type)) $type = 'data';
		$filepath = CACHE_PATH.$module.'/';
		$filename = $filepath.$name.$this->_setting['suf'];
		
		if(file_exists($filename)) 
		{
			$res['filename'] = $name.$this->_setting['suf'];
			$res['filepath'] = $filepath;
			$res['filectime'] = filectime($filename);
			$res['filemtime'] = filemtime($filename);
			$res['filesize'] = filesize($filename);
			return $res;
		} 
		else 
		{
			return false;
		}
	}

	/**
	 * 一系列数字填充的数组。
	 *
	 *     // Fill an array with values 5, 10, 15, 20
	 *     $values = Arr::range(5, 20);
	 *
	 * @param   integer  stepping
	 * @param   integer  ending number
	 * @return  array
	 */
	public function range_array($step = 25, $max = 300)
	{
		if ($step < 1)
			return false;
	
		$array = array();
		for ($i = $step; $i <= $max; $i += $step)
		{
			$array[] = $i;
		}
	
		return $array;
	}
	
	/**
	 * 获取取值范围
	 * @param  $str
	 * @param  $array
	 * @return string
	 */
	public function get_range($str, $array)
	{
		//比如小于5的就是5 等于5的就是10 大于5的就是15
		foreach ($array as $key => $vale)
		{
			if($array[$key]>$str){
				$str_new = $vale;
				break;
			}
		}
		return $str_new;
	}
		

}

?>