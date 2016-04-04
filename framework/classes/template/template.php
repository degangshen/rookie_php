<?php	defined('ROOKIE') or die('No direct script access.');
//error_reporting("E_ERROR | E_WARNING");
/**
 * 模块标签解析类
 * @author shendegang
 * @version 1.0 2011-10-31
 */
class Rookie_Template {
	
	//模板目录
	public static $template_dir = NULL;
	
	//编译的缓存目录
	public static $ctemplate_dir = NULL;
	
	//文件更新时间，3600
	public static $cache_lifetime = 0;
	
	//模板模式production 生产模式，Timemode时间模式
	public static $template_type = 'prod';
	
	//当前模板所在的样式，默认default
	public static $style = 'default';
	
	/**
	 * 编译模板标签 
	 * @param  $template	模板文件名
	 * @return unknown
	 */
	public static function tpl($template)
	{
		if (self::$template_dir === NULL)
		{
			self::$template_dir = WEBPATH.'protected/templates'.DIRECTORY_SEPARATOR;
			self::$ctemplate_dir = CACHEPATH.'caches_templates'.DIRECTORY_SEPARATOR;
		}
		if (strstr($template,'/'))
		{
			$module = substr($template, 0, strpos($template, '/'));
			$template = substr($template,  strpos($template, '/')+1,  strlen($template));
		}
		else
		{
			//获取当前所在的模型
			$module = Rookie_Uri::$modules;
		}
		
		//当前模板目录路径
		$cur_tpl_path = self::$template_dir.self::$style.DIRECTORY_SEPARATOR.$module.DIRECTORY_SEPARATOR;
		
		//当前模板文件所在的路径
		$cur_tpl_file_path = $cur_tpl_path.$template.'.html';
		
		//当前缓存模板目录路径
		$cur_tpl_cache = CACHEPATH . 'caches_templates' . DIRECTORY_SEPARATOR . self::$style . DIRECTORY_SEPARATOR.$module.DIRECTORY_SEPARATOR;
		
		//当前缓存的模板文件路径
		$cur_tpl_cache_file = $cur_tpl_cache.sha1($template).'.php';
		
		//判断缓存文件
		if(file_exists($cur_tpl_cache_file))
		{
			//文件修改时间 
			$tlp_time = filemtime($cur_tpl_file_path);
			$tlp_c_time = filemtime($cur_tpl_cache_file);
			
			$template_type = self::$template_type;
			//判断模板是否修改，修改马上就更新
			if ($template_type === 'prod')
			{
				if ($tlp_c_time == $tlp_time)
					return $cur_tpl_cache_file;
			}
			
			if ($tlp_time < $tlp_c_time)
			{
				return $cur_tpl_cache_file;
			}
			//根据时间来更新
			if(self::$cache_lifetime > 0)
			{
				if ($tlp_c_time - $tlp_time < self::$cache_lifetime)
					return $cur_tpl_cache_file;
			}
		}
		
		if ( ! $module)
			Rookie_Core::system_error();
			
		//判断模型的模板目录是否存在
		if ( ! is_dir( $cur_tpl_path ))
		{
			try 
			{
				mkdir(self::$template_dir);
				chmod(self::$template_dir, 0777);
			}
			catch (Exception $e)
			{
				throw new Rookie_Exception('template mkdir() error');
			}
		}
				
		if (file_exists($cur_tpl_file_path))
		{
			if ( ! is_dir( $cur_tpl_cache ))
			{
				try 
				{
					mkdir($cur_tpl_cache);
					chmod($cur_tpl_cache, 0777);
				}
				catch (Exception $e)
				{
					throw new Rookie_Exception('cache_template mkdir() error');
				}
			}
		}
		
		//获取模板内容
		$content = @file_get_contents($cur_tpl_file_path);
		//解析模板标签
		$new_content = self::template_parse($content);
		
		if ($new_content)
		{
			if (file_exists($cur_tpl_cache_file))
				chmod($cur_tpl_cache_file, 0777);
				
			//写入缓存模板标签里
			@file_put_contents($cur_tpl_cache_file, $new_content);
		}
		else
		{
			throw new Rookie_Exception('template template_parse() error');
		}
			
		return $cur_tpl_cache_file;
	}

	
	/**
	 * 解析模板标签
	 * @param  string $content
	 * @return mixed
	 */
	public static function template_parse($str)
	{
		$str = preg_replace ( "/\{template\s+(.+)\}/", "<?php include Rookie_Template::tpl(\\1); ?>", $str );
		$str = preg_replace ( "/\{include\s+(.+)\}/", "<?php include \\1; ?>", $str );
		$str = preg_replace ( "/\{php\s+(.+)\}/", "<?php \\1?>", $str );
		$str = preg_replace ( "/\{if\s+(.+?)\}/", "<?php if(\\1) { ?>", $str );
		$str = preg_replace ( "/\{else\}/", "<?php } else { ?>", $str );
		$str = preg_replace ( "/\{elseif\s+(.+?)\}/", "<?php } elseif (\\1) { ?>", $str );
		$str = preg_replace ( "/\{\/if\}/", "<?php } ?>", $str );
		//for 循环
		$str = preg_replace("/\{for\s+(.+?)\}/","<?php for(\\1) { ?>",$str);
		$str = preg_replace("/\{\/for\}/","<?php } ?>",$str);
		//++ --
		$str = preg_replace("/\{\+\+(.+?)\}/","<?php ++\\1; ?>",$str);
		$str = preg_replace("/\{\-\-(.+?)\}/","<?php ++\\1; ?>",$str);
		$str = preg_replace("/\{(.+?)\+\+\}/","<?php \\1++; ?>",$str);
		$str = preg_replace("/\{(.+?)\-\-\}/","<?php \\1--; ?>",$str);
		$str = preg_replace ( "/\{loop\s+(\S+)\s+(\S+)\}/", "<?php \$n=1;if(is_array(\\1)) foreach(\\1 AS \\2) { ?>", $str );
		$str = preg_replace ( "/\{loop\s+(\S+)\s+(\S+)\s+(\S+)\}/", "<?php \$n=1; if(is_array(\\1)) foreach(\\1 AS \\2 => \\3) { ?>", $str );
		$str = preg_replace ( "/\{\/loop\}/", "<?php \$n++;}unset(\$n); ?>", $str );
		$str = preg_replace ( "/\{([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff:]*\(([^{}]*)\))\}/", "<?php echo \\1;?>", $str );
		$str = preg_replace ( "/\{\\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff:]*\(([^{}]*)\))\}/", "<?php echo \\1;?>", $str );
		$str = preg_replace ( "/\{(\\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\}/", "<?php echo \\1;?>", $str );
		
		$str = preg_replace("/\{(\\$[a-zA-Z0-9_\[\]\'\"\$\x7f-\xff]+)\}/es", "\self::addquote('<?php echo \\1;?>')",$str);		
		$str = preg_replace("/\{(\\$[a-zA-Z0-9_\[\]\'\"\\$\x7f-\xff]+)\}/es", "<?php echo \\1;?>",$str);

		$str = preg_replace("/\{\\$([a-zA-Z_-]+)>([a-zA-Z_-]+)}/", "<?php echo \$\\1>\\2;?>", $str ); //解析$a->b
		$str = preg_replace ( "/\{([A-Z_\x7f-\xff][A-Z0-9_\x7f-\xff]*)\}/s", "<?php echo \\1;?>", $str );
		//$str = preg_replace("/\{pc:(\w+)\s+([^}]+)\}/ie", "self::pc_tag('$1','$2', '$0')", $str);
		//$str = preg_replace("/\{\/pc\}/ie", "self::end_pc_tag()", $str);
		$str = "<?php defined('ROOKIE') or die('No direct script access.'); ?>" . $str;
		return $str;
	}
	
	//更新模板缓存
	
	/**
	 * 转义 // 为 /
	 *
	 * @param $var	转义的字符
	 * @return 转义后的字符
	 */
	public static function addquote($var) {
		return @str_replace ( "\\\"", "\"", @preg_replace ( "/\[([a-zA-Z0-9_\-\.\x7f-\xff]+)\]/s", "['\\1']", $var ) );
	}
	
	/**
	 * 加载视图
	 * @param string $template
	 */
	public static function view($template, $data)
	{
		extract($data);
		if(isset(TestController::$layout))
		{
			$contents = self::tpl($template);
			include self::tpl('layout/'.TestController::$layout);
		}	
		else
			include self::tpl($template);
	}
}