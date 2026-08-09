# Table Shared Memory Berperforma Tinggi

Karena bahasa `PHP` nggak dukung multi-thread, `Swoole` pake mode multi-proses. Di mode multi-proses ada isolasi memory proses, jadi ngubah variable `global` dan superglobal di worker process, nggak berpengaruh di proses lain.

> Kalo set `worker_num=1`, nggak ada isolasi proses, bisa pake variable global buat nyimpen data

```php
$fds = array();
$server->on('connect', function ($server, $fd){
    echo "connection open: {$fd}\n";
    global $fds;
    $fds[] = $fd;
    var_dump($fds);
});
```

`$fds` walau variable global, cuma berlaku di proses saat ini. `Swoole` server internal bakal bikin banyak `Worker` process, pas `var_dump($fds)` dicetak, nilainya cuma `fd` dari sebagian koneksi.

Solusinya pake external storage:

* Database, kayak: `MySQL`, `MongoDB`
* Cache server, kayak: `Redis`, `Memcache`
* File disk, kalo akses multi-proses bersamaan perlu lock

Operasi database biasa dan file disk, ada banyak waktu tunggu `IO`. Makanya disarankan pake:

* `Redis` in-memory database, baca/tulis cepet banget, tapi ada masalah koneksi TCP, performa juga bukan yang tertinggi.
* `/dev/shm` filesystem in-memory, semua operasi baca/tulis di memory, tanpa `IO`, performa tinggi banget, tapi data nggak terformat dan ada masalah sinkronisasi.

?> Selain storage di atas, disarankan pake shared memory buat nyimpen data. `Swoole\Table` adalah struktur data konkuren berperforma tinggi berbasis shared memory dan lock. Buat ngatasin masalah sharing data dan sinkronisasi lock multi-proses/multi-thread. Kapasitas memory `Table` nggak dibatasin `memory_limit` PHP

!> Jangan baca/tulis `Table` pake cara array, harus pake API yang disediakan dokumen;  
Object `Table\Row` yang diambil cara array adalah object sekali pakai, jangan andelin buat operasi berlebihan.
Mulai `v4.7.0`, nggak dukung lagi baca/tulis `Table` pake cara array, dan object `Table\Row` dihapus.

* **Kelebihan**

  * Performa kuat, satu thread bisa baca/tulis `200` ribu kali per detik;
  * Kode aplikasi nggak perlu lock, `Table` punya row lock spin lock bawaan, semua operasi aman multi-thread/multi-proses. User nggak perlu mikirin sinkronisasi data;
  * Dukung multi-proses, `Table` bisa dipake buat sharing data antar proses;
  * Pake row lock, bukan global lock. Cuma kalo 2 proses di `CPU` time yang sama baca data yang sama baru terjadi rebutan lock.

* **Iterasi**

!> Jangan lakukan operasi hapus selama iterasi (bisa ambil semua `key` dulu baru hapus)

Class `Table` implement interface iterator dan `Countable`, bisa pake `foreach` buat iterasi, pake `count` buat hitung jumlah baris.

```php
foreach($table as $row)
{
  var_dump($row);
}
echo count($table);
```

## Properti

### size

Dapetin jumlah maksimum baris table.

```php
Swoole\Table->size;
```

### memorySize

Dapetin ukuran memory yang beneran dipake, satuan byte.

```php
Swoole\Table->memorySize;
```

## Method

### __construct()

Buat memory table.

