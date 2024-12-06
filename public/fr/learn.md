#的基础知识

##四种设置回调函数的方式

* **匿名函数**

```php
$server->on('Request', function ($req, $resp) use ($a, $b, $c) {
    echo "hello world";
});
```
!> 可以使用`use`向匿名函数传递参数

* **类静态方法**

```php
class A
{
    static function test($req, $resp)
    {
        echo "hello world";
    }
}
$server->on('Request', 'A::Test');
$server->on('Request', array('A', 'Test'));
```
!> 对应的静态方法必须为`public`

* **函数**

```php
function my_onRequest($req, $resp)
{
    echo "hello world";
}
$server->on('Request', 'my_onRequest');
```

* **对象方法**

```php
class A
{
    function test($req, $resp)
    {
        echo "hello world";
    }
}

$object = new A();
$server->on('Request', array($object, 'test'));
```

!> 对应的方法必须为`public`

##同步IO/异步IO

在`Swoole4+`下所有的业务代码都是同步写法（`Swoole1.x`时代才支持异步写法，现在已经移除了异步客户端，对应的需求完全可以用协程客户端实现），完全没有心智负担，符合人类思维习惯，但同步的写法底层可能有`同步IO/异步IO`之分。

无论是同步IO/异步IO，`Swoole/Server`都可以维持大量`TCP`客户端连接(参考[SWOOLE_PROCESS模式](/learn?id=swoole_process))。你的服务是阻塞还是非阻塞不需要单独的配置某些参数，取决于你的代码里面是否有同步IO的操作。

**什么是同步IO：**
 
简单的例子就是执行到`MySQL->query`的时候，这个进程什么事情都不做，等待MySQL返回结果，返回结果后再向下执行代码，所以同步IO的服务并发能力是很差的。

**什么样的代码是同步IO：**

 * 没有开启[一键协程化](/runtime)的时候，那么你的代码里面绝大部分涉及IO的操作都是同步IO的，协程化后，就会变成异步IO，进程不会傻等在那里，参考[协程调度](/coroutine?id=协程调度)。
 * 有些`IO`是没法一键协程化，没法将同步IO变为异步IO的，例如`MongoDB`(相信`Swoole`会解决这个问题)，需要写代码时候注意。

!> [协程](/coroutine) 是为了提高并发的，如果我的应用就没有高并发，或者必须要用某些无法异步化IO的操作(例如上文的MongoDB)，那么你完全可以不开启[一键协程化](/runtime)，关闭[enable_coroutine](/server/setting?id=enable_coroutine)，多开一些`Worker`进程，这就是和`Fpm/Apache`是一样的模型了，值得一提的是由于`Swoole`是常驻进程的，即使同步IO性能也会有很大提升，实际应用中也有很多公司这样做。

###同步IO转换成异步IO

[上小节](/learn?id=同步io异步io)介绍了什么是同步/异步IO，在`Swoole`下面，有些情况同步的`IO`操作是可以转换成异步IO的。
 
 - 开启[一键协程化](/runtime)后，`MySQL`、`Redis`、`Curl`等操作会变成异步IO。
 - 利用[Event](/event)模块手动管理事件，将fd加到[EventLoop](/learn?id=什么是eventloop)里面，变成异步IO，例子：

```php
//利用inotify监控文件变化
$fd = inotify_init();
//将$fd添加到Swoole的EventLoop
Swoole\Event::add($fd, function () use ($fd){
    $var = inotify_read($fd);//文件发生变化后读取变化的文件。
    var_dump($var);
});
```

上述代码如果不调用`Swoole\Event::add`将IO异步化，直接`inotify_read()`将阻塞Worker进程，其他的请求将得不到处理。

 - 使用`Swoole\Server`的[sendMessage()](/server/methods?id=sendMessage)方法进行进程间通讯，默认`sendMessage`是同步IO，但有些情况是会被`Swoole`转换成异步IO，用[User进程](/server/methods?id=addprocess)举例：

