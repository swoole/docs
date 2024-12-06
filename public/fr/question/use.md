# Utilisation des questions

## Comment Swoole se comporte-t-il en termes de performance ?

> Comparaison de QPS

Une benchmarking a été effectuée avec l'outil Apache-Bench (ab) sur un serveur Nginx statique, un programme HTTP en Golang et un programme HTTP en PHP7+Swoole. Sur la même machine, lors d'un test de base avec 100 processus en parallèle pour un total de 1 million de demandes HTTP, les comparaisons de QPS sont les suivantes :

| Software | QPS | Version du logiciel |
| --- | --- | --- |
| Nginx | 164489.92 | nginx/1.4.6 (Ubuntu) |
| Golang | 166838.68 | go version go1.5.2 linux/amd64 |
| PHP7+Swoole | 287104.12 | Swoole-1.7.22-alpha |
| Nginx-1.9.9 | 245058.70 | nginx/1.9.9 |

!> Remarque : Dans le test Nginx-1.9.9, l'accès_log a été désactivé et le cache des fichiers statiques ouvert en mémoire avec open_file_cache a été activé.

> Environment de test

* CPU : Intel® Core™ i5-4590 CPU @ 3.30GHz × 4
* Mémoire : 16G
* Disque : 128G SSD
* Système d'exploitation : Ubuntu14.04 (Linux 3.16.0-55-generic)

> Méthode de benchmarking

```shell
ab -c 100 -n 1000000 -k http://127.0.0.1:8080/
```

> Configuration VHOST

```nginx
server {
    listen 80 default_server;
    root /data/webroot;
    index index.html;
}
```

> Page de test

```html
<h1>Bonjour le monde !</h1>
```

> Nombre de processus

Nginx a lancé 4 processus Worker
```shell
htf@htf-All-Series:~/soft/php-7.0.0$ ps aux|grep nginx
root      1221  0.0  0.0  86300  3304 ?        Ss   12月07   0:00 nginx: master process /usr/sbin/nginx
www-data  1222  0.0  0.0  87316  5440 ?        S    12月07   0:44 nginx: worker process
www-data  1223  0.0  0.0  87184  5388 ?        S    12月07   0:36 nginx: worker process
www-data  1224  0.0  0.0  87000  5520 ?        S    12月07   0:40 nginx: worker process
www-data  1225  0.0  0.0  87524  5516 ?        S    12月07   0:45 nginx: worker process
```

> Golang

Code de test

```go
package main

import (
    "log"
    "net/http"
    "runtime"
)

func main() {
    runtime.GOMAXPROCS(runtime.NumCPU() - 1)

    http.HandleFunc("/", func(w http.ResponseWriter, r *http.Request) {
        w.Header().Add("Last-Modified", "Thu, 18 Jun 2015 10:24:27 GMT")
        w.Header().Add("Accept-Ranges", "bytes")
        w.Header().Add("E-Tag", "55829c5b-17")
        w.Header().Add("Server", "golang-http-server")
        w.Write([]byte("<h1>\nHello world!\n</h1>\n"))
    })

    log.Printf("Go http Server listen on :8080")
    log.Fatal(http.ListenAndServe(":8080", nil))
}
```

> PHP7+Swoole

PHP7 a activé l'accélérateur `OPcache`.

Code de test

```php
$http = new Swoole\Http\Server("127.0.0.1", 9501, SWOOLE_BASE);

$http->set([
    'worker_num' => 4,
]);

$http->on('request', function ($request, Swoole\Http\Server $response) {
    $response->header('Last-Modified', 'Thu, 18 Jun 2015 10:24:27 GMT');
    $response->header('E-Tag', '55829c5b-17');
    $response->header('Accept-Ranges', 'bytes');    
    $response->end("<h1>\nHello Swoole.\n</h1>");
});

$http->start();
```

> **Performance des frameworks Web mondiaux autoritaires Techempower Web Framework Benchmarks**

Résultats de l'évaluation la plus récente : [techempower](https://www.techempower.com/benchmarks/#section=test&runid=9d5522a6-2917-467a-9d7a-8c0f6a8ed790)

Swoole mène la première place parmi les langues dynamiques

Test d'opération IO de base de données, sans optimisation spéciale pour le code d'affaires de base

