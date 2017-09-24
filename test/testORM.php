<?php

require '../vendor/autoload.php';

use orm\DB;
use orm\ORM;

class Goods extends ORM
{

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

$goods = new Goods($statment);

var_dump($goods->find()->one());

var_dump($goods->find()->all());

var_dump($goods->find()->where('id',1)->all());

var_dump($statment->queryInfoLog());