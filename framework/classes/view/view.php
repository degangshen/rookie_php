<?php	defined('ROOKIE') or die('No direct script access.');
/**
 * 模块解析类
 *
 * @author Leng Sheng Hong <darkredz@gmail.com> 
 * @author 修改版 shendegang <php_java@163.com>  
 * @copyright Copyright &copy; 2009 Leng Sheng Hong
 */
class Rookie_View {
	//控制层
    public $controller;
    //数据
    public static $data;
    //tag标签
    protected static $tags;
    //主要呈现文件夹
    protected static $mainRenderFolder;
    //tag类
    protected $tagClassName;
    //tag 模块的类
    protected $tagModuleName;

    /**
     * 确定使用哪个类来作为模板标签。
     *
     * If $module is equal to '/', the main app's template tag class will be used.
     * 如果$module等于'/'，主要的应用程序的模板标签类将被使用。
     *
     * @param string $class 模板标签类的名称
     * @param string $module 该模块的文件夹名称。定义该模块的名称，标签类，如果从另一个模块。
     */
    public function setTagClass($class, $module=Null){
        $this->tagClassName = $class;
        $this->tagModuleName = $module;
    }
    
    /**
     * Includes the native PHP template file to be output.
     * 包括原生的PHP模板文件输出。
     * @param string $file PHP template file name without extension .php PHP模板不带扩展名的PHP文件的名称。
     * @param array $data 要在模板视图中使用的数据的关联数组。
     * @param object $controller 控制器对象，通过这使视图中，您可以访问控制器。
     * @param bool $includeTagClass如果为true，DooView将确定包括模板标签类。否则，没有文件将被加载
     */
    public function renderc($file, $data=NULL, $controller=NULL, $includeTagClass=TRUE){
        $this->data = $data;
        $this->controller = $controller;
        if($includeTagClass===TRUE)
            $this->loadTagClass();
        include CACHE_PATH . 'caches_template'.DIRECTORY_SEPARATOR.'default'.DIRECTORY_SEPARATOR.M.DIRECTORY_SEPARATOR."$file.php";
    }

	/**
     * 引入模板的视图文件
     * @param string $file File name without extension (.php)
     */
    public function inc($file){
        include CACHE_PATH . 'caches_template'.DIRECTORY_SEPARATOR.'default'.DIRECTORY_SEPARATOR.M.DIRECTORY_SEPARATOR."$file.php";
    }

//    public function  __call($name,  $arguments) {
//        if($this->controller!=NULL){
//            return call_user_func_array(array(&$this->controller, $name), $arguments);
//        }
//    }
//
//    public function  __get($name) {
//        if($this->controller!=NULL){
//            return $this->controller->{$name};
//        }
//    }
//    
    /**
     * Writes the generated output produced by render() to file.
     * 写入的render（）文件生成的输出。
     * @param string $path Path to 路径保存生成的输出。
     * @param string $templatefile 模板文件名（无扩展名）
     * @param array $data  在模板文件中要使用的数据的关联数组。例如。 <B>$data['username']</ b>，您应该使用<B>{{username}}</ B>在模板中。
     * @return string|false 渲染输出的文件名保存（HTML）。
     */
    public function saveRendered($path, $templatefile, $data=NULL){
        ob_start();
        $this->render($templatefile, $data, null, true);
        $data = ob_get_contents();
        ob_end_clean();
        if(file_put_contents($path, $data)>0){
            $filename = explode('/',$path);
            return $filename[sizeof($filename)-1];
        }
        return false;
    }

    /**
     * 写入renderc（）生成的输出文件。
     * @param string $path 路径保存生成的输出
     * @param string $templatefile 模板文件名（无扩展名）
     * @param array $data 在模板文件中要使用的数据的关联数组。例如。 <B>$data['username']</ b>，您应该使用<B>{{username}}</ B>在模板中。
     * @param object $controller 控制器对象，通过这使视图中，您可以访问控制器。
     * @param bool $includeTagClass 如果为true，DooView将确定包括模板标签类。否则，没有文件将被加载
     * @return string|false 渲染输出的文件名保存（HTML）。
     */
    public function saveRenderedC($path, $templatefile, $data=NULL, $controller=NULL, $includeTagClass=TRUE){
        ob_start();
        $this->renderc($templatefile, $data, $controller, $includeTagClass);
        $data = ob_get_contents();
        ob_end_clean();
        if(file_put_contents($path, $data)>0){
            $filename = explode('/',$path);
            return $filename[sizeof($filename)-1];
        }
        return false;
    }
    