```php
$serv = new Swoole\Server("0.0.0.0", 9501, SWOOLE_BASE);
$serv->set(
    [
        'worker_num' => 1,
    ]
);

$serv->on('pipeMessage', function ($serv, $src_worker_id, $data) {
    echo "#{$serv->worker_id} message from #$src_worker_id: $data\n";
    sleep(10);//不接收sendMessage发来的数据，缓冲区将很快写满
});

$serv->on('receive', function (swoole_server $serv, $fd, $reactor_id, $data) {

});

//情况1：同步IO(默认行为)
$userProcess = new Swoole\Process(function ($worker) use ($serv) {
    while (1) {
        var_dump($serv->sendMessage("big string", 0));//默认情况下，缓存区写满后，此处会阻塞
    }
}, false);

//情况2：通过enable_coroutine参数开启UserProcess进程的协程支持，为了防止其他协程得不到 EventLoop 的调度，
//Swoole会把sendMessage转换成异步IO
$enable_coroutine = true;
$userProcess = new Swoole\Process(function ($worker) use ($serv) {
    while (1) {
        var_dump($serv->sendMessage("big string", 0));//缓存区写满后，不会阻塞进程,会报错
    }
}, false, 1, $enable_coroutine);

//情况3：在UserProcess进程里面如果设置了异步回调(例如设置定时器、Swoole\Event::add等)，
//为了防止其他回调函数得不到 EventLoop 的调度，Swoole会把sendMessage转换成异步IO
$userProcess = new Swoole\Process(function ($worker) use ($serv) {
    swoole_timer_tick(2000, function ($interval) use ($worker, $serv) {
        echo "timer\n";
    });
    while (1) {
        var_dump($serv->sendMessage("big string", 0));//缓存区写满后，不会阻塞进程,会报错
    }
}, false);

$serv->addProcess($userProcess);

$serv->start();
```

 - 同理，[Task进程](/learn?id=taskworker进程)通过`sendMessage()`进程间通讯是一样的，不同的是task进程开启协程支持是通过Server的[task_enable_coroutine](/server/setting?id=task_enable_coroutine)配置开启，并且不存在`情况3`，也就是说task进程不会因为开启异步回调就将sendMessage异步IO。

##什么是EventLoop

所谓`EventLoop`，即事件循环，可以简单的理解为epoll_wait，会把所有要发生事件的句柄（fd）加入到`epoll_wait`中，这些事件包括可读，可写，出错等。

对应的进程就阻塞在`epoll_wait`这个内核函数上，当发生了事件(或超时)后`epoll_wait`这个函数就会结束阻塞返回结果，就可以回调相应的PHP函数，例如，收到客户端发来的数据，回调`onReceive`回调函数。

当有大量的fd放入到了`epoll_wait`中，并且同时产生了大量的事件，`epoll_wait`函数返回的时候就会挨个调用相应的回调函数，叫做一轮事件循环，即IO多路复用，然后再次阻塞调用`epoll_wait`进行下一轮事件循环。
## Question de la frontière des paquets TCP

En l'absence de concurrence, le code dans [le démarrage rapide](/start/start_tcp_server) peut fonctionner normalement, mais lorsque la concurrence est élevée, il y a un problème de frontière des paquets TCP. Le protocole TCP résout au niveau des mécanismes de base les problèmes de séquence et de retransmission des paquets manquants du protocole UDP, mais en comparaison avec UDP, il apporte de nouveaux problèmes. Le protocole TCP est de type flux, les paquets n'ont pas de frontière, et les applications utilisant le TCP pour communiquer doivent faire face à ces défis, communément appelés problème de paquets collés TCP.

Comme la communication TCP est de type flux, lors de la réception d'un grand paquet, il peut être divisé en plusieurs paquets envoyés. Plusieurs appels à `Send` peuvent également être fusionnés en un seul pour l'envoi. Il est donc nécessaire de procéder à deux opérations pour résoudre ce problème :

* Dépaquetage : Le `Server` reçoit plusieurs paquets et doit les décomposer.
* Rempaquetage : Les données reçues par le `Server` ne constituent qu'une partie d'un paquet, il est nécessaire de stocker les données et de les fusionner en un paquet complet.

