# Ekstensi Standard Library PHP (`stdext`)

Mulai dari versi `6.1.0`, `Swoole` menambahkan modul `stdext` untuk memperluas standard library `PHP`, memberikan ekstensi dan perbaikan pada sintaks dasar yang lama tidak diperbaiki oleh `PHP` official tetapi sangat diinginkan oleh komunitas.

## Instalasi
Saat kompilasi perlu menambahkan opsi `--enable-swoole-stdext` untuk mengaktifkan modul ini.

> Modul ini mendukung mode `php-fpm` dan `cli`

## Array dan String Sebagai Object

Di bahasa pemrograman lain, array dan string ada sebagai object, misalnya `C++`, `Java`, `JS`, `Python`, `Golang`, dll. Array dan string memiliki built-in method untuk berbagai operasi. Sementara di `PHP`, perlu menggunakan fungsi untuk mengoperasikan array dan string, yang lebih mirip dengan bahasa `C` jaman dulu.

Ekstensi sintaks ini menjadikan `string` dan `array` sebagai `final class` bawaan, sehingga bisa langsung menggunakan object method untuk beroperasi.
Built-in method tipe dasar pada dasarnya tetap memanggil implementasi `PHP` standard library. Built-in method memiliki korespondensi satu-satu dengan fungsi `str_` atau `array_` di `PHP`,
misalnya `$text->replace()` setara dengan fungsi `str_replace`.
Sistem hanya menyesuaikan nama fungsi, beberapa method menyesuaikan urutan parameter, dan beberapa method menyesuaikan tipe parameter dan return value. Detail lihat daftar method string, daftar method array, daftar method Stream.

### Array Method

```php
$array = [1, 2, 3, 99];

$array->contains(99);
$index = $array->indexOf(3);
$string = $array->join(';');
```

### String Object
```php
$str = "hello world!";

$true = $str->startsWith('hello');
$true = $str->endsWith('!');
$array = $str->split(' ');
```

## Strongly Typed Array
Bisa memaksa tipe `key` dan `value` array, mengatasi masalah type lubang hitam yang disebabkan oleh penggunaan array. Sintaks mirip dengan penulisan generic `C++`, mendukung multi-level nesting.

### List Syntax

```php
$array = typed_array('<int>', [1, 2, 3]);
$array = typed_array('<string>', ['hello', 'world'];
$array = typed_array('<TestObject>', [new TestObject];
$array = typed_array('<<stdClass>>');
```

### Map Syntax
```php
$array = typed_array('<int, string>', [1 => 'a', 2 => 'b', 3 => 'c'];
$array = typed_array('<string, TestObject>', [ 'first' => new TestObject];
$array = typed_array('<string, <stdClass>>');
```
