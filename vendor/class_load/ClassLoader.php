<?php

	require 'Path.php';

	const DIRECTORY_SEPAPATOR = '/';

	spl_autoload_register(function($className){
		$namespace = '';
		$fileName = '';
		$className = ltrim($className,'\\');

		$lastPosition = strrpos($className, '\\');
		
		if ($lastPosition) {
			$namespace = substr($className, 0,$lastPosition);
			$className = substr($className, $lastPosition + 1);
			$fileName = str_replace('\\', DIRECTORY_SEPAPATOR, $namespace) . DIRECTORY_SEPAPATOR;
		}
		
		$fileName .= str_replace('_', DIRECTORY_SEPAPATOR, $className) . '.php';

		$fileName = str_replace('\\', DIRECTORY_SEPAPATOR, base_path($fileName));

		require_once $fileName;
	});
