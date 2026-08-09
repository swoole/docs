# Konflik Ekstensi

Beberapa ekstensi `PHP` untuk tracing dan debugging banyak pakai global variable, yang bisa bikin coroutine `Swoole` crash. Matikan ekstensi berikut:

* phptrace
* aop
* molten
* xhprof
* phalcon (coroutine `Swoole` nggak bisa jalan di framework `phalcon`)


## Dukungan Xdebug
Mulai versi `5.1`, kamu bisa langsung pakai ekstensi `xdebug` buat debugging program `Swoole`, cukup lewat parameter command line atau edit `php.ini`.

```ini
swoole.enable_fiber_mock=On
```

Atau

```shell
php -d swoole.enable_fiber_mock=On your_file.php
```
