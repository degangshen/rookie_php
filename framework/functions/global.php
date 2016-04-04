<?php

/**
 * 获取用户请求信息
 * @param $type
 */
function get_user_request($type = NULL)
{
	$request_info = array();
	
	if (isset($_SERVER['SERVER_PROTOCOL']))
		$request_info['protocol'] = $_SERVER['SERVER_PROTOCOL'];
	else
		$request_info['protocol'] = SITEPROTOCOL;

	// 使用服务器的请求的方法
	$request_info['method'] = $_SERVER['REQUEST_METHOD'];

	// 这个要求是安全的
	if ( ! empty($_SERVER['HTTPS']) AND filter_var($_SERVER['HTTPS'], FILTER_VALIDATE_BOOLEAN))
		$request_info['secure'] = TRUE;
	
	// 这要求有一个引荐
	if (isset($_SERVER['HTTP_REFERER']))
		$request_info['referrer'] = $_SERVER['HTTP_REFERER'];

	// 浏览器类型
	if (isset($_SERVER['HTTP_USER_AGENT']))
		$request_info['user_agent'] = $_SERVER['HTTP_USER_AGENT'];

	// 通常用来表示AJAX请求
	if (isset($_SERVER['HTTP_X_REQUESTED_WITH']))
		$request_info['requested_with'] = $_SERVER['HTTP_X_REQUESTED_WITH'];
		
	if ($type !== NULL)
	{
		if( ! isset($request_info[$type]))
			$request_info[$type] = null;
		else 
			return $request_info[$type];
	}
	else
	{
		return $request_info;
	}
}
	
/**
 * 返回经addslashes处理过的字符串或数组
 * @param $string 需要处理的字符串或数组
 * @return mixed
 */
function new_addslashes($string){
	if(!is_array($string)) return addslashes($string);
	foreach($string as $key => $val) $string[$key] = new_addslashes($val);
	return $string;
}

/**
* 字符串加密、解密函数
* @param	string	$txt		字符串
* @param	string	$operation	ENCODE为加密，DECODE为解密，可选参数，默认为ENCODE，
* @param	string	$key		密钥：数字、字母、下划线
* @return	string
*/
function authcode($string, $operation = 'ENCODE', $key = '', $expiry = 0) 
{
	//$auth_key = md5(Rookie_Core::$sys_config['authkey'].str_replace('7.0' ,'8.0',$_SERVER['HTTP_USER_AGENT']));
	$auth_key = md5(Rookie_Core::$sys_config['authkey']);
	$ckey_length = 4;
	$key = md5($key != '' ? $key : $auth_key);
	$keya = md5(substr($key, 0, 16));
	$keyb = md5(substr($key, 16, 16));
	$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

	$cryptkey = $keya.md5($keya.$keyc);
	$key_length = strlen($cryptkey);

	$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
	$string_length = strlen($string);

	$result = '';
	$box = range(0, 255);

	$rndkey = array();
	for($i = 0; $i <= 255; $i++) {
		$rndkey[$i] = ord($cryptkey[$i % $key_length]);
	}

	for($j = $i = 0; $i < 256; $i++) {
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}

	for($a = $j = $i = 0; $i < $string_length; $i++) {
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
	}

	if($operation == 'DECODE') {
		if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
			return substr($result, 26);
		} else {
			return '';
		}
	} else {
		return $keyc.str_replace('=', '', base64_encode($result));
	}

}


/**
 * 表单提交隐藏码
 * @param unknown_type $specialadd
 */
function formhash($specialadd = '') 
{
	$config = Rookie_Core::$sys_config;
	$hashadd = defined('ROOKIE') ? 'No direct script access.' : '';
	return substr(md5(substr(SYS_TIME, 0, 5).'username'.'uid'.$config['authkey'].$hashadd.$specialadd), 8, 8);
}

/**
 * 提示信息页面跳转，跳转地址如果传入数组，页面会提示多个地址供用户选择，默认跳转地址为数组的第一个值，时间为5秒。
 * showmessage('登录成功', array('默认跳转地址'=>'http://www.roookie.com'));
 * @param string $msg 提示信息
 * @param mixed(string/array) $url_forward 跳转地址
 * @param int $ms 跳转等待时间
 */
function showmessage($msg, $url_forward = 'goback', $ms = 1250, $dialog = '', $returnjs = '') 
{
	include Rookie_Template::tpl('common/showmessage');
	die;
	
}

/**
 * 管理员提示语言
 * @param unknown_type $msg
 * @param unknown_type $url_forward
 * @param unknown_type $ms
 * @param unknown_type $dialog iframe名字
 * @param unknown_type $returnjs
 */
function showmessage_admin($msg, $url_forward = 'goback', $ms = 1250, $dialog = '', $returnjs = '') 
{
	include Rookie_Template::tpl('common/admin_showmessage');
	die;
}

/**
 * iframe弹出框关闭
 */
function art_close()
{
	echo "<script>window.top.art.dialog({id:'add'}).close();window.location.reload();</script>";
}

/**
 * 生成随机字符串
 * @param string $lenth 长度
 * @return string 字符串
 */
function create_randomstr($lenth = 6) 
{
	return random($lenth, '123456789abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ');
}
/**
* 产生随机字符串
*
* @param    int        $length  输出长度 
* @param    string     $chars   可选的 ，默认为 0123456789
* @return   string     字符串
*/
function random($length, $chars = '0123456789') 
{
	$hash = '';
	$max = strlen($chars) - 1;
	for($i = 0; $i < $length; $i++) 
	{
		$hash .= $chars[mt_rand(0, $max)];
	}
	return $hash;
}
/**
 * 对象转数组
 * @author 佚名
 * @param object $obj
 * @return array
 */
function object_to_array($e){
	$e=(array)$e; 
	foreach($e as $k=>$v)
	{ 
		if( gettype($v)=='resource' ) 
		return; 
		if( gettype($v)=='object' || gettype($v)=='array' ) 
		$e[$k]=(array)object_to_array($v); 
	}
 	return $e; 
} 

/**
 * 判断用户是否已经登录
 * 
 */
