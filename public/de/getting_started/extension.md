# Erweiterungskonflikte

Da einige tracking- und Debugging-Erweiterungen für `PHP` stark auf globale Variablen setzen, können sie zu einem Absturz der `Swoole`-Koordination führen. Bitte deaktivieren Sie die folgenden relevanten Erweiterungen:

* phptrace
* aop
* molten
* xhprof
* phalcon (Swoole-Koordination kann nicht in der `phalcon`-Framework-Umgebung laufen)

## Xdebug Unterstützung
Ab Version `5.1` kann die `xdebug`-Erweiterung direkt zur Debuggen von `Swoole`-Programmen verwendet werden, durch Befehlszeilenparameter oder durch das Bearbeiten der `php.ini` aktiviert.

```ini
swoole.enable_fiber_mock=On
```

Oder

```shell
php -d swoole.enable_fiber_mock=On your_file.php
```
