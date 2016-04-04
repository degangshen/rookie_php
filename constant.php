<?php
define('ROOKIE', TRUE);

// WEB路径
define('WEBPATH', realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR);

// 系统类路径
define('SYSPATH', WEBPATH.'framework'.DIRECTORY_SEPARATOR);

//配置文件路径
define('CONFIGPATH', WEBPATH.'protected'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR);

//主机协议
define('SITEPROTOCOL', isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://'); //主机协议

//WEB地址
@define('WEBURL', SITEPROTOCOL . $_SERVER['HTTP_HOST'] . "/");

//日志
define('LOGPATH', WEBPATH.'caches/log'.DIRECTORY_SEPARATOR);

//缓存
define('CACHEPATH', WEBPATH.'caches'.DIRECTORY_SEPARATOR);

// 错误级别
error_reporting(E_ALL | E_STRICT);

//输出页面字符集
define('CHARSET' , 'utf-8'); 

//系统开始时间
define('SYS_START_TIME', microtime());

//系统时间
define('SYS_TIME', time());

//每页显示的条数
define("PAGE", 7);

/* 验证码 */
define('CAPTCHA_REGISTER',          1); //注册时使用验证码
define('CAPTCHA_LOGIN',             2); //登录时使用验证码
define('CAPTCHA_COMMENT',           4); //评论时使用验证码
define('CAPTCHA_ADMIN',             8); //后台登录时使用验证码
define('CAPTCHA_LOGIN_FAIL',       16); //登录失败后显示验证码
define('CAPTCHA_MESSAGE',          32); //留言时使用验证码