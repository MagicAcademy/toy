<?php
	
	namespace vendor\route;

	use \Closure;
	use vendor\container\IOC;

	class RouteCollection {

		private $_before = null;

		private $_after = null;

		private $_current = null;

		private $ioc = null;

		public function __construct($current,IOC $ioc = null){
			$this->ioc = $ioc;
			$this->_before = $this->defaultInit();
			$this->_current = $current;
			$this->_after = $this->defaultInit();
		}

		private function defaultInit(){
			return function($parameter = null){
				return $parameter;
			};
		}

		private function build($function){
			if( !($function instanceof Closure) ){
				return function($parameter = null){
					return $parameter;
				};
			}else{
				return $function;
			}
		}

		public function before($middleware){
			$this->_before = $this->build($middleware);
			return $this;
		}

		public function after($middleware){
			$this->_after = $this->build($middleware);
			return $this;
		}

		public function done(){
			return call_user_func($this->_after,call_user_func($this->_current,call_user_func($this->_before,$this->ioc)));
		}

	}