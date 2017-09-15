<?php declare(strict_types=1);

namespace orm;

use orm\Statement;
use \Closure;
use orm\exception\DBStatementException;

class Join
{
	const TYPE = [
		'join' => 1 << 0,
		'left join' => 1 << 1,
		'right join' => 1 << 2
	];

	public function appendJoin(int $type, string $column, string $notation, $params)
	{
		
	}
}