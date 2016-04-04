<?php
/**
 * DooValidator class file.
 * @author Leng Sheng Hong <darkredz@gmail.com> 修改人：沈德刚
 * @link http://www.doophp.com/
 * @copyright Copyright &copy; 2009 Leng Sheng Hong
 * @license http://www.doophp.com/license
 */

/**
 * 一个辅助类，有助于验证数据。
 *
 * <p>DooValidator形式和模型数据验证可用于保存前/插入/删除数据。</p>
 *
 * <p>要使用DooValidator，你必须创建它的一个实例，并定义的​​验证规则。
 * 所有的方法开始与“测试”，可用于验证数据的规则。规则名称是区分大小写的。</p>
 *
 * <p>您可以通过自定义错误消息，随着规则。默认情况下在各个领域的规则 <b>required</b></p>
 * <code>
 * $rules = array(
 *              'creattime'=>array(
 *                    array('datetime'),
 *                    array('email'),
 *                    array('optional')   //Optional field
 *              ),
 *              'username'=>array(
 *                    array('username',6,16),
 *                    //Custom error message will be used
 *                    array('lowercase', 'Username must only be lowercase.')
 *               ),
 *
 *               //只有一个规则
 *               'pwd'=>array('password'),
 *               'email'=>array('email'),
 *               'age'=>array('between',13,200),
 *               'today'=>array('date','mm/dd/yy'),
 *
 *               //自定义规则，所需的静态方法
 *               'a'=>array('custom', 'MainController::isA'),
 *
 *               //自定义必填字段消息
 *               'content'=>array('required', 'Content is required!')
 *        );
 * </code>
 *
 * <p>规则定义的基础上验证方法的参数。例如：</p>
 * <code>
 * //Validation method
 * testBetween($value, $min, $max, $msg=null)
 *
 * $rule = array(
 *     'field'=>array('between', 0, 20)
 *     'field2'=>array('between', 0, 20, 'Custom Err Msg!')
 * );
 * </code>
 *
 * <p>你可以DooValidator通过调用可用的验证规则列表：getAvailableRules（）</p>
 *
 * <p>To validate the data, create an instance of DooValidator and call validate() in your Controller/Model.</p>
 * <code>
 * $v = new DooValidator();
 *
 * # 有3种不同的验证模式。
 * //$v->checkMode = DooValidator::CHECK_SKIP; //检查_跳过
 * //$v->checkMode = DooValidator::CHECK_ALL_ONE;
 * //$v->checkMode = DooValidator::CHECK_ALL;
 *
 * //$_POST或数据传递需要ASSOC阵列
 * //$data = array('username'=>'doophp', 'pwd'=>'12345');
 * if($error = $v->validate($_POST, $rules)){
 *      print_r($error);
 * }
 * </code>
 *
 * <p>你可以传入一个字符串加载预定义的规则，其中位于 SITE_PATH/protected/config/forms/</p>
 * <code>
 * <?php
 * //in protected/config/forms/example.php
 * return array(
 *      'username'=>array(
 *                      array('username',4,5,'username invalid'),
 *                      array('maxlength',6,'This is too long'),
 *                      array('minlength',6)
 *                  ),
 *      'pwd'=>array('password',3,5),
 *      'email'=>array('email')
 *  );
 * ?>
 *
 * //in your Controller/Model
 * $error = $v->validate($data, 'example');
 * </code>
 *
 * <p>如果没有返回的validate（）调用，它意味着所有的数据通过验证规则。</p>
 *
 * <p>当你使用框架的模型生成功能模型验证规则自动生成。
 * 如果你的模型扩展DooModel或DooSmartModel，您可以验证通过调用数据 DooModel::validate()</p>
 * <code>
 * $f = new Food;
 * $f->name = 'My Food name for validation';
 * $error = $f->validate();
 *
 * //Or...
 * $error = Food::_validate($f);
 * </code>
 *
 * @author Leng Sheng Hong <darkredz@gmail.com>
 * @version $Id: DooValidator.php 1000 2009-08-30 11:37:22
 * @package doo.helper
 * @since 1.2
 */
class DooValidator {
    /**
     * 检查所有返回的所有错误
     */
    const CHECK_ALL = 'all';

    /**
     * 检查所有的每个数据字段返回一个错误
     */
    const CHECK_ALL_ONE = 'all_one';

    /**
     * 一旦检测到第一个是返回一个错误
     */
    const CHECK_SKIP = 'skip';

	/**
	 * 使用PHP空方法测试所需（或可选）
	 */
	const REQ_MODE_NULL_EMPTY = 'nullempty';

	/**
	 * 只有确保所需的字段非空/不接受所需的空
	 */
	const REQ_MODE_NULL_ONLY = 'null';
    
    /**
     * 默认要求，信息显示字段名“FIRST_NAME是必需的。
     */
    const REQ_MSG_FIELDNAME = 'fieldname';
    
    /**
     * 默认需要显示的消息“这是必需的。”
     */
    const REQ_MSG_THIS = 'this';
    
    /**
     * 默认需要转换字段的名称，下划线字的消息。例如：（field= FIRST_NAME）。 “名字是必需的。”
     */    
    const REQ_MSG_UNDERSCORE_TO_SPACE = 'underscore';
    
