<?php

namespace vendor\config;

use \ArrayAccess;
use \FilesystemIterator;

/**
 * 这个类没有做遍历文件树的功能,所以那些配置文件都只写在app/config目录里面,
 * 不做目录树分类
 */
class Config  implements ArrayAccess{
    
    private $container = [];

    private $rootPath = '';

    public function __construct($path = ''){
        if( file_exists($path) && is_dir($path) ){
            $this->rootPath = rtrim($path,'/').'/';

            $this->dirIter($this->rootPath);
            
        }
    }

    /**
     * 这个方法是遍历$path的目录下的PHP文件
     * 并且会将PHP文件里面的东西以 [文件名] => 文件内的数组 形式存放到$this->container里面,使用深度优先的递归
     * 
     * @AuthorHTL neetdai
     * @DateTime  2017-05-26T16:00:26+0800
     */
    private function dirIter($dir = ''){
        $dir = rtrim($dir,'/') . '/';
        $od = opendir($dir);

        while( ($path = readdir($od)) !== false ){
            if($path === '.' || $path === '..')continue;

            $realPath = $dir . $path;

            if( is_dir($realPath) ){

                $this->dirIter($realPath);
            }else{
                $pathinfo = pathinfo($path);

                if( is_file($realPath) && $pathinfo['extension'] === 'php' ){
                    
                    $tmpPath = $dir . basename($path,'.php');

                    $newPath = strtr($tmpPath, [$this->rootPath => '']);
                    
                    $keys = explode('/', $newPath);

                    $i = &$this->container;

                    foreach ($keys as $value) {
                        $i[$value] = [];
                        $i = &$i[$value];
                    }
                    
                    $i = require $realPath;
                }
            }
        }

        closedir($od);
    }

    public function offsetExists($offset){
        return array_key_exists($offset, $this->container);
    }

    public function offsetGet($offset){
        return isset($this->container[$offset])?$this->container[$offset]:null;
    }

    public function offsetSet($offset,$value){
        if(is_null($offset)){
            $this->container[] = $value;
        }else{
            $this->container[$offset] = $value;
        }
    }

    public function offsetUnset($offset){
        unset($this->container[$offset]);
    }
}