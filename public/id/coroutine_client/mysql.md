# Coroutine\MySQL

Klien MySQL coroutine.

!> Klien ini tidak lagi direkomendasikan. Disarankan menggunakan `Swoole\Runtime::enableCoroutine` + `pdo_mysql` atau `mysqli`, yaitu [one-click coroutine](/runtime) untuk klien `MySQL` native.  
!> Setelah `Swoole 6.0`, klien `MySQL` coroutine ini telah dihapus.

## Contoh Penggunaan

```php
use Swoole\Coroutine\MySQL;
use function Swoole\Coroutine\run;

run(function () {
    $swoole_mysql = new MySQL();
    $swoole_mysql->connect([
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'user'     => 'user',
        'password' => 'pass',
        'database' => 'test',
    ]);
    $res = $swoole_mysql->query('select sleep(1)');
    var_dump($res);
});
```

## Fitur defer

Silakan lihat bagian [Klien Konkuren](/coroutine/multi_call).

## Stored Procedure

Sejak versi `4.0.0`, mendukung stored procedure `MySQL` dan pengambilan beberapa result set.

## MySQL8.0

`Swoole-4.0.1` atau versi lebih tinggi mendukung semua kemampuan verifikasi keamanan `MySQL8`, sehingga Anda dapat langsung menggunakan klien tanpa harus mengatur ulang pengaturan password.

### Versi di bawah 4.0.1

`MySQL-8.0` menggunakan plugin `caching_sha2_password` yang lebih aman secara default. Jika Anda upgrade dari `5.x`, Anda dapat langsung menggunakan semua fitur `MySQL`. Untuk `MySQL` yang baru dibuat, Anda perlu menjalankan perintah berikut di baris perintah `MySQL` untuk membuatnya kompatibel:

```SQL
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'password';
flush privileges;
```

Ganti `'root'@'localhost'` dalam pernyataan dengan user yang Anda gunakan, dan `password` dengan password user tersebut.

Jika masih tidak dapat digunakan, atur `default_authentication_plugin = mysql_native_password` di my.cnf.

## Properti

### serverInfo

Informasi koneksi, disimpan sebagai array yang diteruskan ke fungsi koneksi.

### sock

Deskriptor file yang digunakan untuk koneksi.

### connected

Apakah sudah terhubung ke server `MySQL`.

!> Lihat [Ketidaksesuaian properti connected dengan status koneksi](/question/use?id=ketidaksesuaian-properti-connected-dengan-status-koneksi)

### connect_error

Pesan error saat menjalankan `connect` ke server.

### connect_errno

Kode error saat menjalankan `connect` ke server, bertipe integer.

### error

Pesan error yang dikembalikan server saat menjalankan perintah `MySQL`.

### errno

Kode error yang dikembalikan server saat menjalankan perintah `MySQL`, bertipe integer.

### affected_rows

Jumlah baris yang terpengaruh.

### insert_id

`id` dari record terakhir yang disisipkan.

## Method

### connect()

Membangun koneksi MySQL.

```php
Swoole\Coroutine\MySQL->connect(array $serverInfo): bool
```

!> `$serverInfo`: Parameter diteruskan dalam format array.

```php
[
    'host'        => 'Alamat IP MySQL', // Jika UNIX Socket lokal, harus diisi dengan format seperti `unix://tmp/your_file.sock`
    'user'        => 'User database',
    'password'    => 'Password database',
    'database'    => 'Nama database',
    'port'        => 'Port MySQL default 3306 parameter opsional',
    'timeout'     => 'Waktu timeout koneksi', // Hanya memengaruhi timeout koneksi, tidak memengaruhi method query dan execute, lihat `aturan timeout klien`
    'charset'     => 'Set karakter',
    'strict_type' => false, // Aktifkan mode ketat, data yang dikembalikan method query juga akan dikonversi ke tipe kuat
    'fetch_mode'  => true,  // Aktifkan mode fetch, bisa menggunakan fetch/fetchAll seperti pdo untuk mengambil baris demi baris atau seluruh result set (versi 4.0 ke atas)
]
```

### query()

Menjalankan statement SQL.

```php
Swoole\Coroutine\MySQL->query(string $sql, float $timeout = 0): array|false
```

  * **Parameter**

    * **`string $sql`**
      * **Fungsi**: Statement SQL
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

    * **`float $timeout`**
      * **Fungsi**: Durasi timeout 【Jika server `MySQL` gagal mengembalikan data dalam waktu yang ditentukan, sistem akan mengembalikan `false`, mengatur kode error ke `110`, dan memutus koneksi】
      * **Satuan**: Detik, dengan presisi minimum milidetik (`0.001` detik)
      * **Default**: `0`
      * **Nilai lain**: Tidak ada
      * **Lihat [aturan timeout klien](/coroutine_client/init?id=aturan-timeout)**

  * **Return Value**

    * Timeout/error mengembalikan `false`, selain itu mengembalikan hasil query dalam bentuk `array`

  * **Penerimaan Tertunda**

    !> Setelah mengatur `defer`, memanggil `query` akan langsung mengembalikan `true`. Memanggil `recv` akan masuk ke penungguan coroutine dan mengembalikan hasil query.

  * **Contoh**

```php
use Swoole\Coroutine\MySQL;
use function Swoole\Coroutine\run;