    /**
     * 呈现视图文件，生成的视图模板的编译版本，如果有必要
     * @param string $file 模板文件名（无扩展名）
     * @param array $data 在模板文件中要使用的数据的关联数组。例如。 <B>$data['username']</ b>，您应该使用<B>{{username}}</ B>在模板中。
     * @param bool $process 如果是TRUE，检查模板的最后修改时间对已编译的版本。再生，如果模板是新的。
     * @param bool $forceCompile 最后修改时间的检查和强制忽略编译每次访问的模板
     */
    public static function render($file, $data=NULL, $process=NULL, $forceCompile=false){
		
    	//判断模板是否总是编译
        if(Rookie_Core::$template_compile_always === true){
            $process = $forceCompile = true;
        }
        //如果过程中没有设置，然后检查应用程序的模式，如果生产模式，跳过这个过程（假），只包含编译后的文件
        else if($process===NULL){
            $process = (Rookie_Core::$app_mode!='prod');
        }

        //只包含已编译的文件，如果过程是假的
        if($process!=true){
            //包括用户定义的模板使用模板标签
           // $this->loadTagClass();
            include CACHEPATH . 'caches_template'.DIRECTORY_SEPARATOR.'default'.DIRECTORY_SEPARATOR.Rookie_Uri::$modules.DIRECTORY_SEPARATOR."$file.php";
        }
        else{
            $cfilename = CACHEPATH . 'caches_template'.DIRECTORY_SEPARATOR.'default'.DIRECTORY_SEPARATOR.Rookie_Uri::$modules.DIRECTORY_SEPARATOR."$file.php";
            $vfilename = WEBPATH . 'templates'.DIRECTORY_SEPARATOR.'default'.DIRECTORY_SEPARATOR.Rookie_Uri::$modules.DIRECTORY_SEPARATOR."$file.html";
            //如果文件存在，并且没有年龄比HTML模板文件，包括编译PHP和退出函数
            if(!$forceCompile){
                if(file_exists($cfilename)){
                    if(filemtime($cfilename)>=filemtime($vfilename)){
                       // $this->setTags();
                        include $cfilename;
                        return;
                    }
                }
            }
            
            self::$data = $data;
            self::compile($file, $vfilename, $cfilename);
            include $cfilename;
        }
    }

    /**
     * 呈现布局
     * @param string $layoutName 布局的名称
     * @param string $viewFile 查看文件的名称（没有扩展名。HTML）
     * @param array $data 在模板文件中要使用的数据的关联数组。例如。 <B>$data['username']</ b>，您应该使用<B>{{username}}</ B>在模板中。
     * @param bool $process 如果是TRUE，检查模板的最后修改时间对已编译的版本。再生，如果模板是新的。
     * @param bool $forceCompile 最后修改时间的检查和强制忽略编译每次访问的模板。
     */
    public function renderLayout($layout) {

    	
    	$layoutArges = str_replace('\'', '', $layout[1]);
    	$layoutArges = explode(',', $layoutArges);
    	
    	list($layoutName, $viewFile) = $layoutArges;
    	
        $compiledViewFile = $layoutName . '/' . $viewFile;

        if(Init::load_config('app', 'TEMPLATE_COMPILE_ALWAYS') == true){
            $process = $forceCompile = true;
        }
        //如果过程中没有设置，然后检查应用程序的模式，如果生产模式，跳过这个过程（假），只包含编译后的文件
        else if($process===NULL){
            $process = (Init::load_config('app', 'APP_MODE') != 'prod');
        }

        //只包含已编译的文件，如果过程是假的
        if($process!=true){
            //包括用户定义的模板使用模板标签
            //$this->loadTagClass();
            include CACHE_PATH . 'caches_template'.DIRECTORY_SEPARATOR.'default'.DIRECTORY_SEPARATOR.M.DIRECTORY_SEPARATOR."$layoutName.php";
        }
        else{
            $lfilename = APP_PATH . 'templates'.DIRECTORY_SEPARATOR.'default'.DIRECTORY_SEPARATOR.'layout'.DIRECTORY_SEPARATOR."$layoutName.html";
            $cfilename = CACHE_PATH . 'caches_template'.DIRECTORY_SEPARATOR.'default'.DIRECTORY_SEPARATOR.M.DIRECTORY_SEPARATOR."$layoutName.php";
            $vfilename = APP_PATH . 'templates'.DIRECTORY_SEPARATOR.'default'.DIRECTORY_SEPARATOR.'layout'.DIRECTORY_SEPARATOR."$layoutName.html";
            
            //如果文件存在，并且没有年龄比HTML模板文件和布局文件，包括编译PHP和退出函数
            if(!$forceCompile){
                if(file_exists($cfilename)){
                    if(filemtime($cfilename)>=filemtime($vfilename) && filemtime($cfilename)>=filemtime($lfilename)){
                        $this->setTags();
                        include $cfilename;
                        return;
                    }
                }
            }
            $this->data = $data;
            $this->compileLayout($compiledViewFile, $lfilename, $vfilename, $cfilename);
            include $cfilename;
        }

    }

    /**
     * 包含视图块的内容与布局
     * @var array
     */
    private $viewBlocks = null;

