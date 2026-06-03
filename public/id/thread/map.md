# Container Konkuren Aman Map

Membuat struktur `Map` konkuren, bisa dikirim sebagai parameter thread ke child thread. Operasi baca/tulis terlihat di thread lain.

## Karakteristik
- `Map`, `ArrayList`, `Queue` akan mengalokasikan memori secara otomatis, tidak perlu alokasi tetap seperti `Table`.

- Sistem akan mengunci secara otomatis, aman untuk thread.

- Tipe variabel yang bisa dikirim lihat [Data Type](thread/transfer.md).

- Tidak mendukung iterator, bisa gunakan `keys()`, `values()`, `toArray()` sebagai gantinya.

- Object `Map`, `ArrayList`, `Queue` harus dikirim sebagai parameter thread ke child thread sebelum thread dibuat.

- `Thread\Map` mengimplementasikan interface `ArrayAccess` dan `Countable`, bisa langsung dioperasikan sebagai array.

## Contoh
```php
use Swoole\Thread;
use Swoole\Thread\Map;

$args = Thread::getArguments();
if (empty($args)) {
    $map = new Map;
    $thread = new Thread(__FILE__, $i, $map);
    sleep(1);
    $map['test'] = uniqid();
    $thread->join();
} else {
    $map = $args[1];
    sleep(2);
    var_dump($map['test']);
}
```

- Menambah atau mengubah: `$map[$key] = $value`
- Menghapus: `unset($map[$key])`
- Membaca: `$value = $map[$key]`
- Mendapatkan panjang: `count($map)`

## Method

### __construct()
Konstruktor container konkuren aman `Map`

```php
Swoole\Thread\Map->__construct(?array $values = null)
```

- `$values` opsional, mengiterasi array dan menambahkan nilainya ke `Map`

### add()
Menulis data ke `Map`

```php
Swoole\Thread\Map->add(mixed $key, mixed $value) : bool
```
  * **Parameter**
      * `mixed $key`
          * Fungsi: Key yang akan ditambahkan.
          * Nilai default: Tidak ada.
          * Nilai lain: Tidak ada.
  
      * `mixed $value`
          * Fungsi: Nilai yang akan ditambahkan.
          * Nilai default: Tidak ada.
          * Nilai lain: Tidak ada.
  
  * **Return value**
      * Jika `$key` sudah ada, mengembalikan `false`, jika tidak mengembalikan `true` yang berarti berhasil.

### update()
Memperbarui data di `Map`

```php
Swoole\Thread\Map->update(mixed $key, mixed $value) : bool
```

  * **Parameter**
      * `mixed $key`
          * Fungsi: Key yang akan diubah.
          * Nilai default: Tidak ada.
          * Nilai lain: Tidak ada.
  
      * `mixed $value`
          * Fungsi: Nilai yang akan diubah.
          * Nilai default: Tidak ada.
          * Nilai lain: Tidak ada.
  
  * **Return value**
      * Jika `$key` tidak ada, mengembalikan `false`, jika tidak mengembalikan `true` yang berarti berhasil

### incr()
Menambah nilai di `Map` secara aman, mendukung float atau integer. Jika tipe lain di-increment, akan otomatis dikonversi ke integer, diinisialisasi ke `0`, lalu di-increment

```php
Swoole\Thread\Map->incr(mixed $key, mixed $value = 1) : int | float
```
* **Parameter**
    * `mixed $key`
        * Fungsi: Key yang akan di-increment, jika tidak ada akan dibuat otomatis dengan nilai awal `0`.
        * Nilai default: Tidak ada.
        * Nilai lain: Tidak ada.

    * `mixed $value`
        * Fungsi: Nilai increment.
        * Nilai default: 1.
        * Nilai lain: Tidak ada.

* **Return value**
    * Mengembalikan nilai setelah increment.

### decr()
Mengurangi nilai di `Map` secara aman, mendukung float atau integer. Jika tipe lain di-decrement, akan otomatis dikonversi ke integer, diinisialisasi ke `0`, lalu di-decrement

```php
Swoole\Thread\Map->decr(mixed $key, mixed $value = 1) : int | float
```
* **Parameter**
    * `mixed $key`
        * Fungsi: Key yang akan di-decrement, jika tidak ada akan dibuat otomatis dengan nilai awal `0`.
        * Nilai default: Tidak ada.
        * Nilai lain: Tidak ada.

    * `mixed $value`
        * Fungsi: Nilai decrement.
        * Nilai default: 1.
        * Nilai lain: Tidak ada.

* **Return value**
    * Mengembalikan nilai setelah decrement.

### count()
Mendapatkan jumlah elemen

```php
Swoole\Thread\Map()->count(): int
```

  * **Return value**
      * Mengembalikan jumlah elemen di Map.

### keys()
Mengembalikan semua `key`

```php
Swoole\Thread\Map()->keys(): array
```

  * **Return value**
    * Mengembalikan semua `key` dari `Map`

### values()
Mengembalikan semua `value`

```php
Swoole\Thread\Map()->values(): array
```

* **Return value**
    * Mengembalikan semua `value` dari `Map`

### toArray()
Mengonversi `Map` ke array

```php
Swoole\Thread\Map()->toArray(): array
```

### clean()
Menghapus semua elemen

```php
Swoole\Thread\Map()->clean(): void
```

### sort()
Mengurutkan elemen di dalam container. Sistem akan menyimpan mapping `key` dan `value`, hanya mengurutkan `value`. Perilakunya sama dengan `asort()`.

```php
Swoole\Thread\Map()->sort(): void
```
