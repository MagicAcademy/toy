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
use \ArrayAccess;

class ORM implements Iterator, ArrayAccess{

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
     * 禁止查询的字段
     * @var array
     */
    protected $blockFields = [];

    /**
     * 允许查询的字段
     * @var array
     */
    protected $accessFields = [];

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

    /**
     * 关联的ORM
     * @var array
     */
    protected $relationClass = [];

    public function __construct()
    {
        $this->accessFields = array_diff($this->fields,$this->blockFields);
    }

    /**
     * 返回主键
     * @AuthorHTL
     * @DateTime  2017-11-25T17:26:21+0800
     * @return    [type]                   [description]
     */
    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
    }

    /**
     * 设置 orm\DB
     * @AuthorHTL
     * @DateTime  2017-11-25T17:26:34+0800
     * @param     DB                       $connect [description]
     */
    public function setDB(DB $connect)
    {
        $this->connect = $connect;
    }

    /**
     * 返回表名
     * @AuthorHTL
     * @DateTime  2017-11-25T17:28:36+0800
     * @return    [type]                   [description]
     */
    public function getTable(): string
    {
        return $this->tableName;
    }

    /**
     * 返回别名
     * @AuthorHTL
     * @DateTime  2017-11-25T17:28:53+0800
     * @return    [type]                   [description]
     */
    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * 表示一对一关系
     * @AuthorHTL
     * @DateTime  2017-11-25T17:31:15+0800
     * @param     string                   $className [description]
     * @param     string                   $targetKey [description]
     * @param     string                   $selfKey   [description]
     * @return    array                             [description]
     */
    protected function hasOne(string $className,string $targetKey = '',string $selfKey = ''): array
    {
        return [
            'className' => $className,
            'relation' => self::RELATIONS['hasOne'],
            'targetKey' => $targetKey,
            'selfKey' => $selfKey
        ];
    }

    /**
     * 表示一对多的关系
     * @AuthorHTL
     * @DateTime  2017-11-25T17:32:35+0800
     * @param     string                   $className [description]
     * @param     string                   $targetKey [description]
     * @param     string                   $selfKey   [description]
     * @return    boolean                             [description]
     */
    protected function hasMany(string $className,string $targetKey = '',string $selfKey = ''): array
    {
        return [
            'className' => $className,
            'relation' => self::RELATIONS['hasMany'],
            'targetKey' => $targetKey,
            'selfKey' => $selfKey
        ];
    }

    /**
     * 表示属于一对多关系
     * @DateTime  2017-11-25T17:32:42+0800
     * @param     string                   $className [description]
     * @param     string                   $targetKey [description]
     * @param     string                   $selfKey   [description]
     * @return    [type]                              [description]
     */
    protected function belongTo(string $className,string $targetKey = '',string $selfKey = ''): array
    {
        return [
            'className' => $className,
            'relation' => self::RELATIONS['belongTo'],
            'targetKey' => $targetKey,
            'selfKey' => $selfKey
        ];
    }

    /**
     * 表示查找数据
     * @AuthorHTL
     * @DateTime  2017-11-25T17:35:43+0800
     * @return    [type]                   [description]
     */
    public function find(): self
    {
        $this->statement = $this->connect->init()->table($this->getTable());
        $this->isFind = true;
        return $this;
    }

    /**
     * 使用 Statement->one 获取数据,获取的数据类型为 stdClass 或 null
     * @AuthorHTL
     * @DateTime  2017-11-25T17:35:59+0800
     * @return    [type]                   [description]
     */
    public function one(): self
    {
        if ($this->isFind === false) {
            $this->find();
        }

        $this->putFieldsToSelect();
        $rows = $this->statement->one();

        $this->rows = $rows;

        $this->isObject = true;
        $this->rows = is_null($rows)?[]:[$rows];
        $this->count = count($this->rows);
        $this->objectRows = $rows;
        return $this;
    }

    protected function putFieldsToSelect()
    {
        $this->statement->select(...$this->accessFields);
    }

    /**
     * 使用 Statement->all 获取数据,获取的数据类型为 array
     * @AuthorHTL
     * @DateTime  2017-11-25T17:36:08+0800
     * @return    [type]                   [description]
     */
    public function all(): self
    {
        if ($this->isFind === false) {
            $this->find();
        }

        $this->putFieldsToSelect();
        $rows = $this->statement->all();

        $this->rows = $rows;
        $this->count = count($this->rows);
        return $this;
    }

    /**
     * [current description]
     * @AuthorHTL
     * @DateTime  2017-11-25T17:36:20+0800
     * @return    [type]                   [description]
     */
    public function current(): self
    {
        return $this;
    }

    /**
     * [key description]
     * @AuthorHTL
     * @DateTime  2017-11-25T17:36:29+0800
     * @return    [type]                   [description]
     */
    public function key()
    {
        return $this->key;
    }

    /**
     * [next description]
     * @AuthorHTL
     * @DateTime  2017-11-25T17:36:39+0800
     * @return    function                 [description]
     */
    public function next()
    {
        return $this->rows[$this->key++];
    }

    /**
     * [rewind description]
     * @AuthorHTL
     * @DateTime  2017-11-25T17:36:44+0800
     * @return    [type]                   [description]
     */
    public function rewind()
    {
        $this->key = 0;
    }

    /**
     * [valid description]
     * @AuthorHTL
     * @DateTime  2017-11-25T17:36:48+0800
     * @return    [type]                   [description]
     */
    public function valid(): bool
    {
        return $this->key < $this->count;
    }

    /**
     * [beforeInsert description]
     * @AuthorHTL
     * @DateTime  2017-11-25T17:39:40+0800
     * @return    [type]                   [description]
     */
    protected function beforeInsert()
    {

    }

    /**
     * [beforeUpdate description]
     * @AuthorHTL
     * @DateTime  2017-11-25T17:39:43+0800
     * @return    [type]                   [description]
     */
    protected function beforeUpdate()
    {

    }

    /**
     * [beforeSave description]
     * @AuthorHTL
     * @DateTime  2017-11-25T17:39:49+0800
     * @return    [type]                   [description]
     */
    protected function beforeSave()
    {

    }

    /**
     * [save description]
     * @AuthorHTL
     * @DateTime  2017-11-25T17:39:54+0800
     * @return    [type]                   [description]
     */
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

    public function getRows()
    {
        return $this->rows;
    }

    /**
     * [__set description]
     * @AuthorHTL
     * @DateTime  2017-11-25T17:39:32+0800
     * @param     string                   $name  [description]
     * @param     [type]                   $value [description]
     */
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
            $result = $this->buildRelation($name);
            $this->rows[$this->key]->$name = $result;
            return $result;
        } else {
            throw new ORMException('noting for ' . $name);
        }
    }

    /**
     * 获取关系,
     * 根据 ClassName 生成对应的 class,
     * 根据 relation 判断调用 one 还是 all 方法
     * targetKey 表示关联表的字段名
     * selfKey 表示本表的字段名
     * 
     * 如果 class 不是 ORM 类型,就会抛出异常
     */
    protected function buildRelation(string $name)
    {
        $relation = $this->$name();
        $class = null;

        if (isset($this->relationClass[$relation['className']])) {
            $class = $this->relationClass[$relation['className']];
        } else {
            $class = new $relation['className']();

            if (!($class instanceof ORM)) {
                throw new ORMException($relation['className'] . ' type must be ORM');
            }

            $this->relationClass[$relation['className']] = $class;
        }

        $class->setDB($this->connect);
        $selfKey = $relation['selfKey'];
        $class->find()->where($relation['targetKey'],$this->$selfKey);
        if ($relation['relation'] & self::ONE) {
            return $class->one();
        } else {
            return $class->all()->getRows();
        }
    }

    /**
     * 检查是否有该属性
     * @AuthorHTL
     * @DateTime  2017-11-25T17:39:09+0800
     * @param     [type]                   $object [description]
     * @param     string                   $name   [description]
     * @return    [type]                           [description]
     */
    protected function checkProperty($object,string $name): bool
    {
        if (is_null($object)) {
            return false;
        }

        return isset($object->$name);
    }

    /**
     * 动态调用 Statment 的方法
     * @AuthorHTL
     * @DateTime  2017-11-25T17:39:14+0800
     * @param     string                   $name [description]
     * @param     array                    $args [description]
     * @return    [type]                         [description]
     */
    public function __call(string $name,array $args = [])
    {
        if ($this->isFind === false) {
            $this->find();
        }

        $reflectionMethod = new ReflectionMethod($this->statement,$name);
        $reflectionMethod->invokeArgs($this->statement,$args);

        return $this;
    }

    /**
     * 转化成 json 格式
     * @AuthorHTL
     * @DateTime  2017-11-25T18:02:41+0800
     * @return    [type]                   [description]
     */
    public function asJson(): string
    {
        if ($this->isObject) {
            if (is_null($this->objectRows)) {
                return json_encode([]);
            } else {
                return json_encode($this->objectRows);
            }
        } else {
            return json_encode($this->rows);
        }
    }

    /**
     * 判断是否有该下标
     * @AuthorHTL
     * @DateTime  2017-12-03T12:50:20+0800
     * @param     [type]                   $offset [description]
     * @return    [type]                           [description]
     */
    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->rows);
    }

    /**
     * 作为数组获取对应的下标的值
     * @AuthorHTL
     * @DateTime  2017-12-03T12:50:25+0800
     * @param     [type]                   $offset [description]
     * @return    [type]                           [description]
     */
    public function offsetGet($offset)
    {
        return $this->offsetExists($offset)?$this->rows[$offset]:null;
    }

    /**
     * [offsetSet description]
     * @AuthorHTL
     * @DateTime  2017-12-03T12:50:29+0800
     * @param     [type]                   $offset [description]
     * @param     [type]                   $value  [description]
     * @return    [type]                           [description]
     */
    public function offsetSet($offset,$value)
    {

    }

    /**
     * 删除下标的值
     * @AuthorHTL
     * @DateTime  2017-12-03T12:50:33+0800
     * @param     [type]                   $offset [description]
     * @return    [type]                           [description]
     */
    public function offsetUnset($offset)
    {
        unset($this->rows[$offset]);
    }
}