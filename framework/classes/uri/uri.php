<?php	defined('ROOKIE') or die('No direct script access.');
/**
 * Rookie_Uri是全局范围内，处理用户的请求。
 *
 * <p>在MVC模式管理控制器，处理URI请求404未找到，重定向等</p>
 *
 * <p>这个类是紧耦合与uri_loader.php</p>
 *
 * @author 修改版 shendegang <php_java@163.com>  
 * @since 1.0
 */
class Rookie_Uri{
    /**
     * @var array routes defined in <i>caches/config/routes.php</i>
     */
    public static $route;

    /**
     * @var string $modules 当前所在的模块名称
     */
    public static $modules;
    
    /**
     * @var string $controller 当前所在的controller层
     */
    public static $controller;
    
    /**
     * @var string $action 当前所在的方法 
     */
	public static $action;
	
    /**
     * 运行Web应用程序的主要功能
     */
    public static function run()
    {
        self::throwHeader( self::routeTo() );
    }
    
    /**
     * 处理路由过程。
     * 自动路由，子文件夹，子子文件夹上的子域名支持。
     * 它可用于带或不带<I>的index.php</ I>在URI
     * @return mixed 如404或​​重定向URL的HTTP状态代码
     */
    public static function routeTo()
    {
        $router = new Rookie_Uri_Router;
        $routeRs = $router->execute(self::$route);
      	//  self::$modules = strtolower(substr($routeRs[0], 0, strpos($routeRs[0], 'Controller')));
        //self::$modules = $routeRs[2]['__routematch']['m'];
        self::$modules = $routeRs[3];
        self::$controller = strtolower(str_replace( "Controller", "",$routeRs[0]));
        self::$action = $routeRs[1];
        
        if($routeRs[0] !== null && $routeRs[1] !== null)
        {
            //调度，调用控制器类
            if($routeRs[0][0] !== '[')
            {
            	$file_name = WEBPATH . 'protected/modules/' . self::$modules .  "/{$routeRs[0]}.php";
            	if (file_exists($file_name))
            		require_once $file_name;
                else 
                	Rookie_Core::system_error();
                	//Rookie_Core::system_error("modules not sources!");
            }

            if(strpos($routeRs[0], '/') !== false)
            {
                $clsname = explode('/', $routeRs[0]);
                $routeRs[0] = $clsname[ sizeof($clsname)-1 ];
            }

            //如果定义的类的名称，使用类名来创建控制器对象
            $clsnameDefined = (sizeof($routeRs) === 4);
           // if($clsnameDefined)
               // $controller = new $routeRs[0];
          //  else
            //判断类是否存在
            if (class_exists($routeRs[0]))
            	$controller = new $routeRs[0];
            else 
           		Rookie_Core::system_error();
            	//Rookie_Core::system_error("class not sources!");

            $controller->params = $routeRs[2];

            if(isset($controller->params['__extension']) === true)
            {
                $controller->extension = $controller->params['__extension'];
                unset($controller->params['__extension']);
            }
			if(isset($controller->params['__routematch']) === true)
			{
                $controller->routematch = $controller->params['__routematch'];
                unset($controller->params['__routematch']);
            }

            if($_SERVER['REQUEST_METHOD'] === 'PUT')
                $controller->init_put_vars();

            //在运行之前，一般用于ACL的AUTH
            if($clsnameDefined)
            {
                if($rs = $controller->beforeRun($routeRs[3], $routeRs[1]))
                {
                    return $rs;
                }
            }
            else
            {
                if($rs = $controller->beforeRun($routeRs[0], $routeRs[1]))
                {
                    return $rs;
                }
            }

            //获取类里的所以方法名
            $methods = get_class_methods($routeRs[0]);
            if (in_array($routeRs[1], $methods))
            		$routeRs = $controller->$routeRs[1]();
            else 
            		Rookie_Core::system_error();
            	//Rookie_Core::system_error("method not sources!");
            $controller->afterRun($routeRs);
            return $routeRs;      
        }
        else
        {
            self::throwHeader(404);
        }
    }

    /**
     * 重排的URI内部路由
     * @param string $routeuri route uri to redirect to
     * @param bool $is404 send a 404 status in header
     */
    public function reroute($routeuri, $is404 = false)
    {
        $_SERVER['REQUEST_URI'] = $routeuri;
        
        if($is404===true)
            header('HTTP/1.1 404 Not Found');
            
        $this->routeTo();
    }


    /**
     * 分析控制器返回值，并发送相应的头，如404，302，301，重定向到内部的路线。
     *
     * <p>这是非常搜索引擎友好的，但你需要知道HTTP状态代码的基础。</p>
     * <p>自动处理404，包括错误文件或重定向到内部路线
     * 处理错误的基础上，配置ERROR_404_DOCUMENT</ B>和<b> ERROR_404_ROUTE</ B></ P>
     * <P>控制器返回值的例子：</ P>
     * <code>
     * 404                                  #send 404 header
     * array('/internal/route', 404)        #send 404 header & redirect to an internal route
     * 'http://www.google.com'              #redirect to URL. default 302 Found sent
     * array('http://www.google.com',301)   #redirect to URL. forced 301 Moved Permenantly sent
     * array('/hello/sayhi', 'internal')    #redirect internally, 200 OK
     * </code>
     * @param mixed $code
     */
    public static function throwHeader($code)
    {
    	//检查 HTTP 报头是否发送/已发送到何处。
        if(headers_sent())
        {
            return;
        }
        
        if($code!=null)
        {
            if(is_int($code))
            {
            	//加载错误页面
            	echo $code;
            	
                exit;
               
            }
            elseif(is_string($code))
            {
                Rookie_Uri_Router::redirect($code);
            }
            elseif(is_array($code))
            {
                if($code[1]=='internal')
                {
                    $this->reroute($code[0]);
                    exit;
                }
                elseif($code[1]===404)
                {
                    $this->reroute($code[0],true);
                    exit;
                }
                elseif($code[1]===302)
                {
                    Rookie_Uri_Router::redirect($code[0],true, $code[1], array("HTTP/1.1 302 Moved Temporarily"));
                }
                else
                {
                    Rookie_Uri_Router::redirect($code[0],true, $code[1]);
                }
            }
        }
    }

    /**
     * 要调试DooPHP的诊断视图的变量
     * @param mixed $var The variable to view in diagnostics.
     */
    public function debug($var)
    {
        throw new Rookie_Exception($var);
    }

}