**Performance supérieure à tous les frameworks de langues statiques (utilisation de MySQL plutôt que PostgreSQL)**


## Comment Swoole maintient-il une connexion TCP à long terme ?

Pour maintenir une connexion TCP à long terme, il y a deux groupes de configurations [tcp_keepalive](/server/setting?id=open_tcp_keepalive) et [heartbeat](/server/setting?id=heartbeat_check_interval).


## Comment Swoole redémarre-t-il correctement le service ?

Dans le développement quotidien, après avoir modifié le code PHP, il est souvent nécessaire de redémarrer le service pour que le code prenne effet. Un serveur arrière occupé gère constamment des demandes, et si l'administrateur utilise la méthode `kill` pour arrêter/redémarrer le processus du serveur, cela peut entraîner l'arrêt du code au milieu de son exécution, sans assurer l'intégrité de toute la logique commerciale.

Swoole offre un mécanisme souple d'arrêt/redémarrage, permettant à l'administrateur d'envoyer un signal spécifique ou d'appeler la méthode `reload` au serveur, ce qui permet aux processus de travail de se terminer et de se relancer. Pour plus de détails, veuillez consulter [reload()](/server/methods?id=reload).

Cependant, il est important de noter les points suivants :

Tout d'abord, il est essentiel que le nouveau code soit réchauffé dans l'événement `OnWorkerStart` pour prendre effet. Par exemple, si une classe est chargée par l'autoload de Composer avant `OnWorkerStart`, cela ne fonctionnera pas.

Deuxièmement, la `reload` doit être utilisée en combinaison avec les deux paramètres [max_wait_time](/server/setting?id=max_wait_time) et [reload_async](/server/setting?id=reload_async), ce qui permet de réaliser un redémarrage asynchrone sécurisé.

Sans cette caractéristique, si les processus Worker reçoivent un signal de redémarrage ou atteignent [max_request](/server/setting?id=max_request), ils arrêteront immédiatement le service. À ce moment-là, il est possible que des événements asynchrones soient encore en attente au sein des processus Worker, et ces tâches asynchrones seront abandonnées. Après avoir établi les paramètres mentionnés ci-dessus, de nouveaux Worker seront créés en premier, et les anciens Worker se termineront d'eux-mêmes après avoir traité tous les événements, c'est-à-dire `reload_async`.

Si les anciens Worker ne se terminent pas, un timer est ajouté en dessous pour forcer la fin des Worker si le temps convenu ([max_wait_time](/server/setting?id=max_wait_time) secondes) n'est pas atteint. Cela générera une [WARNING](/question/use?id=forced-to-terminate).

Exemple :

```php
<?php
$serv = new Swoole\Server('0.0.0.0', 9501, SWOOLE_PROCESS);
$serv->set(array(
    'worker_num' => 1,
    'max_wait_time' => 60,
    'reload_async' => true,
));
$serv->on('receive', function (Swoole\Server $serv, $fd, $reactor_id, $data) {

    echo "[#" . $serv->worker_id . "]\tClient[$fd] receive data: $data\n";
    
    Swoole\Timer::tick(5000, function () {
        echo 'tick';
    });
});

$serv->start();
```

Dans l'exemple ci-dessus, sans reload_async, le timer créé dans onReceive sera perdu et n'aura pas l'occasion de traiter la fonction de rappel du timer.


### Événement de sortie du processus

Pour soutenir la caractéristique de redémarrage asynchrone, un nouvel événement [onWorkerExit](/server/events?id=onWorkerExit) a été ajouté en dessous. Lorsque les anciens Worker sont sur le point de se terminer, l'événement `onWorkerExit` est déclenché. Dans la fonction de rappel de cet événement, l'application peut essayer de nettoyer certaines connexions long terme `Socket`, jusqu'à ce qu'il n'y ait plus de fd dans le cycle d'événements ou qu'il soit atteint [max_wait_time](/server/setting?id=max_wait_time) pour quitter le processus.

```php
$serv->on('WorkerExit', function (Swoole\Server $serv, $worker_id) {
    $redisState = $serv->redis->getState();
    if ($redisState == Swoole\Redis::STATE_READY or $redisState == Swoole\Redis::STATE_SUBSCRIBE)
    {
        $serv->redis->close();
    }
});
```

