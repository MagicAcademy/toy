<?php declare(strict_types=1);

namespace orm;

use orm\Statement;
use \Closure;
use orm\exception\DBStatementException;

class Join
{
	public static $TYPE = [
		'join' => 1 << 0,
		'left join' => 1 << 1,
		'right join' => 1 << 2,
        'and' => 1 << 3,
        'or' => 1 << 4
	];

    const SPOCE = [
        'join' => 1 << 0 | 1 << 1 | 1 << 2,
        'condition' => 1 << 3 | 1 << 4
    ];

    const MAP = [
        1 << 0 => 'join',
        1 << 1 => 'left join',
        1 << 2 => 'right join',
        1 << 3 => 'and',
        1 << 4 => 'or'
    ];

    protected $joins = [];

    protected $joinStatement = '';

	public function appendJoin(int $type, string $table, $condition)
	{
        $isClosure = $condition instanceof Closure;
        if (!is_string($condition) && !$isClosure) {
            throw new DBStatementException("condition should be string or Closure");
        }
		$this->joins[] = [
            'type' => $type,
            'table' => $table,
            'condition' => $condition,
            'isClosure' => $isClosure
        ];
	}

    public function and(string $condition): Join
    {
        $this->joins[] = [
            'type' => self::$TYPE['and'],
            'condition' => $condition
        ];

        return $this;
    }

    public function or(string $condition): Join
    {
        $this->joins[] = [
            'type' => self::$TYPE['or'],
            'condition' => $condition
        ];

        return $this;
    }

    public function parse()
    {
        foreach ($this->joins as $join) {
            if ($join['type'] & self::SPOCE['join']) {
                $this->parseJoin($join);
            } else {
                $this->parseCondition($join);
            }
            
        }

        return $this->joinStatement;
    }

    protected function parseJoin(array $join)
    {
        $this->joinStatement .= sprintf(
                                        " %s %s on ",
                                        self::MAP[$join['type']],
                                        $join['table']
                                    );

        if ($join['isClosure']) {
            $this->joinStatement .= '( ';

            $newJoin = new static;
            $join['condition']($newJoin);
            $this->joinStatement .= ltrim(ltrim($newJoin->parse(),'and'),'or');

            $this->joinStatement .= ' )';
        } else {
            $this->joinStatement .= $join['condition'];
        }
    }

    protected function parseCondition(array $join)
    {
        $this->joinStatement .= sprintf('%s %s',self::MAP[$join['type']],$join['condition']);
    }
}