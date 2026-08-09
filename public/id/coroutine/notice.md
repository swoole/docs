# Hal yang Perlu Diketahui dalam Pemrograman Coroutine

Saat menggunakan fitur [coroutine](/coroutine) Swoole, harap baca bab ini dengan saksama.

## Paradigma Pemrograman

* Dilarang menggunakan variabel global di dalam coroutine
* Coroutine yang menggunakan kata kunci `use` untuk membawa variabel eksternal ke dalam lingkup saat ini, dilarang menggunakan referensi
* Komunikasi antar coroutine harus menggunakan [Channel](/coroutine/channel)

!> Artinya, komunikasi antar coroutine jangan menggunakan variabel global atau mereferensi variabel eksternal ke lingkup saat ini, melainkan gunakan `Channel`

* Jika proyek memiliki ekstensi yang melakukan `hook` pada `zend_execute_ex` atau `zend_execute_internal`, perlu perhatian khusus pada C stack. Bisa menggunakan [Co::set](/coroutine/coroutine?id=set) untuk mengatur ulang ukuran C stack.

!> Setelah melakukan `hook` pada kedua fungsi entry ini, sebagian besar panggilan instruksi PHP yang datar akan berubah menjadi panggilan fungsi `C`, meningkatkan konsumsi C stack.

## Keluar dari Coroutine

Di versi Swoole lama, menggunakan `exit` untuk keluar paksa dari skrip di dalam coroutine dapat menyebabkan error memori yang mengakibatkan hasil tak terduga atau `coredump`. Di layanan Swoole, menggunakan `exit` akan menyebabkan seluruh process service keluar dan semua coroutine di dalamnya berhenti secara abnormal, menyebabkan masalah serius. Swoole sejak lama melarang developer menggunakan `exit`, namun developer bisa menggunakan cara tidak biasa dengan melempar exception, lalu menangkapnya di lapisan atas untuk mencapai logika keluar yang sama dengan `exit`.

!> Versi v4.2.2 ke atas mengizinkan skrip (yang belum membuat `http_server`) untuk `exit` hanya dengan coroutine saat ini.

Swoole **v4.1.0** ke atas mendukung langsung penggunaan `exit` PHP di dalam `coroutine` dan `service event loop`. Dalam kasus ini, infrastruktur secara otomatis akan melempar `Swoole\ExitException` yang bisa ditangkap, sehingga developer dapat menangkapnya di posisi yang diperlukan dan mengimplementasikan logika keluar yang sama seperti PHP asli.

### Swoole\ExitException

`Swoole\ExitException` mewarisi `Exception` dan menambahkan dua method baru `getStatus` dan `getFlags`:

```php
namespace Swoole;

class ExitException extends \Exception
{
	public function getStatus(): mixed
	public function getFlags(): int
}
```

#### getStatus()

Mendapatkan parameter `status` yang diteruskan saat `exit($status)`, mendukung tipe variabel apa pun.

```php
public function getStatus(): mixed
```

#### getFlags()

Mendapatkan mask informasi lingkungan saat exit.

```php
public function getFlags(): int
```

Berikut mask yang tersedia:

| Konstanta | Keterangan |
| -- | -- |
| SWOOLE_EXIT_IN_COROUTINE | Keluar di coroutine |
| SWOOLE_EXIT_IN_SERVER | Keluar di Server |

### Cara Penggunaan

#### Penggunaan Dasar

```php
use Swoole\Coroutine;
use function Swoole\Coroutine\run;

function route()
{
    controller();
}

function controller()
{
    your_code();
}

function your_code()
{
    Coroutine::sleep(.001);
    exit(1);
}

run(function () {
    try {
        route();
    } catch (\Swoole\ExitException $e) {
        var_dump($e->getMessage());
        var_dump($e->getStatus() === 1);
        var_dump($e->getFlags() === SWOOLE_EXIT_IN_COROUTINE);
    }
});
```

#### Keluar dengan Kode Status

```php
use function Swoole\Coroutine\run;

$exit_status = 0;
run(function () {
    try {
        exit(123);
    } catch (\Swoole\ExitException $e) {
        global $exit_status;
        $exit_status = $e->getStatus();
    }
});
var_dump($exit_status);
```

