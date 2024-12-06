# Événement

L'extension `Swoole` offre également des interfaces pour manipuler directement le cycle d'événements `epoll/kqueue` de base. Il est possible d'ajouter des `socket` créés par d'autres extensions, des `socket` créés par l'extension `stream/socket` dans le code PHP, etc., au [EventLoop](/learn?id=什么是eventloop) de Swoole. Sinon, si un `$fd` tiers est une I/O synchrone, cela peut empêcher le EventLoop de Swoole d'exécuter, [voir exemple de référence](/learn?id=同步io转换成异步io).

!> Le module `Event` est assez bas niveau et constitue une encapsulation primaire de `epoll`. Les utilisateurs doivent avoir une expérience en programmation de la multiplexage des I/O.


## Priorité des événements

1.回调 fonction de traitement des signaux définie par `Process::signal`
2.回调 fonction de tick et après timeout définie par `Timer::tick` et `Timer::after`
3.Fonction d'exécution différée définie par `Event::defer`
4.Callback fonction de cycle défini par `Event::cycle`


## Méthodes


### add()

Ajoute un `socket` au gestionnaire d'événements du réacteur de base. Cette fonction peut être utilisée en mode `Server` ou `Client`.
```php
Swoole\Event::add(mixed $sock, callable $read_callback, callable $write_callback = null, int $flags = null): bool
```

!> Lors de son utilisation dans un programme `Server`, il est essentiel de l'utiliser après le démarrage des processus `Worker`. Aucun appel à une interface I/O asynchrone ne doit être fait avant le démarrage du `Server::start`.

* **Paramètres** 

  * **`mixed $sock`**
    * **Fonctionnalité** : Descripteur de fichier, ressource `stream`, ressource `sockets`, objet
    * **Valeur par défaut** : None
    * **Autres valeurs** : None

  * **`callable $read_callback`**
    * **Fonctionnalité** : Callback fonction pour les événements lisibles
    * **Valeur par défaut** : None
    * **Autres valeurs** : None

  * **`callable $write_callback`**
    * **Fonctionnalité** : Callback fonction pour les événements écrits【Ce paramètre peut être un nom de fonction de chaîne, un objet+méthode, une méthode statique d'une classe ou une fonction anonyme. La fonction spécifiée est appelée lorsque ce `socket` est lisible ou écritable.】
    * **Valeur par défaut** : None
    * **Autres valeurs** : None

  * **`int $flags`**
    * **Fonctionnalité** : Masque des types d'événements【Il est possible d'activer/désactiver la surveillance des événements lisibles et écrits, comme `SWOOLE_EVENT_READ`, `SWOOLE_EVENT_WRITE` ou `SWOOLE_EVENT_READ|SWOOLE_EVENT_WRITE`】
    * **Valeur par défaut** : None
    * **Autres valeurs** : None

* **Les 4 types de `$sock`**


Type | Description
---|---
int | Descripteur de fichier, y compris `Swoole\Client->$sock`, `Swoole\Process->$pipe` ou autre `$fd`
ressource stream | Ressource créée par `stream_socket_client`/`fsockopen`
ressource sockets | Ressource créée par `socket_create` de l'extension `sockets`, nécessitant l'ajout de [./configure --enable-sockets](/environment?id=编译选项) lors de la compilation
objet | `Swoole\Process` ou `Swoole\Client`,底层将自动转换为[UnixSocket](/learn?id=什么是IPC)（`Process`）ou le `socket` de la connexion client（`Swoole\Client`）

* **Valeur de retour**

  * Le succès de l'ajout de surveillance des événements retourne `true`
  * Échec de l'ajout de surveillance des événements retourne `false`, veuillez utiliser `swoole_last_error` pour obtenir le code d'erreur
  * Un `socket` déjà ajouté ne peut pas être ajouté à nouveau, vous pouvez utiliser `swoole_event_set` pour modifier la fonction de rappel et le type d'événement correspondant au `socket`

  !> Lors de l'utilisation de `Swoole\Event::add` pour ajouter un `socket` à la surveillance des événements, le底层automatiquement met ce `socket` en mode non bloquant

* **Exemple d'utilisation**

