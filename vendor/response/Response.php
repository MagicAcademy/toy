<?php

	namespace vendor\response;

	class Response{

		public function statusCode($status_code = 200){

			return $this;
		}

		public function json($s){
			json_encode($s);
		}

	}