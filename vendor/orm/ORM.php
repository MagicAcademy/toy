<?php declare(strict_types=1);
	
namespace orm;

use orm\DB;
use orm\Statement;

class ORM{

    protected $tableName = '';

    protected $alias = '';

    protected $connect = null;

    protected $statement = null;

    protected $isFind = false;

    protected $columns = [];

    public function __construct(DB $connect)
    {
        $this->connect = $connect;
        $this->tableName = strtolower(static::class);
    }

    protected function table(): string
    {
        return $this->tableName;
    }

    protected function prefix(): string
    {
        return '';
    }

    protected function getTable()
    {
        $this->tableName = $this->prefix() . $this->table();
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

    protected function belongTo(string $anotherORMName,array $condition,string $alias = '')
    {

    }

    protected function hasMany(string $anotherORMName,array $condition,string $alias = '')
    {

    }

    protected function hasOne(string $anotherORMName,array $condition,string $alias = '')
    {

    }

    public function __set(string $name,$value)
    {
        $this->columns[$name] = $value;
    }
}