# Swoole\Async\Client

`Swoole\Async\Client`以下简称`Client`，是一个异步非阻塞的`TCP/UDP/UnixSocket`网络客户端，异步客户端需要设置事件回调函数，而不是同步等待。

- 异步客户端是`Swoole\Client`的子类，可调用部分同步阻塞客户端的方法  
- 仅在 `6.0` 或以上版本可用


## 完整示例

```php
$cli = new Swoole\Async\Client(SWOOLE_SOCK_TCP);

$client->on("connect", function(Swoole\Async\Client $client) {
    Assert::true($client->isConnected());
    $client->send(RandStr::gen(1024, RandStr::ALL));
});

$client->on("receive", function(Swoole\Async\Client $client, string $data){
    $recv_len = strlen($data);
    $client->send(RandStr::gen(1024, RandStr::ALL));
    $client->close();
    Assert::false($client->isConnected());
});

$client->on("error", function(Swoole\Async\Client $client) {
    echo "error";
});

$client->on("close", function(Swoole\Async\Client $client) {
    echo "close";
});

$client->connect("127.0.0.1", 9501, 0.2);
```

## 方法

本页仅列出与 `Swoole\Client` 存在差异的方法，子类未修改的方法，请参考[同步阻塞客户端](client.md)。

### __construct()

构造方法，参考父类构造方法

```php
Swoole\Async\Client::__construct(int $sock_type, bool $async = true, string $key);
```

> 异步客户端的第二个参数必须为`true`

### on()

注册`Client`的事件回调函数。

```php
Swoole\Async\Client->on(string $event, callable $callback): bool
```

!> 重复调用`on`方法时会覆盖上一次的设定

  * **参数**

    * `string $event`

      * 功能：回调事件名称，大小写不敏感
      * 默认值：无
      * 其它值：无

    * `callable $callback`

      * 功能：回调函数
      * 默认值：无
      * 其它值：无

      !> 可以是函数名的字符串，类静态方法，对象方法数组，匿名函数 参考[此节](/learn?id=几种设置回调函数的方式)。
  
  * **返回值**

    * 返回`true`表示操作成功，返回`false`表示操作失败。


### isConnected()
判断当前客户端是否已与服务端建立了连接。

```php
Swoole\Async\Client->isConnected(): bool
```

* 返回`true`表示已连接，返回`false`表示未连接

### sleep()

暂时停止接收数据，调用后将从事件循环中移出，不再触发数据接收事件，除非调用`wakeup()`方法恢复。

```php
Swoole\Async\Client->sleep(): bool
```

* 返回`true`表示操作成功，返回`false`表示操作失败

### wakeup()

恢复接收数据，调用后将加入事件循环。

```php
Swoole\Async\Client->wakeup(): bool
```

* 返回`true`表示操作成功，返回`false`表示操作失败


### enableSSL()

动态开启`SSL/TLS`加密，一般用于`startTLS`客户端。在连接建立后先发送明文数据，然后再开启加密传输。

```php
Swoole\Async\Client->enableSSL(callable $callback): bool
```

* 此函数只能在`connect`成功之后被调用
* 异步客户端必须设置`$callback`，在`SSL`握手完成后回调此函数
* 返回`true`表示操作成功，返回`false`表示操作失败

## 回调事件

### connect
在连接建立后触发，若设置了`HTTP`或`Socks5`代理和`SSL`隧道加密，则在代理握手完成且`SSL`加密握手完成后触发。

```php
$client->on("connect", function(Swoole\Async\Client $client) {
    Assert::true($client->isConnected());    
});
```

在此事件回调后使用`isConnected()`判断将返回`true`


### error 
在连接建立失败后触发，可获取读取`$client->errCode`获取错误信息。
```php
$client->on("error", function(Swoole\Async\Client $client) {
    var_dump($client->errCode);  
});
```

- 请注意`connect`和`error`只会触发其中之一，连接建立成功或失败，只能存在一个结果
- `Client::connect()`可能会直接返回`false`，表示连接失败，这时将不会执行`error`回调，请务必检查`connect`调用返回值
- `error`事件是异步结果，从发起连接到`error`事件触发中间会存在一定`IO`等待时间
- `connect`返回失败表示立即失败，此错误由操作系统直接触发，中间不存在任何`IO`等待时间

### receive
接收到数据后触发

```php
$client->on("receive", function(Swoole\Async\Client $client, string $data){
    var_dump(strlen($data));
});
```

- 若未设置任何协议，例如`EOF`或`LENGTH`，最大返回数据长度为`64K`
- 若设置了协议处理参数，最大数据长度为`package_max_length`参数设置，默认为`2M`
- `$data`一定不为空，如果收到了系统错误或者连接关闭，将触发`close`事件

### close
连接关闭时触发

```php
$client->on("close", function(Swoole\Async\Client $client) {
    echo "close";
});
```