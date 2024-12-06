# Remarques sur la programmation

Cette section expliquera en détail les différences entre la programmation coopérative et la programmation synchrone ainsi que les précautions à prendre.


## Remarques

* Ne pas exécuter de fonctions telles que `sleep` ou d'autres fonctions de sommeil dans le code, car cela peut entraîner le blocage de tout le processus ; dans les coopératives, utilisez [Co::sleep()](/coroutine/system?id=sleep) ou utilisez `sleep` après avoir [co-réalisé le processus](/runtime) ; pour référence : [l'impact du sleep/usleep](/getting_started/notice?id=sleepusleep的影响)
* `exit/die` est dangereux, il peut entraîner la sortie du processus `Worker` ; pour référence : [l'impact de la fonction exit/die](/getting_started/notice?id=exitdie函数的影响)
* Vous pouvez capturer les erreurs fatales en utilisant `register_shutdown_function` pour effectuer des tâches de nettoyage en cas d'arrêt anormal du processus ; pour référence : [capturer les erreurs fatales pendant le fonctionnement du serveur](/getting_started/notice?id=捕获server运行期致命错误)
* Si une exception est lancée dans le code PHP, elle doit être capturée avec un `try/catch` dans la fonction de rappel, sinon cela peut entraîner la sortie du processus de travail ; pour référence : [capturer les exceptions et les erreurs](/getting_started/notice?id=捕获异常和错误)
* Ne pas utiliser `set_exception_handler`, il faut utiliser la méthode `try/catch` pour gérer les exceptions ;
* Les processus `Worker` ne doivent pas partager le même client de service réseau telles que `Redis` ou `MySQL`. Le code pour créer des connexions à `Redis/MySQL` peut être placé dans la fonction de rappel `onWorkerStart`. Pour référence : [Est-il possible de partager une seule connexion Redis ou MySQL](/question/use?id=是否可以共用一个redis或mysql连接)


## Programmation coopérative

En utilisant la caractéristique `Coroutine`, veuillez lire attentivement les [remarques sur la programmation coopérative](/coroutine/notice)


## Programmation parallèle

Veuillez noter que contrairement au mode de blocage synchrone, le mode `coopératif` permet l'exécution **parallèle** du programme. Pendant la même période, le `Server` peut avoir plusieurs demandes, il est donc **absolument nécessaire que l'application crée des ressources et des contextes différents pour chaque client ou demande**. Sinon, il peut y avoir des erreurs de données et logiques entre différents clients et demandes.


## Définition répétée de classes/fonctions

C'est une erreur courante chez les nouveaux. Comme Swoole est en mémoire permanente, les fichiers contenant la définition des classes/fonctions ne sont pas libérés après le chargement. Par conséquent, lorsque vous incluez un fichier PHP contenant une classe/fonction, vous devez utiliser `include_once` ou `require_once`, sinon cela peut entraîner une erreur fatale `cannot redeclare function/class`.


## Gestion de la mémoire

!> Il est particulièrement important de faire attention lors de l'écriture de `Server` ou d'autres processus résidents.

La gestion de la mémoire des守护进程 PHP et des programmes Web ordinaires est complètement différente. Après le démarrage du `Server`, les principes de base de la gestion de la mémoire sont les mêmes que ceux du programme PHP-CLI ordinaire. Pour plus d'informations, veuillez consulter l'article sur la gestion de la mémoire du `Zend VM`.


### Variables locales

Après le retour de la fonction d'événement, tous les objets locaux et variables seront entièrement récupérés et n'ont pas besoin d'être unset. Si la variable est un type de ressource, la ressource correspondante sera également libérée par le PHP de base.

```php
function test()
{
	$a = new Object;
	$b = fopen('/data/t.log', 'r+');
	$c = new swoole_client(SWOOLE_SYNC);
	$d = new swoole_client(SWOOLE_SYNC);
	global $e;
	$e['client'] = $d;
}
```

* `$a`, `$b`, `$c` sont toutes des variables locales, lorsque cette fonction `return`, ces trois variables seront immédiatement libérées, la mémoire correspondante sera immédiatement libérée, et le handle de fichier ouvert sera immédiatement fermé.
* `$d` est également une variable locale, mais avant de `return`, elle a été sauvegardée dans la variable globale `$e`, donc elle ne sera pas libérée. Lorsque `unset($e['client'])` est exécuté et qu'aucune autre variable PHP n'est toujours en référence à la variable `$d`, alors `$d` sera libérée.


### Variables globales

Dans le PHP, il y a trois types de variables globales.

* Les variables déclarées avec la keyword `global`
* Les variables statiques des classes et des fonctions déclarées avec la keyword `static`
* Les variables superglobales PHP, y compris `$_GET`, `$_POST`, `$GLOBALS`, etc.

Les variables globales et les objets, les variables statiques des classes, les variables conservées sur l'objet `Server` ne seront pas libérées. Il est à la charge du programmeur de gérer la destruction de ces variables et objets.

```php
class Test
{
	static $array = array();
	static $string = '';
}

function onReceive($serv, $fd, $reactorId, $data)
{
	Test::$array[] = $fd;
	Test::$string .= $data;
}
```

* Dans les fonctions de rappel d'événement, il est particulièrement important de faire attention aux valeurs de type `array` des variables non locales, certaines opérations telles que `TestClass::$array[] = "string"` peuvent entraîner une fuite de mémoire, et en cas grave, une surcharge de mémoire peut survenir. Il est donc nécessaire de faire attention au nettoyage des grands tableaux lorsque cela est nécessaire.

* Dans les fonctions de rappel d'événement, il est nécessaire de faire attention à la mémoire lors de l'opération de concaténation des chaînes de variables non locales, comme `TestClass::$string .= $data`, car cela peut entraîner une fuite de mémoire, et en cas grave, une surcharge de mémoire peut survenir.


### Solutions

* Pour les programmes de `Server` synchrone, bloquant et responsifs sans état, il est possible de configurer [max_request](/server/setting?id=max_request) et [task_max_request](/server/setting?id=task_max_request). Lorsque le processus [Worker](/learn?id=worker进程) / [Task进程](/learn?id=taskworker进程) se termine ou atteint la limite de tâches, le processus s'arrête automatiquement, et toutes les variables/objets/ressources de ce processus seront libérées et récupérées.
* Il est possible d'utiliser `unset` pour nettoyer les variables et récupérer les ressources en temps opportun dans le code interne `onClose` ou en établissant des horloges.


## Isolement des processus

L'isolation des processus est également un problème fréquent rencontré par de nombreux nouveaux. Pourquoi la modification de la valeur d'une variable globale n'affecte-t-elle pas ? La raison en est que les variables globales sont dans différents processus, et l'espace de mémoire est isolé, donc inefficace.

Ainsi, lors du développement de programmes `Server` avec Swoole, il est nécessaire de comprendre le problème de l'isolation des processus. Les différents processus Worker d'un programme Server Swoole sont isolés, et l'opération des variables globales, des horloges et des écouteurs d'événements est uniquement valide dans le processus actuel.

* Les variables PHP dans différents processus ne sont pas partagées, même les variables globales, si leur valeur est modifiée dans le processus A, elle est inefficace dans le processus B.
* Si vous avez besoin de partager des données entre différents processus Worker, vous pouvez utiliser des outils tels que `Redis`, `MySQL`, `fichiers`, `Swoole\Table`, `APCu`, `shmget`, etc.
* Les handles de fichiers dans différents processus sont isolés, donc les connexions Socket créées ou les fichiers ouverts dans le processus A sont inefficaces dans le processus B, même si vous envoie son fd au processus B, il n'est pas utilisable.

Exemple :

```php
$server = new Swoole\Http\Server('127.0.0.1', 9500);

$i = 1;

$server->on('Request', function ($request, $response) {
	global $i;
    $response->end($i++);
});

$server->start();
```

Dans un serveur multiprocessus, bien que `$i` soit une variable globale (avec `global`), en raison de l'isolation des processus, supposons qu'il y ait 4 processus de travail, lorsque `$i++` est exécuté dans le processus de travail 1, en réalité, seule la valeur de `$i` dans le processus de travail 1 devient 2, tandis que les valeurs des autres trois processus de travail restent à 1.

La bonne façon de faire est d'utiliser les structures de données Swoole fournies telles que [Swoole\Atomic](/memory/atomic) ou [Swoole\Table](/memory/table). Comme dans l'exemple ci-dessus, on peut utiliser `Swoole\Atomic` pour mettre en œuvre cela.

```php
$server = new Swoole\Http\Server('127.0.0.1', 9500);

$atomic = new Swoole\Atomic(1);

$server->on('Request', function ($request, $response) use ($atomic) {
    $response->end($atomic->add(1));
});

$server->start();
```

!> Les données de la structure atomique Swoole sont basées sur la mémoire partagée, et lorsque la méthode `add` est utilisée pour ajouter 1, cela est également valide dans les autres processus de travail.

Les composants Swoole fournis tels que [Table](/memory/table), [Atomic](/memory/atomic), [Lock](/memory/lock) peuvent être utilisés pour la programmation multiprocessus, mais doivent être créés avant le démarrage du `Server`. De plus, les connexions TCP maintenues par le `Server` peuvent également être opérées à travers les processus, comme `Server->send` et `Server->close`.
## nettoyage du cache de stat

Le niveau inférieur de PHP a ajouté un `Cache` aux appels système `stat`, et lorsque des fonctions telles que `stat`, `fstat`, `filemtime`, etc., sont utilisées, il est possible que le niveau inférieur touche au cache et retourne des données historiques.

Il est possible d'utiliser la fonction [clearstatcache](https://www.php.net/manual/en/function.clearstatcache.php) pour nettoyer le cache de `stat` des fichiers.


## GENERateurs de nombres aléatoires mt_rand

Dans Swoole, si `mt_rand` est appelé à l'intérieur du processus parent, les résultats de `mt_rand` appelés à l'intérieur de différents processus enfants seront les mêmes, il est donc nécessaire de rappeler `mt_srand` à chaque processus enfant pour réinitialiser le générateur de nombres aléatoires.

!> Des fonctions PHP telles que `shuffle` et `array_rand`, qui dépendent de la génération de nombres aléatoires, seront également affectées  

Exemple :

```php
mt_rand(0, 1);

// Début
$worker_num = 16;

// Fork des processus
for($i = 0; $i < $worker_num; $i++) {
    $process = new Swoole\Process('child_async', false, 2);
    $pid = $process->start();
}

// Exécution asynchrone des processus
function child_async(Swoole\Process $worker) {
    mt_srand(); // Réinitialisation du générateur
    echo mt_rand(0, 100).PHP_EOL;
    $worker->exit();
}
```


## Capture des exceptions et erreurs


### Exceptions/Erreurs capturables

Il existe généralement trois types d'exceptions/erreurs capturables dans PHP

1. `Error` : Un type spécial d'erreur lancé par le noyau PHP, comme l'absence d'une classe, d'une fonction, une erreur de paramètre de fonction, etc. L'utilisation de la classe `Error` dans le code PHP pour lancer une exception n'est pas recommandée.
2. `Exception` : La classe de base des exceptions que les développeurs d'applications devraient utiliser.
3. `ErrorException` : Cette classe de base est spécifiquement chargée de transformer les informations de `Warning`/`Notice`, etc., de PHP en exceptions via la fonction `set_error_handler`. Il est prévu dans l'avenir de PHP que toutes les `Warning`/`Notice` soient transformées en exceptions, afin que les programmes PHP puissent gérer plus efficacement et de manière contrôlée diverses erreurs.

!> Toutes les classes mentionnées ci-dessus implémentent l'interface `Throwable`, ce qui signifie que vous pouvez capturer toutes les exceptions/erreurs pouvant être lancées avec `try {} catch(Throwable $e) {}`.

Exemple 1 :
```php
try {
	test();
} 
catch(Throwable $e) {
	var_dump($e);
}
```
Exemple 2 :
```php
try {
	test();
}
catch (Error $e) {
	var_dump($e);
}
catch(Exception $e) {
	var_dump($e);
}
```


### Erreurs fatales et exceptions non capturables

Un niveau important d'erreur PHP, comme une exception/erreur non capturée, une pénurie de mémoire ou certaines erreurs de compilation (classe héritée inexistante), lancera une `Fatal Error` au niveau `E_ERROR`. Cela se produit lorsque le programme PHP rencontre une erreur irréversible, et le programme PHP ne peut pas capturer cet niveau d'erreur, il ne peut que traiter certaines opérations par la suite avec `register_shutdown_function`.


### Capture des exceptions/erreurs runtime dans les coroutines

Dans la programmation coroutine de Swoole4, si une erreur est lancée dans le code d'une coroutine, cela entraînera l'arrêt de tout le processus et l'arrêt de l'exécution de toutes les coroutines du processus. Au niveau supérieur de la coroutine, on peut d'abord effectuer un `try/catch` pour capturer les exceptions/erreurs, se terminant uniquement par la coroutine en échec.

```php
use Swoole\Coroutine;
use function Swoole\Coroutine\run;

run(function () {
    Coroutine::create(function () {
        try {
            call_user_func($func);
        }
        catch (Error $e) {
            var_dump($e);
        }
        catch(Exception $e) {
            var_dump($e);
        }
    });

    // L'erreur d'une coroutine n'affectera pas l'autre coroutine
    Coroutine::create(function () {
        Coroutine::sleep(5);
        echo 2;
    });
});
```


### Capture des erreurs fatales runtime du serveur

Une fois qu'une erreur fatale se produit pendant l'opération du serveur, les connexions des clients ne peuvent pas recevoir de réponse. Par exemple, pour un serveur web, si une erreur fatale se produit, il faut envoyer une information d'erreur HTTP 500 aux clients.

Dans PHP, on peut capturer les erreurs fatales avec les deux fonctions `register_shutdown_function` et `error_get_last`, et envoyer l'information de l'erreur aux connexions des clients.

Voici un exemple de code spécifique :

```php
$http = new Swoole\Http\Server("127.0.0.1", 9501);
$http->on('request', function ($request, $response) {
    register_shutdown_function(function () use ($response) {
        $error = error_get_last();
        var_dump($error);
        switch ($error['type'] ?? null) {
            case E_ERROR :
            case E_PARSE :
            case E_CORE_ERROR :
            case E_COMPILE_ERROR :
                // log ou envoyer :
                // error_log($message);
                // $server->send($fd, $error['message']);
                $response->status(500);
                $response->end($error['message']);
                break;
        }
    });
    exit(0);
});
$http->start();
```


## Impact de l'utilisation


### Impact de sleep/usleep

Dans les programmes d'IO asynchrone, il est interdit d'utiliser `sleep/usleep/time_sleep_until/time_nanosleep`. (Dans la suite, `sleep` désigne toutes les fonctions de sommeil.)

* La fonction `sleep` rend le processus endormi et bloqué
* Le système d'exploitation ne réveillera le processus actuel qu'après le temps spécifié
* Pendant le sommeil, seul un signal peut interrompre
* Étant donné que la gestion des signaux de Swoole est basée sur `signalfd`, même l'envoi d'un signal ne peut pas interrompre le sommeil

Les fonctions de Swoole telles que [Swoole\Event::add](/event?id=add), [Swoole\Timer::tick](/timer?id=tick), [Swoole\Timer::after](/timer?id=after) et [Swoole\Process::signal](/process/process?id=signal) cessent de fonctionner après le sommeil du processus. Le [Swoole\Server](/server/tcp_init) ne peut plus traiter de nouvelles demandes.

#### Exemple

```php
$server = new Swoole\Server("127.0.0.1", 9501);
$server->set(['worker_num' => 1]);
$server->on('receive', function ($server, $fd, $reactor_id, $data) {
    sleep(100);
    $server->send($fd, 'Swoole: '.$data);
});
$server->start();
```

!> L'exécution de la fonction `sleep` dans l'événement [onReceive](/server/events?id=onreceive) fait que le serveur ne peut plus recevoir de demandes des clients pendant 100 secondes.


### Impact de la fonction exit/die

Dans les programmes Swoole, l'utilisation de `exit/die` est interdite. Si `exit/die` est utilisé dans le code PHP, le processus actuel de [Worker](/learn?id=worker进程), de [Task进程](/learn?id=taskworker进程), de [User进程](/server/methods?id=addprocess) et du processus Swoole\Process s'arrêtera immédiatement.

L'utilisation de `exit/die` entraînera l'arrêt anormal du processus Worker, qui sera à nouveau redémarré par le processus maître, ce qui finira par entraîner un arrêt et un redémarrage continus du processus et la création de nombreux journaux d'alarme.

Il est conseillé d'utiliser `try/catch` à la place de `exit/die` pour interrompre l'exécution et sortir de la pile des appels PHP.

```php
Swoole\Coroutine\run(function () {
    try
    {
        exit(0);
    } catch (Swoole\ExitException $e)
    {
        echo $e->getMessage()."\n";
    }
});
```

!> `Swoole\ExitException` est directement pris en charge dans la version 4.1.0 de Swoole et versions supérieures, permettant l'utilisation de `exit` dans les coroutines et les serveurs. Dans ce cas, le niveau inférieur lancera automatiquement une exception capturable `Swoole\ExitException`, et les développeurs peuvent l'attraper et mettre en œuvre la logique de sortie similaire à celle du PHP native à des endroits nécessaires. Pour plus d'informations sur l'utilisation, veuillez consulter [Sortie de la coroutine](/coroutine/notice?id=sortie-de-la-coroutine);

La gestion des exceptions est plus amicale que celle de `exit/die`, car les exceptions sont contrôlables, tandis que `exit/die` est incontrôlable. En mettant en place un `try/catch` à l'extérieur, il est possible de capturer les exceptions et d'arrêter uniquement la tâche actuelle. Le processus Worker peut continuer à traiter de nouvelles demandes, tandis que l'utilisation de `exit/die` entraînera l'arrêt direct du processus, et toutes les variables et ressources sauvegardées par le processus actuel seront détruites. Si le processus contient d'autres tâches à traiter, celles rencontrant `exit/die` seront également abandonnées.
### L'impact des boucles `while`

Dans les programmes asynchrones, si une boucle infinie est rencontrée, les événements ne peuvent pas être déclenchés. Les programmes d'I/O asynchrones utilisent le modèle `Reactor`, qui exige un sondage en `reactor->wait` pendant son exécution. Si une boucle infinie se produit, le contrôle du programme est alors entre les mains de la `while`, le `reactor` ne peut pas prendre le contrôle, il ne peut pas détecter les événements, et donc la fonction de rappel d'événement I/O ne peut pas être déclenchée.

!> Un code à forte intensité computationnelle qui ne contient aucune opération I/O ne peut donc pas être considéré comme bloquant  

#### Exemple de programme

```php
$server = new Swoole\Server('127.0.0.1', 9501);
$server->set(['worker_num' => 1]);
$server->on('receive', function ($server, $fd, $reactorId, $data) {
    $i = 0;
    while(1)
    {
        $i++;
    }
    $server->send($fd, 'Swoole: '.$data);
});
$server->start();
```

!> Dans l'événement [onReceive](/server/events?id=onreceive), une boucle infinie est exécutée, le `server` ne peut plus recevoir de demandes de client, il doit attendre la fin de la boucle pour pouvoir continuer à traiter de nouveaux événements.
