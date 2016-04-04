<?php
$route['*']['/'] = array('IndexController', 'index', 'm' => 'default');
$route['*']['/manage.do'] = array('TestController', 'index', 'm' => 'default');
?>