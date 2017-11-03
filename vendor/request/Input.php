<?php
    
namespace vendor\request;

class Input{

    private $input = [];

    private $allowTags = [];

    private $allowAttr = [];

    public function __construct($allowTags = [],$allowAttr = []){
        $this->input = array_merge($_GET,$_POST);
        $this->allowAttr = $allowAttr;
    }

    public function get($key,$default = null){
        return isset($this->input[$key])?$this->input[$key]:$default;
    }

    public function all(){
        return $this->input;
    }

    public function except($key){
        $list = $this->input;

        if(array_key_exists($key, $list)){
            unset($list[$key]);
        }

        return $list;
    }

}