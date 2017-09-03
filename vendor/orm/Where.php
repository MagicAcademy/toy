<?php

namespace orm;

use \Closure;

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
        'or in' => 1 << 3
    ];

    const SPOCE = [
        'where' => 1 | 2,
        'whereIn' => 3 | 4
    ];

    const MAP = [
        1 << 0 => 'and',
        1 << 1 => 'or',
        1 << 2 => 'and in',
        1 << 3 => 'or in'
    ];

    public function appendWhere(string $type,array $args)
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
                        'sign' => '=',
                        'params' => $args[1]
                        ];

        } elseif ($argsCount >= 3) {
            if (!in_array(self::ALLOW_SIGN,$args[1])) {
                throw new Exception($args[1] . ' isn\'t allow to used');
            }
            $where = [
                        'isClosure' => false,
                        'column' => $args[0],
                        'sign' => $args[1],
                        'params' => $args[2]
                        ];
        }
        $where['type'] = $type;
        $this->wheres[] = $where;
    }

    public function appendWhereIn(string $type,string $column,$params)
    {
        $isClosure = $params instanceof Closure;
        $isArray = is_array($params);
        if (!$isArray || !$isClosure) {
            throw new Exception($params . 'should be a array or Closure');
        }   
        $where = [
                    'type' => $type,
                    'column' => $column,
                    'isClosure' => $isClosure,
                    ];

        if ($isArray) {
            $this->params = array_merge($this->params,$params);
        }
        if ($isClosure) {
            $where['isSpoce'] = true;
            $where['where'] = $params($this);
        }
    }

    public function parse(): array
    {
        foreach ($this->wheres as $where) {
            if ($where['type'] & (self::SPOCE['where'])) {
                $this->parseWhere($where);
            } elseif ($where['type'] & (self::$TYPE['and in'] | self::$TYPE['or in'])) {
                $this->parseWhereIn($where);
            }
        }

        return [$this->whereStatement,$this->params];
    }

    protected function parseWhere($where)
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
            $this->whereStatement .= ' ' . $where['sign'];
            $this->whereStatement .= ' ' . '?';
            $this->params[] = $where['params'];
        }
    }

    protected function parseWhereIn($where)
    {

    }

    public function __call($alias,$value)
    {
        $name = '';
        $type = substr($alias, 0, 2);
        if (strtolower($type) !== 'or') {
            $type = trim('and ' . strtolower(explode('where', lcfirst($alias), 2)[1]));
        } else {
            $type = join(' ',explode('Where', $alias, 2));
        }
        
        $split = explode('or', $alias, 2);
        if (count($split) === 2) {
            $fn = 'append' . ucfirst($split[1]);
        } else {
            $fn = 'append' . ucfirst($split[0]);
        }
        
        if (method_exists($this, $fn)) {
            $this->$fn(self::$TYPE[trim($type)],$value);
        } else {
            throw new Exception($alias . '  is not found');
        }
        return $this;
    }
}