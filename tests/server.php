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

use SwooleThrift\SwooleServer;
use SwooleThrift\SwooleServerTransport;
use Thrift\Exception\TException;
use Thrift\Factory\TBinaryProtocolFactory;
use Thrift\Factory\TTransportFactory;
use Thrift\TMultiplexedProcessor;

class Test implements \Services\Test\TestIf
{
    public function say($name)
    {
        return "from test:" . $name;
    }
    public function getlistMap($arrlistMap)
    {
        return $arrlistMap;
    }
}

try {
    $processor = new TMultiplexedProcessor();
    $processor->registerProcessor("Test", new \Services\Test\TestProcessor(new Test()));

    $transportFactory = new TTransportFactory();
    $protocolFactory = new TBinaryProtocolFactory(false, false);

    $transport = new SwooleServerTransport('localhost', 9191);

    $server = new SwooleServer($processor, $transport, $transportFactory, $transportFactory, $protocolFactory, $protocolFactory);

    $server->serve();
} catch (TException $tx) {
    print 'TException: ' . $tx->getMessage() . "\n";
}