```php
Swoole\Table::__construct(int $size, float $conflict_proportion = 0.2);
```

  * **Parameter**

    * **`int $size`**
      * **Fungsi**: Jumlah maksimum baris table
      * **Default**: tidak ada
      * **Nilai lain**: tidak ada

      !> Karena `Table` dibangun di atas shared memory, nggak bisa di-expand secara dinamis. Jadi `$size` harus dihitung dan diset sebelum dibuat. Jumlah maksimum baris yang bisa disimpan `Table` berbanding lurus dengan `$size`, tapi nggak persis sama. Misal `$size` `1024`, jumlah baris yang beneran bisa disimpan **kurang dari** `1024`. Kalo `$size` kegedean, memory mesin kurang, `Table` bakal gagal dibuat.

    * **`float $conflict_proportion`**
      * **Fungsi**: Rasio maksimum konflik hash
      * **Default**: `0.2` (yaitu `20%`)
      * **Nilai lain**: Minimal `0.2`, maksimal `1`

  * **Perhitungan Kapasitas**

      * Kalo `$size` bukan pangkat `2`, kayak `1024`, `8192`, `65536`, dll, internal bakal otomatis sesuaikan ke angka terdekat. Kalo kurang dari `1024`, default jadi `1024`, yaitu `1024` adalah minimum. Mulai `v4.4.6` minimum jadi `64`.
      * Total memory yang dipake `Table` adalah (`Panjang struktur HashTable` + `Panjang KEY 64 byte` + `nilai $size`) * (`1 + nilai $conflict_proportion sebagai hash conflict`) * (`ukuran kolom`).
      * Kalo data `Key` dan rasio konflik Hash lebih dari `20%`, kapasitas blok memory cadangan kurang, pas `set` data baru bakal error `Unable to allocate memory`, balik `false`, simpan gagal. Saat itu perlu perbesar `$size` dan restart service.
      * Kalo memory cukup, usahakan set nilai ini agak gede.

### column()

Nambahin kolom di memory table.

```php
Swoole\Table->column(string $name, int $type, int $size = 0);
```

  * **Parameter**

    * **`string $name`**
      * **Fungsi**: Nama field
      * **Default**: tidak ada
      * **Nilai lain**: tidak ada

    * **`int $type`**
      * **Fungsi**: Tipe field
      * **Default**: tidak ada
      * **Nilai lain**: `Table::TYPE_INT`, `Table::TYPE_FLOAT`, `Table::TYPE_STRING`

    * **`int $size`**
      * **Fungsi**: Panjang maksimum string【field tipe string wajib specify `$size`】
      * **Satuan**: byte
      * **Default**: tidak ada
      * **Nilai lain**: tidak ada

  * **Penjelasan Tipe `$type`**

Tipe | Keterangan
---|---
Table::TYPE_INT | Default 8 byte
Table::TYPE_STRING | Setelah diset, string yang diset nggak boleh lebih dari `$size`
Table::TYPE_FLOAT | Pake 8 byte memory

### create()

Buat memory table. Setelah struktur table didefinisikan, jalanin `create` buat minta memory ke OS, bikin table.

```php
Swoole\Table->create(): bool
```

Pake method `create` buat bikin table, bisa baca properti [memorySize](/memory/table?id=memorysize) buat dapetin ukuran memory yang beneran dipake

  * **Tips**

    * Sebelum panggil `create`, nggak bisa pake method baca/tulis kayak `set`, `get`
    * Setelah panggil `create`, nggak bisa pake method `column` buat nambah field baru
    * Memory sistem kurang, minta gagal, `create` balik `false`
    * Minta memory sukses, `create` balik `true`

    !> `Table` pake shared memory buat nyimpen data. Sebelum bikin child process, harus jalanin `Table->create()`;  
    `Table` di `Server`, `Table->create()` harus dijalanin sebelum `Server->start()`.

  * **Contoh Penggunaan**

```php
$table = new Swoole\Table(1024);
$table->column('id', Swoole\Table::TYPE_INT);
$table->column('name', Swoole\Table::TYPE_STRING, 64);
$table->column('num', Swoole\Table::TYPE_FLOAT);
$table->create();

$worker = new Swoole\Process(function () {}, false, false);
$worker->start();

//$serv = new Swoole\Server('127.0.0.1', 9501);
//$serv->start();
```

### set()

Set data baris. `Table` pake cara `key-value` buat akses data.

