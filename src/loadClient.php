<?php
namespace SwooleThrift;

use Thrift\ClassLoader\ThriftClassLoader;
use Thrift\Exception\TException;
use Thrift\Protocol\TBinaryProtocol;
use Thrift\Protocol\TMultiplexedProtocol;
use Thrift\Transport\TBufferedTransport;
use Thrift\Transport\TSocket;

class loadClient
{
    public $intPort = 9191;
    public $strHost = 'localhost';
    public $objTransport;
    public $objTrotocol;

    public function __construct($arrConfig = array())
    {
        if (empty($arrConfig['gen_php'])) {
            throw new TException("必须定义 gen_php 地址");
        }
        $strPhpPath = $arrConfig['gen_php'];
        $loader = new ThriftClassLoader();
        $loader->registerNamespace('Service', $strPhpPath);
        $loader->register();

        $intPort = $arrConfig['port'] ?? $this->intPort;
        $strHost = $arrConfig['host'] ?? $this->strHost;
        $this->objTransport = new TBufferedTransport(new TSocket($strHost, $intPort));
        $this->objTrotocol = new TBinaryProtocol($this->objTransport, false, false);
    }

    public function registSever($strServerName)
    {

        $serverName = "\\Services\\" . $strServerName . "\\" . $strServerName . "Client";
        if (!class_exists($serverName)) {
            throw new TException("不存在的服务");
        }
        $this->objTransport->open();
        return new $serverName(new TMultiplexedProtocol($this->objTrotocol, $strServerName));
    }
    public function __destruct()
    {
        $this->objTransport->close();
    }
}
