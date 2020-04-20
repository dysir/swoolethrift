<?php
namespace SwooleThrift;

use Swoole\Server;
use Thrift\Exception\TTransportException;
use Thrift\Factory\TStringFuncFactory;
use Thrift\Transport\TTransport;

/**
 * 传输层实现，继承 thrift TTransport
 * 按照 thrift 风格重写 swoole 接受数据后的回调对象
 */
class SwooleTransport extends TTransport
{
    /**
     * [__construct 重新构造 接收到的后调对象]
     * @param Server $server    [回调对象 Swoole\Server]
     * @param int    $fd        [description]
     * @param int    $reactorId [description]
     * @param string $data      [description]
     */
    # 回调对象
    public $ser;
    # 链接描述符
    public $fd;
    # tcp连接所在的 Reactor 线程 ID
    public $reactorId;
    # 收到的数据内容，可能是文本或者二进制内容
    public $data;
    # 偏移
    public $offset = 0;

    public function __construct(Server $server, int $fd, int $reactorId, string $data)
    {
        $this->ser = $server;
        $this->fd = $fd;
        $this->reactorId = $reactorId;
        $this->data = $data;
    }

    public function isOpen()
    {
        return $this->ser->exist($this->fd);
    }
    public function open()
    {
        if (!$this->ser->exist($this->fd)) {
            throw new TTransportException("链接不存在", TTransportException::NOT_OPEN);
        }
        return true;
    }
    /**
     * [close 关闭连接]
     * @param  boolean $bool [true 为强制关闭，丢弃发送队列中的数据]
     * @return [type]        [description]
     */
    public function close($bool = false)
    {
        return $this->ser->close($bool);
    }
    /**
     * [read 按长度读取数据]
     * @param  [type] $len [description]
     * @return [type]      [description]
     */
    public function read($len)
    {
        if (strlen($this->data) < $len + $this->offset) {
            throw new TTransportException("超出读取范围", TTransportException::END_OF_FILE);
        }

        $data = substr($this->data, $this->offset, $len);
        $this->offset += $len;
        return $data;
    }

    public function readAll($len)
    {
        $data = '';
        $got = 0;
        while (($got = TStringFuncFactory::create()->strlen($data)) < $len) {
            $data .= $this->read($len - $got);
        }
        return $data;
    }
    /**
     * [readAll 读取全部，TTransport 已实现 不必重写]
     * @return [type] [description]
     */
    # public function readAll()
    /**
     * [write 写入]
     * @return [type] [description]
     */
    public function write($buf)
    {
        if (!$this->ser->send($this->fd, $buf)) {
            throw new TTransportException("发送数据失败", TTransportException::UNKNOWN);
        }
        return true;
    }
    public function flush()
    {

    }
}