run(function () {
    $swoole_mysql = new MySQL();
    $swoole_mysql->connect([
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'user'     => 'user',
        'password' => 'pass',
        'database' => 'test',
    ]);
    $res = $swoole_mysql->query('show tables');
    if ($res === false) {
        return;
    }
    var_dump($res);
});
```

### prepare()

Mengirim permintaan prepared SQL ke server MySQL.

!> `prepare` harus digunakan bersama dengan `execute`. Setelah permintaan prepared berhasil, panggil method `execute` untuk mengirim parameter data ke server `MySQL`.

```php
Swoole\Coroutine\MySQL->prepare(string $sql, float $timeout): Swoole\Coroutine\MySQL\Statement|false;
```

  * **Parameter**

    * **`string $sql`**
      * **Fungsi**: Statement prepared【menggunakan `?` sebagai placeholder parameter】
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

    * **`float $timeout`**
      * **Fungsi**: Waktu timeout
      * **Satuan**: Detik, dengan presisi minimum milidetik (`0.001` detik)
      * **Default**: `0`
      * **Nilai lain**: Tidak ada
      * **Lihat [aturan timeout klien](/coroutine_client/init?id=aturan-timeout)**

  * **Return Value**

    * Mengembalikan `false`, dapat memeriksa `$db->error` dan `$db->errno` untuk menentukan penyebab error
    * Mengembalikan objek `Coroutine\MySQL\Statement`, dapat memanggil method [execute](/coroutine_client/mysql?id=statement-execute) objek untuk mengirim parameter

  * **Contoh**

```php
use Swoole\Coroutine\MySQL;
use function Swoole\Coroutine\run;

run(function () {
    $db = new MySQL();
    $ret1 = $db->connect([
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'user'     => 'root',
        'password' => 'root',
        'database' => 'test',
    ]);
    $stmt = $db->prepare('SELECT * FROM userinfo WHERE id=?');
    if ($stmt == false) {
        var_dump($db->errno, $db->error);
    } else {
        $ret2 = $stmt->execute(array(10));
        var_dump($ret2);
    }
});
```

### escape()

Melakukan escape karakter khusus dalam statement SQL untuk mencegah serangan SQL injection. Diimplementasikan berdasarkan fungsi yang disediakan oleh `mysqlnd` dan memerlukan ekstensi `mysqlnd` di `PHP`.

!> Saat kompilasi perlu menambahkan [--enable-mysqlnd](/environment?id=opsi-kompilasi) untuk mengaktifkannya.

```php
Swoole\Coroutine\MySQL->escape(string $str): string
```

  * **Parameter**

    * **`string $str`**
      * **Fungsi**: Karakter yang akan di-escape
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

  * **Contoh Penggunaan**

```php
use Swoole\Coroutine\MySQL;
use function Swoole\Coroutine\run;

