<?php

	require '../vendor/config/Config.php';

	use vendor\config\Config;

	$config = new Config('../app/config/');

	var_dump($config);