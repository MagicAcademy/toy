<?php
    
namespace orm;

use \PDO;
use \PDOStatement;
use \PDOException;
use \Closure;
use orm\Select;
use orm\Statement;

class DB{

    public static $instance = null;

    protected $config = [];

    protected $connect = null;

    protected $sqlMethod = [];

    protected $statement = null;

    public function __clone(){}

    public function __construct(){}

    public static function getInstance()
    {
        if ( is_null(self::$instance) ) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function init($config = [])
    {
        if ( is_null($this->connect) ) {
            if ( isset($config['database']) ) {
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

    public function table(string $tableName)
    {
    	$this->statement = new Statement($this);
    	return $this->statement->table($tableName);
    }

    public function execute(string $sql,array $params)
    {
    	// $statement = $this->connect->prepare($sql);
    	var_dump($sql);
    }
}