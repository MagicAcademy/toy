<?php
	
	require '../vendor/autoload.php';
	use container\IOC;

	interface A{

	}

	class B implements A{

	}

	class C {

		public function __construct(A $a){
			var_dump($a);
		}

	}

	class D {

		public $d = null;

		public function __construct(){
			var_dump($this->d);
		}

	}

	function test(){
		echo 'hello world';
	}

	$ioc = IOC::getInstance();
	$ioc2 = clone $ioc;

	var_dump($ioc2 === $ioc,$ioc,$ioc2);

	$ioc->bind('A',B::class);
	$ioc->bind('C',C::class);
	$ioc->make('C');


	$ioc->bind('test1',function(){
		echo 'test1';
	});
	$ioc->make('test1');

	$ioc->setSingle('D',D::class);
	$d = $ioc->make('D');
	$d->d = 123;
	$d = $ioc->make('D');
	var_dump($d->d);

	// warn
	// IOC::bind('test',test);
	// IOC::make('test');
	// IOC::bind('123',function(){
	// 		var_dump(123);
	// });
	// IOC::make('123');
