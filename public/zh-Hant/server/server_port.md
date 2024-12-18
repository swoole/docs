# Swoole\Server\Port

这里是`Swoole\Server\Port`的详细介绍。

## 属性

### $host
返回监听的主机地址，该属性是一个`string`类型的字符串。

```php
Swoole\Server\Port->host
```

### $port
返回监听的主机端口，该属性是一个`int`类型的整数。

```php
Swoole\Server\Port->port
```

### $type
返回这组`server`类型。该属性是一个枚举，返回`SWOOLE_TCP`，`SWOOLE_TCP6`，`SWOOLE_UDP`，`SWOOLE_UDP6`，`SWOOLE_UNIX_DGRAM`，`SWOOLE_UNIX_STREAM`其中一个。

```php
Swoole\Server\Port->type
```

### $sock
返回监听的套接字，该属性是一个`int`类型的整数。

```php
Swoole\Server\Port->sock
```

### $ssl
返回是否开启`ssl`加密，该属性是一个`bool`类型。

```php
Swoole\Server\Port->ssl
```

### $setting
返回对该端口的设置，该属性是一个`array`的数组。

```php
Swoole\Server\Port->setting
```

### $connections
返回连接该端口的全部连接，该属性是一个迭代器。

```php
Swoole\Server\Port->connections
```

## 方法

### set() 

用于设置`Swoole\Server\Port`运行时的各项参数，使用方式与[Swoole\Server->set()](/server/methods?id=set)一样。

```php
Swoole\Server\Port->set(array $setting): void
```

### on() 

用于设置`Swoole\Server\Port`回调函数，使用方式与[Swoole\Server->on()](/server/methods?id=on)一样。

```php
Swoole\Server\Port->on(string $event, callable $callback): bool
```

### getCallback() 

返回设置的回调函数。

```php
Swoole\Server\Port->getCallback(string $name): ?callback
```

  * **参数**

    * `string $name`

      * 功能：回调事件名称
      * 默认值：无
      * 其它值：无

  * **返回值**

    * 返回回调函数表示操作成功，返回`null`表示不存在此回调函数。

### getSocket() 

将当前的套接字`fd`转化成php的`Socket`对象。

```php
Swoole\Server\Port->getSocket(): Socket|false
```

  * **返回值**

    * 返回`Socket`对象表示操作成功，返回`false`表示操作失败。

!> 注意，只有在编译`Swoole`的过程中开启了`--enable-sockets`，该函数才能使用。
