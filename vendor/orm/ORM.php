<?php
	
	namespace vendor\orm;

	use \PDO;
	use \PDOException;
	use \PDOStatement;

	class ORM{

		public static $instance = null;

		protected $config = [];

		protected $connect = null;

		protected $sql = '';

		public function __clone(){}

		public function __construct(){}

		public static function getInstance(){
			if( is_null(self::$instance) ){
				self::$instance = new self;
			}
			return self::$instance;
		}

		public function init($config = []){
			if( isset($config['database']) ){
				$this->config = $config['database'];

				$dsn = sprintf(
								'%s:dbname=%s;host=%s;port=%d',
								$this->config['type'],
								$this->config['dbname'],
								$this->config['host'],
								$this->config['port']
							);

				$this->connect = new PDO($dsn,$this->config['username'],$this->config['password']);
			}
		}
	}