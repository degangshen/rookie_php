<?php
/**
 * DooPhpCache class file.
 *
 * @author Leng Sheng Hong <darkredz@gmail.com>
 * @link http://www.doophp.com/
 * @copyright Copyright &copy; 2009 Leng Sheng Hong
 * @license http://www.doophp.com/license
 */


/**
 * DooPhpCache provides file based caching which convert data into PHP variables.
 *
 * @author Leng Sheng Hong <darkredz@gmail.com>
 * @version $Id: DooPhpCache.php 1000 2009-08-29 14:18:10
 * @package doo.cache
 * @since 1.2
 */

class Rookie_Cache_Php implements Module{

    public static $_directory;

    /**
     * Option to hash the Cache ID into md5 hash string
     * @var bool
     */
    public static $hashing = false;

    public  function setInit($path='') {
    	
    	if ( !$path) {
        		self::$_directory =  WEBPATH . 'caches/cache_php/';
        }else{
             self::$_directory = $path;
        }
    }

    /**
     * Retrieves a value from cache with an Id.
     *
     * @param string $id A unique key identifying the cache
     * @return mixed The value stored in cache. Return null if no cache found or already expired.
     */
    public static function get($id) {
        if(self::$hashing===true)
            $cfile = self::$_directory . md5($id) . '.php';
        else
            $cfile = self::$_directory . $id . '.php';

        if (file_exists($cfile)){
            include $cfile ;
            if(time() < $data[0]){
                return $data[1];
            }else{
                unlink($cfile);
            }
        }
    }


    /**
     * Retrieves a value from cache with an Id from different directories
     *
     * @param string $folder Directory name for the cache files stored
     * @param string $id A unique key identifying the cache
     * @return mixed The value stored in cache. Return null if no cache found or already expired.
     */
    public static function getIn($folder, $id) {
        if(self::$hashing===true)
            $cfile = self::$_directory . $folder .'/'. md5($id) .'.php';
        else
            $cfile = self::$_directory . $folder .'/'. $id .'.php';

        if (file_exists($cfile)){
            include $cfile ;
            if(time() < $data[0]){
                return $data[1];
            }else{
                unlink($cfile);
            }
        }
    }

     /**
      * Adds a cache with an unique Id.
      *
      * @param string $id Unique Id of the cache
      * @param mixed $value Cache data value to be stored.
      * @param int $expire Duration to determine if the cache is expired.
      * @return bool
      */
    public static function set($id, $value, $expire=0) {
        if($expire===0)
            $expire = time()+31536000;
        else
            $expire = time()+$expire;

        if(self::$hashing===true)
			return file_put_contents(self::$_directory . md5($id) . '.php', '<?php defined(\'ROOKIE\') or die(\'No direct script access.\');  $data = array('.$expire.', '. var_export($value, true) . '); ?>', LOCK_EX);
        
        return file_put_contents(self::$_directory . $id . '.php', '<?php defined(\'ROOKIE\') or die(\'No direct script access.\');  $data = array('.$expire.', '. var_export($value, true) . '); ?>', LOCK_EX);
    }

    /**
     * Store cache in different directories
     *
     * @param string $folder Directory name for the cache files to be created and stored
     * @param string $id Unique Id of the cache
     * @param mixed $value Cache data value to be stored.
     * @param int $expire Duration to determine if the cache is expired.
     * @return bool
     */
    public function setIn($folder, $id, $value, $expire=0) {
        $cfile = self::$_directory.$folder.'/';

        if(!file_exists($cfile))
            mkdir($cfile);

        if(self::$hashing===true)
            $cfile .= md5($id) . '.php';
        else
            $cfile .= $id . '.php';

        if($expire===0)
            $expire = time()+31536000;
        else
            $expire = time()+$expire;

        return file_put_contents($cfile, '<?php $data = array('.$expire.', '. var_export($value, true) . '); ?>', LOCK_EX);
    }

    /**
     * Delete a cache file by Id
     * @param $id Id of the cache
     * @return mixed
     */
    public static function flush($id) {
        if(self::$hashing===true)
            $cfile = self::$_directory.md5($id).'.php';
        else
            $cfile = self::$_directory.$id.'.php';

        if (file_exists($cfile)) {
            unlink($cfile);
            return true;
        }
        return false;
    }

    /**
     * Deletes all data cache files
     * @return bool
     */
    public static function flushAll() {
        $handle = opendir(self::$_directory);

        while(($file = readdir($handle)) !== false) {
            if (is_file($file))
                unlink($file);
        }
        return true;
    }

    /**
     * Deletes all data cache in a folder
     * @param string $folder
     */
	public static function flushAllIn($folder){
        $cfile = self::$_directory.$folder.'/';
        if(file_exists($cfile)){
            $handle = opendir($cfile);
            while(($file = readdir($handle)) !== false) {
                $file = $cfile.$file;
                if (is_file($file)){
                    unlink( $file );
                }
            }
        }
    }

    /**
     * Deletes a data cache in a folder identified by an ID
     * @param string $folder
     * @param string $id
     */
	public static function flushIn($folder, $id){
       if(self::$hashing===true)
            $cfile = self::$_directory.$folder.'/'.md5($id).'.php';
        else
            $cfile = self::$_directory.$folder.'/'.$id.'.php';
        if(file_exists($cfile)){
            unlink( $cfile );
        }
    }


}
