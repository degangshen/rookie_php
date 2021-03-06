<?php	defined('ROOKIE') or die('No direct script access.');
/**
 * DooModel is the base class for Model classes in DooPHP that provides useful querying methods.
 *
 * <p>The model classes can extend DooModel for more powerful ORM features which enable you to write shorter codes.
 * Extending this class is optional.</p>
 *
 * <p>All the extra ORM methods can be accessed in a static and non-static way. Example:</p>
 *
 * <code>
 * $food = new Food;
 * $food->getOne();
 * Food::_getOne();
 *
 * $food->count();
 * Food::_count();
 *
 * //Dynamic querying
 * $food->getById(14);
 * $food->getById_location(14, 'Malaysia');
 *
 * //Get only one item
 * $food->getById_first(14);
 * $food->getById_location_first(14, 'Malaysia');
 *
 * //Gets list of food with its food type
 * $food->relateFoodType();
 * $food->relateFoodType($food, array('limit'=>'first'));
 * $food->relateFoodType_first();
 *
 * //Static querying needs PHP 5.3 or above
 * Food::getById(14);
 * Food::getById_first(14);
 * Food::getById_location_first(14, 'Malaysia');
 * Food::relateFoodType();
 * Food::relateFoodType_first();
 * </code>
 *
 * <p>If you are using static querying methods such as Food::_count(), Food::getById(), you would need to setup the class name in your Model constructor.</p>
 * <code>
 * Doo::loadCore('db/DooModel');
 *
 * class Model extends DooModel{
 *     function __construct(){
 *         parent::$className = __CLASS__;
 *         //OR parent::setupModel(__CLASS__);
 *     }
 * }
 * </code>
 * Please check the database demo MainController's test() method for some example usage.
 *
 * @author Leng Sheng Hong <darkredz@gmail.com> 修改改者：shendegang
 * @version $Id: DooModel.php 1000 2009-08-27 10:28:56
 * @package doo.db
 * @since 1.2
 */
class Rookie_Db{
    /**
     * Determine whether the DB field names should be case sensitive.
     * @var bool
     */
    protected static $caseSensitive = false;

    /**
     * The class name of the Model
     * @var string
     */
    protected static $className = __CLASS__;

    /**
     * Constructor of a Model. Sets the model class properties with a list of keys & values.
     * @param array $properties Array of data (keys and values) to set the model properties
     */
    public function __construct($properties=null){
        if($properties!==null){
            foreach($properties as $k=>$v){
                if(in_array($k, $this->_fields))
                    $this->{$k} = $v;
            }
        }
    }

    /**
     * Setup the model. Use if needed with constructor.
     *
     * @param string $class Class name of the Model
     * @param bool $caseSensitive Determine whether the DB field names should be case sensitive.
     */
    protected function setupModel( $class=__CLASS__, $caseSensitive=false ){
        self::$className = $class;
        self::$caseSensitive = $caseSensitive;
    }

    /**
     * Validate the Model with the rules defined in getVRules()
     *
     * @param string $checkMode Validation mode. all, all_one, skip
	 * @param string $requireMode Require Check Mode. null, nullempty
     * @return array Return array of errors if exists. Return null if data passes the validation rules.
     */
    public function validate($checkMode='all', $requireMode='null'){
        //all, all_one, skip
        Rookie_Core::loadHelp('validator');
        $v = new Rookie_Validator();
        $v->checkMode = $checkMode;
		$v->requireMode = $requireMode;
        return $v->validate(get_object_vars($this), $this->getVRules());
    }

    /**
     * Validate the Model with the rules defined in getVRules()
     *
     * @param object $model Model object to be validated.
     * @param string $checkMode Validation mode. all, all_one, skip
	 * @param string $requireMode Require Check Mode. null, nullempty
     * @return array Return array of errors if exists. Return null if data passes the validation rules.
     */
    public static function _validate($model, $checkMode='all', $requireMode='null'){
        //all, all_one, skip
        Rookie_Core::loadHelp('validator');
        $v = new Rookie_Validator;
        $v->checkMode = $checkMode;
		$v->requiredMode = $requireMode;
        return $v->validate(get_object_vars($model), $model->getVRules());
    }