En même temps, dans [Swoole Plus](https://www.swoole.com/swoole_plus), une fonction de détection des changements de fichiers a été ajoutée, permettant de redémarrer automatiquement les worker sans avoir à recharger manuellement ou envoyer un signal.
## Pourquoi ne pas fermer immédiatement après avoir envoyé est-il insécurité ?

Faire fermer immédiatement après avoir envoyé est insécurisant, que ce soit du côté serveur ou du côté client.

L'opération d'envoi réussir signifie seulement que les données ont été correctement écrites dans le cache de socket du système d'exploitation, mais cela ne signifie pas que l'autre partie a vraiment reçu les données. Il n'est pas possible de garantir avec certitude que le système d'exploitation a envoyé avec succès, que le serveur distant a reçu, ou que le programme du côté serveur a traité les données.

> Pour la logique après le fermeture, veuillez consulter la configuration suivante pour les paramètres linger.

Cette logique est similaire à une conversation téléphonique : A dit quelque chose à B, puis A raccroche. A ne sait pas si B a bien entendu. Si A a fini de parler et que B répond par un 'ok' avant de raccrocher, c'est absolument sûr.

Configuration linger

Lorsqu'un 'socket' est fermé, si le tampon contient encore des données, le niveau sous-操作系统 décide comment gérer cela en fonction de la configuration linger :

```c
struct linger
{
     int l_onoff;
     int l_linger;
};
```

* Si l_onoff = 0, le close s'effectue immédiatement et le niveau sous-操作系统 envoie les données non envoyées avant de libérer les ressources, c'est-à-dire une sortie élégante.
* Si l_onoff != 0 et l_linger = 0, le close s'effectue immédiatement mais ne发送 pas les données non envoyées, il utilise plutôt un paquet RST pour fermer brusquement le descripteur de socket, c'est-à-dire une sortie forcée.
* Si l_onoff != 0 et l_linger > 0, le close ne s'effectue pas immédiatement, le noyau attend un certain temps, ce temps est déterminé par la valeur de l_linger. Si le temps de délai est atteint avant que les données non envoyées (y compris le paquet FIN) soient envoyées et reconnues par l'autre côté, le close retourne correctement et le descripteur de socket sort élégamment. Sinon, le close retourne directement une erreur, les données non envoyées sont perdues et le descripteur de socket est forcé de sortir. Si le descripteur de socket est configuré pour être non bloquant, alors le close retourne directement la valeur.

## client a déjà été lié à une autre coroutine

Pour une connexion TCP, le sous-système Swoole permet qu'une seule coroutine lit et une seule coroutine écrit simultanément sur le même TCP. Cela signifie qu'il n'est pas possible pour plusieurs coroutines de lire ou d'écrire sur le même TCP à la fois, le sous-système lèvera une erreur de liaison :

```shell
Erreur fatale : Un coupable non attrapé de Swoole\Error : Le socket #6 a déjà été lié à une autre coroutine #2, il n'est pas permis de lire ou d'écrire sur le même socket dans la coroutine #3 en même temps
```

Code moderne :

```php
use Swoole\Coroutine;
use Swoole\Coroutine\Http\Client;
use function Swoole\Coroutine\run;

run(function() {
    $cli = new Client('www.xinhuanet.com', 80);
    Coroutine::create(function () use ($cli) {
        $cli->get('/');
    });
    Coroutine::create(function () use ($cli) {
        $cli->get('/');
    });
});
```

Solution de référence : https://wenda.swoole.com/detail/107474

!> Cette restriction s'applique à tous les environnements multicouplets, la plus courante étant l'utilisation commune d'une connexion TCP dans des fonctions de retour comme [onReceive](/server/events?id=onreceive), car ces fonctions de retour créent automatiquement une coroutine.
Que faire si vous avez besoin d'un pool de connexions ? Swoole intègre un [pool de connexions](/coroutine/conn_pool) qui peut être utilisé directement, ou vous pouvez encapsuler manuellement un pool de connexions avec un canal.

## Appel à une fonction indefinie Co\run()

La plupart des exemples dans cet article utilisent `Co\run()` pour créer un conteneur de coroutines, [comprenez ce qu'est un conteneur de coroutines](/coroutine?id=qu'est-ce-qu'un-conteneur-de-coroutines)

Si vous rencontrez l'erreur suivante :

```bash
Erreur PHP fatale : Un coupable non attrapé d'erreur : Appel à une fonction indefinie Co\run()

Erreur PHP fatale : Un coupable non attrapé d'erreur : Appel à une fonction indefinie go()
```

 cela indique que votre version de l'extension Swoole est inférieure à `v4.4.0` ou que vous avez fermé manuellement le [nom court de coroutine](/other/alias?id=nom-court-de-coroutine), voici les solutions possibles :

* Si c'est parce que la version est trop basse, veuillez mettre à jour l'extension vers `>= v4.4.0` ou utiliser la keyword `go` pour remplacer `Co\run` et créer une coroutine ;
* Si vous avez fermé le nom court de coroutine, veuillez l'ouvrir [nom court de coroutine](/other/alias?id=nom-court-de-coroutine) ;
* Utilisez la méthode [Coroutine::create](/coroutine/coroutine?id=create) pour remplacer `Co\run` ou `go` et créer une coroutine ;
* Utilisez le nom complet : `Swoole\Coroutine\run` ;


## Peut-on partager une seule connexion Redis ou MySQL ?

Absolument pas. Chaque processus doit créer une connexion Redis, MySQL, PDO séparément, et il en va de même pour les autres clients de stockage. La raison est que si vous partagez une connexion, il n'est pas possible de garantir que le résultat est traité par quel processus, le processus détenant la connexion peut théoriquement lire et écrire sur cette connexion, ce qui entraînerait une confusion des données.

**Par conséquent, il est absolument interdit de partager des connexions entre plusieurs processus**

* Dans [Swoole\Server](/server/init), vous devez créer un objet de connexion dans la fonction de callback [onWorkerStart](/server/events?id=onworkerstart)
* Dans [Swoole\Process](/process/process), vous devez créer un objet de connexion dans la fonction de callback après le démarrage du processus fils avec [Swoole\Process->start](/process/process?id=start)
* Cette information est également valide pour les programmes utilisant `pcntl_fork`

Exemple :

```php
$server = new Swoole\Server('0.0.0.0', 9502);

//必须在onWorkerStart回调中创建redis/mysql连接
$server->on('workerstart', function($server, $id) {
    $redis = new Redis();
	$redis->connect('127.0.0.1', 6379);
	$server->redis = $redis;
});

$server->on('receive', function (Swoole\Server $server, $fd, $reactor_id, $data) {	
	$value = $server->redis->get("key");
	$server->send($fd, "Swoole: ".$value);
});

$server->start();
```


## Question de connexion déjà fermée

Comme indiqué ci-dessous

```bash
AVIS swFactoryProcess_finish (ERRNO 1004) : envoyé 165 octets échoué, car la connexion [fd=123] est fermée

AVIS swFactoryProcess_finish (ERREUR 1005) : la connexion [fd=123] n'existe plus
```

Cela se produit lorsque le serveur répond et que le client a déjà coupé la connexion

Cela se produit souvent dans les cas suivants :

* Le navigateur se rafraîchit follement la page (avant même qu'elle ne soit entièrement chargée)
* L'stress test ab est annulé au milieu
* Le stress test wrk basé sur le temps (les demandes inachevées sont annulées à l'expiration du temps)

Tous ces cas sont des phénomènes normaux et peuvent être ignorés, donc le niveau d'erreur de cette erreur est AVIS

Si des connexions se coupent sans raison dans de nombreux cas, alors il faut faire attention

```bash
WARNING swWorker_discard_data (ERRNO 1007) : [2] a reçu des données incorrectes [21 octets] de socket #75

WARNING Worker_discard_data (ERRNO 1007) : [2] ignore les données [5 octets] reçues de session #2
```

De même, cet avertissement indique également que la connexion a été fermée, et les données reçues seront abandonnées. Référence [discard_timeout_request](/server/setting?id=discard_timeout_request)
### Remarque

Bien que la version asynchrone précédente prenne en charge les mises à jour "réelles" de la propriété `connected`, elle n'était en réalité pas fiable, la connexion pourrait se déconnecter immédiatement après avoir été vérifiée.

## Qu'est-ce qui se passe lorsque la connexion est refusée

Lorsqu'une connexion est refusée lors de l'utilisation de `telnet 127.0.0.1 9501`, cela indique que le serveur n'écoute pas sur ce port.

* Vérifiez si le programme s'est exécuté avec succès : `ps aux`
* Vérifiez si le port est en écoute : `netstat -lp`
* Visualisez le processus de communication réseau avec `tcpdump traceroute`

## Resource temporarily unavailable [11]

Le client `swoole_client` rencontre une erreur lorsqu'il utilise la fonction `recv` :

```shell
swoole_client::recv(): recv() failed. Error: Resource temporarily unavailable [11]
```

Cette erreur indique que le serveur n'a pas renvoyé de données dans le délai imparti, et que la réception a expiré.

* Vous pouvez utiliser `tcpdump` pour visualiser le processus de communication réseau et vérifier si le serveur a envoyé des données
* La fonction `$serv->send` du serveur doit être vérifiée pour voir si elle retourne `true`
* Lors de la communication avec l'extérieur, si le temps de réponse est long, il est nécessaire d'augmenter le délai d'expiration de `swoole_client`

## worker exit timeout, forced to terminate :id=forced-to-terminate

Vous pouvez rencontrer l'erreur suivante :

```bash
WARNING swWorker_reactor_try_to_exit (ERRNO 9012): worker exit timeout, forced to terminate
```

Cela indique que le Worker n'a pas quitté dans le délai convenu ([max_wait_time](/server/setting?id=max_wait_time) secondes), et que Swoole a forcé la terminaison de ce processus.

Vous pouvez reproduire l'erreur avec le code suivant :

```php
use Swoole\Timer;

$server = new Swoole\Server('127.0.0.1', 9501);
$server->set(
    [
        'reload_async' => true,
        'max_wait_time' => 4,
    ]
);

$server->on('workerStart', function (Swoole\Server $server, int $wid) {
    if ($wid === 0) {
        Timer::tick(5000, function () {
            echo 'tick';
        });
        Timer::after(500, function () use ($server) {
            $server->shutdown();
        });
    }
});

$server->on('receive', function () {

});

$server->start();
```

## Unable to find callback function for signal Broken pipe: 13

Vous pouvez rencontrer l'erreur suivante :

```bash
WARNING swSignalfd_onSignal (ERRNO 707): Unable to find callback function for signal Broken pipe: 13
```

Cela indique qu'un message a été envoyé à une connexion déjà fermée, généralement parce que la valeur de retour de l'envoi n'a pas été vérifiée, et que l'envoi a continué même après un échec.

## Quelles sont les connaissances de base nécessaires pour apprendre Swoole

### Multiprocessing/Multithreading

* Connaissez les concepts de processus et de threads dans l'opération système `Linux`
* Connaissez les connaissances de base sur le changement de调度 (scheduling) des processus/threads `Linux`
* Connaissez les méthodes de communication entre processus, telles que les tuyaux, les `UnixSocket`, les files d'attente de messages, la mémoire partagée

### SOCKET

* Connaissez les opérations de base du `SOCKET`, telles que `accept/connect`, `send/recv`, `close`, `listen`, `bind`
* Connaissez les concepts de tampon de réception, de tampon d'envoi, de blocage/non-blocage, de timeout, etc., du `SOCKET`

### IO Multiplexing

* Connaissez `select`/`poll`/`epoll`
* Connaissez les boucles d'événements basées sur `select`/`epoll`, le modèle `Reactor`
* Connaissez les événements lisibles, écrivables

### Protocole réseau TCP/IP

* Connaissez le protocole `TCP/IP`
* Connaissez les protocoles de transport `TCP`, `UDP`

### Outils de débogage

* Utilisez [gdb](/other/tools?id=gdb) pour déboguer les programmes `Linux`
* Utilisez [strace](/other/tools?id=strace) pour suivre les appels système des processus
* Utilisez [tcpdump](/other/tools?id=tcpdump) pour suivre le processus de communication réseau
* Autres outils système `Linux`, tels que `ps`, [lsof](/other/tools?id=lsof), `top`, `vmstat`, `netstat`, `sar`, `ss`, etc.

## Object of class Swoole\Curl\Handler could not be converted to int

Lors de l'utilisation de [SWOOLE_HOOK_CURL](/runtime?id=swoole_hook_curl), une erreur se produit :

```bash
PHP Notice:  Object of class Swoole\Curl\Handler could not be converted to int

PHP Warning: curl_multi_add_handle() expects parameter 2 to be resource, object given
```

La raison en est que le curl hooké n'est plus un type de ressource, mais un objet, donc il n'est pas possible de le convertir en type int.

!> Le problème de la conversion en `int` est suggéré d'être signalé à l'équipe SDK pour modifier le code. Dans PHP8, le curl n'est plus un type de ressource, mais un objet.

Il y a trois solutions possibles :

1. Ne pas activer [SWOOLE_HOOK_CURL](/runtime?id=swoole_hook_curl). Cependant, à partir de la version [v4.5.4](/version/log?id=v454), [SWOOLE_HOOK_ALL](/runtime?id=swoole_hook_all) comprend par défaut [SWOOLE_HOOK_CURL](/runtime?id=swoole_hook_curl), vous pouvez l'ajouter avec `SWOOLE_HOOK_ALL ^ SWOOLE_HOOK_CURL` pour désactiver [SWOOLE_HOOK_CURL](/runtime?id=swoole_hook_curl).

2. Utiliser la SDK Guzzle, qui peut remplacer le Handler pour réaliser la coroutinisation.

3. À partir de la version Swoole `v4.6.0`, vous pouvez utiliser [SWOOLE_HOOK_NATIVE_CURL](/runtime?id=swoole_hook_native_curl) à la place de [SWOOLE_HOOK_CURL](/runtime?id=swoole_hook_curl).

## Lorsqu'il est utilisé conjointement avec la coroutinisation en une seule commande et Guzzle 7.0+, les résultats de la demande sont directement affichés dans la console :id=hook_guzzle

Le code de reproduction est le suivant

```php
// composer require guzzlehttp/guzzle
include __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;
use Swoole\Coroutine;

// Pour les versions antérieures à v4.5.4
//Coroutine::set(['hook_flags' => SWOOLE_HOOK_ALL | SWOOLE_HOOK_CURL]);
Coroutine::set(['hook_flags' => SWOOLE_HOOK_ALL]);
Coroutine\run(function () {
    $client = new Client();
    $url = 'http://baidu.com';
    $res = $client->request('GET', $url);
    var_dump($res->getBody()->getContents());
});

// Les résultats de la demande seront affichés directement, et non imprimés
//<html>
//<meta http-equiv="refresh" content="0;url=http://www.baidu.com/">
//</html>
//string(0) ""
```

!> La méthode de résolution est la même que pour le problème précédent. Cependant, ce problème a été corrigé dans la version Swoole >= `v4.5.8`.


## Error: No buffer space available[55]

Cette erreur peut être ignorée. Il s'agit simplement de l'option [socket_buffer_size](/server/setting?id=socket_buffer_size) étant trop grande pour certains systèmes, ce qui n'affecte pas l'exécution du programme.


## La taille maximale des demandes GET/POST


### Demande GET maximale de 8192

La demande GET ne contient qu'une tête HTTP, et Swoole utilise un tampon de mémoire fixe de 8K de taille, qui ne peut pas être modifié. Si la demande n'est pas une demande HTTP correcte, une erreur se produira. Le niveau inférieur lancera l'erreur suivante :

```bash
WARN swReactorThread_onReceive_http_request: http header is too long.
```

### Téléchargement de fichiers avec POST

La taille maximale est limitée par l'option de configuration [package_max_length](/server/setting?id=package_max_length), qui est par défaut de 2M. Vous pouvez appeler la méthode [Server->set](/server/methods?id=set) pour passer une nouvelle valeur et modifier la taille. Comme Swoole utilise la mémoire entièrement en mémoire, si la taille est trop grande, cela pourrait entraîner l'épuisement des ressources du serveur par un grand nombre de demandes en parallèle.

Méthode de calcul : `Occupation de mémoire maximale` = `Nombre maximal de demandes en parallèle` * `package_max_length`