Par conséquent, lors de la communication réseau TCP, il est nécessaire de définir un protocole de communication. Les protocoles de communication réseau TCP couramment utilisés comprennent `HTTP`, `HTTPS`, `FTP`, `SMTP`, `POP3`, `IMAP`, `SSH`, `Redis`, `Memcache`, `MySQL`, etc.

Il convient de mentionner que Swoole intègre de nombreuses interprétations courantes des protocoles pour résoudre les problèmes de frontière des paquets TCP des serveurs de ces protocoles. Il suffit de configurer simplement, voir [ouvre_protocol_http](/server/setting?id=open_http_protocol), [ouvre_protocol_http2](/http_server?id=open_http2_protocol), [ouvre_protocol_websocket](/server/setting?id=open_websocket_protocol), [ouvre_protocol_mqtt](/server/setting?id=open_mqtt_protocol).

En plus des protocoles couramment utilisés, il est également possible de définir des protocoles personnalisés. Swoole prend en charge deux types de protocoles de communication réseau personnalisés.

* **Protocole avec indicateur de fin EOF**

Le principe de traitement du protocole EOF est d'ajouter une série de caractères spéciaux à la fin de chaque paquet pour indiquer que le paquet est terminé. Par exemple, `Memcache`, `FTP`, `SMTP` utilisent tous `\r\n` comme indicateur de fin. Lors de l'envoi de données, il suffit d'ajouter `\r\n` à la fin du paquet. Lors du traitement du protocole EOF, il est essentiel d'assurer qu'il n'y ait pas d'EOF dans le milieu des paquets, sinon cela entraînera des erreurs de dépaquetage.

Dans le code du `Server` et du `Client`, il suffit de configurer deux paramètres pour utiliser le protocole EOF pour le traitement.

```php
$server->set(array(
    'open_eof_split' => true,
    'package_eof' => "\r\n",
));
$client->set(array(
    'open_eof_split' => true,
    'package_eof' => "\r\n",
));
```

Mais la configuration EOF mentionnée ci-dessus est moins performante, Swoole parcourra chaque byte pour voir si la donnée est `\r\n`. Une autre façon de configurer est la suivante.

```php
$server->set(array(
    'open_eof_check' => true,
    'package_eof' => "\r\n",
));
$client->set(array(
    'open_eof_check' => true,
    'package_eof' => "\r\n",
));
```
Cette configuration est beaucoup plus performante car elle ne parcourt pas les données, mais elle ne résout que le problème du dépaquetage. Il n'est pas possible de résoudre le problème du rempaquetage, c'est-à-dire que vous pourriez recevoir plusieurs demandes de la part du client dans une seule invocation de `onReceive`, et vous devez alors dépaqueter manuellement, par exemple en utilisant `explode("\r\n", $data)`. La plus grande utilisation de cette configuration est pour les services qui répondent aux demandes (par exemple, les commandes entrées dans une console), où il n'est pas nécessaire de se soucier de la division des données. Cela est dû au fait que le client, après avoir lancé une demande, doit attendre que le serveur rende la réponse à la demande actuelle avant de lancer une nouvelle demande, il ne envoie pas deux demandes simultanément.

* **Protocole avec tête fixe + corps de paquet**

La méthode de tête fixe est très universelle et peut souvent être vue dans les programmes d'serveur. La caractéristique de ce protocole est que chaque paquet est toujours composé de deux parties : une tête et un corps. La tête indique le corps ou la longueur totale du paquet avec un champ, et la longueur est généralement représentée par un entier de 2 ou 4 bytes. Après avoir reçu la tête, le serveur peut contrôler précisément combien de données doit recevoir pour obtenir un paquet complet en fonction de la valeur de la longueur. La configuration de Swoole peut bien soutenir ce protocole, avec des paramètres flexibles pour faire face à toutes les situations.

Dans le serveur, le traitement des paquets se fait dans la fonction de rappel [onReceive](/server/events?id=onreceive). Lorsque le traitement du protocole est configuré, l'événement [onReceive](/server/events?id=onreceive) ne sera déclenché que lorsqu'un paquet complet est reçu. Après avoir configuré le traitement du protocole, le client n'a plus besoin de passer une longueur lors de l'appel à la fonction [$client->recv()](/client?id=recv), et la fonction `recv` retourne après avoir reçu un paquet complet ou en cas d'erreur.

