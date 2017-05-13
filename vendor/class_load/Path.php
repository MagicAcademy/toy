<?php
	
	define('BASE_PATH', dirname(dirname(__dir__)));

	function base_path($path = ''){
		return BASE_PATH . '/' . ltrim($path,'/');
	}