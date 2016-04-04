<?php
/**
 * 连接数据库
 * @author shendegang
 *
 */
class Rookie_Db_Conn implements Module
{
	/**
	 * @var array $loadClassName 
	 */
	private static $loadClassName = array();
	
	/* (non-PHPdoc)
	 * @see Module::setInit()
	 */
	public function setInit($param = array()) 
	{
		if(Rookie_Core::$_db===NULL){
			if(Rookie_Core::$_useDbReplicate===NULL){
				Rookie_Core::loadCore('db/DooSqlMagic');
				Rookie_Core::$_db = new DooSqlMagic;
				Rookie_Core::$_db->setDb($param,'dev');
			}else{
				Rookie_Core::loadCore('db/DooMasterSlave');
				Rookie_Core::$_db = new DooMasterSlave;
				Rookie_Core::$_db->setDb($param,'slave');
			}
		}
		
		if(!Rookie_Core::$_db->connected)
			Rookie_Core::$_db->connect();
		
		return Rookie_Core::$_db;
	}
	
	/**
	 * 设置表名
	 * @param  string $tableModelName
	 * @return object
	 */
	public function table($tableModelName)
	{
		$modelName = "{$tableModelName}Model";
		$path =  WEBPATH."protected/model/$modelName.php";
		if (isset(self::$loadClassName[md5($path)]))
			return self::$loadClassName[md5($path)];
		else
		{
			require  $path;
			$modelObj =  new $modelName();
			self::$loadClassName[md5($path)] = $modelObj;
			return $modelObj;
		}
	}
}