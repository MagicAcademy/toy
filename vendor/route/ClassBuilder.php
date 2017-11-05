<?php declare(strict_types=1);

namespace route;

use \Exception;
use \ReflectionClass;
use \ReflectionMethod;


class ClassBuilder{

    private $list = [];

    public function __construct(string $classAndMethod = '',string $delimiter = ''){
        $this->list = explode($delimiter, $classAndMethod,2);
    }

    public function build($paramter){
        $class = $this->list[0];
        $method = $this->list[1];

        return (new $class())->$method($paramter);
    }
}