    //-------------- shorthands --------------------------
    /**
     * Shorthand for Doo::db()
     * @return DooSqlMagic
     */
    public function db(){
        return Rookie_Core::db();
    }

    /**
     * Commits a transaction. Transactions can be nestable.
     */
    public function commit(){
        Rookie_Core::db()->commit();
    }

    /**
     * Initiates a transaction. Transactions can be nestable.
     */
    public function beginTransaction(){
        Rookie_Core::db()->beginTransaction();
    }

    /**
     * Rolls back a transaction. Transactions can be nestable.
     */
    public function rollBack(){
        Rookie_Core::db()->rollBack();
    }

    /**
     * Find a record. (Prepares and execute the SELECT statements)
     * @param array $opt Associative array of options to generate the SELECT statement. Supported: <i>where, limit, select, param, groupby, asc, desc, custom, asArray</i>
     * @param array 是否缓存此查询  $cache = array('name', '3600');
     * @return mixed A model object or associateve array of the queried result
     */
    public function find($opt=null, $cache = array())
    {
//     	if (is_array($cache) && count($cache) > 1)
//     	{
//     		$id = sprintf('%x',crc32($cache[0]));
//     		$expire = isset($cache[1]) && !empty($cache[0]) ? $cache[1] : 0;
//     		$result = array();
//     		if ( ! Rookie_Cache_Mem::get($id))
//     		{
//     			$result = Rookie_Core::db()->find($this, $opt);
//     			Rookie_Cache_Mem::set($id, $result, $expire);
//     		}
//     		else
//     			$result = Rookie_Cache_Mem::get($id);
//     		return $result;
//     	}
//     	else 
    		return Rookie_Core::db()->find($this, $opt);
    }

    /**
     * Find a record and its associated model. Relational search. (Prepares and execute the SELECT statements)
     * @param string $rmodel The related model class name.
     * @param array $opt Associative array of options to generate the SELECT statement. Supported: <i>where, limit, select, param, joinType, groupby, match, asc, desc, custom, asArray, include, includeWhere, includeParam</i>
     * @return mixed A list of model object(s) or associateve array of the queried result
     */
    public function relate($rmodel, $opt=null){
        return Rookie_Core::db()->relate($this, $rmodel, $opt);
    }

    /**
     * Combine relational search results (combine multiple relates).
     *
     * Example:
     * <code>
     * $food = new Food;
     * $food->relateMany(array('Recipe','Article','FoodType'))
     * </code>
     *
     * @param array $rmodel The related models class names.
     * @param array $opt Array of options for each related model to generate the SELECT statement. Supported: <i>where, limit, select, param, joinType, groupby, match, asc, desc, custom, asArray, include, includeWhere, includeParam</i>
     * @return mixed A list of model objects of the queried result
     */
	public function relateMany($rmodel, $opt=null){
        return Rookie_Core::db()->relateMany($this, $rmodel, $opt);
    }

    /**
     * 展开相关的模型 (Tree Relationships).
     *
     * Example:
     * <code>
     * $recipe = new Recipe;
     * $recipe->relateExpand(array('Food','Article'))
     * </code>
     *
     * @param array $rmodel The related models class names.
     * @param array $opt Array of options for each related model to generate the SELECT statement. Supported: <i>where, limit, select, param, joinType, groupby, match, asc, desc, custom, asArray, include, includeWhere, includeParam</i>
     * @return mixed A list of model objects of the queried result
     */
	public function relateExpand($rmodel, $opt=null){
        return Rookie_Core::db()->relateExpand($this, $rmodel, $opt);
    }

    /**
     * Adds a new record. (Prepares and execute the INSERT statements)
     * @return int The inserted record's Id
     */
    public function insert(){
        return Rookie_Core::db()->insert($this);
    }

    /**
     * Adds a new record with a list of keys & values (assoc array) (Prepares and execute the INSERT statements)
     * @param array $data Array of data (keys and values) to be insert
     * @return int The inserted record's Id
     */
    public function insertAttributes($data){
        return Rookie_Core::db()->insertAttributes($this, $data);
    }
    
