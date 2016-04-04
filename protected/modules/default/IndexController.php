<?php defined('ROOKIE') or die('No direct script access.');
/**
 * 首页
 * @author shendegang
 *
 */
class IndexController extends controller 
{
	public function index()
	{
		
		//Rookie_Core::app()->cache()->set('');
		//var_dump(Rookie_Core::app('cacheFile')->set('shen','tttbbbbbbbbttttttt'));
		//echo Rookie_Core::app('cacheFile')->get('shen');
	
    		//	$rs->ping('127.0.0.1:6379');
		
		//var_dump(Rookie_Core::app('cacheMem')->set('sh33在33ena','aaa'));
// 		echo Rookie_Core::app('cacheMem')->get('sh33在33ena');
		//var_dump( Rookie_Core::app('cacheMem')->get('shen') );
		//echo 'aa大a';
		echo 1/0;
		
		var_dump(Rookie_Core::app('cacheFileArray')->set('aaa', array('sss','ddd')));
		$a = Rookie_Core::app('cacheFileArray')->get('aaa');
		//print_r($a);
		
// 		include_once 'model/TestModel.php';
// 		$TestModel = new TestModel();
// 		$rs = $TestModel->find(array('where' => '1=1'));
// 		print_r($rs);
		//print_r(Rookie_Core::app('testDb')->table('Test')->find(array('where' => '1=1')));
		//print_r(Rookie_Core::app('db')->table('Test')->test());
		
		$res = Rookie_Core::app('db')->table('Test')->test1();
		//print_r($res);
		//include Rookie_Template::tpl("index");
	}
}