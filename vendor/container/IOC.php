<?php

	namespace vendor\container;

	use vendor\container\IOCException;
	use \ReflectionClass;
	use \ReflectionMethod;
	use \ReflectionFunction;
	use \Closure;

	class IOC{

		private static $Binding = [];

		/**
		 * [bind 绑定一个匿名函数或者一个字符串类名]
		 * @AuthorHTL
		 * @DateTime  2017-05-07T16:12:17+0800
		 * @param     string                   $abstract 对$concrete的一个映射名称
		 * @param     Closure or string        $concrete 一个匿名函数或一个字符串类名
		 */
		public static function bind($abstract, $concrete){

			if(!array_key_exists($abstract, self::$Binding)){
				self::$Binding[$abstract] = [
												'concrete' => $concrete,
												'isSingle' => false,
												'single' => null
											];
			}
		}

		/**
		 * [make description]
		 * @AuthorHTL
		 * @DateTime  2017-05-07T14:40:40+0800
		 * @param     string                   $abstract [description]
		 * @return    [type]                             [description]
		 */
		
		public static function make($abstract){
			if(!array_key_exists($abstract, self::$Binding)){
				self::$Binding[$abstract] = [
												'concrete' => $abstract,
												'isSingle' => false,
												'single' => null
											];
			}

			$target = self::$Binding[$abstract];

			if($target['isSingle']){
				return $target['single'];
			}else{
				$concrete = $target['concrete'];
				$reflect = null;
				if($concrete instanceof Closure){
					return self::buildClosure(self::getFunction($concrete));
				}else{
					return self::buildClass(self::getConstruct($concrete));
				}
			}
		}

		/**
		 * [dependParameters description]
		 * @AuthorHTL
		 * @DateTime  2017-05-07T14:50:54+0800
		 * @param     array                    $parameters [description]
		 * @return    [type]                               [description]
		 */
		private static function dependParameters(array $parameters){
			$depends = [];
			$depend = null;
			foreach($parameters as $parameter){
				$depend = $parameter->getClass();

				if(is_null($depend)){
					if($parameter->isDefaultValueAvailable()){
						$depends[] = $parameter->getDefaultValue();
					}else{
						$depends[] = null;
					}
				}else{
					$depends[] = self::make($depend->getName());
				}
			}
			return $depends;
		}

		/**
		 * [buildClass description]
		 * @AuthorHTL
		 * @DateTime  2017-05-07T14:58:20+0800
		 * @param     [type]                   $reflect [description]
		 * @return    class                             [description]
		 */
		private static function buildClass($reflect){
			$constructor = $reflect->getConstructor();

			if(!is_null($constructor)){
				$parameters = self::dependParameters($reflect->getConstructor()->getParameters());
				return $reflect->newInstanceArgs($parameters);
			}else{
				return $reflect->newInstanceWithoutConstructor();
			}
		}

		/**
		 * [buildClosure description]
		 * @AuthorHTL
		 * @DateTime  2017-05-07T14:58:29+0800
		 * @param     [type]                   $reflect [description]
		 * @return    Closure                           [description]
		 */
		private static function buildClosure($reflect){
			$parameters = self::dependParameters($reflect->getParameters());
			return $reflect->invokeArgs($parameters);
		}

		/**
		 * [getConstruct description]
		 * @AuthorHTL
		 * @DateTime  2017-05-07T14:58:36+0800
		 * @param     [type]                   $concrete [description]
		 * @return    ReflectionClass                    [description]
		 */
		private static function getConstruct($concrete){
			return new ReflectionClass($concrete);
		}

		/**
		 * [getFunction description]
		 * @AuthorHTL
		 * @DateTime  2017-05-07T14:58:41+0800
		 * @param     [type]                   $concrete [description]
		 * @return    ReflectionFunction                 [description]
		 */
		private static function getFunction($concrete){
			return new ReflectionFunction($concrete);
		}

		/**
		 * [setSingle description]
		 * @AuthorHTL
		 * @DateTime  2017-05-07T16:10:46+0800
		 * @param     [string]                   $abstract [description]
		 */
		public static function setSingle($abstract,$concrete){
			self::bind($abstract,$concrete);
			$single = self::make($abstract);
			self::$Binding[$abstract]['single'] = $single;
			self::$Binding[$abstract]['isSingle'] = true;
		}
	}