function check_user_login()
{
	//开启session
	$session = Rookie_Session::instance();

	$rookie_auth = Rookie_Cookie::get('auth');
	//判断Auth cookie是否存在
	if ($rookie_auth && $session->get('member_id'))
	{
		$list_users = explode("\t", authcode($rookie_auth, 'DECODE'));
		if (empty($list_users[0]))
			delete_user_cookie();
		
		list($member_id, $password, $name) = $list_users;

		if (isset($member_id))
		{
			$member_config = Rookie_Core::$member_config['config'];
			
			//是否将用户信息放到缓存里
			if ($member_config['is_memcache'])
			{
				//用户缓存唯一ID
				$mem_member_id = get_memcache_user_name();
				
				if ( ! $mem_member_id) 
					showmessage("请先登录!",'/login/login/member.html');
				
				$memcache = Rookie_Cache_Mem::init();
				if ($user_infos = Rookie_Cache_Mem::get($mem_member_id))
				{
					return $user_infos;
				}
				else 
				{
					$member_infos = get_member_info($name, $member_id, $password);
					
					if ( ! $member_infos->member_id)
					{
						//清空cookie
						delete_user_cookie();
					}
					else 
					{
						Rookie_Cache_Mem::set($mem_member_id, object_to_array($member_infos));
						return Rookie_Cache_Mem::get($mem_member_id);
					}
				}
			}
			else
			{
				return object_to_array(get_member_info($name, $member_id, $password));
			}
		}
		else 
		{
			//清空cookie
			delete_user_cookie();
		}
		
	}
	else
	{
		//清空cookie
		delete_user_cookie();
	}
}

/**
 * 获取用户信息
 * @param string $name
 * @param integer $member_id
 * @param string $password
 */
function get_member_info($name, $member_id, $password)
{
	include_once WEBPATH . 'modules/member/model/MemberModel.php';
	$member_model = new MemberModel();
	//设置会员表名
	$member_model->_table = 'member_'.substr($name, 0, 1);
	
	//从数据库里查询用户信息
	$member_infos = $member_model->getOne(array(
		'where'	=> "member_id=? and password=?",
		'param' => array($member_id, $password)
	));
	
	if ( ! $member_infos->member_id)
	{
		//清空cookie
		delete_user_cookie();
	}
	else 
	{
		return $member_infos;
	}
}


/**
 * 判断管理用户是否已经登录
 * @param string $name_alias 用户权限控制
 */
function check_user_login_admin($name_alias = NULL, $auth = null)
{
	if ($rookie_auth = Rookie_Cookie::get('admin_auth'))
	{
		//Rookie_Session::instance();
		
	}
	else
	{
		//Rookie_Session::instance();
		$rookie_auth = $auth;
	}
	//判断Auth cookie是否存在
	if ($rookie_auth)
	{
		include_once WEBPATH . 'modules/manage/model/AdminMemberModel.php';
		$member_model = new AdminMemberModel();

		$list_users = explode("\t", authcode($rookie_auth, 'DECODE'));
		if (empty($list_users[0]))
			delete_user_cookie_admin();
			
		list($member_id, $password, $name) = $list_users;
		
		//从数据库里查询用户信息
		$member_infos = $member_model->getOne(array(
			'where'	=> "member_id=? and password=?",
			'param' => array($member_id, $password)
		));
		
		if ( ! isset($member_infos->member_id) || ! $member_infos->member_id)
		{
			//清空cookie
			delete_user_cookie_admin();
		}
		else 
		{
			//判断用户当前地址访问的权限
			$role_id = $member_infos->role_id;
			if ( ! $role_id)
			{
				//清空cookie
				delete_user_cookie_admin();
			}
			else
			{
				role_url($role_id, $name_alias, $member_id, false);
			}
			return $member_infos;
		}
		
	}
	else
	{
		//清空cookie
		delete_user_cookie_admin();
	}
}


/**
 * 清空cookie
 */
function delete_user_cookie()
{
	//清空cookie
	Rookie_Cookie::delete('auth', '');
	Rookie_Cookie::delete('_userid', '');
	Rookie_Cookie::delete('_name', '');
	showmessage("请先登录在操作！","/login/login/member.html");
}

/**
 * 清空cookie
 */
function delete_user_cookie_admin()
{
	//清空cookie
	Rookie_Cookie::delete('admin_auth', '');
	Rookie_Cookie::delete('_userid', '');
	Rookie_Cookie::delete('_name', '');
	showmessage_admin("请先登录在操作！","/manage/login/manage.html");
}
/**
 * 根据UID分表算法
 *
 * @param string $table_name 表名
 * @param int $uid //用户ID
 * @param int $bit //表后缀保留几位
 * @param int $seed //向右移动位数
 * @return string 
 */
function get_table($table_name, $uid , $bit = 4 , $seed = 14)
{
	return $table_name."_" . sprintf( "%0{$bit}d" , ($uid >> $seed) );
}
/**
* 字符串加密、解密函数
*
* @param	string	$txt		字符串
* @param	string	$operation	ENCODE为加密，DECODE为解密，可选参数，默认为ENCODE，
* @param	string	$key		密钥：数字、字母、下划线
* @return	string
*/
function sys_auth($txt, $operation = 'ENCODE', $key = 'adbasdfwer2343sfasdf234') 
{
	$auth_key = md5($key);
	$key	= $key ? $key : $auth_key;
	$txt	= $operation == 'ENCODE' ? (string)$txt : base64_decode($txt);
	$len	= strlen($key);
	$code	= '';
	for($i=0; $i<strlen($txt); $i++){
		$k		= $i % $len;
		$code  .= $txt[$i] ^ $key[$k];
	}
	$code = $operation == 'DECODE' ? $code : base64_encode($code);
	return $code;
}
/**
 * 获取请求ip
 *
 * @return ip地址
 */
function ip() 
{
	if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
		$ip = getenv('HTTP_CLIENT_IP');
	} elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
		$ip = getenv('HTTP_X_FORWARDED_FOR');
	} elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
		$ip = getenv('REMOTE_ADDR');
	} elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	return preg_match ( '/[\d\.]{7,15}/', $ip, $matches ) ? $matches [0] : '';
}

/**
 * 判断当前用户访问的url地址权限
 */
