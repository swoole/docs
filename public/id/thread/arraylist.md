# Container Konkuren Aman List

Membuat struktur `List` konkuren, bisa dikirim sebagai parameter thread ke child thread. Operasi baca/tulis terlihat di thread lain.

## Karakteristik
- `Map`, `ArrayList`, `Queue` akan mengalokasikan memori secara otomatis, tidak perlu alokasi tetap seperti `Table`.

- Sistem akan mengunci secara otomatis, aman untuk thread.

- Tipe variabel yang bisa dikirim lihat [Data Type](thread/transfer.md)

- Tidak mendukung iterator, bisa gunakan `toArray()` sebagai gantinya

- Object `Map`, `ArrayList`, `Queue` harus dikirim sebagai parameter thread ke child thread sebelum thread dibuat

- `Thread\ArrayList` mengimplementasikan interface `ArrayAccess` dan `Countable`, bisa langsung dioperasikan sebagai array

- `Thread\ArrayList` hanya mendukung operasi index numerik, non-numerik akan dipaksa dikonversi

## Contoh
```php
use Swoole\Thread;
use Swoole\Thread\ArrayList;

$args = Thread::getArguments();
if (empty($args)) {
    $list = new ArrayList;
    $thread = new Thread(__FILE__, $i, $list);
    sleep(1);
    $list[] = uniqid();
    $thread->join();
} else {
    $list = $args[1];
    sleep(2);
    var_dump($list[0]);
}
```

- Menambah atau mengubah: `$list[$index] = $value`
- Menghapus: `unset($list[$index])`
- Membaca: `$value = $list[$index]`
- Mendapatkan panjang: `count($list)`

## Penghapusan
Perhatikan bahwa operasi hapus akan menyebabkan pergeseran massal `List`. Misalnya `List` memiliki `1000` elemen, saat `unset($list[4])`,
maka `$list[5:999]` akan digeser untuk mengisi kekosongan. Namun tidak akan menyalin elemen secara dalam, hanya memindahkan pointer-nya.

> Jika `List` besar, menghapus elemen di awal bisa menghabiskan banyak `CPU`

## Method

### __construct()
Konstruktor container konkuren aman `ArrayList`

```php
Swoole\Thread\ArrayList->__construct(?array $values = null)
```

- `$values` opsional, mengiterasi array dan menambahkan nilainya ke `ArrayList`
- Hanya menerima array tipe `list`, tidak menerima array asosiatif, jika tidak akan melempar exception
- Array asosiatif perlu dikonversi ke array tipe `list` menggunakan `array_values`

### incr()
Menambah nilai di `ArrayList` secara aman, mendukung float atau integer. Jika tipe lain di-increment, akan otomatis dikonversi ke integer, diinisialisasi ke `0`, lalu di-increment.

```php
Swoole\Thread\ArrayList->incr(int $index, mixed $value = 1) : int | float
```

* **Parameter**
    * `int $index`
        * Fungsi: Index numerik, harus alamat index yang valid, jika tidak akan melempar exception.
        * Nilai default: Tidak ada.
        * Nilai lain: Tidak ada.

    * `mixed $value`
        * Fungsi: Nilai increment.
        * Nilai default: 1.
        * Nilai lain: Tidak ada.

* **Return value**
    * Mengembalikan nilai setelah increment.

### decr()
Mengurangi nilai di `ArrayList` secara aman, mendukung float atau integer. Jika tipe lain di-decrement, akan otomatis dikonversi ke integer, diinisialisasi ke `0`, lalu di-decrement.

```php
Swoole\Thread\ArrayList->decr(int $index, mixed $value = 1) : int | float
```

* **Parameter**
    * `int $index`
        * Fungsi: Index numerik, harus alamat index yang valid, jika tidak akan melempar exception.
        * Nilai default: Tidak ada.
        * Nilai lain: Tidak ada.

    * `mixed $value`
        * Fungsi: Nilai decrement.
        * Nilai default: 1.
        * Nilai lain: Tidak ada.

* **Return value**
    * Mengembalikan nilai setelah decrement.

### count()
Mendapatkan jumlah elemen `ArrayList`

```php
Swoole\Thread\ArrayList()->count(): int
```

* **Return value**
    * Mengembalikan jumlah elemen di List.

### toArray()
Mengonversi `ArrayList` ke array

```php
Swoole\Thread\ArrayList()->toArray(): array
```

* **Return value** Array, mengembalikan semua elemen di `ArrayList`.

### clean()
Menghapus semua elemen

```php
Swoole\Thread\ArrayList()->clean(): void
```

### sort()
Mengurutkan elemen di dalam container. Perilakunya sama dengan `sort()`.

```php
Swoole\Thread\ArrayList()->sort(): void
```
