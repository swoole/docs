# 扩展冲突

由于某些跟踪调试的`PHP`扩展大量使用了全局变量，可能会导致`Swoole`协程发生崩溃。请关闭以下相关扩展：

* phptrace
* aop
* molten
* xhprof
* phalcon（`Swoole`协程无法运行在 `phalcon` 框架中）


## Xdebug 支持
从 `5.1` 版本开始可直接使用 `xdebug` 扩展来调试 `Swoole` 程序，通过命令行参数或者修改 `php.ini` 启用。

```ini
swoole.enable_fiber_mock=On
```

或者 

```shell
php -d swoole.enable_fiber_mock=On your_file.php
```