    /**
     * 解析并编译成一个布局，以期填补占位符
     * 存储生成的视图文件，然后正常处理DooView:compile
     * @param string $viewFile 不带扩展名的  extension .html
     * @param string $lfilename 布局文件的完整路径
     * @param string $vfilename 被合并到布局的完整路径
     * @param string $cfilename 编译后的文件保存到的完整路径
     */
    protected function compileLayout($viewFile, $lfilename, $vfilename, $cfilename) {

        $layout = file_get_contents($lfilename);
        $view = file_get_contents($vfilename);

        // 确定的区块内查看文件
        // <!-- block:NAME -->CONTENT<!-- endblock -->
        $this->viewBlocks = array();
        // We use \s\S to get ANY character including newlines etc as '.' will not get new lines
        //我们使用\ S \ S任何字符，包括换行符等作为'.'不会得到新的生产线
        // Also use +? and *? so as to use non greedy matching
        //同时使用+？ *？使用非贪婪匹配
        preg_replace_callback('/<!-- block:([^\t\r\n]+?) -->([\s\S]*?)<!-- endblock -->/', array( &$this, 'storeViewBlock'), $view);
        $compiledLayoutView = preg_replace_callback('/<!-- placeholder:([^\t\r\n]+?) -->([\s\S]*?)<!-- endplaceholder -->/', array( &$this, 'replacePlaceholder'), $layout);


        $this->mainRenderFolder = $viewFile;

        //--------------------------- 解析 -----------------------------
        //如果没有编译过的文件存在或已编译的文件是旧，产生一个新的
        $str = $this->compileTags($compiledLayoutView);

		Doo::loadHelper('DooFile');
		$fileManager = new DooFile(0777);
		$fileManager->create($cfilename, $str, 'w+');

    }


    /**
     * 解析和编译模板文件。protected/viewc文件夹中生成的模板  
     * @param string $file 模板不带扩展名的文件名 extension .html
     * @param string $vfilename 模板文件的完整路径
     * @param string $cfilename 编译后的文件保存到的完整路径
     */
    protected static function compile($file, $vfilename, $cfilename){
        self::$mainRenderFolder = $file;

        //--------------------------- 解析 -----------------------------
        //如果没有编译过的文件存在或已编译的文件是旧，产生一个新的
        $str = self::compileTags(file_get_contents($vfilename));

		Init::load_sys_class('helper/DooFile');
		$fileManager = new DooFile(0777);
		$fileManager->create($cfilename, $str, 'w+');
    }

    /**
     * 加载的模板类，并返回类的名称。
     * @return string 加载的类的名称
     */
    public function loadTagClass(){
        /* 如果包括标签类没有定义主应用程序的加载TemplateTag
         * 否则，如果render（）方法被称为一个模块，加载ModulenameTag*/
		$tagFile = '';

        if( !isset($this->tagClassName) ){
            if( Init::load_config('app','PROTECTED_FOLDER_ORI') === FALSE ){
                $tagFile = APP_PATH.'/plugin/TemplateTag.php';
                $tagcls = 'TemplateTag';
            }else{
                $tagcls = explode('/', Init::load_config('app', 'PROTECTED_FOLDER'));
                $tagcls = ucfirst($tagcls[sizeof($tagcls)-2]) . 'Tag';
                $tagFile = APP_PATH . '/plugin/' . $tagcls .'.php';
            }
        }else{
            //加载的主要应用程序的TemplateTag如果模块是'/'
            if($this->tagModuleName=='/'){
                $tagFile = Doo::conf()->SITE_PATH . Doo::conf()->PROTECTED_FOLDER_ORI . 'plugin/'. $this->tagClassName .'.php';
            }
            else if($this->tagModuleName===Null){
                $tagFile = Doo::conf()->SITE_PATH . Doo::conf()->PROTECTED_FOLDER . 'plugin/'. $this->tagClassName .'.php';
            }
            else{
                if(isset(Doo::conf()->PROTECTED_FOLDER_ORI))
                    $tagFile = Doo::conf()->SITE_PATH . Doo::conf()->PROTECTED_FOLDER_ORI .'module/'. $this->tagModuleName . '/plugin/'. $this->tagClassName .'.php';
                else
                    $tagFile = Doo::conf()->SITE_PATH . Doo::conf()->PROTECTED_FOLDER .'module/'. $this->tagModuleName . '/plugin/'. $this->tagClassName .'.php';
            }
            $tagcls = $this->tagClassName;
        }
		if (file_exists($tagFile)) {
			require_once $tagFile;
			return $tagcls;
		} else {
			return false;
		}
    }