```php
$server->set(array(
    'open_length_check' => true,
    'package_max_length' => 81920,
    'package_length_type' => 'n', //voir php pack()
    'package_length_offset' => 0,
    'package_body_offset' => 2,
));
```

!> Pour la signification spécifique de chaque configuration, veuillez consulter la section [Configuration](/server/setting?id=open_length_check) du chapitre "Serveurs/Clients".


## Qu'est-ce que IPC

Il existe de nombreuses façons de communiquer entre deux processus sur le même hôte (abrégé en IPC). Sous Swoole, deux méthodes sont utilisées : `Unix Socket` et `sysvmsg`. Voici une présentation détaillée des deux :


- **Unix Socket**  

    Nom complet : UNIX Domain Socket, abrégé en UDS, utilise l'API des sockets (socket, bind, listen, connect, read, write, close, etc.), contrairement à TCP/IP, il n'est pas nécessaire de specifier IP et port, mais plutôt un nom de fichier (par exemple `/tmp/php-fcgi.sock` entre FPM et Nginx). L'UDS est une communication en mémoire complète implémentée par le noyau Linux, sans aucun coût IO. Dans un test de lecture et d'écriture de 1024 bytes par processus, 1 million de communications prennent seulement 1,02 seconde, et c'est très puissant. Par défaut, Swoole utilise cette méthode d'IPC.  
      
    * **`SOCK_STREAM` et `SOCK_DGRAM`**  

        - Sous Swoole, il y a deux types de communication UDS, `SOCK_STREAM` et `SOCK_DGRAM`, qui peuvent être simplement compris comme la différence entre TCP et UDP. Lorsque vous utilisez le type `SOCK_STREAM`, vous devez également prendre en compte le problème de la frontière des paquets TCP.  
        - Lorsque vous utilisez le type `SOCK_DGRAM`, vous n'avez pas à vous soucier du problème de la frontière des paquets TCP, chaque donnée envoyée avec `send()` a une frontière, vous recevez la quantité de données envoyée, sans perte de paquets ou de désordre pendant le transport, et l'ordre d'écriture `send` et de lecture `recv` est complètement identique. Après un succès de la fonction `send`, vous pouvez certainement recevoir avec `recv`.  

    Lorsque les données d'IPC sont petites, il est très approprié d'utiliser cette méthode `SOCK_DGRAM`. **En raison du limitation de 64k par paquet IP, la taille des données envoyées avec `SOCK_DGRAM` ne doit pas dépasser 64k, et il est important de noter que si la vitesse de réception est trop lente et que le tampon système est plein, les paquets seront jetés, car UDP permet la perte de paquets. Vous pouvez augmenter le tampon en conséquence.**


- **sysvmsg**
     
    C'est le "queue de messages" fourni par Linux, cette méthode d'IPC utilise un nom de fichier comme clé pour la communication. Cette méthode est très rigide et n'est pas très utilisée dans les projets pratiques, donc elle n'est pas expliquée en détail ici.

    * **Deux scénarios où cette méthode d'IPC est utile:**

        - Prevenir la perte de données, si tout le service tombe en panne, les messages dans la queue seront toujours là et peuvent continuer à être consommés, **mais il y a aussi le problème des données sales**.
        - Il est possible de délivrer des données depuis l'extérieur, par exemple, le processus Worker sous Swoole peut envoyer des tâches au processus Task via la queue de messages, et d'autres processus peuvent également envoyer des tâches dans la queue pour que le Task les consomme, et il est même possible d'ajouter des messages manuellement à la queue depuis la ligne de commande.
## Différences et liens entre le processus Maître, les threads Reactor, les processus Worker, les processus Task et le processus Manager :id=diff-process


### Processus Maître

* Le processus Maître est un processus multithreadé, voir [Diagramme de structure de processus/thread](/server/init?id=diagramme_structure_processus_thread)


### Threads Reactor

