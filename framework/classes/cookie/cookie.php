<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Cookie helper.
 *
 * @package    Kohana
 * @category   Helpers
 * @author     Kohana Team
 * @copyright  (c) 2008-2011 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Rookie_Cookie {

	/**
	 * @var  string  Magic salt to add to the cookie SHA-1 散列 加密添加到Cookie
	 */
	public static $salt = 'rookie';

	/**
	 * @var  integer  Cookie过期之前的秒数
	 */
	public static $expiration = 86400;

	/**
	 * @var  string  该cookie可用来限制路径
	 */
	public static $path = '/';

	/**
	 * @var  string  该cookie可用来限制域
	 */
	public static $domain = NULL;

	/**
	 * @var  boolean  仅通过安全连接传输的Cookie
	 */
	public static $secure = FALSE;

	/**
	 * @var  boolean 只有发送的Cookie通过HTTP，禁用JavaScript访问
	 */
	public static $httponly = FALSE;

	/**
	 * 获取一个签名的cookie的值。没有签名的Cookies不会回。
	 * 如果cookie的签名，但无效，该cookie将被删除。
	 *
	 *     //获取的"theme"的cookie，或使用"blue"如果cookie不存在
	 *     $theme = Cookie::get('theme', 'blue');
	 *
	 * @param   string  cookie name
	 * @param   mixed   default value to return
	 * @return  string
	 */
	public static function get($key, $default = NULL, $is_decode = TRUE)
	{
		if ( ! isset($_COOKIE[$key]))
		{
			// cookie不存在
			return $default;
		}

		// 获取cookie的值
		$cookie = $_COOKIE[$key];
		if ($is_decode)
		{
			$default = authcode($cookie, "DECODE");
		}
		else
		{
			$default = $cookie;
		}
		
		return $default;
	}

	/**
	 * 设置一个签名的cookie。请注意，所有的cookie值必须是字符串，也没有将自动序列化！
	 *
	 *     // Set the "theme" cookie
	 *     Cookie::set('theme', 'red');
	 *
	 * @param   string   name of cookie
	 * @param   string   value of cookie
	 * @param   integer  寿命在几秒钟内
	 * @return  boolean
	 * @uses    Cookie::salt
	 */
	public static function set($name, $value, $expiration = NULL, $is_encode = TRUE)
	{
		if ($expiration === NULL)
		{
			// 使用默认的过期
			$expiration = self::$expiration;
		}

		if ($expiration !== 0)
		{
			// 到期预计将UNIX时间戳
			$expiration += time();
		}

		if ($is_encode)
		{
			$value = authcode($value);
		}

		return setcookie($name, $value, $expiration, self::$path, self::$domain, self::$secure, self::$httponly);
	}

	/**
	 * NULL值和到期删除的cookie。
	 *
	 *     Cookie::delete('theme');
	 *
	 * @param   string   cookie name
	 * @return  boolean
	 * @uses    Cookie::set
	 */
	public static function delete($name)
	{
		// 删除的cookie
		unset($_COOKIE[$name]);

		// 无效的Cookie，并使其过期
		return setcookie($name, NULL, -86400, self::$path, self::$domain, self::$secure, self::$httponly);
	}

	/**
	 * SHA-1 散列 加密
	 * Generates a salt string for a cookie based on the name and value.
	 *
	 *     $salt = Cookie::salt('theme', 'red');
	 *
	 * @param   string   name of cookie
	 * @param   string   value of cookie
	 * @return  string
	 */
	public static function salt($name, $value)
	{
		// Require a valid salt
		if ( ! self::$salt)
		{
			throw new Rookie_Exception('A valid cookie salt is required. Please set Cookie::$salt.');
		}

		// 确定用户代理
		$agent = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : 'unknown';

		return sha1($agent.$name.self::$salt);
	}

} // End cookie
