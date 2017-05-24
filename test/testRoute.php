<?php
	
	require '../vendor/route/Route.php';
	require '../vendor/route/RouteCollection.php';

	use vendor\route\Route;

	$route = new Route();
	
	$route->get('/get',function(){
		var_dump('get');
	});
	
	$route->get('/get/@number@',function(){
		var_dump('/get/@number@');
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

	var_dump($r);