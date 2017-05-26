<?php
	
	namespace vendor\config;

	use \ArrayAccess;
	use \FilesystemIterator;

	/**
	 * 这个类没有做遍历文件树的功能,所以那些配置文件都只写在app/config目录里面,
	 * 不做目录树分类
	 */
	class Config extends FilesystemIterator implements ArrayAccess{
		
		private $container = [];

		public function __construct($path = ''){
			parent::__construct($path,self::UNIX_PATHS);
		}

		/**
		 * 这个方法是遍历$path的目录下的PHP文件,不包括二级目录
		 * 并且会将PHP文件里面的东西以 [文件名] => 文件内的数组 形式存放到$this->container里面
		 * 
		 * @AuthorHTL neetdai
		 * @DateTime  2017-05-26T16:00:26+0800
		 */
		public function iter(){
			foreach($this as $path){
				if( $path->isFile() && ($path->getExtension() === 'php') ){
					$this->container = array_merge($this->container,[$path->getBasename('.php') => require $path->getPathName()]);
				}
			}
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