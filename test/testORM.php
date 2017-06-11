<?php
	
	require '../vendor/orm/DB.php';
	require '../vendor/orm/Select.php';

	use vendor\orm\DB;
	use vendor\orm\Select;

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
	$orm->bind('select',function($connect){
		return function($parameters)use($connect){
			$select = new Select($connect);
			return $select->select($parameters);
		};
	});

	var_dump(
		$statment->select('product_name','id')
				->table('goods')
				->where('amount',100)
				->get()
		);

	var_dump(
		$statment->select()
				->table('goods')
				->whereIn('id',[1,2])
				->get()
		);

	// 如果orWhere 或者 orWhereIn 在 where 或者 whereIn 之前被使用的话,就会返回Exception
	// var_dump(
	// 	$statment->select()
	// 			->table('goods')
	// 			->orWhere('amount',100)
	// 			->get()
	// 	);

	var_dump(
		$statment->select()->table('goods')
				->one()
		);