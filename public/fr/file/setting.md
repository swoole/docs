# Configuration

`Swoole` a défini plusieurs paramètres clés qui influencent les caractéristiques des opérations asynchrones de fichiers, qui peuvent être définis via `swoole_async_set` ou `Swoole\Server->set()`.

Exemple :

```php
<?php
swoole_async_set([
    'aio_core_worker_num' => 10,
    'aio_worker_num' => 20,
    'aio_max_wait_time' => 60,
    'aio_max_idle_time' => 60,
    'iouring_entries' => 1024
]);

$server = new Swoole\Server('127.0.0.1', 9501);
$server->set([
    'aio_core_worker_num' => 10,
    'aio_worker_num' => 20,
    'aio_max_wait_time' => 60,
    'aio_max_idle_time' => 60,
    'iouring_entries' => 1024
]);
```

### aio_core_worker_num

?> Définit le nombre minimum de threads dans le pool de threads, par défaut est le nombre de cœurs CPU.

### aio_worker_num

?> Définit le nombre maximum de threads dans le pool de threads, par défaut est le nombre de cœurs CPU * 8.

### aio_max_wait_time

?> Définit la durée maximale d'attente pour les threads dans le pool de threads, par défaut est `0`.

### aio_max_idle_time

?> Définit la durée maximale d'inactivité pour les threads dans le pool de threads, par défaut est `1s`.

### iouring_entries

?> Définit la taille de la file d'attente `io_uring`, par défaut est `8192`. Si la valeur passée n'est pas un nombre de puissance de deux, le noyau la modifie à la plus proche puissance de deux supérieure à cette valeur.

!> Si la valeur passée est trop grande, le noyau peut lever une exception et arrêter le programme.

!> La fonctionnalité `io_uring` est disponible uniquement si le système est équipé de `liburing` et que `Swoole` a été compilé avec `--enable-iouring`.

!> La fonctionnalité `io_uring` est disponible uniquement si le système est équipé de `liburing` et que la version de `Swoole` v6.0 ou supérieure a été compilée avec `--enable-iouring`.
