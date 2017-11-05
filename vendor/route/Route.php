<?php declare(strict_types=1);

namespace route;

use \Exception;
use route\RouteCollection;
use toyInterfaces\ResponseInterface;
use toyInterfaces\NotFoundInterface;
use \ArrayAccess;


class Route{

    private $notFound = null;

    private $config = [];

    private $matchList = [
                            'GET' => [
                                        'pattern' => [],
                                        'match' => []
                                    ],
                            'POST' => [
                                        'pattern' => [],
                                        'match' => []
                                    ],
                            'DELETE' => [
                                            'pattern' => [],
                                            'match' => []
                                        ],
                            'PUT' => [
                                        'pattern' => [],
                                        'match' => []
                                    ]
                        ];

    public function __construct(NotFoundInterface $notFound = null){
        $this->notFound = $notFound;
    }

    public function setConfig($config){
        if( is_array($config) || ($config instanceof ArrayAccess) ){
            $this->config = $config;
        }
    }

    /**
     * [__call description]
     * @AuthorHTL neetdai
     * @DateTime  2017-05-13T13:43:01+0800
     * @param     [type]                   $name      [description]
     * @param     array                    $arguments [description]
     * @return    [type]                              [description]
     */
    public function __call($name,array $arguments){
        $argLength = count($arguments);

        if($argLength < 2){
            throw new Exception('can\' match');
        }

        $upper = strtoupper($name);

        if(!array_key_exists($upper, $this->matchList)){
            throw new Exception('not found this method :' . $name);
        }

        $this->matchList[$upper]['pattern'][] = $this->convertRegular($arguments[0]);
        
        $collection = new RouteCollection($arguments[1],$this->config);
        $this->matchList[$upper]['match'][] = $collection;

        return $collection;
    }

    /**
     * [convertRegular description]
     * @AuthorHTL neetdai
     * @DateTime  2017-05-13T14:48:52+0800
     * @param     string                   $uri [description]
     * @return    string                        [description]
     */
    private function convertRegular($uri){
        $start = '/';
        $end = '/';

        $match = [
            '@number@' => $this->numberRegular(),
            '@string@' => '\w+',
            '@more@' => '.*'
        ];

        $uri = trim($uri);
        
        $first = strpos($uri, '@more@',0);
        if($first === false || $first !== 0){
            $start .= '^';
        }

        $count = count($uri);
        $last = strrpos($uri, '@more@',$count - 1);
        if($last === false || $last < $count - 6){
            $end = '$' . $end;
        }

        return $start . strtr(preg_quote($uri,'/'),$match) . $end;
    }

    private function numberRegular(){
        return '(\d+|\d+.\d+)';
    }

    /**
     * [match description]
     * @AuthorHTL neetdai
     * @DateTime  2017-05-13T13:40:27+0800
     * @return    [type]                   [description]
     */
    public function match(){
        $method = $_SERVER['REQUEST_METHOD'];

        $uri = explode($_SERVER['SCRIPT_NAME'],$_SERVER['REQUEST_URI'],2)[1];
        
        $matchs = $this->matchList[$method]['pattern'];

        foreach($matchs as $key=>$item){
            if(preg_match($item, $uri)){
                return $this->matchList[$method]['match'][$key]->done();
            }
        }
        
        if(is_null($this->notFound)){
            return call_user_func(function(){
                // http_response_code(404);
                ob_start();
                echo 'not found';
                $content = ob_get_contents();
                ob_flush();
                http_response_code(404);
                ob_end_flush();
            });
        }else{
            throw $this->notFound;
        }
    }
}