## Penanganan Exception

Dalam pemrograman coroutine, exception bisa langsung ditangani dengan `try/catch`. **Namun harus ditangkap di dalam coroutine, tidak boleh menangkap exception lintas coroutine**.

!> Tidak hanya `Exception` yang dilempar di lapisan aplikasi, beberapa error infrastruktur juga bisa ditangkap, seperti `function`, `class`, `method` yang tidak ada.

### Contoh Salah

Pada kode di bawah, `try/catch` dan `throw` berada di coroutine yang berbeda, exception tidak bisa ditangkap di dalam coroutine. Saat coroutine keluar dan menemukan exception yang tidak tertangkap, akan menyebabkan fatal error.

```bash
PHP Fatal error:  Uncaught RuntimeException
```

```php
try {
	Swoole\Coroutine::create(function () {
		throw new \RuntimeException(__FILE__, __LINE__);
	});
}
catch (\Throwable $e) {
	echo $e;
}
```

### Contoh Benar

Menangkap exception di dalam coroutine.

```php
function test() {
	throw new \RuntimeException(__FILE__, __LINE__);
}

Swoole\Coroutine::create(function () {
	try {
		test();
	}
	catch (\Throwable $e) {
		echo $e;
	}
});
```

## Tidak Boleh Terjadi Perpindahan Coroutine di Method Magic __get / __set