    /**
     * DooPHP模板标签和处理字符串替换它们需要与有关的PHP代码
     * @param string $str 这是查看文件的HTML模板标记
     * @return string The PHP markedup查看文件版本
     */
    private static function  compileTags($str) {
        //引入自定义标签模板
        if(self::$tags===NULL){
            //if(Init::load_config('app','TEMPLATE_TAGS')){
             //   $globals_tags = $GLOBALS['globals_tags'];
              //  $this->tags = $globals_tags;
           // }
        }
        if( Rookie_Core::$template_allow_php === TRUE ){
            if( Rookie_Core::$template_allow_php === False ){
                $str = preg_replace('/<\?(php|\=|\+)?([\S|\s]*)\?>/Ui', '', $str);
            }
        }else{
            $str = preg_replace_callback('/<\?(php|\=|\+)?([\S|\s]*)\?>/Ui', array( 'convertPhpFunction'), $str);
        }
 		//convert end loop
        $str = str_replace('<!-- endloop -->', '<?php endforeach; ?>', $str);

        //convert end for
        $str = str_replace('<!-- endfor -->', '<?php endforeach; ?>', $str);

        //convert variables to static string <p>{{+username}}</p> becomes <p>myusernamevalue</p>
        $str = preg_replace_callback('/{{\+([^ \t\r\n\(\)\.}]+)}}/', array( &$this, 'writeStaticVar'), $str);

        $str = preg_replace('/<!--{{([^ \t\r\n\(\)\.}]+)}}-->/', "<?php echo $1; ?>", $str);
        //convert variables {{username}}
        $str = preg_replace('/{{([^ \t\r\n\(\)\.}]+)}}/', "<?php echo \$data['$1']; ?>", $str);

		//convert non $data key variables {{$user.john}} {{$user.total.male}}
        $str = preg_replace_callback('/{{\$([^ \t\r\n\(\)\.}]+)\.([^ \t\r\n\(\)}]+)}}/', array( &$this, 'convertNonDataVarKey'), $str);

        //convert key variables {{user.john}} {{user.total.male}}
        $str = preg_replace_callback('/{{([^ \t\r\n\(\)\.}]+)\.([^ \t\r\n\(\)}]+)}}/', array( &$this, 'convertVarKey'), $str);

        //convert start loop <!--# loop users --> <!--# loop users' value --> <!--# loop users' value' value -->
        $str = preg_replace_callback('/<!-- loop ([^ \t\r\n\(\)}\']+).* -->/', array( &$this, 'convertLoop'), $str);

        //convert variable in loop {{user' value}}  {{user' value' value}}
        $str = preg_replace_callback('/{{([^ \t\r\n\(\)\.}\']+)([^\t\r\n\(\)}{]+)}}/', array( &$this, 'convertVarLoop'), $str);

		$str = preg_replace_callback('/{{([^ \t\r\n\(\)}]+?)\((.*?)\)}}/', array( &$this, 'convertFunction'), $str);

        //convert start of for loop
        $str = preg_replace_callback('/<!-- for ([^\t\r\n\(\)}{]+) -->/', array( &$this, 'convertFor'), $str);

        //convert else
        $str = str_replace('<!-- else -->', '<?php else: ?>', $str);

        //convert end if
        $str = str_replace('<!-- endif -->', '<?php endif; ?>', $str);

        // convert set
        $str = preg_replace_callback('/<!-- set ([^ \t\r\n\(\)\.}]+) as (.*?) -->/U', array( &$this, 'convertSet'), $str);

        //convert if and else if condition <!-- if expression --> <!-- elseif expression -->  only functions in template_tags are allowed
        $str = preg_replace_callback('/<!-- (if|elseif) ([^\t\r\n}]+) -->/U', array( &$this, 'convertCond'), $str);

        //convert else
        $str = str_replace('<!-- continue -->', '<?php continue; ?>', $str);

        //convert else
        $str = str_replace('<!-- break -->', '<?php break; ?>', $str);

        //convert end cache <!-- endcache -->
        $str = str_replace('<!-- endcache -->', "\n<?php Doo::cache('front')->end(); ?>\n<?php endif; ?>", $str);

        //convert cache <!-- cache('partial_id', 60) -->
        $str = preg_replace_callback('/<!-- cache\(([^\t\r\n}\)]+)\) -->/', array( &$this, 'convertCache'), $str);

        //convert layout <!-- layout('file_name','viewFile') -->
        $str = preg_replace_callback('/<!-- layout\(([^\t\r\n}\)]+)\) -->/', array( &$this, 'renderLayout'), $str);
        
        //convert include to php include and parse & compile the file, if include file not exist Echo error and exit application
        // <?php echo $data['file']; chars allowed for the grouping
        $str = preg_replace_callback('/<!-- include [\'\"]{1}([^\t\r\n\"]+).*[\'\"]{1} -->/', array( &$this, 'convertInclude'), $str);

        //删除注释
      //  if(Init::load_config('app','TEMPLATE_SHOW_COMMENT')!=true){
            //$str = preg_replace('/<!-- comment -->.+<!-- endcomment -->/s', '', $str);
            $str = str_replace('<!-- comment -->', '<?php /** ', $str);
            $str = str_replace('<!-- endcomment -->', ' */ ?>', $str);
        //}

        return $str;
    }

