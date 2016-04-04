<?php
/**
 * Rookie 框架入口点
 * @author shendegang
 * @copyright	2011-9-27
 */
//引入常量
include 'constant.php';
header("X-Powered-By:facebook rookie.com");
header('Content-type: text/html; charset='.CHARSET);	

// 定义应用程序内存分析使用情况
! defined('ROOKIE_START_MEMORY') && 
	define('ROOKIE_START_MEMORY', memory_get_usage());



// 设置默认时区
date_default_timezone_set('Etc/GMT-8');

// 加载Rookie核心类
require './framework/classes/Rookie.php';

$config = include CONFIGPATH.'main.php';

// 设置Rookie自动加载类
spl_autoload_register(array('Rookie_Core', 'autoLoad'));

/**
 * 启用自动加载类 
 * 如果在解序列化的时候需要实例化一个未定义类，则可以设置回调函数以供调用
 *（以免得到的是不完整的 object "__PHP_Incomplete_Class"
 */
ini_set('unserialize_callback_func', 'spl_autoload_call');


// 初始化
Rookie_Core::init($config);
include CONFIGPATH . 'route.php';

//php index.php -p="index/index/default" cli用法
//$opt = getopt('p:');
//if(isset($opt['p'])){
//   $_SERVER['REQUEST_URI'] = '/'. $opt['p'];
//   $_SERVER['REQUEST_METHOD'] = 'GET';
//}

Rookie_Uri::$route = $route;

xhprof_enable();
// //xhprof_enable(XHPROF_FLAGS_NO_BUILTINS); 不记录内置的函数
// //xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);  同时分析CPU和Mem的开销
$xhprof_on = true;

Rookie_Uri::run();

if($xhprof_on){
	$xhprof_data = xhprof_disable();
	$xhprof_root = '/xhprof/';
	include_once $xhprof_root."xhprof_lib/utils/xhprof_lib.php";
	include_once $xhprof_root."xhprof_lib/utils/xhprof_runs.php";
	$xhprof_runs = new XHProfRuns_Default();
	$run_id = $xhprof_runs->save_run($xhprof_data, "hx");
	echo '<a href="/xhprof/xhprof_html/index.php?run='.$run_id.'&source=hx" target="_blank">统计</a>';
}

?>