    /**
     * Use insertAttributes() instead 现在没用了
     * @deprecated deprecated since version 1.3
     */
    public function insert_attributes($data){
        return $this->insertAttributes($data);
    }

    /**
     * 增加了与其相关联的模型的一个新的记录。关系插入。 （编制和执行INSERT语句）
     * @param array $rmodels A list of associated model objects to be insert along with the main model.
     * @return int The inserted record's Id
     */
    public function relatedInsert($rmodels){
        return Rookie_Core::db()->relatedInsert($this, $rmodels);
    }

    /**
     * 查询函数
     * @param  array $sql
     * @return mixed $query
     */
    public function query($sql)
    {
    	return Rookie_Core::db()->query($sql);
    }
    
    /**
     * 返回结果集
     * @param  string $query
     * @return array 
     */
    public function fetchRow($query)
    {
    	return Rookie_Core::db()->fetchRow($query);
    }
    
 	/**
     * 返回结果集
     * @param  string $query
     * @return array 
     */
    public function fetchAll($query)
    {
    	return Rookie_Core::db()->fetchAll($query);
    }
    
    /**
     * Update an existing record. (Prepares and execute the UPDATE statements)
     * @param array $opt Associative array of options to generate the UPDATE statement. Supported: <i>where, limit, field, param</i>
     * @return int Number of rows affected
     */
    public function update($opt=NULL){
        return Rookie_Core::db()->update($this, $opt);
    }

    /**
     * Update an existing record with a list of keys & values (assoc array). (Prepares and execute the UPDATE statements)
     * @param array $opt Associative array of options to generate the UPDATE statement. Supported: <i>where, limit, field, param</i>
     * @return int Number of rows affected
     */
    public function update_attributes($data, $opt=NULL){
        return Rookie_Core::db()->update_attributes($this, $data, $opt);
    }

    /**
     * Update an existing record with its associated models. Relational update. (Prepares and execute the UPDATE statements)
     * @param array $rmodels A list of associated model objects to be updated or insert along with the main model.
     * @param array $opt Assoc array of options to update the main model. Supported: <i>where, limit, field, param</i>
     */
    public function relatedUpdate($rmodels, $opt=NULL){
        return Rookie_Core::db()->relatedUpdate($this, $rmodels, $opt);
    }

    /**
     * Returns the last inserted record's id
     * @return int
     */
    public function lastInsertId(){
        return Rookie_Core::db()->lastInsertId();
    }

	/**
	 * Delete ALL existing records. (Prepares and executes the DELETE statements)
	 */
	public function deleteAll() {
		return Rookie_Core::db()->deleteAll($this);
	}

    /**
     * Delete an existing record. (Prepares and execute the DELETE statements)
     * @param array $opt Associative array of options to generate the UPDATE statement. Supported: <i>where, limit, param</i>
     */
    public function delete($opt=NULL){
        return Rookie_Core::db()->delete($this, $opt);
    }

    //------- static shorthand methods
    /**
     * Find a record. (Prepares and execute the SELECT statements)
     * @param mixed $model The model class name or object to be select
     * @param array $opt Associative array of options to generate the SELECT statement. Supported: <i>where, limit, select, param, asc, desc, custom, asArray</i>
     * @return mixed A model object or associateve array of the queried result
     */
    public static function _find($model, $opt=null){
        return Rookie_Core::db()->find($model, $opt);
    }

    /**
     * Find a record and its associated model. Relational search. (Prepares and execute the SELECT statements)
     * @param mixed $model The model class name or object to be select.
     * @param string $rmodel The related model class name.
     * @param array $opt Associative array of options to generate the SELECT statement. Supported: <i>where, limit, select, param, joinType, match, asc, desc, custom, asArray, include, includeWhere, includeParam</i>
     * @return mixed A list of model object(s) or associateve array of the queried result
     */
    public static function _relate($model, $rmodel, $opt=null){
        if($model==null || $model=='')
            $model = self::$className;
        return Rookie_Core::db()->relate($model, $rmodel, $opt);
    }

