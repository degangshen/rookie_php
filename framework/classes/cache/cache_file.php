<?php
/**
 * DooFileCache class file.
 *
 * @author Leng Sheng Hong <darkredz@gmail.com>
 * @link http://www.doophp.com/
 * @copyright Copyright &copy; 2009 Leng Sheng Hong
 * @license http://www.doophp.com/license
 */


/**
 * DooFileCache provides file based caching methods.
 *
 * @author Leng Sheng Hong <darkredz@gmail.com>
 * @version $Id: DooFileCache.php 1000 2009-08-27 19:36:10
 * @package doo.cache
 * @since 1.1
 */

class Rookie_Cache_File implements Module{

    public static $_directory;

    /**
     * Option to hash the Cache ID into md5 hash string
     * @var bool
     */
    public static $hashing = true;

    public function setInit($path='') {
        if ( $path=='' ) {
        		self::$_directory =  WEBPATH . 'caches/';
        }else{
             self::$_directory = $path;
        }
       // echo self::$_directory;
    }

    /**
     * Retrieves a value from cache with an Id.
     *
     * @param string $id A unique key identifying the cache
     * @return mixed The value stored in cache. Return null if no cache found or already expired.
     */
    public static function get($id) {
        if(self::$hashing===true)
            $cfile = self::$_directory . md5($id);
        else
            $cfile = self::$_directory . $id;

        if (file_exists($cfile)){
            $data = file_get_contents($cfile) ;
            $expire = substr($data, 0, 10);

            if(time() < $expire){
                return unserialize(substr($data, 10));
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
            $cfile = self::$_directory . $folder .'/'. md5($id);
        else
            $cfile = self::$_directory . $folder .'/'. $id;

        if (file_exists($cfile)){
            $data = file_get_contents($cfile) ;
            $expire = substr($data, 0, 10);

            if(time() < $expire){
                return unserialize(substr($data, 10));
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
      * @param int $expire Duration to determine if the cache is expired. 0 for never expire
      * @return bool
      */
    public static function set($id, $value, $expire=0) {
        if($expire===0)
            $expire = time()+31536000;
        else
            $expire = time()+$expire;

        if(self::$hashing===true)
            return file_put_contents(self::$_directory . md5($id) , $expire.serialize($value), LOCK_EX);
            
        return file_put_contents(self::$_directory . $id , $expire.serialize($value), LOCK_EX);
    }

    /**
     * 在不同的目录存储高速缓存
     *
     * @param string $folder 要创建和存储缓存文件的目录名称
     * @param string $id 缓存的唯一ID
     * @param mixed $value 缓存数据值被存储。
     * @param int $expire 时间，以确定是否缓存过期。 0永不过期
     * @return bool
     */
    public static function setIn($folder, $id, $value, $expire=0) {
        $cfile = self::$_directory.$folder.'/';

        if(!file_exists($cfile))
            mkdir($cfile);

        if(self::$hashing===true)
            $cfile .= md5($id);
        else
            $cfile .= $id;

        if($expire===0)
            $expire = time()+31536000;
        else
            $expire = time()+$expire;
        return file_put_contents($cfile, $expire.serialize($value), LOCK_EX);
    }

    /**
     * Delete a cache file by Id
     * @param $id Id of the cache
     * @return mixed
     */
    public static function flush($id) {
        if(self::$hashing===true)
            $cfile = self::$_directory.md5($id);
        else
            $cfile = self::$_directory.$id;

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
            if (is_file(self::$_directory . $file))
                unlink(self::$_directory . $file);
            else if (is_dir(self::$_directory . $file) && substr($file, 0, 4) == 'mdl_')
                self::flushAllIn($file);	
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
            $cfile = self::$_directory.$folder.'/'.md5($id);
        else
            $cfile = self::$_directory.$folder.'/'.$id;

        if(file_exists($cfile)){
            unlink( $cfile );
        }
    }

}
