# Masalah Instalasi

## Upgrade Versi Swoole

Kamu bisa gunakan pecl untuk instalasi dan upgrade

```shell
pecl upgrade swoole
```

Bisa juga download versi baru langsung dari github/gitee/pecl, lalu instal ulang dengan kompilasi.

* Update versi Swoole tidak perlu menghapus versi lama, proses instalasi akan menimpa versi lama
* Setelah kompilasi Swoole, tidak ada file tambahan, hanya `swoole.so`. Kalau ada binary dari mesin lain, tinggal timpa `swoole.so` untuk ganti versi
* Kalau clone dari git, setelah `git pull`, jangan lupa jalankan `phpize`, `./configure`, `make clean`, `make install`
* Bisa juga pakai docker yang sesuai untuk upgrade versi Swoole

## Ada di phpinfo tapi tidak ada di php -m

Cek dulu apakah ada di mode CLI, jalankan `php --ri swoole`

Kalau keluar info ekstensi Swoole, artinya instalasi berhasil!

**99.999% orang di step ini sudah bisa langsung pakai Swoole**

Tidak perlu peduliin `php -m` atau `phpinfo` di web ada atau tidak Swoole

Karena Swoole jalan di mode cli, di mode fpm tradisional fungsinya sangat terbatas

Di mode fpm, fitur utama seperti async/coroutine **tidak bisa dipakai**. 99.999% orang tidak akan mendapatkan yang diinginkan di mode fpm, tapi malah sibuk mikirin kenapa info ekstensi tidak muncul

**Pastikan kamu benar-benar paham mode jalan Swoole, baru lanjut urus masalah instalasi!**

### Penyebab

Setelah kompilasi Swoole, muncul di halaman `phpinfo` `php-fpm/apache`, tapi tidak muncul di `php -m` command line. Penyebabnya mungkin `cli/php-fpm/apache` pakai konfigurasi php.ini yang berbeda

### Solusi

1. Cek lokasi php.ini

Jalankan `php -i | grep php.ini` atau `php --ini` di `cli` untuk cari path absolut php.ini

`php-fpm/apache` cek di halaman `phpinfo` untuk cari path absolut php.ini

2. Cek apakah di php.ini ada `extension=swoole.so`

```shell
cat /path/to/php.ini | grep swoole.so
```

## pcre.h: No such file or directory

Kompilasi ekstensi Swoole muncul error

```bash
fatal error: pcre.h: No such file or directory
```

Penyebabnya kurang pcre, perlu install libpcre

### ubuntu/debian

```shell
sudo apt-get install libpcre3 libpcre3-dev
```
### centos/redhat

```shell
sudo yum install pcre-devel
```

### Linux Lainnya

