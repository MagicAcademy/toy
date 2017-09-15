<?php declare(strict_types=1);

namespace orm;

use \Closure;
use orm\exception\DBStatementException;
use orm\Statement;
use orm\DB;
use \ReflectionMethod;
use \ReflectionParameter;

class Where
{

    protected $whereStatement = '';

    protected $wheres = [];

    protected $params = [];

    const ALLOW_SIGN = [
        '=',
        '<',
        '<=',
        '>',
        '>=',
        '<>',
        '%like',
        'like%',
        '%like%',
        'like',
        '%LIKE',
        'LIKE%',
        'LIKE'
    ];

    public static $TYPE = [
        'and' => 1 << 0,
        'or' => 1 << 1,
        'and in' => 1 << 2,
        'or in' => 1 << 3,
        'and select' => 1 << 4,
        'or select' => 1 << 5,
        'and between' => 1 << 6,
        'or between' => 1 << 7
    ];

    const SPOCE = [
        'where' => 1 | 2,
        'whereIn' => 4 | 8,
        'whereSelect' => 16 | 32,
        'whereBetween' => 64 | 128
    ];

    const MAP = [
        1 << 0 => 'and',
        1 << 1 => 'or',
        1 << 2 => 'and in',
        1 << 3 => 'or in',
        1 << 4 => 'and select',
        1 << 5 => 'or select',
        1 << 6 => 'and between',
        1 << 7 => 'or between'
    ];

    /**
     * [appendWhere description]
     * @AuthorHTL
     * @DateTime  2017-09-10T18:01:17+0800
     * @param     int                   $type 这里的type为and或者or
     * @param     array                    $args 这个参数是按照数组的数量长度决定
     *                                           where的参数的
     *                                           长度为:
     *                                               1. 代表传进来的是一个匿名函数,
     *                                               函数的参数是一个Where类型的对象,
     *                                               作用是表示 where (xx = 1 and XX = 2) 这种嵌套的sql语句
     *                                               2. 代表 where('xx',1) =>  where xx = 1
     *                                               3. 代表 where('xx','>',1) => where xx > 1
     * @throws    DBStatementException     这里会因为$args的长度等于0,或者$args长度为1时,$args[0]的类型不为匿名函数而抛出异常
     */
    public function appendWhere(int $type,array $args)
    {
        $argsCount = count($args);
        $where = [];
        if ($argsCount < 1) {
            throw new Exception('param count must be large than 0');
        } elseif ($argsCount === 1) {
            if (!($args[0] instanceof Closure)) {
                throw new Exception('param must be a Closure type when param count is 1');
            }

            $where = [
                        'isClosure' => true,
                        'closure' => $args[0]
                        ];
        } elseif ($argsCount === 2) {
            $where = [
                        'isClosure' => false,
                        'column' => $args[0],
                        'notation' => '=',
                        'params' => $args[1]
                        ];

        } elseif ($argsCount >= 3) {
            if (!in_array(self::ALLOW_SIGN,$args[1])) {
                throw new Exception($args[1] . ' isn\'t allow to used');
            }
            $where = [
                        'isClosure' => false,
                        'column' => $args[0],
                        'notation' => $args[1],
                        'params' => $args[2]
                        ];
        }
        $where['type'] = $type;
        $this->wheres[] = $where;
    }

    /**
     * [appendWhereIn description]
     * @AuthorHTL
     * @DateTime  2017-09-10T18:48:08+0800
     * @param     int                   $type   这里是and in 或者 or in
     * @param     string                   $column 这个是字段名
     * @param     array || Closure         $params 这里的可以为一个数组或者是一个匿名函数,
     *                                             函数的参数类型为 Statement
     * @throws    DBStatementException     这里会因为$params的类型不为数组或者匿名函数为抛出异常
     */
    public function appendWhereIn(int $type,string $column,$params)
    {
        $isClosure = $params instanceof Closure;
        $isArray = is_array($params);
        if (!$isArray && !$isClosure) {
            throw new DBStatementException($params . ' should be a array or Closure');
        }   
        $where = [
                    'type' => $type,
                    'column' => $column,
                    'isClosure' => $isClosure,
                    'params' => $params
                    ];

        $this->wheres[] = $where;
    }

    public function appendWhereSelect(int $type,string $column,string $notation,Closure $select)
    {
        if (!in_array($notation, self::ALLOW_SIGN)) {
            throw new DBStatementException($notation . ' not a legal notation of sql statement');
        }
        $this->wheres[] = [
            'type' => $type,
            'column' => $column,
            'notation' => $notation,
            'select' => $select
        ];
    }

