# Process\Manager

Manager proses, berbasis [Process\Pool](/process/process_pool). Bisa mengelola banyak proses. Dibanding `Process\Pool`, bisa dengan gampang bikin banyak proses yang jalanin tugas berbeda, dan bisa kontrol apakah setiap proses mau pake lingkungan coroutine.

## Dukungan Versi

| Versi | Nama Class                     | Catatan Update                          |
| ----- | ------------------------------ | --------------------------------------- |
| v4.5.3 | Swoole\Process\ProcessManager | -                                       |
| v4.5.5 | Swoole\Process\Manager        | Diganti nama, ProcessManager jadi alias Manager |

!> Tersedia di versi `v4.5.3` ke atas.

## Contoh Penggunaan

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

## Method

### __construct()

Konstruktor.

```php
Swoole\Process\Manager::__construct(int $ipcType = SWOOLE_IPC_NONE, int $msgQueueKey = 0);
```

* **Parameter**

  * **`int $ipcType`**
    * **Fungsi**: Mode komunikasi antar proses, sama kayak `$ipc_type` di `Process\Pool`【default `0` artinya nggak pake fitur IPC】
    * **Default**: `0`
    * **Nilai lain**: tidak ada

  * **`int $msgQueueKey`**
    * **Fungsi**: `key` message queue, sama kayak `$msgqueue_key` di `Process\Pool`
    * **Default**: tidak ada
    * **Nilai lain**: tidak ada

### setIPCType()

Set cara komunikasi antar worker process.

```php
Swoole\Process\Manager->setIPCType(int $ipcType): self;
```

* **Parameter**

  * **`int $ipcType`**
    * **Fungsi**: Mode komunikasi antar proses
    * **Default**: tidak ada
    * **Nilai lain**: tidak ada

### getIPCType()

Dapetin cara komunikasi antar worker process.

```php
Swoole\Process\Manager->getIPCType(): int;
```

### setMsgQueueKey()

Set `key` message queue.

```php
Swoole\Process\Manager->setMsgQueueKey(int $msgQueueKey): self;
```

* **Parameter**

  * **`int $msgQueueKey`**
    * **Fungsi**: `key` message queue
    * **Default**: tidak ada
    * **Nilai lain**: tidak ada

### getMsgQueueKey()

Dapetin `key` message queue.

```php
Swoole\Process\Manager->getMsgQueueKey(): int;
```

### add()

Nambahin worker process.

```php
Swoole\Process\Manager->add(callable $func, bool $enableCoroutine = false): self;
```

* **Parameter**

  * **`callable $func`**
    * **Fungsi**: Callback yang dijalanin proses saat ini
    * **Default**: tidak ada
    * **Nilai lain**: tidak ada

  * **`bool $enableCoroutine`**
    * **Fungsi**: Apakah bikin coroutine buat proses ini buat jalanin callback
    * **Default**: false
    * **Nilai lain**: tidak ada

### addBatch()

Nambahin worker process secara batch.

```php
Swoole\Process\Manager->addBatch(int $workerNum, callable $func, bool $enableCoroutine = false): self
```

* **Parameter**

  * **`int $workerNum`**
    * **Fungsi**: Jumlah proses yang ditambahin
    * **Default**: tidak ada
    * **Nilai lain**: tidak ada

  * **`callable $func`**
    * **Fungsi**: Callback yang dijalanin proses-proses ini
    * **Default**: tidak ada
    * **Nilai lain**: tidak ada

  * **`bool $enableCoroutine`**
    * **Fungsi**: Apakah bikin coroutine buat proses-proses ini
    * **Default**: tidak ada
    * **Nilai lain**: tidak ada

### start()

Mulai worker process.

```php
Swoole\Process\Manager->start(): void
```
