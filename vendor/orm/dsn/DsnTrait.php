<?php

namespace orm\dsn;

use orm\exception\DBDsnException;

trait DsnTrait
{

    protected function check()
    {
        foreach (self::OPTION_INTERSECT as $value) {
            if (!isset($this->option[$value])) {
                throw new DBDsnException('DB option should have ' . $value);
            }
            if (empty($this->option[$value])) {
                throw new DBDsnException($value . ' should not empty');
            }
        }
    }
} 