	private function template($file ,$m = M) {
    	return '<?php include CACHE_PATH."caches_template".DIRECTORY_SEPARATOR."default".DIRECTORY_SEPARATOR.M.DIRECTORY_SEPARATOR.$t[0].".php"; ?>';
	}

    private function writeStaticVar($matches){
        return $this->data[$matches[1]];
    }

    private static function convertPhpFunction($matches){
        if(stripos($matches[0], '<?php')!==0 && strpos($matches[0], '<?=')!==0 && strpos($matches[0], '<?+')!==0  && strpos($matches[0], '<? ')!==0 ){
            return $matches[0];
        }

        $str = preg_replace_callback('/([^ \t\r\n\(\)}]+)([\s\t]*?)\(/', array( &$this, 'parseFunc'), $matches[2]);
        if(strpos($str, 'php')===0)
            $str = substr($str, 3);

        //if short tag <?=, convert to <?php echo
        if($matches[2][0]=='='){
            $str = substr($str, 1);
            return '<?php echo ' . $str .' ?>';
        }
        //写变量的值
        else if($matches[2][0]=='+'){
            $str = substr($str, 1);
            return eval('return ' . $str);
        }

        return '<?php ' . $str .' ?>';
    }

    private function parseFunc($matches){
        //对模板标签的匹配和检查功能名称
        if(!empty($matches[1])){
            $funcname = trim(strtolower($matches[1]));
            if($funcname[0]=='+' || $funcname[0]=='=')
                $funcname = substr($funcname, 1);
                
            $controls = array('if','elseif','else if','while','switch','for','foreach','switch','return','include','require','include_once','require_once','declare','define','defined','die','constant','array');

            //跳过检查的静态方法 usage: TemplateTag::go(), Doo::conf()
            if(stripos($funcname, $this->tags['_class'] . '::')===False && stripos($funcname, 'Doo')===False){
                //$funcname = str_ireplace($this->tags['_class'] . '::', '', $funcname);
                if(!in_array($funcname, $controls)){
                    if(!in_array($funcname, $this->tags)) {
                        return 'function_deny(';
                    }
                }
            }
        }
        return $matches[1].'(';
    }

    private function stripCommaStr($matches){
        $str = implode('\/\.\;', explode(',', $matches[0]) );
        $str = substr($str, 1, strlen($str)-2);
        return "'".$str."'";
    }

    private function convertFunction($matches) {
        if($matches[1][0]=='+'){
            $matches[1] = substr($matches[1], 1);
            $writeStaticValue = true;
        }
        if(!in_array(strtolower($matches[1]), $this->tags)) {
            return '<span style="color:#ff0000;">Function '.$matches[1].'() Denied</span>';
        }

        $functionName = $matches[1];
        if(isset($this->tags['_methods']) && in_array($functionName, $this->tags['_methods'])===True){
            $functionName = $this->tags['_class'] . '::' . $functionName;
        }

        //replace , to something else if it's in a string parameter
        if(strpos($matches[2], ',')!==False){
            $matches[2] = preg_replace_callback('/\"(.+)\"/', array( &$this, 'stripCommaStr'), $matches[2]);
        }

		$stmt = str_replace('<?php echo ', '', $matches[2]);
        $stmt = str_replace('; ?>', '', $stmt);
        $parameters = explode(',', $stmt);

        $args = '';

        foreach ($parameters as $param) {
            $param = trim($param);
            if (strlen($args) > 0) {
                $args .= ', ';
            }

            // Is a number
            if (preg_match('/^[0-9]*\\.?[0-9]{0,}$/', $param)) {
                $args .= $param;
            }
            // Is a string 'anything' OR "anything"
            elseif (preg_match('/^[\'\"].*[\'\"]$/', $param)) {
                $args .= str_replace('\/\.\;', ',', $param);
            }
            elseif (strtolower($param)=='true' || strtolower($param)=='false') {
                $args .= $param;
            }
            // Got parameter values to handle
            else {
                $args .= $this->extractObjectVariables($param);
            }
        }

        //if + in front, write the value of the function call
        if(!empty($writeStaticValue)){
            return eval("return {$functionName}($args);");
        }

        return "<?php echo {$functionName}($args); ?>";

    }

    private function checkFuncAllowed($matches){
        if(!in_array(strtolower($matches[1]), $this->tags))
            return 'function_deny('. $matches[2] .')';
        return $matches[1].'('. $matches[2] .')';
    }

    private function storeViewBlock($matches){
        // Store blocks as blockName => blockContent
        $this->viewBlocks[$matches[1]] = $matches[2];
        return '';
    }

