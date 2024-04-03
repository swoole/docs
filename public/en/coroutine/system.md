# Coroutine\System

Coroutine encapsulation of system-related APIs. This module is available after the official release of `v4.4.6`. Most APIs are implemented based on an AIO thread pool.

!> For versions before `v4.4.6`, please use `Co` alias or `Swoole\Coroutine` for calls, such as `Co::sleep` or `Swoole\Coroutine::sleep`.  
Starting from `v4.4.6`, it is **recommended** to use `Co\System::sleep` or `Swoole\Coroutine\System::sleep`.  
This change aims to standardize the namespace, but also ensures backward compatibility (meaning the previous syntax before `v4.4.6` is still valid, no need to modify).
## Methods
### statvfs()

Get file system information.

!> Available in Swoole version >= v4.2.5

```php
Swoole\Coroutine\System::statvfs(string $path): array|false
```

  * **Parameters** 

    * **`string $path`**
      * **Description**: Directory where the file system is mounted (e.g., `/`, can be obtained using `df` and `mount -l` commands)
      * **Default**: None
      * **Other values**: None

  * **Usage Example**

    ```php
    Swoole\Coroutine\run(function () {
        var_dump(Swoole\Coroutine\System::statvfs('/'));
    });
    
    // array(11) {
    //   ["bsize"]=>
    //   int(4096)
    //   ["frsize"]=>
    //   int(4096)
    //   ["blocks"]=>
    //   int(61068098)
    //   ["bfree"]=>
    //   int(45753580)
    //   ["bavail"]=>
    //   int(42645728)
    //   ["files"]=>
    //   int(15523840)
    //   ["ffree"]=>
    //   int(14909927)
    //   ["favail"]=>
    //   int(14909927)
    //   ["fsid"]=>
    //   int(1002377915335522995)
    //   ["flag"]=>
    //   int(4096)
    //   ["namemax"]=>
    //   int(255)
    // }
    ```
### fread()

Read file in coroutine mode.

```php
Swoole\Coroutine\System::fread(resource $handle, int $length = 0): string|false
```

!> The `fread` method does not support non-file type `stream` (such as `STDIN` or `Socket`) in versions below `v4.0.4`. Please do not use `fread` to operate on such resources.  
For versions above `v4.0.4`, the `fread` method supports non-file type `stream` resources. The underlying mechanism will automatically choose to use `AIO` thread pool or the [Event Loop](/learn?id=what-is-an-event-loop) to implement it.

  * **Parameters** 

    * **`resource $handle`**
      * **Description**: File handle [must be a file type `stream` resource opened by `fopen`]
      * **Default**: None
      * **Other values**: None

    * **`int $length`**
      * **Description**: Length to read [default is `0`, meaning reading the entire content of the file]
      * **Default**: `0`
      * **Other values**: None

  * **Return Value** 

    * Returns the string content on success; returns `false` on failure.

  * **Usage Example**  

    ```php
    $fp = fopen(__FILE__, "r");
    Swoole\Coroutine\run(function () use ($fp)
    {
        $r = Swoole\Coroutine\System::fread($fp);
        var_dump($r);
    });
    ```
### fwrite()

Write data to a file in coroutine mode.

```php
Swoole\Coroutine\System::fwrite(resource $handle, string $data, int $length = 0): int|false
```

!> `fwrite` method does not support non-file type `stream`, such as `STDIN`, `Socket`, in versions prior to `v4.0.4`. Please do not use `fwrite` to operate on such resources.  
Starting from `v4.0.4`, the `fwrite` method supports non-file type `stream` resources. The underlying system will automatically choose to use either the `AIO` thread pool or [EventLoop](/learn?id=what-is-eventloop) depending on the type of `stream`.

  * **Parameters** 

    * **`resource $handle`**
      * **Function**: File handle [must be a file type `stream` opened with `fopen`]
      * **Default**: None
      * **Other values**: None

    * **`string $data`**
      * **Function**: Data content to be written [can be text or binary data]
      * **Default**: None
      * **Other values**: None

    * **`int $length`**
      * **Function**: Length to be written [default is `0`, indicating writing all content in `$data`, `$length` must be less than the length of `$data`]
      * **Default**: `0`
      * **Other values**: None

  * **Return Value** 

    * Returns the length of data written if successful, `false` on failure.

  * **Usage Example**  

    ```php
    $fp = fopen(__DIR__ . "/test.data", "a+");
    Swoole\Coroutine\run(function () use ($fp)
    {
        $r = Swoole\Coroutine\System::fwrite($fp, "hello world\n", 5);
        var_dump($r);
    });
    ```
