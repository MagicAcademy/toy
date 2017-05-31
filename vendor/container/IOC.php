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
		 * [bind 绑定一个匿名函数或者一个字符串类名,]
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
												'single' => null,
												'alreadySingle' => false
											];
			}
		}

		/**
		 * 通过键值生成一个匿名函数或者一个对象
		 * @AuthorHTL neetdai
		 * @DateTime  2017-05-07T14:40:40+0800
		 * @param     string                   $abstract [description]
		 * @return    closure|object                     [description]
		 */
		
		public static function make($abstract){

			$target = self::$Binding[$abstract];

			if($target['isSingle']){
				if($target['alreadySingle']){
					return $target['single'];
				}else{
					$single = self::build($target);
					self::$Binding[$abstract]['alreadySingle'] = true;
					self::$Binding[$abstract]['single'] = $single;
					return $single;
				}
			}else{
				return self::build($target);
			}
		}

		/**
		 * [build description]
		 * @AuthorHTL
		 * @DateTime  2017-05-19T23:21:00+0800
		 * @param     [type]                   $target [description]
		 * @return    [type]                           [description]
		 */
		private static function build($target){
			$concrete = $target['concrete'];
			$reflect = null;
			if($concrete instanceof Closure){
				return self::buildClosure(self::getFunction($concrete));
			}else{
				return self::buildClass(self::getConstruct($concrete));
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
					// 这里是为了兼容使用命名空间的类名和没有使用命名空间的类名都能够通过make实例化类
					// 注意:如果存在 a\aClass 和 b\aClass 两个类,这两个类本来是不同的类,
					// 但是这里会将最后一个 '\' 后面的字符串识别成键名,所以这两个会冲突,所以要检查一下自己的类名时候会冲突
					$name = rtrim($depend->getName(),'\\');

					$position = strrpos($name, '\\',-1);

					if( $position === false ){
						$depends[] = self::make($name);
						continue;
					}

					$abstract = substr($name, $position + 1);

					$depends[] = self::make($abstract);
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
			self::$Binding[$abstract] = [
											'concrete' => $concrete,
											'isSingle' => true,
											'single' => null,
											'alreadySingle' => false
										];
		}
	}