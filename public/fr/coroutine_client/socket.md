# Coordonnées\Socket

Le module `Swoole\Coroutine\Socket` permet de réaliser des opérations `IO` plus granules par rapport aux modules `Socket` associés à la [serveur en style de coroutines](/server/co_init) et au [client en style de coroutines](/coroutine_client/init).

!> Il est possible d'utiliser le raccourci `Co\Socket` pour simplifier le nom de la classe. Ce module est assez bas niveau, il est préférable que les utilisateurs aient une expérience en programmation avec des sockets.


## Exemple complet

```php
use Swoole\Coroutine;
use function Swoole\Coroutine\run;

run(function () {
    $socket = new Coroutine\Socket(AF_INET, SOCK_STREAM, 0);

    $retval = $socket->connect('127.0.0.1', 9601);
    while ($retval)
    {
        $n = $socket->send('hello');
        var_dump($n);

        $data = $socket->recv();
        var_dump($data);

        // Une erreur s'est produite ou l'autre partie a fermé la connexion, il est également nécessaire de fermer la connexion locale
        if ($data === '' || $data === false) {
            echo "errCode: {$socket->errCode}\n";
            $socket->close();
            break;
        }

        Coroutine::sleep(1.0);
    }

    var_dump($retval, $socket->errCode, $socket->errMsg);
});
```


## Planification des coroutines

Les interfaces d'opération `IO` fournies par le module `Coroutine\Socket` sont toutes de style de programmation synchrone, et le bas niveau utilise automatiquement le [调度器 de coroutines](/coroutine?id=调度器) pour réaliser l'[IO asynchrone](/learn?id=同步io异步io) .


## Codes d'erreur

Lors de l'exécution des appels système liés aux `sockets`, un code d'erreur de -1 peut être retourné, et le bas niveau définit la propriété `Coroutine\Socket->errCode` avec l'编号 d'erreur système `errno`. Veuillez consulter la documentation `man` correspondante. Par exemple, si la méthode `$socket->accept()` retourne une erreur, vous pouvez consulter la documentation des codes d'erreur listés dans la documentation `man accept`.


## Propriétés


### fd

ID du descripteur de fichier correspondant au `socket`


### errCode

Code d'erreur


## Méthodes


### __construct()

Constructeur. Crée un objet `Coroutine\Socket`.

```php
Swoole\Coroutine\Socket::__construct(int $domain, int $type, int $protocol);
```

!> Pour plus de détails, veuillez consulter la documentation `man socket`.

  * **Paramètres** 

    * **`int $domain`**
      * **Fonction** : domaine du protocole【Peut utiliser `AF_INET`, `AF_INET6`, `AF_UNIX`】
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

    * **`int $type`**
      * **Fonction** : type【Peut utiliser `SOCK_STREAM`, `SOCK_DGRAM`, `SOCK_RAW`】
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

    * **`int $protocol`**
      * **Fonction** : protocole【Peut utiliser `IPPROTO_TCP`, `IPPROTO_UDP`, `IPPROTO_STCP`, `IPPROTO_TIPC`, `0`】
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

!> Le constructeur appelle l'appel système `socket` pour créer un handle de `socket`. Si la création échoue, une exception `Swoole\Coroutine\Socket\Exception` est lancée et la propriété `$socket->errCode` est définie. La valeur de cette propriété peut être utilisée pour obtenir la raison de l'échec de l'appel système.


### getOption()

Obtenir la configuration.

!> Cette méthode correspond à l'appel système `getsockopt`, veuillez consulter la documentation `man getsockopt`.  
Cette méthode est équivalente à la fonction `socket_get_option` de l'extension `sockets`, veuillez consulter la [documentation PHP](https://www.php.net/manual/zh/function.socket-get-option.php).

!> Version Swoole >= v4.3.2

