<?php
error_reporting(E_ALL);
define('THRIFT_ROOT', __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR);

#加载依赖
if (file_exists(THRIFT_ROOT . "vendor" . DIRECTORY_SEPARATOR . "autoload.php")) {
    require_once THRIFT_ROOT . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";
} elseif (file_exists(THRIFT_ROOT . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "autoload.php")) {
    require_once THRIFT_ROOT . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "autoload.php";
} else {
    echo "请先配置 autoload.php 引入";
    exit;
    require_once "";
}
require_once THRIFT_ROOT . "src" . DIRECTORY_SEPARATOR . "SwooleServer.php";
require_once THRIFT_ROOT . "src" . DIRECTORY_SEPARATOR . "SwooleServerTransport.php";
require_once THRIFT_ROOT . "src" . DIRECTORY_SEPARATOR . "SwooleTransport.php";

#注册函数
$loader = new Thrift\ClassLoader\ThriftClassLoader();
$loader->registerNamespace('Service', THRIFT_ROOT . 'tests' . DIRECTORY_SEPARATOR . 'gen-php');
$loader->register();

use Thrift\Exception\TException;
use Thrift\Protocol\TBinaryProtocol;
use Thrift\Protocol\TMultiplexedProtocol;
use Thrift\Transport\TBufferedTransport;
use Thrift\Transport\TSocket;

try {
    $transport = new TBufferedTransport(new TSocket('localhost', 9191));
    $protocol = new TBinaryProtocol($transport, false, false);
    $test = new \Services\Test\TestClient(new TMultiplexedProtocol($protocol, "Test"));
    $transport->open();
    $res = $test->say("测试");
    echo $res . "\n";
    $res = $test->getlistMap(array(
        array(
            't1' => '内容1',
            't2' => '内容2',
        ),
    ));

    var_dump($res);
    $transport->close();
} catch (TException $tx) {
    print 'TException: ' . $tx->getMessage() . "\n";
}
