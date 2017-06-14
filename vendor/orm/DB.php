<?php
	
	namespace vendor\orm;

	use \PDO;
	use \PDOException;
	use \Closure;
	use vendor\orm\Select;

	class DB{

		public static $instance = null;

		protected $config = [];

		protected $connect = null;

		protected $sqlMethod = [];

		public function __clone(){}

		public function __construct(){}

		public static function getInstance(){
			if( is_null(self::$instance) ){
				self::$instance = new self;
			}
			return self::$instance;
		}

		public function init($config = []){
			if( is_null($this->connect) ){
				if( isset($config['database']) ){
					$this->config = $config['database'];

					$dsn = sprintf(
									'%s:dbname=%s;host=%s;port=%d;charset=%s',
									$this->config['type'],
									$this->config['dbname'],
									$this->config['host'],
									$this->config['port'],
									$this->config['charset']
								);

					$this->connect = new PDO($dsn,$this->config['username'],$this->config['password']);

				}
			}

			return $this;
		}

		public function bind($methodName,Closure $initFunction){
			$this->sqlMethod[$methodName] = $initFunction($this->connect);
		}

		protected function defaultMethod(){
			$this->bind('select',function($connect){
				return function($args)use($connect){
					$select = new Select($connect);
					return $select->select($args);
				};
			});
		}

		public function __call($name,$args = []){
			return $this->sqlMethod[$name]($args);
		}
	}