    /**
     * 默认需要消息转换字段的名称与驼峰话。例如：（field= FIRST_NAME）。 “名字是必需的。”
     */    
    const REQ_MSG_CAMELCASE_TO_SPACE = 'camelcase';

    /**
     * Validation mode
     * @var string all/all_one/skip
     */
    public $checkMode = 'all_one';

	/**
	 * 应该如何进行测试所需的字段（或留下可选）
	 * @var string empty/null
	 */
	public $requireMode = 'nullempty';
    
    /**
     * 默认的方法来产生所需的字段的错误消息。
     * @var string
     */
    public $requiredMsgDefaultMethod = 'camelcase';
    
    /**
     * 默认后缀为必填字段错误消息。
     * @var string
     */
    public $requiredMsgDefaultSuffix = '不能为空';

    /**
     * 修剪的数据字段。这些数据将被修改。
     * @param array $data assoc array to be trimmed
	 * @param int $maxDepth Maximum number of recursive calls. Defaults to a max nesting depth of 5.
     */
    public function trimValues(&$data, $maxDepth = 5) {
		foreach($data as $k=>&$v) {
			if (is_array($v)) {
				if ($maxDepth > 0) {
					$this->trimValues($v, $maxDepth - 1);
				}
			} else {
				$v = trim($v);
			}
		}
	}
	
	/**
	 * 判断用户信息功能详解
	 * alpha: 输入字符串可以只包含字母 ($string , $message)
	 * alphaNumeric: 输入字符串只能由字母或数字 ($string , $message)
	 * between: 验证值数字范围($value, $min, $max, $msg=null)
	 * betweenInclusive: 验证值范围($value, $min, $max, $msg=null)
	 * ColorHex: 确认颜色# ff0000格式  ($value, $msg=null)
	 * Custom: 验证数据和自己定义的规则。 ($value, $function, $options=null ,$msg=null)
	 * date: 确认日期格式。默认yyyy/mm/dd
	 * dateBetween: 验证给定日期是的范围($value, $dateStart, $dateEnd, $msg=null)
	 * datetime: 日期时间验证($string , $message)
	 * digit: 验证数字($value, $msg=null)
	 * email: 验证email
	 * equal: 验证一个值等于一个数字 包括长度($value, $equalValue, $msg=null)
	 * equalAs: 如果它是公平的验证领域和一些其他的领域从$ _GET和$ _POST方法 ($value, $method, $field, $msg=null)
	 * float: 验证是不是float型
	 * greaterThan: 验证值大于一个数字($value, $number, $msg=null)
	 * greaterThanOrEqual: 验证一个值大于或等于一个数字($value, $number, $msg=null)
	 * ip: 验证ip($value, $msg=null)
	 * integer: 验证integer型($value, $msg=null)
	 * lessThan: 验证一个值小于一个数字($value, $number, $msg=null)
	 * lessThanOrEqual: 验证值小于或等于一个数字($value, $number, $msg=null)
	 * lowercase: 验证小写的字符串。($value, $msg=null)
	 * max: 验证最大值的一个数字。($value, $msg=null)
	 * maxlength: 验证最大值的一个字符串长度。($value, $msg=null)
	 * min: 验证最小值的一个数字。($value, $msg=null)
	 * minlength: 验证最小值的一个字符串长度。($value, $msg=null)
	 * notEmpty: 验证是否为empty($value, $msg=null)
	 * notEqual:($value, $equalValue, $msg=null)
	 * notNull：验证是否为null($value, $msg=null)
	 * password:验证密码($value, $minLength=6, $maxLength=32, $msg=null)
	 * passwordComplex:验证一个复杂密码($value, $msg=null)
	 * price:验证是个价格，两位数($value, $msg=null)
	 * uppercase:验证大写的字符串。($value, $msg=null)
	 * url: 验证url($value, $msg=null)
	 * username:验证用户名格式。($value, $minLength=4, $maxLength=12, $msg=null)
	 * dbExist:验证价字段是否存在数据表里($value, $table, $field, $msg=null)
	 * dbNotExist:验证价字段是否存在数据表里($value, $table, $field, $msg=null)
	 * alphaSpace: 验证是否包含字符($value, $msg=null)
	 * notInList: 验证值在值是否包含在一个数组里($value, $valueList, $msg=null)
	 * inList: 验证值在值是否包含在一个数组里($value, $valueList, $msg=null)
	 */
    /**
     * Get a list of available rules
     * @return array
     */
    public static function getAvailableRules(){
        return array('alpha', 'alphaNumeric', 'between', 'betweenInclusive',
                    'colorHex', 'custom', 'date', 'dateBetween', 'datetime', 'digit', 'email', 'equal', 'equalAs', 'float',
                    'greaterThan', 'greaterThanOrEqual', 'ip', 'integer', 'lessThan', 'lessThanOrEqual', 'lowercase', 'max',
                    'maxlength', 'min', 'minlength', 'notEmpty', 'notEqual', 'notNull', 'password', 'passwordComplex', 'price', 'regex',
                    'uppercase', 'url', 'username','dbExist','dbNotExist','alphaSpace','notInList','inList'
                );
    }

