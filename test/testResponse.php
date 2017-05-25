<?php

	require '../interface/ResponseInterface.php';
	require '../vendor/response/Response.php';
	require '../vendor/response/ResponseException.php';

	use vendor\response\Response;
	use vendor\response\ResponseException;

	$response = new Response(new ResponseException());

	$response->init();

	$response->json(123);

	$response->json('123');

	$response->string(123);

	$response->complete();