<?php declare(strict_types=1);

namespace orm;

use orm\DB;
use orm\Where;
use \Closure;
use orm\Join;

class Statement
{

    protected $sql = '';

    protected $tableName = '';

    protected $join = null;

    protected $where = null;

    protected $columns = [];

    protected $connect = null;

    protected $params = [];

    /**
     * 是否需要执行sql语句
     **/
    protected $whetherExcute = true;

    /**
     * 这个方法的作用是在使用子查询语句中使用all或者get等生成sql语句,防止重复合并参数
     * @AuthorHTL
     * @DateTime  2017-09-15T22:15:19+0800
     */
    public function preventExcute()
    {
        $this->whetherExcute = false;
    }

    /**
     * 设置连接
     * @AuthorHTL
     * @DateTime  2017-09-15T23:28:48+0800
     * @param     DB                       $connect [description]
     */
    public function setConnect(DB $connect)
    {
        $this->connect = $connect;
    }

    /**
     * 创建一个Where对象
     * @AuthorHTL
     * @DateTime  2017-09-15T23:40:34+0800
     */
    protected function initWhere()
    {
        if (!($this->where instanceof Where)) {
            $this->where = new Where();
        }
    }

    /**
     * 创建一个Join对象
     * @AuthorHTL
     * @DateTime  2017-09-15T23:49:37+0800
     */
    protected function initJoin()
    {
        if (!($this->join instanceof Join)) {
            $this->join = new Join();
        }
    }

    /**
     * 设置table
     * @AuthorHTL
     * @DateTime  2017-09-15T23:43:05+0800
     * @param     string                   $tableName [description]
     * @return    [type]                              [description]
     */
    public function table(string $tableName): Statement
    {
        $this->tableName = $tableName;
        return $this;
    }

    /**
     * where语句
     * 这个where方法的参数是可变长度的
     * 
     * 当长度为0时,抛出异常
     * @throws DBStatementException param count must be large than 0
     * 
     * 当长度为1时,参数应该为回调函数,函数的参数类型为Where,使用方法为
     *
     *      $select->where(function(Where $where){
     *          $where->where('column',1)
     *                  ->orWhere('column',2)
     *      })
     * 相当于sql语句中的
     *     where ( column = 1 or column = 2 )
     * 
     * 回调函数中的Where会调用调用跟Select相同名称的方法
     * 当传进来的不是一个回调函数时,会抛出异常
     * @throws DBStatementException param must be a Closure type when param count is 1
     *
     * 当长度为2时,其作用与sql语句作用相同
     *
     *      where column = 'xxx' => where('column','xxx')
     * 
     * 第一个参数应为字符串的字段,否则会抛出异常
     * @throws DBStatementException column must be a string type
     *
     * 当长度超过或等于3时,第一个参数为字符串的字段名,第二个参数为字符串的sql符号,第三个为sql语句的参数
     *
     *      where column > 1 => where('column','>',1)
     *
     * 第一个参数应为字符串类型,否则会抛出异常
     * @throws DBStatement param must be a Closure type when param count is 1
     * 第二个参数应为sql符号,否则会抛出异常
     * @throws  DBStatement 
     *
     * 注意,多个where或者orWhere同时使用效果为
     *
     *      where('column',1)->orWhere('column',2)->where('type',1) => where column = 1 or column = 2 and type = 1
     * 
     * @AuthorHTL
     * @DateTime  2017-09-15T23:51:35+0800
     * @return    [type]                   [description]
     */
    public function where(): Statement
    {
        $this->initWhere();
        $this->where->appendWhere(Where::$TYPE['and'],func_get_args());
        return $this;
    }

    public function orWhere(): Statement
    {
        $this->initWhere();
        $this->where->appendWhere(Where::$TYPE['or'],func_get_args());
        return $this;
    }

    public function whereIn(string $column,$params): Statement
    {
        $this->initWhere();
        $this->where->appendWhereIn(Where::$TYPE['and in'],$column,$params);
        return $this;
    }

    public function orWhereIn(string $column,$params): Statement
    {
        $this->initWhere();
        $this->where->appendWhereIn(Where::$TYPE['or in'],$column,$params);
        return $this;
    }

    public function whereSelect(string $column,string $notation,Closure $select): Statement
    {
        $this->initWhere();
        $this->where->appendWhereSelect(Where::$TYPE['and select'],$column,$notation,$select);
        return $this;
    }

    public function orWhereSelect(strint $column,string $notation,Closure $select): Statement
    {
        $this->initWhere();
        $this->where->appendWhereSelect(Where::$TYPE['or select'],$column,$notation,$select);
        return $this;
    }

    public function whereBetween(string $column,array $between): Statement
    {
        $this->initWhere();
        $this->where->appendWhereBetween(Where::$TYPE['and between'],$column,$between);
        return $this;
    }

    public function orWhereBetween(string $column,array $between): Statement
    {
        $this->initWhere();
        $this->where->appendWhereBetween(Where::$TYPE['or between'],$column,$between);
        return $this;
    }

    public function join(string $table,$condition): Statement
    {
        $this->initJoin();
        $this->join->appendJoin(Join::$TYPE['join'],$table,$condition);
        return $this;
    }

    public function leftJoin(string $table,$condition): Statement
    {
        $this->initJoin();
        $this->join->appendJoin(Join::$TYPE['left join'],$table,$condition);
    }

    public function rightJoin(string $table,$condition): Statement
    {
        $this->initJoin();
        $this->join->appendJoin(Join::$TYPE['right join'],$table,$condition);
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

        if (!is_null($this->join)) {
            $this->sql .= ' ' . trim($this->join->parse());
        }

        if (!is_null($this->where)) {
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

    public function count(string $column = '*',bool $isDistinct = false)
    {
        if ($this->whetherExcute) {
            $this->columns = ['count( ' . ($isDistinct?'distinct ':'') . $column . ' )'];
            $this->execSelectSqlStatement();
            return $this->connect->executeColumn($this->sql,$this->params,0);
        }
    }

    public function one()
    {
        if ($this->whetherExcute) {
            $this->execSelectSqlStatement();
            $this->sql .= 'limit 1;';
            return $this->connect->executeOne($this->sql,$this->params);
        }
    }

    public function all()
    {
        if ($this->whetherExcute) {
            $this->execSelectSqlStatement();
            $this->sql .= ';';
            return $this->connect->executeAll($this->sql,$this->params);
        }
    }
}