    /**
     * Get appropriate rules for a certain DB data type
     * @param string $dataType
     * @return string Rule name for the data type
     */
    public static function dbDataTypeToRules($type){
        $dataType = array(
                        //integers
                        'tinyint'=>'integer',
                        'smallint'=>'integer',
                        'mediumint'=>'integer',
                        'int'=>'integer',
                        'bigint'=>'integer',

                        //float
                        'float'=>'float',
                        'double'=>'float',
                        'decimal'=>'float',

                        //datetime
                        'date'=>'date',
                        'datetime'=>'datetime',
                        'timestamp'=>'datetime',
                        'time'=>'datetime'
                    );
        if(isset($dataType[$type]))
            return $dataType[$type];
    }

    /**
     * Validate the data with the defined rules.验证数据定义规则。
     *
     * @param array $data Data to be validate. One dimension assoc array, eg. array('user'=>'leng', 'email'=>'abc@abc.com')
     * @param string|array $rules Validation rule. Pass in a string to load the predefined rules in SITE_PATH/protected/config/forms
     * @return array Returns an array of errors if errors exist.
     */
    public function validate($data=null, $rules=null, $field_names = null){
        //$data = array('username'=>'leng s', 'pwd'=>'234231dfasd', 'email'=>'asdb12@#asd.com.my');
        //$rules = array('username'=>array('username'), 'pwd'=>array('password',6,32), 'email'=>array('email'));
    	if (count($data) > 30)
    		showmessage('表单长度过长');
    		
    	if(is_string($rules)){
            $rules = include(APP_PATH.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.M.DIRECTORY_SEPARATOR.'forms'.DIRECTORY_SEPARATOR.$rules.'.php');
        }

        $optErrorRemove = array();

        foreach($data as $dk=>$dv){
            if($this->requireMode == DooValidator::REQ_MODE_NULL_EMPTY && ($dv === null || $dv === '') ||
			   $this->requireMode == DooValidator::REQ_MODE_NULL_ONLY  && $dv === null){
                unset($data[$dk]);
			}
        }

        if($missingKey = array_diff_key($rules, $data) ){
                $fieldnames = array_keys($missingKey);
                $customRequireMsg = null;

                foreach($fieldnames as $key => $fieldname){
                    if(isset($missingKey[$fieldname])){
                        if( in_array('required', $missingKey[$fieldname]) ){
                            $customRequireMsg = $missingKey[$fieldname][1];
                        }
                        else if(is_array($missingKey[$fieldname][0])){
                            foreach($missingKey[$fieldname] as $f)
                                if($f[0]=='required'){
                                    if(isset($f[1]))
                                        $customRequireMsg = $f[1];
                                    break;
                                }
                        }
                    }

                    //remove optional fields from error
                    if(is_array($missingKey[$fieldname][0])){
                       foreach($missingKey[$fieldname] as $innerArrayRules){
                           if($innerArrayRules[0]=='optional'){
                               //echo $fieldname.' - 1 this is not set and optional, should be removed from error';
                               $optErrorRemove[] = $fieldname;
                               break;
                           }
                       }
                    }

                    if($this->checkMode==DooValidator::CHECK_ALL){
                        if($customRequireMsg!==null)
                            $errors[$fieldname] = $customRequireMsg;
                        else
                            $errors[$fieldname] = $this->getRequiredFieldDefaultMsg($fieldname, $field_names[$key]);
                    }else if($this->checkMode==DooValidator::CHECK_SKIP){
                        if(in_array($fieldname, $optErrorRemove))
                            continue;
                        if($customRequireMsg!==null)
                            return $customRequireMsg;
                        return $this->getRequiredFieldDefaultMsg($fieldname, $field_names[$key]);
                    }else if($this->checkMode==DooValidator::CHECK_ALL_ONE){
                        if($customRequireMsg!==null)
                            $errors[$fieldname] = $customRequireMsg;
                        else
                            $errors[$fieldname] = $this->getRequiredFieldDefaultMsg($fieldname, $field_names[$key]);
                    }
                }
        }

        foreach($data as $k=>$v){
            if(!isset($rules[$k])) continue;
            $cRule = $rules[$k];
            foreach($cRule as $v2){
                if(is_array($v2)){
                    //print_r(array_slice($v2, 1));
                    $vv = array_merge(array($v),array_slice($v2, 1));

					$vIsEmpty = ($this->requireMode == DooValidator::REQ_MODE_NULL_EMPTY && ($v === null || $v === '') ||
								 $this->requireMode == DooValidator::REQ_MODE_NULL_ONLY  && $v === null) ? true : false;

                    //call func
                    if($vIsEmpty && $v2[0]=='optional'){
                        //echo $k.' - this is not set and optional, should be removed from error';
                        $optErrorRemove[] = $k;
                    }
                    if($err = call_user_func_array(array(&$this, 'test'.$v2[0]), $vv) ){
                        if($this->checkMode==DooValidator::CHECK_ALL)
                            $errors[$k][$v2[0]] = $err;
                        else if($this->checkMode==DooValidator::CHECK_SKIP && !$vIsEmpty && $v2[0]!='optional'){
                            return $err;
                        }else if($this->checkMode==DooValidator::CHECK_ALL_ONE)
                            $errors[$k] = $err;
                    }
                }
                else if(is_string($cRule[0])){
                    if(sizeof($cRule)>1){
                        //print_r(array_slice($cRule, 1));
                        $vv = array_merge(array($v),array_slice($cRule, 1));

                        if($err = call_user_func_array(array(&$this, 'test'.$cRule[0]), $vv) ){
                            if($this->checkMode==DooValidator::CHECK_ALL || $this->checkMode==DooValidator::CHECK_ALL_ONE)
                                $errors[$k] = $err;
                            else if($this->checkMode==DooValidator::CHECK_SKIP){
                                return $err;
                            }
                        }
                    }else{
                        if($err = $this->{'test'.$cRule[0]}($v) ){
                            if($this->checkMode==DooValidator::CHECK_ALL || $this->checkMode==DooValidator::CHECK_ALL_ONE)
                                $errors[$k] = $err;
                            else if($this->checkMode==DooValidator::CHECK_SKIP){
                                return $err;
                            }
                        }
                    }
                    continue 2;
                }
            }
        }
        if(isset($errors)){
            if(sizeof($optErrorRemove)>0){
                foreach($errors as $ek=>$ev){
                    if(in_array($ek, $optErrorRemove)){
                        //echo '<h3>Removing error '.$ek.'</h3>';
                        unset($errors[$ek]);
                    }
                }
            }
            return $errors;
        }
    }
    