* Les threads Reactor sont des threads créés dans le processus Maître
* Ils sont responsables de maintenir les connexions TCP avec les clients, de traiter l'IO réseau, de traiter les protocoles, d'envoyer et de recevoir des données
* Ils ne exécutent aucun code PHP
* Ils bufferisent, assemblent et découpent les données envoyées par les clients TCP en un paquet de demande complet


### Processus Worker

* Ils acceptent les paquets de demande livrés par les threads Reactor et exécutent des fonctions de rappel PHP pour traiter les données
* Ils génèrent des données de réponse qui sont envoyées en parallèle aux threads Reactor, qui à leur tour les envoient aux clients TCP
* Ils peuvent fonctionner en mode asynchrone non bloquant ou en mode synchrone bloquant
* Les Workers fonctionnent en plusieurs processus


### Processus TaskWorker

* Ils acceptent les tâches livrées par les processus Worker via les méthodes Swoole\Server->[task](/server/methods?id=task)/[taskwait](/server/methods?id=taskwait)/[taskCo](/server/methods?id=taskCo)/[taskWaitMulti](/server/methods?id=taskWaitMulti)
* Ils traitent les tâches et renvoient les résultats aux processus Worker (en utilisant [Swoole\Server->finish](/server/methods?id=finish))
* Ils fonctionnent entièrement en mode synchrone bloquant
* Les TaskWorkers fonctionnent en plusieurs processus, [exemple complet de tâche](/start/start_task)


### Processus Manager

* Ils sont responsables de la création/récupération des processus Worker/Task

Leur relation peut être comprise comme suit : le Reactor est à la place du nginx, le Worker est à la place du PHP-FPM. Les threads Reactor traitent asynchronement et en parallèle les demandes réseau, puis les转发 aux processus Worker pour traitement. Le Reactor et le Worker communiquent entre eux via [unixSocket](/learn?id=qu'est-ce que l'IPC).

Dans les applications PHP-FPM, il est fréquent de mettre en place une tâche asynchrone dans des files d'attente comme Redis, et de lancer en arrière-plan certains processus PHP pour traiter ces tâches asynchronement. Le TaskWorker fourni par Swoole est un ensemble plus complet qui intègre le lancement des tâches, les files d'attente et la gestion des processus de traitement des tâches PHP. Il est très simple d'implémenter le traitement des tâches asynchrones grâce aux API fournies en bas niveau. De plus, le TaskWorker peut renvoyer un retour d'état au Worker après l'exécution des tâches.

Les Reactor, Worker et TaskWorker de Swoole peuvent être étroitement combinés pour fournir des modes d'utilisation plus avancés.

Pour faire un parallèle plus populaire, supposons que le Server soit une usine, alors le Reactor est le vendeur qui accepte les commandes des clients. Le Worker est le travailleur qui, une fois la commande acceptée par le vendeur, travaille pour produire ce que le client veut. Le TaskWorker peut être considéré comme un employé administratif qui peut aider le Worker à faire des tâches diverses, permettant au Worker de se concentrer sur son travail principal.

Comme illustré :

![process_demo](_images/server/process_demo.png)


## Présentation des trois modes de fonctionnement du Server

Dans le troisième paramètre du constructeur de Swoole\Server, vous pouvez saisir trois valeurs de constante -- [SWOOLE_BASE](/learn?id=swoole_base), [SWOOLE_PROCESS](/learn?id=swoole_process) et [SWOOLE_THREAD](/learn?id=swoole_thread), qui seront présentées séparément ci-dessous pour différencier les avantages et les inconvénients de ces trois modes.


### SWOOLE_PROCESS

Dans le mode SWOOLE_PROCESS, toutes les connexions TCP des clients d'un Server SWOOLE sont établies avec le [processus principal](/learn?id=reactor-thread). La mise en œuvre interne est assez complexe et utilise une grande quantité de communication inter-processus et de mécanismes de gestion de processus. Il est adapté aux scénarios où la logique métier est très complexe. Swoole offre un mécanisme de gestion de processus complet et des mécanismes de protection de mémoire.
Dans les cas où la logique métier est extrêmement complexe, il peut également fonctionner de manière stable sur le long terme.

Swoole offre dans les threads Reactor une fonction Buffer qui peut faire face à un grand nombre de connexions lentes et à des clients malveillants qui écrivent des caractères un par un.

