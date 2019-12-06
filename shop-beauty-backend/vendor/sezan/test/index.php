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


$EBusinessID = 1361747;
$AppKey      = '6ce385cb-eb88-4a4b-97a2-7286f0454376';

// $app = new SeZan\Kdniao\Eorder($EBusinessID, $AppKey);
$app = new SeZan\Kdniao\PrintEorder($EBusinessID, $AppKey);

$eorder = [];
$eorder["ShipperCode"] = "SF";
$eorder["OrderCode"] = "01265770038990";
$eorder["PayType"] = 1;
$eorder["ExpType"] = 1;

$sender = [];
$sender["Name"] = "李先生";
$sender["Mobile"] = "18888888888";
$sender["ProvinceName"] = "广东省";
$sender["CityName"] = "深圳市";
$sender["ExpAreaName"] = "福田区";
$sender["Address"] = "赛格广场5401AB";

$receiver = [];
$receiver["Name"] = "李先生";
$receiver["Mobile"] = "18888888888";
$receiver["ProvinceName"] = "广东省";
$receiver["CityName"] = "深圳市";
$receiver["ExpAreaName"] = "福田区";
$receiver["Address"] = "赛格广场5401AB";

$commodityOne = [];
$commodityOne["GoodsName"] = "其他";
$commodity = [];
$commodity[] = $commodityOne;

$eorder["Sender"] = $sender;
$eorder["Receiver"] = $receiver;
$eorder["Commodity"] = $commodity;

var_export($app->parseRequestData($eorder, $sender, $receiver, $commodity)->build());