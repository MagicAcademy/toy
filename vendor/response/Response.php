<?php

	namespace vendor\response;

	class Response{

		private $response = '';

		public function __construct(){
			ob_start();
		}

		public function __destruct(){
			ob_end_flush();
		}

		public function statusCode($status_code = 200){
			http_response_code($status_code);
			return $this;
		}

		public function json($s){
			header('Content-type: application/json');
			$this->reposonse = json_encode($s);
		}

	}