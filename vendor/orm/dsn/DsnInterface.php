<?php

namespace orm\dsn;

interface DsnInterface
{

    public function setOption(array $option);

    public function getDsn():string;
}