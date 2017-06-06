<?php
	
	namespace bootstrap\core;

	use vendor\container\IOC;
	use vendor\config\Config;
	use vendor\exception\ResponseException;
	use vendor\response\Response;
	use vendor\route\Route;
	use \Exception;
	use vendor\exception\NotFoundException;

	$ioc = IOC::getInstance();
	$ioc->setSingle('Config',Config::class);
	$ioc->bind('ResponseException',ResponseException::class);
	$ioc->setSingle('ResponseInterface',Response::class);
	$ioc->setSingle('Route',Route::class);
	$ioc->bind('NotFoundInterface',NotFoundException::class);

	$config = $ioc->make('Config');
	$config->setPath('app/config');
	$config->iter();
	
	$response = $ioc->make('ResponseInterface');
	$response->init();
	try{
		$route = $ioc->make('Route');
		$route->setConfig($config);
		require 'app/route.php';
		$route->match();
	}catch(NotFoundException $e){
		$response->statusCode(404);
		$response->error($e);
	}catch(Exception $e){
		$response->statusCode(500);
		$response->error($e);
	}finally{
		$response->complete();
	}