    /**
     * Adds a new record. (Prepares and execute the INSERT statements)
     * @param object $model The model object to be insert.
     * @return int The inserted record's Id
     */
    public static function _insert($model){
        return Rookie_Core::db()->insert($model);
    }

    /**
     * Adds a new record with a list of keys & values (assoc array) (Prepares and execute the INSERT statements)
     * @param object $model The model object to be insert.
     * @param array $data Array of data (keys and values) to be insert
     * @return int The inserted record's Id
     */
    public static function _insertAttributes($model, $data){
        return Rookie_Core::db()->insert_attributes($model, $data);
    }
    
    /**
     * Use DooModel::_insertAttributes() instead.
     * @deprecated deprecated since version 1.3
     */
    public static function _insert_attributes($model, $data){
        return Rookie_Core::db()->insertAttributes($model, $data);
    }

    /**
     * Adds a new record with its associated models. Relational insert. (Prepares and execute the INSERT statements)
     * @param object $model The model object to be insert.
     * @param array $rmodels A list of associated model objects to be insert along with the main model.
     * @return int The inserted record's Id
     */
    public static function _relatedInsert($model, $rmodels){
        return Rookie_Core::db()->relatedInsert($model, $rmodels);
    }

    /**
     * Update an existing record with its associated models. Relational update. (Prepares and execute the UPDATE statements)
     * @param mixed $model The model object to be updated.
     * @param array $rmodels A list of associated model objects to be updated or insert along with the main model.
     * @param array $opt Assoc array of options to update the main model. Supported: <i>where, limit, field, param</i>
     */
    public static function _relatedUpdate($model, $rmodels, $opt=NULL){
        return Rookie_Core::db()->relatedUpdate($model, $rmodels, $opt);
    }

    /**
     * Update an existing record. (Prepares and execute the UPDATE statements)
     * @param mixed $model The model object to be updated.
     * @param array $opt Associative array of options to generate the UPDATE statement. Supported: <i>where, limit, field, param</i>
     */
    public static function _update($model, $opt=NULL){
        return Rookie_Core::db()->update($model, $opt);
    }

    /**
     * Update an existing record with a list of keys & values (assoc array). (Prepares and execute the UPDATE statements)
     * @param mixed $model The model object to be updated.
     * @param array $opt Associative array of options to generate the UPDATE statement. Supported: <i>where, limit, field, param</i>
     */
    public static function _update_attributes($model, $data, $opt=NULL){
        return Rookie_Core::db()->update($model, $data, $opt);
    }

    /**
     * Returns the last inserted record's id
     * @return int
     */
    public static function _lastInsertId(){
        return Rookie_Core::db()->lastInsertId();
    }

	/**
	 * Delete ALL existing records. (Prepares and executes the DELETE statement)
	 * @param mixed $model The model object whos records should all be deleted
	 */
	public static function _deleteAll($model) {
		return Rookie_Core::db()->deleteAll($model);
	}

    /**
     * Delete an existing record. (Prepares and execute the DELETE statements)
     * @param mixed $model The model object to be deleted.
     * @param array $opt Associative array of options to generate the UPDATE statement. Supported: <i>where, limit, param</i>
     */
    public static function _delete($model, $opt=NULL){
        return Rookie_Core::db()->delete($model, $opt);
    }


    //---------- Useful querying methods such as getOne, count, limit

    /**
     * Retrieve a list of paginated data. To be used with DooPager
     *
     * @param string $limit String for the limit query, eg. '6,10'
     * @param string $asc Fields to be sorted Ascendingly. Use comma to seperate multiple fields, eg. 'name,timecreated'
     * @param string $desc Fields to be sorted Descendingly. Use comma to seperate multiple fields, eg. 'name,timecreated'
     * @param array $options Options for the query. Available options see @see find()
     * @return mixed A model object or associateve array of the queried result
     */
    public function limit($limit=1, $asc='', $desc='', $options=null){
        if($asc!='' || $desc!='' || $options!==null){
            $options['limit'] = $limit;
            if($asc!=''){
                $options['asc'] = $asc;
            }
            if($desc!=''){
                $options['desc'] = $desc;
            }
            if($asc!='' && $desc!=''){
                $options['asc'] = $asc;
                $options['custom'] = ','. $desc .' DESC';
            }
            //print_r($options);
            return Rookie_Core::db()->find($this, $options);
        }
        return Rookie_Core::db()->find($this, array('limit'=>$limit));
    }

