<?php

	namespace vendor\request;

	use \Exception;
	use \Throwable;

	/**
	* 
	*/
	class RequestException extends Exception{
		
		protected $message = '';

		protected $line = 0;

		protected $code = 0;

		protected $file = '';

		public function __construct($message = '', $code = 0, Throwable $previous = null){
			parent::__construct($message,$code,$previous);
		}
	}