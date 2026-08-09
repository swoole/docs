# Atomic Counter Tanpa Lock Antar Proses/Thread

`Atomic` adalah class operasi hitung atomik yang disediakan `Swoole` di level bawah, memudahkan penambahan/pengurangan integer tanpa lock.

* Pakai shared memory, bisa operasi hitung antar proses berbeda
* Berdasarkan instruksi atomik `CPU` dari `gcc/clang`, tanpa perlu lock
* Di program server, harus dibuat sebelum `Server->start` supaya bisa dipake di `Worker` process
* Default pake tipe `32` bit unsigned, kalo perlu `64` bit signed integer, bisa pake `Swoole\Atomic\Long`
* Mode multi-thread perlu pake `Swoole\Thread\Atomic` dan `Swoole\Thread\Atomic\Long`, selain namespace beda, interface-nya sama persis dengan `Swoole\Atomic` dan `Swoole\Atomic\Long`.

!> Jangan bikin counter di callback kayak [onReceive](/server/events?id=onreceive), nanti memory terus naik, bocor memory.

!> Dukung atomic counter `64` bit signed long integer, perlu pake `new Swoole\Atomic\Long`. `Atomic\Long` nggak dukung method `wait` dan `wakeup`.

## Contoh Lengkap

```php
$atomic = new Swoole\Atomic();

$serv = new Swoole\Server('127.0.0.1', '9501');
$serv->set([
    'worker_num' => 1,
    'log_file' => '/dev/null'
]);
$serv->on("start", function ($serv) use ($atomic) {
    if ($atomic->add() == 2) {
        $serv->shutdown();
    }
});
$serv->on("ManagerStart", function ($serv) use ($atomic) {
    if ($atomic->add() == 2) {
        $serv->shutdown();
    }
});
$serv->on("ManagerStop", function ($serv) {
    echo "shutdown\n";
});
$serv->on("Receive", function () {
    
});
$serv->start();
```

## Method

### __construct()

Konstruktor. Bikin object atomic counter.

```php
Swoole\Atomic::__construct(int $init_value = 0);
```

  * **Parameter**

    * **`int $init_value`**
      * **Fungsi**: Tentukan nilai inisialisasi
      * **Default**: `0`
      * **Nilai lain**: tidak ada

!> -`Atomic` cuma bisa operasi integer `32` bit unsigned, maksimal `4.2` miliar, nggak dukung negatif;  
-Di `Server`, atomic counter harus dibuat sebelum `Server->start`;  
-Di [Process](/process/process), atomic counter harus dibuat sebelum `Process->start`.

### add()

Tambah hitungan.

```php
Swoole\Atomic->add(int $add_value = 1): int
```

  * **Parameter**

    * **`int $add_value`**
      * **Fungsi**: Nilai yang ditambah【harus positif】
      * **Default**: `1`
      * **Nilai lain**: tidak ada

  * **Return Value**

    * Method `add` balikin nilai hasil setelah operasi

!> Kalo ditambah nilai asli sampe lebih dari `4.2` miliar, bakal overflow, bit tinggi dibuang.

### sub()

Kurang hitungan.

```php
Swoole\Atomic->sub(int $sub_value = 1): int
```

  * **Parameter**

    * **`int $sub_value`**
      * **Fungsi**: Nilai yang dikurang【harus positif】
      * **Default**: `1`
      * **Nilai lain**: tidak ada

  * **Return Value**

    * Method `sub` balikin nilai hasil setelah operasi

!> Kalo nilai asli dikurang sampe di bawah `0` bakal overflow, bit tinggi dibuang.

### get()

Dapetin nilai hitungan saat ini.

```php
Swoole\Atomic->get(): int
```

  * **Return Value**

    * Balik nilai numerik saat ini

### set()

Set nilai saat ini ke angka yang ditentukan.

