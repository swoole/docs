# Swoole\WebSocket\Server

?> Grâce au support intégré du serveur `WebSocket`, il est possible d'écrire un serveur `WebSocket` à plusieurs processus asynchrone en quelques lignes de code PHP.

```php
$server = new Swoole\WebSocket\Server("0.0.0.0", 9501);

$server->on('open', function (Swoole\WebSocket\Server $server, $request) {
    echo "server: handshake success with fd{$request->fd}\n";
});

$server->on('message', function (Swoole\WebSocket\Server $server, $frame) {
    echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
    $server->push($frame->fd, "this is server");
});

$server->on('close', function ($server, $fd) {
    echo "client {$fd} closed\n";
});

$server->start();
```

* **Client**

  * Les navigateurs tels que Chrome, Firefox, versions élevées d'IE/Safari ont en interne un client `WebSocket` en langage JavaScript.
  * Le cadre de développement des mini-applications WeChat a en interne un client `WebSocket`.
  * Dans les programmes PHP asynchrone [Swoole\Coroutine\Http](/coroutine_client/http_client) peut être utilisé comme client `WebSocket`.
  * Dans les programmes PHP synchrones bloqués comme Apache/PHP-FPM, vous pouvez utiliser le client `WebSocket` synchronisé fourni par le [swoole/framework](https://github.com/matyhtf/framework/blob/master/libs/Swoole/Client/WebSocket.php).
  * Les clients qui ne sont pas `WebSocket` ne peuvent pas communiquer avec le serveur `WebSocket`.

* **Comment déterminer si une connexion est un client `WebSocket`**

?> En utilisant l'exemple ci-dessous pour obtenir des informations sur la connexion [/server/methods?id=getclientinfo], l'une des valeurs de l'array retourné est la [websocket_status](/websocket_server?id=连接状态). Vous pouvez déterminer si c'est une connexion `WebSocket` en fonction de cet état.
```php
$server = new Swoole\WebSocket\Server("0.0.0.0", 9501);
$server->on('message', function (Swoole\WebSocket\Server $server, $frame) {
    $client = $server->getClientInfo($frame->fd);
    // 或者 $client = $server->connection_info($frame->fd);
    if (isset($client['websocket_status'])) {
        echo "是websocket 连接";
    } else {
        echo "不是websocket 连接";
    }
});
```

## Événements

?> En plus des回调 functions de la classe de base [Swoole\Server](/server/methods) et [Swoole\Http\Server](/http_server), le serveur `WebSocket` ajoute quatre événements supplémentaires pour l'configuration. Parmi eux :

* Le callback function `onMessage` est requis.
* Les callback functions `onOpen`, `onHandShake` et `onBeforeHandShakeResponse` (proposés par Swoole 5) sont optionnels.


### onBeforeHandShakeResponse

!> Disponible pour les versions >= `v5.0.0` de Swoole.

?> **Se produit avant l'établissement d'une connexion `WebSocket`. Si vous n'avez pas besoin de personnaliser le processus de hachage, mais que vous souhaitez également ajouter certaines informations de `header http` à la réponse, alors vous pouvez appeler cet événement.**

```php
onBeforeHandShakeResponse(Swoole\Http\Request $request, Swoole\Http\Response $response);
```


### onHandShake

?> **Se produit après l'établissement d'une connexion `WebSocket` et avant le hachage. Le serveur `WebSocket` effectue automatiquement le processus de hachage. Si vous souhaitez que l'utilisateur gère lui-même le hachage, vous pouvez configurer le callback function `onHandShake`.**

```php
onHandShake(Swoole\Http\Request $request, Swoole\Http\Response $response);
```

* **Avertissement**

  * Le callback function `onHandShake` est optionnel.
  * Après avoir configuré le callback function `onHandShake`, l'événement `onOpen` ne sera plus déclenché. Le code d'application doit gérer cela lui-même, en utilisant `$server->defer` pour appeler la logique `onOpen`.
  * Dans `onHandShake`, vous devez appeler [response->status()](/http_server?id=status) pour设置了tatus code à `101` et [response->end()](/http_server?id=end) pour répondre, sinon le hachage échouera.
  * Le protocole de hachage intégré est `Sec-WebSocket-Version: 13`. Les navigateurs de version inférieure doivent mettre en œuvre le hachage par eux-mêmes.

* **Note**

!> Si vous devez gérer le hachage par vous-même, alors configurez ce callback function. Si vous n'avez pas besoin de "personnaliser" le processus de hachage, alors ne configurez pas ce callback et utilisez le hachage par défaut de Swoole. Voici les éléments essentiels que doit avoir le callback function de hachage personnalisé :

```php
$server->on('handshake', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
    // print_r( $request->header );
    // if (如果不满足我某些自定义的需求条件，那么返回end输出，返回false，握手失败) {
    //    $response->end();
    //     return false;
    // }

    // websocket握手连接算法验证
    $secWebSocketKey = $request->header['sec-websocket-key'];
    $patten = '#^[+/0-9A-Za-z]{21}[AQgw]==$#';
    if (0 === preg_match($patten, $secWebSocketKey) || 16 !== strlen(base64_decode($secWebSocketKey))) {
        $response->end();
        return false;
    }
    echo $request->header['sec-websocket-key'];
    $key = base64_encode(
        sha1(
            $request->header['sec-websocket-key'] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11',
            true
        )
    );

    $headers = [
        'Upgrade' => 'websocket',
        'Connection' => 'Upgrade',
        'Sec-WebSocket-Accept' => $key,
        'Sec-WebSocket-Version' => '13',
    ];

    // WebSocket connection to 'ws://127.0.0.1:9502/'
    // failed: Error during WebSocket handshake:
    // Response must not include 'Sec-WebSocket-Protocol' header if not present in request: websocket
    if (isset($request->header['sec-websocket-protocol'])) {
        $headers['Sec-WebSocket-Protocol'] = $request->header['sec-websocket-protocol'];
    }

    foreach ($headers as $key => $val) {
        $response->header($key, $val);
    }

    $response->status(101);
    $response->end();
});
```

!> Après avoir configuré le callback function `onHandShake`, l'événement `onOpen` ne sera plus déclenché. Le code d'application doit gérer cela lui-même, en utilisant `$server->defer` pour appeler la logique `onOpen`.

```php
$server->on('handshake', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
    // 省略了握手内容
    $response->status(101);
    $response->end();

    global $server;
    $fd = $request->fd;
    $server->defer(function () use ($fd, $server)
    {
      echo "Client connected\n";
      $server->push($fd, "hello, welcome\n");
    });
});
```


### onOpen

?> **Lorsque un client `WebSocket` établit une connexion avec le serveur et termine le hachage, ce callback function est appelé.**

```php
onOpen(Swoole\WebSocket\Server $server, Swoole\Http\Request $request);
```

* **Avertissement**

    * `$request` est un objet de demande [HTTP](/http_server?id=httprequest), qui contient les informations de la demande de hachage envoyée par le client.
    * Dans la fonction d'événement `onOpen`, vous pouvez appeler [push](/websocket_server?id=push) pour envoyer des données au client ou appeler [close](/server/methods?id=close) pour fermer la connexion.
    * Le callback function `onOpen` est optionnel.


### onMessage

?> **Lorsque le serveur reçoit un cadre de données envoyé par le client, ce callback function est appelé.**

```php
onMessage(Swoole\WebSocket\Server $server, Swoole\WebSocket\Frame $frame)
```

* **Avertissement**

  * `$frame` est un objet [Swoole\WebSocket\Frame](/websocket_server?id=swoolewebsocketframe), qui contient les informations du cadre de données envoyé par le client.
  * Le callback function `onMessage` doit être configuré, sinon le serveur ne peut pas démarrer.
  * Les cadres de données `ping` envoyés par le client ne déclenchent pas `onMessage`. Le sous-système en底层 répond automatiquement par un paquet `pong`, mais vous pouvez également configurer la parameter [open_websocket_ping_frame](/websocket_server?id=open_websocket_ping_frame) pour gérer manuellement.

!> Si `$frame->data` est de type texte, la format de codage est nécessairement `UTF-8`, ce qui est stipulé par le protocole `WebSocket`.
### onRequest

?> `Swoole\WebSocket\Server` hérite de [Swoole\Http\Server](/http_server), donc toutes les `API` et les paramètres de configuration offerts par `Http\Server` peuvent être utilisés. Veuillez consulter la section [Swoole\Http\Server](/http_server).

* La mise en place d'un rappel [onRequest](/http_server?id=on) permet au `WebSocket\Server` de fonctionner également en tant que serveur HTTP
* Si aucun rappel [onRequest](/http_server?id=on) n'est mis en place, le `WebSocket\Server` retournera une page d'erreur HTTP 400 lorsqu'il reçoit une demande HTTP
* Si l'on souhaite déclencher la diffusion de toutes les notifications WebSocket par la réception d'une demande HTTP, il est important de prendre en compte le problème de portée. Pour les procédures, utilisez `global` pour faire référence au `Swoole\WebSocket\Server`, et pour les objets, vous pouvez affecter un `Swoole\WebSocket\Server` à une propriété membre

#### Code de style procédural

```php
$server = new Swoole\WebSocket\Server("0.0.0.0", 9501);
$server->on('open', function (Swoole\WebSocket\Server $server, $request) {
    echo "server: handshake success with fd{$request->fd}\n";
});
$server->on('message', function (Swoole\WebSocket\Server $server, $frame) {
    echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
    $server->push($frame->fd, "this is server");
});
$server->on('close', function ($server, $fd) {
    echo "client {$fd} closed\n";
});
$server->on('request', function (Swoole\Http\Request $request, Swoole\Http\Response $response) {
    global $server;//appeler le serveur extérieur
    // $server->connections itère sur tous les fd des connexions websocket des utilisateurs, pour pousser à tous les utilisateurs
    foreach ($server->connections as $fd) {
        // Il faut d'abord vérifier si c'est une connexion websocket correcte, sinon il pourrait y avoir un échec de la poussée
        if ($server->isEstablished($fd)) {
            $server->push($fd, $request->get['message']);
        }
    }
});
$server->start();
```

#### Code de style object-oriented

```php
class WebSocketServer
{
    public $server;

    public function __construct()
    {
        $this->server = new Swoole\WebSocket\Server("0.0.0.0", 9501);
        $this->server->on('open', function (Swoole\WebSocket\Server $server, $request) {
            echo "server: handshake success with fd{$request->fd}\n";
        });
        $this->server->on('message', function (Swoole\WebSocket\Server $server, $frame) {
            echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
            $server->push($frame->fd, "this is server");
        });
        $this->server->on('close', function ($ser, $fd) {
            echo "client {$fd} closed\n";
        });
        $this->server->on('request', function ($request, $response) {
            // Récupérer la valeur du paramètre 'message' de la demande HTTP pour la diffusion aux utilisateurs
            // $this->server->connections itère sur tous les fd des connexions websocket des utilisateurs, pour pousser à tous les utilisateurs
            foreach ($this->server->connections as $fd) {
                // Il faut d'abord vérifier si c'est une connexion websocket correcte, sinon il pourrait y avoir un échec de la poussée
                if ($this->server->isEstablished($fd)) {
                    $this->server->push($fd, $request->get['message']);
                }
            }
        });
        $this->server->start();
    }
}

new WebSocketServer();
```


### onDisconnect

?> **Cette événement est déclenché uniquement lorsque la connexion non WebSocket est fermée.**

!> Disponible à partir de la version Swoole `v4.7.0`

```php
onDisconnect(Swoole\WebSocket\Server $server, int $fd)
```

!> Lorsque le rappel `onDisconnect` est mis en place, une demande HTTP non WebSocket ou la méthode `$response->close()` appelée dans [onRequest](/websocket_server?id=onrequest) déclenche le rappel `onDisconnect`. Cependant, si [onRequest](/websocket_server?id=onrequest) se termine normalement, aucun événement `onClose` ou `onDisconnect` n'est appelé.  


## Méthodes

`Swoole\WebSocket\Server` est une sous-classe de [Swoole\Server](/server/methods), donc toutes les méthodes de `Server` peuvent être appelées.

Il est important de noter que pour envoyer des données à un client WebSocket, il faut utiliser la méthode `Swoole\WebSocket\Server::push`, qui prendra en charge l'encapsulation du protocole WebSocket. La méthode `Swoole\Server->send()](/server/methods?id=send)` est l'interface deSend originale du TCP.

La méthode `Swoole\WebSocket\Server->disconnect()](/websocket_server?id=disconnect)` permet de fermer une connexion WebSocket de la part du serveur, et permet de spécifier un code d'état de fermeture ([code d'état de fermeture websocket](/websocket_server?id=websocket关闭帧状态码)) (selon le protocole WebSocket, les codes d'état valides sont des entiers décimaux, pouvant être `1000` ou `4000-4999`) et une raison de fermeture (utilisée en UTF-8, la longueur du texte ne doit pas dépasser `125` caractères). Si aucun code d'état n'est spécifié, il est fixé à `1000`, et la raison de fermeture est vide.


### push

?> **Envoyer des données à un client WebSocket, la longueur maximale ne doit pas dépasser `2M`.**

```php
Swoole\WebSocket\Server->push(int $fd, \Swoole\WebSocket\Frame|string $data, int $opcode = WEBSOCKET_OPCODE_TEXT, bool $finish = true): bool

// À partir de la version v4.4.12, le paramètre a été remplacé par flags
Swoole\WebSocket\Server->push(int $fd, \Swoole\WebSocket\Frame|string $data, int $opcode = WEBSOCKET_OPCODE_TEXT, int $flags = SWOOLE_WEBSOCKET_FLAG_FIN): bool
```

* **Paramètres** 

  * **`int $fd`**

    * **Fonction** : ID de la connexion client 【Si le `$fd` spécifié ne correspond pas à une connexion client WebSocket, l'opération échouera】
    * **Valeur par défaut** : None
    * **Autres valeurs** : None

  * **`Swoole\WebSocket\Frame|string $data`**

    * **Fonction** : Contenu des données à envoyer
    * **Valeur par défaut** : None
    * **Autres valeurs** : None

  !> À partir de la version Swoole v4.2.0, si `$data` est un objet [Swoole\WebSocket\Frame](/websocket_server?id=swoolewebsocketframe), les paramètres suivants seront ignorés

  * **`int $opcode`**

    * **Fonction** : Specifier la format du contenu des données envoyées 【Par défaut pour le texte. Pour envoyer du contenu binaire, le `$opcode` doit être fixé à `WEBSOCKET_OPCODE_BINARY`】
    * **Valeur par défaut** : `WEBSOCKET_OPCODE_TEXT`
    * **Autres valeurs** : `WEBSOCKET_OPCODE_BINARY`

  * **`bool $finish`**

    * **Fonction** : Indicate si la transmission est terminée
    * **Valeur par défaut** : `true`
    * **Autres valeurs** : `false`

* **Valeur de retour**

  * Opération réussie, retourne `true`, opération échouée, retourne `false`

!> À partir de la version v4.4.12, le paramètre `finish` (de type `bool`) a été remplacé par `flags` (de type `int`) pour prendre en charge la compression WebSocket, où `finish` correspond à la valeur `1` de `SWOOLE_WEBSOCKET_FLAG_FIN`, et la valeur booléenne originale sera implicitement convertie en entier, cette modification est compatible avec les versions antérieures sans impact. De plus, le flag de compression est `SWOOLE_WEBSOCKET_FLAG_COMPRESS`.

!> Dans le mode BASE (/learn?id=base模式的限制 :), il n'est pas possible de transmettre des données en utilisant la méthode `push` entre les processus.


### exist

?> **Vérifier si un client WebSocket existe et si son état est actif.**

!> À partir de la version v4.3.0, cette `API` est utilisée uniquement pour vérifier l'existence d'une connexion, veuillez utiliser `isEstablished` pour déterminer si c'est une connexion WebSocket

```php
Swoole\WebSocket\Server->exist(int $fd): bool
```

* **Valeur de retour**

  * Connexion existante et handshake WebSocket terminé, retourne `true`
  * Connexion inexistante ou handshake non terminé, retourne `false`
### pack

?> **Envelopper les messages WebSocket.**

```php
Swoole\WebSocket\Server::pack(\Swoole\WebSocket\Frame|string $data $data, int $opcode = WEBSOCKET_OPCODE_TEXT, bool $finish = true, bool $mask = false): string

// À partir de la version v4.4.12, le paramètre 'finish' (de type bool) a été remplacé par le paramètre 'flags' (de type int) pour prendre en charge la compression WebSocket. Le 'finish' correspondant à la valeur 'SWOOLE_WEBSOCKET_FLAG_FIN' est à '1'. Les valeurs booléennes existantes sont implicitement converties en types int, ce changement est compatible avec les versions antérieures sans impact.

Swoole\WebSocket\Server::pack(\Swoole\WebSocket\Frame|string $data $data, int $opcode = WEBSOCKET_OPCODE_TEXT, int $flags = SWOOLE_WEBSOCKET_FLAG_FIN): string

Swoole\WebSocket\Frame::pack(\Swoole\WebSocket\Frame|string $data $data, int $opcode = WEBSOCKET_OPCODE_TEXT, int $flags = SWOOLE_WEBSOCKET_FLAG_FIN): string
```

* **Paramètres** 

  * **`Swoole\WebSocket\Frame|string $data $data`**

    * **Fonction** : Contenu du message
    * **Valeur par défaut** : Aucun
    * **Autres valeurs** : Aucun

  * **`int $opcode`**

    * **Fonction** : Specifier la format du contenu de la donnée envoyée 【Par défaut, c'est du texte. Pour envoyer un contenu binaire, le paramètre `$opcode` doit être设置为`WEBSOCKET_OPCODE_BINARY`】
    * **Valeur par défaut** : `WEBSOCKET_OPCODE_TEXT`
    * **Autres valeurs** : `WEBSOCKET_OPCODE_BINARY`

  * **`bool $finish`**

    * **Fonction** : Indicate si le cadre est terminé
    * **Valeur par défaut** : Aucun
    * **Autres valeurs** : Aucun

  * **`bool $mask`**

    * **Fonction** : Indique si masquage est établi 【À partir de la version v4.4.12, ce paramètre a été supprimé】
    * **Valeur par défaut** : Aucun
    * **Autres valeurs** : Aucun

* **Valeur de retour**

  * Retourne un paquet de données WebSocket enveloppé, qui peut être envoyé à l'autre partie par la méthode [send()](/server/methods?id=send) de la classe de base Swoole\Server

* **Exemple**

```php
$ws = new Swoole\Server('127.0.0.1', 9501 , SWOOLE_BASE);

$ws->set(array(
    'log_file' => '/dev/null'
));

$ws->on('WorkerStart', function (\Swoole\Server $serv) {
});

$ws->on('receive', function ($serv, $fd, $threadId, $data) {
    $sendData = "HTTP/1.1 101 Switching Protocols\r\n";
    $sendData .= "Upgrade: websocket\r\nConnection: Upgrade\r\nSec-WebSocket-Accept: IFpdKwYy9wdo4gTldFLHFh3xQE0=\r\n";
    $sendData .= "Sec-WebSocket-Version: 13\r\nServer: swoole-http-server\r\n\r\n";
    $sendData .= Swoole\WebSocket\Server::pack("hello world\n");
    $serv->send($fd, $sendData);
});

$ws->start();
```


### unpack

?> **Désenvelopper un cadre de données WebSocket.**

```php
Swoole\WebSocket\Server::unpack(string $data): Swoole\WebSocket\Frame|false;
```

* **Paramètres** 

  * **`string $data`**

    * **Fonction** : Contenu du message
    * **Valeur par défaut** : Aucun
    * **Autres valeurs** : Aucun

* **Valeur de retour**

  * Retourne `false` en cas d'échec de la désenveloppement, sinon retourne un objet [Swoole\WebSocket\Frame](/websocket_server?id=swoolewebsocketframe)


### disconnect

?> **Envoi intentionnel d'un cadre de fermeture à un client WebSocket et fermeture de la connexion.**

!> Disponible à partir de la version Swoole >= `v4.0.3`

```php
Swoole\WebSocket\Server->disconnect(int $fd, int $code = SWOOLE_WEBSOCKET_CLOSE_NORMAL, string $reason = ''): bool
```

* **Paramètres** 

  * **`int $fd`**

    * **Fonction** : ID de la connexion client 【Si le `$fd` spécifié ne correspond pas à une connexion client WebSocket, l'envoi échouera】
    * **Valeur par défaut** : Aucun
    * **Autres valeurs** : Aucun

  * **`int $code`**

    * **Fonction** : Code d'état de la fermeture de la connexion 【Selon la `RFC6455`, pour les codes d'état de fermeture des connexions d'applications, les valeurs possibles sont de `1000` ou de `4000-4999`】
    * **Valeur par défaut** : `SWOOLE_WEBSOCKET_CLOSE_NORMAL`
    * **Autres valeurs** : Aucun

  * **`string $reason`**

    * **Fonction** : Raison de la fermeture 【String `utf-8`, la longueur en octets ne doit pas dépasser `125`】
    * **Valeur par défaut** : Aucun
    * **Autres valeurs** : Aucun

* **Valeur de retour**

  * Retourne `true` en cas d'envoi réussi, sinon `false` en cas d'échec ou de code d'état illégal


### isEstablished

?> **Vérifier si la connexion est une connexion client WebSocket valide.**

?> Cette fonction est différente de la méthode `exist`, qui ne vérifie que si c'est une connexion TCP, sans savoir si c'est une connexion client WebSocket qui a déjà effectué la main-d'œuvre.

```php
Swoole\WebSocket\Server->isEstablished(int $fd): bool
```

* **Paramètres** 

  * **`int $fd`**

    * **Fonction** : ID de la connexion client 【Si le `$fd` spécifié ne correspond pas à une connexion client WebSocket, l'opération échouera】
    * **Valeur par défaut** : Aucun
    * **Autres valeurs** : Aucun

* **Valeur de retour**

  * Retourne `true` si c'est une connexion valide, sinon `false`


## Classes de cadre de données WebSocket


### Swoole\WebSocket\Frame

?> À partir de la version v4.2.0, il y a une prise en charge ajoutée pour les objets [Swoole\WebSocket\Frame](/websocket_server?id=swoolewebsocketframe) envoyés par les serveurs et les clients  
À partir de la version v4.4.12, un nouveau attribut `flags` a été ajouté pour prendre en charge les cadres de compression WebSocket, et une nouvelle sous-classe [Swoole\WebSocket\CloseFrame](/websocket_server?id=swoolewebsocketcloseframe) a été ajoutée

Un objet cadre standard a les attributs suivants


Constantes | Explication 
---|--- 
fd | ID du socket client, utilisé lors de l'envoi de données avec `$server->push`    
data | Contenu du données, qui peut être du texte ou des données binaires, et peut être déterminé par la valeur de `opcode`    
opcode | Type de [cadre de données WebSocket](/websocket_server?id=数据帧类型), voir la documentation standard du protocole WebSocket    
finish | Indique si le cadre de données est complet, une demande WebSocket peut être divisée en plusieurs cadres de données pour l'envoi (la fusion automatique des cadres de données est déjà implémentée en bas, vous n'avez donc pas à vous soucier de recevoir des cadres de données incomplets)  