function role_url($role_id, $name_alias = NULL, $member_id = null, $is_auth = true)
{
	if ($is_auth)
	{
		$rookie_auth = Rookie_Cookie::get('admin_auth');
		$list_users = explode("\t", authcode($rookie_auth, 'DECODE'));
		if (empty($list_users[0]))
				delete_user_cookie_admin();
				
		list($member_id, $password, $name) = $list_users;
	}
	
		
	if (intval($member_id) == '1')
	{
		return true;
	}
		
	empty($name_alias) && exit("权限不够！");
		
	Rookie_Cache_Php::$_directory = CACHEPATH . 'role_caches' . DIRECTORY_SEPARATOR;
	$role_setting_info = Rookie_Cache_Php::get("role_setting_info");
	
	if(isset($role_setting_info[$role_id]))
		$user_role_setting = $role_setting_info[$role_id];
	else
		delete_user_cookie_admin();
		
	if (is_array($name_alias))
	{
		$is_role = 0;
		foreach ($name_alias as $key => $val)
		{
			if (in_array($val ,$user_role_setting))
			{
				$is_role = $is_role + 1;
			}
		}
		if ($is_role)
		{
			return true;
		}
	}
	
	if ($name_alias == 'all' || in_array($name_alias, $user_role_setting))
		return true;
	else
		exit("权限不够！");
	
		
	
}
/**
 * 定义函数pageft(),三个参数的含义为：
 * @param string $totle		           信息总数；
 * @param integer $displaypg	每页显示信息数，这里设置为默认是20；
 * @param string $url			分页导航中的链接，除了加入不同的查询信息“page”外的部分都与这个URL相同。
 * 默认值本该设为本页URL（即$_SERVER["REQUEST_URI"]），但设置默认值的右边只能为常量，所以该默认值设为空字符串，在函数内部再设置为本页URL。
 */
function page_ajax($totle,$displaypg = PAGE,$url='',$arg = '', $page_curr = '')
{
	//定义几个全局变量：
	//$page：当前页码；
	//$firstcount：（数据库）查询的起始项；
	//$pagenav：页面导航条代码，函数内部并没有将它输出；
	//$_SERVER：读取本页URL“$_SERVER["REQUEST_URI"]”所必须。
	if( ! isset($_GET["page"])) 
	{
		$page = 1;
	}
	else 
	{
		if ($page_curr)
			$page = $page_curr;
		else
			$page = $_GET["page"];
	}
		
	//如果$url使用默认，即空值，则赋值为本页URL：
//	if( ! $url)
//		$url = $_SERVER["REQUEST_URI"];

	//URL分析：
	$parse_url = parse_url($url);
	$url_query = isset($parse_url["query"]) ? $parse_url["query"] : NULL ; //单独取出URL的查询字串
	if($url_query)
	{
		//因为URL中可能包含了页码信息，我们要把它去掉，以便加入新的页码信息。
		$url_query = preg_replace("/(^|&)page=$page/","",$url_query);
		
		//将处理后的URL的查询字串替换原来的URL的查询字串：
		$url = str_replace($parse_url["query"],$url_query,$url);

		//在URL后加page查询信息，但待赋值：
		if($url_query) 
			$url .= "&page"; 
		else 
			$url .= "page";
	}
	else 
	{
		$url .= "?page";
	}
	$lastpg = ceil($totle/$displaypg); //最后页，也是总页数
	$page = min($lastpg,$page);
	$prepg = $page-1; //上一页
	$nextpg = ($page==$lastpg ? 0 : $page+1); //下一页
	$firstcount = ($page-1)*$displaypg;

	//开始分页导航条代码：
	$pagenav = "显示第 <B>".($totle?($firstcount+1):0)."</B>-<B>".min($firstcount+$displaypg,$totle)."</B> 条记录，共 $totle 条记录&nbsp;&nbsp;&nbsp;&nbsp;";
	
	//如果只有一页则跳出函数：
	if($lastpg <= 1) return false;

	$pagenav .= " <a href='javascript:;page._onclick(\"$url=1&$arg\")'>第一页</a> ";
	if($prepg) $pagenav .= " <a href='javascript:;page._onclick(\"$url=$prepg&$arg\")'>上一页</a> "; else $pagenav.=" 前页 ";
	if($nextpg) $pagenav .= " <a href='javascript:;page._onclick(\"$url=$nextpg&$arg\")'>下一页</a> "; else $pagenav.=" 后页 ";
	$pagenav .= " <a href='javascript:;page._onclick(\"$url=$lastpg&$arg\")'>最后一页</a> ";

	//下拉跳转列表，循环列出所有页码：
	$pagenav .= "　到第 <select name='topage' size='1' onchange='javascript:;page._onclick(\"$url=$lastpg&$arg\",\" \", this.value)'>\n";
	for($i=1; $i<=$lastpg; $i++)
	{
		if($i == $page) 
			$pagenav .= "<option value='$i' selected>$i</option>\n";
		else 
			$pagenav .= "<option value='$i'>$i</option>\n";
	}
	$pagenav .= "</select> 页，共 $lastpg 页";
	return $pagenav;	
}
/**
 * 定义函数pageft(),三个参数的含义为：
 * @param string $totle		           信息总数；
 * @param integer $displaypg	每页显示信息数，这里设置为默认是20；
 * @param string $url			分页导航中的链接，除了加入不同的查询信息“page”外的部分都与这个URL相同。
 * 默认值本该设为本页URL（即$_SERVER["REQUEST_URI"]），但设置默认值的右边只能为常量，所以该默认值设为空字符串，在函数内部再设置为本页URL。
 */
function page($totle,$displaypg = PAGE,$url='',$arg = '', $page_curr = '')
{
	//定义几个全局变量：
	//$page：当前页码；
	//$firstcount：（数据库）查询的起始项；
	//$pagenav：页面导航条代码，函数内部并没有将它输出；
	//$_SERVER：读取本页URL“$_SERVER["REQUEST_URI"]”所必须。
	if( ! isset($_GET["page"])) 
	{
		$page = 1;
	}
	else 
	{
		if ($page_curr)
			$page = $page_curr;
		else
			$page = $_GET["page"];
	}
		
	//如果$url使用默认，即空值，则赋值为本页URL：
	if( ! $url)
		$url = $_SERVER["REQUEST_URI"];

	//URL分析：
	$parse_url = parse_url($url);
	$url_query = isset($parse_url["query"]) ? $parse_url["query"] : NULL ; //单独取出URL的查询字串
	if($url_query)
	{
		//因为URL中可能包含了页码信息，我们要把它去掉，以便加入新的页码信息。
		$url_query = preg_replace("/(^|&)page=$page/","",$url_query);
		
		//将处理后的URL的查询字串替换原来的URL的查询字串：
		$url = str_replace($parse_url["query"],$url_query,$url);

		//在URL后加page查询信息，但待赋值：
		if($url_query) 
			$url .= "&page"; 
		else 
			$url .= "page";
	}
	else 
	{
		$url .= "?page";
	}
	$lastpg = ceil($totle/$displaypg); //最后页，也是总页数
	$page = min($lastpg,$page);
	$prepg = $page-1; //上一页
	$nextpg = ($page==$lastpg ? 0 : $page+1); //下一页
	$firstcount = ($page-1)*$displaypg;

	//开始分页导航条代码：
	$pagenav = "显示第 <B>".($totle?($firstcount+1):0)."</B>-<B>".min($firstcount+$displaypg,$totle)."</B> 条记录，共 $totle 条记录&nbsp;&nbsp;&nbsp;&nbsp;";
	
	//如果只有一页则跳出函数：
	if($lastpg <= 1) return false;

	$pagenav .= " <a href='$url=1&$arg'>第一页</a> ";
	if($prepg) $pagenav .= " <a href='$url=$prepg&$arg'>上一页</a> "; else $pagenav.=" 前页 ";
	if($nextpg) $pagenav .= " <a href='$url=$nextpg&$arg'>下一页</a> "; else $pagenav.=" 后页 ";
	$pagenav .= " <a href='$url=$lastpg&$arg'>最后一页</a> ";

	//下拉跳转列表，循环列出所有页码：
	$pagenav .= "　到第 <select name='topage' size='1' onchange='window.location=\"$url=\"+this.value'>\n";
	for($i=1; $i<=$lastpg; $i++)
	{
		if($i == $page) 
			$pagenav .= "<option value='$i' selected>$i</option>\n";
		else 
			$pagenav .= "<option value='$i'>$i</option>\n";
	}
	$pagenav .= "</select> 页，共 $lastpg 页";
	return $pagenav;	
}

