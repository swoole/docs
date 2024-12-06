# Swoole\Client

`Swoole\Client`, également appelé `Client`, fournit une encapsulation des clients pour `TCP/UDP/UnixSocket`. Il suffit d'utiliser `new Swoole\Client` pour l'utiliser. Il peut être utilisé dans un environnement `FPM/Apache`.

Par rapport aux fonctions traditionnelles de la série [streams](https://www.php.net/streams), il y a plusieurs avantages :

  * La fonction `stream` a une durée de temps par défaut de timeout assez longue, et si la réponse de l'autre partie est trop longue, cela peut entraîner un blocage de longue durée
  * La fonction `stream` a une taille par défaut de tampon de lecture de `8192`, ce qui ne prend pas en charge les gros paquets `UDP`
  * Le `Client` prend en charge `waitall`, ce qui permet de prendre en une fois tout le paquet lorsqu'il y a une longueur de paquet déterminée, sans avoir à lire en boucle
  * Le `Client` prend en charge `UDP Connect`, ce qui résout le problème des paquets dispersés `UDP`
  * Le `Client` est écrit en code pur `C`, spécialement pour gérer les `sockets`, la fonction `stream` est très complexe. Le `Client` a une meilleure performance
  * Il est possible d'utiliser la fonction [swoole_client_select](/client?id=swoole_client_select) pour réaliser le contrôle Concurrency de plusieurs `Clients`


### Exemple complet

```php
$client = new Swoole\Client(SWOOLE_SOCK_TCP);
if (!$client->connect('127.0.0.1', 9501, -1)) {
    exit("connect failed. Error: {$client->errCode}\n");
}
$client->send("hello world\n");
echo $client->recv();
$client->close();
```


## Méthodes


### __construct()

Constructeur

```php
Swoole\Client::__construct(int $sock_type, bool $is_sync = false, string $key);
```

* **Paramètres** 

  * **`int $sock_type`**
    * **Fonction** : Indique le type de `socket`【Pris en charge `SWOOLE_SOCK_TCP`, `SWOOLE_SOCK_TCP6`, `SWOOLE_SOCK_UDP`, `SWOOLE_SOCK_UDP6`】Pour la signification spécifique, veuillez consulter [cette section](/server/methods?id=__construct)
    * **Valeur par défaut** : Aucun
    * **Autres valeurs** : Aucun

  * **`bool $is_sync`**
    * **Fonction** : Mode de blocage synchrone, il ne peut être établi que sur `false`. Si vous souhaitez utiliser le mode de callback asynchrone, veuillez utiliser `Swoole\Async\Client`
    * **Valeur par défaut** : `false`
    * **Autres valeurs** : Aucun

  * **`string $key`**
    * **Fonction** : Utilisé pour la `Key` des connexions longues 【Utilise par défaut `IP:PORT` comme `key`. Même avec la même `key`, même si on crée deux fois, il n'y aura qu'une seule connexion TCP】
    * **Valeur par défaut** : `IP:PORT`
    * **Autres valeurs** : Aucun

!> Il est possible d'utiliser les macros fournies en bas pour spécifier le type, veuillez consulter [définition des constantes](/consts)

#### Création de connexions longues dans PHP-FPM/Apache

```php
$cli = new Swoole\Client(SWOOLE_SOCK_TCP | SWOOLE_KEEP);
```

Après avoir ajouté le drapeau [SWOOLE_KEEP](/client?id=swoole_keep), la connexion TCP créée ne sera pas fermée lorsque la demande PHP se termine ou que la méthode `$cli->close()` est appelée. La prochaine fois que vous exécutez `connect`, elle réutilisera la connexion créée précédemment. La manière dont les connexions longues sont conservées est par défaut avec `ServerHost:ServerPort` comme `key`. Vous pouvez spécifier le `key` dans le troisième paramètre.

L'objet `Client` détruit automatiquement le `socket` en appelant la méthode [close](/client?id=close) lors de sa destruction.

#### Utilisation du Client dans le Serveur

  * Le `Client` doit être utilisé dans les fonctions d'événement [callback](/server/events).
  * Le `Serveur` peut être connecté par un `socket client` écrit en n'importe quelle langue. De même, le `Client` peut se connecter à un `socket serveur` écrit en n'importe quelle langue.

!> L'utilisation de ce `Client` dans un environnement de coroutines Swoole4+ entraînera un recul vers le modèle [synchrone](/learn?id=同步io异步io).


### set()

Établir les paramètres du client, doivent être exécutés avant [connect](/client?id=connect).

```php
Swoole\Client->set(array $settings);
```

Les options de configuration disponibles sont référencées dans Client - [Options de configuration](/client?id=configuration)


### connect()

Connecter au serveur distant.

```php
Swoole\Client->connect(string $host, int $port, float $timeout = 0.5, int $sock_flag = 0): bool
```

* **Paramètres** 

  * **`string $host`**
    * **Fonction** : Adresse du serveur【Pris en charge la résolution asynchrone automatique de domaine, `$host` peut être directement saisi comme nom de domaine】
    * **Valeur par défaut** : Aucun
    * **Autres valeurs** : Aucun

  * **`int $port`**
    * **Fonction** : Port du serveur
    * **Valeur par défaut** : Aucun
    * **Autres valeurs** : Aucun

  * **`float $timeout`**
    * **Fonction** : Établir le temps de timeout
    * **Unité de valeur** : seconde【Pris en charge les nombres à virgule flottante, comme `1.5` signifie `1s`+`500ms`】
    * **Valeur par défaut** : `0.5`
    * **Autres valeurs** : Aucun

  * **`int $sock_flag`**
    - Dans le cas du type `UDP`, cela indique si l'on active la configuration `udp_connect`. Après avoir établi cette option, le `$host` et le `$port` seront liés, et ce `UDP` ignorera les paquets qui ne sont pas destinés à l'adresse spécifiée `/port`.
    - Dans le cas du type `TCP`, si `$sock_flag=1`, cela configure le socket en mode non bloquant, et ensuite ce fd deviendra [IO asynchrone](/learn?id=同步io异步io), la méthode `connect` retournera immédiatement. Si vous设置了 `$sock_flag` à `1`, vous devez d'abord utiliser la méthode [swoole_client_select](/client?id=swoole_client_select) pour vérifier si la connexion est terminée avant d'envoyer ou de recevoir des données.

* **Valeur de retour**

  * Succès, retourne `true`
  * Échec, retourne `false`, veuillez vérifier la propriété `errCode` pour obtenir la raison de l'échec

* **Mode synchrone**

La méthode `connect` bloquera jusqu'à ce que la connexion soit réussie et que `true` soit retourné. À ce moment-là, vous pourrez envoyer des données au serveur ou recevoir des données.

```php
if ($cli->connect('127.0.0.1', 9501)) {
      $cli->send("data");
} else {
      echo "connect failed.";
}
```

Si la connexion échoue, elle retournera `false`

> Un client TCP synchrone peut se reconnecter au serveur en exécutant à nouveau `Connect` après avoir appelé `close`.

* **Reconnexion après échec**

Si la connexion échoue et que vous souhaitez se reconnecter une fois, vous devez d'abord fermer le vieux `socket` avec `close`, sinon vous obtiendrez une erreur `EINPROCESS`, car le `socket` actuel est en train de se connecter au serveur, et le client ne sait pas si la connexion a réussi, donc il ne peut pas exécuter à nouveau `connect`. L'appel à `close` fermera le `socket` actuel, et le niveau inférieur créera un nouveau `socket` pour se connecter.

!> Après avoir activé la connexion longue [SWOOLE_KEEP](/client?id=swoole_keep), le premier argument de la méthode `close` doit être réglé sur `true` pour forcer la destruction du `socket` de connexion longue.

```php
if ($socket->connect('127.0.0.1', 9502) === false) {
    $socket->close(true);
    $socket->connect('127.0.0.1', 9502);
}
```

* **UDP Connect**

Par défaut, le niveau inférieur ne active pas `udp connect`. Lorsqu'un client `UDP` exécute `connect`, le niveau inférieur crée immédiatement un `socket` et retourne avec succès. À ce moment-là, l'adresse liée à ce `socket` est `0.0.0.0`, et tout autre machine peut envoyer des paquets à ce port.

Comme `$client->connect('192.168.1.100', 9502)`, à ce moment-là, l'opération système alloue au hasard un port de `58232` pour le client `socket`, et d'autres machines, comme `192.168.1.101`, peuvent également envoyer des paquets à ce port.

?> Si `udp connect` n'est pas activé, l'élément `host` retourné par `getsockname` est `0.0.0.0`

En mettant le quatrième argument à `1`, activez `udp connect`, `$client->connect('192.168.1.100', 9502, 1, 1)`. À ce moment-là, il lie le client et le serveur, et le niveau inférieur lie l'adresse du `socket` en fonction de l'adresse du serveur. Si vous avez connecté à `192.168.1.100`, le `socket` actuel sera lié à l'adresse locale de la plage `192.168.1.*`. Après avoir activé `udp connect`, le client ne recevra plus de paquets de n'autres machines envoyés à ce port.
### recv()

Récupérer des données du côté serveur.

```php
Swoole\Client->recv(int $size = 65535, int $flags = 0): string | false
```

* **Paramètres**

  * **`int $size`**
    * **Fonction** : Longueur maximale du tampon de stockage des données reçues【Ne pas configurer cette valeur trop grande, sinon cela occupera beaucoup de mémoire】
    * **Valeur par défaut** : None
    * **Autres valeurs** : None

  * **`int $flags`**
    * **Fonction** : Paramètres supplémentaires que l'on peut configurer【par exemple Client::MSG_WAITALL](/client?id=clientmsg_waitall), voir [cette section](/client?id=constantes) pour plus d'informations sur les paramètres
    * **Valeur par défaut** : None
    * **Autres valeurs** : None

* **Valeurs de retour**

  * Si les données sont reçues avec succès, retourne une chaîne de caractères
  * Si la connexion est fermée, retourne une chaîne vide
  * Si l'opération échoue, retourne `false` et définit la propriété `$client->errCode`

* **Protocole EOF/Longueur**

  * Après avoir activé la détection EOF/Longueur sur le côté client, il n'est pas nécessaire de configurer les paramètres `$size` et `$waitall`. La couche d'extension retournera un paquet de données complet ou retournera `false`, voir la section [analyse du protocole](/client?id=analyse_protocole).
  * Lorsque vous recevez une tête de paquet erronée ou que la valeur de la longueur dans la tête de paquet dépasse la configuration [package_max_length](/server/setting?id=package_max_length), `recv` retournera une chaîne vide, et le code PHP doit fermer cette connexion.


### send()

Envoyer des données à un serveur distant, il est nécessaire d'établir une connexion avant de pouvoir envoyer des données à l'autre partie.

```php
Swoole\Client->send(string $data): int|false
```

* **Paramètres**

  * **`string $data`**
    * **Fonction** : Contenu à envoyer【prenant en charge les données binaires】
    * **Valeur par défaut** : None
    * **Autres valeurs** : None

* **Valeurs de retour**

  * Si l'envoi est réussi, retourne la longueur des données envoyées
  * Si l'opération échoue, retourne `false` et définit la propriété `errCode`

* **Remarques**

  * Si `connect` n'est pas exécuté, appeler `send` déclenche une alerte
  * Il n'y a pas de restriction sur la longueur des données envoyées
  * Si les données envoyées sont trop grandes et remplissent le tampon deSocket, l'exécution du programme sera bloquée en attendant qu'il soit possible d'écrire


### sendfile()

Envoyer un fichier au serveur, cette fonction est basée sur l'appel système `sendfile`

```php
Swoole\Client->sendfile(string $filename, int $offset = 0, int $length = 0): bool
```

!> sendfile ne peut pas être utilisé pour les clients UDP et les connexions de tunnel SSL

* **Paramètres**

  * **`string $filename`**
    * **Fonction** : Chemin du fichier à envoyer
    * **Valeur par défaut** : None
    * **Autres valeurs** : None

  * **`int $offset`**
    * **Fonction** : Offsetting du fichier à envoyer【permet de commencer à transmettre des données à partir d'une partie spécifique du fichier. Cette caractéristique peut être utilisée pour soutenir le transfert en cours de route】
    * **Valeur par défaut** : None
    * **Autres valeurs** : None

  * **`int $length`**
    * **Fonction** : Taille des données à envoyer【par défaut, la taille du fichier entier】
    * **Valeur par défaut** : None
    * **Autres valeurs** : None

* **Valeurs de retour**

  * Si le fichier fourni n'existe pas, retourne `false`
  * Si l'exécution est réussie, retourne `true`

* **Remarques**

  * `sendfile` restera bloqué jusqu'à ce que le fichier entier soit envoyé ou qu'une erreur fatale se produise



### sendto()

Envoyer un paquet UDP à tout hôte `IP:PORT`, uniquement pris en charge pour les types `SWOOLE_SOCK_UDP/SWOOLE_SOCK_UDP6`

```php
Swoole\Client->sendto(string $ip, int $port, string $data): bool
```

* **Paramètres**

  * **`string $ip`**
    * **Fonction** : Adresse IP de l'hôte cible, prend en charge IPv4/IPv6
    * **Valeur par défaut** : None
    * **Autres valeurs** : None

  * **`int $port`**
    * **Fonction** : Port de l'hôte cible
    * **Valeur par défaut** : None
    * **Autres valeurs** : None

  * **`string $data`**
    * **Fonction** : Contenu des données à envoyer【ne doit pas dépasser 64K】
    * **Valeur par défaut** : None
    * **Autres valeurs** : None


### enableSSL()

Activer dynamiquement le tunnel de chiffrement SSL, cette fonction peut être utilisée uniquement si `swoole` a été compilé avec `--enable-openssl`.

```php
Swoole\Client->enableSSL(): bool
```

Après avoir établi une connexion, le client peut utiliser la méthode `enableSSL` pour passer à la communication chiffrée par SSL. Si la connexion est déjà chiffrée par SSL, veuillez consulter la section [Configuration SSL](/client?id=ssl_related). Pour activer dynamiquement le tunnel de chiffrement SSL avec `enableSSL`, deux conditions doivent être remplies :

  * Le type de création du client doit être non SSL
  * Le client doit déjà être connecté au serveur

L'appel à `enableSSL` bloquera jusqu'à ce que la mainmise SSL soit terminée.

* **Exemple**

```php
$client = new Swoole\Client(SWOOLE_SOCK_TCP);
if (!$client->connect('127.0.0.1', 9501, -1))
{
    exit("connect failed. Error: {$client->errCode}\n");
}
$client->send("hello world\n");
echo $client->recv();
//Activer le tunnel de chiffrement SSL
if ($client->enableSSL())
{
    //La mainmise SSL est terminée, les données envoyées et reçues à partir de maintenant sont chiffrées
    $client->send("hello world\n");
    echo $client->recv();
}
$client->close();
```



### getPeerCert()

Obtenir les informations du certificat du côté serveur, cette fonction peut être utilisée uniquement si `swoole` a été compilé avec `--enable-openssl`.

```php
Swoole\Client->getPeerCert(): string|false
```

* **Valeurs de retour**

  * Si l'opération est réussie, retourne une chaîne de caractères décrivant le certificat X509
  * Si l'opération échoue, retourne `false`

!> Cette méthode ne peut être appelée qu'après la fin de la mainmise SSL.
  
Il est possible d'analyser les informations du certificat en utilisant la fonction `openssl_x509_parse` fournie par l'extension OpenSSL.

!> Il est nécessaire d'activer `--enable-openssl` lors de la compilation de swoole.


### verifyPeerCert()

Vérifier le certificat du côté serveur, cette fonction peut être utilisée uniquement si `swoole` a été compilé avec `--enable-openssl`.

```php
Swoole\Client->verifyPeerCert()
```


### isConnected()

Retourner l'état de connexion du Client

* Retourne `false`, cela indique qu'il n'est actuellement pas connecté au serveur
* Retourne `true`, cela indique qu'il est actuellement connecté au serveur

```php
Swoole\Client->isConnected(): bool
```

!> La méthode `isConnected` retourne l'état à l'application, elle indique simplement que le `Client` a exécuté `connect` et s'est connecté avec succès au `Server`, et n'a pas appelé `close` pour fermer la connexion. Le `Client` peut exécuter des opérations telles que `send`, `recv`, `close`, etc., mais ne peut pas exécuter à nouveau `connect`.
Cela ne signifie pas nécessairement que la connexion est utilisable, il est toujours possible de recevoir une erreur lors de l'exécution de `send` ou `recv`, car l'application ne peut pas obtenir l'état réel de la connexion TCP sous-jacente, et l'application doit interagir avec le noyau pour obtenir l'état réel de la connexion utilisable lors de l'exécution de `send` ou `recv`.


### getSockName()

Utilisé pour obtenir l'adresse locale host:port du socket client.

!> Il est nécessaire d'être connecté avant d'utiliser cette méthode

```php
Swoole\Client->getsockname(): array|false
```

* **Valeurs de retour**

```php
array('host' => '127.0.0.1', 'port' => 53652);
```


### getPeerName()

Obtenir l'adresse IP et le port de l'autre côté du socket

!> Seulement pris en charge pour les types `SWOOLE_SOCK_UDP/SWOOLE_SOCK_UDP6/SWOOLE_SOCK_UNIX_DGRAM`

```php
Swoole\Client->getpeername(): array|false
```

Après que le client UDP a envoyé un paquet de données à un serveur, il est possible que ce serveur ne soit pas celui qui répond au client. Il est possible d'utiliser la méthode `getpeername` pour obtenir l'adresse IP et le port du serveur réel qui répond.

!> Cette fonction doit être appelée après avoir reçu `$client->recv()`

### fermer()

Ferme la connexion.

```php
Swoole\Client->close(bool $force = false): bool
```

* **Paramètres**

  * **`bool $force`**
    * **Fonction** : Forcer la fermeture de la connexion [peut être utilisé pour fermer une connexion longue [SWOOLE_KEEP](/client?id=swoole_keep)]
    * **Valeur par défaut** : None
    * **Autres valeurs** : None

Après avoir appelé `close` sur une connexion `swoole_client`, ne tentez pas de发起 à nouveau une `connect`. La bonne pratique est de détruire le `Client` actuel et de créer un nouveau `Client` pour établir une nouvelle connexion.

L'objet `Client` se ferme automatiquement lors de sa destruction.


### shutdown()

Ferme le client

```php
Swoole\Client->shutdown(int $how): bool
```

* **Paramètres**

  * **`int $how`**
    * **Fonction** : Établir comment fermer le client
    * **Valeur par défaut** : None
    * **Autres valeurs** : Swoole\Client::SHUT_RDWR (fermer la lecture et l'écriture), SHUT_RD (fermer la lecture), Swoole\Client::SHUT_WR (fermer l'écriture)


### getSocket()

Obtient le handle du socket sous-jacent, l'objet retourné est un handle de ressource de socket.

!> Cette méthode nécessite la dépendance de l'extension `sockets` et doit avoir été compilée avec l'option [--enable-sockets](/environment?id=compilation_options) activée

```php
Swoole\Client->getSocket()
```

Utilisez la fonction `socket_set_option` pour configurer certains paramètres de socket plus bas.

```php
$socket = $client->getSocket();
if (!socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1)) {
    echo 'Impossible de configurer l\'option sur le socket: '. socket_strerror(socket_last_error()) . PHP_EOL;
}
```


### swoole_client_select

L'utilisation de la fonction `swoole_client_select` pour le traitement parallèle de `Swoole\Client` implique l'appel à la fonction système `select` pour l'[itération des événements](/learn?id=what-is-eventloop), et non `epoll_wait`. Contrairement au [Module Event](/event), cette fonction est utilisée dans un environnement d'IO synchrone (si elle est appelée dans un processus Worker de Swoole, cela empêchera l'exécution de l'itération des événements epoll de Swoole propre).

La signature de la fonction est :

```php
int swoole_client_select(array &$read, array &$write, array &$error, float $timeout);
```

* `swoole_client_select` accepte quatre paramètres : `$read`, `$write`, `$error` qui sont respectivement les descripteurs de fichiers pour la lecture/l'écriture/l'erreur.  
* Ces trois paramètres doivent être des références à des tableaux. Les éléments de ces tableaux doivent être des objets `swoole_client`.
* Cette méthode est basée sur la fonction système `select`, qui prend en charge jusqu'à `1024` sockets.
* Le paramètre `$timeout` est le temps d'attente pour la fonction système `select`, en secondes, et accepte un nombre à virgule flottante.
* La fonction est similaire à la fonction PHP native `stream_select()`, à la différence que `stream_select` ne prend en charge que le type de variable stream de PHP, et sa performance est médiocre.

Après un appel réussi, la fonction retourne le nombre d'événements et modifie les tableaux `$read`/`$write`/`$error`. Utilisez une boucle `foreach` pour itérer sur les tableaux, puis exécutez `$item->recv`/`$item->send` pour envoyer et recevoir des données. Ou appelez `$item->close()` ou `unset($item)` pour fermer le socket.

Le retour de `swoole_client_select` est `0` si, dans le délai imparti, aucun événement IO n'est disponible et que l'appel à `select` a expiré.

!> Cette fonction peut être utilisée dans un environnement `Apache/PHP-FPM`.    

```php
$clients = array();

for($i=0; $i< 20; $i++)
{
    $client = new Swoole\Client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC); //synchrone bloquant
    $ret = $client->connect('127.0.0.1', 9501, 0.5, 0);
    if(!$ret)
    {
        echo "Échec de la connexion au serveur. errCode=".$client->errCode;
    }
    else
    {
    	$client->send("HELLO WORLD\n");
    	$clients[$client->sock] = $client;
    }
}

while (!empty($clients))
{
    $write = $error = array();
    $read = array_values($clients);
    $n = swoole_client_select($read, $write, $error, 0.6);
    if ($n > 0)
    {
        foreach ($read as $index => $c)
        {
            echo "Réception #{$c->sock}: " . $c->recv() . "\n";
            unset($clients[$c->sock]);
        }
    }
}
```


## Propriétés


### errCode

Code d'erreur

```php
Swoole\Client->errCode: int
```

Lorsque `connect/send/recv/close` échoue, le valor de `$swoole_client->errCode` est automatiquement mis à jour.

Le code d'erreur correspond au `errno Linux`. Vous pouvez utiliser `socket_strerror` pour transformer le code d'erreur en message d'erreur.

```php
echo socket_strerror($client->errCode);
```

Pour référence : [Liste des codes d'erreur Linux](/other/errno?id=linux)


### sock

Déscription du descripteur de fichier du socket de connexion.

```php
Swoole\Client->sock;
```

Dans le code PHP, vous pouvez utiliser

```php
$sock = fopen("php://fd/".$swoole_client->sock); 
```

* Convertir le `socket` de `Swoole\Client` en un `socket de flux`. Vous pouvez utiliser les fonctions `fread/fwrite/fclose`, etc., pour effectuer des opérations de processus.

* Dans la [Swoole\Server](/server/methods?id=__construct), `$fd` ne peut pas être converti avec cette méthode, car `$fd` est simplement un nombre, et le descripteur de fichier `$fd` appartient au processus principal, voir la [mode SWOOLE_PROCESS](/learn?id=swoole_process).

* Le `$swoole_client->sock` peut être converti en un entier pour servir de clé dans un tableau.

!> Il est important de noter que la valeur de la propriété `$swoole_client->sock` ne peut être obtenue qu'après avoir établi une connexion avec le serveur. Avant une connexion avec le serveur, cette propriété a une valeur de `null`.


### reuse

Indique si cette connexion est nouvellement créée ou si elle réutilise une connexion existante. Utilisé conjointement avec [SWOOLE_KEEP](/client?id=swoole_keep).

#### Scénarios d'utilisation

Après avoir établi une connexion avec un serveur `WebSocket`, il est nécessaire de réaliser une main-d'œuvre si la connexion est réutilisée, il n'est pas nécessaire de réaliser à nouveau la main-d'œuvre, il suffit d'envoyer des trames de données `WebSocket`.

```php
if ($client->reuse) {
    $client->send($data);
} else {
    $client->doHandShake();
    $client->send($data);
}
```


### reuseCount

Indique le nombre de fois cette connexion a été réutilisée. Utilisé conjointement avec [SWOOLE_KEEP](/client?id=swoole_keep).

```php
Swoole\Client->reuseCount;
```


### type

Indique le type de `socket`, et retourne la valeur de `$sock_type` dans la construction de `Swoole\Client::__construct()`

```php
Swoole\Client->type;
```


### id

Retourne la valeur de `$key` dans la construction de `Swoole\Client::__construct()`, utilisé conjointement avec [SWOOLE_KEEP](/client?id=swoole_keep)

```php
Swoole\Client->id;
```


### setting

Retourne les paramètres de configuration settés par `Swoole\Client::set()`

```php
Swoole\Client->setting;
```


## Constantes


### SWOOLE_KEEP

`Swoole\Client` prend en charge la création d'une connexion TCP longue vers le serveur dans un environnement `PHP-FPM/Apache`. Utilisation :

```php
$client = new Swoole\Client(SWOOLE_SOCK_TCP | SWOOLE_KEEP);
$client->connect('127.0.0.1', 9501);
```

Lorsque l'option `SWOOLE_KEEP` est activée, la connexion ne sera pas fermée après la fin d'une demande, et la prochaine fois que vous effectuez une `connect`, elle réutilisera la connexion créée précédemment. Si la `connect` échoue parce que la connexion a déjà été fermée par le serveur, alors une nouvelle connexion sera créée.

?> Avantages de SWOOLE_KEEP

* La connexion TCP longue peut réduire les consommations supplémentaires d'IO dues aux trois mains-choses de la `connect` et des quatre mains-choses de la `close`.
* Réduire le nombre de fermetures de connexion et d'établissements de connexion côté serveur.


### Swoole\Client::MSG_WAITALL

  * Si l'option Client::MSG_WAITALL est définie, il faut préciser la valeur précise de `$size`, sinon l'attente continuera indéfiniment jusqu'à ce que la longueur du données reçues atteigne `$size`
  * Lorsque Client::MSG_WAITALL n'est pas définie, la valeur maximale de `$size` est de `64K`
  * Si une valeur erronée de `$size` est définie, cela entraînera un timeout de la réception et retournera `false`
### Swoole\Client::MSG_DONTWAIT

Réception non bloquante des données, qui retourne immédiatement quel que soit le cas.

### Swoole\Client::MSG_PEEK

Vue sur les données du tampon de `socket`. Après avoir établi la configuration `MSG_PEEK`, la méthode `recv` lit les données sans modifier le curseur, de sorte que la prochaine invocation de `recv` retournera toujours les données à partir de l'emplacement précédent.

### Swoole\Client::MSG_OOB

Lecture de données hors ligne, veuillez rechercher "données hors ligne TCP".

### Swoole\Client::SHUT_RDWR

Ferme les côtés lecture et écriture du client.

### Swoole\Client::SHUT_RD

Ferme le côté lecture du client.

### Swoole\Client::SHUT_WR

Ferme le côté écriture du client.

## Configuration

Le `Client` peut utiliser la méthode `set` pour configurer certaines options et activer certaines fonctionnalités.

### Analyse du protocole

?> L'analyse du protocole est conçue pour résoudre le problème des limites des paquets TCP (/apprendre?id=problème_de_limites_des_paquets_TCP), et la signification des configurations connexes est cohérente avec celle du `Swoole\Server`. Pour plus d'informations, veuillez vous référer à la section de configuration du [protocole Swoole\Server](/server/setting?id=open_eof_check).

* **Détection de fin de paquet**

```php
$client->set(array(
    'open_eof_check' => true,
    'package_eof' => "\r\n\r\n",
    'package_max_length' => 1024 * 1024 * 2,
));
```

* **Détection de longueur**

```php
$client->set(array(
    'open_length_check' => true,
    'package_length_type' => 'N',
    'package_length_offset' => 0, // Le Nème byte est la valeur de la longueur du paquet
    'package_body_offset' => 4, // Quel est le nombre de bytes à partir duquel la longueur est calculée
    'package_max_length' => 2000000, // La longueur maximale du protocole
));
```

!> Actuellement, les fonctionnalités d'analyse de protocole automatiques [open_length_check](/server/setting?id=open_length_check) et [open_eof_check](/server/setting?id=open_eof_check) sont prises en charge ;  
Après avoir configuré l'analyse du protocole, la méthode `recv()` du client ne prendra plus en paramètre la longueur et retournera toujours un paquet complet.

* **Protocole MQTT**

!> Activer l'analyse du protocole MQTT, et le回调 [onReceive](/server/events?id=onreceive) recevra un paquet MQTT complet.

```php
$client->set(array(
    'open_mqtt_protocol' => true,
));
```

* **Taille du tampon du socket**	

!> Cela comprend le tampon de l'opération système sous-jacent du `socket`, le tampon de mémoire pour la réception de données au niveau de l'application et le tampon de mémoire pour l'envoi de données au niveau de l'application.	

```php	
$client->set(array(	
    'socket_buffer_size' => 1024 * 1024 * 2, // 2M de tampon	
));	
```

* **Désactiver l'algorithme de fusion Nagle**

```php
$client->set(array(
    'open_tcp_nodelay' => true,
));
```


### Liens liés à SSL

* **Configuration des certificats SSL/TLS**

```php
$client->set(array(
    'ssl_cert_file' => $your_ssl_cert_file_path,
    'ssl_key_file' => $your_ssl_key_file_path,
));
```

* **ssl_verify_peer**

Vérifier le certificat du serveur.

```php
$client->set([
    'ssl_verify_peer' => true,
]);
```

Lorsqu'il est activé, il vérifie si le certificat et le nom d'hôte du serveur correspondent. Si ce n'est pas le cas, il fermera automatiquement la connexion.

* **Certificats auto-signés**

Vous pouvez configurer `ssl_allow_self_signed` à `true` pour autoriser les certificats auto-signés.

```php
$client->set([
    'ssl_verify_peer' => true,
    'ssl_allow_self_signed' => true,
]);
```

* **Nom d'hôte du serveur**

Définissez le nom d'hôte du serveur, utilisé en combinaison avec la configuration `ssl_verify_peer` ou avec la méthode [Client::verifyPeerCert](/client?id=verifypeercert).

```php
$client->set([
    'ssl_host_name' => 'www.google.com',
]);
```

* **Fichiers de CA**

Lorsque `ssl_verify_peer` est activé, ces fichiers sont utilisés pour vérifier les certificats distants. La valeur de cette option est le chemin complet et le nom du fichier du certificat CA local dans le système de fichiers.

```php
$client->set([
    'ssl_cafile' => '/etc/CA',
]);
```

* **Chemin des CA**

Si `ssl_cafile` n'est pas défini, ou si le fichier indiqué par `ssl_cafile` n'existe pas, les certificats appropriés seront recherchés dans le répertoire indiqué par `ssl_capath`. Ce répertoire doit déjà être un répertoire de certificats hashés.

```php
$client->set([
    'ssl_capath' => '/etc/capath/',
])
```

* **Mot de passe du certificat local**

Le mot de passe du certificat [ssl_cert_file](/server/setting?id=ssl_cert_file) local.

* **Exemple**

```php
$client = new Swoole\Client(SWOOLE_SOCK_TCP | SWOOLE_SSL);

$client->set(array(
    'ssl_cert_file' => __DIR__.'/ca/client-cert.pem',
    'ssl_key_file' => __DIR__.'/ca/client-key.pem',
    'ssl_allow_self_signed' => true,
    'ssl_verify_peer' => true,
    'ssl_cafile' => __DIR__.'/ca/ca-cert.pem',
));
if (!$client->connect('127.0.0.1', 9501, -1))
{
    exit("connect failed. Error: {$client->errCode}\n");
}
echo "connect ok\n";
$client->send("hello world-" . str_repeat('A', $i) . "\n");
echo $client->recv();
```


### package_length_func

Définissez une fonction de calcul de longueur, qui est utilisée de manière entièrement cohérente avec la méthode [package_length_func](/server/setting?id=package_length_func) du `Swoole\Server`. Utilisée en combinaison avec [open_length_check](/server/setting?id=open_length_check). La fonction de longueur doit retourner un entier.

* Retourne `0`, pas assez de données, nécessite de recevoir plus de données
* Retourne `-1`, erreur de données, le niveau inférieur fermera automatiquement la connexion
* Retourne la longueur totale du paquet (y compris la tête et le corps du paquet), le niveau inférieur assemblera automatiquement le paquet et le retournera au callback

Par défaut, le niveau inférieur lit jusqu'à `8K` de données, et si la longueur de la tête du paquet est plus petite, cela peut entraîner une consommation de copie de mémoire. Vous pouvez configurer le paramètre `package_body_offset`, permettant au niveau inférieur de lire uniquement la tête du paquet pour la résolution de la longueur.

* **Exemple**

```php
$client = new Swoole\Client(SWOOLE_SOCK_TCP);
$client->set(array(
    'open_length_check' => true,
    'package_length_func' => function ($data) {
        if (strlen($data) < 8) {
            return 0;
        }
        $length = intval(trim(substr($data, 0, 8)));
        if ($length <= 0) {
            return -1;
        }
        return $length + 8;
    },
));
if (!$client->connect('127.0.0.1', 9501, -1))
{
    exit("connect failed. Error: {$client->errCode}\n");
}
$client->send("hello world\n");
echo $client->recv();
$client->close();
```


### socks5_proxy

Configuration du proxy SOCKS5.

!> Il est invalide de configurer qu'une seule option, il faut toujours configurer à la fois `host` et `port` ; `socks5_username` et `socks5_password` sont des paramètres optionnels. `socks5_port` et `socks5_password` ne peuvent pas être `null`.

```php
$client->set(array(
    'socks5_host' => '192.168.1.100',
    'socks5_port' => 1080,
    'socks5_username' => 'username',
    'socks5_password' => 'password',
));
```


### http_proxy

Configuration du proxy HTTP.

!> `http_proxy_port` et `http_proxy_password` ne peuvent pas être `null`.

* **Configuration de base**

```php
$client->set(array(
    'http_proxy_host' => '192.168.1.100',
    'http_proxy_port' => 1080,
));
```

* **Configuration de vérification**

```php
$client->set(array(
    'http_proxy_user' => 'test',
    'http_proxy_password' => 'test_123456',
));
```


### bind

!> Il est invalide de configurer uniquement `bind_port`, veuillez configurer à la fois `bind_port` et `bind_address`

?> Lorsque l'ordinateur dispose de plusieurs cartes réseau, la configuration du paramètre `bind_address` peut forcer le client `Socket` à se lier à une adresse réseau spécifique.  
La configuration de `bind_port` permet au client `Socket` d'utiliser un port fixe pour se connecter à un serveur extérieur.

```php
$client->set(array(
    'bind_address' => '192.168.1.100',
    'bind_port' => 36002,
));
```
### Champ d'application

Les paramètres de configuration `Client` ci-dessus s'appliquent également aux clients suivants

  * [Swoole\Coroutine\Client](/coroutine_client/client)
  * [Swoole\Coroutine\Http\Client](/coroutine_client/http_client)
  * [Swoole\Coroutine\Http2\Client](/coroutine_client/http2_client)
