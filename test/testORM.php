<?php
	
	require '../vendor/autoload.php';

	use orm\DB;
	// use vendor\orm\Select;

	$orm = DB::getInstance();



	// $statment = $orm->init([
	// 			'database' => [
	// 					'type' => 'mysql',
	// 					'host' => 'localhost',
	// 					'port' => 3306,
	// 					'username' => 'root',
	// 					'password' => '',
	// 					'dataBaseName' => 'shop',
	// 					'charset' => 'utf8'
	// 				]
	// 			]);

	$statment = $orm->init([
				'database' => [
						'type' => 'pgsql',
						'host' => 'localhost',
						'port' => 5432,
						'username' => 'root',
						'password' => '123456',
						'dataBaseName' => 'shop',
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

	var_dump(
		$statment->table('goods')
				->whereIn('id',[1,2])
				->all()
		);


	// var_dump($orm->queryInfoLog());

	// var_dump(
	// 	$statment->table('goods')
	// 			->whereIn('id',function($select){
	// 				$select->table('goods')
	// 						->select('id');
	// 			})
	// 			->all()
	// 	);

	var_dump(
		$statment->table('goods')
				->where('product_name','')
				->orWhereIn('id',function($select){
					$select->table('goods')
							->where('id',1);
				})
				->all()
	);

	var_dump(
		$statment->table('goods')
				->whereSelect('id','>',function($select){
					$select->table('goods')
							->where('id',1)
							->select('id');
				})
				->all()
	);

	var_dump(
		$statment->table('goods')
					->whereBetween('id',[1,3])
					->all()
	);

	var_dump(
		$statment->table('goods')
				->where(function($where){
					$where->where('id',1)
						->orWhereBetween('id',[1,2])
						->whereSelect('id','=',function($select){
							$select->table('goods')
									->where('id',1)
									->select('id');
						});
				})
				->all()
	);

	var_dump(
		$statment->table('goods as g')
				->join('goods_color as gc','g.id = gc.id')
				->all()
	);

	var_dump(
		$statment->table('goods as g')
				->join('goods_color as gc',function($join){
					$join->and('g.id = gc.id');
				})
				->all()
	);

	var_dump(
		$statment->table('goods')
				->count()
	);

	var_dump(
		$statment->table('goods')
				->count('id',true)
	);

	// var_dump(
	// 	$statment->table('goods')
	// 				->insert([
	// 						'id' => 3,
	// 						'product_name' => 'hello world'
	// 						])
	// );
	
	var_dump(
		$statment->table('goods')
				->insert([
					'id' => 4
				])
	);

	var_dump(
		$statment->table('goods')
				->insert([
					['id' => 5],
					['id' => 6]
				])
	);

	var_dump(
		$statment->table('goods')
				->where('id',4)
				->delete()
	);

	// var_dump(
	// 	$orm->queryInfoLog()
	// 	);