run(function () {
    $db = new MySQL();
    $db->connect([
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'user'     => 'root',
        'password' => 'root',
        'database' => 'test',
    ]);
    $data = $db->escape("abc'efg\r\n");
});
```

### begin()

Memulai transaksi. Digabungkan dengan `commit` dan `rollback` untuk menangani transaksi `MySQL`.

```php
Swoole\Coroutine\MySQL->begin(): bool
```

!> Memulai transaksi `MySQL`. Mengembalikan `true`, gagal mengembalikan `false`. Periksa `$db->errno` untuk mendapatkan kode error.

!> Dengan objek koneksi `MySQL` yang sama, hanya satu transaksi yang dapat dimulai dalam satu waktu;  
harus menunggu transaksi sebelumnya di-`commit` atau di-`rollback` untuk memulai transaksi baru;  
jika tidak, sistem akan melempar exception `Swoole\MySQL\Exception` dengan `code` `21`.

  * **Contoh**

    ```php
    $db->begin();
    $db->query("update userinfo set level = 22 where id = 1");
    $db->commit();
    ```

### commit()

Menyelesaikan transaksi.

!> Harus digunakan bersama dengan `begin`.

```php
Swoole\Coroutine\MySQL->commit(): bool
```

!> Mengembalikan `true`, gagal mengembalikan `false`. Periksa `$db->errno` untuk mendapatkan kode error.

### rollback()

Membatalkan transaksi.

!> Harus digunakan bersama dengan `begin`.

```php
Swoole\Coroutine\MySQL->rollback(): bool
```

!> Mengembalikan `true`, gagal mengembalikan `false`. Periksa `$db->errno` untuk mendapatkan kode error.

### Statement->execute()

Mengirim parameter data prepared SQL ke server MySQL.

!> `execute` harus digunakan bersama dengan `prepare`, `prepare` harus dipanggil sebelum `execute` untuk memulai permintaan prepared.

!> Method `execute` dapat dipanggil beberapa kali.

```php
Swoole\Coroutine\MySQL\Statement->execute(array $params, float $timeout = -1): array|bool
```

  * **Parameter**

    * **`array $params`**
      * **Fungsi**: Parameter data prepared【Harus sama jumlahnya dengan parameter statement `prepare`. `$params` harus array dengan indeks numerik, urutan parameter sama dengan statement `prepare`】
      * **Default**: Tidak ada
      * **Nilai lain**: Tidak ada

    * **`float $timeout`**
      * **Fungsi**: Waktu timeout【Jika server `MySQL` gagal mengembalikan data dalam waktu yang ditentukan, sistem akan mengembalikan `false`, mengatur kode error ke `110`, dan memutus koneksi】
      * **Satuan**: Detik, dengan presisi minimum milidetik (`0.001` detik)
      * **Default**: `-1`
      * **Nilai lain**: Tidak ada
      * **Lihat [aturan timeout klien](/coroutine_client/init?id=aturan-timeout)**

  * **Return Value**

    * Mengembalikan `true`, jika parameter `fetch_mode` di `connect` diatur ke `true`
    * Mengembalikan array dataset, jika bukan kasus di atas
    * Mengembalikan `false`, dapat memeriksa `$db->error` dan `$db->errno` untuk menentukan penyebab error

  * **Contoh Penggunaan**

```php
use Swoole\Coroutine\MySQL;
use function Swoole\Coroutine\run;

run(function () {
    $db = new MySQL();
    $ret1 = $db->connect([
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'user'     => 'root',
        'password' => 'root',
        'database' => 'test',
    ]);
    $stmt = $db->prepare('SELECT * FROM userinfo WHERE id=? and name=?');
    if ($stmt == false) {
        var_dump($db->errno, $db->error);
    } else {
        $ret2 = $stmt->execute(array(10, 'rango'));
        var_dump($ret2);

        $ret3 = $stmt->execute(array(13, 'alvin'));
        var_dump($ret3);
    }
});
```

### Statement->fetch()

Mengambil baris berikutnya dari result set.

```php
Swoole\Coroutine\MySQL\Statement->fetch(): ?array
```

!> Swoole versi >= `4.0-rc1`, perlu menambahkan opsi `fetch_mode => true` saat connect

  * **Contoh**

```php
$stmt = $db->prepare('SELECT * FROM ckl LIMIT 1');
$stmt->execute();
while ($ret = $stmt->fetch()) {
    var_dump($ret);
}
```

!> Mulai dari driver `MySQL` baru di `v4.4.0`, `fetch` harus digunakan dengan cara seperti kode contoh sampai membaca `NULL`, jika tidak, permintaan baru tidak dapat dilakukan (karena mekanisme baca sesuai kebutuhan, dapat menghemat memori)

### Statement->fetchAll()

Mengembalikan array yang berisi semua baris dalam result set.

```php
Swoole\Coroutine\MySQL\Statement->fetchAll():? array
```

!> Swoole versi >= `4.0-rc1`, perlu menambahkan opsi `fetch_mode => true` saat `connect`

  * **Contoh**

```php
$stmt = $db->prepare('SELECT * FROM ckl LIMIT 1');
$stmt->execute();
$stmt->fetchAll();
```

### Statement->nextResult()

Maju ke hasil respons berikutnya dalam handle statement multi-respons (misalnya, beberapa hasil dari stored procedure).

```php
Swoole\Coroutine\MySQL\Statement->nextResult():? bool
```

  * **Return Value**

    * Mengembalikan `TRUE`
    * Mengembalikan `FALSE`
    * Tidak ada hasil berikutnya mengembalikan `NULL`

  * **Contoh**

    * **Mode non-fetch**

    ```php
    $stmt = $db->prepare('CALL reply(?)');
    $res  = $stmt->execute(['hello mysql!']);
    do {
      var_dump($res);
    } while ($res = $stmt->nextResult());
    var_dump($stmt->affected_rows);
    ```

    * **Mode fetch**

    ```php
    $stmt = $db->prepare('CALL reply(?)');
    $stmt->execute(['hello mysql!']);
    do {
      $res = $stmt->fetchAll();
      var_dump($res);
    } while ($stmt->nextResult());
    var_dump($stmt->affected_rows);
    ```

!> Mulai dari driver `MySQL` baru di `v4.4.0`, `fetch` harus dibaca sampai `NULL` menggunakan cara kode contoh, jika tidak, permintaan baru tidak dapat dilakukan (karena mekanisme baca sesuai kebutuhan, dapat menghemat memori)