function page_limit($page)
{
	$page_size = PAGE;
	$page_s = ($page-1)*$page_size;
	return "$page_s, $page_size";
}
/**
 * 获取用户memcache_user_id
 * return string 
 */
function get_memcache_user_name()
{
	$rookie_auth = Rookie_Cookie::get('auth');
	$member_infos_name = null;
	//判断Auth cookie是否存在
	if ($rookie_auth)
	{
		list($member_id, $password, $name) = explode("\t", authcode($rookie_auth, 'DECODE'));
		if ( ! $name)
			showmessage("请登录在操作");
		else 
		{
			$member_infos_name = md5(md5("mem_member_id_".$name.$member_id));
		}
	}
	return $member_infos_name;
}

/**
 * 获取后台菜单缓存
 * @param $type 1主菜单缓存，2子菜单缓存
 */
function get_menu_cache($type = 1)
{
	//获取缓存
	Rookie_Cache_Php::$_directory = CACHEPATH . 'menu_caches' . DIRECTORY_SEPARATOR;
	
	if ($type === 1)
		return Rookie_Cache_Php::get("menu_list_cache");
	else
		return Rookie_Cache_Php::get("menu_list_chail_cache");
}

/**
 * CSS的路径
 */
function css_path( $path = NULL )
{
	$file_time = filemtime(CSSPATH.$path);
	$v = $path . "?v=" . date("Ymd", $file_time);
	
	$path = Rookie_Core::$_static_path;
	echo "http://".$path[rand(0,5)] . CSSPATH . $v;
}

/**
 * JS的路径
 */
function js_path( $path = NULL )
{
	$file_time = filemtime(JSPATH.$path);
	$v = $path . "?v=" . date("Ymd", $file_time);
	
	$path = Rookie_Core::$_static_path;
	echo "http://".$path[rand(0,5)] . JSPATH . $v;
}

/**
 * JS的路径
 */
function js_path_return( $path = NULL )
{
	$file_time = filemtime(JSPATH.$path);
	$v = $path . "?v=" . date("Ymd", $file_time);
	
	$path = Rookie_Core::$_static_path;
	return "http://".$path[rand(0,5)] . JSPATH . $v;
}

/**
 * IMG的路径
 */
function img_path( $path = NULL )
{
	$file_time = filemtime(IMGPATH.$path);
	$v = $path . "?v=" . date("Ymd", $file_time);
	
	$path = Rookie_Core::$_static_path;
	echo "http://".$path[rand(0,5)] . IMGPATH . $v;
}
/**
 * 取得文件扩展
 * 
 * @param $filename 文件名
 * @return 扩展名
 */
function fileext($filename) {
	return strtolower(trim(substr(strrchr($filename, '.'), 1, 10)));
}

/**
 * 获取当前url
 */
function cur_url()
{

	$sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
	$php_self = $_SERVER['PHP_SELF'] ? safe_replace($_SERVER['PHP_SELF']) : safe_replace($_SERVER['SCRIPT_NAME']);
	$path_info = isset($_SERVER['PATH_INFO']) ? safe_replace($_SERVER['PATH_INFO']) : '';
	$relate_url = isset($_SERVER['REQUEST_URI']) ? safe_replace($_SERVER['REQUEST_URI']) : $php_self.(isset($_SERVER['QUERY_STRING']) ? '?'.safe_replace($_SERVER['QUERY_STRING']) : $path_info);
	return $sys_protocal.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '').$relate_url;

}

/**
 * 安全过滤函数
 *
 * @param $string
 * @return string
 */
function safe_replace($string) {
	$string = str_replace('%20','',$string);
	$string = str_replace('%27','',$string);
	$string = str_replace('%2527','',$string);
	$string = str_replace('*','',$string);
	$string = str_replace('"','&quot;',$string);
	$string = str_replace("'",'',$string);
	$string = str_replace('"','',$string);
	$string = str_replace(';','',$string);
	$string = str_replace('<','&lt;',$string);
	$string = str_replace('>','&gt;',$string);
	$string = str_replace("{",'',$string);
	$string = str_replace('}','',$string);
	return $string;
}



/**
 * 过滤ASCII码从0-28的控制字符
 * @return String
 */
function trim_unsafe_control_chars($str) {
	$rule = '/[' . chr ( 1 ) . '-' . chr ( 8 ) . chr ( 11 ) . '-' . chr ( 12 ) . chr ( 14 ) . '-' . chr ( 31 ) . ']*/';
	return str_replace ( chr ( 0 ), '', preg_replace ( $rule, '', $str ) );
}

/**
 * 格式化文本域内容
 *
 * @param $string 文本域内容
 * @return string
 */
function trim_textarea($string) {
	$string = nl2br ( str_replace ( ' ', '&nbsp;', $string ) );
	return $string;
}

/**
 * 将文本格式成适合js输出的字符串
 * @param string $string 需要处理的字符串
 * @param intval $isjs 是否执行字符串格式化，默认为执行
 * @return string 处理后的字符串
 */
