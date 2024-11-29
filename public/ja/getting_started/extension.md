# 拡張衝突

一部のトレースデバッグ用の `PHP` 拡張が大量にグローバル変数を使用しているため、`Swoole` コルテックスがクラッシュすることがあります。関連する拡張を以下のようにオフにしてください：

* phptrace
* aop
* molten
* xhprof
* phalcon（`Swoole` コルテックスは `phalcon` フレームワークで実行できません）

## Xdebug 支援
`5.1` 版から `xdebug` 拡張を直接使用して `Swoole` プログラムをデバッグできます。コマンドラインパラメータまたは `php.ini` の編集によって有効にします。

```ini
swoole.enable_fiber_mock=On
```

または

```shell
php -d swoole.enable_fiber_mock=On your_file.php
```