    /**
     * Set default settings to display the default error message for required fields
     * @param type $displayMethod Default error message display method. use: DooValidator::REQ_MSG_UNDERSCORE_TO_SPACE, DooValidator::REQ_MSG_CAMELCASE_TO_SPACE, DooValidator::REQ_MSG_THIS, DooValidator::REQ_MSG_FIELDNAME
     * @param type $suffix suffix for the error message. Default is ' field is required'
     */
    public function setRequiredFieldDefaults( $displayMethod = DooValidator::REQ_MSG_UNDERSCORE_TO_SPACE, $suffix = ' field is required'){
        $this->requiredMsgDefaultMethod = $displayMethod;
        $this->requiredMsgDefaultSuffix = $suffix;
    }
    
    /**
     * Get the default error message for required field
     * @param string $fieldname Name of the field
     * @return string Error message
     */
    public function getRequiredFieldDefaultMsg($fieldname, $field_names = null){
        if($this->requiredMsgDefaultMethod==DooValidator::REQ_MSG_UNDERSCORE_TO_SPACE)
            return ucfirst(str_replace('_', ' ', $fieldname)) . $this->requiredMsgDefaultSuffix;

        if($this->requiredMsgDefaultMethod==DooValidator::REQ_MSG_THIS)
            return 'This ' . $this->requiredMsgDefaultSuffix;        
        
        if($this->requiredMsgDefaultMethod==DooValidator::REQ_MSG_CAMELCASE_TO_SPACE)
            return $field_names.$this->requiredMsgDefaultSuffix;
        //return ucfirst(strtolower(preg_replace('/([A-Z])/', ' $1', $fieldname))) . $this->requiredMsgDefaultSuffix;
        
        if($this->requiredMsgDefaultMethod==DooValidator::REQ_MSG_FIELDNAME)
            return $fieldname . $this->requiredMsgDefaultSuffix;
    }

    public function testOptional($value){}
    public function testRequired($value, $msg){
		if ($this->requireMode == DooValidator::REQ_MODE_NULL_EMPTY && ($value === null || $value === '') ||
			$this->requireMode == DooValidator::REQ_MODE_NULL_ONLY  && $value === null) {

            if($msg!==null) return $msg;
            return 'This field is required!';
        }
    }

    /**
     * Validate data with your own custom rules. 验证数据和自己定义的规则。
     *
     * Usage in Controller:
     * <code>
     * public static function isA($value){
     *      if($value!='a'){
     *          return 'Value must be A';
     *      }
     * }
     *
     * public function test(){
     *     $rules = array(
     *          'email'=>array('custom', 'TestController::isA')
     *     );
     *
     *     $v = new DooValidator();
     *     if($error = $v->validate($_POST, $rules)){
     *          //display error
     *     }
     * }
     * </code>
     *
     * @param string $value Value of data to be validated
     * @param string $function Name of the custom function
     * @param string $msg Custom error message
     * @return string
     */
    public function testCustom($value, $function, $options=null ,$msg=null){
        if($options==null){
            if($err = call_user_func($function, $value)){
                if($err!==true){
                    if($msg!==null) return $msg;
                    return $err;
                }
            }
        }else{
            //if array, additional parameters
            if($err = call_user_func_array($function, array_merge(array($value), $options)) ){
                if($err!==true){
                    if($msg!==null) return $msg;
                    return $err;
                }
            }
        }
    }

    /**
     * Validate against a Regex rule
     *
     * @param string $value Value of data to be validated
     * @param string $regex Regex rule to be tested against
     * @param string $msg Custom error message
     * @return string
     */
    public function testRegex($value, $regex, $msg=null){
        if(!preg_match($regex, $value) ){
            if($msg!==null) return $msg;
            return 'Error in field.';
        }
    }