function format_js($string, $isjs = 1)
{
	$string = addslashes(str_replace(array("\r", "\n"), array('', ''), $string));
	return $isjs ? 'document.write("'.$string.'");' : $string;
}

/**
 * 转义 javascript 代码标记
 *
 * @param $str
 * @return mixed
 */
function trim_script($str) {
	$str = preg_replace ( '/\<([\/]?)script([^\>]*?)\>/si', '&lt;\\1script\\2&gt;', $str );
	$str = preg_replace ( '/\<([\/]?)iframe([^\>]*?)\>/si', '&lt;\\1iframe\\2&gt;', $str );
	$str = preg_replace ( '/\<([\/]?)frame([^\>]*?)\>/si', '&lt;\\1frame\\2&gt;', $str );
	$str = preg_replace ( '/]]\>/si', ']] >', $str );
	return $str;
}

/**
 * 记录管理员操作日志
 */
function admin_log_write($message)
{
	include_once WEBPATH . 'modules/manage/model/AdminLogModel.php';
	$log_model = new AdminLogModel();
	
	$rookie_auth = Rookie_Cookie::get('admin_auth');
	$list_users = explode("\t", authcode($rookie_auth, 'DECODE'));
	list($member_id, $password, $name) = $list_users;

	$url = cur_url();
	$ip = ip();
	$data = $message;
	$log_model = $log_model->insertAttributes(array(
		'member_id'	=> "$member_id",
		'name'		=> "$name",
		'url'		=> "$url",
		'data'		=> "$data",
		'ip'		=> "$ip",
		'time'		=> time()
	));
}

/**	
 * 获得所有模块的名称以及链接地址
 *
 * @access      public
 * @param       string      $directory      插件存放的目录
 * @return      array
 */
function read_modules($directory = '.')
{
    $dir         = @opendir($directory);
    $set_modules = true;
    $modules     = array();

    while (false !== ($file = @readdir($dir)))
    {
        if (preg_match("/^.*?\.php$/", $file))
        {
            include_once($directory. '/' .$file);
        }
    }
    @closedir($dir);
    unset($set_modules);

    foreach ($modules AS $key => $value)
    {
        ksort($modules[$key]);
    }
    ksort($modules);

    return $modules;
}
/**
 *  将一个字串中含有全角的数字字符、字母、空格或'%+-()'字符转换为相应半角字符
 *
 * @access  public
 * @param   string       $str         待转换字串
 *
 * @return  string       $str         处理后字串
 */
function make_semiangle($str)
{
    $arr = array('０' => '0', '１' => '1', '２' => '2', '３' => '3', '４' => '4',
                 '５' => '5', '６' => '6', '７' => '7', '８' => '8', '９' => '9',
                 'Ａ' => 'A', 'Ｂ' => 'B', 'Ｃ' => 'C', 'Ｄ' => 'D', 'Ｅ' => 'E',
                 'Ｆ' => 'F', 'Ｇ' => 'G', 'Ｈ' => 'H', 'Ｉ' => 'I', 'Ｊ' => 'J',
                 'Ｋ' => 'K', 'Ｌ' => 'L', 'Ｍ' => 'M', 'Ｎ' => 'N', 'Ｏ' => 'O',
                 'Ｐ' => 'P', 'Ｑ' => 'Q', 'Ｒ' => 'R', 'Ｓ' => 'S', 'Ｔ' => 'T',
                 'Ｕ' => 'U', 'Ｖ' => 'V', 'Ｗ' => 'W', 'Ｘ' => 'X', 'Ｙ' => 'Y',
                 'Ｚ' => 'Z', 'ａ' => 'a', 'ｂ' => 'b', 'ｃ' => 'c', 'ｄ' => 'd',
                 'ｅ' => 'e', 'ｆ' => 'f', 'ｇ' => 'g', 'ｈ' => 'h', 'ｉ' => 'i',
                 'ｊ' => 'j', 'ｋ' => 'k', 'ｌ' => 'l', 'ｍ' => 'm', 'ｎ' => 'n',
                 'ｏ' => 'o', 'ｐ' => 'p', 'ｑ' => 'q', 'ｒ' => 'r', 'ｓ' => 's',
                 'ｔ' => 't', 'ｕ' => 'u', 'ｖ' => 'v', 'ｗ' => 'w', 'ｘ' => 'x',
                 'ｙ' => 'y', 'ｚ' => 'z',
                 '（' => '(', '）' => ')', '〔' => '[', '〕' => ']', '【' => '[',
                 '】' => ']', '〖' => '[', '〗' => ']', '“' => '[', '”' => ']',
                 '‘' => '[', '’' => ']', '｛' => '{', '｝' => '}', '《' => '<',
                 '》' => '>',
                 '％' => '%', '＋' => '+', '—' => '-', '－' => '-', '～' => '-',
                 '：' => ':', '。' => '.', '、' => ',', '，' => '.', '、' => '.',
                 '；' => ',', '？' => '?', '！' => '!', '…' => '-', '‖' => '|',
                 '”' => '"', '’' => '`', '‘' => '`', '｜' => '|', '〃' => '"',
                 '　' => ' ');

    return strtr($str, $arr);
}

/**
 * 邮件发送
 *
 * @param: $name[string]        接收人姓名
 * @param: $email[string]       接收人邮件地址
 * @param: $subject[string]     邮件标题
 * @param: $content[string]     邮件内容
 * @param: $type[int]           0 普通邮件， 1 HTML邮件
 * @param: $notification[bool]  true 要求回执， false 不用回执
 *
 * @return boolean
 */
