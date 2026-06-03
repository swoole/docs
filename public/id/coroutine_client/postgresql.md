# Coroutine\PostgreSQL

Klien `PostgreSQL` coroutine.

!> Direkonstruksi ulang di Swoole 5.0, dengan penggunaan yang sangat berbeda dari versi lama. Jika Anda menggunakan versi lama, silakan lihat [dokumentasi lama](/coroutine_client/postgresql-old.md).

!> Setelah Swoole 6.0, klien `PostgreSQL` coroutine telah dihapus, silakan gunakan [pdo_pgsql coroutine](/runtime?id=swoole_hook_pdo_pgsql) sebagai gantinya

## Kompilasi dan Instalasi

* Pastikan pustaka `libpq` sudah terinstal di sistem
* Di `mac`, setelah menginstal `postgresql`, pustaka `libpq` sudah termasuk. Mungkin ada perbedaan antar lingkungan; di `ubuntu` mungkin perlu `apt-get install libpq-dev`, di `centos` mungkin perlu `yum install postgresql10-devel`
* Saat mengompilasi Swoole, tambahkan opsi kompilasi: `./configure --enable-swoole-pgsql`

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
    $stmt = $pg->query('SELECT * FROM test;');
    $arr = $stmt->fetchAll();
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
    $stmt = $pg->query('SELECT * FROM test');
    $arr = $stmt->fetchAll();
    $pg->query('COMMIT');
    var_dump($arr);
});
```

## Properti

### error

Mendapatkan pesan error.

## Method

### connect()

Membangun koneksi coroutine non-blocking ke `postgresql`.

```php
Swoole\Coroutine\PostgreSQL->connect(string $conninfo, float $timeout = 2): bool
```

!> `$conninfo` adalah informasi koneksi. Mengembalikan true jika koneksi berhasil, false jika gagal. Anda dapat menggunakan properti [error](/coroutine_client/postgresql?id=error) untuk mendapatkan informasi error.
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
Swoole\Coroutine\PostgreSQL->query(string $sql): \Swoole\Coroutine\PostgreSQLStatement|false;
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
        $stmt = $pg->query('SELECT * FROM test;');
        $arr = $stmt->fetchAll();
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
        $stmt = $pg->query("insert into test (id,text) VALUES (24,'text') RETURNING id ;");
        $arr = $stmt->fetchRow();
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
        $stmt = $pg->query('SELECT * FROM test;');
        $arr = $stmt->fetchAll();
        $pg->query('COMMIT;');
        var_dump($arr);
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
$stmt = Swoole\Coroutine\PostgreSQL->prepare(string $sql);
$stmt->execute(array $params);
```

  * **Contoh Penggunaan**

```php
use Swoole\Coroutine\PostgreSQL;
use function Swoole\Coroutine\run;

run(function () {
    $pg = new PostgreSQL();
    $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=wuzhenyu password=112");
    $stmt = $pg->prepare("select * from test where id > $1 and id < $2");
    $res = $stmt->execute(array(1, 3));
    $arr = $stmt->fetchAll();
    var_dump($arr);
});
```

## PostgreSQLStatement

Nama kelas: `Swoole\Coroutine\PostgreSQLStatement`

Semua query akan mengembalikan objek `PostgreSQLStatement`

### fetchAll()

```php
Swoole\Coroutine\PostgreSQLStatement->fetchAll(int $result_type = SW_PGSQL_ASSOC): false|array;
```

  * **Parameter**
    * **`$result_type`**
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
Swoole\Coroutine\PostgreSQLStatement->affectedRows(): int
```

### numRows()

Mengembalikan jumlah baris.

```php
Swoole\Coroutine\PostgreSQLStatement->numRows(): int
```

### fetchObject()

Mengambil satu baris sebagai objek.

```php
Swoole\Coroutine\PostgreSQLStatement->fetchObject(int $row, ?string $class_name = null, array $ctor_params = []): object;
```

  * **Contoh**

```php
use Swoole\Coroutine\PostgreSQL;
use function Swoole\Coroutine\run;

run(function () {
    $pg = new PostgreSQL();
    $conn = $pg->connect("host=127.0.0.1 port=5432 dbname=test user=wuzhenyu");
    $stmt = $pg->query('SELECT * FROM test;');
    
    $row = 0;
    for ($row = 0; $row < $stmt->numRows(); $row++) {
        $data = $stmt->fetchObject($row);
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
    $stmt = $pg->query('SELECT * FROM test;');
    
    $row = 0;
    while ($data = $stmt->fetchObject($row)) {
        echo $data->id . " \n ";
        $row++;
    }
});
```

### fetchAssoc()

Mengambil satu baris sebagai array asosiatif.

```php
Swoole\Coroutine\PostgreSQLStatement->fetchAssoc(int $row, int $result_type = SW_PGSQL_ASSOC): array
```

### fetchArray()

Mengambil satu baris sebagai array.

```php
Swoole\Coroutine\PostgreSQLStatement->fetchArray(int $row, int $result_type = SW_PGSQL_BOTH): array|false
```

  * **Parameter**
    * **`int $row`**
      * **Fungsi**: `row` adalah nomor baris (record) yang ingin diambil. Baris pertama adalah `0`.
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada
    * **`$result_type`**
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
    $stmt = $pg->query('SELECT * FROM test;');
    $arr = $stmt->fetchArray(1, SW_PGSQL_ASSOC);
    var_dump($arr);
});
```

### fetchRow()

Mengambil satu baris data (record) berdasarkan resource `result` yang ditentukan dan mengembalikannya sebagai array. Setiap kolom yang diperoleh disimpan secara berurutan dalam array, dimulai dari offset `0`.

```php
Swoole\Coroutine\PostgreSQLStatement->fetchRow(int $row, int $result_type = SW_PGSQL_NUM): array|false
```

  * **Parameter**
    * **`int $row`**
      * **Fungsi**: `row` adalah nomor baris (record) yang akan diambil. Baris pertama adalah `0`.
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada
    * **`$result_type`**
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
    $stmt = $pg->query('SELECT * FROM test;');
    while ($row = $stmt->fetchRow()) {
        echo "name: $row[0]  mobile: $row[1]" . PHP_EOL;
    }
});
```
