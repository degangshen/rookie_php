<?php
/**
 * redis class
 * @author shendegang
 *
 */
class Rookie_Cache_Redis implements Module
{
	public static $init;
    
	private static $_reidsConfig = array();
	
	private static $_redis;
    
	private static $_timeout = 3;
	
	private static $_flexihash;
	
    public function setInit($conf=Null) 
    {
	    	if (self::$init)
	    		return self::$_redis;
    		
	    	self::$_flexihash = new Flexihash();
	    	
	    	self::$_reidsConfig = $conf;
    		self::$init = true;
    		
    		if( ! class_exists('redis'))
    			throw new Rookie_Exception(' Class \'redis\' not found' );
    		self::$_redis = new Redis();	
    		
    		$this->connection();
    		
    }		
    
    /**
     * 连接
     * @param array $redisConfig 配置文件
     */
    public function connection()
    {
    	
    	$redisConfig = self::$_flexihash->addTargets(self::$_reidsConfig);
    	if (is_array(self::$_reidsConfig) && count(self::$_reidsConfig) >= 1)
    	{
    		try {
    			self::$_redis->connect($redisConfig[0], $redisConfig[1], self::$_timeout);
    		}catch (Exception $e)
    		{
    			//删除连接失败的ip
    			unset(self::$_reidsConfig[$key]);
    		}
    	}
    	return false;
    }
   
}