<?php defined('ROOKIE') or die('No direct script access.');
class TestController extends CController
{
	public static $layout = 'main';
	
	public function index()
	{
		$res = Rookie_Core::app('db')->table('Test')->test1();
		$data = array(
			'test' => array('tt'=>'aaa','daf'=>'aaaaaaaa在'),
			'b' => 'ssss',
			'info' => $res	
		);
		
		//Rookie_Core::loadSys('Test');
		Test::a();
		Rookie_Template::view('default/index', $data);
	}
}