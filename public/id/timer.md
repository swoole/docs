# Timer

Timer dengan presisi milidetik. Level bawah diimplementasikan berdasarkan `epoll_wait` dan `setitimer`, struktur data menggunakan `heap minimum`, dapat mendukung penambahan banyak timer.

* Di proses IO sinkron menggunakan `setitimer` dan sinyal, seperti proses `Manager` dan `TaskWorker`
* Di proses IO asinkron menggunakan timeout `epoll_wait`/`kevent`/`poll`/`select`

## Performa

Level bawah menggunakan struktur data heap minimum untuk mengimplementasikan timer, penambahan dan penghapusan timer semuanya operasi memori, sehingga performa sangat tinggi.

> Di script benchmark resmi [timer.php](https://github.com/swoole/benchmark/blob/master/timer.php), menambah atau menghapus `10` ribu timer dengan waktu acak memakan waktu sekitar `0.08s`.

```shell
~/workspace/swoole/benchmark$ php timer.php
add 100000 timer :0.091133117675781s
del 100000 timer :0.084658145904541s
```

!> Timer adalah operasi memori, tanpa konsumsi `IO`

## Perbedaan

`Timer` berbeda dengan `pcntl_alarm` di `PHP` sendiri. `pcntl_alarm` diimplementasikan berdasarkan `sinyal jam + fungsi tick`, memiliki beberapa kekurangan:

* Maksimal hanya mendukung detik, sedangkan `Timer` bisa ke level milidetik
* Tidak mendukung pengaturan beberapa timer secara bersamaan
* `pcntl_alarm` bergantung pada `declare(ticks = 1)`, performanya sangat buruk

## Timer Nol Milidetik

Level bawah tidak mendukung timer dengan parameter waktu `0`. Ini berbeda dengan bahasa pemrograman seperti `Node.js`. Di `Swoole` dapat menggunakan [Swoole\Event::defer](/event?id=defer) untuk mencapai fungsi serupa.

```php
Swoole\Event::defer(function () {
  echo "hello\n";
});
```

!> Kode di atas memiliki efek yang sama persis dengan `setTimeout(func, 0)` di `JS`.

## Alias

`tick()`, `after()`, `clear()` semuanya memiliki alias gaya fungsi

Method Statis Kelas | Alias Gaya Fungsi
---|---
`Swoole\Timer::tick()` | `swoole_timer_tick()`
`Swoole\Timer::after()` | `swoole_timer_after()`
`Swoole\Timer::clear()` | `swoole_timer_clear()`

## Method

### tick()

Mengatur timer interval clock.

Berbeda dengan timer `after`, timer `tick` akan terus terpicu sampai dipanggil [Timer::clear](/timer?id=clear) untuk membersihkan.

```php
Swoole\Timer::tick(int $msec, callable $callback_function, ...$params): int
```

!> 1. Timer hanya valid dalam ruang proses saat ini  
   2. Timer diimplementasikan murni asinkron, tidak bisa digunakan bersama fungsi [IO sinkron](/learn?id=同步io异步io), jika tidak waktu eksekusi timer akan kacau  
   3. Mungkin ada sedikit kesalahan selama eksekusi timer

* **Parameter** 

    * **`int $msec`**
      * **Fungsi**: Menentukan waktu
      * **Satuan**: Milidetik [misal `1000` berarti `1` detik, di versi `v4.2.10` ke bawah maksimal tidak boleh melebihi `86400000`]
      * **Nilai Default**: Tidak ada
      * **Nilai Lain**: Tidak ada

    * **`callable $callback_function`**
      * **Fungsi**: Fungsi yang akan dijalankan saat waktu habis, harus dapat dipanggil
      * **Nilai Default**: Tidak ada
      * **Nilai Lain**: Tidak ada

    * **`...$params`**
      * **Fungsi**: Memberikan data ke fungsi eksekusi [parameter ini juga opsional]
      * **Nilai Default**: Tidak ada
      * **Nilai Lain**: Tidak ada
      
      !> Dapat menggunakan sintaks `use` fungsi anonim untuk meneruskan parameter ke fungsi callback

* **Fungsi Callback $callback_function** 

    ```php
    callbackFunction(int $timer_id, ...$params);
    ```

      * **`int $timer_id`**
        * **Fungsi**: `ID` timer [dapat digunakan untuk [Timer::clear](/timer?id=clear) membersihkan timer ini]
        * **Nilai Default**: Tidak ada
        * **Nilai Lain**: Tidak ada

      * **`...$params`**
        * **Fungsi**: Parameter ketiga `$param` yang dimasukkan oleh `Timer::tick`
        * **Nilai Default**: Tidak ada
        * **Nilai Lain**: Tidak ada

* **Ekstensi**

    * **Koreksi Timer**

      Waktu eksekusi fungsi callback timer tidak mempengaruhi waktu eksekusi timer berikutnya. Contoh: mengatur timer `tick` `10ms` pada `0.002s`, pertama kali akan menjalankan fungsi callback pada `0.012s`, jika fungsi callback membutuhkan `5ms`, timer berikutnya tetap akan terpicu pada `0.022s`, bukan `0.027s`.
      
      Tetapi jika waktu eksekusi fungsi callback timer terlalu lama, bahkan menutupi waktu eksekusi timer berikutnya. Level bawah akan melakukan koreksi waktu, membuang perilaku yang sudah kedaluwarsa, dan memicu callback di waktu berikutnya. Seperti contoh di atas, fungsi callback pada `0.012s` membutuhkan `15ms`, seharusnya pada `0.022s` ada satu callback timer. Sebenarnya timer ini baru kembali pada `0.027s`, sudah kedaluwarsa. Level bawah akan memicu callback timer lagi pada `0.032s`.
    
    * **Mode Korutin**

      Di lingkungan korutin, callback `Timer::tick` akan otomatis membuat korutin, dapat langsung menggunakan `API` terkait korutin, tanpa perlu memanggil `go` untuk membuat korutin.
      
      !> Dapat mengatur [enable_coroutine](/timer?id=close-timer-co) untuk menonaktifkan pembuatan korutin otomatis

* **Contoh Penggunaan**

    ```php
    Swoole\Timer::tick(1000, function(){
        echo "timeout\n";
    });
    ```

    * **Contoh Benar**

    ```php
    Swoole\Timer::tick(3000, function (int $timer_id, $param1, $param2) {
        echo "timer_id #$timer_id, after 3000ms.\n";
        echo "param1 is $param1, param2 is $param2.\n";

        Swoole\Timer::tick(14000, function ($timer_id) {
            echo "timer_id #$timer_id, after 14000ms.\n";
        });
    }, "A", "B");
    ```

    * **Contoh Salah**

    ```php
    Swoole\Timer::tick(3000, function () {
        echo "after 3000ms.\n";
        sleep(14);
        echo "after 14000ms.\n";
    });
    ```

### after()

Menjalankan fungsi setelah waktu yang ditentukan. Fungsi `Swoole\Timer::after` adalah timer satu kali, akan dihancurkan setelah selesai.

Fungsi ini berbeda dengan fungsi `sleep` yang disediakan pustaka standar `PHP`, `after` bersifat non-blocking. Sedangkan `sleep` akan menyebabkan proses saat ini masuk ke blocking, tidak bisa menangani request baru.

```php
Swoole\Timer::after(int $msec, callable $callback_function, ...$params): int
```

* **Parameter** 

    * **`int $msec`**
      * **Fungsi**: Menentukan waktu
      * **Satuan**: Milidetik [misal `1000` berarti `1` detik, di versi `v4.2.10` ke bawah maksimal tidak boleh melebihi `86400000`]
      * **Nilai Default**: Tidak ada
      * **Nilai Lain**: Tidak ada

    * **`callable $callback_function`**
      * **Fungsi**: Fungsi yang akan dijalankan saat waktu habis, harus dapat dipanggil.
      * **Nilai Default**: Tidak ada
      * **Nilai Lain**: Tidak ada

    * **`...$params`**
      * **Fungsi**: Memberikan data ke fungsi eksekusi [parameter ini juga opsional]
      * **Nilai Default**: Tidak ada
      * **Nilai Lain**: Tidak ada
      
      !> Dapat menggunakan sintaks `use` fungsi anonim untuk meneruskan parameter ke fungsi callback

* **Nilai Kembali**

    * Berhasil mengembalikan `ID` timer, jika ingin membatalkan timer, panggil [Swoole\Timer::clear](/timer?id=clear)

* **Ekstensi**

    * **Mode Korutin**

      Di lingkungan korutin, callback [Swoole\Timer::after](/timer?id=after) akan otomatis membuat korutin, dapat langsung menggunakan `API` terkait korutin, tanpa perlu memanggil `go` untuk membuat korutin.
      
      !> Dapat mengatur [enable_coroutine](/timer?id=close-timer-co) untuk menonaktifkan pembuatan korutin otomatis

* **Contoh Penggunaan**

```php
$str = "Swoole";
Swoole\Timer::after(1000, function() use ($str) {
    echo "Hello, $str\n";
});
```

### clear()

Menggunakan `ID` timer untuk menghapus timer.

```php
Swoole\Timer::clear(int $timer_id): bool
```

* **Parameter** 

    * **`int $timer_id`**
      * **Fungsi**: `ID` timer [setelah memanggil [Timer::tick](/timer?id=tick), [Timer::after](/timer?id=after) akan mengembalikan ID integer]
      * **Nilai Default**: Tidak ada
      * **Nilai Lain**: Tidak ada

!> `Swoole\Timer::clear` tidak bisa digunakan untuk menghapus timer di proses lain, hanya berlaku untuk proses saat ini

* **Contoh Penggunaan**

```php
$timer = Swoole\Timer::after(1000, function () {
    echo "timeout\n";
});

var_dump(Swoole\Timer::clear($timer));
var_dump($timer);

// Output: bool(true) int(1)
// Tidak output: timeout
```

### clearAll()

Menghapus semua timer dalam proses Worker saat ini.

!> Versi Swoole >= `v4.4.0` tersedia

```php
Swoole\Timer::clearAll(): bool
```

### info()

Mengembalikan informasi `timer`.

!> Versi Swoole >= `v4.4.0` tersedia

```php
Swoole\Timer::info(int $timer_id): array
```

* **Nilai Kembali**

```php
array(5) {
  ["exec_msec"]=>
  int(6000)
  ["exec_count"]=> // Ditambahkan di v4.8.0
  int(5)
  ["interval"]=>
  int(1000)
  ["round"]=>
  int(0)
  ["removed"]=>
  bool(false)
}
```

### list()

Mengembalikan iterator timer, dapat menggunakan `foreach` untuk iterasi semua id `timer` dalam proses Worker saat ini.

!> Versi Swoole >= `v4.4.0` tersedia

```php
Swoole\Timer::list(): Swoole\Timer\Iterator
```

* **Contoh Penggunaan**

```php
foreach (Swoole\Timer::list() as $timer_id) {
    var_dump(Swoole\Timer::info($timer_id));
}
```

### stats()

Melihat status timer.

!> Versi Swoole >= `v4.4.0` tersedia

```php
Swoole\Timer::stats(): array
```

* **Nilai Kembali**

```php
array(3) {
  ["initialized"]=>
  bool(true)
  ["num"]=>
  int(1000)
  ["round"]=>
  int(1)
}
```

### set()

Mengatur parameter terkait timer.

```php
Swoole\Timer::set(array $array): void
```

!> Method ini ditandai sebagai tidak digunakan lagi sejak versi `v4.6.0`.

## Menonaktifkan Korutin :id=close-timer-co

Secara default, timer akan otomatis membuat korutin saat menjalankan fungsi callback. Dapat mengatur timer secara terpisah untuk menonaktifkan korutin.

```php
swoole_async_set([
  'enable_coroutine' => false,
]);
```
