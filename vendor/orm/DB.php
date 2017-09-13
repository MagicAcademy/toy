<?php declare(strict_types=1);
    
namespace orm;

use \PDO;
use \PDOStatement;
use \PDOException;
use \Closure;
use orm\Select;
use orm\Statement;

class DB{

    const INFO_OPTION = [
        'last' => 1,
        'first' => 2,
        'all' => 3
    ];

    public static $instance = null;

    protected $config = [];

    protected $connect = null;

    protected $sqlMethod = [];

    protected $statement = null;

    protected $queryInfo = [];

    protected $dsn = [];

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

                $this->dsn = $this->build($this->config['type']);

                $this->dsn->setOption($this->config);

                $dsn = $this->dsn->getDsn();

                $this->connect = new PDO($dsn,$this->config['username'],$this->config['password']);
            }
        }

        return $this;
    }

    protected function build(string $name)
    {
        $dsn = 'orm\dsn\\' .ucfirst(strtolower(trim($name)));
        return new $dsn();
    }

    public function table(string $tableName)
    {
        $this->statement = new Statement();
        $this->statement->setConnect($this);
        return $this->statement->table($tableName);
    }

    public function executeOne(string $sql,array $params)
    {
        $statement = $this->execute($sql,$params);
        return $statement->fetch();
    }

    public function executeAll(string $sql,array $params): array
    {
        $statement = $this->execute($sql,$params);
        return $statement->fetchAll(PDO::FETCH_CLASS,'stdclass');
    }

    protected function execute(string $sql,array $params): PDOStatement
    {
        $statement = $this->connect->prepare($sql);
        $statement->execute($params);
        $this->queryInfo[] = [
                                'statement' => $sql,
                                'params' => $params
                            ];
        return $statement;
    }

    public function queryInfoLog(int $type = self::INFO_OPTION['all'])
    {
        switch ($type) {
            case self::INFO_OPTION['all']:
                return $this->queryInfo;
            case self::INFO_OPTION['last']:
                if ($tmp = end($this->queryInfo) !== false) {
                    return $tmp;
                }
                return [];
            
            default:
                throw new Exception($type . ' is error.please select DB::INFO_OPTION[\'all\'] or DB::INFO_OPTION[\'last\'] or DB::INFO_OPTION[\'first\']');
        }
    }
}