    /**
     * Validate username format. 验证用户名格式。
     *
     * @param string $value Value of data to be validated
     * @param int $minLength Minimum length
     * @param int $maxLength Maximum length
     * @param string $msg Custom error message
     * @return string
     */
    public function testUsername($value, $minLength=4, $maxLength=12, $msg=null){
        if(!preg_match('/^[a-zA-Z][a-zA-Z.0-9_-]{'. ($minLength-1) .','.$maxLength.'}$/i', $value)){
            if($msg!==null) return $msg;
            return "User name must be $minLength-$maxLength characters. Only characters, dots, digits, underscore & hyphen are allowed.";
        }
        else if(strpos($value, '..')!==False){
            if($msg!==null) return $msg;
            return "User name cannot consist of 2 continuous dots.";
        }
        else if(strpos($value, '__')!==False){
            if($msg!==null) return $msg;
            return "User name cannot consist of 2 continuous underscore.";
        }
        else if(strpos($value, '--')!==False){
            if($msg!==null) return $msg;
            return "User name cannot consist of 2 continuous dash.";
        }
        else if(strpos($value, '.-')!==False || strpos($value, '-.')!==False ||
                strpos($value, '._')!==False || strpos($value, '_.')!==False ||
                strpos($value, '_-')!==False || strpos($value, '-_')!==False){
            if($msg!==null) return $msg;
            return "User name cannot consist of 2 continuous punctuation.";
        }
        else if(ctype_punct($value[0])){
            if($msg!==null) return $msg;
            return "User name cannot start with a punctuation.";
        }
        else if(ctype_punct( substr($value, strlen($value)-1) )){
            if($msg!==null) return $msg;
            return "User name cannot end with a punctuation.";
        }
    }

    /**
     * Validate password format 验证密码格式
     *
     * @param string $value Value of data to be validated
     * @param int $minLength Minimum length
     * @param int $maxLength Maximum length
     * @param string $msg Custom error message
     * @return string
     */
    public function testPassword($value, $minLength=6, $maxLength=32, $msg=null){
        if(!preg_match('/^[\w~!@#$%^&*]{'.$minLength.','.$maxLength.'}$/i', $value)){
            if($msg!==null) return $msg;
            return "Only characters, dots, digits, underscore & hyphen are allowed. Password must be at least $minLength characters long.";
        }
    }

    /**
     * Validate against a complex password format 对一个复杂的密码验证的格式
     *
     * @param string $value Value of data to be validated
     * @param string $msg Custom error message
     * @return string
     */
    public function testPasswordComplex($value, $msg=null){
        if(!preg_match('A(?=[-_a-zA-Z0-9]*?[A-Z])(?=[-_a-zA-Z0-9]*?[a-z])(?=[-_a-zA-Z0-9]*?[0-9])[-_a-zA-Z0-9]{6,32}z', $value)){
            if($msg!==null) return $msg;
            return 'Password must contain at least one upper case letter, one lower case letter and one digit. It must consists of 6 or more letters, digits, underscores and hyphens.';
        }
    }

    /**
     * Validate email address
     *
     * @param string $value Value of data to be validated
     * @param string $msg Custom error message
     * @return string
     */
    public function testEmail($value, $msg=null){
		// Regex based on best solution from here: http://fightingforalostcause.net/misc/2006/compare-email-regex.php
        if(!preg_match('/^([\w\!\#$\%\&\'\*\+\-\/\=\?\^\`{\|\}\~]+\.)*[\w\!\#$\%\&\'\*\+\-\/\=\?\^\`{\|\}\~]+@((((([a-z0-9]{1}[a-z0-9\-]{0,62}[a-z0-9]{1})|[a-z])\.)+[a-z]{2,6})|(\d{1,3}\.){3}\d{1,3}(\:\d{1,5})?)$/i', $value) ||
            strpos($value, '--')!==False || strpos($value, '-.')!==False
        ){
            if($msg!==null) return $msg;
            return 'Invalid email format!';
        }
    }

    /**
     * Validate a URL
     *
     * @param string $value Value of data to be validated
     * @param string $msg Custom error message
     * @return string
     */
    public function testUrl($value, $msg=null){
        if(!preg_match('/^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i', $value)){
            if($msg!==null) return $msg;
            return 'Invalid URL!';
        }
    }

    /**
     * Validate an IP address (198.168.1.101)
     *
     * @param string $value Value of data to be validated
     * @param string $msg Custom error message
     * @return string
     */
    public function testIP($value, $msg=null){
        //198.168.1.101
        if (!preg_match('/^(([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]).){3}([1-9]?[0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/',$value)) {
            if($msg!==null) return $msg;
            return 'Invalid IP address!';
        }
    }

 

    /**
     * Validate Color hex #ff0000  确认颜色# ff0000格式 
     *
     * @param string $value Value of data to be validated
     * @param string $msg Custom error message
     * @return string
     */
    public function testColorHex($value, $msg=null){
        //#ff0000
        if (!preg_match('/^#([0-9a-f]{1,2}){3}$/i', $value)) {
            if($msg!==null) return $msg;
            return 'Invalid color code!';
        }
    }

