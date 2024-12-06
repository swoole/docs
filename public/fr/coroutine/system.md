# Co-routine\System

Emballage de coroutines pour les `API` système. Ce module est disponible après la version officielle `v4.4.6`. La plupart des `API` sont basées sur le pool de threads AIO.

!> Pour les versions antérieures à `v4.4.6`, veuillez utiliser les abréviations `Co` ou `Swoole\Coroutine` pour appeler, comme `Co::sleep` ou `Swoole\Coroutine::sleep`.  
Les versions `v4.4.6` et ultérieures recommandent officiellement l'utilisation de `Co\System::sleep` ou `Swoole\Coroutine\System::sleep`.  
Cette modification vise à normaliser le namespace, mais assure également la compatibilité descendante (c'est-à-dire que les écritures antérieures à la version `v4.4.6` sont également possibles et ne nécessitent pas de modification).


## Méthodes


### statvfs()

Obtenir des informations sur le système de fichiers.

!> Disponible pour les versions Swoole >= v4.2.5

```php
Swoole\Coroutine\System::statvfs(string $path): array|false
```

  * **Paramètres** 

    * **`string $path`**
      * **Fonction** : La directory montée du système de fichiers 【comme `/` , vous pouvez utiliser les commandes `df` et `mount -l` pour obtenir】
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

  * **Exemples d'utilisation**

    ```php
    Swoole\Coroutine\run(function () {
        var_dump(Swoole\Coroutine\System::statvfs('/'));
    });
    ```
  * **Exemples de sortie**    

    ```php
    array(11) {
      ["bsize"]=>
      int(4096)
      ["frsize"]=>
      int(4096)
      ["blocks"]=>
      int(61068098)
      ["bfree"]=>
      int(45753580)
      ["bavail"]=>
      int(42645728)
      ["files"]=>
      int(15523840)
      ["ffree"]=>
      int(14909927)
      ["favail"]=>
      int(14909927)
      ["fsid"]=>
      int(1002377915335522995)
      ["flag"]=>
      int(4096)
      ["namemax"]=>
      int(255)
    }
    ```


### fread()

Lecture asynchrone de fichiers en utilisant des coroutines.

```php
Swoole\Coroutine\System::fread(resource $handle, int $length = 0): string|false
```

!> Dans les versions inférieures à `v4.0.4`, la méthode `fread` ne prend pas en charge les flux non de type fichier, comme `STDIN` ou `Socket`. Veuillez ne pas utiliser `fread` pour gérer ces types de ressources.  
Dans les versions supérieures à `v4.0.4`, la méthode `fread` prend en charge les ressources de flux non de type fichier, et le sous-système utilise automatiquement le pool de threads AIO ou l'[EventLoop](/learn?id=什么是eventloop) en fonction du type de flux.

!> Cette méthode a été déprecée dans la version `5.0` et supprimée dans la version `6.0`

  * **Paramètres** 

    * **`resource $handle`**
      * **Fonction** : Le handle de fichier 【doit être un flux de type fichier ouvert par `fopen`】
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

    * **`int $length`**
      * **Fonction** : La longueur à lire 【par défaut est `0`, signifie lire tout le contenu du fichier】
      * **Valeur par défaut** : `0`
      * **Autres valeurs** : None

  * **Valeurs de retour** 

    * Retourne le contenu du string en cas de réussite de la lecture, sinon retourne `false`

  * **Exemples d'utilisation**  

    ```php
    $fp = fopen(__FILE__, "r");
    Swoole\Coroutine\run(function () use ($fp)
    {
        $r = Swoole\Coroutine\System::fread($fp);
        var_dump($r);
    });
    ```


### fwrite()

Écriture asynchrone de données dans un fichier en utilisant des coroutines.

```php
Swoole\Coroutine\System::fwrite(resource $handle, string $data, int $length = 0): int|false
```

!> Dans les versions inférieures à `v4.0.4`, la méthode `fwrite` ne prend pas en charge les flux non de type fichier, comme `STDIN` ou `Socket`. Veuillez ne pas utiliser `fwrite` pour gérer ces types de ressources.  
Dans les versions supérieures à `v4.0.4`, la méthode `fwrite` prend en charge les ressources de flux non de type fichier, et le sous-système utilise automatiquement le pool de threads AIO ou l'EventLoop en fonction du type de flux.

!> Cette méthode a été déprecée dans la version `5.0` et supprimée dans la version `6.0`

  * **Paramètres** 

    * **`resource $handle`**
      * **Fonction** : Le handle de fichier 【doit être un flux de type fichier ouvert par `fopen`】
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

    * **`string $data`**
      * **Fonction** : Le contenu à écrire 【peut être du texte ou des données binaires】
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

    * **`int $length`**
      * **Fonction** : La longueur de la lecture 【par défaut est `0`, signifie écrire tout le contenu de `$data`, `$length` doit être inférieur à la longueur de `$data`】
      * **Valeur par défaut** : `0`
      * **Autres valeurs** : None

  * **Valeurs de retour** 

    * Retourne la longueur de la donnée écrite en cas de réussite de l'écriture, sinon retourne `false`

  * **Exemples d'utilisation**  

    ```php
    $fp = fopen(__DIR__ . "/test.data", "a+");
    Swoole\Coroutine\run(function () use ($fp)
    {
        $r = Swoole\Coroutine\System::fwrite($fp, "hello world\n", 5);
        var_dump($r);
    });
    ```


### fgets()

Lecture asynchrone du contenu d'un fichier ligne par ligne en utilisant des coroutines.

Le sous-système utilise un tampon de `php_stream` par défaut de `8192` octets, qui peut être ajusté en utilisant `stream_set_chunk_size`.

```php
Swoole\Coroutine\System::fgets(resource $handle): string|false
```

!> La fonction `fgets` ne peut être utilisée que pour les ressources de flux de type fichier, Swoole version >= `v4.4.4` disponible

!> Cette méthode a été déprecée dans la version `5.0` et supprimée dans la version `6.0`

  * **Paramètres** 

    * **`resource $handle`**
      * **Fonction** : Le handle de fichier 【doit être un flux de type fichier ouvert par `fopen`】
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

  * **Valeurs de retour** 

    * Retourne une ligne de données incluant `EOL` (`\r` ou `\n`) lorsqu'une `EOL` est lue,
    * Si aucune `EOL` n'est lue mais que la longueur du contenu dépasse `8192` octets du tampon de `php_stream`, retourne `8192` octets de données sans `EOL`,
    * Lorsqu'on atteint la fin du fichier `EOF`, retourne une chaîne vide, utilisez `feof` pour déterminer si le fichier est terminé,
    * Retourne `false` en cas d'échec de la lecture, utilisez la fonction [swoole_last_error](/functions?id=swoole_last_error) pour obtenir l'code d'erreur.

  * **Exemples d'utilisation**  

    ```php
    $fp = fopen(__DIR__ . "/defer_client.php", "r");
    Swoole\Coroutine\run(function () use ($fp)
    {
        $r = Swoole\Coroutine\System::fgets($fp);
        var_dump($r);
    });
    ```


### readFile()

Lecture asynchrone d'un fichier en utilisant des coroutines.

```php
Swoole\Coroutine\System::readFile(string $filename): string|false
```

  * **Paramètres** 

    * **`string $filename`**
      * **Fonction** : Le nom du fichier
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

  * **Valeurs de retour** 

    * Retourne le contenu du string en cas de réussite de la lecture, sinon retourne `false`, utilisez [swoole_last_error](/functions?id=swoole_last_error) pour obtenir l'information sur l'erreur,
    * La méthode `readFile` n'a pas de restriction de taille, le contenu lu sera stocké en mémoire, donc la lecture de très gros fichiers peut consommer beaucoup de mémoire,

  * **Exemples d'utilisation**  

    ```php
    $filename = __DIR__ . "/defer_client.php";
    Swoole\Coroutine\run(function () use ($filename)
    {
        $r = Swoole\Coroutine\System::readFile($filename);
        var_dump($r);
    });
    ```
### writeFile()

Écrire dans un fichier en mode coroutine.

```php
Swoole\Coroutine\System::writeFile(string $filename, string $fileContent, int $flags): bool
```

  * **Paramètres** 

    * **`string $filename`**
      * **Fonction** : Nom du fichier【doit avoir les droits d'écriture, le fichier est créé automatiquement s'il n'existe pas. Le retournera `false` immédiatement en cas d'échec de l'ouverture du fichier】
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

    * **`string $fileContent`**
      * **Fonction** : Contenu à écrire dans le fichier【le maximum de contenu écritable est de `4M`】
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

    * **`int $flags`**
      * **Fonction** : Options d'écriture【par défaut, le contenu actuel du fichier est effacé, utiliser `FILE_APPEND` pour ajouter au bout du fichier】
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

  * **Valeurs de retour** 

    * Retourne `true` en cas d'écriture réussie
    * Retourne `false` en cas d'écriture échouée

  * **Exemple d'utilisation**  

    ```php
    $filename = __DIR__ . "/defer_client.php";
    Swoole\Coroutine\run(function () use ($filename)
    {
        $w = Swoole\Coroutine\System::writeFile($filename, "hello swoole!");
        var_dump($w);
    });
    ```


### sleep()

Entrer dans un état d'attente.

Correspond à la fonction `sleep` de PHP, mais contrairement à `Coroutine::sleep`, qui est réalisé par le scheduler de coroutines, il y aura un `yield` du coroutine actuel, laissant le temps au système et ajoutant un timer asynchrone. Lorsque la durée de temps expire, le coroutine actuel est `resume`, reprenant son exécution.

L'utilisation de l'interface `sleep` permet de réaliser facilement la fonction d'attente avec un délai.

```php
Swoole\Coroutine\System::sleep(float $seconds): void
```

  * **Paramètres** 

    * **`float $seconds`**
      * **Fonction** : Durée de l'attente【doit être supérieur à `0`, et ne doit pas dépasser une journée (86400 secondes)】
      * **Unité de valeur** : seconde, la précision minimale est de milliseconde (0.001 seconde)
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

  * **Exemple d'utilisation**  

    ```php
    $server = new Swoole\Http\Server("127.0.0.1", 9502);

    $server->on('Request', function($request, $response) {
        //attendre 200ms avant de répondre au navigateur
        Swoole\Coroutine\System::sleep(0.2);
        $response->end("<h1>Hello Swoole!</h1>");
    });

    $server->start();
    ```


### exec()

Exécuter une commande shell. Le bas niveau gère automatiquement le [scheduling des coroutines](/coroutine?id=scheduling-des-coroutines).

```php
Swoole\Coroutine\System::exec(string $cmd): array
```

  * **Paramètres** 

    * **`string $cmd`**
      * **Fonction** : Commande shell à exécuter
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

  * **Valeurs de retour**

    * Retourne `false` en cas d'échec de l'exécution, et un tableau en cas de succès, contenant le code d'exit du processus, le signal et le contenu de l'output.

    ```php
    array(
        'code'   => 0,  // Code d'exit du processus
        'signal' => 0,  // Signal
        'output' => '', // Contenu de l'output
    );
    ```

  * **Exemple d'utilisation**  

    ```php
    Swoole\Coroutine\run(function() {
        $ret = Swoole\Coroutine\System::exec("md5sum ".__FILE__);
    });
    ```

  * **Note**

  !> Si l'exécution de la commande shell prend trop de temps, cela peut entraîner une sortie due à un timeout. Dans ce cas, vous pouvez résoudre le problème en augmentant la [timeout de lecture du socket](/coroutine_client/init?id=timeout-de-lecture-du-socket).


### gethostbyname()

Résoudre le nom de domaine en IP. Implémenté en utilisant un pool de threads synchrones, le bas niveau gère automatiquement le [scheduling des coroutines](/coroutine?id=scheduling-des-coroutines).

```php
Swoole\Coroutine\System::gethostbyname(string $domain, int $family = AF_INET, float $timeout = -1): string|false
```

  * **Paramètres** 

    * **`string $domain`**
      * **Fonction** : Nom de domaine
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

    * **`int $family`**
      * **Fonction** : Famille de domaine【`AF_INET` pour une adresse IPv4, utiliser `AF_INET6` pour une adresse IPv6】
      * **Valeur par défaut** : `AF_INET`
      * **Autres valeurs** : `AF_INET6`

    * **`float $timeout`**
      * **Fonction** : Timeout
      * **Unité de valeur** : seconde, la précision minimale est de milliseconde (0.001 seconde)
      * **Valeur par défaut** : `-1`
      * **Autres valeurs** : None

  * **Valeurs de retour**

    * Retourne l'adresse IP correspondant au nom de domaine en cas de succès, `false` en cas d'échec, utilisez [swoole_last_error](/functions?id=swoole_last_error) pour obtenir des informations sur l'erreur.

    ```php
    array(
        'code'   => 0,  // Code d'exit du processus
        'signal' => 0,  // Signal
        'output' => '', // Contenu de l'output
    );
    ```

  * **Extensiones**

    * **Contrôle du timeout**

      Le paramètre `$timeout` peut contrôler le temps d'attente des coroutines. Si un résultat n'est pas retourné dans la période spécifiée, la coroutine continuera immédiatement à retourner `false` et à exécuter la prochaine instruction. Dans la mise en œuvre de base, cette tâche asynchrone sera marquée comme `cancel`, mais `gethostbyname` continuera à s'exécuter dans le pool de threads AIO.
      
      Vous pouvez modifier le temps de timeout des fonctions C sous-jacentes de `gethostbyname` et `getaddrinfo` en modifiant le fichier `/etc/resolv.conf`. Pour plus d'informations, veuillez consulter [Configuration du timeout de résolution DNS et des tentatives](/learn_other?id=configuration-du-timeout-de-resolution-dns-et-des-tentes).

  * **Exemple d'utilisation**  

    ```php
    Swoole\Coroutine\run(function () {
        $ip = Swoole\Coroutine\System::gethostbyname("www.baidu.com", AF_INET, 0.5);
        echo $ip;
    });
    ```


### getaddrinfo()

Résoudre le DNS pour rechercher l'adresse IP correspondant à un nom de domaine.

Contrairement à `gethostbyname`, `getaddrinfo` prend en charge davantage de paramètres et peut retourner plusieurs résultats IP.

```php
Swoole\Coroutine\System::getaddrinfo(string $domain, int $family = AF_INET, int $socktype = SOCK_STREAM, int $protocol = STREAM_IPPROTO_TCP, string $service = null, float $timeout = -1): array|false
```

  * **Paramètres** 

    * **`string $domain`**
      * **Fonction** : Nom de domaine
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

    * **`int $family`**
      * **Fonction** : Famille de domaine【`AF_INET` pour une adresse IPv4, utiliser `AF_INET6` pour une adresse IPv6】
      * **Valeur par défaut** : None
      * **Autres valeurs** : None
      
      !> Pour plus d'informations sur les autres paramètres, veuillez consulter la documentation `man getaddrinfo`.

    * **`int $socktype`**
      * **Fonction** : Type de socket
      * **Valeur par défaut** : `SOCK_STREAM`
      * **Autres valeurs** : `SOCK_DGRAM`, `SOCK_RAW`

    * **`int $protocol`**
      * **Fonction** : Protocole
      * **Valeur par défaut** : `STREAM_IPPROTO_TCP`
      * **Autres valeurs** : `STREAM_IPPROTO_UDP`, `STREAM_IPPROTO_STCP`, `STREAM_IPPROTO_TIPC`, `0`

    * **`string $service`**
      * **Fonction** :
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

    * **`float $timeout`**
      * **Fonction** : Timeout
      * **Unité de valeur** : seconde, la précision minimale est de milliseconde (0.001 seconde)
      * **Valeur par défaut** : `-1`
      * **Autres valeurs** : None

  * **Valeurs de retour**

    * Retourne un tableau composé de plusieurs adresses IP en cas de succès, `false` en cas d'échec

  * **Exemple d'utilisation**  

    ```php
    Swoole\Coroutine\run(function () {
        $ips = Swoole\Coroutine\System::getaddrinfo("www.baidu.com");
        var_dump($ips);
    });
    ```
### dnsLookup()

Requête d'adresse de domaine.

Contrairement à `Coroutine\System::gethostbyname`, `Coroutine\System::dnsLookup` est directement basé sur la communication réseau UDP client pour réaliser la recherche d'adresse, et non pas en utilisant la fonction `gethostbyname` fournie par `libc`.

!> Disponible à partir de la version Swoole `v4.4.3`, le sous-système lit `/etc/resolve.conf` pour obtenir l'adresse du serveur DNS, et ne prend en charge que la résolution de noms de domaines `AF_INET (IPv4)` pour l'instant. À partir de la version Swoole `v4.7`, la troisième argument peut être utilisée pour prendre en charge la résolution de noms de domaines `AF_INET6 (IPv6)`.

```php
Swoole\Coroutine\System::dnsLookup(string $domain, float $timeout = 5, int $type = AF_INET): string|false
```

  * **Paramètres** 

    * **`string $domain`**
      * **Fonction** : Nom de domaine
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

    * **`float $timeout`**
      * **Fonction** : Temps de timeout
      * **Unité de valeur** : Seconde, la précision minimale est de 0,001 seconde (0,001 seconde)
      * **Valeur par défaut** : `5`
      * **Autres valeurs** : None

    * **`int $type`**
        * **Unité de valeur** : Seconde, la précision minimale est de 0,001 seconde (0,001 seconde)
        * **Valeur par défaut** : `AF_INET`
        * **Autres valeurs** : `AF_INET6`

    !> Le paramètre `$type` est disponible à partir de la version Swoole `v4.7`.

  * **Valeurs de retour**

    * Si la résolution est réussie, retourne l'adresse IP correspondante
    * Si échoué, retourne `false`, et vous pouvez utiliser [swoole_last_error](/functions?id=swoole_last_error) pour obtenir des informations sur l'erreur

  * **Erreurs courantes**

    * `SWOOLE_ERROR_DNSLOOKUP_RESOLVE_FAILED` : Ce domaine ne peut pas être résolu, l'enquête a échoué
    * `SWOOLE_ERROR_DNSLOOKUP_RESOLVE_TIMEOUT` : La résolution a timeout, le serveur DNS peut être en panne et ne pas pouvoir retourner le résultat dans le délai imparti

  * **Exemples d'utilisation**  

    ```php
    Swoole\Coroutine\run(function () {
        $ip = Swoole\Coroutine\System::dnsLookup("www.baidu.com");
        echo $ip;
    });
    ```


### wait()

Correspond à l'original [Process::wait](/process/process?id=wait), mais cette API est une version de coroutines, ce qui causera le blocage des coroutines, elle peut remplacer les fonctions `Swoole\Process::wait` et `pcntl_wait`.

!> Disponible à partir de la version Swoole `v4.5.0`

```php
Swoole\Coroutine\System::wait(float $timeout = -1): array|false
```

* **Paramètres** 

    * **`float $timeout`**
      * **Fonction** : Temps de timeout, un nombre négatif signifie que le timeout n'expirera jamais
      * **Unité de valeur** : Seconde, la précision minimale est de 0,001 seconde (0,001 seconde)
      * **Valeur par défaut** : `-1`
      * **Autres valeurs** : None

* **Valeurs de retour**

  * Si l'opération est réussie, elle retourne un tableau contenant le `PID` du processus enfant, le code d'état de sortie, et le signal qui a été utilisé pour `KILL` le processus
  * Si l'opération échoue, elle retourne `false`

!> Après le démarrage de chaque processus enfant, le processus parent doit envoyer une coroutine pour appeler `wait()` (ou `waitPid()`) pour récupérer le processus, sinon le processus enfant deviendra un processus fantôme, gaspillant les ressources de processus du système d'exploitation.  
Si vous utilisez des coroutines, vous devez d'abord créer un processus, puis démarrer des coroutines à l'intérieur du processus. Il ne faut pas faire l'inverse, sinon la situation de fork avec des coroutines sera très complexe et rendra difficile le traitement au niveau du système d'exploitation.

* **Exemple**

```php
use Swoole\Coroutine;
use Swoole\Coroutine\System;
use Swoole\Process;

$process = new Process(function () {
    echo 'Hello Swoole';
});
$process->start();

Coroutine\run(function () use ($process) {
    $status = System::wait();
    assert($status['pid'] === $process->pid);
    var_dump($status);
});
```


### waitPid()

Essentiellement identique à la méthode `wait` mentionnée ci-dessus, la différence est que cette API permet de spécifier un processus particulier pour attendre.

!> Disponible à partir de la version Swoole `v4.5.0`

```php
Swoole\Coroutine\System::waitPid(int $pid, float $timeout = -1): array|false
```

* **Paramètres** 

    * **`int $pid`**
      * **Fonction** : ID du processus
      * **Valeur par défaut** : `-1` (signifie n'importe quel processus, à ce moment-là équivalent à la méthode `wait`)
      * **Autres valeurs** : N'importe quel nombre naturel

    * **`float $timeout`**
      * **Fonction** : Temps de timeout, un nombre négatif signifie que le timeout n'expirera jamais
      * **Unité de valeur** : Seconde, la précision minimale est de 0,001 seconde (0,001 seconde)
      * **Valeur par défaut** : `-1`
      * **Autres valeurs** : None

* **Valeurs de retour**

  * Si l'opération est réussie, elle retourne un tableau contenant le `PID` du processus enfant, le code d'état de sortie, et le signal qui a été utilisé pour `KILL` le processus
  * Si l'opération échoue, elle retourne `false`

!> Après le démarrage de chaque processus enfant, le processus parent doit envoyer une coroutine pour appeler `wait()` (ou `waitPid()`) pour récupérer le processus, sinon le processus enfant deviendra un processus fantôme, gaspillant les ressources de processus du système d'exploitation.

* **Exemple**

```php
use Swoole\Coroutine;
use Swoole\Coroutine\System;
use Swoole\Process;

$process = new Process(function () {
    echo 'Hello Swoole';
});
$process->start();

Coroutine\run(function () use ($process) {
    $status = System::waitPid($process->pid);
    var_dump($status);
});
```


### waitSignal()

Écouteur de signal de version coroutines, qui bloquera la coroutine actuelle jusqu'à ce que le signal soit déclenché, il peut remplacer les fonctions `Swoole\Process::signal` et `pcntl_signal`.

!> Disponible à partir de la version Swoole `v4.5.0`

```php
Swoole\Coroutine\System::waitSignal(int $signo, float $timeout = -1): bool
```

  * **Paramètres** 

    * **`int $signo`**
      * **Fonction** : Type de signal
      * **Valeur par défaut** : None
      * **Autres valeurs** : Constantes SIG telles que `SIGTERM`, `SIGKILL`, etc.

    * **`float $timeout`**
      * **Fonction** : Temps de timeout, un nombre négatif signifie que le timeout n'expirera jamais
      * **Unité de valeur** : Seconde, la précision minimale est de 0,001 seconde (0,001 seconde)
      * **Valeur par défaut** : `-1`
      * **Autres valeurs** : None

  * **Valeurs de retour**

    * Si le signal est reçu, il retourne `true`
    * Si le signal n'est pas reçu avant l'expiration du timeout, il retourne `false`

  * **Exemples**

```php
use Swoole\Coroutine;
use Swoole\Coroutine\System;
use Swoole\Process;

$process = new Process(function () {
    Coroutine\run(function () {
        $bool = System::waitSignal(SIGUSR1);
        var_dump($bool);
    });
});
$process->start();
sleep(1);
$process::kill($process->pid, SIGUSR1);
```

### waitEvent()

Écouteur de signal de version coroutines, qui bloquera la coroutine actuelle jusqu'à ce que le signal soit déclenché. Attente des événements IO, il peut remplacer les fonctions liées à `swoole_event`.

!> Disponible à partir de la version Swoole `v4.5`

```php
Swoole\Coroutine\System::waitEvent(mixed $socket, int $events = SWOOLE_EVENT_READ, float $timeout = -1): int | false
```

* **Paramètres** 

    * **`mixed $socket`**
      * **Fonction** : Descriptor de fichier (n'importe quel type qui peut être transformé en fd, comme un objet Socket, une ressource, etc.)
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

    * **`int $events`**
      * **Fonction** : Type d'événement
      * **Valeur par défaut** : `SWOOLE_EVENT_READ`
      * **Autres valeurs** : `SWOOLE_EVENT_WRITE` ou `SWOOLE_EVENT_READ | SWOOLE_EVENT_WRITE`

    * **`float $timeout`**
      * **Fonction** : Temps de timeout, un nombre négatif signifie que le timeout n'expirera jamais
      * **Unité de valeur** : Seconde, la précision minimale est de 0,001 seconde (0,001 seconde)
      * **Valeur par défaut** : `-1`
      * **Autres valeurs** : None

* **Valeurs de retour**

  * Retourne l'union des types d'événements déclenchés et (peut être plusieurs bits), en fonction de la valeur passée dans le paramètre `$events`
  * Si l'opération échoue, elle retourne `false`, et vous pouvez utiliser [swoole_last_error](/functions?id=swoole_last_error) pour obtenir des informations sur l'erreur

* **Exemples**

> Le code synchrone bloquant peut être transformé en non bloquant par cette API

```php
use Swoole\Coroutine;

Coroutine\run(function () {
    $client = stream_socket_client('tcp://www.qq.com:80', $errno, $errstr, 30);
    $events = Coroutine::waitEvent($client, SWOOLE_EVENT_READ | SWOOLE_EVENT_WRITE);
    assert($events === SWOOLE_EVENT_WRITE);
    fwrite($client, "GET / HTTP/1.1\r\nHost: www.qq.com\r\n\r\n");
    $events = Coroutine::waitEvent($client, SWOOLE_EVENT_READ);
    assert($events === SWOOLE_EVENT_READ);
    $response = fread($client, 8192);
    echo $response;
});
```