```php
Swoole\Atomic->set(int $value): void
```

  * **Parameter**

    * **`int $value`**
      * **Fungsi**: Nilai target yang mau diset
      * **Default**: tidak ada
      * **Nilai lain**: tidak ada

### cmpset()

Kalo nilai saat ini sama dengan parameter `1`, set nilai saat ini jadi parameter `2`.

```php
Swoole\Atomic->cmpset(int $cmp_value, int $set_value): bool
```

  * **Parameter**

    * **`int $cmp_value`**
      * **Fungsi**: Kalo nilai saat ini sama `$cmp_value` balik `true`, lalu set nilai jadi `$set_value`. Kalo beda balik `false`【harus integer kurang dari `4.2` miliar】
      * **Default**: tidak ada
      * **Nilai lain**: tidak ada

    * **`int $set_value`**
      * **Fungsi**: Kalo nilai saat ini sama `$cmp_value` balik `true`, lalu set nilai jadi `$set_value`. Kalo beda balik `false`【harus integer kurang dari `4.2` miliar】
      * **Default**: tidak ada
      * **Nilai lain**: tidak ada

### wait()

Set ke status wait.

!> Kalo nilai atomic counter `0`, program masuk status nunggu. Proses lain panggil `wakeup` bisa bangunin program lagi. Implementasi ini berbasis `Linux Futex`. Pake fitur ini, cuma pake `4` byte memory udah bisa mewujudkan fungsi nunggu, notif, lock. Di platform yang nggak dukung `Futex`, internal pake loop `usleep(1000)` simulasi.

```php
Swoole\Atomic->wait(float $timeout = 1.0): bool
```

  * **Parameter**

    * **`float $timeout`**
      * **Fungsi**: Waktu timeout【set `-1` artinya nggak pernah timeout, bakal nunggu sampe ada proses lain yang bangunin】
      * **Satuan**: detik【dukung float, contoh `1.5` artinya `1s` + `500ms`】
      * **Default**: `1`
      * **Nilai lain**: tidak ada

  * **Return Value**

    * Timeout balik `false`, kode error `EAGAIN`, bisa pake `swoole_errno`
    * Sukses balik `true`, artinya ada proses lain yang berhasil bangunin lock saat ini lewat `wakeup`

  * **Lingkungan Coroutine**

  `wait` bakal blocking seluruh proses, bukan cuma coroutine. Jadi jangan pake `Atomic->wait()` di lingkungan coroutine biar nggak bikin proses gantung.

!> -Pas pake fitur `wait/wakeup`, nilai atomic counter cuma boleh `0` atau `1`, kalo nggak bakal bermasalah;  
-Kalo nilai atomic counter `1`, artinya nggak perlu masuk status nunggu, resource lagi tersedia. Fungsi `wait` bakal langsung balik `true`.

  * **Contoh Penggunaan**

    ```php
    $n = new Swoole\Atomic;
    if (pcntl_fork() > 0) {
        echo "master start\n";
        $n->wait(1.5);
        echo "master end\n";
    } else {
        echo "child start\n";
        sleep(1);
        $n->wakeup();
        echo "child end\n";
    }
    ```

### wakeup()

Bangunin proses lain yang ada di status wait.

```php
Swoole\Atomic->wakeup(int $n = 1): bool
```

  * **Parameter**

    * **`int $n`**
      * **Fungsi**: Jumlah proses yang mau dibangunin
      * **Default**: tidak ada
      * **Nilai lain**: tidak ada

* Kalo atomic counter saat ini `0`, artinya nggak ada proses yang `wait`, `wakeup` bakal langsung balik `true`;
* Kalo atomic counter saat ini `1`, artinya ada proses yang `wait`, `wakeup` bakal bangunin proses yang nunggu, dan balik `true`;
* Setelah proses yang dibangunin balik, atomic counter diset jadi `0`, saat itu bisa panggil `wakeup` lagi buat bangunin proses lain yang `wait`.
