<?php
/**
 * DooMemCache class file.
 *
 * @author Leng Sheng Hong <darkredz@gmail.com>
 * @link http://www.doophp.com/
 * @copyright Copyright &copy; 2009 Leng Sheng Hong
 * @license http://www.doophp.com/license
 */

class Rookie_Cache_Mem implements Module{
    /**
     * Memcached connection
     * @var Memcache
     */
    public static $_memcache;

    /**
     * Configurations of the connections
     * @var array
     */
    public static $_config;

    public static $_cache;
    
    public static $init;
    
    public function setInit($conf=Null) 
    {
	    	if (self::$init)
	    		return self::$_memcache;
    	
    		self::$init = true;
    		
    		if( ! class_exists('Memcached'))
    			throw new Rookie_Exception(' Class \'Memcache\' not found' );
    			
        	self::$_memcache = new Memcached();
		
        // host, port, persistent, weight
        if($conf!==Null)
        {
        		self::$_memcache->setOption(Memcached::OPT_DISTRIBUTION, Memcached::DISTRIBUTION_CONSISTENT);  
			self::$_memcache->setOption(Memcached::OPT_HASH, Memcached::HASH_CRC);  
			self::$_memcache->addServers($conf);
        }
        else
        {
            self::$_memcache->addServer('127.0.0.1', 11211);
        }
        return self::$_memcache;
    }

    /**
     * Adds a cache with an unique Id.
     *
     * @param string $id Cache Id
     * @param mixed $data Data to be stored
     * @param int $expire Seconds to expired
     * @param int $compressed To store the data in Zlib compressed format
     * @return bool True if success
     */
    public static function set($id, $data, $expire=3600){
         return self::$_memcache->add($id, $data, $expire);
    }

    /**
     * Retrieves a value from cache with an Id.
     *
     * @param string $id A unique key identifying the cache
     * @return mixed The value stored in cache. Return false if no cache found or already expired.
     */
    public static function get($id){
        return self::$_memcache->get($id);
    }
    
    /**
     * Deletes an APC data cache with an identifying Id
     *
     * @param string $id Id of the cache
     * @return bool True if success
     */
    public static function flush($id){
        return self::$_memcache->delete($id);
    }

    /**
     * Deletes all data cache
     * @return bool True if success
     */
    public static function flushAll(){
        return self::$_memcache->flush();
    }


}

