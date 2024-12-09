# Extension Conflicts

Due to the extensive use of global variables by certain debugging `PHP` extensions, it may cause crashes in `Swoole` coroutines. Please disable the following related extensions:

* phptrace
* aop
* molten
* xhprof
* phalcon (Swoole coroutines cannot run in the `phalcon` framework)

## Xdebug Support
Starting from version `5.1`, you can directly use the `xdebug` extension to debug `Swoole` programs, enabled either through command line arguments or by modifying the `php.ini` file.

```ini
swoole.enable_fiber_mock=On
```

Or

```shell
php -d swoole.enable_fiber_mock=On your_file.php
```
