<?php

namespace vendor\route;

use \Closure;
use vendor\container\IOC;
use \Exception;
use vendor\route\ClassBuilder;

class RouteCollection {

    private $_before = null;

    private $_after = null;

    private $_current = null;

    private $config = [];

    public function __construct($current,$config){

        if( isset($config['base']) ){
            $this->config = $config['base'];
        }
        
        $this->_before = $this->defaultInit();
        
        if( is_string($current) ){
            $this->_current = $this->config['controller_namespace_prefix'] . $current;
        }else{
            $this->_current = $current;
        }
        
        $this->_after = $this->defaultInit();

    }

    private function defaultInit(){
        return function($parameter = null){
            return $parameter;
        };
    }

    private function build($function){
        if( is_null($function) ){
            return function($parameter = null){
                return $parameter;
            };
        }else{
            return $function;
        }
    }

    /**
     * 设置进入controller之前的中间件
     * @AuthorHTL neetdai
     * @DateTime  2017-06-06T12:27:19+0800
     * @param     [string|Closure]                   $middleware [可以是闭包或者是字符串,
     * 如果是字符串的话就以 类名(不需要加命名空间) + config文件夹中的base中的 split_controller_and_method + 方法名]
     * @return    [RouteCollection]                               [description]
     */
    public function before($middleware){
        $this->_before = $this->build($middleware);

        if( is_string($this->_before) ){
            $this->_before = $this->config['middleware_namespace_prefix'] . $this->_before;
        }

        return $this;
    }

    /**
     * 设置执行controller以后的中间件
     * @AuthorHTL
     * @DateTime  2017-06-06T12:33:22+0800
     * @param     [type]                   $middleware [description]
     * @return    [type]                               [description]
     */
    public function after($middleware){
        $this->_after = $this->build($middleware);

        if( is_string($this->_after) ){
            $this->_after = $this->config['middleware_namespace_prefix'] . $this->_after;
        }

        return $this;
    }

    public function done(){
        $iter = [$this->_before,$this->_current,$this->_after];
        $result = null;

        foreach($iter as $function){

            if( is_callable($function) ){
                $result = call_user_func($function,$result);
            }else{
                $result = (new ClassBuilder($function,$this->config['split_controller_and_method']))->build($result);
            }
        }
        return $result;
    }

}