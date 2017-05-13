<?php
	
	require '../vendor/request/Route.php';
	require '../vendor/request/RequestException.php';

	use vendor\request\Route;
	use vendor\request\RequestException;

	$route = new Route();
	
	$route->get('/get',function(){
		var_dump('get');
	});

	$route->get('/get/@more@',function(){
		var_dump('/get/@more@');
	});

	$route->get('/get/test',function(){
		var_dump('get/test');
	});

	$route->post('post',function(){
		var_dump('post');
	});

	$r = $route->match();

	if(!is_null($r)){
		$r();
	}