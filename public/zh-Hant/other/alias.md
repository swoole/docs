# 函式別名總覽

## 協程短名稱

簡化協程相關`API`的名稱書寫。可修改`php.ini`設定`swoole.use_shortname=On/Off`來開啟/關閉短名，默認為開啟。

所有的 `Swoole\Coroutine` 前綴的類名映射為`Co`。此外還有下面的一些映射：

### 建立協程

```php
//Swoole\Coroutine::create等價於go函數
go(function () {
	Co::sleep(0.5);
	echo 'hello';
});
go('test');
go([$object, 'method']);
```

### 通道操作

```php
//Coroutine\Channel可以簡寫為chan
$c = new chan(1);
$c->push($data);
$c->pop();
```

### 延遲執行

```php
//Swoole\Coroutine::defer可以直接用defer
defer(function () use ($db) {
    $db->close();
});
```

## 短名稱方法

!> 以下這種方式中`go`和`defer`，Swoole 版本 >= `v4.6.3` 可用

```php
use function Swoole\Coroutine\go;
use function Swoole\Coroutine\run;
use function Swoole\Coroutine\defer;

run(function () {
    defer(function () {
        echo "co1 end\n";
    });
    sleep(1);
    go(function () {
        usleep(100000);
        defer(function () {
            echo "co2 end\n";
        });
        echo "co2\n";
    });
    echo "co1\n";
});
```

## 協程System API

在`4.4.4`版本中系統操作相關的協程`API`從`Swoole\Coroutine`類中，遷移到了`Swoole\Coroutine\System`類中。獨立為一個新模組。為了向下兼容，底層依然保留了在`Coroutine`類之上的別名方法。

* 例如 `Swoole\Coroutine::sleep`對應`Swoole\Coroutine\System::sleep`
* 例如 `Swoole\Coroutine::fgets`對應`Swoole\Coroutine\System::fgets`

## 類短別名映射關係

!> 推薦使用命名空間風格。

| 下划線類名風格                | 命名空間風格                  |
| --------------------------- | --------------------------- |
| swoole_server               | Swoole\Server               |
| swoole_client               | Swoole\Client               |
| swoole_process              | Swoole\Process              |
| swoole_timer                | Swoole\Timer                |
| swoole_table                | Swoole\Table                |
| swoole_lock                 | Swoole\Lock                 |
| swoole_atomic               | Swoole\Atomic               |
| swoole_atomic_long          | Swoole\Atomic\Long          |
| swoole_buffer               | Swoole\Buffer               |
| swoole_redis                | Swoole\Redis                |
| swoole_error                | Swoole\Error                |
| swoole_event                | Swoole\Event                |
| swoole_http_server          | Swoole\Http\Server          |
| swoole_http_client          | Swoole\Http\Client          |
| swoole_http_request         | Swoole\Http\Request         |
| swoole_http_response        | Swoole\Http\Response        |
| swoole_websocket_server     | Swoole\WebSocket\Server     |
| swoole_connection_iterator  | Swoole\Connection\Iterator  |
| swoole_exception            | Swoole\Exception            |
| swoole_http2_request        | Swoole\Http2\Request        |
| swoole_http2_response       | Swoole\Http2\Response       |
| swoole_process_pool         | Swoole\Process\Pool         |
| swoole_redis_server         | Swoole\Redis\Server         |
| swoole_runtime              | Swoole\Runtime              |
| swoole_server_port          | Swoole\Server\Port          |
| swoole_server_task          | Swoole\Server\Task          |
| swoole_table_row            | Swoole\Table\Row            |
| swoole_timer_iterator       | Swoole\Timer\Iterator       |
| swoole_websocket_closeframe | Swoole\Websocket\Closeframe |
| swoole_websocket_frame      | Swoole\Websocket\Frame      |
