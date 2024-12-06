# API des coroutines

> Il est conseillé de consulter d'abord l' [aperçu](/coroutine) pour comprendre les concepts de base des coroutines avant de lire cette section.


## Méthodes


### set()

Configuration des coroutines, pour établir les options liées aux coroutines.

```php
Swoole\Coroutine::set(array $options);
```


Paramètres | Stabilité depuis cette version | Effet 
---|---|---
max_coroutine | - | Définit le nombre maximal de coroutines globales, au-delà duquel il est impossible de créer de nouvelles coroutines, et cela sera remplacé par [server->max_coroutine](/server/setting?id=max_coroutine) sous Server.
stack_size/c_stack_size | - | Définit la taille de la mémoire de la pile C initiale pour chaque coroutine, par défaut 2M
log_level | v4.0.0 | Niveau de journalisation [voir](/consts?id=niveau_de_journalisation)
trace_flags | v4.0.0 | Étiquettes de suivi [voir](/consts?id=étiquettes_de_suivi)
socket_connect_timeout | v4.2.10 | Timeout pour l'établissement d'une connexion, **voir [règles de timeout client](/coroutine_client/init?id=règles_de_timeout)**
socket_read_timeout | v4.3.0 | Timeout de lecture, **voir [règles de timeout client](/coroutine_client/init?id=règles_de_timeout)**
socket_write_timeout | v4.3.0 | Timeout d'écriture, **voir [règles de timeout client](/coroutine_client/init?id=règles_de_timeout)**
socket_dns_timeout | v4.4.0 | Timeout pour la résolution DNS, **voir [règles de timeout client](/coroutine_client/init?id=règles_de_timeout)**
socket_timeout | v4.2.10 | Timeout de sending/receiving, **voir [règles de timeout client](/coroutine_client/init?id=règles_de_timeout)**
dns_cache_expire | v4.2.11 | Définit la durée de vie du cache DNS de swoole, en secondes, par défaut 60 secondes
dns_cache_capacity | v4.2.11 | Définit la capacité du cache DNS de swoole, par défaut 1000
hook_flags | v4.4.0 | Configuration de l'étendue des hooks pour une coroutine en un clic, voir [coroutinisation en un clic](/runtime)
enable_preemptive_scheduler | v4.4.0 | Activer le planificateur préemptif des coroutines, avec une limite maximale d'exécution de 10ms pour les coroutines, ce qui remplacera la configuration [ini](/other/config)
dns_server | v4.5.0 | Définit le serveur DNS utilisé pour les requêtes DNS, par défaut "8.8.8.8"
exit_condition | v4.5.0 | Passer une `callable`, qui retourne un booléen, pour personnaliser la condition de sortie du réacteur. Par exemple : si vous souhaitez que le programme ne se ferme que lorsque le nombre de coroutines est égal à zéro, vous pouvez écrire `Co::set(['exit_condition' => function () {return Co::stats()['coroutine_num'] === 0;}]);`
enable_deadlock_check | v4.6.0 | Activer ou désactiver la détection de deadlock des coroutines, par défaut activé
deadlock_check_disable_trace | v4.6.0 | Activer ou désactiver l'affichage des traces de détection de deadlock des coroutines
deadlock_check_limit | v4.6.0 | Limiter le nombre maximal d'affichages lors de la détection de deadlock des coroutines
deadlock_check_depth | v4.6.0 | Limiter le nombre de traces de pile retournées lors de la détection de deadlock des coroutines
max_concurrency | v4.8.2 | Nombre maximal de demandes en parallèle


### getOptions()

Obtenir les options de configuration des coroutines établies.

!> Disponible pour les versions Swoole >= `v4.6.0`

```php
Swoole\Coroutine::getOptions(): null|array;
```


### create()

Créer une nouvelle coroutine et l'exécuter immédiatement.

```php
Swoole\Coroutine::create(callable $function, ...$args): int|false
go(callable $function, ...$args): int|false // Référence à la configuration use_shortname dans php.ini
```

* **Paramètres**

    * **`callable $function`**
      * **Fonctionnalité** : Code exécuté par la coroutine, doit être `callable`, le nombre total de coroutines que le système peut créer est limité par la configuration [server->max_coroutine](/server/setting?id=max_coroutine)
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

