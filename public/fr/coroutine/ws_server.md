# Serveur WebSocket

?> Implementations de serveur WebSocket entièrement coroutine, héritant de [Coroutine\Http\Server](/coroutine/http_server), offrant un soutien au protocole `WebSocket` au niveau du matériel, que nous ne discuterons pas ici, mais disons seulement les différences.

!> Cette section est disponible après la version 4.4.13.


## Exemple complet

```php
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\WebSocket\CloseFrame;
use Swoole\Coroutine\Http\Server;
use function Swoole\Coroutine\run;

run(function () {
    $server = new Server('127.0.0.1', 9502, false);
    $server->handle('/websocket', function (Request $request, Response $ws) {
        $ws->upgrade();
        while (true) {
            $frame = $ws->recv();
            if ($frame === '') {
                $ws->close();
                break;
            } else if ($frame === false) {
                echo 'errorCode: ' . swoole_last_error() . "\n";
                $ws->close();
                break;
            } else {
                if ($frame->data == 'close' || get_class($frame) === CloseFrame::class) {
                    $ws->close();
                    break;
                }
                $ws->push("Hello {$frame->data}!");
                $ws->push("How are you, {$frame->data}?");
            }
        }
    });

    $server->handle('/', function (Request $request, Response $response) {
        $response->end(<<<HTML
    <h1>Swoole WebSocket Server</h1>
    <script>
var wsServer = 'ws://127.0.0.1:9502/websocket';
var websocket = new WebSocket(wsServer);
websocket.onopen = function (evt) {
    console.log("Connected to WebSocket server.");
    websocket.send('hello');
};

websocket.onclose = function (evt) {
    console.log("Disconnected");
};

websocket.onmessage = function (evt) {
    console.log('Retrieved data from server: ' + evt.data);
};

websocket.onerror = function (evt, e) {
    console.log('Error occured: ' + evt.data);
};
</script>
HTML
        );
    });

    $server->start();
});
```


### Exemple de diffusion

```php
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\WebSocket\CloseFrame;
use Swoole\Coroutine\Http\Server;
use function Swoole\Coroutine\run;

run(function () {
    $server = new Server('127.0.0.1', 9502, false);
    $server->handle('/websocket', function (Request $request, Response $ws) {
        $ws->upgrade();
        global $wsObjects;
        $objectId = spl_object_id($ws);
        $wsObjects[$objectId] = $ws;
        while (true) {
            $frame = $ws->recv();
            if ($frame === '') {
                unset($wsObjects[$objectId]);
                $ws->close();
                break;
            } else if ($frame === false) {
                echo 'errorCode: ' . swoole_last_error() . "\n";
                $ws->close();
                break;
            } else {
                if ($frame->data == 'close' || get_class($frame) === CloseFrame::class) {
                    unset($wsObjects[$objectId]);
                    $ws->close();
                    break;
                }
                foreach ($wsObjects as $obj) {
                    $obj->push("Server：{$frame->data}");
                }
            }
        }
    });
    $server->start();
});
```


## Processus de traitement

* `$ws->upgrade()` : Envoyer un message d'握手 réussi `WebSocket` au client
* Boucle `while(true)` pour traiter la réception et l'envoi des messages
* `$ws->recv()` : Réception d'un cadre de message `WebSocket`
* `$ws->push()` : Envoyer un cadre de données au côté opposé
* `$ws->close()` : Fermer la connexion

!> `$ws` est un objet `Swoole\Http\Response`, veuillez consulter la documentation ci-dessous pour chaque méthode spécifique.


## Méthodes


### upgrade()

Envoi d'un message indiquant que l'握手 `WebSocket` a réussi.

!> Cette méthode ne doit pas être utilisée dans les serveurs de style [asynchrone](/http_server)

```php
Swoole\Http\Response->upgrade(): bool
```


### recv()

Réception d'un message `WebSocket`.

!> Cette méthode ne doit pas être utilisée dans les serveurs de style [asynchrone](/http_server), l'appel à la méthode `recv` va [suspendre](/coroutine?id=协程调度) le coroutine actuel, attendant l'arrivée des données pour reprendre l'exécution du coroutine.

```php
Swoole\Http\Response->recv(float $timeout = 0): Swoole\WebSocket\Frame | false | string
```

* **Valeurs de retour**

  * Message reçu avec succès, retourne un objet `Swoole\WebSocket\Frame`, veuillez consulter [Swoole\WebSocket\Frame](/websocket_server?id=swoolewebsocketframe)
  * Échec de la réception, retourne `false`, veuillez utiliser [swoole_last_error()](/functions?id=swoole_last_error) pour obtenir l'code d'erreur
  * Connexion fermée, retourne une chaîne vide
  * Pour les traitements des valeurs de retour, veuillez consulter l'exemple de diffusion [群发示例](/coroutine/ws_server?id=群发示例)


### push()

Envoi d'un cadre de données `WebSocket`.

!> Cette méthode ne doit pas être utilisée dans les serveurs de style [asynchrone](/http_server), lors de l'envoi de gros paquets, il est nécessaire de surveiller l'écriture, ce qui peut entraîner plusieurs [switchs de coroutine](/coroutine?id=协程调度)

```php
Swoole\Http\Response->push(string|object $data, int $opcode = WEBSOCKET_OPCODE_TEXT, bool $finish = true): bool
```

* **Paramètres**

  !> Si `$data` est un objet `Swoole\WebSocket\Frame`, les paramètres suivants seront ignorés, ce qui prend en charge l'envoi de divers types de cadres

  * **`string|object $data`**

    * **Fonctionnalité** : Contenu à envoyer
    * **Valeur par défaut** : Aucun
    * **Autres valeurs** : Aucun

  * **`int $opcode`**

    * **Fonctionnalité** : Specifier le format du contenu envoyé 【Par défaut pour le texte. Pour envoyer du contenu binaire, le paramètre `$opcode` doit être mis à `WEBSOCKET_OPCODE_BINARY`】
    * **Valeur par défaut** : `WEBSOCKET_OPCODE_TEXT`
    * **Autres valeurs** : `WEBSOCKET_OPCODE_BINARY`

  * **`bool $finish`**

    * **Fonctionnalité** : Indicate si l'envoi est terminé
    * **Valeur par défaut** : `true`
    * **Autres valeurs** : `false`

### close()

Fermer la connexion `WebSocket`.

!> Cette méthode ne doit pas être utilisée dans les serveurs de style [asynchrone](/http_server), dans les versions antérieures à 4.4.15, un avertissement `Warning` pourrait être émis par erreur, il suffit d'ignorer.

```php
Swoole\Http\Response->close(): bool
```

Cette méthode fermera directement la connexion `TCP` sans envoyer de cadre de fermeture, contrairement à la méthode `disconnect()` de `WebSocket\Server`.
Vous pouvez utiliser la méthode `push()` pour envoyer un cadre de fermeture avant de fermer la connexion, notifiant ainsi activement le client.

```php
$frame = new Swoole\WebSocket\CloseFrame;
$frame->reason = 'close';
$ws->push($frame);
$ws->close();
```