### fgets()

Read file content line by line in coroutine mode.

It uses the `php_stream` buffer internally with a default size of `8192` bytes, and you can set the buffer size using `stream_set_chunk_size`.

```php
Swoole\Coroutine\System::fgets(resource $handle): string|false
```

!> `fgets` function can only be used for file type `stream` resources. Available in Swoole version >= `v4.4.4`

  * **Parameters** 

    * **`resource $handle`**
      * **Description**: File handle [must be a file type `stream` resource opened with `fopen`]
      * **Default value**: None
      * **Other values**: None

  * **Return Values** 

    * If it reads to `EOL` (`\r` or `\n`), it will return a line of data including `EOL`.
    * If it doesn't read to `EOL` but the content length exceeds the `php_stream` buffer size of `8192` bytes, it will return `8192` bytes of data excluding `EOL`.
    * When reaching the end of the file (`EOF`), it returns an empty string. You can use `feof` to check if the file has been fully read.
    * Returns `false` on read failure. Use the [swoole_last_error](/functions?id=swoole_last_error) function to get the error code.

  * **Usage Example**  

    ```php
    $fp = fopen(__DIR__ . "/defer_client.php", "r");
    Swoole\Coroutine\run(function () use ($fp)
    {
        $r = Swoole\Coroutine\System::fgets($fp);
        var_dump($r);
    });
    ```
### readFile()

Coroutine way of reading a file.

```php
Swoole\Coroutine\System::readFile(string $filename): string|false
```

  * **Parameters**

    * **`string $filename`**
      * **Function**: File name
      * **Default value**: None
      * **Other values**: None

  * **Return Value**

    * Returns the content of the file as a string on success, returns `false` on failure, error information can be obtained using [swoole_last_error](/functions?id=swoole_last_error)
    * The `readFile` method does not have a size limit. The read content will be stored in memory, so reading very large files may consume too much memory.

  * **Usage Example**

    ```php
    $filename = __DIR__ . "/defer_client.php";
    Swoole\Coroutine\run(function () use ($filename)
    {
        $r = Swoole\Coroutine\System::readFile($filename);
        var_dump($r);
    });
    ```
### writeFile()

Write to file using coroutines.

```php
Swoole\Coroutine\System::writeFile(string $filename, string $fileContent, int $flags): bool
```

  * **Parameters** 

    * **`string $filename`**
      * **Description**：File name【must have write permission, will automatically create the file if it does not exist. Returns `false` immediately if failed to open the file】
      * **Default Value**：None
      * **Other Values**：None

    * **`string $fileContent`**
      * **Description**：Content to write to the file【maximum `4M` can be written】
      * **Default Value**：None
      * **Other Values**：None

    * **`int $flags`**
      * **Description**：Options for writing【by default, it clears the current file content. Can use `FILE_APPEND` to append to the end of the file】
      * **Default Value**：None
      * **Other Values**：None

  * **Return Value** 

    * Returns `true` on successful write
    * Returns `false` on failure to write

  * **Usage Example**  

    ```php
    $filename = __DIR__ . "/defer_client.php";
    Swoole\Coroutine\run(function () use ($filename)
    {
        $w = Swoole\Coroutine\System::writeFile($filename, "hello swoole!");
        var_dump($w);
    });
    ```
### sleep()

Enters a waiting state.

Equivalent to the `sleep` function in `PHP`, the difference is that `Coroutine::sleep` is implemented by the coroutine scheduler. The underlying operation will `yield` the current coroutine, relinquishing the time slice, and add an asynchronous timer. When the timeout period is reached, it will `resume` the current coroutine to continue execution.

Using the `sleep` interface can easily implement timeout waiting functionality.

```php
Swoole\Coroutine\System::sleep(float $seconds): void
```

  * **Parameters** 

    * **`float $seconds`**
      * **Function**: The time to sleep for【Must be greater than `0`, with a maximum limit of one day (`86400` seconds)】.
      * **Unit**: Seconds, with a minimum precision of milliseconds (`0.001` seconds).
      * **Default Value**: None.
      * **Other Values**: None.

  * **Usage Example**  

    ```php
    $server = new Swoole\Http\Server("127.0.0.1", 9502);

    $server->on('Request', function($request, $response) {
        // Wait for 200ms before sending a response to the browser
        Swoole\Coroutine\System::sleep(0.2);
        $response->end("<h1>Hello Swoole!</h1>");
    });

    $server->start();
    ```  