* **Valeurs de retour**

    * Return false en cas d'échec de création
    * Return l'ID de la coroutine créée avec succès

!> Étant donné que le code des sous-coroutines est exécuté en priorité par le niveau inférieur, `Coroutine::create` ne retourne que lorsque les sous-coroutines sont suspendues, et le code de la coroutine actuelle continue d'être exécuté.

  * **Ordre d'exécution**

    Dans une coroutine, utiliser `go` pour créer de nouvelles coroutines en nested. Comme Swoole utilise un modèle de processus et de thread uniques pour les coroutines :

    * Les sous-coroutines créées avec `go` s'exécuteront en priorité, et lorsqu'elles sont terminées ou suspendues, elles reviendront à la coroutine parent pour continuer l'exécution du code
    * Si une sous-coroutine est suspendue après que la coroutine parent s'est terminée, cela n'affecte pas l'exécution des sous-coroutines

    ```php
    \Co\run(function() {
        go(function () {
            Co::sleep(3.0);
            go(function () {
                Co::sleep(2.0);
                echo "co[3] end\n";
            });
            echo "co[2] end\n";
        });

        Co::sleep(1.0);
        echo "co[1] end\n";
    });
    ```

* **Coûts des coroutines**

  Chaque coroutine est indépendante et nécessite un espace de mémoire séparé (mémoire de pile), dans la version `PHP-7.2`, le niveau inférieur alloue 8K de `stack` pour stocker les variables des coroutines, la taille d'un `zval` est de 16 octets, donc une `stack` de 8K peut stocker jusqu'à 512 variables. Si la mémoire de pile des coroutines dépasse 8K, le `ZendVM` se dilatera automatiquement.

  La mémoire de pile des coroutines est libérée lorsqu'une coroutine se termine.

  * Pour PHP-7.1 et PHP-7.0, la mémoire de pile par défaut est de 256K
  * Il est possible d'appeler `Co::set(['stack_size' => 4096])` pour modifier la taille par défaut de la mémoire de pile



### defer()

`defer` est utilisé pour la libération des ressources, il sera appelé avant **la fermeture de la coroutine** (c'est-à-dire après l'exécution du code de la fonction de la coroutine), même en cas d'exception levée, les `defer` déjà enregistrés seront exécutés.

!> Disponible pour les versions Swoole >= 4.2.9

```php
Swoole\Coroutine::defer(callable $function);
defer(callable $function); // API courte
```

!> Il est important de noter que leur ordre d'appel est inverse (dernier en premier sorti), c'est-à-dire que ceux qui sont enregistrés en dernier sont exécutés en premier, ce qui correspond à la logique correcte de libération des ressources, car les ressources les plus récemment demandées peuvent dépendre des ressources précédemment demandées, comme libérer les ressources demandées en premier avant que les ressources demandées plus tard ne puissent être libérées.

  * **Exemple**

```php
go(function () {
    defer(function () use ($db) {
        $db->close();
    });
});
```


### exists()

Déterminer si une coroutine spécifiée existe.

```php
Swoole\Coroutine::exists(int $cid = 0): bool
```

!> Disponible pour les versions Swoole >= v4.3.0

  * **Exemple**

```php
\Co\run(function () {
    go(function () {
        go(function () {
            Co::sleep(0.001);
            var_dump(Co::exists(Co::getPcid())); // 1: true
        });
        go(function () {
            Co::sleep(0.003);
            var_dump(Co::exists(Co::getPcid())); // 3: false
        });
        Co::sleep(0.002);
        var_dump(Co::exists(Co::getPcid())); // 2: false
    });
});
```


### getCid()

Obtenir l'ID unique de la coroutine actuelle, également appelé `getuid`, qui est un entier positif unique au sein du processus.

```php
Swoole\Coroutine::getCid(): int
```

* **Valeurs de retour**

    * Return l'ID de la coroutine actuelle en cas de succès
    * Return -1 si l'on n'est pas dans un environnement de coroutine

### getPcid()

Obtenir l'ID parent du coroutine actuel.

```php
Swoole\Coroutine::getPcid([$cid]): int
```

!> Version Swoole >= v4.3.0

