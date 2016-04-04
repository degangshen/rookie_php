<?php	defined('ROOKIE') or die('No direct script access.');
/**
 * DooMasterSlave is an ORM tool based on DooSqlMagic with Database Replication support.
 *
 * <p>This class handles Master-Slave connections. It automatically handle CRUD operations with appropriate slave/master DB server.
 * You can handle thee connections manually by using useConnection, connectMaster, and connectSlave.</p>
 *
 * <p>DooMasterSlave <b>DOES NOT</b> send SELECT statement to a random slave. Instead, it is based on calculation with both access time and Slave nodes.</p>
 *
 * <p>To use DB replication, you would have to setup the slave servers in <b>db.conf.php</b></p>
 * 
 * <code>
 * //这将作为主
 * $dbconfig['dev'] = array('localhost', 'db', 'root', '1234', 'mysql',true);
 *
 * //从站作为主站相同的信息
 * $dbconfig['slave'] = array('192.168.1.1', '192.168.1.2', '192.168.1.3');
 *
 * //OR ...
 * //使用不同的资讯奴隶，使用一个字符串，如果是作为主信息相同。
 * $dbconfig['slave'] = array(
 *                      array('192.168.1.1', 'db', 'dairy', '668dj0', 'mysql',true),
 *                      array('192.168.1.2', 'db', 'yuhus', 'gu34k2', 'mysql',true),
 *                      array('192.168.1.3', 'db', 'lily', '84ju2a', 'mysql',true),
 *                      '192.168.1.4'
 *                   );
 * </code>
 *
 * <p>In the bootstrap index.php you would need to call the <b>useDbReplicate</b> method.</p>
 * <code>
 * Doo::useDbReplicate();
 * Doo::db()->setMap($dbmap);
 * Doo::db()->setDb($dbconfig, $config['APP_MODE']);
 * </code>
 *
 * @author Leng Sheng Hong <darkredz@gmail.com> 修改改者：shendegang
 * @version $Id: DooMasterSlave.php 1000 2009-08-20 22:53:26
 * @package doo.db
 * @since 1.1
 */

Rookie_Core::loadCore('db/DooSqlMagic');

class DooMasterSlave extends DooSqlMagic {

    const MASTER = 'master';
    const SLAVE = 'slave';

    /**
     * Stores the pdo connection for master & slave
     * @var array
     */
    protected $pdoList = array();

    protected $autoToggle = True;

    protected $masterConfig = array();
    
    /**
     * Connects to the database with the default slaves configurations
     */
    public function connect(){
        if($this->dbconfig==NULL)return;

        $slaves = $this->dbconfig_list['slave'];
        $totalSlaves = sizeof($slaves);
		$this->masterConfig =  $this->dbconfig_list['master'];
		
        $time = round(microtime(true), 2)*100;
        $time = substr($time, strlen($time)-2);

        $sessionToHandle = 100/$totalSlaves;

        $choosenSlaveIndex = floor($time/$sessionToHandle);

        $choosenSlave = $slaves[$choosenSlaveIndex];
       
        $newDbconfig = array();
        foreach ($this->dbconfig as $key => $val)
        {
        	$configId = md5($val[0].$val[1].$val[2].$val[3]);
        	$newDbconfig[$configId] = $val;
        }
        $this->dbconfig = $newDbconfig;
      
        if(is_string($choosenSlave)){
           // echo '<br><br>' . "{$this->dbconfig[4]}:host={$choosenSlave};dbname={$this->dbconfig[1]}";
            try{
                $this->pdo = new PDO("{$this->dbconfig[4]}:host={$choosenSlave};dbname={$this->dbconfig[1]}", $this->dbconfig[2], $this->dbconfig[3],array(PDO::ATTR_PERSISTENT => $this->dbconfig[5]));
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->connected = true;
            	if(isset($this->dbconfig['charset']) && isset($this->dbconfig['collate'])){
	                $this->pdo->exec("SET NAMES '". $this->dbconfig['charset']. "' COLLATE '". $this->dbconfig['collate'] ."'");
	            }
	            else if(isset($this->dbconfig['charset']) ){
	                $this->pdo->exec("SET NAMES '". $this->dbconfig['charset']. "'");
	            }
            }catch(PDOException $e){
                throw new SqlMagicException('Failed to open the DB connection', SqlMagicException::DBConnectionError);
            }
        }else{
           // echo '<br><br>' . "{$choosenSlave[4]}:host={$choosenSlave[0]};dbname={$choosenSlave[1]}";
            try{
            	$configId = md5($choosenSlave[0].$choosenSlave[1].$choosenSlave[2].$choosenSlave[3]);
            	$this->pdo = new PDO("{$choosenSlave[4]}:host={$choosenSlave[0]};dbname={$choosenSlave[1]}", $choosenSlave[2], $choosenSlave[3],array(PDO::ATTR_PERSISTENT => $choosenSlave[5]));
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->connected = true;
                $tempDbconfig = array();
                foreach ($this->dbconfig as $k => $v)
                	$tempDbconfig[] = $v;
                $this->dbconfig = $tempDbconfig;
	            if(isset($this->dbconfig['charset']) && isset($this->dbconfig['collate'])){
	                $this->pdo->exec("SET NAMES '". $this->dbconfig['charset']. "' COLLATE '". $this->dbconfig['collate'] ."'");
	            }
	            else if(isset($this->dbconfig['charset']) ){
	                $this->pdo->exec("SET NAMES '". $this->dbconfig['charset']. "'");
	            }
            
            }catch(PDOException $e){
            	$message = 'mysql连接错误，Failed to open the DB connection. 文件名：'.$e->getFile().'行数：'.$e->getLine().'错误服务器地址：'.$choosenSlave[0];
            	Rookie_Log::instance()->add(Rookie_Log::ERROR, $message);
            	//throw new SqlMagicException('Failed to open the DB connection', SqlMagicException::DBConnectionError);
            	unset($this->dbconfig[$configId]);
            	$this->connect();
               
            }
        }
        $this->pdoList[1] = $this->pdo;
    }