    private function replacePlaceholder($matches) {
        $blockName = $matches[1];
        // If the block has been defined in the view then use it otherwise
        // use the default from the layout
        if (isset( $this->viewBlocks[$matches[1]] )) {
            return $this->viewBlocks[$matches[1]];
        } else {
            return $matches[2];
        }
    }

    private function convertCache($matches){
		$data = str_replace(array('<?php echo ', '; ?>'), '', $matches[1]);
        $data = explode(',', $data);
        if(sizeof($data)==2){
            $data[1] = intval($data[1]);
            return "<?php if (!Doo::cache('front')->getPart({$data[0]}, {$data[1]})): ?>\n<?php Doo::cache('front')->start({$data[0]}); ?>";
        }else{
            return "<?php if (!Doo::cache('front')->getPart({$data[0]})): ?>\n<?php Doo::cache('front')->start({$data[0]}); ?>";
        }
    }

    private function convertCond($matches){
        //echo '<h1>'.str_replace('>', '&gt;', str_replace('<', '&lt;', $matches[2])).'</h1>';
        $stmt = str_replace('<?php echo ', '', $matches[2]);
        $stmt = str_replace('; ?>', '', $stmt);
        //echo '<h1>'.$stmt.'</h1>';

        //prevent malicious HTML designers to use function with spaces
        //eg. unlink        ( 'allmyfiles.file'  ), php allows this to happen!!
        $stmt = preg_replace_callback('/([a-z0-9\-_]+)[ ]*\([ ]*([^ \t\r\n}]+)\)/i', array( &$this, 'checkFuncAllowed'), $stmt);

        //echo '<h1>'.$stmt.'</h1>';
        switch($matches[1]){
            case 'if':
                return '<?php if( '.$stmt.' ): ?>';
            case 'elseif':
                return '<?php elseif( '.$stmt.' ): ?>';
        }
    }

    private function convertFor($matches) {
        $expr = str_replace('<?php echo ', '', $matches[1]);
        $expr = str_replace('; ?>', '', $expr);

        //for: i from 0 to 10
		if (preg_match('/([a-z0-9\-_]+?) from ([^ \t\r\n\(\)}]+) to ([^ \t\r\n\(\)}]+)( step ([^ \t\r\n\(\)}]+))?/i', $expr)){
			$expr = preg_replace_callback('/([a-z0-9\-_]+?) from ([^ \t\r\n\(\)}]+) to ([^ \t\r\n\(\)}]+)( step ([^ \t\r\n\(\)}]+))?/i', array( &$this, 'buildRangeForLoop'), $expr);
		}
		// for: 'myArray as key=>val'
		else if (preg_match('/([a-z0-9\-_]+?) as ([a-z0-9\-_]+)[ ]?=>[ ]?([a-z0-9\-_]+)/i', $expr)) {
			$expr = preg_replace_callback('/([a-z0-9\-_]+?) as ([a-z0-9\-_]+)[ ]?=>[ ]?([a-z0-9\-_]+)/i', array( &$this, 'buildKeyValForLoop'), $expr);
		}
		// for: 'myArray as val'
		else if (preg_match('/([a-z0-9\-_]+?) as ([a-z0-9\-_]+)/i', $expr)) {
			$expr = preg_replace_callback('/([a-z0-9\-_]+?) as ([a-z0-9\-_]+)/i', array( &$this, 'buildValForLoop'), $expr);
		}
        return $expr;
    }

    private function buildRangeForLoop($matches) {
        $stepBy = isset($matches[5]) ? $matches[5] : 1;
        return '<?php foreach(range(' . $matches[2] . ', ' . $matches[3] . ', ' . $stepBy . ') as $data[\'' . $matches[1] . '\']): ?>';
    }

	private function buildKeyValForLoop($matches) {
		return '<?php foreach($data[\''.$matches[1].'\'] as $'.$matches[2].'=>$'.$matches[3].'): ?>';
	}

	private function buildValForLoop($matches) {
		return '<?php foreach($data[\''.$matches[1].'\'] as $'.$matches[2].'): ?>';
	}

