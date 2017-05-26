<?php

	require '../vendor/config/Config.php';

	use vendor\config\Config;

	$config = new Config('../app/config/');

	$config->iter();

	var_dump($config);