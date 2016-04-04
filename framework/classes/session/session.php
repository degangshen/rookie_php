<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Base session class.
 *
 * @package    Kohana
 * @category   Session
 * @author     Kohana Team
 * @copyright  (c) 2008-2011 Kohana Team
 * @license    http://kohanaframework.org/license
 */
abstract class Rookie_Session {

	/**
	 * @var  string  默认的会话适配器
	 */
	public static $default = 'native';

	/**
	 * @var  array  会话实例
	 */
	public static $instances = array();

	/**
	 * 创建一个给定类型的单身会议。一些会话类型
     *（本机，数据库）也支持重新启动一个会议上通过一项
     * 会话ID作为第二个参数。
	 *
	 *     $session = Session::instance();
	 *
	 * [!!] [Session::write] 请求结束时将自动被调用。
	 *
	 * @param   string   type of session (native, cookie, etc)
	 * @param   string   session identifier
	 * @return  Session
	 * @uses    Kohana::$config
	 */
	public static function instance($type = NULL, $id = NULL)
	{
		if ($type === NULL)
		{
			// 使用默认的类型
			$type = Rookie_Session::$default;
		}

		if ( ! isset(Rookie_Session::$instances[$type]))
		{
			// 这种类型的加载配置
			$config = Rookie_Core::$sys_config['session_storage'];

			// 设置会话类的名称
			$class = 'Rookie_Session_'.ucfirst($type);

			// 创建一个新的会话实例
			Rookie_Session::$instances[$type] = $session = new $class();

			// 写在关机会议
			register_shutdown_function(array($session, 'write'));
		}

		return Rookie_Session::$instances[$type];
	}

	/**
	 * @var  string  cookie name
	 */
	protected $_name = 'session';

	/**
	 * @var  int  cookie的生命周期
	 */
	protected $_lifetime = 0;

	/**
	 * @var  bool  会话数据加密？
	 */
	protected $_encrypted = FALSE;

	/**
	 * @var  array  session data
	 */
	protected $_data = array();

	/**
	 * @var  bool  session destroyed?
	 */
	protected $_destroyed = FALSE;

	/**
	 * Overloads the name, lifetime, and encrypted session settings.
	 *
	 * [!!] Sessions can only be created using the [Session::instance] method.
	 *
	 * @param   array   configuration
	 * @param   string  session id
	 * @return  void
	 * @uses    Session::read
	 */
	public function __construct(array $config = NULL, $id = NULL)
	{
		$config = Rookie_Core::$sys_config;
		
		if (isset($config['session_name']))
		{
			// Cookie name to store the session id in
			$this->_name = (string) $config['session_name'];
		}

		if (isset($config['session_lifetime']))
		{
			// Cookie lifetime
			$this->_lifetime = (int) $config['session_lifetime'];
		}

		if (isset($config['session_encrypted']))
		{
			if ($config['session_encrypted'] === TRUE)
			{
				// Use the default Encrypt instance
				$config['session_encrypted'] = 'default';
			}

			// Enable or disable encryption of data
			$this->_encrypted = $config['session_encrypted'];
		}

		// Load the session
		$this->read($id);
	}

	/**
	 * Session object is rendered to a serialized string. If encryption is
	 * enabled, the session will be encrypted. If not, the output string will
	 * be encoded using [base64_encode].
	 *
	 *     echo $session;
	 *
	 * @return  string
	 * @uses    Encrypt::encode
	 */
	public function __toString()
	{
		// Serialize the data array
		$data = serialize($this->_data);

		if ($this->_encrypted)
		{
			// Encrypt the data using the default key
			$data = Rookie_Encrypt::instance($this->_encrypted)->encode($data);
		}
		else
		{
			// Obfuscate the data with base64 encoding
			$data = base64_encode($data);
		}

		return $data;
	}

	/**
	 * Returns the current session array. The returned array can also be
	 * assigned by reference.
	 *
	 *     // Get a copy of the current session data
	 *     $data = $session->as_array();
	 *
	 *     // Assign by reference for modification
	 *     $data =& $session->as_array();
	 *
	 * @return  array
	 */
	public function & as_array()
	{
		return $this->_data;
	}

	/**
	 * Get the current session id, if the session supports it.
	 *
	 *     $id = $session->id();
	 *
	 * [!!] Not all session types have ids.
	 *
	 * @return  string
	 * @since   3.0.8
	 */
	public function id()
	{
		return NULL;
	}

	/**
	 * Get the current session cookie name.
	 *
	 *     $name = $session->name();
	 *
	 * @return  string
	 * @since   3.0.8
	 */
	public function name()
	{
		return $this->_name;
	}

	/**
	 * Get a variable from the session array.
	 *
	 *     $foo = $session->get('foo');
	 *
	 * @param   string   variable name
	 * @param   mixed    default value to return
	 * @return  mixed
	 */
	public function get($key, $default = NULL)
	{
		return array_key_exists($key, $this->_data) ? $this->_data[$key] : $default;
	}

