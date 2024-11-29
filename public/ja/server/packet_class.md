# Swoole\Server\Packet

ここでは `Swoole\Server\Packet` についての詳細な紹介です。
## 属性
### $server_socket
サーバー側のファイル記述子 `fd`を返します。この属性は `int` 型の整数です。

```php
Swoole\Server\Packet->server_socket
```
### $server_port
サーバーがlistenしているポート番号 `server_port`を返します。この属性は `int` 型の整数です。

```php
Swoole\Server\Packet->server_port
```
### $dispatch_time
このリクエストデータが到着した時間 `dispatch_time`を返します。この属性は `double` 型です。

```php
Swoole\Server\Packet->dispatch_time
```
### $address
クライアントの住所 `address`を返します。この属性は `string` 型の文字列です。

```php
Swoole\Server\Packet->address
```
### $port
クライアントがlistenしているポート番号 `port`を返します。この属性は `int` 型の整数です。

```php
Swoole\Server\Packet->port
```
### $data
クライアントから伝達されたデータ `data`を返します。この属性は `string` 型の文字列です。

```php
Swoole\Server\Packet->data
```