### exec()

Execute a shell command. Automatically performs [coroutine scheduling](/coroutine?id=coroutine-scheduling) at the underlying level.

```php
Swoole\Coroutine\System::exec(string $cmd): array
```

  * **Parameters** 

    * **`string $cmd`**
      * **Function**: The shell command to be executed
      * **Default Value**: None
      * **Other Values**: None

  * **Return Value**

    * Returns an array containing the process's exit status code, signal, and output content if successful, or `false` if failed.

    ```php
    array(
        'code'   => 0,  // Process exit status code
        'signal' => 0,  // Signal
        'output' => '', // Output content
    );
    ```

  * **Usage Example**  

    ```php
    Swoole\Coroutine\run(function() {
        $ret = Swoole\Coroutine\System::exec("md5sum ".__FILE__);
    });
    ```

  * **Note**

  !>If the execution of the script command takes too long, it will result in a timeout and exit. In this case, you can solve this problem by increasing the [socket_read_timeout](/coroutine_client/init?id=timeout-rules).
### gethostbyname()

Resolve a domain name to an IP address. Simulate implementation based on synchronous thread pools, automatically underlying [coroutine scheduling](/coroutine?id=coroutine-scheduling).

```php
Swoole\Coroutine\System::gethostbyname(string $domain, int $family = AF_INET, float $timeout = -1): string|false
```

  * **Parameters** 

    * **`string $domain`**
      * **Purpose**: Domain name
      * **Default value**: None
      * **Other values**: None

    * **`int $family`**
      * **Purpose**: Address family [`AF_INET` returns `IPv4` address, using `AF_INET6` returns `IPv6` address]
      * **Default value**: `AF_INET`
      * **Other values**: `AF_INET6`

    * **`float $timeout`**
      * **Purpose**: Timeout value
      * **Unit**: Seconds with minimum precision of milliseconds (`0.001` seconds)
      * **Default value**: `-1`
      * **Other values**: None

  * **Return Value**

    * Returns the IP address corresponding to the domain name on success, `false` on failure. Error message can be retrieved using [swoole_last_error](/functions?id=swoole_last_error).

    ```php
    array(
        'code'   => 0,  // Exit status code
        'signal' => 0,  // Signal
        'output' => '', // Output content
    );
    ```

  * **Extensions**

    * **Timeout Control**

      The `$timeout` parameter can control the coroutine waiting time. If no result is returned within the specified time, the coroutine will immediately return `false` and continue executing downstream. In the underlying implementation, this asynchronous task will be marked as `cancel`, and `gethostbyname` will continue to execute in the `AIO` thread pool.

      You can modify `/etc/resolv.conf` to set the timeout for the underlying `C` functions of `gethostbyname` and `getaddrinfo`. For details, please refer to [Setting DNS Resolution Timeout and Retries](/learn_other?id=dns-resolution-timeout-and-retries)

  * **Usage Example**  

    ```php
    Swoole\Coroutine\run(function () {
        $ip = Swoole\Coroutine\System::gethostbyname("www.baidu.com", AF_INET, 0.5);
        echo $ip;
    });
    ```  
### getaddrinfo()

Perform DNS resolution to obtain the `IP` address corresponding to a domain name.

Different from `gethostbyname`, `getaddrinfo` supports more parameter settings and can return multiple `IP` results.

```php
Swoole\Coroutine\System::getaddrinfo(string $domain, int $family = AF_INET, int $socktype = SOCK_STREAM, int $protocol = STREAM_IPPROTO_TCP, string $service = null, float $timeout = -1): array|false
```

  * **Parameters** 

    * **`string $domain`**
      * **Function**: domain name
      * **Default value**: none
      * **Other values**: none

    * **`int $family`**
      * **Function**: domain family [`AF_INET` returns `IPv4` addresses, `AF_INET6` returns `IPv6` addresses]
      * **Default value**: none
      * **Other values**: none
      
      !> Refer to the `man getaddrinfo` document for other parameter settings

    * **`int $socktype`**
      * **Function**: protocol type
      * **Default value**: `SOCK_STREAM`
      * **Other values**: `SOCK_DGRAM`, `SOCK_RAW`

    * **`int $protocol`**
      * **Function**: protocol
      * **Default value**: `STREAM_IPPROTO_TCP`
      * **Other values**: `STREAM_IPPROTO_UDP`, `STREAM_IPPROTO_STCP`, `STREAM_IPPROTO_TIPC`, `0`

    * **`string $service`**
      * **Function**:
      * **Default value**: none
      * **Other values**: none

    * **`float $timeout`**
      * **Function**: timeout duration
      * **Value unit**: seconds, with a minimum precision of milliseconds (`0.001` seconds)
      * **Default value**: `-1`
      * **Other values**: none

  * **Return Value**

    * Returns an array of multiple `IP` addresses on success, `false` on failure.

  * **Usage Example**  

    ```php
    Swoole\Coroutine\run(function () {
        $ips = Swoole\Coroutine\System::getaddrinfo("www.baidu.com");
        var_dump($ips);
    });
    ```
