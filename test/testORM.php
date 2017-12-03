<?php

require '../vendor/autoload.php';

use orm\DB;
use orm\ORM;

class Goods extends ORM
{
    protected $alias = 'g';

    protected $fields = [
                            'id',
                            'product_name',
                            'amount',
                            'total'
                        ];

    // protected $blockFields = ['id'];

    protected $primaryKey = 'id';

    protected $tableName = 'goods';

    public function goodsColor()
    {
        return $this->hasMany('GoodsColor','id','id');
    }
}


class GoodsColor extends ORM
{
    protected $alias = 'gc';

    protected $fields = [
                            'id',
                            'name'
                        ];

    protected $primaryKey = 'id';

    protected $tableName = 'goods_color';
}

class GoodsSize extends ORM
{
    protected $alias = 'gs';

    protected $fields = [
                            'id'
                        ];
}


$orm = DB::getInstance();
$statment = $orm->init([
            'database' => [
                    'type' => 'mysql',
                    'host' => 'localhost',
                    'port' => 3306,
                    'username' => 'root',
                    'password' => '',
                    'dataBaseName' => 'shop',
                    'charset' => 'utf8'
                ]
            ]);

// $statment = $orm->init([
//     'database' => [
//         'type' => 'pgsql',
//         'host' => 'localhost',
//         'port' => 5432,
//         'username' => 'root',
//         'password' => '123456',
//         'dataBaseName' => 'shop',
//         'charset' => 'utf8'
//         ]
// ]);

$goods = new Goods();

$goods->setDB($statment);

// 没有这个id
// foreach ($goods->find()->all() as $key => $value) {
//     // var_dump($value->id);
//     var_dump($value->product_name);
//     // var_dump($value->goodsColor);
//     var_dump($value->goodsColor);
// }

var_dump($goods->asJson());

// 报错 原因是找不到数据
// $result = $goods->find()->where('id',9999)->one();

// var_dump($result->id);
var_dump($goods->asJson());
foreach ($goods->find()->one() as $key => $value) {
    // var_dump($value->id);
    var_dump($value->product_name);
    var_dump($value->goodsColor);
    // var_dump($value->goodsColor->color_name);
}

$a = clone $goods->find()->one();
var_dump($goods->asJson());


foreach ($goods->find()->one() as $key => $value) {
    // var_dump($value->id);
    var_dump($value->goodsColor[0]->id);
    // var_dump($value->goodsColor->color_name);
}


var_dump($statment->queryInfoLog());