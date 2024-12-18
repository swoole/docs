# 扩展冲突

由於某些追蹤調試的 PHP 擴展大量使用了全域變數，可能會導致 Swoole 協程發生崩潰。請關閉以下相關擴展：

* phptrace
* aop
* molten
* xhprof
* phalcon（Swoole 協程無法在 phalcon 框架中運行）

## Xdebug 支援
從 5.1 版本開始可直接使用 xdebug 擴展來調試 Swoole 程式，透過命令列參數或者修改 php.ini 啟用。

```ini
swoole.enable_fiber_mock=On
```

或者 

```shell
php -d swoole.enable_fiber_mock=On your_file.php
```
