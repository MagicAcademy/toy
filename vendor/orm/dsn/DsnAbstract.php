<?php

namespace orm\dsn;

abstract class DsnAbstract
{
    const OPTION_INTERSECT = [
            'type',
            'host',
            'port',
            'dataBaseName',
            'charset'
        ];
}
