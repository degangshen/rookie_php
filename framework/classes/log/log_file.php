<?php defined('SYSPATH') or die('No direct script access.');
/**
 * 文件日志写。写出一个以YYYY / MM目录中的消息，并存储它们。
 *
 * @package    Kohana
 * @category   Logging
 * @author     Kohana Team
 * @copyright  (c) 2008-2011 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Rookie_Log_File extends Rookie_Log_Writer {

	/**
	 * @var  string  放置日志文件的目录
	 */
	protected $_directory;

	/**
	 * 创建一个新的文件记录器。检查该目录存在，并且是可写的。
	 *
	 *     $writer = new Log_File($directory);
	 *
	 * @param   string  日志目录
	 * @return  void
	 */
	public function __construct($directory)
	{
		if ( ! is_dir($directory) OR ! is_writable($directory))
		{
			throw new Rookie_Exception('Directory :dir must be writable',
				array(':dir' => Rookie_Debug::path($directory)));
		}

		// 确定的目录路径
		$this->_directory = realpath($directory).DIRECTORY_SEPARATOR;
	}

	/**
	 * 写入到日志文件中的每个消息。日志文件将被
	 * 追加到'YYYY / MM / DD.log.php`文件，其中YYYY是当前
	 * 年，MM是当前月份，DD是当前日期。
	 *
	 *     $writer->write($messages);
	 *
	 * @param   array   messages
	 * @return  void
	 */
	public  function write(array $messages)
	{
		// 每年设置的目录名称
		$directory = $this->_directory.date('Y');

		if ( ! is_dir($directory))
		{
			// 每年创建目录
			mkdir($directory, 02777);

			// 修复的umask问题，必须手动设置设置权限（）
			chmod($directory, 02777);
		}

		// 本月新增的目录
		$directory .= DIRECTORY_SEPARATOR.date('m');

		if ( ! is_dir($directory))
		{
			// 建立每月目录
			mkdir($directory, 02777);

			// 修复的umask问题，必须手动设置设置权限（）
			chmod($directory, 02777);
		}

		// 设置日志文件的名称
		$filename = $directory.DIRECTORY_SEPARATOR.date('d').'.php';

		if ( ! file_exists($filename))
		{
			// 创建日志文件
			file_put_contents($filename, "<?php defined('SYSPATH') or die('No direct script access.'); ?>".PHP_EOL);

			// 允许任何人都可以写日志文件
			chmod($filename, 0666);
		}

		foreach ($messages as $message)
		{
			// 每个消息写入日志文件
			// Format: time --- level: body
			file_put_contents($filename, PHP_EOL.$message['time'].' --- '.$this->_log_levels[$message['level']].': '.$message['body'], FILE_APPEND);
		}
	}

} // End Kohana_Log_File