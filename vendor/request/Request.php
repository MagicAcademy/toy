<?php
	
	namespace vendor\request;

	use vendor\request\RequestException;
	use vendor\request\Input;

	class Request{

		private $server = [];

		private $route = null;

		private $input = null;

		public function __construct(Route $route,Input $input){
			$this->server = $_SERVER;
			$this->route = $route;
			$this->input = $input;
		}

		public function route(){
			return $this->route;
		}

		public function input(){
			return $this->input;
		}
	}