* **Paramètres**

    * **`int $cid`**
      * **Fonction** : ID du coroutine, paramètre par défaut, peut être fourni avec l'ID de certains coroutines pour obtenir leur ID parent
      * **Valeur par défaut** : Coroutine actuel
      * **Autres valeurs** : Aucun

  * **Exemples**

```php
var_dump(Co::getPcid());
\Co\run(function () {
    var_dump(Co::getPcid());
    go(function () {
        var_dump(Co::getPcid());
        go(function () {
            var_dump(Co::getPcid());
            go(function () {
                var_dump(Co::getPcid());
            });
            go(function () {
                var_dump(Co::getPcid());
            });
            go(function () {
                var_dump(Co::getPcid());
            });
        });
        var_dump(Co::getPcid());
    });
    var_dump(Co::getPcid());
});
var_dump(Co::getPcid());

// --EXPECT--

// bool(false)
// int(-1)
// int(1)
// int(2)
// int(3)
// int(3)
// int(3)
// int(1)
// int(-1)
// bool(false)
```

!> Appeler `getPcid` sur des coroutines non imbriquées retournera `-1` (créées à partir de l'espace non coroutine)  
Appeler `getPcid` à l'intérieur d'un coroutine non coroutine retournera `false` (pas de parent coroutine)  
L'ID `0` est réservé et ne apparaîtra pas dans les valeurs de retour

!> Les coroutines n'ont pas de relation parent-enfant substantielle, elles sont isolées et fonctionnent indépendamment entre elles, cet ID `Pcid` peut être considéré comme l'ID de la coroutine qui a créé la coroutine actuelle

  * **Utilisations**

    * **Faire des appels en chaîne à plusieurs coroutines**

```php
\Co\run(function () {
    go(function () {
        $ptrace = Co::getBackTrace(Co::getPcid());
        // balababala
        var_dump(array_merge($ptrace, Co::getBackTrace(Co::getCid())));
    });
});
```


### getContext()

Obtenir l'objet de contexte actuel du coroutine.

```php
Swoole\Coroutine::getContext([int $cid = 0]): Swoole\Coroutine\Context
```

!> Version Swoole >= v4.3.0

* **Paramètres**

    * **`int $cid`**
      * **Fonction** : ID du coroutine, paramètre optionnel
      * **Valeur par défaut** : ID du coroutine actuel
      * **Autres valeurs** : Aucun

  * **Effets**

    * Le contexte est automatiquement nettoyé après la sortie du coroutine (s'il n'y a pas d'autres coroutines ou de références à des variables globales)
    * Sans coût d'enregistrement et d'appel de `defer` (pas besoin de registration de méthodes de nettoyage, pas besoin d'appel de fonctions pour nettoyer)
    * Sans coût de calcul de hachage du contexte basé sur l'implémentation PHP des tableaux (avantage certain lorsque le nombre de coroutines est énorme)
    * `Co\Context` utilise `ArrayObject`, répondant à divers besoins d'storage (c'est à la fois un objet et peut être manipulé comme un tableau)

  * **Exemples**

```php
function func(callable $fn, ...$args)
{
    go(function () use ($fn, $args) {
        $fn(...$args);
        echo 'Coroutine#' . Co::getCid() . ' exit' . PHP_EOL;
    });
}

/**
* Compatibility for lower version
* @param object|Resource $object
* @return int
*/
function php_object_id($object)
{
    static $id = 0;
    static $map = [];
    $hash = spl_object_hash($object);
    return $map[$hash] ?? ($map[$hash] = ++$id);
}

class Resource
{
    public function __construct()
    {
        echo __CLASS__ . '#' . php_object_id((object)$this) . ' constructed' . PHP_EOL;
    }

    public function __destruct()
    {
        echo __CLASS__ . '#' . php_object_id((object)$this) . ' destructed' . PHP_EOL;
    }
}

$context = new Co\Context();
assert($context instanceof ArrayObject);
assert(Co::getContext() === null);
func(function () {
    $context = Co::getContext();
    assert($context instanceof Co\Context);
    $context['resource1'] = new Resource;
    $context->resource2 = new Resource;
    func(function () {
        Co::getContext()['resource3'] = new Resource;
        Co::yield();
        Co::getContext()['resource3']->resource4 = new Resource;
        Co::getContext()->resource5 = new Resource;
    });
});
Co::resume(2);

Swoole\Event::wait();

// --EXPECT--
// Resource#1 constructed
// Resource#2 constructed
// Resource#3 constructed
// Coroutine#1 exit
// Resource#2 destructed
// Resource#1 destructed
// Resource#4 constructed
// Resource#5 constructed
// Coroutine#2 exit
// Resource#5 destructed
// Resource#3 destructed
// Resource#4 destructed
```


### yield()

Faire volontairement céder le droit d'exécution du coroutine actuel. Au lieu de la [gestion des coroutines basée sur l'I/O](/coroutine?id=gestion-des-coroutines).

Cette méthode a un autre alias : `Coroutine::suspend()`.

!> Doit être utilisé en couple avec la méthode `Coroutine::resume()`. Après que la coroutine a `yield`, elle doit être `resumée` par une autre coroutine extérieure, sinon cela entraînera une fuite de coroutines, la coroutine suspendue ne s'exécutera jamais.

```php
Swoole\Coroutine::yield();
```

  * **Exemples**

```php
$cid = go(function () {
    echo "co 1 start\n";
    Co::yield();
    echo "co 1 end\n";
});

go(function () use ($cid) {
    echo "co 2 start\n";
    Co::sleep(0.5);
    Co::resume($cid);
    echo "co 2 end\n";
});
Swoole\Event::wait();
```


### resume()

Résumer manuellement une coroutine donnée pour qu'elle continue à fonctionner, pas basé sur la [gestion des coroutines basée sur l'I/O](/coroutine?id=gestion-des-coroutines).

