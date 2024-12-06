# Conflit d'extension

En raison de l'utilisation massive de variables globales par certaines extensions de débogage PHP, il est possible que les coroutines Swoole s'effondrent. Veuillez désactiver les extensions suivantes :

* phptrace
* aop
* molten
* xhprof
* phalcon (les coroutines Swoole ne peuvent pas fonctionner dans le cadre de l'framework Phalcon)

## Support Xdebug
À partir de la version 5.1, il est possible d'utiliser directement l'extension Xdebug pour déboguer des programmes Swoole, en utilisant des paramètres de ligne de commande ou en modifiant le php.ini.

```ini
swoole.enable_fiber_mock=On
```

ou

```shell
php -d swoole.enable_fiber_mock=On your_file.php
```