    /**
     * Retrieve model by one record.
     *
     * @param array $options Options for the query. Available options see @see find()
     * @return mixed A model object or associateve array of the queried result
     */
    public function getOne($options=null){
        if($options!==null){
            $options['limit'] = 1;
            return Rookie_Db::db()->find($this, $options);
        }
        return Rookie_Db::db()->find($this, array('limit'=>1));
    }

    /**
     * Retrieve the total records in a table. COUNT()
     *
     * @param array $options Options for the query. Available options see @see find() and additional 'distinct' option
     * @return int total of records
     */
    public function count($options=null){
		$options['select'] = isset($options['having']) ? $options['select'] . ', ' : '';
		if (isset($options['distinct']) && $options['distinct'] == true) {
			$options['select'] .= 'COUNT(DISTINCT '. $this->_table . '.' . $this->_fields[0] .') as _doototal';
		} else {
			$options['select'] .= 'COUNT('. $this->_table . '.' . $this->_fields[0] .') as _doototal';
		}
        $options['asArray'] = true;
        $options['limit'] = 1;
        $rs = Rookie_Core::db()->find($this, $options);
        return $rs['_doototal'];
    }

    //------ static methods for the useful querying method -----

    /**
     * Retrieve a list of paginated data. To be used with DooPager
     *
     * @param object $model The model object to be select.
     * @param string $limit String for the limit query, eg. '6,10'
     * @param string $asc Fields to be sorted Ascendingly. Use comma to seperate multiple fields, eg. 'name,timecreated'
     * @param string $desc Fields to be sorted Descendingly. Use comma to seperate multiple fields, eg. 'name,timecreated'
     * @param array $options Options for the query. Available options see @see find()
     * @return mixed A model object or associateve array of the queried result
     */
    public static function _limit($limit=1, $asc='', $desc='', $options=null){
        if($asc!='' || $desc!='' || $options!==null){
            $options['limit'] = $limit;
            if($asc!=''){
                $options['asc'] = $asc;
            }
            if($desc!=''){
                $options['desc'] = $desc;
            }
            if($asc!='' && $desc!=''){
                $options['asc'] = $asc;
                $options['custom'] = ','. $desc .' DESC';
            }
            return Rookie_Core::db()->find(self::$className, $options);
        }
        return Rookie_Core::db()->find(self::$className, array('limit'=>$limit));
    }

    /**
     * Retrieve model by one record.
     *
     * @param object $model The model object to be select.
     * @param array $options Options for the query. Available options see @see find()
     * @return mixed A model object or associateve array of the queried result
     */
    public static function _getOne($model=null, $options=null){
        if($model===null)
            $model = new self::$className;

        if($options!==null){
            $options['limit'] = 1;
            return Rookie_Core::db()->find($model, $options);
        }
        return Rookie_Core::db()->find($model, array('limit'=>1));
    }

    /**
     * Retrieve the total records in a table. COUNT()
     *
     * @param object $model The model object to be select.
     * @param array $options Options for the query. Available options see @see find() and additional 'distinct' option
     * @return int total of records
     */
    public static function _count($model=null, $options=null){
        if($model===null)
            $model = new self::$className;

		$options['select'] = isset($options['having']) ? $options['select'] . ', ' : '';
		if (isset($options['distinct']) && $options['distinct'] == true) {
			$options['select'] .= 'COUNT(DISTINCT '. $model->_table . '.' . $model->_fields[0] .') as _doototal';
		} else {
			$options['select'] .= 'COUNT('. $model->_table . '.' . $model->_fields[0] .') as _doototal';
		}

        $options['asArray'] = true;
        $options['limit'] = 1;
        $rs = Rookie_Core::db()->find($model, $options);
        return $rs['_doototal'];
    }


