# Process\Manager

Process manager, implemented based on [Process\Pool](/process/process_pool). It can manage multiple processes. Compared to `Process\Pool`, it can easily create multiple processes that perform different tasks, and can control whether each process should be in a coroutine environment.

## Version Support

| Version | Class Name                    | Release Notes                           |
| ------- | -----------------------------  | ---------------------------------------- |
| v4.5.3  | Swoole\Process\ProcessManager  | -                                        |
| v4.5.5  | Swoole\Process\Manager         | Renamed, ProcessManager is an alias for Manager |

!> Available in versions `v4.5.3` and above.

## Usage Example

```php
use Swoole\Process\Manager;
use Swoole\Process\Pool;

$pm = new Manager();

for ($i = 0; $i < 2; $i++) {
    $pm->add(function (Pool $pool, int $workerId) {
    });
}

$pm->start();
```

## Methods

### __construct()

Constructor method.

```php
Swoole\Process\Manager::__construct(int $ipcType = SWOOLE_IPC_NONE, int $msgQueueKey = 0);
```

* **Parameters**

  * **`int $ipcType`**
    * **Function**: Mode of inter-process communication, consistent with `$ipc_type` of `Process\Pool`
    * **Default Value**: `0`
    * **Other Values**: N/A

  * **`int $msgQueueKey`**
    * **Function**: Key of the message queue, consistent with `$msgqueue_key` of `Process\Pool`
    * **Default Value**: N/A
    * **Other Values**: N/A

### setIPCType()

Set the communication method between worker processes.

```php
Swoole\Process\Manager->setIPCType(int $ipcType): self;
```

* **Parameters**

  * **`int $ipcType`**
    * **Function**: Inter-process communication mode
    * **Default Value**: N/A
    * **Other Values**: N/A

### getIPCType()

Get the communication method between worker processes.

```php
Swoole\Process\Manager->getIPCType(): int;
```

### setMsgQueueKey()

Set the `key` of the message queue.

```php
Swoole\Process\Manager->setMsgQueueKey(int $msgQueueKey): self;
```

* **Parameters**

  * **`int $msgQueueKey`**
    * **Function**: Key of the message queue
    * **Default Value**: N/A
    * **Other Values**: N/A

### getMsgQueueKey()

Get the `key` of the message queue.

```php
Swoole\Process\Manager->getMsgQueueKey(): int;
```

### add()

Add a worker process.

```php
Swoole\Process\Manager->add(callable $func, bool $enableCoroutine = false): self;
```

* **Parameters**

  * **`callable $func`**
    * **Function**: Callback function executed by the current process
    * **Default Value**: N/A
    * **Other Values**: N/A

  * **`bool $enableCoroutine`**
    * **Function**: Whether to create a coroutine for this process to execute the callback function
    * **Default Value**: false
    * **Other Values**: N/A

### addBatch()

Add worker processes in batch.

```php
Swoole\Process\Manager->addBatch(int $workerNum, callable $func, bool $enableCoroutine = false): self
```

* **Parameters**

  * **`int $workerNum`**
    * **Function**: Number of processes to add in batch
    * **Default Value**: N/A
    * **Other Values**: N/A

  * **`callable $func`**
    * **Function**: Callback function to be executed by these processes
    * **Default Value**: N/A
    * **Other Values**: N/A

  * **`bool $enableCoroutine`**
    * **Function**: Whether to create coroutines for these processes to execute the callback function
    * **Default Value**: N/A
    * **Other Values**: N/A

### start()

Start the worker processes.

```php
Swoole\Process\Manager->start(): void
```
