<?php
	
	namespace vendor\request;

	use vendor\request\RequestException;
	use vendor\request\Input;

	class Request{

		private $server = [];

		private $input = null;

		public function __construct(,Input $input){
			$this->server = $_SERVER;
			$this->input = $input;
		}

		public function route(){
			return $this->route;
		}

		public function input(){
			return $this->input;
		}
	}