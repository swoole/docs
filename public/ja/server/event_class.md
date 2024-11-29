# Swoole\Server\Event

ここでは `Swoole\Server\Event` についての詳細な紹介です。
## 属性
### $reactor_id
使用している `Reactor` スレッドの IDを返します。この属性は `int` 型の整数です。

```php
Swoole\Server\Event->reactor_id
```
### $fd
その接続のファイル記述子 `fd`を返します。この属性は `int` 型の整数です。

```php
Swoole\Server\Event->fd
```
### $dispatch_time
そのリクエストデータの到着時間 `dispatch_time`を返します。この属性は `double` 型です。`onReceive`イベントでのみ、この属性は `0`ではありません。

```php
Swoole\Server\Event->dispatch_time
```
### $data
そのクライアントから送信されたデータ `data`を返します。この属性は `string` 型の文字列です。`onReceive`イベントでのみ、この属性は `null`ではありません。