    private function convertLoop($matches){
        $looplevel = sizeof(explode('\' ', $matches[0]));
        if(strpos($matches[0], "' ")!==FALSE){
            $strValue = str_repeat("' value", $looplevel-1);
            $loopStr = "<!-- loop {$matches[1]}$strValue.";
            if( strpos($matches[0], $loopStr)===0){
                $loopStr = substr($matches[0], strlen($loopStr));
                $loopStr = str_replace(' -->', '', $loopStr);
                $param = explode('.', $loopStr);
                $varBck ='';
                foreach($param as $pa){
                    if(strpos($pa, '@')===0){
                        $varBck .= '->' . substr($pa, 1);
                    }else{
                        $varBck .= "['$pa']";
                    }
                }
                $thislvl = $looplevel-1;
                $loopname = "\$v$thislvl$varBck";
            }else{
                $loopname = ($looplevel<2)? '$data[\''.$matches[1].'\']' : '$v'. ($looplevel-1);
            }
        }
        else if(strpos($matches[1], '.@')!==FALSE){
            $varname = str_replace('.@', '->', $matches[1]);
            $varname = explode('->', $varname);
            $firstname = $varname[0];
            array_splice($varname, 0, 1);
            $loopname =  '$data[\''.$firstname.'\']->' . implode('->', $varname) ;
        }
        else if(strpos($matches[1], '.')!==FALSE){
            $varname = explode('.',$matches[1]);
            $firstname = $varname[0];
            array_splice($varname, 0, 1);
            $loopname =  '$data[\''.$firstname .'\'][\''. implode("']['", $varname) .'\']';
        }
        else{
            $loopname = ($looplevel<2)? '$data[\''.$matches[1].'\']' : '$v'. ($looplevel-1);
        }
        return '<?php foreach('.$loopname.' as $k'.$looplevel.'=>$v'.$looplevel.'): ?>';
    }

	private function convertInclude($matches){
        $file = $matches[1];

        /*include file is a Variable <!-- include "{{file}}" -->,
         *modify the converted string <?php echo $data['file']; ?> to $data['file']; and return it to be written to file
         * <!-- include "<?php echo $data['file']; ?>" --> after convert to var */
        $includeVarPos = strpos($file, '<?php echo $data');
        if($includeVarPos===0){
            $file = str_replace('<?php echo ', '', $file);
            $file = str_replace('; ?>', '', $file);
            $dynamicFilename = '<?php include "{'.$file.'}.php"; ?>';

            //get the real template file name from $data passed in by users
            $file = $this->data[str_replace('\']', '', str_replace('$data[\'', '', $file) )];
        }

        //if first char is '/' then load the files in view root 'view' folder, <!-- '/admin/index' --> view/admin/index.html
        if(substr($file, 0,1)=='/'){
            $file = substr($file, 1);
            $cfilename = CACHE_PATH . 'caches_template'.DIRECTORY_SEPARATOR.'default'.DIRECTORY_SEPARATOR.M.DIRECTORY_SEPARATOR."$file.php";
            $vfilename = APP_PATH . 'templates'.DIRECTORY_SEPARATOR.'default'.DIRECTORY_SEPARATOR.M.DIRECTORY_SEPARATOR."$file.html";;
        }
        else{
            $folders = explode('/', $this->mainRenderFolder);
            //$file = implode('/', array_splice($folders, 0, -1)).'/'.$file;
            $cfilename = CACHE_PATH . 'caches_template'.DIRECTORY_SEPARATOR.'default'.DIRECTORY_SEPARATOR.M.DIRECTORY_SEPARATOR."$file.php";
            $vfilename = APP_PATH . 'templates'.DIRECTORY_SEPARATOR.'default'.DIRECTORY_SEPARATOR.M.DIRECTORY_SEPARATOR."$file.html";
        }

        if(!file_exists($vfilename)){
            echo "<span style=\"color:#ff0000\">Include view file <strong>$file.html</strong> not found</span>";
            exit;
        }else{
            if(file_exists($cfilename)){
                if(filemtime($vfilename)>filemtime($cfilename)){
                    $this->compile($file, $vfilename, $cfilename);
                }
            }else{
                $this->compile($file, $vfilename, $cfilename);
            }
        }

        if(isset ($dynamicFilename) )
            return $dynamicFilename;
            
        return '<?php include CACHE_PATH . "caches_template".DIRECTORY_SEPARATOR. "default".DIRECTORY_SEPARATOR.M."/'.$file.'.php"; ?>'; 
    }
    
    private function convertSet($matches) {

        $expr = str_replace('<?php echo ', '', $matches[2]);
        $expr = str_replace('; ?>', '', $expr);
        $expr = preg_replace_callback('/([a-z0-9\-_]+)[ ]*\([ ]*([^ \t\r\n}]+)\)/i', array( &$this, 'checkFuncAllowed'), $expr);
        
        return '<?php $data[\'' . $matches[1] . '\'] = ' . $expr . '; ?>';
    }