Cette classe comprend [Swoole\WebSocket\Frame::pack()](/websocket_server?id=pack) et [Swoole\WebSocket\Frame::unpack()](/websocket_server?id=unpack), utilisées pour emballer et déballer les messages websocket, les paramètres sont expliqués de la même manière que pour `Swoole\WebSocket\Server::pack()` et `Swoole\WebSocket\Server::unpack()`


### Swoole\WebSocket\CloseFrame

Un objet cadre de fermeture standard a les attributs suivants


Constantes | Explication 
---|--- 
opcode | Type de [cadre de données WebSocket](/websocket_server?id=数据帧类型), voir la documentation standard du protocole WebSocket    
code | Code d'état de fermeture de WebSocket, voir [les codes d'erreur définis dans le protocole websocket](https://developer.mozilla.org/zh-CN/docs/Web/API/CloseEvent)    
reason | Raison de la fermeture, si elle n'est pas spécifiée, elle est vide

Si le serveur doit recevoir des cadres de fermeture, il doit activer le paramètre [open_websocket_close_frame](/websocket_server?id=open_websocket_close_frame) en utilisant `$server->set`
### État de connexion


Constante | Valeur correspondante | Description
---|---|---
WEBSOCKET_STATUS_CONNECTION | 1 | Connexion en attente de la main-d'œuvre
WEBSOCKET_STATUS_HANDSHAKE | 2 | En train de faire la main-d'œuvre
WEBSOCKET_STATUS_ACTIVE | 3 | Connexion établie avec succès en attente de données de la part du navigateur
WEBSOCKET_STATUS_CLOSING | 4 | Connexion en train de procéder à la main-d'œuvre de fermeture, sur le point de se fermer


### Codes d'état des帧 de fermeture WebSocket


Constante | Valeur correspondante | Description
---|---|---
WEBSOCKET_CLOSE_NORMAL | 1000 | Fermeture normale, la connexion a terminé sa tâche
WEBSOCKET_CLOSE_GOING_AWAY | 1001 | Disconnection du serveur
WEBSOCKET_CLOSE_PROTOCOL_ERROR | 1002 | Erreur de protocole, interruption de la connexion
WEBSOCKET_CLOSE_DATA_ERROR | 1003 | Erreur de données, par exemple, demande de données textuelles mais réception de données binaires
WEBSOCKET_CLOSE_STATUS_ERROR | 1005 | Indique l'absence de code d'état attendu
WEBSOCKET_CLOSE_ABNORMAL | 1006 | Sans envoi de frame de fermeture
WEBSOCKET_CLOSE_MESSAGE_ERROR | 1007 | Disconnection en raison de la réception de données mal formées (par exemple, message texte contenant des données non UTF-8).
WEBSOCKET_CLOSE_POLICY_ERROR | 1008 | Disconnection en raison de la réception de données non conformes aux accords. C'est un code d'état général utilisé pour des scénarios qui ne correspondent pas aux codes d'état 1003 et 1009.
WEBSOCKET_CLOSE_MESSAGE_TOO_BIG | 1009 | Disconnection en raison de la réception d'un frame de données trop grand
WEBSOCKET_CLOSE_EXTENSION_MISSING | 1010 | Le client attend que le serveur négocie une ou plusieurs extensions, mais le serveur ne les gère pas, entraînant la disconnection du client.
WEBSOCKET_CLOSE_SERVER_ERROR | 1011 | Le client, en raison de circonstances inattendues, empêche l'achèvement de sa demande, entraînant la disconnection du serveur.
WEBSOCKET_CLOSE_TLS | 1015 | Réservé. Indique que la connexion est fermée en raison de l'impossibilité de terminer la main-d'œuvre TLS (par exemple, en raison de l'incapacité à vérifier le certificat du serveur).


## Options

?> `Swoole\WebSocket\Server` est une sous-classe de `Server`, qui peut utiliser la méthode [Swoole\WebSocker\Server::set()](/server/methods?id=set) pour passer des options de configuration et ajuster certains paramètres.


### websocket_subprotocol

?> **Configuration du sous-protocole `WebSocket`.**

?> Après la configuration, la tête HTTP de la réponse de la main-d'œuvre inclura `Sec-WebSocket-Protocol: {$websocket_subprotocol}`. Pour plus d'informations sur l'utilisation, veuillez consulter la documentation RFC relative au protocole `WebSocket`.

```php
$server->set([
    'websocket_subprotocol' => 'chat',
]);
```


### open_websocket_close_frame

?> **Activer la réception des frames de fermeture (avec l'opcode `0x08`) dans le callback `onMessage` du protocole `WebSocket`, par défaut à `false`.**

?> Une fois activé, vous pouvez recevoir dans le callback `onMessage` de `Swoole\WebSocket\Server` les frames de fermeture envoyés par le client ou le serveur. Les développeurs peuvent alors les gérer eux-mêmes.

```php
$server = new Swoole\WebSocket\Server("0.0.0.0", 9501);
$server->set(array("open_websocket_close_frame" => true));
$server->on('open', function (Swoole\WebSocket\Server $server, $request) {
});

$server->on('message', function (Swoole\WebSocket\Server $server, $frame) {
    if ($frame->opcode == 0x08) {
        echo "Frame de fermeture reçu : Code {$frame->code} Reason {$frame->reason}\n";
    } else {
        echo "Message reçu : {$frame->data}\n";
    }
});

$server->on('close', function ($server, $fd) {
});

$server->start();
```


### open_websocket_ping_frame

?> **Activer la réception des frames `Ping` (avec l'opcode `0x09`) dans le callback `onMessage` du protocole `WebSocket`, par défaut à `false`.**

?> Une fois activé, vous pouvez recevoir dans le callback `onMessage` de `Swoole\WebSocket\Server` les frames `Ping` envoyés par le client ou le serveur. Les développeurs peuvent alors les gérer eux-mêmes.

!> Version Swoole >= `v4.5.4` disponible

```php
$server->set([
    'open_websocket_ping_frame' => true,
]);
```

!> Lorsque la valeur est `false`, le niveau inférieur répondra automatiquement par un frame `Pong`, mais si elle est fixée à `true`, les développeurs doivent répondre eux-mêmes par un frame `Pong`.

* **Exemple**

```php
$server = new Swoole\WebSocket\Server("0.0.0.0", 9501);
$server->set(array("open_websocket_ping_frame" => true));
$server->on('open', function (Swoole\WebSocket\Server $server, $request) {
});

$server->on('message', function (Swoole\WebSocket\Server $server, $frame) {
    if ($frame->opcode == 0x09) {
        echo "Frame Ping reçu : Code {$frame->opcode}\n";
        // Répondre par un frame Pong
        $pongFrame = new Swoole\WebSocket\Frame;
        $pongFrame->opcode = WEBSOCKET_OPCODE_PONG;
        $server->push($frame->fd, $pongFrame);
    } else {
        echo "Message reçu : {$frame->data}\n";
    }
});

$server->on('close', function ($server, $fd) {
});

$server->start();
```


### open_websocket_pong_frame

?> **Activer la réception des frames `Pong` (avec l'opcode `0x0A`) dans le callback `onMessage` du protocole `WebSocket`, par défaut à `false`.**

?> Une fois activé, vous pouvez recevoir dans le callback `onMessage` de `Swoole\WebSocket\Server` les frames `Pong` envoyés par le client ou le serveur. Les développeurs peuvent alors les gérer eux-mêmes.

!> Version Swoole >= `v4.5.4` disponible

```php
$server->set([
    'open_websocket_pong_frame' => true,
]);
```

* **Exemple**

```php
$server = new Swoole\WebSocket\Server("0.0.0.0", 9501);
$server->set(array("open_websocket_pong_frame" => true));
$server->on('open', function (Swoole\WebSocket\Server $server, $request) {
});

$server->on('message', function (Swoole\WebSocket\Server $server, $frame) {
    if ($frame->opcode == 0xa) {
        echo "Frame Pong reçu : Code {$frame->opcode}\n";
    } else {
        echo "Message reçu : {$frame->data}\n";
    }
});

$server->on('close', function ($server, $fd) {
});

$server->start();
```


### websocket_compression

?> **Activer la compression des données**

?> Lorsque la valeur est `true`, il est possible de comprimer les frames avec `zlib`. La capacité à compresser dépend du fait que le client peut gérer la compression (décidé selon les informations de main-d'œuvre, voir RFC-7692). Pour vraiment compresser un frame spécifique, utilisez le paramètre `flags` `SWOOLE_WEBSOCKET_FLAG_COMPRESS`, voir [cette section](/websocket_server?id=compression_des_frames_websockets-(rfc-7692)).

!> Version Swoole >= `v4.4.12` disponible


## Autres

!> Des exemples de code connexes peuvent être trouvés dans le [test unitaire WebSocket](https://github.com/swoole/swoole-src/tree/master/tests/swoole_websocket_server).


### Compression des frames WebSocket (RFC-7692)

?> Tout d'abord, vous devez configurer `'websocket_compression' => true` pour activer la compression (lors de la main-d'œuvre `WebSocket`, des informations sur le support de la compression sont échangées avec l'autre partie). Ensuite, vous pouvez utiliser le flag `SWOOLE_WEBSOCKET_FLAG_COMPRESS` pour compresser un frame spécifique.

#### Exemple

* **Serveur**

```php
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

$server = new Server('127.0.0.1', 9501);
$server->set(['websocket_compression' => true]);
$server->on('message', function (Server $server, Frame $frame) {
    $server->push(
        $frame->fd,
        'Hello Swoole',
        SWOOLE_WEBSOCKET_OPCODE_TEXT,
        SWOOLE_WEBSOCKET_FLAG_FIN | SWOOLE_WEBSOCKET_FLAG_COMPRESS
    );
    // $server->push($frame->fd, $frame); // Ou le serveur peut转发 directement le frame envoyé par le client
});
$server->start();
```

* **Client**

```php
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $cli = new Client('127.0.0.1', 9501);
    $cli->set(['websocket_compression' => true]);
    $cli->upgrade('/');
    $cli->push(
        'Hello Swoole',
        SWOOLE_WEBSOCKET_OPCODE_TEXT,
        SWOOLE_WEBSOCKET_FLAG_FIN | SWOOLE_WEBSOCKET_FLAG_COMPRESS
    );
});
```
### Envoyer un cadre Ping

?> Étant donné que WebSocket est une connexion à long terme, si aucune communication n'a lieu pendant un certain temps, la connexion peut se déconnecter. Dans ce cas, un mécanisme de battement de cœur est nécessaire. Le protocole WebSocket comprend deux cadres, Ping et Pong, qui peuvent être envoyés périodiquement pour maintenir la connexion à long terme.

#### Exemple

* **Serveur**

```php
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

$server = new Server('127.0.0.1', 9501);
$server->on('message', function (Server $server, Frame $frame) {
    $pingFrame = new Frame;
    $pingFrame->opcode = WEBSOCKET_OPCODE_PING;
    $server->push($frame->fd, $pingFrame);
});
$server->start();
```

* **Client**

```php
use Swoole\WebSocket\Frame;
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function () {
    $cli = new Client('127.0.0.1', 9501);
    $cli->upgrade('/');
    $pingFrame = new Frame;
    $pingFrame->opcode = WEBSOCKET_OPCODE_PING;
    // Envoyer PING
    $cli->push($pingFrame);
    
    // Réception de PONG
    $pongFrame = $cli->recv();
    var_dump($pongFrame->opcode === WEBSOCKET_OPCODE_PONG);
});
```
