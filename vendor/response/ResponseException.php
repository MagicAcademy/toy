<?php

	namespace vendor\Response;

	use \Exception;
	use \Throwable;

	class ResponseException extends Exception{

		public function __construct($message = "" , $code = 0 , Throwable $previous = NULL ){
			parent::__construct($message,$code,$previous);
		}

	}