    private function extractObjectVariables($str) {

        $varname = '';
        $args = '';

        if(strpos($str, '.@')!==FALSE){
            $properties = explode('.@', $str);
            
            if(strpos($properties[0], "' ")!==FALSE){
                $looplevel = sizeof(explode('\' ', $properties[0]));

                //if ' key found that it's a key $k1
                if(strpos($properties[0],"' key")!==FALSE || strpos($properties[0],"' k")!==FALSE){
                    $varname = '$k' . ($looplevel-1);
                }else{
                    $varname = '$v' . ($looplevel-1);

                    //remove the variable part with the ' key or  ' value
                    array_splice($properties, 0, 1);

                    //join it up as array $v1['attachment']['pdf']   from  {{upper(msgdetails' value.attachment.pdf)}}
                    $varname .= "->". implode("->", $properties);
                }
            }else{
                $objname = $properties[0];
                array_splice($properties, 0, 1);
                $varname .= "\$data['$objname']->". implode("->", $properties);
            }

        } else if(strpos($str, '.')!==FALSE){
            $properties = explode('.', $str);
            if(strpos($properties[0], "' ")!==FALSE){
                $looplevel = sizeof(explode('\' ', $properties[0]));

                //if ' key found that it's a key $k1
                if(strpos($properties[0],"' key")!==FALSE || strpos($properties[0],"' k")!==FALSE){
                    $varname = '$k' . ($looplevel-1);
                }else{
                    $varname = '$v' . ($looplevel-1);

                    //remove the variable part with the ' key or  ' value
                    array_splice($properties, 0, 1);

                    //join it up as array $v1['attachment']['pdf']   from  {{upper(msgdetails' value.attachment.pdf)}}
                    $varname .= "['". implode("']['", $properties) ."']";
                }
            }else{
                $varname .= "\$data['". implode("']['", $properties) ."']";
            }
        } else {
            //if the function found used with a key or value in a loop, then use $k1,$k2 or $v1,$v2 instead of $data
            if(strpos($str, "' ")!==FALSE){
                $looplevel = sizeof(explode('\' ', $str));

                //if ' key found that it's a key $k1
                if(strpos($str,"' key")!==FALSE || strpos($str,"' k")!==FALSE){
                    $varname = '$k' . ($looplevel-1);
                }else{
                    $varname = '$v' . ($looplevel-1);
                }
            }else{
                $varname = "\$data['".$str."']";
            }

        }

        $varname = str_replace("\$data[''", "'", $varname);
        $varname = str_replace("'']", "'", $varname);

        return $varname;
    }

	private function convertNonDataVarKey($matches) {

		$varname = '';
        //if more than 1 dots, eg. users.total.pdf
        if(strpos($matches[2], '@')!==FALSE){
            $varname = str_replace('@', '->', $matches[2]);
            $varname = str_replace('.', '', $varname);
        }
        else if(strpos($matches[2], '.')!==FALSE){
            $properties = explode('.', $matches[2]);
            $varname .= "['". implode("']['", $properties) ."']";
        }
        //only 1 dot, users.john
        else{
            $varname = "['".$matches[2]."']";
        }
        return "<?php echo \${$matches[1]}{$varname}; ?>";

	}
    
    private function convertVarKey($matches){
        $varname = '';
        //if more than 1 dots, eg. users.total.pdf
        if(strpos($matches[2], '@')!==FALSE){
            $varname = str_replace('@', '->', $matches[2]);
            $varname = str_replace('.', '', $varname);
        }
        else if(strpos($matches[2], '.')!==FALSE){
            $properties = explode('.', $matches[2]);
            $varname .= "['". implode("']['", $properties) ."']";
        }
        //only 1 dot, users.john
        else{
            $varname = "['".$matches[2]."']";
        }
        return "<?php echo \$data['{$matches[1]}']$varname; ?>";
    }

    private function convertVarLoop($matches){
        $looplevel = sizeof(explode('\' ', $matches[0]));
        
        //if ' key found that it's a key $k1
        if(strpos($matches[0],"' key")!==FALSE || strpos($matches[0],"' k")!==FALSE)
            $varname = 'k' . ($looplevel-1);
        else{
            $varname = 'v' . ($looplevel-1);
            // This lets us use $data['key'] values as element indexes
            $matches[2] = str_replace('<?php echo ', '', $matches[2]);
            $matches[2] = str_replace('; ?>', '', $matches[2]);
            //remove the first variable if the ' is found, we dunwan the loop name
            if(strpos($matches[2], "' ")!==FALSE){
                $matches[2] = explode("' ", $matches[2]);
                array_splice($matches[2], 0, 1);
                $matches[2] = "' ".implode("' ", $matches[2] );
            }

            //users' value.uname  becomes  $v1['uname']
            //users' value.posts.latest  becomes  $v1['posts']['latest']
            //users' value.@uname  becomes  $v1->uname
            //users' value.@posts.@latest  becomes  $v1->posts->latest
            if(strpos($matches[2], '.@')!==FALSE){
                $varname .= str_replace('.@', '->', $matches[2]);
                $varname = str_replace("' value",'', $varname);
                $varname = str_replace("' v",'', $varname);
            }
            else if(strpos($matches[2], '.')!==FALSE){
                $properties = explode('.', $matches[2]);
                if(sizeof($properties)===2)
                    $varname .= "['".$properties[1]."']";
                else{
                    array_splice($properties, 0, 1);
                    $varname .= "['". implode("']['", $properties) ."']";
                }
            }
        }
        return '<?php echo $'.$varname.'; ?>';
    }

}
