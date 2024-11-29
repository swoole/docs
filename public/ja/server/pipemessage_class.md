# Swoole\Server\PipeMessage

こちらでは`Swoole\Server\PipeMessage`についての詳細な説明です。
## 属性
### $source_worker_id
データの送信元である`worker`プロセスのIDを返します。この属性は`int`型の整数です。

```php
Swoole\Server\PipeMessage->source_worker_id
```
### $dispatch_time
このリクエストデータが到着した時間`dispatch_time`を返します。この属性は`double`型です。

```php
Swoole\Server\PipeMessage->dispatch_time
```
### $data
この接続が携帯するデータ`data`を返します。この属性は`string`型の文字列です。

```php
Swoole\Server\PipeMessage->data
```
