<?php declare(strict_types=1);

namespace orm;

use orm\DB;
use orm\Where;
use \Closure;

class Statement
{

    protected $sql = '';

    protected $tableName = '';

    protected $join = [];

    protected $where = null;

    protected $columns = [];

    protected $connect = null;

    protected $params = [];

    public function setConnect(DB $connect)
    {
        $this->connect = $connect;
    }

    protected function initWhere()
    {
        if (!($this->where instanceof Where)) {
            $this->where = new Where();
        }
    }

    public function table(string $tableName)
    {
        $this->tableName = $tableName;
        return $this;
    }

    public function where()
    {
        $this->initWhere();
        $this->where->appendWhere(Where::$TYPE['and'],func_get_args());
        return $this;
    }

    public function orWhere()
    {
        $this->initWhere();
        $this->where->appendWhere(Where::$TYPE['or'],func_get_args());
        return $this;
    }

    public function whereIn(string $column,$params)
    {
        $this->initWhere();
        $this->where->appendWhereIn(Where::$TYPE['and in'],$column,$params);
        return $this;
    }

    public function orWhereIn(string $column,$params)
    {
        $this->initWhere();
        $this->where->appendWhereIn(Where::$TYPE['or in'],$column,$params);
        return $this;
    }

    public function whereSelect(string $column,string $notation,Closure $select)
    {
        $this->initWhere();
        $this->whereSelect(Where::$TYPE['and select'],$column,$notation,$select);
        return $this;
    }

    public function orWhereSelect(strint $column,string $notation,Closure $select)
    {
        $this->initWhere();
        $this->whereSelect(Where::$TYPE['or select'],$column,$notation,$select);
        return $this;
    }

    public function whereBeteewn()
    {

    }

    public function orWhereBeteewn()
    {

    }

    public function select()
    {
        $this->columns = array_merge($this->columns,func_get_args());
    }

    protected function selectSqlStatement()
    {
        $this->sql = 'select';
        if (count($this->columns) === 0) {
            $this->sql .= ' * ';
        } else {
            $this->sql .= ' ' . implode(',', $this->columns);
        }

        $this->sql .= ' from ' . $this->tableName;
        if (!is_null($this->where)){
            list($sql,$params) = $this->where->parse();
            $this->sql .= ' where ' . ltrim(ltrim($sql,' and'),' or');
            $this->params = array_merge($this->params,$params);
        }
    }

    public function getSqlStatement(): string
    {
        $this->selectSqlStatement();
        return $this->sql;
    }

    public function getParams(): array
    {
        $this->selectSqlStatement();
        return $this->params;
    }

    public function one()
    {
        $this->selectSqlStatement();
        $this->sql .= 'limit 1;';
        return $this->connect->executeOne($this->sql,$this->params);
    }

    public function all()
    {
        $this->selectSqlStatement();
        $this->sql .= ';';
        return $this->connect->executeAll($this->sql,$this->params);
    }
}