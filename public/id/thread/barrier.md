# Sinkronisasi Eksekusi Thread Barrier

`Thread\Barrier` adalah mekanisme sinkronisasi thread. Ini memungkinkan beberapa thread untuk sinkron di titik tertentu, memastikan semua thread menyelesaikan tugasnya sebelum mencapai titik kritis (barrier). Hanya ketika semua thread yang berpartisipasi mencapai barrier ini, mereka bisa melanjutkan eksekusi kode selanjutnya.

Misalnya kita membuat `4` thread, kita ingin mereka semua siap lalu menjalankan tugas bersama-sama, seperti pistol start dalam lomba lari. Ini bisa diimplementasikan dengan `Thread\Barrier`.


## Contoh
```php
use Swoole\Thread;
use Swoole\Thread\Barrier;

const N = 4;
$args = Thread::getArguments();

if (empty($args)) {
    $barrier = new Barrier(N);
    $n = N;
    $threads = [];
    while($n--) {
        $threads[] = new Thread(__FILE__, $barrier, $n);
    }
} else {
    $barrier = $args[0];
    $n = $args[1];
    // Menunggu semua thread siap
    $barrier->wait();
    echo "thread $n is running\n";
}
```

## Method

### __construct()
Konstruktor

```php
Thread\Barrier()->__construct(int $count): void
```

  * **Parameter**
      * `int $count`
          * Fungsi: Jumlah thread, harus lebih dari `1`.
          * Nilai default: Tidak ada.
          * Nilai lain: Tidak ada.
  
Jumlah thread yang menjalankan operasi `wait` harus sesuai dengan nilai yang diatur, jika tidak semua thread akan terblokir.

### wait()

Memblokir dan menunggu thread lain. Saat semua thread berada dalam status `wait`, semua thread yang menunggu akan dibangunkan secara bersamaan untuk melanjutkan eksekusi.

```php
Thread\Barrier()->wait(): void
```
