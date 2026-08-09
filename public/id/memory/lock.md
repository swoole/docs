# Lock Antar Proses/Thread

* Di kode `PHP` bisa dengan gampang bikin lock `Swoole\Lock` buat sinkronisasi data. Class `Lock` dukung `3` tipe lock.
* Mode multi-thread perlu pake `Swoole\Thread\Lock`, selain namespace beda, interface-nya sama persis dengan `Swoole\Lock`.

| Tipe Lock          | Keterangan |
|-------------------|------------|
| SWOOLE_MUTEX     | Mutex lock |
| SWOOLE_RWLOCK    | Read-write lock |
| SWOOLE_SPINLOCK  | Spin lock |

!> Jangan bikin lock di callback kayak [onReceive](/server/events?id=onreceive), nanti memory terus naik, bocor memory.

## Contoh Penggunaan

```php
$lock = new Swoole\Lock(SWOOLE_MUTEX);
echo "[Master]create lock\n";
$lock->lock();
if (pcntl_fork() > 0) {
  sleep(1);
  $lock->unlock();
} else {
  echo "[Child] Wait Lock\n";
  $lock->lock();
  echo "[Child] Get Lock\n";
  $lock->unlock();
  exit("[Child] exit\n");
}
echo "[Master]release lock\n";
unset($lock);
sleep(1);
echo "[Master]exit\n";
```

## Peringatan

!> Lock nggak bisa dipake di coroutine. Hati-hati, jangan pake `API` yang bisa picu switch coroutine di antara operasi `lock` dan `unlock`.

### Contoh Salah

!> Kode ini `100%` deadlock di mode coroutine.

```php
$lock = new Swoole\Lock();
$c = 2;

while ($c--) {
  go(function () use ($lock) {
      $lock->lock();
      Co::sleep(1);
      $lock->unlock();
  });
}
```

## Method

### __construct()

Konstruktor.

```php
Swoole\Lock::__construct(int $type = SWOOLE_MUTEX);
```

!> Jangan looping bikin/hancurin object lock, nanti bocor memory.

  * **Parameter**

    * **`int $type`**
      * **Fungsi**: Tipe lock
      * **Default**: `SWOOLE_MUTEX`【Mutex lock】
      * **Nilai lain**: tidak ada

!> Setiap tipe lock dukung method yang beda. Misalnya read-write lock bisa dukung `$lock->lock_read()`. Selain file lock, tipe lock lain harus dibuat di parent process, biar child process hasil `fork` bisa bersaing dapetin lock.

### lock()

Operasi lock. Kalo ada proses lain yang megang lock, ini bakal blocking sampe proses yang megang lock `unlock()`.

#### Setelah versi 6.1.0
```php
Swoole\Lock->lock(int $operation, float $timeout = -1): bool
```

  * **Parameter**

    * **`int $operation`**
      * **Fungsi**: Tipe operasi lock
      * **Default**: `LOCK_EX`【Exclusive lock】
      * **Nilai lain**:
        * `LOCK_EX`: Exclusive lock
        * `LOCK_SH`: Shared lock (read-only lock, cuma `SWOOLE_RWLOCK` yang dukung)
        * `LOCK_NB`: Non-blocking lock (langsung balik kalo nggak dapet lock)
    * **`float $timeout`**
      * **Fungsi**: Waktu timeout
      * **Satuan**: detik【dukung float, contoh `1.5` artinya `1s` + `500ms`】
      * **Default**: `-1`【artinya nggak pernah timeout】
      * **Nilai lain**: tidak ada

  * **Return Value**

    * Kalo dalam waktu yang ditentukan nggak dapet lock, balik `false`
    * Lock sukses balik `true`

Selain `unlock()`, method lain udah deprecated dan nggak bisa dipake, termasuk:
- `trylock()`
- `lock_read()`
- `trylock_read()`
- `lockwait()`

#### Sebelum versi 6.1.0

```php
Swoole\Lock->lock(): bool
```

### unlock()

Lepas lock.

```php
Swoole\Lock->unlock(): bool
```

### trylock()

Operasi lock. Bedanya sama `lock`, `trylock()` nggak blocking, langsung balik.

```php
Swoole\Lock->trylock(): bool
```

  * **Return Value**

    * Lock sukses balik `true`, saat itu bisa ubah variable shared
    * Lock gagal balik `false`, artinya ada proses lain yang megang lock

### lock_read()

Read-only lock.

```php
Swoole\Lock->lock_read(): bool
```

* Selagi megang read lock, proses lain tetep bisa dapet read lock dan lanjut baca;
* Tapi nggak bisa `$lock->lock()` atau `$lock->trylock()`, dua method ini dapetin exclusive lock. Pas exclusive lock dipasang, proses lain nggak bisa lakukan operasi lock apa pun, termasuk read lock;
* Kalo proses lain dapet exclusive lock (panggil `$lock->lock()`/`$lock->trylock()`), `$lock->lock_read()` bakal blocking sampe proses yang megang exclusive lock lepas lock.

!> Cuma lock tipe `SWOOLE_RWLOCK` yang dukung read-only lock

### trylock_read()

Lock. Method ini sama kayak `lock_read()`, tapi non-blocking.

```php
Swoole\Lock->trylock_read(): bool
```

!> Panggilan langsung balik, harus cek return value buat menentukan apakah dapet lock.

### lockwait()

Operasi lock. Fungsinya sama kayak `lock()`, tapi `lockwait()` bisa set timeout.

```php
Swoole\Lock->lockwait(float $timeout = 1.0): bool
```

  * **Parameter**

    * **`float $timeout`**
      * **Fungsi**: Waktu timeout
      * **Satuan**: detik【dukung float, contoh `1.5` artinya `1s` + `500ms`】
      * **Default**: `1`
      * **Nilai lain**: tidak ada

  * **Return Value**

    * Kalo dalam waktu yang ditentukan nggak dapet lock, balik `false`
    * Lock sukses balik `true`

!> Cuma lock tipe `Mutex` yang dukung `lockwait`
