<?php
	
	namespace vendor\route;

	use \Closure;

	class RouteCollection {

		private $_before = null;

		private $_after = null;

		private $_current = null;

		public function __construct($current){
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
		}

		public function after($middleware){
			$this->after = $this->build($middleware);
		}

		public function done(){
			return call_user_func($this->_after,call_user_func($this->_current,call_user_func($this->_before)));
		}

	}