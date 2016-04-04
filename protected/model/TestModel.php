<?php	defined('ROOKIE') or die('No direct script access.');
/**
 * 日志数据模型
 * @author shendegang
 *
 */
class TestModel extends Rookie_Db
{
	public $id;
	public $user_name;
	
	public $_table = 'test';
    public $_primarykey = 'id';
    public $_fields = array('id','user_name');
	
    
    public function test()
    {
    	echo 'aaaaaaaa';
    }
    
    public function test1()
    {
    	
    	return (Rookie_Core::app('testDb')->table('Test')->find(array('where' => '1=1')));
    	
    //	$res = $this->find(array('where' => '1=1'));
    	//print_r($res);
//     	//测试主重数据库
//     	$this->insertAttributes(array('user_name' => 'test1'));
//     	$this->insertAttributes(array('user_name' => 'test2'));
//     	$this->insertAttributes(array('user_name' => 'test3'));
//     	$this->insertAttributes(array('user_name' => 'test4'));
//     	$this->find(array('where' => '1=1'));
    }
}

