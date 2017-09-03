<?php
	
	require '../vendor/autoload.php';

	use orm\DB;
	// use vendor\orm\Select;

	$orm = DB::getInstance();


	$statment = $orm->init([
				'database' => [
						'type' => 'mysql',
						'host' => 'localhost',
						'port' => 3306,
						'username' => 'root',
						'password' => '',
						'dbname' => 'shop',
						'charset' => 'utf8'
					]
				]);
	
	var_dump(
		$statment->table('goods')
			->where('id',1)
			->one()
		);

	var_dump(
		$statment->table('goods')
				->where(function($where){
					$where->where('id',1)
						->orWhere('id',2);
				})
				->one()
		);