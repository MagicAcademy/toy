<?php

	require '../vendor/request/Input.php';

	use vendor\request\Input;

	$_GET['target'] = '<script type="text/javascript">alert(1);</script>';

	$input = new Input();

	var_dump($input->all());

	echo $input->get('target');