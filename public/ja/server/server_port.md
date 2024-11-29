# Swoole\Server\Port

ここでは `Swoole\Server\Port` についての詳細な説明です。
## 属性
### $host
listenするホストアドレスを返します。この属性は `string` 型の文字列です。

```php
Swoole\Server\Port->host
```
### $port
listenするホストポートを返します。この属性は `int` 型の整数です。

```php
Swoole\Server\Port->port
```
### $type
この `server` タイプを返します。この属性は列挙型で、`SWOOLE_TCP`、`SWOOLE_TCP6`、`SWOOLE_UDP`、`SWOOLE_UDP6`、`SWOOLE_UNIX_DGRAM`、`SWOOLE_UNIX_STREAM`のいずれかです。

```php
Swoole\Server\Port->type
```
### $sock
listenしているソケットを返します。この属性は `int` 型の整数です。

```php
Swoole\Server\Port->sock
```
### $ssl
SSL暗号化が有効かどうかを返します。この属性は `bool` 型です。

```php
Swoole\Server\Port->ssl
```
### $setting
このポートの設定を返します。この属性は `array` 型の配列です。

```php
Swoole\Server\Port->setting
```
### $connections
このポートに接続された全接続を返します。この属性はイテレーターです。

```php
Swoole\Server\Port->connections
```
## 方法
### set()
`Swoole\Server\Port` の運用時に使用する各種パラメータを設定するために使用されます。使用方法は [Swoole\Server->set()](/server/methods?id=set) と同じです。

```php
Swoole\Server\Port->set(array $setting): void
```
### on()
`Swoole\Server\Port` の回调関数を設定するために使用されます。使用方法は [Swoole\Server->on()](/server/methods?id=on) と同じです。

```php
Swoole\Server\Port->on(string $event, callable $callback): bool
```
### getCallback()
設定された回调関数を返します。

```php
Swoole\Server\Port->getCallback(string $name): ?callback
```

  * **引数**

    * `string $name`

      * 機能：回调イベント名
      * 默认値：なし
      * その他：なし

  * **戻り値**

    * 回调関数が存在すると返され、`null`が返される場合はありません。

### getSocket()
現在のソケット `fd` を PHP の `Socket` 对象に変換して返します。

```php
Swoole\Server\Port->getSocket(): Socket|false
```

  * **戻り値**

    * 操作が成功した場合は `Socket` 对象が返され、操作が失敗した場合は `false`が返されます。

!> 注意：Swoole をコンパイルする際に `--enable-sockets` を有効にする必要があります。
