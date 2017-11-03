<?php

namespace vendor\exception;

use \Exception;
use toyInterfaces\NotFoundInterface;

class NotFoundException extends Exception implements NotFoundInterface{

    private $content = 'not found';

    public function __construct($path = ''){
        if( !empty($path) && file_exists($path) && is_file($path)){
            $this->$content = require $path;
        }
    }

    public function __toString(){
        return $this->content;
    }
}