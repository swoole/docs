# 函式列表

除了與網絡通訊相關的函數外，Swoole 也提供了一些用於獲取系統資訊的函數供 PHP 程式使用。


## swoole_set_process_name()

用於設置進程的名稱。修改進程名稱後，透過 ps 命令看到的將不再是 `php your_file.php`，而是設定的字符串。

此函數接受一個字符串參數。

此函數與 PHP5.5 提供的 [cli_set_process_title](https://www.php.net/manual/zh/function.cli-set-process-title.php) 功能是相同的。但 `swoole_set_process_name` 可用於 PHP5.2 之上的任意版本。`swoole_set_process_name` 相容性比 `cli_set_process_title` 要差，如果存在 `cli_set_process_title` 函數則優先使用 `cli_set_process_title`。

```php
function swoole_set_process_name(string $name): void
```

使用示例：

```php
swoole_set_process_name("swoole server");
```


### 如何為 Swoole Server 重命名各個進程名稱 <!-- {docsify-ignore} -->

* 在 [onStart](/server/events?id=onstart) 調用時修改主進程名稱
* 在 [onManagerStart](/server/events?id=onmanagerstart) 調用時修改管理進程(`manager`)的名稱
* 在 [onWorkerStart](/server/events?id=onworkerstart) 調用時修改 worker 進程名稱
 
!> 低版本 Linux 内核和 Mac OSX 不支持進程重命名  


## swoole_strerror()

將錯誤碼轉換成錯誤資訊。

函數原型：

```php
function swoole_strerror(int $errno, int $error_type = 1): string
```

錯誤類型:

* `1`：標準的 `Unix Errno`，由系統調用錯誤產生，如 `EAGAIN`、`ETIMEDOUT` 等
* `2`：`getaddrinfo` 錯誤碼，由 `DNS` 操作產生
* `9`：`Swoole` 底層錯誤碼，使用 `swoole_last_error()` 得到

使用示例：

```php
var_dump(swoole_strerror(swoole_last_error(), 9));
```


## swoole_version()

獲取 swoole 擴展的版本號，如 `1.6.10`

```php
function swoole_version(): string
```

使用示例：

```php
var_dump(SWOOLE_VERSION); //全局變量 SWOOLE_VERSION 同樣表示 swoole 擴展版本
var_dump(swoole_version());
/**
回傳值：
string(6) "1.9.23"
string(6) "1.9.23"
**/
```


## swoole_errno()

獲取最近一次系統調用的錯誤碼，等於 `C/C++` 的 `errno` 變量。

```php
function swoole_errno(): int
```

錯誤碼的值與操作系統有關。可使用 `swoole_strerror` 將錯誤轉換為錯誤資訊。


## swoole_get_local_ip()

此函數用於獲取本機所有網絡接口的 IP 地址。

```php
function swoole_get_local_ip(): array
```

使用示例：

```php
// 獲取本機所有網絡接口的 IP 地址
$list = swoole_get_local_ip();
print_r($list);
/**
回傳值
Array
(
      [eno1] => 10.10.28.228
      [br-1e72ecd47449] => 172.20.0.1
      [docker0] => 172.17.0.1
)
**/
```

!> 注意
* 目前只回傳 IPv4 地址，回傳結果會過濾掉本地 loop 地址 127.0.0.1。
* 結果數組是以 interface 名稱為 key 的關聯數組。比如 `array("eth0" => "192.168.1.100")`
* 此函數會實時調用 `ioctl` 系統調用以獲取接口資訊，底層無緩存


## swoole_clear_dns_cache()

清除 swoole 內建的 DNS 緩存，對 `swoole_client` 和 `swoole_async_dns_lookup` 有效。

```php
function swoole_clear_dns_cache()
```


## swoole_get_local_mac()

獲取本機網卡 `Mac` 地址。

```php
function swoole_get_local_mac(): array
```

* 調用成功返回所有網卡的 `Mac` 地址

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

獲取本機 CPU 核數。

```php
function swoole_cpu_num(): int
```

* 調用成功返回 CPU 核數，例如：

```shell
php -r "echo swoole_cpu_num();"
```


## swoole_last_error()

獲取最近一次 Swoole 底層的錯誤碼。

```php
function swoole_last_error(): int
```

可使用 `swoole_strerror(swoole_last_error(), 9)` 將錯誤轉換為錯誤資訊, 完整錯誤資訊列表看 [Swoole 錯誤碼列表](/other/errno?id=swoole)


## swoole_mime_type_add()

將新的 MIME 類型添加到內建的 MIME 類型表上。

```php
function swoole_mime_type_add(string $suffix, string $mime_type): bool
```


## swoole_mime_type_set()

修改某個 MIME 類型, 失敗(如不存在)返回 `false`。

```php
function swoole_mime_type_set(string $suffix, string $mime_type): bool
```


## swoole_mime_type_delete()

刪除某個 MIME 類型, 失敗(如不存在)返回 `false`。

```php
function swoole_mime_type_delete(string $suffix): bool
```


## swoole_mime_type_get()

獲取檔案名對應的 MIME 類型。

```php
function swoole_mime_type_get(string $filename): string
```


## swoole_mime_type_exists()

獲取後綴對應的 MIME 類型是否存在。

```php
function swoole_mime_type_exists(string $suffix): bool
```


## swoole_substr_json_decode()

零拷貝 JSON 反序列化，除去 `$offset` 和 `$length` 以外，其他參數和 [json_decode](https://www.php.net/manual/en/function.json-decode.php) 一致。

!> Swoole 版本 >= `v4.5.6` 可用，從 `v4.5.7` 版本開始需要在編譯時增加 [--enable-swoole-json](/environment?id=通用參數) 參數啟用。使用場景參考[Swoole 4.5.6 支持零拷貝 JSON 或 PHP 反序列化](https://wenda.swoole.com/detail/107587)

```php
function swoole_substr_json_decode(string $packet, int $offset, int $length, bool $assoc = false, int $depth = 512, int $options = 0)
```

  * **示例**

```php
$val = json_encode(['hello' => 'swoole']);
$str = pack('N', strlen($val)) . $val . "\r\n";
$l = strlen($str) - 6;
var_dump(json_decode(substr($str, 4, $l), true));
var_dump(swoole_substr_json_decode($str, 4, $l, true));
```


## swoole_substr_unserialize()

零拷貝 PHP 反序列化，除去 `$offset` 和 `$length` 以外，其他參數和 [unserialize](https://www.php.net/manual/en/function.unserialize.php) 一致。

!> Swoole 版本 >= `v4.5.6` 可用。使用場景參考[Swoole 4.5.6 支持零拷貝 JSON 或 PHP 反序列化](https://wenda.swoole.com/detail/107587)

```php
function swoole_substr_unserialize(string $packet, int $offset, int $length, array $options= [])
```

  * **示例**

```php
$val = serialize('hello');
$str = pack('N', strlen($val)) . $val . "\r\n";
$l = strlen($str) - 6;
var_dump(unserialize(substr($str, 4, $l)));
var_dump(swoole_substr_unserialize($str, 4, $l));
```


## swoole_error_log()

將錯誤資訊輸出到日誌中。`$level`為[日誌等級](/consts?id=日誌等級)。

!> Swoole 版本 >= `v4.5.8` 可用

```php
function swoole_error_log(int $level, string $msg)
```
## swoole_clear_error()

清除套接字的错误或最后一个错误代码上的错误。

!> Swoole 版本 >= `v4.6.0` 可用

```php
function swoole_clear_error()
```

## swoole_coroutine_socketpair()

协程版本的 [socket_create_pair](https://www.php.net/manual/en/function.socket-create-pair.php)。

!> Swoole 版本 >= `v4.6.0` 可用

```php
function swoole_coroutine_socketpair(int $domain , int $type , int $protocol): array|bool
```

## swoole_async_set

此函数可以设置异步`IO`相关的选项。

```php
function swoole_async_set(array $settings)
```

- enable_signalfd 开启和关闭`signalfd`特性的使用

- enable_coroutine 开关内置协程，[详见](/server/setting?id=enable_coroutine)

- aio_core_worker_num 设置 AIO 最小进程数
- aio_worker_num 设置 AIO 最大进程数

## swoole_error_log_ex()

写入指定等级和错误码的日志。

```php
function swoole_error_log_ex(int $level, int $error, string $msg)
```

!> Swoole 版本 >= `v4.8.1` 可用

## swoole_ignore_error()

忽略指定的错误码的错误日志。

```php
function swoole_ignore_error(int $error)
```

!> Swoole 版本 >= `v4.8.1` 可用
