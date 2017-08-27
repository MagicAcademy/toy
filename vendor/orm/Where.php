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

    const TYPE = [
    	'and' => 1 << 0,
    	'or' => 1 << 1,
    	'and in' => 1 << 2,
    	'or in' => 1 << 3
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
                        'isSpoce' => true,
                        'spoce' => $args[0]($this)
                        ];
        } elseif ($argsCount === 2) {
            $where = [
                        'column' => $args[0],
                        'sign' => '=',
                        'param' => '?',
                        'isClosure' => false
                        ];

            $this->params[] = $args[1];

        } elseif ($argsCount >= 3) {
            if (!in_array(self::ALLOW_SIGN,$args[1])) {
                throw new Exception($args[1] . ' isn\'t allow to used');
            }
            $where = [
                        'column' => $args[0],
                        'sign' => $args[1],
                        'param' => '?',
                        'isClosure' => false
                        ];
            $this->params[] = $args[2];
        }
        $where['type'] = $type;
        $this->wheres[] = $where;
    }

    public function whereIn(string $type,string $column,$params)
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

    public function parse()
    {
        foreach ($this->wheres as $where) {
        	if ($where['type'] & (self::TYPE['and'] | self::TYPE['or'])) {
        		$this->parseWhere($where);
            } elseif ($where['type'] & (self::TYPE['and in'] | self::TYPE['or in'])) {
            	$this->parseWhereIn($where);
            }
        }

        return [$this->whereStatement,$this->params];
    }

    protected function parseWhere($where)
    {
    	if ($where['isSpoce']) {
    		$this->whereStatement .= '(';
    	}
    }

    protected function parseWhereIn($where)
    {

    }
}