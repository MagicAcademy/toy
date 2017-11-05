<?php declare(strict_types=1);

namespace route;

use \Closure;
use vendor\container\IOC;
use \Exception;
use route\ClassBuilder;
use \SplStack;
use \SplQueue;

class RouteCollection
{

    private $_before = null;

    private $_after = null;

    private $_current = null;

    private $config = [];

    public function __construct($current,$config)
    {

        if ( isset($config['base']) ) {
            $this->config = $config['base'];
        }
        
        $this->_before = new SplStack();
        
        if ( is_string($current) ) {
            $this->_current = $this->config['controller_namespace_prefix'] . $current;
        } else {
            $this->_current = $current;
        }
        $this->_after = new SplQueue();

    }

    private function defaultInit()
    {
        return function($parameter = null){
            return $parameter;
        };
    }

    private function build($function)
    {
        if ( is_null($function) ) {
            return function($parameter = null){
                return $parameter;
            };
        } else {
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
    public function before($middleware)
    {
        $_middleware = $this->build($middleware);

        if ( is_string($_middleware) ) {
            $_middleware = $this->config['middleware_namespace_prefix'] . $_middleware;
        }

        $this->_before->push($_middleware);

        return $this;
    }

    /**
     * 设置执行controller以后的中间件
     * @AuthorHTL
     * @DateTime  2017-06-06T12:33:22+0800
     * @param     [type]                   $middleware [description]
     * @return    [type]                               [description]
     */
    public function after($middleware)
    {
        $_middleware = $this->build($middleware);

        if ( is_string($_middleware) ) {
            $_middleware = $this->config['middleware_namespace_prefix'] . $_middleware;
        }

        $this->_after->push($_middleware);

        return $this;
    }

    public function done()
    {
        $result = null;

        foreach ($this->_before as  $function) {
            $result = $this->buildClass($function,$result);
        }

        $result = $this->buildClass($this->_current,$result);

        foreach ($this->_after as $function) {
            $result = $this->buildClass($function,$result);
        }

        return $result;
    }

    protected function buildClass($function,$inputResult)
    {
        if ($function instanceof Closure) {
            return call_user_func($function,$inputResult);
        } else {
            return (new ClassBuilder($function,$this->config['split_controller_and_method']))->build($inputResult);
        }
        
    }

}