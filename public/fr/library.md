# Bibliothèque

Après la version v4, Swoole a intégré le module [Bibliothèque](https://github.com/swoole/library), qui permet de **écrire des fonctionnalités du noyau en code PHP**, rendant les infrastructures sous-jacentes plus stables et fiables.

!> Ce module peut également être installé séparément via Composer. Lors de son utilisation séparée, il est nécessaire de configurer `swoole.enable_library=Off` dans `php.ini` pour désactiver la bibliothèque intégrée de l'extension.

Actuellement, les composants d'outils suivants sont fournis :

- [Coroutine\WaitGroup](https://github.com/swoole/library/blob/master/src/core/Coroutine/WaitGroup.php) pour attendre les tâches de coroutines en parallèle, [documentation](/coroutine/wait_group)

- [Coroutine\FastCGI](https://github.com/swoole/library/tree/master/src/core/Coroutine/FastCGI) pour les clients FastCGI, [documentation](/coroutine_client/fastcgi)

- [Coroutine\Server](https://github.com/swoole/library/blob/master/src/core/Coroutine/Server.php) pour les serveurs coroutines, [documentation](/coroutine/server)

- [Coroutine\Barrier](https://github.com/swoole/library/blob/master/src/core/Coroutine/Barrier.php) pour les barrières de coroutines, [documentation](/coroutine/barrier)

- [CURL hook](https://github.com/swoole/library/tree/master/src/core/Curl) pour la coroutinisation de CURL, [documentation](/runtime?id=swoole_hook_curl)

- [Database](https://github.com/swoole/library/tree/master/src/core/Database) pour les 高级 emballages de pool de connexions et d'objets proxies de divers bases de données, [documentation](/coroutine/conn_pool?id=database)

- [ConnectionPool](https://github.com/swoole/library/blob/master/src/core/ConnectionPool.php) pour les pools de connexions bruts, [documentation](/coroutine/conn_pool?id=connectionpool)

- [Process\Manager](https://github.com/swoole/library/blob/master/src/core/Process/Manager.php) pour les gestionnaires de processus, [documentation](/process/process_manager)

- [StringObject](https://github.com/swoole/library/blob/master/src/core/StringObject.php), [ArrayObject](https://github.com/swoole/library/blob/master/src/core/ArrayObject.php), [MultibyteStringObject](https://github.com/swoole/library/blob/master/src/core/MultibyteStringObject.php) pour la programmation orientée objet de Array et String

- [functions](https://github.com/swoole/library/blob/master/src/core/Coroutine/functions.php) pour certaines fonctions de coroutines, [documentation](/coroutine/coroutine?id=fonctions)

- [Constant](https://github.com/swoole/library/tree/master/src/core/Constant.php) pour les constantes de configuration couramment utilisées

- [HTTP Status](https://github.com/swoole/library/blob/master/src/core/Http/Status.php) pour les codes d'état HTTP