    //--------------- dynamic querying --------------
    public function __call($name, $args){

        // $food->getById( $id );
        // $food->getById(14);
        // $food->getById(14, array('limit'=>1)) ;
        // $food->getById_location(14, 'Malaysia') ;
        // $food->getById_location(14, 'Malaysia', array('limit'=>1)) ;
        if(strpos($name, 'get')===0){
            if(self::$caseSensitive==false){
                $field = strtolower( substr($name,5));
            }else{
                $field = substr($name,5);
            }

            // if end with _first, add 'limit'=>'first' to Option array
            if( substr($name,-6,strlen($field)) == '_first' ){
                $field = str_replace('_first', '', $field);
                $first['limit'] = 1;
            }

            // underscore _ as AND in SQL
            if(strpos($field, '_')!==false){
                $field = explode('_', $field);
            }

            $clsname = get_class($this);
            $obj = new $clsname;

            if(is_string($field)){
                $obj->{$field} = $args[0];

                //if more than the field total, it must be an option array
                if(sizeof($args)>1){
                    if(isset($first))
                        $args[1]['limit'] = 1;
                    return Rookie_Core::db()->find($obj, $args[1]);
                }

                if(isset($first)){
                    return Rookie_Core::db()->find($obj, $first);
                }
                return Rookie_Core::db()->find($obj);
            }
            else{
                $i=0;
                foreach($field as $f){
                    $obj->{$f} = $args[$i++];
                }

                //if more than the field total, it must be an option array
                if(sizeof($args)>$i){
                    if(isset($first))
                        $args[$i]['limit'] = 1;
                    return Rookie_Core::db()->find($obj, $args[$i]);
                }

                if(isset($first)){
                    return Rookie_Core::db()->find($obj, $first);
                }
                return Rookie_Core::db()->find($obj);
            }
        }

        # relateTheRelatedModelClassName
        //$food->relateFoodType();
        //$food->relateFoodType( $optionsOrObject );  if 1 args, it will be option or object
        //$food->relateFoodType( $food, $options );  if more than 1
        # You can get only one by
        //$food->relateFoodType_first();    this adds the 'limit'=>'first' to the Options
        else if(strpos($name, 'relate')===0){
            $relatedClass = substr($name,6);

            // if end with _first, add 'limit'=>'first' to Option array
            if( substr($name,-6,strlen($relatedClass)) == '_first' ){
                $relatedClass = str_replace('_first', '', $relatedClass);
                $first['limit'] = 'first';
                if(sizeof($args)===0){
                    $args[0] = $first;
                }
                else{
                    if(is_array($args[0])){
                        $args[0]['limit'] = 'first';
                    }else{
                        $args[1]['limit'] = 'first';
                    }
                }
            }

            if(sizeof($args)===0){
                return Rookie_Core::db()->relate( $this, $relatedClass);
            }
            else if(sizeof($args)===1){
                if(is_array($args[0])){
                    return Rookie_Core::db()->relate( $this, $relatedClass, $args[0]);
                }else{
                    if(isset($first)){
                        return Rookie_Core::db()->relate( $args[0], $relatedClass, $first);
                    }
                    return Rookie_Core::db()->relate( $args[0], $relatedClass);
                }
            }else{
                return Rookie_Core::db()->relate( $args[0], $relatedClass, $args[1]);
            }
        }
    }

