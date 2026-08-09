# Swoole\Server\Port

Berikut adalah penjelasan detail tentang `Swoole\Server\Port`.

## Properti

### $host
Mengembalikan alamat host yang didengarkan. Properti ini adalah string dengan tipe `string`.

```php
Swoole\Server\Port->host
```

### $port
Mengembalikan port host yang didengarkan. Properti ini adalah integer dengan tipe `int`.

```php
Swoole\Server\Port->port
```

### $type
Mengembalikan tipe `server` ini. Properti ini adalah enumerasi, mengembalikan salah satu dari `SWOOLE_TCP`, `SWOOLE_TCP6`, `SWOOLE_UDP`, `SWOOLE_UDP6`, `SWOOLE_UNIX_DGRAM`, `SWOOLE_UNIX_STREAM`.

```php
Swoole\Server\Port->type
```

### $sock
Mengembalikan socket yang didengarkan. Properti ini adalah integer dengan tipe `int`.

```php
Swoole\Server\Port->sock
```

### $ssl
Mengembalikan apakah enkripsi `ssl` diaktifkan. Properti ini adalah `bool`.

```php
Swoole\Server\Port->ssl
```

### $setting
Mengembalikan pengaturan untuk port ini. Properti ini adalah array `array`.

```php
Swoole\Server\Port->setting
```

### $connections
Mengembalikan semua koneksi ke port ini. Properti ini adalah iterator.

```php
Swoole\Server\Port->connections
```

## Method

### set()

Digunakan untuk mengatur berbagai parameter `Swoole\Server\Port` saat runtime, cara penggunaannya sama dengan [Swoole\Server->set()](/server/methods?id=set).

```php
Swoole\Server\Port->set(array $setting): void
```

### on()

Digunakan untuk mengatur fungsi callback `Swoole\Server\Port`, cara penggunaannya sama dengan [Swoole\Server->on()](/server/methods?id=on).

```php
Swoole\Server\Port->on(string $event, callable $callback): bool
```

### getCallback()

Mengembalikan fungsi callback yang telah diatur.

```php
Swoole\Server\Port->getCallback(string $name): ?callback
```

* **Parameter**

    * `string $name`

        * Fungsi: Nama event callback
        * Default: Tidak ada
        * Nilai Lain: Tidak ada

* **Return Value**

    * Mengembalikan fungsi callback jika berhasil, mengembalikan `null` jika fungsi callback tidak ada.

### getSocket()

Mengubah `fd` socket saat ini menjadi objek `Socket` PHP.

```php
Swoole\Server\Port->getSocket(): Socket|false
```

* **Return Value**

    * Mengembalikan objek `Socket` jika berhasil, mengembalikan `false` jika gagal.

!> Perhatikan, fungsi ini hanya dapat digunakan jika `--enable-sockets` diaktifkan saat kompilasi Swoole.
