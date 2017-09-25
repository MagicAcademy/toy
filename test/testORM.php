<?php

require '../vendor/autoload.php';

use orm\DB;
use orm\ORM;

class Goods extends ORM
{

}

$orm = DB::getInstance();
// $statment = $orm->init([
//             'database' => [
//                     'type' => 'mysql',
//                     'host' => 'localhost',
//                     'port' => 3306,
//                     'username' => 'root',
//                     'password' => '',
//                     'dataBaseName' => 'shop',
//                     'charset' => 'utf8'
//                 ]
//             ]);

$statment = $orm->init([
    'database' => [
        'type' => 'pgsql',
        'host' => 'localhost',
        'port' => 5432,
        'username' => 'root',
        'password' => '123456',
        'dataBaseName' => 'shop',
        'charset' => 'utf8'
        ]
]);

$goods = new Goods();

$goods->setDB($statment);

var_dump($goods->find()->one());

var_dump($goods->find()->all());

var_dump($goods->find()->where('id',1)->all());

var_dump($statment->queryInfoLog());