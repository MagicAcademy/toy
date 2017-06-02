<?php
	
	namespace bootstrap\core;

	use vendor\container\IOC;
	use vendor\config\Config;
	use vendor\exception\ResponseException;
	use vendor\response\Response;
	use vendor\route\Route;
	use \Exception;
	use vendor\exception\NotFoundException;

	IOC::setSingle('Config',Config::class);
	IOC::bind('ResponseException',ResponseException::class);
	IOC::setSingle('ResponseInterface',Response::class);
	IOC::setSingle('Route',Route::class);
	IOC::bind('NotFoundInterface',NotFoundException::class);
	

	$response = IOC::make('ResponseInterface');
	$response->init();
	try{
		$route = IOC::make('Route');
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