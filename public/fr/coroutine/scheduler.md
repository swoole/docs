# Co-routine\Planificateur

?> Toutes les [co-routines](/co-routine) doivent être [créées](/co-routine/co-routine?id=créer) à l'intérieur de la [conteneur de co-routines](/co-routine/conteneur?id=conteneur). Lors du démarrage d'un programme Swoole, la plupart du temps, un conteneur de co-routines est créé automatiquement. Il existe trois façons de démarrer un programme avec Swoole :

   - Appeler la méthode [start](/server/méthodes?id=start) du service server asynchrone pour créer un conteneur de co-routines dans les rappels d'événements, en référence à [enable_co-routine](/server/paramètres?id=enable_co-routine).
   - Appeler la méthode [start](/process/process_pool?id=start) des deux modules de gestion de processus fournis par Swoole, [Process](/process/process) et [Process\Pool](/process/process_pool), pour créer un conteneur de co-routines au démarrage du processus, en référence à l'argument `enable_co-routine` des constructeurs de ces deux modules.
   - Démarrer un programme en écrivant directement des co-routines, en créant d'abord un conteneur de co-routines (la fonction `Coroutine\run()` peut être considérée comme le `main` de Java ou de C), par exemple :

* **Démarrer un service HTTP entièrement co-routine**

```php
use Swoole\Coroutine\Http\Server;
use function Swoole\Coroutine\run;

run(function () {
    $server = new Server('127.0.0.1', 9502, false);
    $server->handle('/', function ($request, $response) {
        $response->end("<h1>Index</h1>");
    });
    $server->handle('/test', function ($request, $response) {
        $response->end("<h1>Test</h1>");
    });
    $server->handle('/stop', function ($request, $response) use ($server) {
        $response->end("<h1>Stop</h1>");
        $server->shutdown();
    });
    $server->start();
});
echo 1;//ne pas exécuté
```

* **Ajouter 2 co-routines pour faire quelque chose en parallèle**

```php
use Swoole\Coroutine;
use function Swoole\Coroutine\run;

run(function () {
    Coroutine::create(function() {
        var_dump(file_get_contents("http://www.xinhuanet.com/"));
    });

    Coroutine::create(function() {
        Coroutine::sleep(1);
        echo "done\n";
    });
});
echo 1;//exécuté
```

!> Disponible dans la version Swoole v4.4+.

!> Ne pas imbriquer `Coroutine\run()`.  
Si la logique à l'intérieur de `Coroutine\run()` a des événements non traités après `Coroutine\run()`, ils seront traités par l'[EventLoop](learn?id=quel-est-l'evenementloop)', et le code suivant ne sera pas exécuté. En revanche, s'il n'y a plus d'événements, l'exécution continuera vers le bas, et on peut à nouveau appeler `Coroutine\run()`.

La fonction `Coroutine\run()` ci-dessus est en fait une encapsulation de la classe `Swoole\Coroutine\Scheduler` (classe de planificateur de co-routines). Pour ceux qui veulent en savoir plus, ils peuvent consulter les méthodes de la classe `Swoole\Coroutine\Scheduler` :


### set()

?> **设置 les paramètres de runtime pour les co-routines.** 

?> Est l'alias de la méthode `Coroutine::set`. Veuillez consulter la documentation sur [Coroutine::set](/co-routine/co-routine?id=set).

```php
Swoole\Coroutine\Scheduler->set(array $options): bool
```

  * **Exemple**

```php
$sch = new Swoole\Coroutine\Scheduler;
$sch->set(['max_co-routine' => 100]);
```


### getOptions()

?> **Obtenir les paramètres de runtime pour les co-routines.** Disponible à partir de la version Swoole v4.6.0

?> Est l'alias de la méthode `Coroutine::getOptions`. Veuillez consulter la documentation sur [Coroutine::getOptions](/co-routine/co-routine?id=getoptions).

```php
Swoole\Coroutine\Scheduler->getOptions(): null|array
```


### add()

?> **Ajouter une tâche.** 

```php
Swoole\Coroutine\Scheduler->add(callable $fn, ... $args): bool
```

  * **Paramètres** 

    * **`callable $fn`**
      * **Fonction** : fonction de rappel
      * **Valeur par défaut** : none
      * **Autres valeurs** : none

    * **`... $args`**
      * **Fonction** : arguments optionnels, qui seront transmis à la co-routine
      * **Valeur par défaut** : none
      * **Autres valeurs** : none

  * **Exemple**

```php
use Swoole\Coroutine;

$scheduler = new Coroutine\Scheduler;
$scheduler->add(function ($a, $b) {
    Coroutine::sleep(1);
    echo assert($a == 'hello') . PHP_EOL;
    echo assert($b == 12345) . PHP_EOL;
    echo "Done.\n";
}, "hello", 12345);

$scheduler->start();
```
  
  * **Note**

    !> Contrairement à la fonction `go`, les co-routines ajoutées ici ne s'exécuteront pas immédiatement, mais attendront que la méthode `start` soit appelée pour être démarrées et exécutées en même temps. Si dans le programme, des co-routines sont ajoutées uniquement sans appeler `start` pour les démarrer, la fonction `$fn` de la co-routine ne sera pas exécutée.


### parallel()

?> **Ajouter des tâches en parallèle.** 

?> Contrairement à la méthode `add`, la méthode `parallel` créera des co-routines en parallèle. Lors du démarrage, `$num` co-routines de `$fn` seront démarrées en même temps pour s'exécuter en parallèle.

```php
Swoole\Coroutine\Scheduler->parallel(int $num, callable $fn, ... $args): bool
```

  * **Paramètres** 

    * **`int $num`**
      * **Fonction** : nombre de co-routines à démarrer
      * **Valeur par défaut** : none
      * **Autres valeurs** : none

    * **`callable $fn`**
      * **Fonction** : fonction de rappel
      * **Valeur par défaut** : none
      * **Autres valeurs** : none

    * **`... $args`**
      * **Fonction** : arguments optionnels, qui seront transmis à la co-routine
      * **Valeur par défaut** : none
      * **Autres valeurs** : none

  * **Exemple**

```php
use Swoole\Coroutine;

$scheduler = new Coroutine\Scheduler;

$scheduler->parallel(10, function ($t, $n) {
    Coroutine::sleep($t);
    echo "Co ".Coroutine::getCid()."\n";
}, 0.05, 'A');

$scheduler->start();
```

### start()

?> **Démarrer le programme.** 

?> Itération des tâches de co-routine ajoutées par les méthodes `add` et `parallel`, et exécution.

```php
Swoole\Coroutine\Scheduler->start(): bool
```

  * **Valeur de retour**

    * Le démarrage réussit, toutes les tâches ajoutées seront exécutées, et `start` retournera `true` lorsque toutes les co-routines se termineront
    * Le démarrage échoue et retourne `false`, peut-être parce que le démarrage a déjà été effectué ou qu'un autre planificateur a déjà été créé et ne peut pas être créé à nouveau