```php
Swoole\Table->set(string $key, array $value): bool
```

  * **Parameter**

    * **`string $key`**
      * **Fungsi**: `key` data
      * **Default**: tidak ada
      * **Nilai lain**: tidak ada

      !> `$key` yang sama berarti baris data yang sama. Kalo `set` pake `key` yang sama, bakal timpa data sebelumnya. Panjang maksimum `key` 63 byte

    * **`array $value`**
      * **Fungsi**: `value` data
      * **Default**: tidak ada
      * **Nilai lain**: tidak ada

      !> Harus array, harus sama persis dengan `$name` yang didefinisikan di field

  * **Return Value**

    * Set sukses balik `true`
    * Gagal balik `false`, mungkin karena konflik Hash kebanyakan, space dinamis nggak bisa alokasi memory. Bisa perbesar parameter ke-2 konstruktor

!> -`Table->set()` bisa set nilai semua field, atau coba ubah sebagian field;  
   -`Table->set()` sebelum diset, semua field baris data kosong;  
   -`set`/`get`/`del` udah ada row lock, jadi nggak perlu panggil `lock`;  
   -**Key bukan binary safe, harus string, jangan masukin data binary.**

  * **Contoh Penggunaan**

```php
$table->set('1', ['id' => 1, 'name' => 'test1', 'age' => 20]);
$table->set('2', ['id' => 2, 'name' => 'test2', 'age' => 21]);
$table->set('3', ['id' => 3, 'name' => 'test3', 'age' => 19]);
```

  * **Set String Melebihi Panjang Maksimum**

    Kalo panjang string yang dimasukin lebih dari ukuran maksimum yang ditentukan pas definisi kolom, internal bakal otomatis potong.

    ```php
    $table->column('str_value', Swoole\Table::TYPE_STRING, 5);
    $table->set('hello', array('str_value' => 'world 123456789'));
    var_dump($table->get('hello'));
    ```

    * Kolom `str_value` maksimal 5 byte, tapi `set` pake string lebih dari `5` byte
    * Internal otomatis potong 5 byte, jadinya nilai `str_value` `world`

!> Mulai `v4.3`, internal alignment memory. Panjang string harus kelipatan 8. Misal panjang 5 otomatis di-align ke 8 byte, jadi nilai `str_value` `world 12`

### incr()

Operasi increment atomik.

```php
Swoole\Table->incr(string $key, string $column, mixed $incrby = 1): int
```

  * **Parameter**

    * **`string $key`**
      * **Fungsi**: `key` data【kalo baris `$key` nggak ada, default nilai kolom `0`】
      * **Default**: tidak ada
      * **Nilai lain**: tidak ada

    * **`string $column`**
      * **Fungsi**: Nama kolom【cuma dukung float dan integer】
      * **Default**: tidak ada
      * **Nilai lain**: tidak ada

    * **`string $incrby`**
      * **Fungsi**: Nilai increment【kalo kolom `int`, `$incrby` harus `int`. Kalo kolom `float`, `$incrby` harus `float`】
      * **Default**: `1`
      * **Nilai lain**: tidak ada

  * **Return Value**

    Balik nilai hasil akhir

### decr()

Operasi decrement atomik.

```php
Swoole\Table->decr(string $key, string $column, mixed $decrby = 1): int
```

  * **Parameter**

    * **`string $key`**
      * **Fungsi**: `key` data【kalo baris `$key` nggak ada, default nilai kolom `0`】
      * **Default**: tidak ada
      * **Nilai lain**: tidak ada

    * **`string $column`**
      * **Fungsi**: Nama kolom【cuma dukung float dan integer】
      * **Default**: tidak ada
      * **Nilai lain**: tidak ada

    * **`string $decrby`**
      * **Fungsi**: Nilai decrement【kalo kolom `int`, `$decrby` harus `int`. Kalo kolom `float`, `$decrby` harus `float`】
      * **Default**: `1`
      * **Nilai lain**: tidak ada

  * **Return Value**

    Balik nilai hasil akhir

    !> Kalo nilai `0` di-decrement bakal jadi negatif