    public function appendWhereBetween(int $type,string $column,array $params)
    {
        if (count($params) < 2) {
            throw new DBStatementException("the length of third params should be greater than one");
        }
        $this->wheres[] = [
            'type' => $type,
            'column' => $column,
            'params' => array_slice($params, 0, 2)
        ];
    }

    public function parse(): array
    {
        foreach ($this->wheres as $where) {
            if ($where['type'] & self::SPOCE['where']) {
                $this->parseWhere($where);
            } elseif ($where['type'] & self::SPOCE['whereIn']) {
                $this->parseWhereIn($where);
            } elseif ($where['type'] & self::SPOCE['whereSelect']) {
                $this->parseWhereSelect($where);
            } elseif ($where['type'] & self::SPOCE['whereBetween']) {
                $this->parseWhereBetween($where);
            }
        }

        return [$this->whereStatement,$this->params];
    }

    protected function parseWhere(array $where)
    {
        $this->whereStatement .= ' ' . self::MAP[$where['type']];
        if ($where['isClosure']) {
            $this->whereStatement .= ' (';
            $newWhere = new self;
            $where['closure']($newWhere);
            list($statement,$params) = $newWhere->parse();
            $this->whereStatement .= ' ' . ltrim(ltrim($statement,' and'),' or');
            $this->params = array_merge($this->params,$params);
            $this->whereStatement .= ' )';
        } else {
            $this->whereStatement .= ' ' . $where['column'];
            $this->whereStatement .= ' ' . $where['notation'];
            $this->whereStatement .= ' ' . '?';
            $this->params[] = $where['params'];
        }
    }

    protected function parseWhereIn(array $where)
    {
        $type = explode(' ', self::MAP[$where['type']], 2);
        $this->whereStatement .= ' ' . $type[0] . ' ' . $where['column'] . ' ' . $type[1] . ' (';
        if ($where['isClosure']) {
            $statement = new Statement();
            $where['params']($statement);

            $statement->execSelectSqlStatement();
            $this->whereStatement .= $statement->getSqlStatement();
            $this->params = array_merge($this->params,$statement->getParams());
        } else {
            $this->whereStatement .= implode(',', array_fill(0, count($where['params']), '?'));
            $this->params = array_merge($this->params,$where['params']);
        }
        $this->whereStatement .= ') ';
    }

    protected function parseWhereSelect(array $where)
    {
        $type = explode(' ', self::MAP[$where['type']], 2);
        $this->whereStatement .= ' ' . $type[0] . ' ' . $where['column'] . ' ' . $where['notation'] . ' ( ';
        
        $statement = new Statement();
        $where['select']($statement);
        $statement->execSelectSqlStatement();
        $this->whereStatement .= $statement->getSqlStatement();
        $this->params = array_merge($this->params,$statement->getParams());

        $this->whereStatement .= ') ';
    }

    protected function parseWhereBetween(array $where)
    {
        $type = explode(' ', self::MAP[$where['type']], 2);
        $this->whereStatement .= sprintf(
                                            ' %s %s between ? and ? ',
                                            $type[0],
                                            $where['column']
                                        );

        $this->params = array_merge($this->params,$where['params']);
    }

    public function __call($alias,$values)
    {
        $name = '';
        $type = substr($alias, 0, 2);
        if (strtolower($type) !== 'or') {
            $type = trim('and ' . strtolower(explode('where', lcfirst($alias), 2)[1]));
        } else {
            $type = join(' ',array_map(function($value){
                return strtolower($value);
            },explode('Where', $alias, 2)));
        }
        
        $split = explode('or', $alias, 2);
        if (count($split) === 2) {
            $fn = 'append' . ucfirst($split[1]);
        } else {
            $fn = 'append' . ucfirst($split[0]);
        }
        
        if (method_exists($this, $fn)) {
            $reflect = new ReflectionMethod(__class__,$fn);
            $number = $reflect->getNumberOfParameters();


            if ($number > 2) {
                array_unshift($values, self::$TYPE[trim($type)]);
                $reflect->invokeArgs($this,$values);
            } else {
                $reflect->invoke($this,self::$TYPE[trim($type)],$values);
            }
        } else {
            throw new Exception($alias . '  is not found');
        }
        return $this;
    }
}