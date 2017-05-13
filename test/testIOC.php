<?php
	
	// namespace test\IOC;

	// require '../../vendor/class_load/ClassLoader.php';

	require '../vendor/container/IOC.php';
	use vendor\container\IOC;

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

	IOC::bind('A',B::class);
	IOC::bind('C',C::class);
	IOC::make('C');


	IOC::bind('test1',function(){
		echo 'test1';
	});
	IOC::make('test1');

	IOC::setSingle('D',D::class);
	$d = IOC::make('D');
	$d->d = 123;
	$d = IOC::make('D');
	var_dump($d->d);

	// warn
	// IOC::bind('test',test);
	// IOC::make('test');
	// IOC::bind('123',function(){
	// 		var_dump(123);
	// });
	// IOC::make('123');