!> Lorsque la coroutine est dans un état suspendu, une autre coroutine peut utiliser `resume` pour réveiller à nouveau la coroutine actuelle

```php
Swoole\Coroutine::resume(int $coroutineId);
```

* **Paramètres**

    * **`int $coroutineId`**
      * **Fonction** : ID de la coroutine à résumer
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

  * **Exemples**

```php
$id = go(function(){
    $id = Co::getuid();
    echo "start coro $id\n";
    Co::suspend();
    echo "resume coro $id @1\n";
    Co::suspend();
    echo "resume coro $id @2\n";
});
echo "start to resume $id @1\n";
Co::resume($id);
echo "start to resume $id @2\n";
Co::resume($id);
echo "main\n";
Swoole\Event::wait();

// --EXPECT--
// start coro 1
// start to resume 1 @1
// resume coro 1 @1
// start to resume 1 @2
// resume coro 1 @2
// main
```


### list()

Itérer sur toutes les coroutines dans le processus actuel.

```php
Swoole\Coroutine::list(): Swoole\Coroutine\Iterator
Swoole\Coroutine::listCoroutines(): Swoole\Coroitine\Iterator
```

!> Pour les versions inférieures à `v4.3.0`, utilisez `listCoroutines`, la nouvelle version a raccourci le nom de la méthode et a fait de `listCoroutines` un alias. `list` est disponible à partir de la version `v4.1.0`.

* **Valeurs de retour**

    * Retourne un itérateur, qui peut être itéré avec `foreach`, ou converti en tableau avec `iterator_to_array`

```php
$coros = Swoole\Coroutine::listCoroutines();
foreach($coros as $cid)
{
    var_dump(Swoole\Coroutine::getBackTrace($cid));
}
```


### stats()

Obtenir les statistiques des coroutines.

```php
Swoole\Coroutine::stats(): array
```

* **Valeurs de retour**