```php
$fp = stream_socket_client("tcp://www.qq.com:80", $errno, $errstr, 30);
fwrite($fp,"GET / HTTP/1.1\r\nHost: www.qq.com\r\n\r\n");

Swoole\Event::add($fp, function($fp) {
    $resp = fread($fp, 8192);
    // Après traitement du socket, retirez le socket de l'événement epoll
    Swoole\Event::del($fp);
    fclose($fp);
});
echo "Fin\n";  // L'appel à Swoole\Event::add ne bloque pas le processus, cette ligne de code s'exécute de manière séquentielle
```

* **Callback fonction**

  * Dans la fonction de rappel pour les événements lisibles ($read_callback), il est essentiel d'utiliser des fonctions telles que `fread`, `recv` pour lire les données du tampon du `socket`, sinon l'événement continuera de se déclencher. Si vous ne souhaitez plus continuer à lire, vous devez utiliser `Swoole\Event::del` pour retirer la surveillance de l'événement.
  * Dans la fonction de rappel pour les événements écrits ($write_callback), après avoir écrit dans le `socket`, vous devez appeler `Swoole\Event::del` pour retirer la surveillance de l'événement, sinon l'événement d'écriture continuera de se déclencher.
  * L'exécution de `fread`, `socket_recv`, `socket_read`, `Swoole\Client::recv` retournant `false` et avec un code d'erreur `EAGAIN` indique qu'il n'y a actuellement aucune donnée dans le tampon de réception du `socket`. Dans ce cas, il est nécessaire d'ajouter une surveillance pour les événements lisibles en attendant la notification du [EventLoop](/learn?id=什么是eventloop).
  * L'exécution de `fwrite`, `socket_write`, `socket_send`, `Swoole\Client::send` retournant `false` et avec un code d'erreur `EAGAIN` indique que le tampon de transmission du `socket` est plein et qu'il n'est pas possible d'envoyer de données pour le moment. Il est nécessaire de surveiller les événements d'écriture en attendant la notification du [EventLoop](/learn?id=什么是eventloop).


### set()

Modifie la fonction de rappel et le masque d'événements de la surveillance des événements.

```php
Swoole\Event::set($fd, mixed $read_callback, mixed $write_callback, int $flags): bool
```

* **Paramètres** 

  * Les paramètres sont exactement les mêmes que ceux de [Event::add](/event?id=add). Si le `$fd` fourni n'existe pas dans le [EventLoop](/learn?id=什么是eventloop), il retourne `false`.
  * Lorsque `$read_callback` n'est pas `null`, la fonction de rappel pour les événements lisibles est modifiée pour la fonction spécifiée.
  * Lorsque `$write_callback` n'est pas `null`, la fonction de rappel pour les événements écrits est modifiée pour la fonction spécifiée.
  * `$flags` peut être activé/désactivé, pour surveiller les événements d'écriture ( `SWOOLE_EVENT_READ` ) et de lecture ( `SWOOLE_EVENT_WRITE` ).  

  !> Notez que si vous surveillez l'événement de lecture `SWOOLE_EVENT_READ`, mais que vous n'avez pas défini de `$read_callback`, le底层 ne fera que conserver les informations de la fonction de rappel et ne déclenchera aucun événement de rappel.
  * Vous pouvez utiliser `Event::set($fd, null, null, SWOOLE_EVENT_READ | SWOOLE_EVENT_WRITE)`, pour modifier les types d'événements surveillés, dans ce cas, le底层 déclenchera un événement de lecture.

* **Changement d'état**

  * Lorsqu'un événement de lecture est surveillé avec `Event::add` ou `Event::set`, mais que le `$read_callback` n'est pas défini, le底层 ne créera aucun événement de rappel et ne fera que conserver les informations de la fonction de rappel.
  * Vous pouvez utiliser `Event::set($fd, null, null, SWOOLE_EVENT_READ | SWOOLE_EVENT_WRITE)`, pour modifier les types d'événements surveillés, dans ce cas, le底层 déclenchera un événement de lecture.

* **Libération des fonctions de rappel**

!> Notez que `Event::set` ne peut remplacer la fonction de rappel, mais ne peut pas libérer la fonction de rappel d'événement. Par exemple : `Event::set($fd, null, null, SWOOLE_EVENT_READ | SWOOLE_EVENT_WRITE)`, les paramètres `$read_callback` et `$write_callback` fournis sont `null`, ce qui signifie que cela ne modifie pas la fonction de rappel définie par `Event::add`, et non que la fonction de rappel d'événement est mise à `null`.

Seule l'appel à `Event::del` pour éliminer la surveillance de l'événement libérera les fonctions de rappel `$read_callback` et `$write_callback`.