    /**
     * Connects to a slave.
     * 
     * Choose an index of the slave configuration to connect as defined in db.conf.php
     * @param int $slaveIndex
     */
    public function connectSlave($slaveIndex){
        $choosenSlave = $this->dbconfig_list['slave'][$slaveIndex];
        try{
            $this->pdo = new PDO("{$choosenSlave[4]}:host={$choosenSlave[0]};dbname={$choosenSlave[1]}", $choosenSlave[2], $choosenSlave[3],array(PDO::ATTR_PERSISTENT => $choosenSlave[5]));
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connected = true;
        }catch(PDOException $e){
            throw new SqlMagicException('Failed to open the DB connection', SqlMagicException::DBConnectionError);
        }
        $this->pdoList[1] = $this->pdo;
    }

    /**
     * Connects to the database with the default database configurations (master)
     */
    public function connectMaster(){
    	$this->dbconfig = $this->masterConfig;
    	if(!isset($this->dbconfig[4]))
    		throw new SqlMagicException('Failed to open the DB connection', SqlMagicException::DBConnectionError);
    		
        try{
            $this->pdo = new PDO("{$this->dbconfig[4]}:host={$this->dbconfig[0]};dbname={$this->dbconfig[1]}", $this->dbconfig[2], $this->dbconfig[3],array(PDO::ATTR_PERSISTENT => $this->dbconfig[5]));
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connected = true;
        }catch(PDOException $e){
            throw new SqlMagicException('Failed to open the DB connection', SqlMagicException::DBConnectionError);
        }
        $this->pdoList[0] = $this->pdo;
    }

    /**
     * Set connection to use slaves or master.
     *
     * If you use this method, you would have to handle the connection (slave/master) manually for the all query codes after this call.
     *
     * @param string $mode Either 'master' or 'slave'
     */
    public function useConnection($mode){
        if($mode=='master'){
            if( !isset($this->pdoList[0]) )
                $this->connectMaster();
            else
                $this->pdo = $this->pdoList[0];
        }
        else if($mode=='slave'){
            $this->pdo = $this->pdoList[1];
        }
        $this->autoToggle = False;
    }

    /**
     * Execute a query to the connected database. Auto toggle between master & slave.
     *
     * @param string $query SQL query prepared statement
     * @param array $param Values used in the prepared SQL
     * @return PDOStatement
     */
    public function query($query, $param=null){
        if($this->autoToggle===True){
            $isSelect = strtoupper(substr($query, 0, 6)) == 'SELECT';
            //change to master if update, insert, delete, create connection if not exist
            if( !isset($this->pdoList[$isSelect]) ){
                echo '<h1>Master connected</h1>';
                $this->connectMaster();
            }else{
                $rr = ($isSelect)?'Slave':'Master';
                echo '<h1>'.  $rr .' used</h1>';
                $this->pdo = $this->pdoList[$isSelect];
            }
        }
        return parent::query($query, $param);
    }
}
