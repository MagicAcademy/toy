<?php

namespace orm\dsn;

use orm\dsn\DsnInterface;
use orm\dsn\DsnAbstract;
use orm\dsn\DsnTrait;

class PgsqlDsn extends DsnAbstract implements DsnInterface
{
    use DsnTrait;

    private $option = [];

    public function setOption(array $option)
    {
        $this->option = $option;
        $this->check();
    }

    public function getDsn():string
    {
        return sprintf(
                        '%s:dbname=%s host=%s port=%d ',
                        $this->option['type'],
                        $this->option['dataBaseName'],
                        $this->option['host'],
                        $this->option['port']
                        );
    }

    public function is():bool
    {
        return strtolower(trim($this->option['type'])) === 'pgsql';
    }
}