#### Avantages du mode de processus :

* Les connexions et les envois de demandes de données sont séparés, ce qui évite que le Worker ne soit déséquilibré en raison de la grande quantité de données de certaines connexions par rapport à d'autres.
* Lorsque le Worker rencontre une erreur fatale, les connexions ne sont pas coupées.
* Il est possible d'atteindre une concurrency par connexion unique, en maintenant seulement quelques connexions TCP, et les demandes peuvent être traitées en parallèle par plusieurs Workers.

#### Inconvénients du mode de processus :

* Il y a un double coût de IPC, le processus maître doit communiquer avec les Workers en utilisant des sockets Unix (/learn?id=qu'est-ce que l'IPC).
* Le mode SWOOLE_PROCESS ne prend pas en charge PHP ZTS, dans ce cas, il est nécessaire d'utiliser SWOOLE_BASE ou d'établir true pour la configuration [single_thread](/server/setting?id=single_thread).


### SWOOLE_BASE

Ce mode SWOOLE_BASE correspond au serveur asynchrone non bloquant traditionnel. Il est entièrement conforme à des programmes tels que Nginx et Node.js.

Le paramètre [worker_num](/server/setting?id=worker_num) est toujours valide pour le mode BASE et lancera plusieurs processus Worker.

Lorsqu'une demande de connexion TCP arrive, tous les processus Worker se disputent cette connexion, et finalement, un processus Worker réussira à établir directement une connexion TCP avec le client. Après cela, toutes les communications de données pour cette connexion sont directement avec ce Worker et ne passent pas par le thread Reactor du processus maître pour être redirigées.

* Dans le mode BASE, il n'y a pas de rôle de processus maître, seulement un rôle de processus Manager (/learn?id=manager-process).
* Chaque processus Worker assume à la fois les responsabilités du thread Reactor et du processus Worker dans le mode SWOOLE_PROCESS.
* Dans le mode BASE, le processus Manager est optionnel. Lorsque `worker_num` est égal à 1 et que les caractéristiques Task et MaxRequest ne sont pas utilisées, Swoole créera directement un seul processus Worker sans créer de processus Manager en dessous.

#### Avantages du mode BASE :

* Le mode BASE n'a pas de coût IPC, ce qui est plus performant.
* Le code du mode BASE est plus simple et moins susceptible d'erreur.

#### Inconvénients du mode BASE :

* Les connexions TCP sont maintenues par les processus Worker, donc lorsque le processus Worker tombe en panne, toutes les connexions de ce Worker sont fermées.
* Un petit nombre de connexions TCP longues ne peuvent pas utiliser tous les processus Worker.
* Les connexions TCP sont liées aux Workers, donc dans les applications de connexions longues, si la quantité de données de certaines connexions est grande, le processus Worker concerné sera très chargé. Cependant, si la quantité de données de certaines connexions est petite, le processus Worker sera très peu chargé, et il n'est pas possible d'équilibrer les différents processus Worker.
* Si la fonction de rappel contient des opérations bloquantes, cela peut entraîner le retour du serveur à un mode synchrone, ce qui peut facilement conduire à un problème de surcharge de la file d'attente backlog du TCP (/server/setting?id=backlog).

#### Scénarios adaptés au mode BASE :

Si les connexions entre les clients ne nécessitent pas d'interaction, vous pouvez utiliser le mode BASE. Par exemple, Memcache, serveur HTTP, etc.

#### Limitations du mode BASE :

Dans le mode BASE, à l'exception des méthodes [send](/server/methods?id=send) et [close](/server/methods?id=close), aucune autre méthode du serveur ne prend en charge l'exécution à travers les processus.

!> Dans la version 4.5.x du BASE, seule la méthode send prend en charge l'exécution à travers les processus ; dans la version 4.6.x, seules les méthodes send et close prennent en charge l'exécution à travers les processus.
### SWOOLE_THREAD

SWOOLE_THREAD est un nouveau mode de fonctionnement introduit dans `Swoole 6.0` qui permet d'activer le mode de service multithreadé grâce au mode `PHP zts`.

