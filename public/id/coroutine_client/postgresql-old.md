# Coroutine\PostgreSQL Versi Lama

Klien `PostgreSQL` coroutine. Perlu mengompilasi ekstensi [ext-postgresql](https://github.com/swoole/ext-postgresql) untuk mengaktifkan fitur ini.

> Dokumentasi ini hanya berlaku untuk Swoole < 5.0

## Kompilasi dan Instalasi

Unduh kode sumber dari: [https://github.com/swoole/ext-postgresql](https://github.com/swoole/ext-postgresql), pastikan menginstal versi rilis yang sesuai dengan versi Swoole.

* Pastikan pustaka `libpq` sudah terinstal di sistem
* Di `mac`, setelah menginstal `postgresql`, pustaka `libpq` sudah termasuk. Mungkin ada perbedaan antar lingkungan; di `ubuntu` mungkin perlu `apt-get install libpq-dev`, di `centos` mungkin perlu `yum install postgresql10-devel`
* Anda juga dapat menentukan direktori pustaka `libpq` secara terpisah, misalnya: `./configure --with-libpq-dir=/etc/postgresql`

## Contoh Penggunaan

```php
use Swoole\Coroutine\PostgreSQL;
use function Swoole\Coroutine\run;

run(function () {
    $pg = new PostgreSQL();
    $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=root password=");
    if (!$conn) {
        var_dump($pg->error);
        return;
    }
    $result = $pg->query('SELECT * FROM test;');
    $arr = $pg->fetchAll($result);
    var_dump($arr);
});
```

### Penanganan Transaksi

```php
use Swoole\Coroutine\PostgreSQL;
use function Swoole\Coroutine\run;

run(function () {
    $pg = new PostgreSQL();
    $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=root password=");
    $pg->query('BEGIN');
    $result = $pg->query('SELECT * FROM test');
    $arr = $pg->fetchAll($result);
    $pg->query('COMMIT');
    var_dump($arr);
});
```

## Properti

### error

Mendapatkan informasi error.

## Method

### connect()

Membangun koneksi coroutine non-blocking ke `postgresql`.

```php
Swoole\Coroutine\PostgreSQL->connect(string $connection_string): bool
```

!> `$connection_string` adalah informasi koneksi. Mengembalikan true jika koneksi berhasil, false jika gagal. Anda dapat menggunakan properti [error](/coroutine_client/postgresql?id=error) untuk mendapatkan informasi error.
  * **Contoh**

```php
use Swoole\Coroutine\PostgreSQL;
use function Swoole\Coroutine\run;

run(function () {
    $pg = new PostgreSQL();
    $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=wuzhenyu password=");
    var_dump($pg->error, $conn);
});
```

### query()

Menjalankan statement SQL. Mengirim perintah coroutine asinkron non-blocking.

```php
Swoole\Coroutine\PostgreSQL->query(string $sql): resource;
```

  * **Parameter**

    * **`string $sql`**
      * **Fungsi**: Statement SQL
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

  * **Contoh**

    * **select**

    ```php
    use Swoole\Coroutine\PostgreSQL;
    use function Swoole\Coroutine\run;

    run(function () {
        $pg = new PostgreSQL();
        $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=root password=");
        $result = $pg->query('SELECT * FROM test;');
        $arr = $pg->fetchAll($result);
        var_dump($arr);
    });
    ```

    * **Mengembalikan insert id**

    ```php
    use Swoole\Coroutine\PostgreSQL;
    use function Swoole\Coroutine\run;

    run(function () {
        $pg = new PostgreSQL();
        $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=wuzhenyu password=");
        $result = $pg->query("insert into test (id,text) VALUES (24,'text') RETURNING id ;");
        $arr = $pg->fetchRow($result);
        var_dump($arr);
    });
    ```

    * **transaction**

    ```php
    use Swoole\Coroutine\PostgreSQL;
    use function Swoole\Coroutine\run;

    run(function () {
        $pg = new PostgreSQL();
        $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=root password=");
        $pg->query('BEGIN;');
        $result = $pg->query('SELECT * FROM test;');
        $arr = $pg->fetchAll($result);
        $pg->query('COMMIT;');
        var_dump($arr);
    });
    ```

### fetchAll()

```php
Swoole\Coroutine\PostgreSQL->fetchAll(resource $queryResult, $resultType = SW_PGSQL_ASSOC):? array;
```

  * **Parameter**
    * **`$resultType`**
      * **Fungsi**: Konstanta. Parameter opsional yang mengontrol bagaimana nilai kembali diinisialisasi.
      * **Default**: `SW_PGSQL_ASSOC`
      * **Nilai lain**: Tidak ada

      Nilai | Nilai Kembali
      ---|---
      SW_PGSQL_ASSOC | Mengembalikan array asosiatif dengan nama field sebagai kunci
      SW_PGSQL_NUM | Mengembalikan array dengan nomor field sebagai kunci
      SW_PGSQL_BOTH | Mengembalikan array dengan keduanya sebagai kunci

  * **Nilai Kembali**

    * Mengambil semua baris dari hasil sebagai array.

### affectedRows()

Mengembalikan jumlah record yang terpengaruh.

```php
Swoole\Coroutine\PostgreSQL->affectedRows(resource $queryResult): int
```

### numRows()

Mengembalikan jumlah baris.

```php
Swoole\Coroutine\PostgreSQL->numRows(resource $queryResult): int
```

### fetchObject()

Mengambil satu baris sebagai objek.

```php
Swoole\Coroutine\PostgreSQL->fetchObject(resource $queryResult, int $row): object;
```

  * **Contoh**

```php
use Swoole\Coroutine\PostgreSQL;
use function Swoole\Coroutine\run;

run(function () {
    $pg = new PostgreSQL();
    $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=wuzhenyu");
    $result = $pg->query('SELECT * FROM test;');
    
    $row = 0;
    for ($row = 0; $row < $pg->numRows($result); $row++) {
        $data = $pg->fetchObject($result, $row);
        echo $data->id . " \n ";
    }
});
```
```php
use Swoole\Coroutine\PostgreSQL;
use function Swoole\Coroutine\run;

run(function () {
    $pg = new PostgreSQL();
    $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=wuzhenyu");
    $result = $pg->query('SELECT * FROM test;');
    
    $row = 0;
    while ($data = $pg->fetchObject($result, $row)) {
        echo $data->id . " \n ";
        $row++;
    }
});
```

### fetchAssoc()

Mengambil satu baris sebagai array asosiatif.

```php
Swoole\Coroutine\PostgreSQL->fetchAssoc(resource $queryResult, int $row): array
```

### fetchArray()

Mengambil satu baris sebagai array.

```php
Swoole\Coroutine\PostgreSQL->fetchArray(resource $queryResult, int $row, $resultType = SW_PGSQL_BOTH): array|false
```

  * **Parameter**
    * **`int $row`**
      * **Fungsi**: `row` adalah nomor baris (record) yang ingin diambil. Baris pertama adalah `0`.
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada
    * **`$resultType`**
      * **Fungsi**: Konstanta. Parameter opsional yang mengontrol bagaimana nilai kembali diinisialisasi.
      * **Default**: `SW_PGSQL_BOTH`
      * **Nilai lain**: Tidak ada

      Nilai | Nilai Kembali
      ---|---
      SW_PGSQL_ASSOC | Mengembalikan array asosiatif dengan nama field sebagai kunci
      SW_PGSQL_NUM | Mengembalikan array dengan nomor field sebagai kunci
      SW_PGSQL_BOTH | Mengembalikan array dengan keduanya sebagai kunci

  * **Nilai Kembali**

    * Mengembalikan array yang sesuai dengan baris (tuple/record) yang diambil. Jika tidak ada lagi baris yang tersedia, mengembalikan `false`.

  * **Contoh Penggunaan**

```php
use Swoole\Coroutine\PostgreSQL;
use function Swoole\Coroutine\run;

run(function () {
    $pg = new PostgreSQL();
    $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=wuzhenyu");
    $result = $pg->query('SELECT * FROM test;');
    $arr = $pg->fetchArray($result, 1, SW_PGSQL_ASSOC);
    var_dump($arr);
});
```

### fetchRow()

Mengambil satu baris data (record) dari resource `result` yang ditentukan dan mengembalikannya sebagai array. Setiap kolom yang diperoleh disimpan secara berurutan dalam array, dimulai dari offset `0`.

```php
Swoole\Coroutine\PostgreSQL->fetchRow(resource $queryResult, int $row, $resultType = SW_PGSQL_NUM): array|false
```

  * **Parameter**
    * **`int $row`**
      * **Fungsi**: `row` adalah nomor baris (record) yang akan diambil. Baris pertama adalah `0`.
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada
    * **`$resultType`**
      * **Fungsi**: Konstanta. Parameter opsional yang mengontrol bagaimana nilai kembali diinisialisasi.
      * **Default**: `SW_PGSQL_NUM`
      * **Nilai lain**: Tidak ada

      Nilai | Nilai Kembali
      ---|---
      SW_PGSQL_ASSOC | Mengembalikan array asosiatif dengan nama field sebagai kunci
      SW_PGSQL_NUM | Mengembalikan array dengan nomor field sebagai kunci
      SW_PGSQL_BOTH | Mengembalikan array dengan keduanya sebagai kunci

  * **Nilai Kembali**

    * Array yang dikembalikan sesuai dengan baris yang diekstrak. Jika tidak ada lagi baris `row` yang dapat diekstrak, mengembalikan `false`.

  * **Contoh Penggunaan**

```php
use Swoole\Coroutine\PostgreSQL;
use function Swoole\Coroutine\run;

run(function () {
    $pg = new PostgreSQL();
    $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=wuzhenyu");
    $result = $pg->query('SELECT * FROM test;');
    while ($row = $pg->fetchRow($result)) {
        echo "name: $row[0]  mobile: $row[1]" . PHP_EOL;
    }
});
```

### metaData()

Melihat metadata tabel. Versi coroutine asinkron non-blocking.

```php
Swoole\Coroutine\PostgreSQL->metaData(string $tableName): array
```

  * **Contoh Penggunaan**

```php
use Swoole\Coroutine\PostgreSQL;
use function Swoole\Coroutine\run;

run(function () {
    $pg = new PostgreSQL();
    $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=wuzhenyu");
    $result = $pg->metaData('test');
    var_dump($result);
});
```

### prepare()

Preprocessing.

```php
Swoole\Coroutine\PostgreSQL->prepare(string $name, string $sql);
Swoole\Coroutine\PostgreSQL->execute(string $name, array $bind);
```

  * **Contoh Penggunaan**

```php
use Swoole\Coroutine\PostgreSQL;
use function Swoole\Coroutine\run;

run(function () {
    $pg = new PostgreSQL();
    $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=wuzhenyu password=112");
    $pg->prepare("my_query", "select * from  test where id > $1 and id < $2");
    $res = $pg->execute("my_query", array(1, 3));
    $arr = $pg->fetchAll($res);
    var_dump($arr);
});
```