### dnsLookup()

Domain address query.

Different from `Coroutine\System::gethostbyname`, `Coroutine\System::dnsLookup` is directly implemented based on `UDP` client network communication rather than using the `gethostbyname` function provided by `libc`.

!> Available for Swoole version >= `v4.4.3`, the underlying system will read `/etc/resolve.conf` to obtain the `DNS` server address, currently only supporting `AF_INET(IPv4)` domain resolution. Starting from Swoole version >= `v4.7`, you can use the third parameter to support `AF_INET6(IPv6)`.

```php
Swoole\Coroutine\System::dnsLookup(string $domain, float $timeout = 5, int $type = AF_INET): string|false
```

  * **Parameters**

    * **`string $domain`**
      * **Function**: domain name
      * **Default Value**: none
      * **Other Values**: none

    * **`float $timeout`**
      * **Function**: timeout duration
      * **Unit**: seconds, with minimum precision as milliseconds (`0.001` seconds)
      * **Default Value**: `5`
      * **Other Values**: none

    * **`int $type`**
        * **Unit**: seconds, with minimum precision as milliseconds (`0.001` seconds)
        * **Default Value**: `AF_INET`
        * **Other Values**: `AF_INET6`

    !> The `$type` parameter is available in Swoole version >= `v4.7`.

  * **Return Value**

    * Returns the corresponding IP address upon successful resolution
    * Returns `false` upon failure, and you can retrieve error information using [swoole_last_error](/functions?id=swoole_last_error)

  * **Common Errors**

    * `SWOOLE_ERROR_DNSLOOKUP_RESOLVE_FAILED`: Unable to resolve this domain name, query failed
    * `SWOOLE_ERROR_DNSLOOKUP_RESOLVE_TIMEOUT`: Resolution timed out, the DNS server may be malfunctioning, and unable to return results within the specified time

  * **Usage Example**  

    ```php
    Swoole\Coroutine\run(function () {
        $ip = Swoole\Coroutine\System::dnsLookup("www.baidu.com");
        echo $ip;
    });
    ```  
### wait()

Corresponding to the original [Process::wait](/process/process?id=wait), the difference is that this API is a coroutine version, which will suspend the coroutine. It can replace the `Swoole\Process::wait` and `pcntl_wait` functions.

!> Available in Swoole version >= `v4.5.0`

```php
Swoole\Coroutine\System::wait(float $timeout = -1): array|false
```

* **Parameters** 

    * **`float $timeout`**
      * **Function**: Timeout time, negative indicates no timeout
      * **Unit**: Seconds, with minimum precision of milliseconds (`0.001` seconds)
      * **Default Value**: `-1`
      * **Other Values**: None

* **Return Value**

  * If successful, it will return an array containing the child process's `PID`, exit status code, and which `KILL` signal was used
  * If failed, it returns `false`

!> After each child process is started, the parent process must dispatch a coroutine to call `wait()` (or `waitPid()`) to collect it, otherwise the child process will become a zombie process, wasting the operating system's process resources.  
If coroutines are used, processes must be created first, then coroutines should be started within processes. It should not be the other way around, otherwise forking with coroutines will become very complex, making it very difficult for the underlying system to handle.

* **Example**

```php
use Swoole\Coroutine;
use Swoole\Coroutine\System;
use Swoole\Process;

$process = new Process(function () {
    echo 'Hello Swoole';
});
$process->start();

Coroutine\run(function () use ($process) {
    $status = System::wait();
    assert($status['pid'] === $process->pid);
    var_dump($status);
});
```
### waitPid()

This API is basically similar to the `wait` method mentioned above, but it allows specifying a specific process to wait for.

!> Available in Swoole version `v4.5.0` and later.

```php
Swoole\Coroutine\System::waitPid(int $pid, float $timeout = -1): array|false
```

