# Data Type
Berikut adalah tipe data yang bisa dikirim dan dibagikan antar thread.

## Basic Type
Variabel tipe `null/bool/int/float`, ukuran memori kurang dari `16 Bytes`, dikirim sebagai nilai.

## String
String di-**copy memori**, disimpan ke `ArrayList`, `Queue`, `Map`.

## Socket Resource

### Daftar Tipe yang Didukung
- `Co\Socket`
- `PHP Stream`
- `PHP Socket(ext-sockets)`, perlu mengaktifkan parameter kompilasi `--enable-sockets`

### Tipe yang Tidak Didukung
- `Swoole\Client`
- `Swoole\Server`
- `Swoole\Coroutine\Client`
- `Swoole\Coroutine\Http\Client`
- Koneksi `pdo`
- Koneksi `redis`
- Tipe resource `Socket` khusus lainnya

### Duplikasi Resource

- Saat menulis, akan melakukan `dup(fd)`, terpisah dari resource asli, tidak saling memengaruhi. Menutup resource asli tidak akan memengaruhi resource baru
- Saat membaca, akan melakukan `dup(fd)`, membangun resource `Socket` baru di `VM` child thread yang membaca
- Saat menghapus, akan melakukan `close(fd)`, membebaskan file handle

Artinya, `Socket` resource akan memiliki `3` reference count:
- Thread tempat `Socket` resource pertama kali dibuat
- Container `ArrayList`, `Queue`, `Map`
- Child thread yang membaca container `ArrayList`, `Queue`, `Map`

Saat tidak ada thread atau container yang memegang resource ini, dan reference count turun ke `0`, `Socket` resource baru benar-benar dibebaskan. Jika reference count tidak `0`,
meskipun `close` dijalankan, koneksi tidak akan ditutup dan tidak akan memengaruhi `Socket` resource yang dipegang thread atau container lain.

Jika ingin mengabaikan reference count dan langsung menutup `Socket`, bisa menggunakan method `shutdown()`, contoh:
- `stream_socket_shutdown()`
- `Socket::shutdown()`
- `socket_shutdown()`

> Operasi `shutdown` akan memengaruhi semua thread yang memegang `Socket` resource, setelah dijalankan tidak bisa digunakan lagi, tidak bisa melakukan operasi `read/write`

## Array
Gunakan `array_is_list()` untuk menentukan tipe array. Jika array numeric index, akan diubah ke `ArrayList`; jika associative array, akan diubah ke `Map`.

- Seluruh array akan diiterasi, elemen dimasukkan ke `ArrayList` atau `Map`
- Mendukung multidimensional array, rekursif mengubahnya menjadi `ArrayList` atau `Map` bersarang

Contoh:
```php
$array = [
    'a' => random_int(1, 999999999999999999),
    'b' => random_bytes(128),
    'c' => uniqid(),
    'd' => time(),
    'e' => [
        'key' => 'value',
        'hello' => 'world',
    ];
];

$map = new Map($array);

// $map['e'] adalah object Map baru, berisi dua elemen, key dan hello, dengan nilai 'value' dan 'world'
var_dump($map['e']);
```

## Object
### Thread Resource Object

Object resource thread seperti `Thread\Lock`, `Thread\Atomic`, `Thread\ArrayList`, `Thread\Map`, dll, bisa langsung disimpan ke `ArrayList`, `Queue`, `Map`.
Operasi ini hanya menyimpan referensi object ke container, tidak melakukan copy object.

Saat menulis object ke `ArrayList` atau `Map`, hanya menambah reference count pada resource thread, tidak melakukan copy. Saat reference count object menjadi `0`, object akan dibebaskan.

Contoh:

```php
$map = new Thread\Map;
$lock = new Thread\Lock; // Reference count saat ini 1
$map['lock'] = $lock; // Reference count saat ini 2
unset($map['lock']); // Reference count saat ini 1
unset($lock); // Reference count saat ini 0, Lock object dibebaskan
```

Daftar yang didukung:

- `Thread\Lock`
- `Thread\Atomic`
- `Thread\Atomic\Long`
- `Thread\Barrier`
- `Thread\ArrayList`
- `Thread\Map`
- `Thread\Queue`

Perhatikan bahwa object `Thread` tidak bisa diserialisasi dan tidak bisa dikirim, hanya tersedia di parent thread.

### Object PHP Biasa
Akan otomatis diserialisasi saat ditulis, dan di-deserialisasi saat dibaca. Perhatikan bahwa jika object mengandung tipe yang tidak bisa diserialisasi, akan melempar exception.
