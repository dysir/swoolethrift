<?php
namespace SwooleThrift;

use Swoole\Server;
use Thrift\Exception\TTransportException;
use Thrift\Server\TServerTransport;

/**
 *  交互协议
 *      实现 listen 和 accept服务
 */
class SwooleServerTransport extends TServerTransport
{

    public $serv; # swoole 实例
    # 回调服务
    public $arrCallBack;
    /**
     * [__construct 初始化参数]
     * @param [type] $host      [description]
     * @param string $port      [端口 默认9090]
     * @param array  $arrOption
     *        reactor_num 默认为 cpu 核数
     *        worker_num  worker数 默认 cpu * 25 配置为 cpu 倍数
     *        max_connection 最大链接数 swoole 默认 ulimit -n 系统默认 1000000 不得低于100000
     *        dispatch_mode 数据包分发策略，无状态 单worker阻塞，默认 3 开启task可以配置为1
     *        daemonize 守护进程 1 正式必须为 1
     *        buffer_output_size 单个链接发送上限 最大32M
     */
    public function __construct($host, $port = '9090', $arrOption = array())
    {
        $this->serv = new Server($host, $port, SWOOLE_PROCESS, SWOOLE_SOCK_TCP);

        $arrOption['worker_num'] = $arrOption['worker_num'] ?? swoole_cpu_num() * 25;
        $arrOption['buffer_output_size'] = $arrOption['buffer_output_size'] ?? 32 * 1024 * 1024;
        $arrOption['daemonize'] = $arrOption['daemonize'] ?? 0;
        $arrOption['dispatch_mode'] = empty($arrOption['dispatch_mode']) || !in_array($arrOption['dispatch_mode'], array(1, 3)) ? 3 : 1;
        $arrOption['max_connection'] = empty($arrOption['max_connection']) || $arrOption['max_connection'] < 100000 ? 100000 : $arrOption['max_connection'];

        if (empty($arrOption['log_file'])) {
            $arrOption['log_file'] = "/tmp/swooleServerLog_" . date("y-m-d");
        } else {
            $arrOption['log_file'] = $arrOption['log_file'] . date("y-m-d");
        }

        ########### 死链接配置 争议###########
        $arrOption['open_tcp_keepalive'] = 1;
        $arrOption['tcp_keepidle'] = 4;
        $arrOption['tcp_keepinterval'] = 1;
        $arrOption['tcp_keepcount'] = 20;

        ########### 包长检测 ###########

        // $arrOption['open_length_check'] = true;
        // $arrOption['package_max_length'] = 1 * 1024 * 1024;
        // $arrOption['package_length_type'] = 'c';
        // $arrOption['package_length_offset'] = 0;
        // $arrOption['package_body_offset'] = 4;

        $this->serv->set($arrOption);

    }
    public function listen()
    {
        if (!$this->serv->start()) {
            throw new TTransportException('SwooleServer 启动失败 端口可能被占用', TTransportException::UNKNOWN);
        }
    }
    /**
     * [on description]
     * @param  [type] $event  [description]
     * @param  [type] $arrRes [description]
     * @return [type]         [array($object , 'func')]
     */
    public function on($event, $arrRes)
    {
        $this->serv->on($event, $arrRes);
    }
    /**
     * [accept 返回 实例化的 TTransport 传输层 实现了open,read,write 等方法 ]
     * @return [type] [description]
     */
    public function acceptImpl()
    {
        $arrCallBack = $this->arrCallBack;
        $this->on('Receive', $arrCallBack['Receive']);
    }
    public function accept()
    {
        $this->acceptImpl();

        // if ($transport == null) {
        //     throw new TTransportException("accept() may not return NULL");
        // }

        // return $transport;
    }
    /**
     * [registeCallBack 注册回调服务]
     * @param  [type] $serverName [服务名 ]
     * @param  [type] $server     [服务 类型不限]
     * @return [type]
     */
    public function registeCallBack($serverName, $server)
    {
        $this->arrCallBack[$serverName] = $server;
    }
    /**
     * [close 停止服务]
     * @return [type] [description]
     */
    public function close()
    {
        $this->serv->stop(-1);
    }
}