    //------------------- Common data validation ---------------------

    /**
     * Validate Date Time 日期时间验证
     *
     * @param string $value Value of data to be validated
     * @param string $msg Custom error message
     * @return string
     */
    public function testDateTime($value, $msg=null){
        $rs = strtotime($value);

        if ($rs===false || $rs===-1){
            if($msg!==null) return $msg;
            return 'Invalid date time format!';
        }
    }

    /**
     * Validate Date format. Default yyyy/mm/dd. 确认日期格式。默认yyyy/mm/dd
     *
     * <p>Date format: yyyy-mm-dd, yyyy/mm/dd, yyyy.mm.dd
     * Date valid from 1900-01-01 through 2099-12-31</p>
     *
     * @param string $value Value of data to be validated
     * @param string $dateFormat Date format
     * @param string $msg Custom error message
     * @return string
     */
    public function testDate($value, $format='yyyy/mm/dd', $msg=null, $forceYearLength=false){
        //Date yyyy-mm-dd, yyyy/mm/dd, yyyy.mm.dd
        //1900-01-01 through 2099-12-31

		$yearFormat = "(19|20)?[0-9]{2}";
		if ($forceYearLength == true) {
			if (strpos($format, 'yyyy') !== false) {
				$yearFormat = "(19|20)[0-9]{2}";
			} else {
				$yearFormat = "[0-9]{2}";
			}
		}

        switch($format){
            case 'dd/mm/yy':
                $format = "/^\b(0?[1-9]|[12][0-9]|3[01])[- \/.](0?[1-9]|1[012])[- \/.]{$yearFormat}\b$/";
                break;
            case 'mm/dd/yy':
                $format = "/^\b(0?[1-9]|1[012])[- \/.](0?[1-9]|[12][0-9]|3[01])[- \/.]{$yearFormat}\b$/";
                break;
            case 'mm/dd/yyyy':
                $format = "/^(0[1-9]|1[012])[- \/.](0[1-9]|[12][0-9]|3[01])[- \/.]{$yearFormat}$/";
                break;
            case 'dd/mm/yyyy':
                $format = "/^(0[1-9]|[12][0-9]|3[01])[- \/.](0[1-9]|1[012])[- \/.]{$yearFormat}$/";
                break;
            case 'yy/mm/dd':
                $format = "/^\b{$yearFormat}[- \/.](0?[1-9]|1[012])[- \/.](0?[1-9]|[12][0-9]|3[01])\b$/";
                break;
            case 'yyyy/mm/dd':
            default:
                $format = "/^\b{$yearFormat}[- \/.](0?[1-9]|1[012])[- \/.](0?[1-9]|[12][0-9]|3[01])\b$/";
        }

        if (!preg_match($format, $value)) {
            if($msg!==null) return $msg;
            return 'Invalid date format!';
        }
    }

    /** 
     * Validate if given date is between 2 dates. 验证给定日期是的范围
     *
     * @param string $value Value of data to be validated
     * @param string $dateStart Starting date
     * @param string $dateEnd Ending date
     * @param string $msg Custom error message
     * @return string
     */
    public function testDateBetween($value, $dateStart, $dateEnd, $msg=null){
		$value = strtotime($value);
        if(!( $value > strtotime($dateStart) && $value < strtotime($dateEnd) ) ) {
            if($msg!==null) return $msg;
            return "Date must be between $dateStart and $dateEnd";
        }
    }

    /**
     * Validate integer
     *
     * @param string $value Value of data to be validated
     * @param string $msg Custom error message
     * @return string
     */
    public function testInteger($value, $msg=null){
        if(intval($value)!=$value || strlen(intval($value))!=strlen($value)){
            if($msg!==null) return $msg;
            return 'Input is not an integer.';
        }
    }

    /**
     * Validate price. 2 decimal points only 确认价格。两位小数而已
     *
     * @param string $value Value of data to be validated
     * @param string $msg Custom error message
     * @return string
     */
    public function testPrice($value, $msg=null){
        // 2 decimal
        if (!preg_match('/^[0-9]*\\.?[0-9]{0,2}$/', $value)){
            if($msg!==null) return $msg;
            return 'Input is not a valid price amount.';
        }
    }

    /**
     * Validate float value.
     *
     * @param string $value Value of data to be validated
     * @param int $decimal Number of Decimal points
     * @param string $msg Custom error message
     * @return string
     */
    public function testFloat($value, $decimal='', $msg=null){
        // any amount of decimal
        if (!preg_match('/^[0-9]*\\.?[0-9]{0,'.$decimal.'}$/', $value)){
            if($msg!==null) return $msg;
            return 'Input is not a valid float value.';
        }
    }

    /**
     * Validate digits.验证数字。
     *
     * @param string $value Value of data to be validated
     * @param string $msg Custom error message
     * @return string
     */
    public function testDigit($value, $msg=null){
        if(!ctype_digit($value)){
            if($msg!==null) return $msg;
            return 'Input is not a digit.';
        }
    }