### isset()

Vérifie si le `$fd` fourni a été ajouté à la surveillance des événements.

```php
Swoole\Event::isset(mixed $fd, int $events = SWOOLE_EVENT_READ | SWOOLE_EVENT_WRITE): bool
```

* **Paramètres** 

  * **`mixed $fd`**
    * **Fonctionnalité** : Un descripteur de socket quelconque【Veuillez consulter la documentation de [Event::add](/event?id=add)】
    * **Valeur par défaut** : None
    * **Autres valeurs** : None

  * **`int $events`**
    * **Fonctionnalité** : Type d'événement à vérifier
    * **Valeur par défaut** : None
    * **Autres valeurs** : None

* **$events**
Type d'événement | Description
---|---
`SWOOLE_EVENT_READ` | Écoutez-vous les événements lisibles
`SWOOLE_EVENT_WRITE` | Écoutez-vous les événements écrits
`SWOOLE_EVENT_READ \| SWOOLE_EVENT_WRITE` | Écoutez les événements lisibles ou écrits

* **Exemple d'utilisation**

```php
use Swoole\Event;

$fp = stream_socket_client("tcp://www.qq.com:80", $errno, $errstr, 30);
fwrite($fp,"GET / HTTP/1.1\r\nHost: www.qq.com\r\n\r\n");

Event::add($fp, function($fp) {
    $resp = fread($fp, 8192);
    Swoole\Event::del($fp);
    fclose($fp);
}, null, SWOOLE_EVENT_READ);
var_dump(Event::isset($fp, SWOOLE_EVENT_READ)); //retourne true
var_dump(Event::isset($fp, SWOOLE_EVENT_WRITE)); //retourne false
var_dump(Event::isset($fp, SWOOLE_EVENT_READ | SWOOLE_EVENT_WRITE)); //retourne true
```


### write()

Utilisé pour les sockets créés par l'extension `stream/sockets` fournie par PHP, permet d'envoyer des données à l'autre partie en utilisant des fonctions telles que `fwrite/socket_send`. Lorsque la quantité de données à envoyer est grande et que le tampon d'écriture du socket est plein, cela peut entraîner un blocage en attente ou retourner une erreur [EAGAIN](/other/errno?id=linux).

La fonction `Event::write` peut transformer l'envoi de données de la ressource `stream/sockets` en **asynchrone**. Lorsque le tampon est plein ou que [EAGAIN](/other/errno?id=linux) est retourné, le niveau sous-jacent de Swoole ajoute les données à la file d'attente d'envoi et écoute l'écriture. Lorsque le socket est prêt à être écrit, le niveau sous-jacent de Swoole écrit automatiquement

```php
Swoole\Event::write(mixed $fd, miexd $data): bool
```

* **Paramètres** 

  * **`mixed $fd`**
    * **Fonctionnalité** : Descripteur de fichier de socket quelconque 【Référez-vous à la documentation sur [Event::add](/event?id=add)]】
    * **Valeur par défaut** : None
    * **Autres valeurs** : None

  * **`miexd $data`**
    * **Fonctionnalité** : Les données à envoyer 【La longueur des données à envoyer ne doit pas dépasser la taille du tampon du `Socket`】
    * **Valeur par défaut** : None
    * **Autres valeurs** : None

!> `Event::write` ne peut pas être utilisé pour les ressources de `stream/sockets` avec une encryption de tunnel telles que `SSL/TLS`  
Après un succès d'opération `Event::write`, le `$socket` est automatiquement mis en mode non bloquant

* **Exemple d'utilisation**

```php
use Swoole\Event;

$fp = stream_socket_client('tcp://127.0.0.1:9501');
$data = str_repeat('A', 1024 * 1024*2);

Event::add($fp, function($fp) {
     echo fread($fp);
});

Event::write($fp, $data);
```

#### Logique de base de Swoole après le tampon du SOCKET est plein

Si l'écriture continue dans le `SOCKET` et que la lecture de l'autre partie n'est pas rapide enough, alors le tampon du `SOCKET` sera plein. Le niveau sous-jacent de Swoole stockera les données dans un tampon de mémoire jusqu'à ce qu'un événement d'écriture puisse se produire pour écrire dans le `SOCKET`.

Si le tampon de mémoire est également plein, à ce moment-là, le niveau sous-jacent de Swoole lancera une erreur `pipe buffer overflow, reactor will block.` et entrera dans un blocage en attente.

