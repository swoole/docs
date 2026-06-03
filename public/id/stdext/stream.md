# Stream Method

## Mapping Table
Misalnya `$fp = fopen($file, 'r+')`, gunakan `$fp->read(1024)` untuk membaca data dari stream, setara dengan memanggil `fread($fp, 1024)`.

| Method        | Deskripsi                                          | Fungsi PHP yang Sesuai |
|---------------|----------------------------------------------------|------------------------| 
| write()       | Menulis data ke stream                             | fwrite()               |
| read()        | Membaca data dari stream                           | fread()                |
| close()       | Menutup stream                                     | fclose()               |
| dataSync()    | Menyinkronkan data stream ke storage device         | fdatasync()            |
| sync()        | Menyinkronkan data dan metadata stream ke storage   | fsync()                |
| truncate()    | Memotong stream ke panjang tertentu                 | ftruncate()            |
| stat()        | Mendapatkan informasi status stream                 | fstat()                |
| seek()        | Memindahkan posisi stream ke lokasi tertentu        | fseek()                |
| tell()        | Mendapatkan posisi saat ini di stream               | ftell()                |
| lock()        | Mengunci atau membuka kunci stream                  | flock()                |
| eof()         | Mengecek apakah sudah mencapai akhir stream          | feof()                 |
| getChar()     | Mendapatkan satu karakter dari stream               | fgetc()                |
| getLine()     | Mendapatkan satu baris data dari stream             | fgets()                |

## Contoh
```php
$fp = fopen('example.txt', 'r+');
$fp->write("Hello, Swoole!\n");
$fp->seek(0);
while (!$fp->eof()) {
    $line = $fp->getLine();
    echo $line;
}
```