clé |效用
---|---
event_num | Nombre d'événements actuel du reactor
signal_listener_num | Nombre de监听ers de signal actuel
aio_task_num | Nombre de tâches IO asynchrones (ici, IO signifie fichier ou DNS, et ne comprend pas l'autre IO réseau, la même chose pour les autres)
aio_worker_num | Nombre de threads de travail IO asynchrones
c_stack_size | Taille de la pile C de chaque coroutine
coroutine_num | Nombre de coroutines actuellement en cours d'exécution
coroutine_peak_num | Nombre maximum de coroutines en cours d'exécution
coroutine_last_cid | ID de la dernière coroutine créée

  * **Exemples**

```php
var_dump(Swoole\Coroutine::stats());

array(1) {
  ["c_stack_size"]=>
  int(2097152)
  ["coroutine_num"]=>
  int(132)
  ["coroutine_peak_num"]=>
  int(2)
}
```
### getBackTrace()

Obtenir l'appel de la pile des fonctions de coroutines.

```php
Swoole\Coroutine::getBackTrace(int $cid = 0, int $options = DEBUG_BACKTRACE_PROVIDE_OBJECT, int $limit = 0): array
```

!> Version Swoole >= v4.1.0

* **Paramètres**

    * **`int $cid`**
      * **Fonction** : `CID` de la coroutine
      * **Valeur par défaut** : `CID` de la coroutine actuelle
      * **Autres valeurs** : Aucun

    * **`int $options`**
      * **Fonction** : Établir les options
      * **Valeur par défaut** : `DEBUG_BACKTRACE_PROVIDE_OBJECT` 【Est-ce que l'index de `object` est fourni】
      * **Autres valeurs** : `DEBUG_BACKTRACE_IGNORE_ARGS` 【Est-ce que l'index des args est ignoré, y compris tous les paramètres des fonctions/méthodes, ce qui peut économiser l'espace de stockage】

    * **`int $limit`**
      * **Fonction** : Limiter le nombre de trames de la pile de retour
      * **Valeur par défaut** : `0`
      * **Autres valeurs** : Aucun

* **Valeurs de retour**

    * Si la coroutine spécifiée n'existe pas, retournera `false`
    * Retourne un tableau en cas de succès, format identique à la valeur de retour de la fonction [debug_backtrace](https://www.php.net/manual/zh/function.debug-backtrace.php)

  * **Exemple**

```php
function test1() {
    test2();
}

function test2() {
    while(true) {
        Co::sleep(10);
        echo __FUNCTION__." \n";
    }
}
\Co\run(function () {
    $cid = go(function () {
        test1();
    });

    go(function () use ($cid) {
        while(true) {
            echo "BackTrace[$cid]:\n-----------------------------------------------\n";
            //Retourne un tableau, doit être formaté pour l'affichage
            var_dump(Co::getBackTrace($cid))."\n";
            Co::sleep(3);
        }
    });
});
Swoole\Event::wait();
```


### printBackTrace()

Imprimer l'appel de la pile des fonctions de coroutines. Paramètres et `getBackTrace` identiques.

!> Disponible pour la version Swoole >= `v4.6.0`

```php
Swoole\Coroutine::printBackTrace(int $cid = 0, int $options = DEBUG_BACKTRACE_PROVIDE_OBJECT, int $limit = 0);
```


### getElapsed()

Obtenir le temps de fonctionnement d'une coroutine afin d'analyser les statistiques ou de trouver des coroutines zombis

!> Disponible pour la version Swoole >= `v4.5.0`

```php
Swoole\Coroutine::getElapsed([$cid]): int
```
* **Paramètres**

    * **`int $cid`**
      * **Fonction** : Optionnel, `CID` de la coroutine
      * **Valeur par défaut** : `CID` de la coroutine actuelle
      * **Autres valeurs** : Aucun

* **Valeurs de retour**

    * Temps de fonctionnement d'une coroutine en浮点数, précision à la milliseconde


### cancel()

Utilisé pour annuler une coroutine, mais ne peut pas être utilisé pour annuler la coroutine actuelle

!> Disponible pour la version Swoole >= `v4.7.0`

```php
Swoole\Coroutine::cancel($cid): bool
```
* **Paramètres**

    * **`int $cid`**
        * **Fonction** : `CID` de la coroutine
        * **Valeur par défaut** : Aucun
        * **Autres valeurs** : Aucun

* **Valeurs de retour**

    * Retourne `true` en cas d'échec, et `false` en cas d'échec
    * Pour vérifier l'échec de l'annulation, appelez [swoole_last_error()](/functions?id=swoole_last_error) pour obtenir des informations d'erreur


### isCanceled()

Utilisé pour déterminer si l'opération actuelle a été annulée manuellement

!> Disponible pour la version Swoole >= `v4.7.0`

```php
Swoole\Coroutine::isCanceled(): bool
```

* **Valeurs de retour**

    * Si l'annulation est terminée normalement par une annulation manuelle, retournera `true`, sinon `false`

#### Exemple

```php
use Swoole\Coroutine;
use Swoole\Coroutine\System;
use function Swoole\Coroutine\run;
use function Swoole\Coroutine\go;

run(function () {
    $chan = new Coroutine\Channel(1);
    $cid = Coroutine::getCid();
    go(function () use ($cid) {
        System::sleep(0.002);
        assert(Coroutine::cancel($cid) === true);
    });

    assert($chan->push("hello world [1]", 100) === true);
    assert(Coroutine::isCanceled() === false);
    assert($chan->errCode === SWOOLE_CHANNEL_OK);

    assert($chan->push("hello world [2]", 100) === false);
    assert(Coroutine::isCanceled() === true);
    assert($chan->errCode === SWOOLE_CHANNEL_CANCELED);

    echo "Done\n";
});
```


### enableScheduler()

Ouvrir temporairement le découpage en temps des coroutines.

!> Disponible pour la version Swoole >= `v4.4.0`

```php
Swoole\Coroutine::enableScheduler();
```


### disableScheduler()

Fermer temporairement le découpage en temps des coroutines.

!> Disponible pour la version Swoole >= `v4.4.0`

```php
Swoole\Coroutine::disableScheduler();
```


### getStackUsage()

Obtenir l'utilisation de la mémoire de la pile PHP actuelle.

!> Disponible pour la version Swoole >= `v4.8.0`

```php
Swoole\Coroutine::getStackUsage([$cid]): int
```

* **Paramètres**

    * **`int $cid`**
        * **Fonction** : Optionnel, `CID` de la coroutine
        * **Valeur par défaut** : `CID` de la coroutine actuelle
        * **Autres valeurs** : Aucun


### join()

Exécuter plusieurs coroutines en parallèle.

!> Disponible pour la version Swoole >= `v4.8.0`

```php
Swoole\Coroutine::join(array $cid_array, float $timeout = -1): bool
```

* **Paramètres**

    * **`array $cid_array`**
        * **Fonction** : Tableau des `CID` des coroutines à exécuter
        * **Valeur par défaut** : Aucun
        * **Autres valeurs** : Aucun

    * **`float $timeout`**
        * **Fonction** : Temps total de dépassement, retournera immédiatement après le dépassement. Cependant, les coroutines en cours d'exécution continueront à s'exécuter jusqu'à la fin et ne seront pas interrompues
        * **Valeur par défaut** :-1
        * **Autres valeurs** : Aucun

* **Valeurs de retour**

    * Retourne `true` en cas de succès, et `false` en cas d'échec
    * Pour vérifier l'échec de l'annulation, appelez [swoole_last_error()](/functions?id=swoole_last_error) pour obtenir des informations d'erreur

* **Exemple d'utilisation**

```php
use Swoole\Coroutine;

use function Swoole\Coroutine\go;
use function Swoole\Coroutine\run;

run(function () {
    $status = Coroutine::join([
        go(function () use (&$result) {
            $result['baidu'] = strlen(file_get_contents('https://www.baidu.com/'));
        }),
        go(function () use (&$result) {
            $result['google'] = strlen(file_get_contents('https://www.google.com/'));
        })
    ], 1);
    var_dump($result, $status, swoole_strerror(swoole_last_error(), 9));
});
```


## Functions


### batch()

Exécuter plusieurs coroutines en parallèle et obtenir les valeurs de retour de ces méthodes de coroutines à travers un tableau.

!> Disponible pour la version Swoole >= `v4.5.2`

```php
Swoole\Coroutine\batch(array $tasks, float $timeout = -1): array
```

* **Paramètres**

    * **`array $tasks`**
      * **Fonction** : Tableau des fonctions回调 à passer, si une `key` est spécifiée, les valeurs de retour seront également associées à cette `key`
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

    * **`float $timeout`**
      * **Fonction** : Temps total de dépassement, retournera immédiatement après le dépassement. Cependant, les coroutines en cours d'exécution continueront à s'exécuter jusqu'à la fin et ne seront pas interrompues
      * **Valeur par défaut** :-1
      * **Autres valeurs** : Aucun

* **Valeurs de retour**

    * Retourne un tableau contenant les valeurs de retour des回调. Si dans le paramètre `$tasks`, une `key` est spécifiée, les valeurs de retour seront également associées à cette `key`

* **Exemple d'utilisation**

```php
use Swoole\Coroutine;
use function Swoole\Coroutine\batch;

Coroutine::set(['hook_flags' => SWOOLE_HOOK_ALL]);

$start_time = microtime(true);
Coroutine\run(function () {
    $use = microtime(true);
    $results = batch([
        'file_put_contents' => function () {
            return file_put_contents(__DIR__ . '/greeter.txt', "Hello,Swoole.");
        },
        'gethostbyname' => function () {
            return gethostbyname('localhost');
        },
        'file_get_contents' => function () {
            return file_get_contents(__DIR__ . '/greeter.txt');
        },
        'sleep' => function () {
            sleep(1);
            return true; // Retourne NULL car le temps de dépassement est de 0,1 seconde, retournera immédiatement après le dépassement. Cependant, les coroutines en cours d'exécution continueront à s'exécuter jusqu'à la fin et ne seront pas interrompues.
        },
        'usleep' => function () {
            usleep(1000);
            return true;
        },
    ], 0.1);
    $use = microtime(true) - $use;
    echo "Utilisation {$use}s, Résultats:\n";
    var_dump($results);
});
$end_time =  microtime(true) - $start_time;
echo "Utilisation {$end_time}s, Terminé\n";
```
### parallel()

Exécuter plusieurs coroutines en parallèle.

!> La version Swoole doit être >= `v4.5.3` pour être utilisable

```php
Swoole\Coroutine\parallel(int $n, callable $fn): void
```

* **Paramètres**

    * **`int $n`**
      * **Fonction** : Établir le nombre maximal de coroutines à `$n`
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

    * **`callable $fn`**
      * **Fonction** : La fonction de rappel à exécuter pour chaque élément
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

* **Exemple d'utilisation**

```php
use Swoole\Coroutine;
use Swoole\Coroutine\System;
use function Swoole\Coroutine\parallel;

$start_time = microtime(true);
Coroutine\run(function () {
    $use = microtime(true);
    $results = [];
    parallel(2, function () use (&$results) {
        System::sleep(0.2);
        $results[] = System::gethostbyname('localhost');
    });
    $use = microtime(true) - $use;
    echo "Utilisation de {$use}s, Résultats :\n";
    var_dump($results);
});
$end_time =  microtime(true) - $start_time;
echo "Utilisation de {$end_time}s, Terminé\n";
```

### map()

Similaire à [array_map](https://www.php.net/manual/zh/function.array-map.php), applique une fonction de rappel à chaque élément d'un tableau.

!> La version Swoole doit être >= `v4.5.5` pour être utilisable

```php
Swoole\Coroutine\map(array $list, callable $fn, float $timeout = -1): array
```

* **Paramètres**

    * **`array $list`**
      * **Fonction** : Tableau sur lequel la fonction `$fn` est appliquée
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

    * **`callable $fn`**
      * **Fonction** : La fonction de rappel à exécuter pour chaque élément du `$list`
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

    * **`float $timeout`**
      * **Fonction** : Temps total de timeout, si dépassé, la fonction retourne immédiatement. Cependant, les coroutines en cours d'exécution continueront jusqu'à la fin sans être interrompues
      * **Valeur par défaut** :-1
      * **Autres valeurs** : Aucun

* **Exemple d'utilisation**

```php
use Swoole\Coroutine;
use function Swoole\Coroutine\map;

function fatorial(int $n): int
{
    return array_product(range($n, 1));
}

Coroutine\run(function () {
    $results = map([2, 3, 4], 'fatorial'); 
    print_r($results);
});
```

### deadlock_check()

Détection de verrouillage de coroutines, appelée lors de l'exécution pour afficher des informations sur les piles d'appel pertinentes ;

par défaut **activé**, après la terminaison de l' [EventLoop](learn?id=什么是eventloop), si un verrouillage de coroutines est présent, le système l'appelle automatiquement en dessous ;

il est possible de l' désactiver en setting `enable_deadlock_check` dans [Coroutine::set](/coroutine/coroutine?id=set).

!> La version Swoole doit être >= `v4.6.0` pour être utilisable

```php
Swoole\Coroutine\deadlock_check();
```
