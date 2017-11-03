<?php

namespace vendor\request;

use vendor\request\RequestException;

class Request{

    private $server = [];

    public function __construct(){
        $this->server = $_SERVER;
    }

    public function __get($key){
        $key = strtoupper($key);
        return isset($this->server[$key])?$this->server[$key]:'';
    }
}