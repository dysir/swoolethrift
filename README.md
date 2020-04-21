## swoole_thrift 

### 企鹅群: 669852173

#### 依赖
- **thrift ^0.13.0**
- **swoole ^4.0.0**
- **php7.2.0 +**
- **GCC4.8 +**

```
thrift 对 php 服务端 socket实现仅支持单进程阻塞处理

使用swoole重新实现socket服务端

二进制传输，传输包不含 VERSION 校验

数据包为 包头int32 四个字节存储包长 + 包体
```


#### 安装测试

> pecl install swoole #已安装的忽略

> composer.json


```
    {
        "repositories":{
            "duyang/swoole_thrift":{
                "type":"git",
                "url":"https://github.com/dysir/swoolethrift.git"
            }
        },
        "require": {
            "duyang/swoole_thrift": "dev-master"
        }
    }

```
>  composer install

>  cd vendor/duyang/swoole_thrift/tests

>  php server.php # 默认监听 9090

> php client.php   # 在启一个终端执行


``` 
    # 正常返回
    from test:测试
    array(1) {
      [0]=>
      array(2) {
        ["t1"]=>
        string(7) "内容1"
        ["t2"]=>
        string(7) "内容2"
      }
    }
```

#### 项目引入客户端

> thrift -r --gen php Test.thrift # 根据thrift文件自动生成客户端代码

test.php
```
<?php
# 引入composer自动加载 
require_once "./vendor/autoload.php";
use Thrift\Exception\TException;

try {
    # 创建客户端服务对象 传入 gen_php 目录地址
    $transport = new \SwooleThrift\loadClient(array(
        'gen_php' => __DIR__ . "/gen-php/",
    ));
    # 注册服务 大小写敏感
    $test = $transport->registSever("Test");
    # 调用
    $res = $test->say("测试");
    var_dump($res) . "\n";
} catch (TException $ex) {
    echo $ex->getMessage();
}

```


### 企鹅群: 669852173