    public static function __callStatic($name, $args){

        // Food::getById( $id );
        // Food::getById(14);
        // Food::getById(14, array('limit'=>1)) ;
        // Food::getById_location(14, 'Malaysia') ;
        // Food::getById_location(14, 'Malaysia', array('limit'=>1)) ;
        if(strpos($name, 'get')===0){
            if(self::$caseSensitive==false){
                $field = strtolower( substr($name,5));
            }else{
                $field = substr($name,5);
            }

            // if end with _first, add 'limit'=>'first' to Option array
            if( substr($name,-7,strlen($field)) == '__first' ){
                $field = str_replace('__first', '', $field);
                $first['limit'] = 1;
            }

            // underscore _ as AND in SQL
            if(strpos($field, '__')!==false){
                $field = explode('__', $field);
            }

            $clsname = self::$className;
            $obj = new $clsname;

            if(is_string($field)){
                $obj->{$field} = $args[0];

                //if more than the field total, it must be an option array
                if(sizeof($args)>1){
                    if(isset($first))
                        $args[1]['limit'] = 1;
                    return Rookie_Core::db()->find($obj, $args[1]);
                }

                if(isset($first)){
                    return Rookie_Core::db()->find($obj, $first);
                }
                return Rookie_Core::db()->find($obj);
            }
            else{
                $i=0;
                foreach($field as $f){
                    $obj->{$f} = $args[$i++];
                }

                //if more than the field total, it must be an option array
                if(sizeof($args)>$i){
                    if(isset($first))
                        $args[$i]['limit'] = 1;
                    return Rookie_Core::db()->find($obj, $args[$i]);
                }

                if(isset($first)){
                    return Rookie_Core::db()->find($obj, $first);
                }
                return Rookie_Core::db()->find($obj);
            }
        }

        # relateTheRelatedModelClassName
        // Food::relateFoodType();
        // Food::relateFoodType( $optionsOrObject );  if 1 args, it will be option or object
        // Food::relateFoodType( $food, $options );  if more than 1
        # You can get only one by
        //$food->relateFoodType_first();    this adds the 'limit'=>'first' to the Options
        else if(strpos($name, 'relate')===0){
            $relatedClass = substr($name,6);

            // if end with _first, add 'limit'=>'first' to Option array
            if( substr($name,-7,strlen($relatedClass)) == '__first' ){
                $relatedClass = str_replace('__first', '', $relatedClass);
                $first['limit'] = 'first';
                if(sizeof($args)===0){
                    $args[0] = $first;
                }
                else{
                    if(is_array($args[0])){
                        $args[0]['limit'] = 'first';
                    }else{
                        $args[1]['limit'] = 'first';
                    }
                }
            }

            if(sizeof($args)===0){
                return Rookie_Core::db()->relate( self::$className, $relatedClass);
            }
            else if(sizeof($args)===1){
                if(is_array($args[0])){
                    return Rookie_Core::db()->relate( self::$className, $relatedClass, $args[0]);
                }else{
                    if(isset($first)){
                        return Rookie_Core::db()->relate( $args[0], $relatedClass, $first);
                    }
                    return Rookie_Core::db()->relate( $args[0], $relatedClass);
                }
            }else{
                return Rookie_Core::db()->relate( $args[0], $relatedClass, $args[1]);
            }
        }
    }

    public static function __set_state($properties){
        $obj = new self::$className;
        foreach($properties as $k=>$v){
            $obj->{$k} = $v;
        }
        return $obj;
    }

    /**
     * 重新连接数据库
     * @param string $data_name 数据库名
     * @param string $is_cluster 是否打开集群,默认为关闭
     */
    public static function set_cluster($data_name = NULL, $is_cluster = false)
    {
		if(empty($data_name))
    		require CACHEPATH . "configs/db.php";
		else 
			require CACHEPATH . "configs/{$data_name}_db.php";
		
		Rookie_Core::$_db=NULL;
        Rookie_Core::$_useDbReplicate = null;
      
		if ($is_cluster)
		{
			Rookie_Core::$_useDbReplicate = TRUE;
			Rookie_Core::$db_type = 'slave';
		}
		else 
		{
			Rookie_Core::$_useDbReplicate = NULL;
			Rookie_Core::$db_type = 'dev';
		}
			
		////数据库连接类型 主重连接slave ,dev prod
		Rookie_Core::$db_configs = $dbconfig; 
		Rookie_Core::useDbReplicate();
		Rookie_Core::db()->setMap($dbconfig);
		Rookie_Core::db()->setDb($dbconfig, 'dev'); //默认的数据库,集群的时候是主数据库
		
    }
	/**
	 * 获取表字段
	 * @param $table 		数据表
	 * @return array
	 */
	public function get_fields($table) 
	{
		$fields = array();
		$r = $this->fetchAll("SHOW COLUMNS FROM model_field");
		
		foreach ($r as $key => $val)
		{
			foreach ($val as $k => $v)
			{
				$fields[$val['Field']] = $val['Type'];
			}
			
		}
		return $fields;
	}
	
	/**
	 * 查询缓存
	 */
	public function cache()
	{
		
	}
}