Download source code dari [situs resmi PCRE](http://www.pcre.org/), kompilasi dan install library `pcre`.

Setelah install library `PCRE`, kompilasi ulang `swoole`, lalu pakai `php --ri swoole` untuk cek apakah ada `pcre => enabled`

## '__builtin_saddl_overflow' was not declared in this scope

 ```
error: '__builtin_saddl_overflow' was not declared in this scope
  if (UNEXPECTED(__builtin_saddl_overflow(Z_LVAL_P(op1), 1, &lresult))) {

note: in definition of macro 'UNEXPECTED'
 # define UNEXPECTED(condition) __builtin_expect(!!(condition), 0)
```

Ini masalah yang sudah diketahui. Masalahnya adalah gcc default di CentOS tidak punya definisi yang diperlukan, bahkan setelah upgrade gcc, PECL tetap pakai compiler lama.

Untuk install driver, harus upgrade gcc dulu dengan menginstall devtoolset:

```shell
sudo yum install centos-release-scl
sudo yum install devtoolset-7
scl enable devtoolset-7 bash
```

## fatal error: 'openssl/ssl.h' file not found

Waktu kompilasi, tambahkan parameter [--with-openssl-dir](/environment?id=parameter-umum) untuk menentukan path library openssl

!> Kalau pakai [pecl](/environment?id=pecl) untuk install Swoole dan ingin mengaktifkan openssl, bisa tambah [--with-openssl-dir](/environment?id=parameter-umum), contoh: `enable openssl support? [no] : yes --with-openssl-dir=/opt/openssl/`

## make atau make install gagal atau error kompilasi

NOTICE: PHP message: PHP Warning:  PHP Startup: swoole: Unable to initialize module  
Module compiled with module API=20090626  
PHP    compiled with module API=20121212  
These options need to match  
in Unknown on line 0  

Versi PHP tidak sesuai dengan `phpize` dan `php-config` yang dipakai waktu kompilasi. Harus pakai path absolut untuk kompilasi dan menjalankan PHP.

```shell
/usr/local/php-5.4.17/bin/phpize
./configure --with-php-config=/usr/local/php-5.4.17/bin/php-config

/usr/local/php-5.4.17/bin/php server.php
```

## Install xdebug

```shell
git clone git@github.com:swoole/sdebug.git -b sdebug_2_9 --depth=1

cd sdebug

phpize
./configure
make clean
make
make install

#Kalau phpize, php-config dll sudah default, langsung jalankan
./rebuild.sh
```

Ubah php.ini untuk load ekstensi, tambahkan ini:

```ini
zend_extension=xdebug.so

xdebug.remote_enable=1
xdebug.remote_autostart=1
xdebug.remote_host=localhost
xdebug.remote_port=8000
xdebug.idekey="xdebug"
```

Cek apakah berhasil di-load

```shell
php --ri sdebug
```

## configure: error: C preprocessor "/lib/cpp" fails sanity check

Kalau pas instalasi error

```shell
configure: error: C preprocessor "/lib/cpp" fails sanity check
```

Artinya kurang library dependensi yang diperlukan, install dengan:

```shell
yum install glibc-headers
yum install gcc-c++
```

## Error kompilasi Swoole versi baru dengan PHP7.4.11+ :id=asm_goto

Di MacOS, kompilasi Swoole versi baru dengan PHP7.4.11+ muncul error seperti:

```shell
/usr/local/Cellar/php/7.4.12/include/php/Zend/zend_operators.h:523:10: error: 'asm goto' constructs are not supported yet
        __asm__ goto(
                ^
/usr/local/Cellar/php/7.4.12/include/php/Zend/zend_operators.h:586:10: error: 'asm goto' constructs are not supported yet
        __asm__ goto(
                ^
/usr/local/Cellar/php/7.4.12/include/php/Zend/zend_operators.h:656:10: error: 'asm goto' constructs are not supported yet
        __asm__ goto(
                ^
/usr/local/Cellar/php/7.4.12/include/php/Zend/zend_operators.h:766:10: error: 'asm goto' constructs are not supported yet
        __asm__ goto(
                ^
4 errors generated.
make: *** [ext-src/php_swoole.lo] Error 1
ERROR: `make' failed
```

Solusi: edit source code di `/usr/local/Cellar/php/7.4.12/include/php/Zend/zend_operators.h`, sesuaikan path header file masing-masing;

Ubah `ZEND_USE_ASM_ARITHMETIC` jadi tetap `0`, yaitu pertahankan kode di `else`

```c
#if defined(HAVE_ASM_GOTO) && !__has_feature(memory_sanitizer)
# define ZEND_USE_ASM_ARITHMETIC 1
#else
# define ZEND_USE_ASM_ARITHMETIC 0
#endif
```

## fatal error: curl/curl.h: No such file or directory :id=libcurl

Setelah mengaktifkan opsi `--enable-swoole-curl`, kompilasi ekstensi Swoole muncul error

```bash
fatal error: curl/curl.h: No such file or directory
```

Penyebabnya kurang dependensi curl, perlu install libcurl

### ubuntu/debian

```shell
sudo apt-get install libcurl4-openssl-dev
```
### centos/redhat

```shell
sudo yum install libcurl-devel
```

### alpine

```shell
apk add curl-dev
```

## fatal error: ares.h: No such file or directory :id=libcares

Setelah mengaktifkan opsi `--enable-cares`, kompilasi ekstensi Swoole muncul error

```bash
fatal error: ares.h: No such file or directory
```

Penyebabnya kurang dependensi c-ares, perlu install libcares

### ubuntu/debian

```shell
sudo apt-get install libc-ares-dev
```

### centos/redhat

```shell
sudo yum install c-ares-devel
```

### alpine

```shell
apk add c-ares-dev
```

### MacOs

```shell
brew install c-ares
```