!> Le retour de `false` en cas de remplissage du tampon est une opération atomique, se produisant uniquement en cas d'écriture réussie complète ou d'échec complet


### del()

Retirez le socket surveillé du `reactor`. `Event::del` doit être utilisé en couple avec `Event::add`.

```php
Swoole\Event::del(mixed $sock): bool
```

!> Il faut utiliser `Event::del` pour retirer l'écoute de l'événement avant d'effectuer l'opération de fermeture du `socket`, sinon cela pourrait entraîner un débordement de mémoire.

* **Paramètres** 

  * **`mixed $sock`**
    * **Fonctionnalité** : Descripteur de fichier de socket
    * **Valeur par défaut** : None
    * **Autres valeurs** : None


### exit()

Quitte le tour d'événement.

!> Cette fonction est uniquement valide dans les programmes `Client`

```php
Swoole\Event::exit(): void
```


### defer()

Exécutez la fonction au début du prochain tour d'événements. 

```php
Swoole\Event::defer(mixed $callback_function);
```

!> La fonction de rappel spécifiée par `Event::defer` sera exécutée à la fin du tour d'événements actuel et avant le début du prochain tour d'événements.

* **Paramètres** 

  * **`mixed $callback_function`**
    * **Fonctionnalité** : La fonction à exécuter après l'expiration du temps
    * **Valeur par défaut** : None
    * **Autres valeurs** : None

  * **`bool $before`**
    * **Fonctionnalité** : Exécuter la fonction avant le [EventLoop](/learn?id=什么是eventloop)
    * **Valeur par défaut** : None
    * **Autres valeurs** : None

!> Il est possible d'avoir à la fois des fonctions de rappel `before=true` et `before=false`.

  * **Exemple d'utilisation**

```php
Swoole\Timer::tick(2000, function ($id) {
    var_dump($id);
});

Swoole\Event::cycle(function () {
    echo "hello [1]\n";
    Swoole\Event::cycle(function () {
        echo "hello [2]\n";
        Swoole\Event::cycle(null);
    });
});
```


### cycle()

Définissez une fonction d'exécution périodique pour le tour d'événements. Cette fonction sera appelée à la fin de chaque tour d'événements. 

```php
Swoole\Event::cycle(callable $callback, bool $before = false): bool
```

* **Paramètres** 

  * **`callable $callback_function`**
    * **Fonctionnalité** : La fonction de rappel à configurer 【Si `$callback` est `null`, cela signifie effacer la fonction `cycle`, si la fonction `cycle` est déjà définie, la nouvelle définition remplacera la précédente】
    * **Valeur par défaut** : None
    * **Autres valeurs** : None

  * **`bool $before`**
    * **Fonctionnalité** : Exécuter la fonction avant le [EventLoop](/learn?id=什么是eventloop)
    * **Valeur par défaut** : None
    * **Autres valeurs** : None

!> Il est possible d'avoir à la fois des fonctions de rappel `before=true` et `before=false`.

  * **Exemple d'utilisation**

```php
Swoole\Timer::tick(1000, function () {
    echo "hello\n";
});

Swoole\Event::cycle(function () {
    echo "hello [1]\n";
    Swoole\Event::cycle(function () {
        echo "hello [2]\n";
        Swoole\Event::cycle(null);
    });
});
```


### wait()

Démarrez l'écoute des événements.

!> Veuillez placer cette fonction à la fin de votre programme PHP

```php
Swoole\Event::wait();
```

* **Exemple d'utilisation**

```php
Swoole\Timer::tick(1000, function () {
    echo "hello\n";
});

Swoole\Event::wait();
```

### dispatch()

Démarrez l'écoute des événements.

!> Exécutez une seule fois l'opération `reactor->wait`, ce qui est similaire à une invocation manuelle de `epoll_wait` sur les plateformes `Linux`. Contrairement à `Event::wait`, `Event::dispatch` maintient un cycle en dessous du niveau sous-jacent.

```php
Swoole\Event::dispatch();
```

* **Exemple d'utilisation**

```php
while(true)
{
    Event::dispatch();
}
```

Le but de cette fonction est de compatibilité avec certains cadres, comme `amp`, qui contrôlent eux-mêmes le cycle du `reactor` à l'intérieur du cadre, tandis que l'utilisation de `Event::wait` fait que le niveau sous-jacent de Swoole maintient le contrôle et ne peut pas céder le contrôle au côté du cadre.
