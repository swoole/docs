# ファンクションリスト

Swooleはネットワーク通信に関連する関数だけでなく、PHPプログラム用にシステム情報を取得するためのいくつかの関数も提供しています。
## swoole_set_process_name()

プロセスの名前を設定するために使用されます。プロセス名を変更した後、psコマンドで見るのは`php your_file.php`ではなく、設定された文字列になります。

この関数は文字列の引数を受け取ります。

この関数はPHP5.5で提供されている[cli_set_process_title](https://www.php.net/manual/zh/function.cli-set-process-title.php)の機能と同じですが、`swoole_set_process_name`はPHP5.2以降の任意のバージョンで使用できます。`swoole_set_process_name`の互換性は`cli_set_process_title`よりも劣りますが、`cli_set_process_title`函数が存在する場合は優先して使用します。

```php
function swoole_set_process_name(string $name): void
```

使用例：

```php
swoole_set_process_name("swoole server");
```
### Swoole Serverの各プロセス名をどのようにリネームするか <!-- {docsify-ignore} -->

* [onStart](/server/events?id=onstart)が呼び出された時にメインプロセスの名前を変更
* [onManagerStart](/server/events?id=onmanagerstart)が呼び出された時に管理プロセス(`manager`)の名前を変更
* [onWorkerStart](/server/events?id=onworkerstart)が呼び出された時にworkerプロセスの名前を変更
 
!> 低版LinuxカーネルとMac OSXではプロセス名のリネームをサポートしていません  
## swoole_strerror()

エラコードをエラ情報に変換します。

関数原型：

```php
function swoole_strerror(int $errno, int $error_type = 1): string
```

エラタイプ:

* `1`：標準の`Unix Errno`で、システムコールエラによって発生し、例えば`EAGAIN`、`ETIMEDOUT`など
* `2`：`getaddrinfo`エラコードで、`DNS`操作によって発生
* `9`：`Swoole`の底層エラコードで、`swoole_last_error()`で取得

使用例：

```php
var_dump(swoole_strerror(swoole_last_error(), 9));
```
## swoole_version()

swoole拡張のバージョン番号を取得します。例えば`1.6.10`のように

```php
function swoole_version(): string
```

使用例：

```php
var_dump(SWOOLE_VERSION); //グローバル変数SWOOLE_VERSIONも同様にswoole拡張のバージョンを表しています
var_dump(swoole_version());
/**
戻り値：
string(6) "1.9.23"
string(6) "1.9.23"
**/
```
## swoole_errno()

最近のシステムコールのエラコードを取得します。これは`C/C++`の`errno`変数に相当します。

```php
function swoole_errno(): int
```

エラコードの値はオペレーティングシステムに関連しています。`swoole_strerror`を使用してエラをエラ情報に変換することができます。
## swoole_get_local_ip()

この関数は、ローカルホストのすべてのネットワークインターフェースのIPアドレスを取得するために使用されます。

```php
function swoole_get_local_ip(): array
```

使用例：

```php
// ローカルホストのすべてのネットワークインターフェースのIPアドレスを取得
$list = swoole_get_local_ip();
print_r($list);
/**
戻り値
Array
(
      [eno1] => 10.10.28.228
      [br-1e72ecd47449] => 172.20.0.1
      [docker0] => 172.17.0.1
)
**/
```

!>注意
* 現在はIPv4アドレスのみを返し、ローカルloopアドレス127.0.0.1はフィルタリングされます。
* 结果のアレイはinterface名をkeyとする関連数组です。例えば `array("eth0" => "192.168.1.100")`
* この関数は`ioctl`システムコールをリアルタイムで呼び出してインターフェース情報を取得しますが、底層にはキャッシュがありません。
## swoole_clear_dns_cache()

swooleの内置DNSキャッシュをクリアします。これは`swoole_client`と`swoole_async_dns_lookup`に有効です。

```php
function swoole_clear_dns_cache()
```
## swoole_get_local_mac()

ローカルネットワークカードの`Mac`アドレスを取得します。

```php
function swoole_get_local_mac(): array
```

* 成功した場合は、すべてのネットワークカードの`Mac`アドレスを返します。

```php
array(4) {
  ["lo"]=>
  string(17) "00:00:00:00:00:00"
  ["eno1"]=>
  string(17) "64:00:6A:65:51:32"
  ["docker0"]=>
  string(17) "02:42:21:9B:12:05"
  ["vboxnet0"]=>
  string(17) "0A:00:27:00:00:00"
}
```
## swoole_cpu_num()

ローカルCPUの核数を取得します。

```php
function swoole_cpu_num(): int
```

* 成功した場合は、CPUの核数を返します。例えば：

```shell
php -r "echo swoole_cpu_num();"
```
## swoole_last_error()

最近のSwooleの底層のエラコードを取得します。

```php
function swoole_last_error(): int
```

`swoole_strerror(swoole_last_error(), 9)`を使用してエラをエラ情報に変換することができます。完全なエラ情報リストは[Swooleエラコードリスト](/other/errno?id=swoole)を参照してください。
## swoole_mime_type_add()

内置のMIMEタイプテーブルに新しいMIMEタイプを追加します。

```php
function swoole_mime_type_add(string $suffix, string $mime_type): bool
```
## swoole_mime_type_set()

特定のMIMEタイプを変更します。失敗（存在しない）の場合は`false`を返します。

```php
function swoole_mime_type_set(string $suffix, string $mime_type): bool
```
## swoole_mime_type_delete()

特定のMIMEタイプを削除します。失敗（存在しない）の場合は`false`を返します。

```php
function swoole_mime_type_delete(string $suffix): bool
```
## swoole_mime_type_get()

ファイル名に対応するMIMEタイプを取得します。

```php
function swoole_mime_type_get(string $filename): string
```
## swoole_mime_type_exists()

サフィックスに対応するMIMEタイプが存在するかどうかを取得します。

```php
function swoole_mime_type_exists(string $suffix): bool
```
## swoole_substr_json_decode()

ゼロキャストJSONデシリアライズを行い、`$offset`と`$length`を除いて、他のパラメータは[json_decode](https://www.php.net/manual/en/function.json-decode.php)と同じです。

!> Swooleバージョンが`v4.5.6`以上で使用可能です。`v4.5.7`バージョンからはコンパイル時に`--enable-swoole-json`パラメータを追加して有効にする必要があります。使用シナリオは[Swoole 4.5.6ではゼロキャストJSONまたはPHPデシリアライズをサポート](https://wenda.swoole.com/detail/107587)を参照してください。

```php
function swoole_substr_json_decode(string $packet, int $offset, int $length, bool $assoc = false, int $depth = 512, int $options = 0)
```

  * **例**

```php
,val = json_encode(['hello' => 'swoole']);
$str = pack('N', strlen($val)) . $val . "\r\n";
$l = strlen($str) - 6;
var_dump(json_decode(substr($str, 4, $l), true));
var_dump(swoole_substr_json_decode($str, 4, $l, true));
```
## swoole_substr_unserialize()

ゼロキャストPHPデシリアライズを行い、`$offset`と`$length`を除いて、他のパラメータは[unserialize](https://www.php.net/manual/en/function.unserialize.php)と同じです。

!> Swooleバージョンが`v4.5.6`以上で使用可能です。使用シナリオは[Swoole 4.5.6ではゼロキャストJSONまたはPHPデシリアライズをサポート](https://wenda.swoole.com/detail/107587)を参照してください。

```php
function swoole_substr_unserialize(string $packet, int $offset, int $length, array $options= [])
```

  * **例**

```php
,val = serialize('hello');
$str = pack('N', strlen($val)) . $val . "\r\n";
$l = strlen($str) - 6;
var_dump(unserialize(substr($str, 4, $l)));
var_dump(swoole_substr_unserialize($str, 4, $l));
```
## swoole_error_log()

エラ情報をログに出力します。`$level`は[ログレベル](/consts?id=日志レベル)です。

!> Swooleバージョンが`v4.5.8`以上で使用可能です

```php
function swoole_error_log(int $level, string $msg)
```
## swoole_clear_error()

ソケットのエラや最後のエラコードのエラをクリアします。

!> Swooleバージョンが`v4.6.0`以上で使用可能です

```php
function swoole_clear_error()
```
## swoole_coroutine_socketpair()

协程版の[socket_create_pair](https://www.php.net/manual/en/function.socket-create-pair.php)です。

!> Swooleバージョンが`v4.6.0`以上で使用可能です

```php
function swoole_coroutine_socketpair(int $domain , int $type , int $protocol): array|bool
```
## swoole_async_set

この関数は、非同期`IO`関連のオプションを設定することができます。

```php
function swoole_async_set(array $settings)
```- enable_signalfdは`signalfd`特徴の使用を開始または停止します- enable_coroutineは内置の協程を開始または停止します。[詳細は](/server/setting?id=enable_coroutine)- aio_core_worker_numはAIOの最小プロセス数を設定します
- aio_worker_numはAIOの最大プロセス数を設定します
## swoole_error_log_ex()

指定されたレベルとエラコードのログを書き込みます。

```php
function swoole_error_log_ex(int $level, int $error, string $msg)
```

!> Swooleバージョンが`v4.8.1`以上で使用可能です
## swoole_ignore_error()

指定されたエラコードのエラログを無視します。

```php
function swoole_ignore_error(int $error)
```

!> Swooleバージョンが`v4.8.1`以上で使用可能です
