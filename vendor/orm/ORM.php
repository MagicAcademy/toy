<?php declare(strict_types=1);
	
namespace orm;

use orm\DB;
use orm\Statement;
use \ReflectionClass;
use orm\exception\ORMException;

class ORM{

    protected $tableName = '';

    protected $alias = '';

    protected $connect = null;

    protected $statement = null;

    protected $isFind = false;

    protected $columns = [];

    protected $relations = [];

    protected $isLazy = false;

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

    public function find()
    {
        $this->getTable();
        $this->statement = $this->connect->table($this->tableName);
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

    protected function belongTo(string $anotherORMName,array $columns)
    {
        $this->relations[$anotherORMName] = [
            'columns' => $columns
        ];
    }

    public function lazyRelation()
    {
        $this->isLazy = true;
    }

    public function __set(string $name,$value)
    {
        $this->columns[$name] = $value;
    }

    public function __call(string $name,array $args)
    {
        if ( ($position = $strpos($name,'has',0)) !== 0 ) {
            throw new ORMException();
        }

        $relationClassName = substr('string', 0, 3);
        $reflectionClass = new ReflectionClass($relationClass);
        $reflectionMethod = $reflectionClass->getMethod('belongTo' . static::class);
    }
}