<?php declare(strict_types=1);
	
namespace orm;

use orm\DB;
use orm\Statement;
use \ReflectionClass;
use \ReflectionMethod;
use orm\exception\ORMException;
use \stdClass;
use orm\Result;
use \Iterator;

class ORM implements Iterator{

    const RELATIONS = [
        'belongTo' => 0x00,
        'hasOne' => 0x01,
        'hasMany' => 0x02
    ];

    const ONE = self::RELATIONS['belongTo'] & self::RELATIONS['hasOne'];

    const MANY = self::RELATIONS['hasMany'];

    const RELATION_TYPE = [
        'hasOne',
        'hasMany',
        'belongTo'
    ];

    /**
     * 字段名
     * @var array
     */
    protected $fields = [];

    /**
     * 主键
     * @var string
     */
    protected $primaryKey = '';

    /**
     * 表名
     * @var string
     */
    protected $tableName = '';

    /**
     * 表的别名
     * @var string
     */
    protected $alias = '';

    /**
     * 连接数据库
     * @var null
     */
    protected $connect = null;

    /**
     * [$statement description]
     * @var null
     */
    protected $statement = null;

    /**
     * 判断是否已经触发查询,来判断应该为 insert 还是 update
     * @var boolean
     */
    protected $isFind = false;

    /**
     * 键:表字段
     * 值:字段值
     * 这个用于存储 insert 和 update 的值
     * @var array
     */
    protected $columns = [];

    /**
     * 关联其他的 ORM
     * 键:
     *     className 类名
     *     relation 关系
     *     targetKey 关联表的字段名
     *     selfKey 本表的字段名
     * @var array
     */
    protected $relations = [];

    protected $isLazy = false;

    /**
     * 获取的结果,如果使用 one 其格式为 [stdClass]
     * @var array
     */
    protected $rows = [];

    /**
     * 遍历的下标
     * @var integer
     */
    protected $key = 0;

    /**
     * 获得结果的数量
     * @var integer
     */
    protected $count = 0;

    /**
     * 判断获得的是 stdClass 还是 array
     * @var boolean
     */
    protected $isObject = false;

    /**
     * 使用 one 获取的 stdClass 或 null
     * @var null|stdClass
     */
    protected $objectRows = null;

    public function __construct()
    {
        
    }

    public function getPrimaryKey(): string
    {
        $info = $this->connect->table('INFORMATION_SCHEMA.KEY_COLUMN_USAGE')
                            ->where('TABLE_NAME',$this->getTable())
                            ->one();

        if ($info) {
            $this->primaryKey = isset($info['CONSTRAINT_NAME']) && $info['CONSTRAINT_NAME'] === 'PRIMARY'?$info['COLUMN_NAME']:'';
        }

        return $this->primaryKey;
    }

    public function setDB(DB $connect)
    {
        $this->connect = $connect;
    }

    public function getTable(): string
    {
        return $this->tableName;
    }

    public function getAlias(): string
    {
        return $this->alias;
    }

    protected function hasOne(string $className,string $targetKey = '',string $selfKey = '')
    {
        return [
            'className' => $className,
            'relation' => self::RELATIONS['hasOne'],
            'targetKey' => $targetKey,
            'selfKey' => $selfKey
        ];
    }

    protected function hasMany(string $className,string $targetKey = '',string $selfKey = '')
    {
        return [
            'className' => $className,
            'relation' => self::RELATIONS['hasMany'],
            'targetKey' => $targetKey,
            'selfKey' => $selfKey
        ];
    }

    protected function belongTo(string $className,string $targetKey = '',string $selfKey = '')
    {
        return [
            'className' => $className,
            'relation' => self::RELATIONS['belongTo'],
            'targetKey' => $targetKey,
            'selfKey' => $selfKey
        ];
    }

    public function find()
    {
        $this->statement = $this->connect->init()->table($this->getTable());
        $this->isFind = true;
        return $this;
    }

    public function one()
    {
        if ($this->isFind === false) {
            $this->find();
        }

        $rows = $this->statement->one();

        $this->rows = $rows;

        $this->isObject = true;
        $this->rows = is_null($rows)?[]:[$rows];
        $this->count = count($this->rows);
        $this->objectRows = $rows;
        return $this;
    }

    public function all()
    {
        if ($this->isFind === false) {
            $this->find();
        }

        $rows = $this->statement->all();

        $this->rows = $rows;
        $this->count = count($this->rows);
        return $this;
    }

    public function current()
    {
        return $this;
    }

    public function key()
    {
        return $this->key;
    }

    public function next()
    {
        return $this->rows[$this->key++];
    }

    public function rewind()
    {
        $this->key = 0;
    }

    public function valid(): bool
    {
        return $this->key < $this->count;
    }

    protected function beforeInsert()
    {

    }

    protected function beforeUpdate()
    {

    }

    protected function beforeSave()
    {

    }

    public function save()
    {
        if ($this->isFind) {
            $this->beforeUpdate();
            $this->beforeSave();
            $this->statement->update($this->columns);
        } else {
            $this->beforeInsert();
            $this->beforeSave();
            $this->statement->insert($this->columns);
        }
        
    }

    public function __set(string $name,$value)
    {
        $this->columns[$name] = $value;
    }

    /**
     * 这个方法的作用是:
     *     1.获取$this->rows的内容.当然了,如果没有使用 all 或 one 方法去查询,是没有结果的
     *     2.根据方法名进行关联查询
     * @AuthorHTL
     * @DateTime  2017-11-24T22:44:05+0800
     * @param     string                   $name [description]
     * @return    [type]                         [description]
     */
    public function __get(string $name)
    {
        if ($this->isObject && $this->checkProperty($this->objectRows,$name)) {
            return $this->objectRows->$name;
        } elseif (!$this->isObject && $this->checkProperty($this->rows[$this->key],$name)) {
            return $this->rows[$this->key]->$name;
        } elseif (method_exists($this, $name)) {
            
            /**
             * 获取关系,
             * 根据 ClassName 生成对应的 class,
             * 根据 relation 判断调用 one 还是 all 方法
             * targetKey 表示关联表的字段名
             * selfKey 表示本表的字段名
             * 
             * 如果 class 不是 ORM 类型,就会抛出异常
             */
            $relation = $this->$name();
            $class = new $relation['className']();

            if (!($class instanceof ORM)) {
                throw new ORMException($relation['className'] . ' type must be ORM');
            }

            $class->setDB($this->connect);
            $selfKey = $relation['selfKey'];
            $class->find()->where($relation['targetKey'],$this->$selfKey);
            if ($relation['relation'] & self::ONE) {
                return $class->one();

            } else {
                return $class->all();
            }
        }
    }

    protected function checkProperty($object,$name): bool
    {
        if (is_null($object)) {
            return false;
        }

        return isset($object->$name);
    }

    public function __call(string $name,array $args = [])
    {
        if ($this->isFind === false) {
            $this->find();
        }

        $reflectionMethod = new ReflectionMethod($this->statement,$name);
        $reflectionMethod->invokeArgs($this->statement,$args);

        return $this;
    }
}