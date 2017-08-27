<?php

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

    public function __construct(DB $connect)
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
    	$this->where->appendWhere(Statement::TYPE['and'],func_get_args());
    	return $this;
    }

    public function orWhere()
    {
    	$this->initWhere();
    	$this->where->appendWhere(Statement::TYPE['or'],func_get_args());
    	return $this;
    }

    public function whereSelect(Closure $select)
    {
    	$this->initWhere();

    }

    public function orWhereSelect()
    {

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

    protected function selectExecute()
    {
    	$this->sql = 'select ';
    	if (count($this->columns) === 0) {
    		$this->sql .= '*';
    	} else {
    		$this->sql .= implode(',', $this->columns)
    	}

    	$this->sql .= $this->tableName;
    	list($sql,$params) = $this->where->parse();
    	$this->sql .= $sql;
    	$this->params = array_merge($this->params,$params);
    }

    public function one()
    {
    	$this->sql .= 'limit 1;';
    	return $this->connect->execute($this->sql,$this->params);
    }
}