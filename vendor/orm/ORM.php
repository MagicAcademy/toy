<?php declare(strict_types=1);
	
namespace orm;

use orm\DB;
use orm\Statement;
use \ReflectionClass;
use \ReflectionMethod;
use orm\exception\ORMException;

class ORM{

    const RELATIONS = [
        'belongTo' => 0,
        'hasOne' => 1,
        'hasMany' => 2
    ];

    protected $tableName = '';

    protected $alias = '';

    protected $connect = null;

    protected $statement = null;

    protected $isFind = false;

    protected $columns = [];

    protected $relations = [];

    protected $isLazy = false;

    protected $isJoin = false;

    public function __construct()
    {
        $this->setTable();
    }

    public function setDB(DB $connect)
    {
        $this->connect = $connect;
    }

    protected function setTable()
    {
        $this->tableName = strtolower(static::class);
    }

    public function getTable(): string
    {
        return $this->tableName;
    }

    public function getAlias(): string
    {
        return $this->alias;
    }

    protected function buildBelongTo(string $className)
    {
        $reflection = new ReflectionClass($className);
        $reflectioMethod = $reflection->getMethod('belongTo' . ucfirst($reflection->getShortName()));
        $instance = $reflection->newInstance();
        $reflectionMethod->invoke($instance);
    }

    public function find()
    {
        $this->getTable();
        $this->statement = $this->connect->table($this->tableName);
        
        if ($this->isJoin) {
            foreach ($this->relations as $className => $relations) {
                $this->build($className);
            }
        }

        $this->isFind = true;
        return $this->statement;
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

    protected function belongTo(string $anotherORMName,array $relations)
    {
        $this->relations[$anotherORMName] = [
            'relationPoint' => $relations,
            'relation' => self::RELATIONS['hasOne']
        ];
    }

    protected function has(string $anotherORMName,array $relations)
    {
        $this->relations[$anotherORMName] = [
            'relationPoint' => $relations,
            'relation' => self::RELATIONS['belongTo']
        ];

        $this->isJoin = true;
    }

    public function lazyRelation()
    {
        $this->isLazy = true;
    }

    public function __set(string $name,$value)
    {
        $this->columns[$name] = $value;
    }

}