```php
Swoole\Coroutine\Socket->getOption(int $level, int $optname): mixed
```

  * **Paramètres** 

    * **`int $level`**
      * **Fonction** : niveau du protocole spécifiant l'option
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

      !> Par exemple, pour récupérer une option au niveau du socket, utilisez le paramètre `level` de `SOL_SOCKET`.  
      Vous pouvez utiliser d'autres niveaux en spécifiant le numéro de protocole pour ce niveau, par exemple `TCP`. Vous pouvez utiliser la fonction [getprotobyname](https://www.php.net/manual/zh/function.getprotobyname.php) pour trouver le numéro de protocole.

    * **`int $optname`**
      * **Fonction** : options de socket disponibles, identiques aux options de la fonction [socket_get_option()](https://www.php.net/manual/zh/function.socket-get-option.php)
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun


### setOption()

Définir la configuration.

!> Cette méthode correspond à l'appel système `setsockopt`, veuillez consulter la documentation `man setsockopt`. Cette méthode est équivalente à la fonction `socket_set_option` de l'extension `sockets`, veuillez consulter la [documentation PHP](https://www.php.net/manual/zh/function.socket-set-option.php).

!> Version Swoole >= v4.3.2

```php
Swoole\Coroutine\Socket->setOption(int $level, int $optname, mixed $optval): bool
```

  * **Paramètres** 

    * **`int $level`**
      * **Fonction** : niveau du protocole spécifiant l'option
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

      !> Par exemple, pour définir une option au niveau du socket, utilisez le paramètre `level` de `SOL_SOCKET`.  
      Vous pouvez utiliser d'autres niveaux en spécifiant le numéro de protocole pour ce niveau, par exemple `TCP`. Vous pouvez utiliser la fonction [getprotobyname](https://www.php.net/manual/zh/function.getprotobyname.php) pour trouver le numéro de protocole.

    * **`int $optname`**
      * **Fonction** : options de socket disponibles, identiques aux options de la fonction [socket_get_option()](https://www.php.net/manual/zh/function.socket-get-option.php)
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

    * **`int $optval`**
      * **Fonction** : valeur de l'option 【Peut être `int`, `bool`, `string`, `array`. Decide en fonction de `level` et `optname`.】
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun


### setProtocol()

Permet au `socket` d'acquérir la capacité de traiter le protocole, peut configurer si l'encryption SSL est activée pour la transmission et résoudre les problèmes de [frontière de paquets TCP](/learn?id=tcp数据包边界问题) , etc.

!> Version Swoole >= v4.3.2

```php
Swoole\Coroutine\Socket->setProtocol(array $settings): bool
```

  * **Paramètres `$settings` soutenus**


Paramètre | Type
---|---
open_ssl | bool
ssl_cert_file | string
ssl_key_file | string
open_eof_check | bool
open_eof_split | bool
open_mqtt_protocol | bool
open_fastcgi_protocol | bool
open_length_check | bool
package_eof | string
package_length_type | string
package_length_offset | int
package_body_offset | int
package_length_func | callable
package_max_length | int

!> La signification de tous les paramètres mentionnés ci-dessus est entièrement identique à celle de la méthode [Server->set()](/server/setting?id=open_eof_check), et n'est donc pas répétée ici.

  * **Exemple**

```php
$socket->setProtocol([
    'open_length_check'     => true,
    'package_max_length'    => 1024 * 1024,
    'package_length_type'   => 'N',
    'package_length_offset' => 0,
    'package_body_offset'   => 4,
]);
```


### bind()

Lier une adresse et un port.

!> Cette méthode n'implique pas d'opération `IO` et ne provoquera pas le changement de coroutine.

```php
Swoole\Coroutine\Socket->bind(string $address, int $port = 0): bool
```

  * **Paramètres** 

    * **`string $address`**
      * **Fonction** : adresse à lier 【Par exemple `0.0.0.0`, `127.0.0.1`】
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

    * **`int $port`**
      * **Fonction** : port à lier 【Par défaut `0`, le système assignera un port disponible au hasard, utilisez la méthode [getsockname](/coroutine_client/socket?id=getsockname) pour obtenir le port attribué par le système】
      * **Valeur par défaut** : `0`
      * **Autres valeurs** : Aucun

  * **Valeurs de retour** 

    * True si le lien est réussi
    * False si le lien échoue, veuillez vérifier la propriété `errCode` pour obtenir la raison de l'échec

### écouter()

Écoute un `Socket`.

!> Cette méthode ne contient pas d'opération `IO`, elle n'entraînera pas le changement de coroutines

```php
Swoole\Coroutine\Socket->listen(int $backlog = 0): bool
```

  * **Paramètres** 

    * **`int $backlog`**
      * **Fonction** : Longueur de la file d'attente d'écoute【Par défaut `0`, le système utilise `epoll` pour réaliser l'IO asynchrone, il n'y a pas de blocage, donc l'importance du `backlog` n'est pas élevée】
      * **Valeur par défaut** : `0`
      * **Autres valeurs** : None

      !> Si dans l'application il y a des logiques bloquantes ou longues, l'acceptation des connexions ne se fait pas en temps opportun, les nouvelles connexions créées s'accumulent dans la file d'attente d'écoute du `backlog`, et si elle dépasse la longueur du `backlog`, le service refusera de nouvelles connexions entrantes

  * **Valeurs de retour** 

    * Retourne `true` en cas de réussite de l'attachement
    * Retourne `false` en cas d'échec de l'attachement, veuillez vérifier la propriété `errCode` pour obtenir la raison de l'échec

  * **Paramètres du noyau**

    La valeur maximale du `backlog` est limitée par le paramètre de noyau `net.core.somaxconn`, et sur `Linux`, il est possible d'utiliser la commande `sysctl` pour ajuster dynamiquement tous les paramètres du noyau. L'ajustement dynamique prend effet immédiatement après la modification des valeurs de paramètres de noyau. Cependant, cet effet est limité au niveau de l'OS, il est nécessaire de redémarrer l'application pour que les changements prennent pleinement effet. La commande `sysctl -a` affiche tous les paramètres de noyau et leurs valeurs.

    ```shell
    sysctl -w net.core.somaxconn=2048
    ```

    La commande ci-dessus modifie la valeur du paramètre de noyau `net.core.somaxconn` en `2048`. Bien que ce changement puisse prendre effet immédiatement, il redevient à sa valeur par défaut après un redémarrage de l'ordinateur. Pour conserver ces changements de manière permanente, il est nécessaire de modifier le fichier `/etc/sysctl.conf`, d'ajouter `net.core.somaxconn=2048` puis d'exécuter la commande `sysctl -p` pour que les changements prennent effet.


### accepter()

Accepte une connexion lancée par un client.

En appelant cette méthode, la coroutine actuelle est immédiatement suspendue et rejoins l'EventLoop pour écouter les événements lisibles. Lorsque le `Socket` est prêt à recevoir des connexions, la coroutine est automatiquement réveillée et le `Socket` de la connexion client est retourné.

!> Cette méthode doit être utilisée après avoir utilisé la méthode `écouter`, elle est applicable à l'extrémité du `Server`.

```php
Swoole\Coroutine\Socket->accept(float $timeout = 0): Coroutine\Socket|false;
```

  * **Paramètres** 

    * **`float $timeout`**
      * **Fonction** : Définir le délai d'attente【Après avoir établi un paramètre de délai, le niveau inférieur installe un minuteur et, si aucune connexion client ne arrive dans le délai spécifié, la méthode `accepter` retournera `false`】
      * **Unité de valeur** : seconde【Soutient les valeurs à virgule flottante, comme `1.5` représente `1s`+`500ms`】
      * **Valeur par défaut** : Voir les règles de délai d'attente des clients [/learn?id=règles_de_délai]
      * **Autres valeurs** : None

  * **Valeurs de retour** 

    * Retourne `false` en cas de dépassement du délai ou si l'appel système `accepter` échoue, utilisez la propriété `errCode` pour obtenir le code d'erreur, où le code d'erreur de dépassement du délai est `ETIMEDOUT`
    * Retourne `true` en cas de succès

  * **Exemple**

```php
use Swoole\Coroutine;
use function Swoole\Coroutine\run;

run(function () {
$socket = new Coroutine\Socket(AF_INET, SOCK_STREAM, 0);
$socket->bind('127.0.0.1', 9601);
$socket->listen(128);

    while(true) {
        echo "Accept: \n";
        $client = $socket->accept();
        if ($client === false) {
            var_dump($socket->errCode);
        } else {
            var_dump($client);
        }
    }
});
```


### se connecter()

Se connecte à un serveur cible.

En appelant cette méthode, un appel système asynchrone `connect` est lancé et la coroutine actuelle est suspendue. Le niveau inférieur écoute les événements écrits et, une fois la connexion établie ou échouée, la coroutine est restaurée.

Cette méthode est applicable à l'extrémité du `Client`, elle prend en charge `IPv4`, `IPv6` et [unixSocket](/learn?id=qu'est-ce_qu'un_socket_unix).

```php
Swoole\Coroutine\Socket->connect(string $host, int $port = 0, float $timeout = 0): bool
```

  * **Paramètres** 

    * **`string $host`**
      * **Fonction** : Adresse du serveur cible【Peu importe si c'est une adresse IP (`127.0.0.1`, `192.168.1.100`), un chemin de socket Unix (`/tmp/php-fpm.sock`) ou un nom de domaine (comme `www.baidu.com`). Si c'est un nom de domaine, le niveau inférieur effectuera automatiquement une résolution DNS asynchrone sans bloquer】
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

    * **`int $port`**
      * **Fonction** : Port du serveur cible【Il est nécessaire de spécifier le port lorsque la `domain` du `Socket` est `AF_INET` ou `AF_INET6`】
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

    * **`float $timeout`**
      * **Fonction** : Définir le délai d'attente【Le niveau inférieur installe un minuteur et, si la connexion n'est pas établie dans le délai spécifié, la méthode `connect` retournera `false`】
      * **Unité de valeur** : seconde【Soutient les valeurs à virgule flottante, comme `1.5` représente `1s`+`500ms`】
      * **Valeur par défaut** : Voir les règles de délai d'attente des clients [/learn?id=règles_de_délai]
      * **Autres valeurs** : None

  * **Valeurs de retour** 

    * Retourne `false` en cas de dépassement du délai ou si l'appel système `connect` échoue, utilisez la propriété `errCode` pour obtenir le code d'erreur, où le code d'erreur de dépassement du délai est `ETIMEDOUT`
    * Retourne `true` en cas de succès


### vérifierLiveness()

Vérifie la vitalité de la connexion par appel système (invalide en cas de déconnexion anormale, ne peut détecter que la déconnexion de l'autre partie sous une fermeture normale)

!> Disponible pour les versions de Swoole >= `v4.5.0`

```php
Swoole\Coroutine\Socket->checkLiveness(): bool
```

  * **Valeurs de retour** 

    * Retourne `true` si la connexion est viable, sinon retourne `false`


### envoyer()

Envoie des données à l'autre partie.

!> La méthode `envoyer` exécute immédiatement l'appel système `send` pour envoyer les données. Lorsque l'appel système `send` retourne une erreur `EAGAIN`, le niveau inférieur écoute automatiquement les événements écrits et suspend la coroutine actuelle. Lorsque l'événement écrit se produit, la méthode `send` est réexécutée pour envoyer les données et la coroutine est réveillée.  

!> Si l'envoi est trop rapide et que la réception est trop lente, cela peut finalement remplir le tampon d'opération système, la coroutine actuelle sera suspendue dans la méthode `send`. Il est possible d'augmenter de manière appropriée la taille du tampon, [/proc/sys/net/core/wmem_max et SO_SNDBUF](https://stackoverflow.com/questions/21856517/whats-the-practical-limit-on-the-size-of-single-packet-transmitted-over-domain)

```php
Swoole\Coroutine\Socket->send(string $data, float $timeout = 0): int|false
```

  * **Paramètres** 

    * **`string $data`**
      * **Fonction** : Contenu des données à envoyer【Peut être du texte ou des données binaires】
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

    * **`float $timeout`**
      * **Fonction** : Définir le délai d'attente
      * **Unité de valeur** : seconde【Soutient les valeurs à virgule flottante, comme `1.5` représente `1s`+`500ms`】
      * **Valeur par défaut** : Voir les règles de délai d'attente des clients [/learn?id=règles_de_délai]
      * **Autres valeurs** : None

  * **Valeurs de retour** 

    * Retourne le nombre de字节 écrits avec succès, **veuillez noter que la quantité de données réellement écrites peut être inférieure à la longueur du paramètre `$data`**, les applications doivent comparer la valeur de retour avec `strlen($data)` pour déterminer si l'envoi est terminé
    * Retourne `false` en cas d'échec de l'envoi, et la propriété `errCode` est définie

### sendAll()

Envoie des données à l'autre extrémité du socket. Contrairement au método `send`, la méthode `sendall` envoie les données de manière complète et persiste jusqu'à ce qu'elle soit entièrement envoyée avec succès ou qu'une erreur survienne et l'opération soit interrompue.

!> La méthode `sendall` exécute immédiatement plusieurs appels système `send` pour envoyer les données. Lorsque l'appel système `send` retourne l'erreur `EAGAIN`, le niveau inférieur surveille automatiquement les événements d'écriture et suspend la coroutine actuelle. Lorsque l'événement d'écriture se produit, il réexécute l'appel système `send` pour envoyer les données jusqu'à ce que la transmission soit complète ou qu'une erreur survienne, et réveille la coroutine correspondante.

!> Version Swoole >= v4.3.0

```php
Swoole\Coroutine\Socket->sendall(string $data, float $timeout = 0) : int | false;
```

  * **Paramètres** 

    * **`string $data`**
      * **Fonction** : Contenu des données à envoyer (peut être du texte ou des données binaires)
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

    * **`float $timeout`**
      * **Fonction** : Définit le temps d'attente
      * **Unité de valeur** : seconde (prévu pour les nombres flottants, comme `1.5` signifie `1s`+`500ms`)
      * **Valeur par défaut** : Consulte les règles de timeout client [ici](/coroutine_client/init?id=règles_de_timeout)
      * **Autres valeurs** : None

  * **Valeurs de retour** 

    * La méthode `sendall` garantit que les données sont envoyées avec succès, mais il est possible que l'autre extrémité du socket se déconnecte pendant le processus `sendall`, dans ce cas, certaines données peuvent être envoyées avec succès. La valeur de retour est la longueur des données envoyées avec succès, et le code d'application doit vérifier si la valeur de retour est égale à `strlen($data)` pour déterminer si la transmission est terminée et si une nouvelle transmission est nécessaire en fonction des exigences du service.
    * Le retour en cas d'échec de l'envoi est `false`, et la propriété `errCode` est définie.


### peek()

Regarde les données dans le tampon de lecture, ce qui équivaut à l'appel système `recv(length, MSG_PEEK)`.

!> La méthode `peek` est immédiatement terminée et ne suspend pas la coroutine, mais elle a le coût d'un appel système.

```php
Swoole\Coroutine\Socket->peek(int $length = 65535): string|false
```

  * **Paramètres** 

    * **`int $length`**
      * **Fonction** : Spécifie la taille de la mémoire utilisée pour copier les données regardées (notez que de la mémoire sera allouée ici, une trop grande longueur peut entraîner une épuisement de la mémoire)
      * **Unité de valeur** : Byte
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

  * **Valeurs de retour** 

    * Le retour en cas de réussite de la regard est les données.
    * Le retour en cas d'échec de la regard est `false`, et la propriété `errCode` est définie.


### recv()

Réception de données.

!> La méthode `recv` suspend immédiatement la coroutine actuelle et surveille les événements lisibles, attendant que l'autre extrémité envoie des données. Lorsque l'événement lisible se produit, elle exécute l'appel système `recv` pour obtenir les données du tampon de socket et réveille la coroutine correspondante.

```php
Swoole\Coroutine\Socket->recv(int $length = 65535, float $timeout = 0): string|false
```

  * **Paramètres** 

    * **`int $length`**
      * **Fonction** : Spécifie la taille de la mémoire utilisée pour recevoir les données (notez que de la mémoire sera allouée ici, une trop grande longueur peut entraîner une épuisement de la mémoire)
      * **Unité de valeur** : Byte
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

    * **`float $timeout`**
      * **Fonction** : Définit le temps d'attente
      * **Unité de valeur** : seconde (prévu pour les nombres flottants, comme `1.5` signifie `1s`+`500ms`)
      * **Valeur par défaut** : Consulte les règles de timeout client [ici](/coroutine_client/init?id=règles_de_timeout)
      * **Autres valeurs** : None

  * **Valeurs de retour** 

    * Le retour en cas de réussite de la réception est les données réelles.
    * Le retour en cas d'échec de la réception est `false`, et la propriété `errCode` est définie.
    * Le retour en cas d'timeout, le code d'erreur est `ETIMEDOUT`.

!> La valeur de retour n'est pas nécessairement égale à la longueur attendue, il est nécessaire de vérifier la longueur des données reçues lors de cet appel. Si vous devez garantir qu'une seule invocation obtient des données de la longueur spécifiée, veuillez utiliser la méthode `recvAll` ou itérer vous-même pour obtenir les données.
Pour les problèmes de bordure de paquets TCP, veuillez consulter la méthode `setProtocol()` ou utiliser `sendto()`.


### recvAll()

Réception de données. Contrairement à `recv`, `recvAll` recevra尽可能complètement les données de la taille de la réponse jusqu'à ce qu'elle soit entièrement reçue ou qu'une erreur survienne et l'opération échoue.

!> La méthode `recvAll` suspend immédiatement la coroutine actuelle et surveille les événements lisibles, attendant que l'autre extrémité envoie des données. Lorsque l'événement lisible se produit, elle exécute l'appel système `recv` pour obtenir les données du tampon de socket, répétant cette action jusqu'à ce qu'elle reçoive la longueur de données spécifiée ou qu'une erreur survienne et l'opération soit interrompue, et réveille la coroutine correspondante.

!> Version Swoole >= v4.3.0

```php
Swoole\Coroutine\Socket->recvAll(int $length = 65535, float $timeout = 0): string|false
```

  * **Paramètres** 

    * **`int $length`**
      * **Fonction** : La taille des données attendues (notez que de la mémoire sera allouée ici, une trop grande longueur peut entraîner une épuisement de la mémoire)
      * **Unité de valeur** : Byte
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

    * **`float $timeout`**
      * **Fonction** : Définit le temps d'attente
      * **Unité de valeur** : seconde (prévu pour les nombres flottants, comme `1.5` signifie `1s`+`500ms`)
      * **Valeur par défaut** : Consulte les règles de timeout client [ici](/coroutine_client/init?id=règles_de_timeout)
      * **Autres valeurs** : None

  * **Valeurs de retour** 

    * Le retour en cas de réussite de la réception est les données réelles, et la longueur du字符串retour est cohérente avec la longueur des paramètres.
    * Le retour en cas d'échec de la réception est `false`, et la propriété `errCode` est définie.
    * Le retour en cas d'timeout, le code d'erreur est `ETIMEDOUT`.


### readVector()

Réception de données par segments.

!> La méthode `readVector` exécute immédiatement l'appel système `readv` pour lire les données. Lorsque l'appel système `readv` retourne l'erreur `EAGAIN`, le niveau inférieur surveille automatiquement les événements lisibles et suspend la coroutine actuelle. Lorsque l'événement lisible se produit, il réexécute l'appel système `readv` pour lire les données et réveille la coroutine correspondante.

!> Version Swoole >= v4.5.7

```php
Swoole\Coroutine\Socket->readVector(array $io_vector, float $timeout = 0): array|false
```

  * **Paramètres** 

    * **`array $io_vector`**
      * **Fonction** : La taille des segments de données attendus
      * **Unité de valeur** : Byte
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

    * **`float $timeout`**
      * **Fonction** : Définit le temps d'attente
      * **Unité de valeur** : seconde (prévu pour les nombres flottants, comme `1.5` signifie `1s`+`500ms`)
      * **Valeur par défaut** : Consulte les règles de timeout client [ici](/coroutine_client/init?id=règles_de_timeout)
      * **Autres valeurs** : None

  * **Valeurs de retour**

    * Le retour en cas de réussite de la réception est les données par segments.
    * Le retour en cas d'échec de la réception est un tableau vide, et la propriété `errCode` est définie.
    * Le retour en cas d'timeout, le code d'erreur est `ETIMEDOUT`.

  * **Exemple** 

```php
$socket = new Swoole\Coroutine\Socket(AF_INET, SOCK_STREAM, 0);
// Si l'autre extrémité a envoyé "helloworld"
$ret = $socket->readVector([5, 5]);
// Alors, $ret est ['hello', 'world']
```


### readVectorAll()

Réception de données par segments.

!> La méthode `readVectorAll` exécute immédiatement plusieurs appels système `readv` pour lire les données. Lorsque l'appel système `readv` retourne l'erreur `EAGAIN`, le niveau inférieur surveille automatiquement les événements lisibles et suspend la coroutine actuelle. Lorsque l'événement lisible se produit, il réexécute l'appel système `readv` pour lire les données, et ce jusqu'à ce que la lecture des données soit terminée ou qu'une erreur survienne, et réveille la coroutine correspondante.

!> Version Swoole >= v4.5.7

```php
Swoole\Coroutine\Socket->readVectorAll(array $io_vector, float $timeout = 0): array|false
```

  * **Paramètres** 

    * **`array $io_vector`**
      * **Fonction** : La taille des segments de données attendus
      * **Unité de valeur** : Byte
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

    * **`float $timeout`**
      * **Fonction** : Définit le temps d'attente
      * **Unité de valeur** : seconde (prévu pour les nombres flottants, comme `1.5` signifie `1s`+`500ms`)
      * **Valeur par défaut** : Consulte les règles de timeout client [ici](/coroutine_client/init?id=règles_de_timeout)
      * **Autres valeurs** : None

  * **Valeurs de retour**

    * Le retour en cas de réussite de la réception est les données par segments.
    * Le retour en cas d'échec de la réception est un tableau vide, et la propriété `errCode` est définie.
    * Le retour en cas d'timeout, le code d'erreur est `ETIMEDOUT`.
### writeVector()

Transmettre des données par segments.

!> La méthode `writeVector` exécute immédiatement la system call `writev` pour envoyer les données. Lorsque la system call `writev` retourne une erreur `EAGAIN`, le niveau inférieur surveille automatiquement les événements écrits et suspend la coroutine actuelle. Lorsque l'événement écrit se produit, la system call `writev` est réexécutée pour envoyer les données et la coroutine est réveillée.  

!> Version Swoole >= v4.5.7

```php
Swoole\Coroutine\Socket->writeVector(array $io_vector, float $timeout = 0): int|false
```

  * **Paramètres** 

    * **`array $io_vector`**
      * **Fonction** : Données segmentées à envoyer
      * **Unité de valeur** : Byte
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

    * **`float $timeout`**
      * **Fonction** :设置了imeout
      * **Unité de valeur** : seconde [Prend en charge les types flottants, comme `1.5` signifie `1s`+`500ms`]
      * **Valeur par défaut** : Referer [Règles de timeout client](/coroutine_client/init?id=règles de timeout)
      * **Autres valeurs** : Aucun

  * **Valeur de retour**

    * Return le nombre de bytes écrits avec succès, **veuillez noter que la données réellement écrites peuvent être inférieure à la longueur totale du `$io_vector` paramètre**, le code de l'application doit comparer la valeur de retour avec la longueur totale du `$io_vector` paramètre pour déterminer si la transmission est terminée
    * Return `false` en cas d'échec de la transmission, et设置了`errCode` propriété

  * **Exemple** 

```php
$socket = new Swoole\Coroutine\Socket(AF_INET, SOCK_STREAM, 0);
// En ce moment, les données sont envoyées à l'autre partie dans l'ordre de l'array, c'est-à-dire envoyer "helloworld"
$socket->writeVector(['hello', 'world']);
```


### writeVectorAll()

Envoyer des données à l'autre partie. Contrairement à la méthode `writeVector`, `writeVectorAll` enverra les données de manière尽可能 complète, jusqu'à ce qu'il soit possible d'envoyer toutes les données avec succès ou qu'une erreur survienne et arrête la transmission.

!> La méthode `writeVectorAll` exécute immédiatement plusieurs appels de system call `writev` pour envoyer les données. Lorsque la system call `writev` retourne une erreur `EAGAIN`, le niveau inférieur surveille automatiquement les événements écrits et suspend la coroutine actuelle. Lorsque l'événement écrit se produit, la system call `writev` est réexécutée pour envoyer les données, jusqu'à ce que la transmission des données soit terminée ou qu'une erreur survienne, et la coroutine correspondante est réveillée.

!> Version Swoole >= v4.5.7

```php
Swoole\Coroutine\Socket->writeVectorAll(array $io_vector, float $timeout = 0): int|false
```

  * **Paramètres** 

    * **`array $io_vector`**
      * **Fonction** : Données segmentées à envoyer
      * **Unité de valeur** : Byte
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

    * **`float $timeout`**
      * **Fonction** :设置了imeout
      * **Unité de valeur** : seconde [Prend en charge les types flottants, comme `1.5` signifie `1s`+`500ms`]
      * **Valeur par défaut** : Referer [Règles de timeout client](/coroutine_client/init?id=règles de timeout)
      * **Autres valeurs** : Aucun

  * **Valeur de retour**

    * `writeVectorAll` garantit que les données sont envoyées avec succès en totalité, mais pendant la période de `writeVectorAll`, il est possible que l'autre partie ferme la connexion, à ce moment-là, il est possible qu'une partie des données soit envoyée avec succès, la valeur de retour retourne la longueur de cette donnée réussie, le code de l'application doit comparer la valeur de retour avec la longueur totale du `$io_vector` paramètre pour déterminer si la transmission est terminée, et si la transmission doit être continuée en fonction des besoins métiers.
    * Return `false` en cas d'échec de la transmission, et设置了`errCode` propriété

  * **Exemple** 

```php
$socket = new Swoole\Coroutine\Socket(AF_INET, SOCK_STREAM, 0);
// En ce moment, les données sont envoyées à l'autre partie dans l'ordre de l'array, c'est-à-dire envoyer "helloworld"
$socket->writeVectorAll(['hello', 'world']);
```


### recvPacket()

Pour les objets Socket qui ont déjà établi un protocole avec la méthode `setProtocol`, cette méthode peut être utilisée pour recevoir un paquet de données complet du protocole.

!> Version Swoole >= v4.4.0

```php
Swoole\Coroutine\Socket->recvPacket(float $timeout = 0): string|false
```

  * **Paramètres** 
    * **`float $timeout`**
      * **Fonction** :设置了imeout
      * **Unité de valeur** : seconde [Prend en charge les types flottants, comme `1.5` signifie `1s`+`500ms`]
      * **Valeur par défaut** : Referer [Règles de timeout client](/coroutine_client/init?id=règles de timeout)
      * **Autres valeurs** : Aucun

  * **Valeur de retour** 

    * Return un paquet de données complet du protocole en cas d'échec de la réception
    * Return `false` en cas d'échec de la réception, et设置了`errCode` propriété
    * Retour à l'échec de la réception en cas d'expiration du temps, avec l'erreur `ETIMEDOUT`


### recvLine()

Pour résoudre le problème de compatibilité avec la fonction [socket_read](https://www.php.net/manual/en/function.socket-read.php)

```php
Swoole\Coroutine\Socket->recvLine(int $length = 65535, float $timeout = 0): string|false
```


### recvWithBuffer()

Pour résoudre le problème de nombreux appels système générés par l'utilisation de `recv(1)` pour recevoir des bytes un par un

```php
Swoole\Coroutine\Socket->recvWithBuffer(int $length = 65535, float $timeout = 0): string|false
```


### recvfrom()

Réception de données et configuration de l'adresse et du port de la source. Utilisé pour les `socket` de type `SOCK_DGRAM`.

!> Cette méthode provoquera [gestion des coroutines](/coroutine?id=gestion des coroutines), le niveau inférieur suspendra immédiatement la coroutine actuelle et surveillera les événements lisibles. Lorsque l'événement lisible se produit, après avoir reçu les données, la méthode `recvfrom` est exécutée pour obtenir le paquet de données.

```php
Swoole\Coroutine\Socket->recvfrom(array &$peer, float $timeout = 0): string|false
```

* **Paramètres**

    * **`array $peer`**
        * **Fonction** : Adresse et port de la partie opposée, type de référence.【La fonction retourne avec succès un tableau qui comprend les éléments `address` et `port`】
        * **Valeur par défaut** : Aucun
        * **Autres valeurs** : Aucun

    * **`float $timeout`**
        * **Fonction** :设置了imeout
        * **Unité de valeur** : seconde [Prend en charge les types flottants, comme `1.5` signifie `1s`+`500ms`]
        * **Valeur par défaut** : Referer [Règles de timeout client](/coroutine_client/init?id=règles de timeout)
        * **Autres valeurs** : Aucun

* **Valeur de retour**

    * Return les données reçues avec succès, et设置了`$peer` en tant que tableau
    * Return `false` en cas d'échec de la réception, et设置了`errCode` propriété, sans modifier le contenu de `$peer`

* **Exemple**

```php
use Swoole\Coroutine;
use function Swoole\Coroutine\run;

run(function () {
    $socket = new Coroutine\Socket(AF_INET, SOCK_DGRAM, 0);
    $socket->bind('127.0.0.1', 9601);
    while (true) {
        $peer = null;
        $data = $socket->recvfrom($peer);
        echo "[Server] recvfrom[{$peer['address']}:{$peer['port']}] : $data\n";
        $socket->sendto($peer['address'], $peer['port'], "Swoole: $data");
    }
});
```


### sendto()

Envoi de données à une adresse et port spécifiques. Utilisé pour les `socket` de type `SOCK_DGRAM`.

!> Cette méthode n'a pas de [gestion des coroutines](/coroutine?id=gestion des coroutines), le niveau inférieur appellera immédiatement `sendto` pour envoyer les données au serveur cible. Cette méthode ne surveille pas l'écriture, la méthode `sendto` peut retourner `false` en raison du plein de la mémoire tampon, il est nécessaire de le gérer soi-même, ou d'utiliser la méthode `send`.

```php
Swoole\Coroutine\Socket->sendto(string $address, int $port, string $data): int|false
```

  * **Paramètres** 

    * **`string $address`**
      * **Fonction** : Adresse IP de la cible ou chemin du [unixSocket](/learn?id=qu'est-ce que IPC) 【La méthode `sendto` ne prend pas en charge les noms de domaine, lorsque `AF_INET` ou `AF_INET6` est utilisé, une adresse IP valide doit être fournie, sinon l'envoi échouera】
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

    * **`int $port`**
      * **Fonction** : Port de la cible 【Pour l'envoi de diffusion, cela peut être `0`】
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

    * **`string $data`**
      * **Fonction** : Données à envoyer 【Peut être du texte ou du contenu binaire, veuillez noter que la longueur maximale du paquet envoyé par `SOCK_DGRAM` est de `64K`】
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

  * **Valeur de retour** 

    * Return le nombre de bytes envoyés avec succès
    * Return `false` en cas d'échec de l'envoi, et设置了`errCode` propriété

  * **Exemple** 

```php
$socket = new Swoole\Coroutine\Socket(AF_INET, SOCK_DGRAM, 0);
$socket->sendto('127.0.0.1', 9601, 'Hello');
```
### getsockname()

Obtenir les informations d'adresse et de port du socket.

!> Cette méthode ne subit pas les coûts de [gestion des coroutines](/coroutine?id=gestion-des-coroutines).

```php
Swoole\Coroutine\Socket->getsockname(): array|false
```

  * **Valeur de retour** 

    * Si la fonction réussit, elle retourne un tableau contenant `address` et `port`
    * Si la fonction échoue, elle retourne `false` et définit la propriété `errCode`


### getpeername()

Obtenir les informations de l'adresse et du port de l'autre côté du `socket`, uniquement pour les `socket` de type `SOCK_STREAM` qui sont établis.

?> Cette méthode ne subit pas les coûts de [gestion des coroutines](/coroutine?id=gestion-des-coroutines).

```php
Swoole\Coroutine\Socket->getpeername(): array|false
```

  * **Valeur de retour** 

    * Si la fonction réussit, elle retourne un tableau contenant `address` et `port`
    * Si la fonction échoue, elle retourne `false` et définit la propriété `errCode`


### close()

Fermer le `Socket`.

!> Si un objet `Swoole\Coroutine\Socket` est détruit, la méthode `close` est automatiquement exécutée, cette méthode ne subit pas les coûts de [gestion des coroutines](/coroutine?id=gestion-des-coroutines).

```php
Swoole\Coroutine\Socket->close(): bool
```

  * **Valeur de retour** 

    * Si la fermeture réussit, elle retourne `true`
    * Si la fermeture échoue, elle retourne `false`
    

### isClosed()

Verifier si le `Socket` est déjà fermé.

```php
Swoole\Coroutine\Socket->isClosed(): bool
```

## Constantes

Équivalentes aux constantes fournies par l'extension `sockets`, sans entrer en conflit avec celle-ci

!> Les valeurs peuvent varier selon le système, les codes suivants ne doivent être utilisés que comme exemple et ne sont pas fiables

```php
define ('AF_UNIX', 1);
define ('AF_INET', 2);

/**
 * Disponible uniquement si la compilation inclut le support IPv6.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('AF_INET6', 10);
define ('SOCK_STREAM', 1);
define ('SOCK_DGRAM', 2);
define ('SOCK_RAW', 3);
define ('SOCK_SEQPACKET', 5);
define ('SOCK_RDM', 4);
define ('MSG_OOB', 1);
define ('MSG_WAITALL', 256);
define ('MSG_CTRUNC', 8);
define ('MSG_TRUNC', 32);
define ('MSG_PEEK', 2);
define ('MSG_DONTROUTE', 4);

/**
 * Non disponible sur les plateformes Windows.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('MSG_EOR', 128);

/**
 * Non disponible sur les plateformes Windows.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('MSG_EOF', 512);
define ('MSG_CONFIRM', 2048);
define ('MSG_ERRQUEUE', 8192);
define ('MSG_NOSIGNAL', 16384);
define ('MSG_DONTWAIT', 64);
define ('MSG_MORE', 32768);
define ('MSG_WAITFORONE', 65536);
define ('MSG_CMSG_CLOEXEC', 1073741824);
define ('SO_DEBUG', 1);
define ('SO_REUSEADDR', 2);

/**
 * Cette constante est uniquement disponible dans PHP 5.4.10 ou plus tard sur les plateformes qui
 * prennent en charge l'option de socket `SO_REUSEPORT` : cela inclut Mac OS X et FreeBSD, mais ne comprend pas Linux ou Windows.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SO_REUSEPORT', 15);
define ('SO_KEEPALIVE', 9);
define ('SO_DONTROUTE', 5);
define ('SO_LINGER', 13);
define ('SO_BROADCAST', 6);
define ('SO_OOBINLINE', 10);
define ('SO_SNDBUF', 7);
define ('SO_RCVBUF', 8);
define ('SO_SNDLOWAT', 19);
define ('SO_RCVLOWAT', 18);
define ('SO_SNDTIMEO', 21);
define ('SO_RCVTIMEO', 20);
define ('SO_TYPE', 3);
define ('SO_ERROR', 4);
define ('SO_BINDTODEVICE', 25);
define ('SOL_SOCKET', 1);
define ('SOMAXCONN', 128);

/**
 * Utilisé pour désactiver l'algorithme TCP Nagle.
 * Ajouté dans PHP 5.2.7.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('TCP_NODELAY', 1);
define ('PHP_NORMAL_READ', 1);
define ('PHP_BINARY_READ', 2);
define ('MCAST_JOIN_GROUP', 42);
define ('MCAST_LEAVE_GROUP', 45);
define ('MCAST_BLOCK_SOURCE', 43);
define ('MCAST_UNBLOCK_SOURCE', 44);
define ('MCAST_JOIN_SOURCE_GROUP', 46);
define ('MCAST_LEAVE_SOURCE_GROUP', 47);
define ('IP_MULTICAST_IF', 32);
define ('IP_MULTICAST_TTL', 33);
define ('IP_MULTICAST_LOOP', 34);
define ('IPV6_MULTICAST_IF', 17);
define ('IPV6_MULTICAST_HOPS', 18);
define ('IPV6_MULTICAST_LOOP', 19);
define ('IPV6_V6ONLY', 27);

/**
 * Opération non autorisée.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EPERM', 1);

/**
 * Aucun fichier ou dossier n'existe.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOENT', 2);

/**
 * Appel système interrompu.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EINTR', 4);

/**
 * Erreur d'I/O.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EIO', 5);

/**
 * Aucun dispositif ou adresse n'existe.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENXIO', 6);

/**
 * Argument de liste trop long.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_E2BIG', 7);

/**
 * Numéro de fichier incorrect.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EBADF', 9);

/**
 * Réessayez.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EAGAIN', 11);

/**
 * Mémoire épuisée.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOMEM', 12);

/**
 * Autorisation refusée.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EACCES', 13);

/**
 * Adresse incorrecte.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EFAULT', 14);

/**
 * Un bloc de dispositif est nécessaire.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOTBLK', 15);

/**
 * Dispositif ou ressource occupé.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EBUSY', 16);

/**
 * Fichier déjà existant.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EEXIST', 17);

/**
 * Lien entre dispositifs.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EXDEV', 18);

/**
 * Aucun dispositif n'existe.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENODEV', 19);

/**
 * Pas un répertoire.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOTDIR', 20);

/**
 * Est un répertoire.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EISDIR', 21);

/**
 * Argument invalide.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EINVAL', 22);

/**
 * Overflow du tableau de fichiers.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENFILE', 23);

/**
 * Trop de fichiers ouverts.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_EMFILE', 24);

/**
 * Pas un clavier.
 * @link http://php.net/manual/en/sockets.constants.php
 */
define ('SOCKET_ENOTTY', 25);

/**
* Pas assez d'espace sur le dispositif.
* @link http://php.net/manual/en/sockets.constants.php
*/
define ('SOCKET_ENOSPC', 28);

/**
* Seek illégal.
* @link http://php.net/manual/en/sockets.constants.php
*/
define ('SOCKET_ESPIPE', 29);

/**
* Système de fichiers en lecture seule.
* @link http://php.net/manual/en/sockets.constants.php
*/
define ('SOCKET_EROFS', 30);

/**
* Trop de liens.
* @link http://php.net/manual/en/sockets.constants.php
*/
define ('SOCKET_EMLINK', 31);

/**
* Pipe cassée.
* @link http://php.net/manual/en/sockets.constants.php
*/
define ('SOCKET_EPIPE', 32);

/**
* Nom de fichier trop long.
* @link http://php.net/manual/en/sockets.constants.php
*/
define ('SOCKET_ENAMETOOLONG', 36);

/**
* Pas assez de verrous disponibles.
* @link http://php.net/manual/en/sockets.constants.php
*/
define ('SOCKET_ENOLCK', 37);

/**
* Fonction non implémentée.
* @link http://php.net/manual/en/sockets.constants.php
*/
define ('SOCKET_ENOSYS', 38);

/**
* Répertoire non vide.
* @link http://php.net/manual/en/sockets.constants.php
*/
define ('SOCKET_ENOTEMPTY', 39);

/**
* Trop de liens symboliques rencontrés.
* @link http://php.net/manual/en/sockets.constants.php
*/
define ('SOCKET_ELOOP', 40);

/**
* Opération qui bloquerait.
* @link http://php.net/manual/en/sockets.constants.php
*/
define ('SOCKET_EWOULDBLOCK', 11);

/**
* Pas de message de la bonne type.
* @link http://php.net/manual/en/sockets.constants.php
*/
define ('SOCKET_ENOMSG', 42);

/**
* Identificateur supprimé.
* @link http://php.net/manual/en/sockets.constants.php
*/
define ('SOCKET_EIDRM', 43);

/**
* Numéro de canal hors plage.
* @link http://php.net/manual/en/sockets.constants.php
*/
define ('SOCKET_ECHRNG', 44);

/**
* Niveaux 2 non synchronisés.
* @link http://php.net/manual/en/sockets.constants.php
*/
define ('SOCKET_EL2NSYNC', 45);

/**
* Niveaux 3 arrêtés.
* @link http://php.net/manual/en/sockets.constants.php
*/
define ('SOCKET_EL3HLT', 46);

/**
* Niveaux 3 réinitialisés.
* @link http://php.net/manual/en/sockets.constants.php
*/
define ('SOCKET_EL3RST', 47);

/**
* Numéro de lien hors plage.
* @link http://php.net/manual/en/sockets.constants.php
*/
define ('SOCKET_ELNRNG', 48);

/**
* Driver de protocole non attaché.
* @link http://php.net/manual/en/sockets.constants.php
*/
define ('SOCKET_EUNATCH', 49);

/**
* Pas de structure CSI disponible.
* @link http://php.net/manual/en/sockets.constants.php
*/
define ('SOCKET_ENOCSI', 50);

/**
* Niveaux 2 arrêtés.
* @link http://php.net/manual/en/sockets.constants.php
*/
define ('SOCKET_EL2HLT', 51);

/**
* Échange invalide.
* @link http://php.net/manual/en/sockets.constants.php
*/
define ('SOCKET_EBADE', 52);

/**
* Description de demande de demande invalide.
* @link http://php.net/manual/en/sockets.constants.php
*/
define ('SOCKET_EBADR', 53);

/**
* Échange plein.
* @link http://php.net/manual/en/sockets.constants.php
*/
define ('SOCKET_EXFULL', 54);

/**
* Pas d'anode.
* @link http://php.net/manual/en/sockets.constants.php
*/
define ('SOCKET_ENOANO', 55);

/**
* Code de demande de demande invalide.
* @link http://php.net/manual/en/sockets.constants.php
*/
define ('SOCKET_EBADRQC', 56);

/**
*slot invalide.
* @link http://php.net/manual/en/sockets.constants.php
*/
define ('SOCKET_EBADSLT', 57);

/**
* Dispositif n'est pas un flux.
* @link http://php.net/manual/en/sockets.constants.php
*/
define ('SOCKET_ENOSTR', 60);

/**
* Pas de données disponibles.
* @link http://php.net/manual/en/sockets.constants.php
*/
define ('SOCKET_ENODATA', 61);

/**
* Chronomètre épuisé.
* @link http://php.net/manual/en/sockets.constants.php
*/
define ('SOCKET_ETIME', 62);

/**
* Pas assez de ressources de flux.
* @link http://php.net/manual/en/sockets.constants.php
*/
define ('SOCKET_ENOSR', 63);

/**
* Machine n'est pas sur le réseau.
* @link http://php.net/manual/en/sockets.constants.php
*/
define ('SOCKET_ENONET', 64);

/**
* L'objet est distant.
* @link http://php.net/manual/en/sockets.constants.php
*/
define ('SOCKET_EREMOTE', 66);

/**
* Le lien a été coupé.
* @link http://php.net/manual/en/sockets.constants.php
*/
define ('SOCKET_ENOLINK', 67);

/**
* Annonce d'erreur.
* @link http://php.net/manual/en/sockets.constants.php
*/
define ('SOCKET_EADV', 68);

/**
* Erreur de montage SRM.
* @link http://php.net/manual/en/sockets.constants.php
*/
define ('
