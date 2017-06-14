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

		protected $limit = null;

		protected $offset = null;

		protected $join = [];

		protected $group = [];

		protected $order = [];

		const WHERE = 1;

		const ORWHERE = 2;

		const WHEREIN = 3;

		const ORWHEREIN = 4;

		const INNERJOIN = 5;

		const LEFTJOIN = 6;

		const RIGHTJOIN = 7;

		public function __construct(PDO $connect){
			$this->connect = $connect;
		}

		/**
		 * [table description]
		 * @AuthorHTL
		 * @DateTime  2017-06-12T12:18:43+0800
		 * @param     [type]                   $tableName [description]
		 * @return    [type]                              [description]
		 */
		public function table($tableName){
			$this->table = $tableName;
			return $this;
		}

		public function select($columns){
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

		/**
		 * where in 的sql语句
		 * @AuthorHTL neetdai
		 * @DateTime  2017-06-14T15:59:44+0800
		 * @param     [type]                   $column [description]
		 * @param     [type]                   $data   [description]
		 * @return    [type]                           [description]
		 */
		public function whereIn($column,$data){
			if( !is_string($column) ){
				throw new Exception($column . 'type must be string');
			}

			if( is_object($data) ){
				throw new Exception($data . ' type can\' object');
			}

			if( !is_array($data) ){
				$data = [$data];
			}

			$this->where[] = $this->arrangeWhere(self::WHEREIN,$column,$data,'');

			return $this;
		}

		public function orWhereIn($column,$data){
			if( is_string($column) ){
				throw new Exception($column . 'type must be string');
			}

			if( !is_object($data) ){
				throw new Exception($data . ' type must be array');
			}

			if( !is_array($data) ){
				$data = [$data];
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

		/**
		 * [join description]
		 * @AuthorHTL
		 * @DateTime  2017-06-14T15:57:11+0800
		 * @param     [type]                   $table        [description]
		 * @param     [type]                   $column       [description]
		 * @param     [type]                   $sign         [description]
		 * @param     [type]                   $relateColumn [description]
		 * @return    [type]                                 [description]
		 */
		public function join($table,$column,$sign,$relateColumn){
			$this->join[] =$this->arrangeJoin(self::INNERJOIN,$table,$column,$sign,$relateColumn);

			return $this;
		}

		/**
		 * [leftJoin description]
		 * @AuthorHTL
		 * @DateTime  2017-06-14T15:57:17+0800
		 * @param     [type]                   $table        [description]
		 * @param     [type]                   $column       [description]
		 * @param     [type]                   $sign         [description]
		 * @param     [type]                   $relateColumn [description]
		 * @return    [type]                                 [description]
		 */
		public function leftJoin($table,$column,$sign,$relateColumn){
			$this->join[] = $this->arrangeJoin(self::LEFTJOIN,$table,$column,$sign,$relateColumn);

			return $this;
		}

		/**
		 * [rightJoin description]
		 * @AuthorHTL
		 * @DateTime  2017-06-14T15:57:22+0800
		 * @param     [type]                   $table        [description]
		 * @param     [type]                   $column       [description]
		 * @param     [type]                   $sign         [description]
		 * @param     [type]                   $relateColumn [description]
		 * @return    [type]                                 [description]
		 */
		public function rightJoin($table,$column,$sign,$relateColumn){
			$this->join[] = $this->arrangeJoin(self::RIGHTJOIN,$table,$column,$sign,$relateColumn);

			return $this;
		}

		protected function arrangeJoin($type,$table,$column,$sign,$relateColumn){
			return [
				'type' => $type,
				'table' => $table,
				'column' => $column,
				'sign' => $sign,
				'relateColumn' => $relateColumn
			];
		}

		/**
		 * limit 是sql语句的limit,这个方法是可变长度参数的
		 * 如果是一个参数,代表着 sql语句中的 限制输出数量
		 * 如果是两个或以上的参数数量,只取前两个,代表着 sql语句中偏移量和限制数
		 * @AuthorHTL neetdai
		 * @DateTime  2017-06-11T23:22:40+0800
		 * @return    [Select]                   [description]
		 */
		public function limit(){
			$args = func_get_args();
			$count = func_num_args();

			if( $count === 1 ){
				$this->limit = $args[0];
			}else if( $count >= 2 ){
				$this->offset = $args[0];
				$this->limit = $args[1];
			}

			return $this;
		}

		public function groupBy($column){
			if( !is_string($column) ){
				throw new Exception($column . ' type must be string');
			}

			$this->group[] = $column;

			return $this;
		}

		public function orderBy($column,$sort = 'asc'){
			$this->order[] = [$column,$sort];
			
			return $this;
		}

		/**
		 * 调用resolve前缀的方法,将select table join where group order,变成sql语句
		 * @AuthorHTL neetdai
		 * @DateTime  2017-06-14T17:57:54+0800
		 * @return    [array]                   会返回 sql 和 参数,以便给pdo进行预处理
		 */
		protected function selectFormat(){
			$sql = 'select';

			if( count($this->columns) === 0 ){
				$sql .= ' *';
			}else{
				
				$sql .= ' ' . implode(', ', $this->columns);
			}

			$sql .= ' from ' . trim($this->table) . ' ';

			$sql .= $this->resolveJoin();

			list($sql,$parameters) = $this->resolveWhere($sql);

			$sql .= $this->resolveGroupBy();

			$sql .= $this->resolveOrderBy();
			
			return [$sql,$parameters];
		}

		protected function resolveJoin(){
			$list = [
				self::INNERJOIN => 'inner join ',
				self::LEFTJOIN => 'left join ',
				self::RIGHTJOIN => 'right join '
			];
			$sql = '';
			foreach($this->join as $join){
				$type = $join['type'];
				$sql .= sprintf(
								'%s %s on %s %s %s ',
								$list[$type],
								$join['table'],
								$join['column'],
								$join['sign'],
								$join['relateColumn']
								);
			}

			return $sql;
		}

		protected function resolveWhere($sql){
			$prevent = false;
			$parameters = [];
			$type = 0;

			$list = [
				self::WHERE => function($sql,$where,$prevent){
					$sql .= ($prevent === false)?'where ':'and ';
					$sql .= $where['column'] . ' ' . $where['sign'] . ' ? ';
					return $sql;
				},
				self::WHEREIN => function($sql,$where,$prevent){
					$sql .= ($prevent === false)?'where ':'and ';
					$sql .= $where['column'] . ' in (' . implode(',', array_fill(0, count($where['data']), '?')) . ') ';
					return $sql;
				},
				self::ORWHERE => function($sql,$where,$prevent){
					if( $prevent === false ){
						throw new Exception('orWhere or orWhereIn must after where or whereIn');
					}else{
						$sql .= 'or ' . $where['column'] . ' ' . $where['sign'] . ' ? ';
					}
					return $sql;
				},
				self::ORWHEREIN => function($sql,$where,$prevent){
					if( $prevent === false ){
						throw new Exception('orWhere or orWhereIn must after where or whereIn');
					}else{
						$sql .= 'or ' . $where['column'] . ' in (' . implode(',', array_fill(0, count($where['data']), '?')) . ') ';
					}
					return $sql;
				}
			];

			foreach($this->where as $where){
				$sql = $list[$where['type']]($sql,$where,$prevent);

				$prevent = true;

				$parameters = array_merge( $parameters , is_array($where['data']) ? $where['data'] : [$where['data']] );
			}

			return [$sql,$parameters];
		}

		/**
		 * 将 $this->group 数组中的group,变成sql语句
		 * @AuthorHTL neetdai
		 * @DateTime  2017-06-14T17:54:10+0800
		 * @return    [string]                   sql语句
		 */
		protected function resolveGroupBy(){
			return count($this->group) > 0 ? 'group by ' . implode(',',$this->group) . ' ':'';
		}

		/**
		 * 将 $this->order 数组中的order,变成sql语句
		 * @AuthorHTL neetdai
		 * @DateTime  2017-06-14T17:45:54+0800
		 * @return    [string]                   sql语句
		 */
		protected function resolveOrderBy(){
			if( count($this->order) > 0 ){
				return 'order by ' . rtrim(array_reduce($this->order,
										function($carry,$item){
											return $carry .= implode(' ', $item) . ',';
										},''),',') . ' ';
			}else{
				return '';
			}
		}

		protected function resolveLimit($sql){
			if( is_int($this->limit) ){
				$sql .= 'limit ';

				if( is_int($this->offset) ){
					$sql .= $this->offset . ',';
				}

				$sql .= $this->limit;
			}

			return $sql;
		}

		protected function exec($sql,$parameters){
			
			$statment = $this->connect->prepare($sql);
			$statment->execute($parameters);

			return $statment->fetchAll(PDO::FETCH_CLASS);
		}

		public function get(){
			
			list($sql,$parameters) = $this->selectFormat();

			$sql = $this->resolveLimit($sql);

			$sql .= ' ;';
		
			return $this->exec($sql,$parameters);
		}

		public function one(){
			list($sql,$parameters) = $this->selectFormat();

			$this->limit = 1;
			$this->offset = 1;

			$sql = $this->resolveLimit($sql) . ' ;';
			
			return $this->exec($sql,$parameters);
		}
	}