Le paramètre [worker_num](/server/setting?id=worker_num) est toujours valide pour le mode `THREAD`, mais plutôt que de créer des processus multiples, cela créera des threads multiples et lancera plusieurs threads Worker.

Il n'y aura qu'un seul processus, et les processus enfants se transformeront en threads pour gérer les demandes des clients.

#### Avantages du mode THREAD :
* La communication entre processus est plus simple, sans consommation supplémentaire de communication IPC.
* La débogage est plus pratique, car il n'y a qu'un seul processus, ce qui rend `gdb -p` plus simple.
* Il y a les commodités de la programmation asynchrone IO avec des coroutines, ainsi que les avantages de l'exécution parallèle des threads et de la pile de mémoire partagée.

#### Désavantages du mode THREAD :
* En cas de crash ou d'appel à Process::exit(), tout le processus quittera, nécessitant une logique de récupération des erreurs telles que le redémarrage des tentatives d'erreur et la reconnexion en cas de déconnexion chez les clients, ainsi que l'utilisation de supervisor et de docker/k8s pour redémarrer automatiquement le processus après son arrêt.
* Les opérations avec `ZTS` et les verrous peuvent avoir des surcoûts supplémentaires, et la performance pourrait être d'environ 10% inférieure à celle du modèle de parallélisme multiprocessus NTS. Pour les services sans état, il est toujours conseillé d'utiliser le mode de fonctionnement multiprocessus NTS.
* Il n'est pas possible de transmettre des objets et des ressources entre les threads.

#### Scénarios adaptatifs au mode THREAD :
* Le mode THREAD est plus efficace pour le développement de serveurs de jeux et de serveurs de communication.

## Quelles sont les différences entre Process, Process\Pool et UserProcess :id=process-diff

### Process

[Process](/process/process) est un module de gestion de processus fourni par Swoole, qui remplace `pcntl` PHP.
 
* Il est possible de réaliser facilement la communication entre processus ;
* Il prend en charge la redirection des entrées et sorties standards, dans les sous-processus, `echo` ne affichera pas l'écran mais écrira dans le tuyau, et l'entrée au clavier peut être redirectionnée pour lire les données via le tuyau ;
* Il offre une [exec](/process/process?id=exec) interface, permettant aux processus créés d'exécuter d'autres programmes et de communiquer facilement avec le processus parent PHP original ;

!> Dans un environnement de coroutines, il n'est pas possible d'utiliser le module `Process`, il est possible d'utiliser une combinaison de `runtime hook` + `proc_open` pour y parvenir, voir [Gestion de processus par coroutines](/coroutine/proc_open)


### Process\Pool

[Process\Pool](/process/process_pool) est une classe PHP qui encapsule le module de gestion de processus du serveur, permettant son utilisation dans du code PHP avec la gestionnaire de processus Swoole.

Dans des projets pratiques, il est souvent nécessaire d'écrire des scripts à long terme, tels que des consommateurs de files d'attente multiprocessus basés sur `Redis`, `Kafka`, `RabbitMQ`, des grappes de recherche multiprocessus, etc. Les développeurs doivent utiliser des extensions telles que `pcntl` et `posix` pour réaliser la programmation multiprocessus, mais cela nécessite également une solide connaissance de la programmation système Linux, sinon il est facile de rencontrer des problèmes. L'utilisation du gestionnaire de processus fourni par Swoole peut considérablement simplifier le travail de programmation de scripts multiprocessus.

* Garantir la stabilité des processus de travail ;
* Prendre en charge le traitement des signaux ;
* Prendre en charge la fonction de livraison de messages de files d'attente et de sockets TCP ;

### UserProcess

`UserProcess` est un processus de travail personnalisé ajouté avec [addProcess](/server/methods?id=addprocess), généralement utilisé pour créer un processus de travail spécial, tel que le monitoring, le rapport ou d'autres tâches spéciales.

Bien que `UserProcess` soit hébergé par le [Process Manager](/learn?id=manager进程), il est relativement indépendant du [Worker Process](/learn?id=worker进程) par rapport à celui-ci, et est utilisé pour exécuter des fonctionnalités personnalisées.