### get()

Dapetin satu baris data.

```php
Swoole\Table->get(string $key, string $field = null): array|false
```

  * **Parameter**

    * **`string $key`**
      * **Fungsi**: `key` data【harus string】
      * **Default**: tidak ada
      * **Nilai lain**: tidak ada

    * **`string $field`**
      * **Fungsi**: Kalo specify `$field`, cuma balikin nilai field itu, bukan seluruh record
      * **Default**: tidak ada
      * **Nilai lain**: tidak ada

  * **Return Value**

    * Kalo `$key` nggak ada, balik `false`
    * Sukses balik array hasil
    * Kalo specify `$field`, cuma balikin nilai field itu, bukan seluruh record

### exist()

Cek apakah suatu key ada di table.

```php
Swoole\Table->exist(string $key): bool
```

  * **Parameter**

    * **`string $key`**
      * **Fungsi**: `key` data【harus string】
      * **Default**: tidak ada
      * **Nilai lain**: tidak ada

### count()

Balikin jumlah entri di table.

```php
Swoole\Table->count(): int
```

### del()

Hapus data.

!> `Key` bukan binary safe, harus string, jangan masukin data binary; **Jangan hapus pas iterasi**.

```php
Swoole\Table->del(string $key): bool
```

  * **Return Value**

    * Kalo data `$key` nggak ada, balik `false`
    * Hapus sukses balik `true`

### stats()

Dapetin status `Swoole\Table`.

```php
Swoole\Table->stats(): array
```

!> Swoole >= `v4.8.0`

## Fungsi Bantuan :id=swoole_table

Biar user gampang bikin `Swoole\Table`.

```php
function swoole_table(int $size, string $fields): Swoole\Table
```

!> Swoole >= `v4.6.0`. Format `$fields` `foo:i/foo:s:num/foo:f`

| Singkatan | Panjang | Tipe              |
| --------- | ------- | ----------------- |
| i         | int     | Table::TYPE_INT   |
| s         | string  | Table::TYPE_STRING |
| f         | float   | Table::TYPE_FLOAT |

Contoh:

```php
$table = swoole_table(1024, 'fd:int, reactor_id:i, data:s:64');
var_dump($table);

$table = new Swoole\Table(1024, 0.25);
$table->column('fd', Swoole\Table::TYPE_INT);
$table->column('reactor_id', Swoole\Table::TYPE_INT);
$table->column('data', Swoole\Table::TYPE_STRING, 64);
$table->create();
var_dump($table);
```

## Contoh Lengkap

```php
<?php
$table = new Swoole\Table(1024);
$table->column('fd', Swoole\Table::TYPE_INT);
$table->column('reactor_id', Swoole\Table::TYPE_INT);
$table->column('data', Swoole\Table::TYPE_STRING, 64);
$table->create();

$serv = new Swoole\Server('127.0.0.1', 9501);
$serv->set(['dispatch_mode' => 1]);
$serv->table = $table;

$serv->on('receive', function ($serv, $fd, $reactor_id, $data) {
	$cmd = explode(" ", trim($data));

	//get
	if ($cmd[0] == 'get')
	{
		//get self
		if (count($cmd) < 2)
		{
			$cmd[1] = $fd;
		}
		$get_fd = intval($cmd[1]);
		$info = $serv->table->get($get_fd);
		$serv->send($fd, var_export($info, true)."\n");
	}
	//set
	elseif ($cmd[0] == 'set')
	{
		$ret = $serv->table->set($fd, array('reactor_id' => $reactor_id, 'fd' => $fd, 'data' => $cmd[1]));
		if ($ret === false)
		{
			$serv->send($fd, "ERROR\n");
		}
		else
		{
			$serv->send($fd, "OK\n");
		}
	}
	else
	{
		$serv->send($fd, "command error.\n");
	}
});

$serv->start();
```