function send_mail($name, $email, $subject, $content, $type = 0, $notification=false)
{
	$shop_config = Rookie_Core::$shop_config;
	
    /* 如果邮件编码不是CHARSET，创建字符集转换对象，转换编码 */
    if (strtolower($shop_config['mail_charset']) != Rookie_Core::$charset)
    {
        $name      = iconv(Rookie_Core::$charset, $shop_config['mail_charset'], $name);
        $subject   = iconv(Rookie_Core::$charset, $shop_config['mail_charset'], $subject);
        $content   = iconv(Rookie_Core::$charset, $shop_config['mail_charset'], $content);
        $shop_config['shop_name'] = iconv(Rookie_Core::$charset, $shop_config['mail_charset'], $shop_config['shop_name']);
    }
    $charset   = $shop_config['mail_charset'];
    /**
     * 使用mail函数发送邮件
     */
    if ($shop_config['mail_service'] == '0' && function_exists('mail'))
    {
        /* 邮件的头部信息 */
        $content_type = ($type == 0) ? 'Content-Type: text/plain; charset=' . $charset : 'Content-Type: text/html; charset=' . $charset;
        $headers = array();
        $headers[] = 'From: "' . '=?' . $charset . '?B?' . base64_encode($shop_config['shop_name']) . '?='.'" <' . $shop_config['smtp_mail'] . '>';
        $headers[] = $content_type . '; format=flowed';
        if ($notification)
        {
            $headers[] = 'Disposition-Notification-To: ' . '=?' . $charset . '?B?' . base64_encode($shop_config['shop_name']) . '?='.'" <' . $shop_config['smtp_mail'] . '>';
        }

        $res = @mail($email, '=?' . $charset . '?B?' . base64_encode($subject) . '?=', $content, implode("\r\n", $headers));

        if (!$res)
        {
            echo("邮件发送失败");

            return false;
        }
        else
        {
            return true;
        }
    }
    /**
     * 使用smtp服务发送邮件
     */
    else
    {
        /* 邮件的头部信息 */
        $content_type = ($type == 0) ?
            'Content-Type: text/plain; charset=' . $charset : 'Content-Type: text/html; charset=' . $charset;
        $content   =  base64_encode($content);

        $headers = array();
        $headers[] = 'Date: ' . gmdate('D, j M Y H:i:s') . ' +0000';
        $headers[] = 'To: "' . '=?' . $charset . '?B?' . base64_encode($name) . '?=' . '" <' . $email. '>';
        $headers[] = 'From: "' . '=?' . $charset . '?B?' . base64_encode($shop_config['shop_name']) . '?='.'" <' . $shop_config['smtp_mail'] . '>';
        $headers[] = 'Subject: ' . '=?' . $charset . '?B?' . base64_encode($subject) . '?=';
        $headers[] = $content_type . '; format=flowed';
        $headers[] = 'Content-Transfer-Encoding: base64';
        $headers[] = 'Content-Disposition: inline';
        if ($notification)
        {
            $headers[] = 'Disposition-Notification-To: ' . '=?' . $charset . '?B?' . base64_encode($shop_config['shop_name']) . '?='.'" <' . $shop_config['smtp_mail'] . '>';
        }

        /* 获得邮件服务器的参数设置 */
        $params['host'] = $shop_config['smtp_host'];
        $params['port'] = $shop_config['smtp_port'];
        $params['user'] = $shop_config['smtp_user'];
        $params['pass'] = $shop_config['smtp_pass'];

        if (empty($params['host']) || empty($params['port']))
        {
            // 如果没有设置主机和端口直接返回 false
            $GLOBALS['err'] ->add("主机设置错误");

            return false;
        }
        else
        {
            // 发送邮件
            if (!function_exists('fsockopen'))
            {
                //如果fsockopen被禁用，直接返回
                $GLOBALS['err']->add("fsockopen被禁用");

                return false;
            }

            Rookie_Core::load_help("smtp");
            static $smtp;

            $send_params['recipients'] = $email;
            $send_params['headers']    = $headers;
            $send_params['from']       = $shop_config['smtp_mail'];
            $send_params['body']       = $content;

            if (!isset($smtp))
            {
                $smtp = new smtp($params);
            }

            if ($smtp->connect() && $smtp->send($send_params))
            {
                return true;
            }
            else
            {
                $err_msg = $smtp->error_msg();
                if (empty($err_msg))
                {
                    $GLOBALS['err']->add('Unknown Error');
                }
                else
                {
                    if (strpos($err_msg, 'Failed to connect to server') !== false)
                    {
                        $GLOBALS['err']->add(sprintf('无法连接到邮件服务器 %s', $params['host'] . ':' . $params['port']));
                    }
                    else if (strpos($err_msg, 'AUTH command failed') !== false)
                    {
                        $GLOBALS['err']->add('邮件服务器验证帐号或密码不正确');
                    }
                    elseif (strpos($err_msg, 'bad sequence of commands') !== false)
                    {
                        $GLOBALS['err']->add('服务器拒绝发送该邮件');
                    }
                    else
                    {
                        $GLOBALS['err']->add($err_msg);
                    }
                }

                return false;
            }
        }
    }
}

 function html_options($arr)
 {
        $selected = $arr['selected'];

        if ($arr['options'])
        {
            $options = (array)$arr['options'];
        }
        elseif ($arr['output'])
        {
            if ($arr['values'])
            {
                foreach ($arr['output'] AS $key => $val)
                {
                    $options["{$arr[values][$key]}"] = $val;
                }
            }
            else
            {
                $options = array_values((array)$arr['output']);
            }
        }
        if ($options)
        {
        	$out = '';
            foreach ($options AS $key => $val)
            {
                $out .= $key == $selected ? "<option value=\"$key\" selected>$val</option>" : "<option value=\"$key\">$val</option>";
            }
        }

        echo $out;
    }

    function html_select_date($arr)
    {
        $pre = $arr['prefix'];
        if (isset($arr['time']))
        {
            if (intval($arr['time']) > 10000)
            {
                $arr['time'] = gmdate('Y-m-d', $arr['time'] + 8*3600);
            }
            $t     = explode('-', $arr['time']);
            $year  = strval($t[0]);
            $month = strval($t[1]);
            $day   = strval($t[2]);
        }
        $now = gmdate('Y', $this->_nowtime);
        if (isset($arr['start_year']))
        {
            if (abs($arr['start_year']) == $arr['start_year'])
            {
                $startyear = $arr['start_year'];
            }
            else
            {
                $startyear = $arr['start_year'] + $now;
            }
        }
        else
        {
            $startyear = $now - 3;
        }

        if (isset($arr['end_year']))
        {
            if (strlen(abs($arr['end_year'])) == strlen($arr['end_year']))
            {
                $endyear = $arr['end_year'];
            }
            else
            {
                $endyear = $arr['end_year'] + $now;
            }
        }
        else
        {
            $endyear = $now + 3;
        }

        $out = "<select name=\"{$pre}Year\">";
        for ($i = $startyear; $i <= $endyear; $i++)
        {
            $out .= $i == $year ? "<option value=\"$i\" selected>$i</option>" : "<option value=\"$i\">$i</option>";
        }
        if ($arr['display_months'] != 'false')
        {
            $out .= "</select>&nbsp;<select name=\"{$pre}Month\">";
            for ($i = 1; $i <= 12; $i++)
            {
                $out .= $i == $month ? "<option value=\"$i\" selected>" . str_pad($i, 2, '0', STR_PAD_LEFT) . "</option>" : "<option value=\"$i\">" . str_pad($i, 2, '0', STR_PAD_LEFT) . "</option>";
            }
        }
        if ($arr['display_days'] != 'false')
        {
            $out .= "</select>&nbsp;<select name=\"{$pre}Day\">";
            for ($i = 1; $i <= 31; $i++)
            {
                $out .= $i == $day ? "<option value=\"$i\" selected>" . str_pad($i, 2, '0', STR_PAD_LEFT) . "</option>" : "<option value=\"$i\">" . str_pad($i, 2, '0', STR_PAD_LEFT) . "</option>";
            }
        }

        return $out . '</select>';
    }

    function html_radios($arr)
    {
        $name    = $arr['name'];
        $checked = $arr['checked'];
        $options = $arr['options'];

        $out = '';
        foreach ($options AS $key => $val)
        {
            $out .= $key == $checked ? "<input type=\"radio\" name=\"$name\" value=\"$key\" checked>&nbsp;{$val}&nbsp;"
                : "<input type=\"radio\" name=\"$name\" value=\"$key\">&nbsp;{$val}&nbsp;";
        }

        return $out;
    }

    function html_select_time($arr)
    {
        $pre = $arr['prefix'];
        if (isset($arr['time']))
        {
            $arr['time'] = gmdate('H-i-s', $arr['time'] + 8*3600);
            $t     = explode('-', $arr['time']);
            $hour  = strval($t[0]);
            $minute = strval($t[1]);
            $second   = strval($t[2]);
        }
        $out = '';
        if (!isset($arr['display_hours']))
        {
            $out .= "<select name=\"{$pre}Hour\">";
            for ($i = 0; $i <= 23; $i++)
            {
                $out .= $i == $hour ? "<option value=\"$i\" selected>" . str_pad($i, 2, '0', STR_PAD_LEFT) . "</option>" : "<option value=\"$i\">" . str_pad($i, 2, '0', STR_PAD_LEFT) . "</option>";
            }

            $out .= "</select>&nbsp;";
        }
        if (!isset($arr['display_minutes']))
        {
            $out .= "<select name=\"{$pre}Minute\">";
            for ($i = 0; $i <= 59; $i++)
            {
                $out .= $i == $minute ? "<option value=\"$i\" selected>" . str_pad($i, 2, '0', STR_PAD_LEFT) . "</option>" : "<option value=\"$i\">" . str_pad($i, 2, '0', STR_PAD_LEFT) . "</option>";
            }

            $out .= "</select>&nbsp;";
        }
        if (!isset($arr['display_seconds']))
        {
            $out .= "<select name=\"{$pre}Second\">";
            for ($i = 0; $i <= 59; $i++)
            {
                $out .= $i == $second ? "<option value=\"$i\" selected>" . str_pad($i, 2, '0', STR_PAD_LEFT) . "</option>" : "<option value=\"$i\">$i</option>";
            }

            $out .= "</select>&nbsp;";
        }

        return $out;
    }
    
    /**
	 * 获得当前格林威治时间的时间戳
	 *
	 * @return  integer
	 */
	function gmtime()
	{
	    return (time() - date('Z'));
	}
	
	/**
	 * 获得服务器的时区
	 *
	 * @return  integer
	 */
	function server_timezone()
	{
	    if (function_exists('date_default_timezone_get'))
	    {
	        return date_default_timezone_get();
	    }
	    else
	    {
	        return date('Z') / 3600;
	    }
	}
	
	
	/**
	 *  生成一个用户自定义时区日期的GMT时间戳
	 *
	 * @access  public
	 * @param   int     $hour
	 * @param   int     $minute
	 * @param   int     $second
	 * @param   int     $month
	 * @param   int     $day
	 * @param   int     $year
	 *
	 * @return void
	 */
	function local_mktime($hour = NULL , $minute= NULL, $second = NULL,  $month = NULL,  $day = NULL,  $year = NULL)
	{
	    $timezone = isset($_SESSION['timezone']) ? $_SESSION['timezone'] : $GLOBALS['_CFG']['timezone'];
	
	    /**
	    * $time = mktime($hour, $minute, $second, $month, $day, $year) - date('Z') + (date('Z') - $timezone * 3600)
	    * 先用mktime生成时间戳，再减去date('Z')转换为GMT时间，然后修正为用户自定义时间。以下是化简后结果
	    **/
	    $time = mktime($hour, $minute, $second, $month, $day, $year) - $timezone * 3600;
	
	    return $time;
	}
	
	
	/**
	 * 将GMT时间戳格式化为用户自定义时区日期
	 *
	 * @param  string       $format
	 * @param  integer      $time       该参数必须是一个GMT的时间戳
	 *
	 * @return  string
	 */
	
	function local_date($format, $time = NULL)
	{
	    $timezone = isset($_SESSION['timezone']) ? $_SESSION['timezone'] : $GLOBALS['_CFG']['timezone'];
	
	    if ($time === NULL)
	    {
	        $time = gmtime();
	    }
	    elseif ($time <= 0)
	    {
	        return '';
	    }
	
	    $time += ($timezone * 3600);
	
	    return date($format, $time);
	}
	
	
	/**
	 * 转换字符串形式的时间表达式为GMT时间戳
	 *
	 * @param   string  $str
	 *
	 * @return  integer
	 */
	function gmstr2time($str)
	{
	    $time = strtotime($str);
	
	    if ($time > 0)
	    {
	        $time -= date('Z');
	    }
	
	    return $time;
	}
	
	/**
	 *  将一个用户自定义时区的日期转为GMT时间戳
	 *
	 * @access  public
	 * @param   string      $str
	 *
	 * @return  integer
	 */
	function local_strtotime($str)
	{
	   // $timezone = isset($_SESSION['timezone']) ? $_SESSION['timezone'] : $GLOBALS['_CFG']['timezone'];
	
		$timezone = 8;
	    /**
	    * $time = mktime($hour, $minute, $second, $month, $day, $year) - date('Z') + (date('Z') - $timezone * 3600)
	    * 先用mktime生成时间戳，再减去date('Z')转换为GMT时间，然后修正为用户自定义时间。以下是化简后结果
	    **/
		
	    $time = strtotime($str) - $timezone * 3600;
	
	    return $time;
	
	}
	
	/**
	 * 获得用户所在时区指定的时间戳
	 *
	 * @param   $timestamp  integer     该时间戳必须是一个服务器本地的时间戳
	 *
	 * @return  array
	 */
	function local_gettime($timestamp = NULL)
	{
	    $tmp = local_getdate($timestamp);
	    return $tmp[0];
	}
	
	/**
	 * 获得用户所在时区指定的日期和时间信息
	 *
	 * @param   $timestamp  integer     该时间戳必须是一个服务器本地的时间戳
	 *
	 * @return  array
	 */
	function local_getdate($timestamp = NULL)
	{
	    $timezone = isset($_SESSION['timezone']) ? $_SESSION['timezone'] : $GLOBALS['_CFG']['timezone'];
	
	    /* 如果时间戳为空，则获得服务器的当前时间 */
	    if ($timestamp === NULL)
	    {
	        $timestamp = time();
	    }
	
	    $gmt        = $timestamp - date('Z');       // 得到该时间的格林威治时间
	    $local_time = $gmt + ($timezone * 3600);    // 转换为用户所在时区的时间戳
	
	    return getdate($local_time);
	}
    
	/**
	* 转换字节数为其他单位
	*
	*
	* @param	string	$filesize	字节大小
	* @return	string	返回大小
	*/
	function sizecount($filesize) {
		if ($filesize >= 1073741824) {
			$filesize = round($filesize / 1073741824 * 100) / 100 .' GB';
		} elseif ($filesize >= 1048576) {
			$filesize = round($filesize / 1048576 * 100) / 100 .' MB';
		} elseif($filesize >= 1024) {
			$filesize = round($filesize / 1024 * 100) / 100 . ' KB';
		} else {
			$filesize = $filesize.' Bytes';
		}
		return $filesize;
	}  
	
	/**
	* 将字符串转换为数组
	*
	* @param	string	$data	字符串
	* @return	array	返回数组格式，如果，data为空，则返回空数组
	*/
	function string2array($data) 
	{
		$data = stripcslashes($data);
		if($data == '') return array();
		eval("\$array = $data;");
		return $array;
	}
	/**
	* 将数组转换为字符串
	*
	* @param	array	$data		数组
	* @param	bool	$isformdata	如果为0，则不使用new_stripslashes处理，可选参数，默认为1
	* @return	string	返回字符串，如果，data为空，则返回空
	*/
	function array2string($data, $isformdata = 1) {
		if($data == '') return '';
		if($isformdata) $data = new_stripslashes($data);
		return addslashes(var_export($data, TRUE));
	}
	/**
	 * 返回经stripslashes处理过的字符串或数组
	 * @param $string 需要处理的字符串或数组
	 * @return mixed
	 */
	function new_stripslashes($string) {
		if(!is_array($string)) return stripslashes($string);
		foreach($string as $key => $val) $string[$key] = new_stripslashes($val);
		return $string;
	}
	/**
	 * 生成上传附件验证
	 * @param $args   参数
	 * @param $operation   操作类型(加密解密)
	 */
	
	function upload_key($args, $operation = 'ENCODE') {
		$pc_auth_key = md5($_SERVER['HTTP_USER_AGENT']);
		$authkey = sys_auth($args, $operation, $pc_auth_key);
		return $authkey;
	}
		
	/**
	 * IE浏览器判断
	 */
	
	function is_ie() {
		$useragent = strtolower($_SERVER['HTTP_USER_AGENT']);
		if((strpos($useragent, 'opera') !== false) || (strpos($useragent, 'konqueror') !== false)) return false;
		if(strpos($useragent, 'msie ') !== false) return true;
		return false;
	}
	
	/**
	 * 获取$_GET值 
	 */
	function G($name)
	{
		return isset($_GET[$name]) && !empty($_GET[$name]) ? $_GET[$name] : 0;
	}
	
	/**
	 * 获取$_POST值 
	 */
	function P($name)
	{
		return isset($_POST[$name]) && !empty($_POST[$name]) ? $_POST[$name] : 0;
	}
	
	/**
	 * 创建一个JSON
	 * @access  public
	 * @param
	 * @return  void
	 */
	function make_json_result($content, $message='', $append=array())
	{
	    make_json_response($content, 0, $message, $append);
	}

	/**
	 * 创建一个JSON格式的错误信息
	 *
	 * @access  public
	 * @param   string  $msg
	 * @return  void
	 */
	function make_json_error($msg)
	{
	    make_json_response('', 1, $msg);
	}

	/**
	 * 创建一个JSON格式的数据
	 *
	 * @access  public
	 * @param   string      $content
	 * @param   integer     $error
	 * @param   string      $message
	 * @param   array       $append
	 * @return  void
	 */
	function make_json_response($content='', $error="0", $message='', $append=array())
	{
	    Rookie_Core::load_help("Rookie_JSON");
		$json = new Rookie_JSON();
	
	    $res = array('error' => $error, 'message' => $message, 'content' => $content);
	
	    if (!empty($append))
	    {
	        foreach ($append AS $key => $val)
	        {
	            $res[$key] = $val;
	        }
	    }
	
	    $val = $json->encode($res);
	
	    exit($val);
	}
	
	function make_json()
	{
		Rookie_Core::load_help("Rookie_JSON");
		return new Rookie_JSON();
	}
	
	/**
	 * 创建像这样的查询: "IN('a','b')";
	 *
	 * @access   public
	 * @param    mix      $item_list      列表数组或字符串
	 * @param    string   $field_name     字段名称
	 *
	 * @return   void
	 */
	function db_create_in($item_list, $field_name = '')
	{
	    if (empty($item_list))
	    {
	        return $field_name . " IN ('') ";
	    }
	    else
	    {
	        if (!is_array($item_list))
	        {
	            $item_list = explode(',', $item_list);
	        }
	        $item_list = array_unique($item_list);
	        $item_list_tmp = '';
	        foreach ($item_list AS $item)
	        {
	            if ($item !== '')
	            {
	                $item_list_tmp .= $item_list_tmp ? ",'$item'" : "'$item'";
	            }
	        }
	        if (empty($item_list_tmp))
	        {
	            return $field_name . " IN ('') ";
	        }
	        else
	        {
	            return $field_name . ' IN (' . $item_list_tmp . ') ';
	        }
	    }
	}
	
	/**
	 * 对 MYSQL LIKE 的内容进行转义
	 *
	 * @access      public
	 * @param       string      string  内容
	 * @return      string
	 */
	function mysql_like_quote($str)
	{
	    return strtr($str, array("\\\\" => "\\\\\\\\", '_' => '\_', '%' => '\%'));
	}
	

?>