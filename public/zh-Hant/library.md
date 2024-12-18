# 函式庫

Swoole 在 v4 版本後內建了 [函式庫](https://github.com/swoole/library) 模組，**使用 PHP 程式碼編寫核心功能**，使得底層設施更加穩定可靠

!> 該模組也可透過 composer 單獨安裝，單獨安裝使用時需透過`php.ini`設定`swoole.enable_library=Off`關閉擴展內建的 library

目前提供了以下工具組件：

- [Coroutine\WaitGroup](https://github.com/swoole/library/blob/master/src/core/Coroutine/WaitGroup.php) 用於等待並發協程任務，[文檔](/coroutine/wait_group)

- [Coroutine\FastCGI](https://github.com/swoole/library/tree/master/src/core/Coroutine/FastCGI) FastCGI 客戶端，[文檔](/coroutine_client/fastcgi)

- [Coroutine\Server](https://github.com/swoole/library/blob/master/src/core/Coroutine/Server.php) 協程 Server，[文檔](/coroutine/server)

- [Coroutine\Barrier](https://github.com/swoole/library/blob/master/src/core/Coroutine/Barrier.php) 協程屏障，[文檔](/coroutine/barrier)

- [CURL hook](https://github.com/swoole/library/tree/master/src/core/Curl) CURL 協程化，[文檔](/runtime?id=swoole_hook_curl)

- [Database](https://github.com/swoole/library/tree/master/src/core/Database) 各种數據庫連接池和物件代理的高級封裝，[文檔](/coroutine/conn_pool?id=database)

- [ConnectionPool](https://github.com/swoole/library/blob/master/src/core/ConnectionPool.php) 原始連接池，[文檔](/coroutine/conn_pool?id=connectionpool)

- [Process\Manager](https://github.com/swoole/library/blob/master/src/core/Process/Manager.php) 進程管理器，[文檔](/process/process_manager)

- [StringObject](https://github.com/swoole/library/blob/master/src/core/StringObject.php) 、[ArrayObject](https://github.com/swoole/library/blob/master/src/core/ArrayObject.php) 、[MultibyteStringObject](https://github.com/swoole/library/blob/master/src/core/MultibyteStringObject.php) 面向物件風格的 Array 和 String 程式設計

- [functions](https://github.com/swoole/library/blob/master/src/core/Coroutine/functions.php) 提供的一些協程函數，[文檔](/coroutine/coroutine?id=函數)

- [Constant](https://github.com/swoole/library/tree/master/src/core/Constant.php) 常 用設定常數

- [HTTP Status](https://github.com/swoole/library/blob/master/src/core/Http/Status.php) HTTP 狀態碼

## 示範程式碼

[Examples](https://github.com/swoole/library/tree/master/examples)
