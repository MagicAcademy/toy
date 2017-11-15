<?php declare(strict_types=1);
	
namespace orm;

use orm\DB;
use orm\Statement;
use \ReflectionClass;
use \ReflectionMethod;
use orm\exception\ORMException;
use \stdClass;

class ORM{

    const RELATIONS = [
        'belongTo' => 0,
        'hasOne' => 1,
        'hasMany' => 2
    ];

    const RELATION_TYPE = [
        'hasOne',
        'hasMany',
        'belongTo'
    ];

    protected $primaryKey = '';

    protected $tableName = '';

    protected $alias = '';

    protected $connect = null;

    protected $statement = null;

    protected $isFind = false;

    protected $columns = [];

    protected $relations = [];

    protected $isLazy = false;

    protected $rows = [];

    public function __construct()
    {
        
    }

    public function getPrimaryKey(): string
    {
        $info = $this->connect->table('INFORMATION_SCHEMA.KEY_COLUMN_USAGE')
                            ->where('TABLE_NAME',$this->getTable())
                            ->one();

        if ($info) {
            $this->primaryKey = isset($info['CONSTRAINT_NAME']) && $info['CONSTRAINT_NAME'] === 'PRIMARY'?$info['COLUMN_NAME']:'';
        }

        return $this->primaryKey;
    }

    public function setDB(DB $connect)
    {
        $this->connect = $connect;
    }

    // protected function setTable()
    // {
    //     $this->tableName = strtolower(static::class);
    // }

    public function getTable(): string
    {
        return $this->tableName;
    }

    public function getAlias(): string
    {
        return $this->alias;
    }

    protected function hasOne(string $className,string $otherKey = '',string $selfKey = '')
    {
        return [
            'className' => $className,
            'relation' => self::RELATIONS['hasOne'],
            'otherKey' => $otherKey,
            'selfKey' => $selfKey
        ];
    }

    protected function hasMany(string $className,string $otherKey = '',string $selfKey = '')
    {
        return [
            'className' => $className,
            'relation' => self::RELATIONS['hasMany'],
            'otherKey' => $otherKey,
            'selfKey' => $selfKey
        ];
    }

    protected function belongTo(string $className,string $otherKey = '',string $selfKey = '')
    {
        return [
            'className' => $className,
            'relation' => self::RELATIONS['belongTo'],
            'otherKey' => $otherKey,
            'selfKey' => $selfKey
        ];
    }

    public function find()
    {
        $this->statement = $this->connect->init()->table($this->getTable());
        $this->isFind = true;
        return $this;
    }

    public function one()
    {
        if ($this->isFind === false) {
            $this->find();
        }

        $rows = $this->statement->one();

        $this->rows = $rows;

        return $rows;
    }

    public function all()
    {
        if ($this->isFind === false) {
            $this->find();
        }

        $rows = $this->statement->all();

        $this->rows = $rows;

        return $rows;
    }

    public function where()
    {

        return $this;
    }

    protected function beforeInsert()
    {

    }

    protected function beforeUpdate()
    {

    }

    protected function beforeSave()
    {

    }

    public function save()
    {
        if ($this->isFind) {
            $this->beforeUpdate();
            $this->beforeSave();
            $this->statement->update($this->columns);
        } else {
            $this->beforeInsert();
            $this->beforeSave();
            $this->statement->insert($this->columns);
        }
        
    }

    public function __get(string $name)
    {
        if (!array_key_exists($name, $this->relations)) {
            throw new ORMException($name . ' not relationship with this');
        }

        $relation = $this->$name();

        $class = new $relation['className']();
        if (!($class instanceof ORM)) {
            throw new ORMException($relation['className'] . ' must type of ORM');
        }

        $class->setDB($this->connect);
        $statement = $class->find()->table($class->getTable());
        
        $classColumn = $relation['otherKey'];
        $locationColumn = $relation['selfKey'];

        if ($classColumn === '') {
            $classColumn = $class->getPrimaryKey();
        }

        if ($locationColumn === '') {
            $locationColumn = $this->getPrimaryKey();
        }

        if ($relation['relation'] & (self::RELATIONS['hasOne'] | self::RELATIONS['belongTo'])  ) {
            
        }

        return $statement;
    }

    public function __set(string $name,$value)
    {
        $this->columns[$name] = $value;
    }

    public function __call(string $name,array $args = [])
    {
        if ($this->isFind === false) {
            $this->find();
        }

        $reflectionMethod = new ReflectionMethod($this->statement,$name);
        $reflectionMethod->invokeArgs($this->statement,$args);

        return $this;
    }
}