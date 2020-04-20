<?php
namespace SwooleThrift;

use SwooleThrift\SwooleTransport;
use Thrift\Server\TServer;

/**
 *  应用层 实现 Thrift server
 *  继承 TServer 类 该类定义了 应用层，传输层，传输协议
 *     transport_  需要实现传输协议，并提供listen accept调用接口  如:http socket websocket，实现：TServerTransport
 *     inputTransportFactory_,outputTransportFactory_
 *         传输层定义,实例化TTransportFactory
 *         服务启动时，调用实例中的 getTransport 方法，处理 传输协议 中accept接口的返回，
 *         该接口返回的实例需要实现 TTransport 类，提供 read write 等读写能力
 *     inputProtocolFactory_,outputProtocolFactory_
 *         对接受和读写数据进行拆包解包服务，实现 TProtocolFactory接口
 *         对传输层接受和发送的数据进行二进制处理
 *         用thrift 提供的实现类TBinaryProtocolFactory，一般不需要重新实现
 *         TBinaryProtocolFactory 提供 getProtocol
 *         调用传输层 getTransport 方法返回的数据
 *     processor_ 注册后的业务层
 */
class SwooleServer extends TServer
{
    /**
     * [serve 启动服务]
     * @return [type] [description]
     */
    public function serve()
    {
        $this->transport_->registeCallBack("Receive", [$this, 'onReceiveCallBack']);
        $this->transport_->accept();
        $this->transport_->listen();

    }

    public function onReceiveCallBack($serv, $fd, $from_id, $data)
    {
        $swooleTransportServer = new SwooleTransport($serv, $fd, $from_id, $data);

        $inputTransport = $this->inputTransportFactory_->getTransport($swooleTransportServer);
        $outputTransport = $this->outputTransportFactory_->getTransport($swooleTransportServer);
        $inputProtocol = $this->inputProtocolFactory_->getProtocol($inputTransport);
        $outputProtocol = $this->outputProtocolFactory_->getProtocol($outputTransport);
        $this->processor_->process($inputProtocol, $outputProtocol);
    }
    /**
     * [stop 停止服务]
     * @return [type] [description]
     */
    public function stop()
    {
        $this->transport_->close();
    }
}