Alasan: [Lihat Analisis Internal PHP7](https://github.com/pangudashu/php7-internal/blob/40645cfe087b373c80738881911ae3b178818f11/3/zend_object.md)

> **Catatan:** Jika kelas memiliki method __get(), saat mengalokasikan memori properti objek (yaitu: properties_table) akan mengalokasikan satu zval tambahan bertipe HashTable. Setiap kali __get($var) dipanggil, nama $var yang dimasukkan akan disimpan di hash table ini. Tujuannya untuk mencegah pemanggilan rekursif. Contoh:
> 
> ***public function __get($var) { return $this->$var; }***
>
> Situasi ini adalah saat memanggil __get() kemudian mengakses properti yang tidak ada, yang akan memanggil __get() secara rekursif. Jika tidak ada pengecekan terhadap $var yang diminta, rekursi akan terus berlanjut. Jadi sebelum memanggil __get(), pertama-tama akan diperiksa apakah $var saat ini sudah ada di __get(), jika sudah, __get() tidak akan dipanggil lagi. Jika tidak, $var akan dimasukkan sebagai key ke HashTable tersebut, lalu nilai hash diatur ke: *guard |= IN_ISSET, setelah selesai memanggil __get() nilai hash diatur ke: *guard &= ~IN_ISSET.
>
> HashTable ini tidak hanya digunakan untuk __get(), method magic lain juga menggunakannya, jadi tipe nilai hash-nya adalah zend_long, method magic yang berbeda menempati bit yang berbeda. Tidak semua objek akan mengalokasikan HashTable tambahan ini. Saat pembuatan objek, akan ditentukan berdasarkan ***zend_class_entry.ce_flags*** apakah mengandung ***ZEND_ACC_USE_GUARDS***. Saat kompilasi kelas, jika ditemukan method __get(), __set(), __unset(), __isset(), maka ce_flags akan diberi mask ini.

Setelah perpindahan coroutine keluar, panggilan berikutnya akan dianggap sebagai panggilan rekursif. Masalah ini disebabkan oleh **fitur** PHP, dan setelah berdiskusi dengan tim pengembang PHP, belum ada solusi untuk saat ini.

Catatan: Meskipun di method magic tidak ada kode yang menyebabkan perpindahan coroutine, namun jika penjadwalan preemptive coroutine diaktifkan, method magic tetap bisa dipindahkan secara paksa oleh coroutine.

Saran: Implementasikan method `get`/`set` sendiri secara eksplisit

Tautan issue asli: [#2625](https://github.com/swoole/swoole-src/issues/2625)

## Error Serius

Tindakan berikut dapat menyebabkan error serius.

### Berbagi Satu Koneksi di Antara Banyak Coroutine

Tidak seperti program blocking sinkron, coroutine memproses permintaan secara konkuren. Oleh karena itu, pada saat yang sama mungkin ada banyak permintaan yang diproses secara paralel. Jika berbagi koneksi klien, akan terjadi kekacauan data antar coroutine. Lihat: [Berbagi Koneksi TCP di Banyak Coroutine](/question/use?id=client-has-already-been-bound-to-another-coroutine)

### Menggunakan Variabel Statis Kelas / Variabel Global untuk Menyimpan Konteks

Banyak coroutine dieksekusi secara konkuren, jadi tidak boleh menggunakan variabel statis kelas / variabel global untuk menyimpan konteks coroutine. Menggunakan variabel lokal aman karena nilai variabel lokal otomatis disimpan di stack coroutine, dan coroutine lain tidak bisa mengakses variabel lokal coroutine lain.

#### Contoh Salah

```php
$server = new Swoole\Http\Server('127.0.0.1', 9501);

$_array = [];
$server->on('request', function ($request, $response) {
    global $_array;
    // Request /a (coroutine 1)
    if ($request->server['request_uri'] == '/a') {
        $_array['name'] = 'a';
        co::sleep(1.0);
        echo $_array['name'];
        $response->end($_array['name']);
    }
    // Request /b (coroutine 2)
    else {
        $_array['name'] = 'b';
        $response->end();
    }
});
$server->start();
```

Kirim `2` permintaan konkuren.

```shell
curl http://127.0.0.1:9501/a
curl http://127.0.0.1:9501/b
```

* Coroutine `1` mengatur nilai variabel global `$_array['name']` menjadi `a`
* Coroutine `1` memanggil `co::sleep` dan ditangguhkan
* Coroutine `2` dijalankan, mengubah nilai `$_array['name']` menjadi `b`, coroutine 2 selesai
* Timer kembali, infrastruktur melanjutkan coroutine `1`. Ada ketergantungan konteks dalam logika coroutine `1`. Saat mencetak `$_array['name']`, yang diharapkan adalah `a`, tapi nilai ini sudah diubah oleh coroutine `2`, hasil sebenarnya adalah `b`, menyebabkan kesalahan logika
* Demikian pula, menggunakan variabel statis kelas `Class::$array`, properti objek global `$object->array`, atau superglobal lain `$GLOBALS` untuk menyimpan konteks dalam program coroutine sangat berbahaya. Bisa terjadi perilaku yang tidak sesuai harapan.

![](../_images/coroutine/notice-1.png)

#### Contoh Benar: Mengelola Konteks dengan Context

Bisa menggunakan kelas `Context` untuk mengelola konteks coroutine. Di kelas `Context`, gunakan `Coroutine::getuid` untuk mendapatkan `ID` coroutine, lalu isolasi variabel global antar coroutine yang berbeda, dan bersihkan data konteks saat coroutine keluar.

```php
use Swoole\Coroutine;

class Context
{
    protected static $pool = [];

    static function get($key)
    {
        $cid = Coroutine::getuid();
        if ($cid < 0)
        {
            return null;
        }
        if(isset(self::$pool[$cid][$key])){
            return self::$pool[$cid][$key];
        }
        return null;
    }

    static function put($key, $item)
    {
        $cid = Coroutine::getuid();
        if ($cid > 0)
        {
            self::$pool[$cid][$key] = $item;
        }

    }

    static function delete($key = null)
    {
        $cid = Coroutine::getuid();
        if ($cid > 0)
        {
            if($key){
                unset(self::$pool[$cid][$key]);
            }else{
                unset(self::$pool[$cid]);
            }
        }
    }
}
```

Penggunaan:

```php
use Swoole\Coroutine\Context;

$server = new Swoole\Http\Server('127.0.0.1', 9501);

$server->on('request', function ($request, $response) {
    if ($request->server['request_uri'] == '/a') {
        Context::put('name', 'a');
        co::sleep(1.0);
        echo Context::get('name');
        $response->end(Context::get('name'));
        // Bersihkan saat keluar coroutine
        Context::delete('name');
    } else {
        Context::put('name', 'b');
        $response->end();
        // Bersihkan saat keluar coroutine
        Context::delete();
    }
});
$server->start();
```
