# Thread Pool

Thread pool bisa menjaga beberapa worker thread tetap berjalan, secara otomatis membuat, merestart, dan menutup child thread.

## Method

### __construct()

Konstruktor.

```php
Swoole\Thread\Pool::__construct(string $workerThreadClass, int $num);
```

* **Parameter**
  * `string $workerThreadClass`: Class yang dijalankan oleh worker thread
  * `int $worker_num`: Menentukan jumlah worker thread


### withArguments()

 Mengatur parameter worker thread, bisa didapatkan di method `run($args)`.

```php
Swoole\Thread\Pool::withArguments(...$args): static;
```


### withAutoloader()

Memuat file `autoload`

```php
Swoole\Thread\Pool::withAutoloader(string $autoloader): static;
```
* **Parameter**
  * `string $autoloader`: Path file `PHP` untuk `autoload`

> Jika menggunakan `Composer`, sistem bisa mendeteksi dan memuat `vendor/autoload.php` secara otomatis di worker process, tidak perlu ditentukan manual

### withClassDefinitionFile()

Mengatur file definisi class worker thread. **File ini hanya boleh berisi kode `namespace`, `use`, `definisi class`, tidak boleh berisi kode yang bisa dieksekusi.**

Class worker thread harus mewarisi class dasar `Swoole\Thread\Runnable` dan mengimplementasikan method `run(array $args)`.

```php
Swoole\Thread\Pool::withClassDefinitionFile(string $classFile): static;
```
* **Parameter**
  * `string $classFile`: Path file `PHP` yang mendefinisikan class worker thread

Jika class worker thread berada di path `autoload`, tidak perlu diatur

### start()

Menjalankan semua worker thread

```php
Swoole\Thread\Pool::start(): void;
```


### shutdown()
Menutup thread pool

```php
Swoole\Thread\Pool::shutdown(): void;
```

## Contoh
```php
$map = new Swoole\Thread\Map();

(new Pool(TestThread::class, 4))
    ->withAutoloader(__DIR__ . '/vendor/autoload.php')
    ->withClassDefinitionFile(__DIR__ . '/TestThread.php')
    ->withArguments(uniqid(), $map)
    ->start();
```

## Thread\Runnable

Class worker thread harus mewarisi class ini.

### run(array $args)

Harus meng-override method ini. `$args` adalah parameter yang diberikan ke object thread pool menggunakan method `withArguments()`.

### shutdown()
Menutup thread pool

### $id
Nomor thread saat ini, rentang `0~(total thread-1)`. Saat thread di-restart, thread pengganti baru memiliki nomor yang sama dengan thread lama.


### Contoh

```php
use Swoole\Thread\Runnable;

class TestThread extends Runnable
{
    public function run($uuid, $map): void
    {
        $map->incr('thread', 1);

        for ($i = 0; $i < 5; $i++) {
            usleep(10000);
            $map->incr('sleep');
        }

        if ($map['sleep'] > 50) {
            $this->shutdown();
        }
    }
}
```