    /**
     * Validate Alpha numeric values.
     *
     * 输入字符串只能由字母或数字
     *
     * @param string $value Value of data to be validated
     * @param string $msg Custom error message
     * @return string
     */
    public function testAlphaNumeric($value, $msg=null){
        if(!ctype_alnum($value)){
            if($msg!==null) return $msg;
            return 'Input can only consist of letters or digits.';
        }
    }

    /**
     * Validate Alpha values.
     *
     * 输入字符串可以只包含字母
     *
     * @param string $value Value of data to be validated
     * @param string $msg Custom error message
     * @return string
     */
    public function testAlpha($value, $msg=null){
        if(!ctype_alpha($value)){
            if($msg!==null) return $msg;
            return 'Input can only consist of letters.';
        }
    }

    /**
     * Validate if string only consist of letters and spaces 验证是否包含字符
     *
     * Input string can only consist of only Letters and spaces.
     *
     * @param string $value Value of data to be validated
     * @param string $msg Custom error message
     * @return string
     */
    public function testAlphaSpace($value, $msg=null){
        if(!ctype_alpha(str_replace(' ','',$value))){
            if($msg!==null) return $msg;
            return 'Input can only consist of letters and spaces.';
        }
    }


    /**
     * Validate lowercase string. 验证小写的字符串。
     *
     * Input string can only be lowercase letters.
     *
     * @param string $value Value of data to be validated
     * @param string $msg Custom error message
     * @return string
     */
    public function testLowercase($value, $msg=null){
        if(!ctype_lower($value)){
            if($msg!==null) return $msg;
            return 'Input can only consists of lowercase letters.';
        }
    }

    /**
     * Validate uppercase string. 验证大写的字符串。
     *
     * Input string can only be uppercase letters.
     *
     * @param string $value Value of data to be validated
     * @param string $msg Custom error message
     * @return string
     */
    public function testUppercase($value, $msg=null){
        if(!ctype_upper($value)){
            if($msg!==null) return $msg;
            return 'Input can only consists of uppercase letters.';
        }
    }

    /**
     * Validate Not Empty. Input cannot be empty.
     *
     * @param string $value Value of data to be validated
     * @param string $msg Custom error message
     * @return string
     */
    public function testNotEmpty($value, $msg=null){
        if(empty($value)){
            if($msg!==null) return $msg;
            return 'Value cannot be empty!';
        }
    }

    /**
     * Validate Max length of a string.
     *
     * @param string $value Value of data to be validated
     * @param int $length Maximum length of the string
     * @param string $msg Custom error message
     * @return string
     */
    public function testMaxLength($value, $length=0, $msg=null){
        if(mb_strlen($value) > $length){
            if($msg!==null) return $msg;
            return "Input cannot be longer than the $length characters.";
        }
    }

    /**
     * Validate Minimum length of a string.
     *
     * @param string $value Value of data to be validated
     * @param int $length Minimum length of the string
     * @param string $msg Custom error message
     * @return string
     */
    public function testMinLength($value, $length=0, $msg=null){
        if(strlen($value) < $length){
            if($msg!==null) return $msg;
            return "Input cannot be shorter than the $length characters.";
        }
    }

    /**
     * Validate Not Null. Value cannot be null.
     *
     * @param string $value Value of data to be validated
     * @param string $msg Custom error message
     * @return string
     */
    public function testNotNull($value, $msg=null){
        if(is_null($value)){
            if($msg!==null) return $msg;
            return 'Value cannot be null.';
        }
    }

    /**
     * Validate Minimum value of a number.
     *
     * @param string $value Value of data to be validated
     * @param int $min Minimum value
     * @param string $msg Custom error message
     * @return string
     */
    public function testMin($value, $min, $msg=null){
        if( $value < $min){
            if($msg!==null) return $msg;
            return "Value cannot be less than $min";
        }
    }

    /**
     * Validate Maximum value of a number.验证最大值的一个数字。
     *
     * @param string $value Value of data to be validated
     * @param int $max Maximum value
     * @param string $msg Custom error message
     * @return string
     */
    public function testMax($value, $max, $msg=null){
        if( $value > $max){
            if($msg!==null) return $msg;
            return "Value cannot be more than $max";
        }
    }

    /**
     * 验证值范围
     *
     * @param string $value Value of data to be validated
     * @param int $min Minimum value
     * @param int $max Maximum value
     * @param string $msg Custom error message
     * @return string
     */
    public function testBetweenInclusive($value, $min, $max, $msg=null){
        if( $value < $min || $value > $max ){
            if($msg!==null) return $msg;
            return "Value must be between $min and $max inclusively.";
        }
    }

    /**
     * 验证值数字范围
     *
     * @param string $value Value of data to be validated
     * @param int $min Minimum value
     * @param int $max Maximum value
     * @param string $msg Custom error message
     * @return string
     */
    public function testBetween($value, $min, $max, $msg=null){
        if( $value < $min+1 || $value > $max-1 ){
            if($msg!==null) return $msg;
            return "Value must be between $min and $max.";
        }
    }