	/**
	 * Get and delete a variable from the session array.
	 *
	 *     $bar = $session->get_once('bar');
	 *
	 * @param   string  variable name
	 * @param   mixed   default value to return
	 * @return  mixed
	 */
	public function get_once($key, $default = NULL)
	{
		$value = $this->get($key, $default);

		unset($this->_data[$key]);

		return $value;
	}

	/**
	 * Set a variable in the session array.
	 *
	 *     $session->set('foo', 'bar');
	 *
	 * @param   string   variable name
	 * @param   mixed    value
	 * @return  $this
	 */
	public function set($key, $value)
	{
		$this->_data[$key] = $value;

		return $this;
	}

	/**
	 * Set a variable by reference.
	 *
	 *     $session->bind('foo', $foo);
	 *
	 * @param   string  variable name
	 * @param   mixed   referenced value
	 * @return  $this
	 */
	public function bind($key, & $value)
	{
		$this->_data[$key] =& $value;

		return $this;
	}

	/**
	 * Removes a variable in the session array.
	 *
	 *     $session->delete('foo');
	 *
	 * @param   string  variable name
	 * @param   ...
	 * @return  $this
	 */
	public function delete($key)
	{
		$args = func_get_args();

		foreach ($args as $key)
		{
			unset($this->_data[$key]);
		}

		return $this;
	}

	/**
	 * Loads existing session data.
	 *
	 *     $session->read();
	 *
	 * @param   string   session id
	 * @return  void
	 */
	public function read($id = NULL)
	{
		$data = NULL;

		try
		{
			if (is_string($data = $this->_read($id)))
			{
				if ($this->_encrypted)
				{
					// Decrypt the data using the default key
					$data = Rookie_Encrypt::instance($this->_encrypted)->decode($data);
				}
				else
				{
					// Decode the base64 encoded data
					$data = base64_decode($data);
				}

				// Unserialize the data
				$data = unserialize($data);
			}
			else
			{
				// Ignore these, session is valid, likely no data though.
			}
		}
		catch (Exception $e)
		{
			// Error reading the session, usually
			// a corrupt session.
			throw new Rookie_Exception('Error reading session data.', NULL, 'SESSION_CORRUPT');
		}

		if (is_array($data))
		{
			// Load the data locally
			$this->_data = $data;
		}
	}

	/**
	 * Generates a new session id and returns it.
	 *
	 *     $id = $session->regenerate();
	 *
	 * @return  string
	 */
	public function regenerate()
	{
		return $this->_regenerate();
	}

	/**
	 * Sets the last_active timestamp and saves the session.
	 *
	 *     $session->write();
	 *
	 * [!!] Any errors that occur during session writing will be logged,
	 * but not displayed, because sessions are written after output has
	 * been sent.
	 *
	 * @return  boolean
	 * @uses    Kohana::$log
	 */
	public function write()
	{
		if (headers_sent() OR $this->_destroyed)
		{
			// Session cannot be written when the headers are sent or when
			// the session has been destroyed
			return FALSE;
		}

		// Set the last active timestamp
		$this->_data['last_active'] = time();

		try
		{
			return $this->_write();
		}
		catch (Exception $e)
		{
			// Log & ignore all errors when a write fails
			Rookie_Core::$log->add(Rookie_Log::ERROR, Rookie_Exception::text($e))->write();

			return FALSE;
		}
	}

	/**
	 * Completely destroy the current session.
	 *
	 *     $success = $session->destroy();
	 *
	 * @return  boolean
	 */
	public function destroy()
	{
		if ($this->_destroyed === FALSE)
		{
			if ($this->_destroyed = $this->_destroy())
			{
				// The session has been destroyed, clear all data
				$this->_data = array();
			}
		}

		return $this->_destroyed;
	}

	/**
	 * Restart the session.
	 *
	 *     $success = $session->restart();
	 *
	 * @return  boolean
	 */
	public function restart()
	{
		if ($this->_destroyed === FALSE)
		{
			// Wipe out the current session.
			$this->destroy();
		}

		// Allow the new session to be saved
		$this->_destroyed = FALSE;

		return $this->_restart();
	}

	/**
	 * Loads the raw session data string and returns it.
	 *
	 * @param   string   session id
	 * @return  string
	 */
	abstract protected function _read($id = NULL);

	/**
	 * Generate a new session id and return it.
	 *
	 * @return  string
	 */
	abstract protected function _regenerate();

	/**
	 * Writes the current session.
	 *
	 * @return  boolean
	 */
	abstract protected function _write();

	/**
	 * Destroys the current session.
	 *
	 * @return  boolean
	 */
	abstract protected function _destroy();

	/**
	 * Restarts the current session.
	 *
	 * @return  boolean
	 */
	abstract protected function _restart();

} // End Session
