<?php
	
	namespace vendor\orm;

	use \PDO;
	use \PDOStatement;
	use \PDOException;
	use \Exception;

	class Select{

		protected $table = '';

		protected $columns = [];

		protected $where = [];

		protected $connect = null;

		const WHERE = 1;

		const ORWHERE = 2;

		const WHEREIN = 3;

		const ORWHEREIN = 4;

		public function __construct(PDO $connect){
			$this->connect = $connect;
		}

		public function table($tableName){
			$this->table = $tableName;
			return $this;
		}

		public function select($columns = []){
			$this->columns = $columns;

			return $this;
		}

		public function where($column,$data,$sign = '='){
			if( is_array($data) || is_object($data) ){
				throw new Exception($data . ' type can\' be array or object');
			}

			$this->where[] = $this->arrangeWhere(self::WHERE,$column,$data,$sign);

			return $this;
		}

		public function orWhere($column,$data,$sign = '='){
			if( is_array($data) || is_object($data) ){
				throw new Exception($data . ' type can\' be array or object');
			}

			$this->where[] = $this->arrangeWhere(self::ORWHERE,$column,$data,$sign);

			return $this;
		}

		public function whereIn($column,$data){
			if( !is_array($data) ){
				throw new Exception($data . ' type must be array');
			}

			$this->where[] = $this->arrangeWhere(self::WHEREIN,$column,$data,'');

			return $this;
		}

		public function orWhereIn($column,$data){
			if( !is_array($data) ){
				throw new Exception($data . ' type must be array');
			}
			$this->where[] = $this->arrangeWhere(self::ORWHEREIN,$column,$data,'');

			return $this;
		}

		protected function arrangeWhere($type,$column,$data,$sign){
			return [
				'type' => $type,
				'column' => $column,
				'data' => $data,
				'sign' => $sign
			];
		}

		protected function selectFormat(){
			$sql = 'select';

			if( count($this->columns) === 0 ){
				$sql .= ' *';
			}else{
				foreach($this->columns as $column){
					
					$sql .= ' ' . trim($column) . ',';
				}
				$sql = rtrim($sql,',');
			}

			$sql .= ' from ' . trim($this->table) . ' ';

			list($sql,$parameters) = $this->resolveWhere($sql);

			return [$sql,$parameters];
		}

		protected function resolveWhere($sql){
			$prevent = false;
			$parameters = [];
			$type = 0;

			foreach($this->where as $where){
				$type = $where['type'];
				
				if( $prevent === false ){
					if( $type === self::WHERE ){
						$sql .= 'where ' . $where['column'] . ' ' . $where['sign'] . ' ? ';
						
						$prevent = true;

					}else if( $type === self::WHEREIN ){
						$sql .= 'where ' . $where['column'] . ' in (' . implode(',', array_fill(0, count($where['data']), '?')) . ') ';
						
						$prevent = true;

					}else{
						throw new Exception('orWhere or orWhereIn must after where or whereIn');
					}
				}else{
					if( $type === self::WHERE ){
						$sql .= 'and ' . $where['column'] . ' ' . $where['sign'] . ' ? ';
					}else if( $type === self::ORWHERE ){
						$sql .= 'or ' . $where['column'] . ' ' . $where['sign'] . ' ? ';
					}else if( $type === self::WHEREIN ){
						$sql .= 'and ' . $where['column'] . ' in (' . implode(',', array_fill(0, count($where['data']), '?')) . ') ';
					}else{
						$sql .= 'or ' . $where['column'] . ' in (' . implode(',', array_fill(0,count(where['data']), '?')) . ') ';
					}
				
				}

				$parameters = array_merge( $parameters , is_array($where['data']) ? $where['data'] : [$where['data']] );
			}

			return [$sql,$parameters];
		}

		protected function exec($sql,$parameters){
			
			$statment = $this->connect->prepare($sql);
			$statment->execute($parameters);

			return $statment->fetchAll(PDO::FETCH_CLASS);
		}

		public function get(){
			
			list($sql,$parameters) = $this->selectFormat();

			$sql .= ' ;';

			return $this->exec($sql,$parameters);
		}

		public function one(){
			list($sql,$parameters) = $this->selectFormat();

			$sql .= ' limit 1 offset 1 ;';

			return $this->exec($sql,$parameters);
		}
	}