    /**
     * Validate if a value is greater than a number 验证值大于一个数字
     *
     * @param string $value Value of data to be validated
     * @param int $number Number to be compared
     * @param string $msg Custom error message
     * @return string
     */
    public function testGreaterThan($value, $number, $msg=null){
        if( !($value > $number)){
            if($msg!==null) return $msg;
            return "Value must be greater than $number.";
        }
    }

    /**
     * Validate if a value is greater than or equal to a number验证一个值大于或等于一个数字
     *
     * @param string $value Value of data to be validated
     * @param int $number Number to be compared
     * @param string $msg Custom error message
     * @return string
     */
    public function testGreaterThanOrEqual($value, $number, $msg=null){
        if( !($value >= $number)){
            if($msg!==null) return $msg;
            return "Value must be greater than or equal to $number.";
        }
    }

    /**
     * Validate if a value is less than a number 
     * 验证一个值小于一个数字
     * @param string $value Value of data to be validated
     * @param int $number Number to be compared
     * @param string $msg Custom error message
     * @return string
     */
    public function testLessThan($value, $number, $msg=null){
        if( !($value < $number)){
            if($msg!==null) return $msg;
            return "Value must be less than $number.";
        }
    }

    /**
     * 验证值小于或等于一个数字
     *
     * @param string $value Value of data to be validated
     * @param int $number Number to be compared
     * @param string $msg Custom error message
     * @return string
     */
    public function testLessThanOrEqual($value, $number, $msg=null){
        if( !($value <= $number)){
            if($msg!==null) return $msg;
            return "Value must be less than $number.";
        }
    }

    /**
     * Validate if a value is equal to a number 验证一个值等于一个数字 包括长度
     *
     * @param string $value Value of data to be validated
     * @param int $equalValue Number to be compared
     * @param string $msg Custom error message
     * @return string
     */
    public function testEqual($value, $equalValue, $msg=null){
        if(!($value==$equalValue && strlen($value)==strlen($equalValue))){
            if($msg!==null) return $msg;
            return 'Both values must be the same.';
        }
    }

    /**
     * Validate if a value is Not equal to a number如果一个值验证不等于一个数字
     *
     * @param string $value Value of data to be validated
     * @param int $equalValue Number to be compared
     * @param string $msg Custom error message
     * @return string
     */
    public function testNotEqual($value, $equalValue, $msg=null){
        if( $value==$equalValue && strlen($value)==strlen($equalValue) ){
            if($msg!==null) return $msg;
            return 'Both values must be different.';
        }
    }

   /**
    * Validate if value Exists in database
    *
    * @param string $value Value of data to be validated
    * @param string $table Name of the table in DB
    * @param string $field Name of field you want to check
    * @return string
    */
    public function testDbExist($value, $table, $field, $msg=null) {
        $result = Doo::db()->fetchRow("SELECT COUNT($field) AS count FROM " . $table . ' WHERE '.$field.' = ? LIMIT 1', array($value));
        if ((!isset($result['count'])) || ($result['count'] < 1)) {
            if($msg!==null) return $msg;
            return 'Value does not exist in database.';
        }
    }

   /**
    * Validate if value does Not Exist in database
    * 验证价字段是否存在数据表里
    * @param string $value Value of data to be validated
    * @param string $table Name of the table in DB
    * @param string $field Name of field you want to check
    * @return string
    */
    public function testDbNotExist($value, $table, $field, $msg=null) {
        $result = Doo::db()->fetchRow("SELECT COUNT($field) AS count FROM " . $table . ' WHERE '.$field.' = ? LIMIT 1', array($value));
        if ((isset($result['count'])) && ($result['count'] > 0)) {
            if($msg!==null) return $msg;
            return 'Same value exists in database.';
        }
    }



    /**
     * Validate if a value is in a list of values 验证值在值是否包含在一个数组里
     *
     * @param string $value Value of data to be validated
     * @param int $equalValue List of values to be checked
     * @param string $msg Custom error message
     * @return string
     */
    public function testInList($value, $valueList, $msg=null){
        if(!(in_array($value, $valueList))){
            if($msg!==null) return $msg;
            return 'Unmatched value.';
        }
    }

    /**
     * Validate if a value is NOT in a list of values
     *
     * @param string $value Value of data to be validated
     * @param int $equalValue List of values to be checked
     * @param string $msg Custom error message
     * @return string
     */
    public function testNotInList($value, $valueList, $msg=null){
        if(in_array($value, $valueList)){
            if($msg!==null) return $msg;
            return 'Unmatched value.';
        }
    }

	/**
	* Validate field if it is equal with some other field from $_GET or $_POST method
	* 如果它是公平的验证领域和一些其他的领域从$ _GET和$ _POST方法 
	* This method is used for validating form
	*
	* @param string $value Value of data to be validated
	* @param string $method Method (get or post), default $_POST
	* @param string $field Name of field that you want to check
	* @return string
	*/

	public function testEqualAs($value, $method, $field, $msg=null) {
		if ($method == "get") {
		  $method = $_GET;
		} else if ($method == "post") {
		  $method = $_POST;
		} else {
		  $method = $_POST;
		}
		if (!isset($method[$field]) || $value != $method[$field]) {
		    if($msg!==null) return $msg;
            return 'Value '.$value.' is not equal with "'.$field.'".';
		}
	}

}
