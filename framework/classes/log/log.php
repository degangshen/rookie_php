<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Message logging with observer-based log writing.
 *
 * [!!] This class does not support extensions, only additional writers.
 *
 * @package    Kohana
 * @category   Logging
 * @author     Kohana Team
 * @copyright  (c) 2008-2011 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Rookie_Log {

	// Log message levels - Windows users see PHP Bug #18090
	const EMERGENCY = LOG_EMERG;    // 0  //紧急情况
	const ALERT     = LOG_ALERT;    // 1
	const CRITICAL  = LOG_CRIT;     // 2
	const ERROR     = LOG_ERR;      // 3
	const WARNING   = LOG_WARNING;  // 4
	const NOTICE    = LOG_NOTICE;   // 5
	const INFO      = LOG_INFO;     // 6
	const DEBUG     = LOG_DEBUG;    // 7
	const STRACE    = 8;

	/**
	 * @var  string  timestamp format for log entries
	 */
	public static $timestamp = 'Y-m-d H:i:s';

	/**
	 * @var  string 时区为日志条目
	 */
	public static $timezone;

	/**
	 * @var  boolean  立即写日志时添加
	 */
	public static $write_on_add = FALSE;

	/**
	 * @var  Log  Singleton实例容器
	 */
	protected static $_instance;

	/**
	 * 获取的这个类的单身实例，使写在关机。
	 *
	 *     $log = Log::instance();
	 *
	 * @return  Log
	 */
	public static function instance()
	{
		if (Rookie_Log::$_instance === NULL)
		{
			// 创建一个新的实例
			Rookie_Log::$_instance = new Rookie_Log;

			// 在关机时写的日志
			register_shutdown_function(array(Rookie_Log::$_instance, 'write'));
		}

		return Rookie_Log::$_instance;
	}

	/**
	 * @var  array  添加消息列表
	 */
	protected $_messages = array();

	/**
	 * @var  array  日志作家名单
	 */
	protected $_writers = array('shendegang');

	/**
	 * 附加一个日志作家，并有选择地限制各级消息将被写入由作家。
	 *
	 *     $log->attach($writer);
	 *
	 * @param   object   Log_Writer实例
	 * @param   mixed    消息级别的阵列，写或最高水平写
	 * @param   integer  分层次写如果美元的水平是不是一个数组
	 * @return  Log
	 */
	public function attach(Log_Writer $writer, $levels = array(), $min_level = 0)
	{
		if ( ! is_array($levels))
		{
			$levels = range($min_level, $levels);
		}
		
		$this->_writers["{$writer}"] = array
		(
			'object' => $writer,
			'levels' => $levels
		);

		return $this;
	}

	/**
	 * 分离日志的作家。必须使用同一作家的对象。
	 *
	 *     $log->detach($writer);
	 *
	 * @param   object  Log_Writer instance
	 * @return  Log
	 */
	public function detach(Log_Writer $writer)
	{
		// Remove the writer
		unset($this->_writers["{$writer}"]);

		return $this;
	}

	/**
	 * 将消息添加到日志中。重置价值，必须要通过更换使用[strtr](http://php.net/strtr).
	 *
	 *     $log->add(Log::ERROR, 'Could not locate user: :user', array(
	 *         ':user' => $username,
	 *     ));
	 *
	 * @param   string  level of message
	 * @param   string  message body
	 * @param   array   values to replace in the message
	 * @return  Log
	 */
	public function add($level, $message, array $values = NULL)
	{
		if ($values)
		{
			// Insert the values into the message
			$message = strtr($message, $values);
		}

		// Create a new message and timestamp it
		$this->_messages[] = array
		(
			'time'  => date("Y-m-d H:i:s", time()),
			'level' => $level,
			'body'  => $message,
		);

		if (Rookie_Log::$write_on_add)
		{
			// Write logs as they are added
			$this->write();
		}

		return $this;
	}

	/**
	 * 写入和清除所有的消息。
	 *
	 *     $log->write();
	 *
	 * @return  void
	 */
	public function write()
	{
		if (empty($this->_messages))
		{
			// 是什么都不写，沿着
			return;
		}

		// 在本地的所有邮件导入
		$messages = $this->_messages;

		// 复位的消息数组
		$this->_messages = array();

		$blog_write = new Rookie_Log_File(LOGPATH);
		
		foreach ($this->_writers as $writer)
		{
			if (empty($writer['levels']))
			{
				// 写的所有邮件
				$blog_write->write($messages);
			}
			else
			{
				// 筛选的邮件
				$filtered = array();

				foreach ($messages as $message)
				{
					// 作家接受这种消息
					$filtered[] = $message;
				}

				// 写筛选的邮件
				$blog_write->write($filtered);
			}
		}
	}

} // End Kohana_Log
