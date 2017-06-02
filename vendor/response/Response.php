<?php

	namespace vendor\response;

	use toyInterfaces\ResponseInterface;
	use \Exception;

	class Response implements ResponseInterface{

		private $response = '';

		private $exception = null;

		private $config = [];

		public function init(){
			ob_start();
		}

		public function statusCode($status_code = 200){
			http_response_code($status_code);
			return $this;
		}

		public function json($s){
			try{
				header('Content-Type: application/json; charset=utf-8');
				$this->response = json_encode($s,JSON_ERROR_UTF8);
				$this->statusCode(200);
				return $this;
			}catch(Exception $e){
				$this->exception = $e;
				return $this->error($e);
			}
		}

		public function string($s){
			try{
				header('Content-Type: text/html');
				$this->response = $s;
			}catch(Exception $e){
				$this->exception = $e;
				return $this->error($e);
			}
		}

		public function make($s){
			try{
				if(is_array($s) || is_object($s)){
					return $this->json($s);
				}
				return $this->string($s);
			}catch(Exception $e){
				return $this->error($e);
			}
		}

		public function complete(){
			echo $this->response;
			ob_end_flush();
		}

		public function error(Exception $e){
			header('Content-Type: text/html; charset=utf-8');
			$this->response = $e;
		}
	}