* **Parameters**

    * **`int $pid`**
      * **Description**: Process ID
      * **Default value**: `-1` (represents any process, equivalent to `wait` in this case)
      * **Other values**: Any natural number

    * **`float $timeout`**
      * **Description**: Timeout value, negative values mean no timeout
      * **Unit**: Seconds with millisecond precision (e.g., `0.001` seconds)
      * **Default value**: `-1`
      * **Other values**: None

* **Return Value**

  * If the operation is successful, an array containing the child process's `PID`, exit status code, and the signal causing the exit `KILL` will be returned
  * If failed, it returns `false`

!> After each child process is started, the parent process must dispatch a coroutine call to `wait()` (or `waitPid()`) for recovery. Otherwise, the child process will become a zombie process, wasting the operating system's process resources.

* **Example**

```php
use Swoole\Coroutine;
use Swoole\Coroutine\System;
use Swoole\Process;

$process = new Process(function () {
    echo 'Hello Swoole';
});
$process->start();

Coroutine\run(function () use ($process) {
    $status = System::waitPid($process->pid);
    var_dump($status);
});
```
### waitSignal()

Coroutine version signal listener, which will block the current coroutine until the signal is triggered, can replace `Swoole\Process::signal` and `pcntl_signal` functions.

!> Available in Swoole version >= `v4.5.0`

```php
Swoole\Coroutine\System::waitSignal(int $signo, float $timeout = -1): bool
```

  * **Parameters**

    * **`int $signo`**
      * **Description**: Type of signal
      * **Default value**: None
      * **Other values**: SIG series constants, such as `SIGTERM`, `SIGKILL`, etc.

    * **`float $timeout`**
      * **Description**: Timeout period, negative value means no timeout
      * **Unit**: Seconds, with a minimum precision of milliseconds (`0.001` seconds)
      * **Default value**: `-1`
      * **Other values**: None

  * **Return Value**

    * Returns `true` when the signal is received
    * Returns `false` if the signal is not received within the timeout

  * **Example**

```php
use Swoole\Coroutine;
use Swoole\Coroutine\System;
use Swoole\Process;

$process = new Process(function () {
    Coroutine\run(function () {
        $bool = System::waitSignal(SIGUSR1);
        var_dump($bool);
    });
});
$process->start();
sleep(1);
$process::kill($process->pid, SIGUSR1);
```
### `waitEvent()`

Coroutine version of signal listener, which will block the current coroutine until the signal is triggered. Wait for IO events, can replace related functions in `swoole_event`.

!> Swoole version >= `v4.5` is required.

```php
Swoole\Coroutine\System::waitEvent(mixed $socket, int $events = SWOOLE_EVENT_READ, float $timeout = -1): int | false
```

* **Parameters**

    * **`mixed $socket`**
      * **Description**: File descriptor (any type that can be converted to a fd, such as Socket object, resource, etc.)
      * **Default**: None
      * **Other values**: None

    * **`int $events`**
      * **Description**: Event type
      * **Default**: `SWOOLE_EVENT_READ`
      * **Other values**: `SWOOLE_EVENT_WRITE` or `SWOOLE_EVENT_READ | SWOOLE_EVENT_WRITE`

    * **`float $timeout`**
      * **Description**: Timeout, negative value means never timeout
      * **Value unit**: Seconds, minimum precision is milliseconds (`0.001` seconds)
      * **Default**: `-1`
      * **Other values**: None

* **Return Value**

  * Returns the sum of triggered event types (may be multiple bits), related to the value passed in parameter `$events`
  * Returns `false` on failure, error information can be obtained using [swoole_last_error](https://www.swoole.co.uk/docs/pages/functions?id=swoole_last_error)

* **Example**

> Synchronous blocking code can be turned into coroutine non-blocking code using this API.

```php
use Swoole\Coroutine;

Coroutine\run(function () {
    $client = stream_socket_client('tcp://www.qq.com:80', $errno, $errstr, 30);
    $events = Coroutine::waitEvent($client, SWOOLE_EVENT_READ | SWOOLE_EVENT_WRITE);
    assert($events === SWOOLE_EVENT_WRITE);
    fwrite($client, "GET / HTTP/1.1\r\nHost: www.qq.com\r\n\r\n");
    $events = Coroutine::waitEvent($client, SWOOLE_EVENT_READ);
    assert($events === SWOOLE_EVENT_READ);
    $response = fread($client, 8192);
    echo $response;
});
```
