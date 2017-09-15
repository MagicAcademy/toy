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

    /**
     * 
     **/
    protected $needExcute = true;

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
        $this->where->appendWhereSelect(Where::$TYPE['and select'],$column,$notation,$select);
        return $this;
    }

    public function orWhereSelect(strint $column,string $notation,Closure $select)
    {
        $this->initWhere();
        $this->where->appendWhereSelect(Where::$TYPE['or select'],$column,$notation,$select);
        return $this;
    }

    public function whereBetween(string $column,array $between)
    {
        $this->initWhere();
        $this->where->appendWhereBetween(Where::$TYPE['and between'],$column,$between);
        return $this;
    }

    public function orWhereBetween(string $column,array $between)
    {
        $this->initWhere();
        $this->where->appendWhereBetween(Where::$TYPE['or between'],$column,$between);
        return $this;
    }

    public function join()
    {

    }

    public function select()
    {
        $this->columns = array_merge($this->columns,func_get_args());
    }

    public function execSelectSqlStatement()
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
        return $this->sql;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function one()
    {
        $this->execSelectSqlStatement();
        $this->sql .= 'limit 1;';
        return $this->connect->executeOne($this->sql,$this->params);
    }

    public function all()
    {
        $this->execSelectSqlStatement();
        $this->sql .= ';';
        return $this->connect->executeAll($this->sql,$this->params);
    }
}