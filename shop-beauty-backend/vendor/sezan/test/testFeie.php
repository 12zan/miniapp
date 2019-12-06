<?php

require '../vendor/autoload.php';

require './Autoload/ClassLoader.php';
$loader = new ClassLoader();

$map = require  './Autoload/autoload_psr4.php';

foreach ($map as $namespace => $path) {
    $loader->setPsr4($namespace, $path);
}

$loader->setUseIncludePath(true);
$loader->register(true);

$user = 'huanhuan@yuanli-inc.com';
$key  = '2RBvnJPdHQubhRqU';

// $app = new SeZan\Feie\Printer($user, $key);

$app = new SeZan\Feie\PrinterOrder($user, $key);

$orderInfo['title']  = '我是一个外卖订单';

$orderInfo['goods'] = [
    ['name' => '米饭', 'uprice' => 10.0, 'number' => 2, 'cprice' => 20.0],
    ['name' => '米饭2', 'uprice' => 10.0, 'number' => 2, 'cprice' => 20.0],
    ['name' => '米饭3', 'uprice' => 10.0, 'number' => 2, 'cprice' => 20.0],
    ['name' => '米饭4', 'uprice' => 10.0, 'number' => 2, 'cprice' => 20.0]
];

$orderInfo['remark']     = '我是备注'; //没有传空

$orderInfo['discounts']  = ['满50减10', '红包减5'];//优惠活动，没有可不传

$orderInfo['count']      = 100;
$orderInfo['address']    = '杭州市西湖区';
$orderInfo['phone']      = '13456706461';
$orderInfo['created_at'] =  '2014-08-08 08:08:08';
$orderInfo['qrstr']      = 'www.12zan.cn';//生成二维码的字符串

$app->setParames(['sn' => '918518094', 'orderInfo' => $orderInfo])->send();
