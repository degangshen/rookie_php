<?php	defined('ROOKIE') or die('No direct script access.');
/**
 * 异常处理类
 * 
 * @category   Base
 * @author     Rookie Team
 * @copyright  (c) 2011-2015 Rookie Team
 * @license    http://www.lailai5.com/ 啦啦屋 
 */
class Rookie_Exception extends Exception{
	

	//错误观点的内容类型
	public static $error_view_content_type = 'text/html';
	
	//定义错误信息
	public static $php_errors = array(
		E_ERROR              => 'Fatal Error',  	 //致命错误
		E_USER_ERROR         => 'User Error',  	     //用户错误
		E_PARSE              => 'Parse Error',  	 //解析错误
		E_WARNING            => 'Warning',     		 //警告
		E_USER_WARNING       => 'User Warning', 	 //用户警告
		E_STRICT             => 'Strict',  			 //严格的
		E_NOTICE             => 'Notice',  			 //公告
		E_RECOVERABLE_ERROR  => 'Recoverable Error', //可恢复的错误
	);
	
	/**
	 * 创建一个新的异常
	 * @param  string $message
	 * @param  array  $previous
	 * @param  mixed  $code
	 * @return void
	 */
	public function __construct($message, array $previous = NULL, $code = 0)
	{
		if (defined('E_DEPRECATED'))
		{
			// E_DEPRECATED只存在于 PHP >= 5.3.0
			Rookie_Exception::$php_errors[E_DEPRECATED] = 'Deprecated';
		}
		
		// 传递消息到父和整数代码
		parent::__construct($message, (int) $code);

		//保存未修改的代码
		$this->code = $code;
	}
	
	/**
	 * 重写toString()方法
	 * @return  string
	 */
	public function __toString()
	{
		return Rookie_Exception::text($this);
	}
	
	public static function handler(Exception $e)
	{
		try 
		{
			$type 	 = get_class($e);
			$code 	 = $e->getCode();
			$message = $e->getMessage();
			$file 	 = $e->getFile();
			$line 	 = $e->getLine();
			
			//获取异常回朔
			$trace = $e->getTrace();
			
			//检查是否被继承，错误异常
			if ($e instanceof ErrorException)
			{
				if (isset(self::$php_errors[$code]))
				{
					$code = self::$php_errors[$code];
				}
				
				//判断当前PHP版本
				if (version_compare(phpversion(), '5.3', '<'))
				{
					//解决方法为在ErrorException错误：getTrace(),所有的PHP5.2版本
					for ($i = count($trace) - 1; $i > 0; --$i)
					{
						if (isset($trace[$i - 1]['args']))
						{
							// 重新定位的args
							$trace[$i]['args'] = $trace[$i - 1]['args'];

							// 删除的args
							unset($trace[$i - 1]['args']);
						}
					}
				}
			}
			
			//创建一个异常的纯文字版
			$error = Rookie_Exception::text($e);
			
			//加入日志异常
			if (is_object(Rookie_Core::$log))
			{
				// 添加异常日志
				Rookie_Core::$log->add(Rookie_Log::ERROR, $error);

				$strace = Rookie_Exception::text($e)."\n--\n" . $e->getTraceAsString();
				Rookie_Core::$log->add(Rookie_Log::STRACE, $strace);

				// Make sure the logs are written
				Rookie_Core::$log->write();
			}
			
			if (Rookie_Core::$config['isCli'])
			{
				// 只显示文本的异常
				echo "\n{$error}\n";
				
				exit(1);
			}
			
			if ( ! headers_sent())
			{
				//确保发送适当的HTTP标头
				$http_header_status = ($e instanceof HTTP_Exception) ? $code : 500;
				
				header('Content-Type: '.Rookie_Exception::$error_view_content_type.'; charset='.Rookie_Core::$config['charset'], TRUE, $http_header_status);
			}
			
			//开始输出缓冲区
			ob_get_clean();
			ob_start();
			
			//如果是ajax
			if (isset($_SERVER['HTTP_REQUEST_TYPE']) && $_SERVER['HTTP_REQUEST_TYPE'] == "ajax")
			{
				// 只显示文本的异常
				echo "\n{$error}\n";

				exit(1);
			}
			
			
			if ($view_file = Rookie_Core::findFile('classes/debug', 'debug_view'))
			{
				include $view_file;
			}
			else 
			{
				throw new Rookie_Exception('Error view file does not exist: views/:file', array(
					':file' => Rookie_Exception::$error_view,
				));
			}
			
			//显示输出缓冲区的内容
			echo ob_get_clean();
			
			exit(1);
		}
		catch (Exception $e)
		{
			//如果存在的话，清洁输出缓冲区
			ob_get_level() AND ob_clean();
			
			//显示异常文本
			echo Rookie_Exception::text($e), '\n';
			
			exit(1);
		}
		
		
	}
	
	/**
	 * 获取单行的文字表示异常：
	 *
	 * Error [ Code ]: Message ~ File [ Line ]
	 *
	 * @param	object  Exception
	 * @return  string
	*/
	public static function text(Exception $e)
	{
		return sprintf('%s [ %s ]: %s ~ %s [ %d ]',
			get_class($e), $e->getCode(), strip_tags($e->getMessage()), Rookie_Debug::path($e->getFile()), $e->getLine());
	}
	
}
?>
