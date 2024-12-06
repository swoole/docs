# Résumé des alias de fonctions


## Noms courts pour les coroutines

Simplifie l'écriture des noms des `API` liées aux coroutines. Vous pouvez modifier la configuration `php.ini` en `swoole.use_shortname=On/Off` pour activer/désactiver les noms courts, par défaut activés.

Tous les noms de classe commençant par `Swoole\Coroutine` sont mappés à `Co`. Il y a aussi les mappages suivants :


### Création de coroutines

```php
// équivalent de la fonction go de Swoole\Coroutine
go(function () {
	Co::sleep(0.5);
	echo 'hello';
});
go('test');
go([$object, 'method']);
```


### Opérations sur les canaux

```php
// Les canaux de Coroutine peuvent être abrégés en chan
$c = new chan(1);
$c->push($data);
$c->pop();
```


### Exécution différée

```php
// La fonction defer de Swoole\Coroutine peut être utilisée directement avec defer
defer(function () use ($db) {
    $db->close();
});
```


## Méthodes avec noms courts

!> Dans ce style, `go` et `defer` sont disponibles à partir de la version Swoole `v4.6.3`

```php
use function Swoole\Coroutine\go;
use function Swoole\Coroutine\run;
use function Swoole\Coroutine\defer;

run(function () {
    defer(function () {
        echo "co1 end\n";
    });
    sleep(1);
    go(function () {
        usleep(100000);
        defer(function () {
            echo "co2 end\n";
        });
        echo "co2\n";
    });
    echo "co1\n";
});
```


## API System de coroutines

Dans la version `4.4.4`, les API de coroutines liées aux opérations système ont été déplacées de la classe `Swoole\Coroutine` vers la classe `Swoole\Coroutine\System`. Elles sont maintenant un nouveau module indépendant. Afin de maintenir la compatibilité vers le bas, des méthodes d'alias sont toujours conservées au-dessus de la classe `Coroutine`.

* Par exemple, `Swoole\Coroutine::sleep` correspond à `Swoole\Coroutine\System::sleep`
* Par exemple, `Swoole\Coroutine::fgets` correspond à `Swoole\Coroutine\System::fgets`

## Mappage des abréviations de classe

!> Il est recommandé d'utiliser le style de namespace.

| Style de classe avec underscores | Style de namespace                  |
| --------------------------- | --------------------------- |
| swoole_server               | Swoole\Server               |
| swoole_client               | Swoole\Client               |
| swoole_process              | Swoole\Process              |
| swoole_timer                | Swoole\Timer                |
| swoole_table                | Swoole\Table                |
| swoole_lock                 | Swoole\Lock                 |
| swoole_atomic               | Swoole\Atomic               |
| swoole_atomic_long          | Swoole\Atomic\Long          |
| swoole_buffer               | Swoole\Buffer               |
| swoole_redis                | Swoole\Redis                |
| swoole_error                | Swoole\Error                |
| swoole_event                | Swoole\Event                |
| swoole_http_server          | Swoole\Http\Server          |
| swoole_http_client          | Swoole\Http\Client          |
| swoole_http_request         | Swoole\Http\Request         |
| swoole_http_response        | Swoole\Http\Response        |
| swoole_websocket_server     | Swoole\WebSocket\Server     |
| swoole_connection_iterator  | Swoole\Connection\Iterator  |
| swoole_exception            | Swoole\Exception            |
| swoole_http2_request        | Swoole\Http2\Request        |
| swoole_http2_response       | Swoole\Http2\Response       |
| swoole_process_pool         | Swoole\Process\Pool         |
| swoole_redis_server         | Swoole\Redis\Server         |
| swoole_runtime              | Swoole\Runtime              |
| swoole_server_port          | Swoole\Server\Port          |
| swoole_server_task          | Swoole\Server\Task          |
| swoole_table_row            | Swoole\Table\Row            |
| swoole_timer_iterator       | Swoole\Timer\Iterator       |
| swoole_websocket_closeframe | Swoole\Websocket\Closeframe |
| swoole_websocket_frame      | Swoole\Websocket\Frame      |
