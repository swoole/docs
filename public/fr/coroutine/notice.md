# Connaissez-vous la programmation asynchrone ?

Lors de l'utilisation des fonctionnalités de [co-routine](/coroutine) de Swoole, veuillez lire attentivement ces directives de programmation.

## Paradigme de programmation

* Interdiction d'utiliser des variables globales à l'intérieur des co-routines
* Utilisation du mot-clé `use` pour introduire des variables externes dans le contexte actuel, interdiction d'utiliser des références
* Les communications entre co-routines doivent utiliser un [Channel](/coroutine/channel)

!> Il ne faut pas utiliser de variables globales ou de références à des variables externes pour la communication entre co-routines, mais plutôt un `Channel`.

* Si votre projet a hooké `zend_execute_ex` ou `zend_execute_internal`, vous devez faire attention au stack C. Vous pouvez utiliser [Co::set](/coroutine/coroutine?id=set) pour redéfinir la taille du stack C

!> Après avoir hooké ces deux fonctions d'entrée, dans la plupart des cas, les appels directs aux instructions PHP plates sont transformés en appels de fonctions C, augmentant la consommation du stack C.

## Sortir d'une co-routine

Dans les versions inférieures de Swoole, utiliser `exit` dans une co-routine pour forcer la sortie du script peut entraîner une erreur de mémoire, des résultats inattendus ou un `coredump`. Dans un service Swoole, utiliser `exit` fait qu'le processus de service entier se termine et que toutes les co-routines internes sont exceptionnellement terminées, ce qui peut entraîner des problèmes graves. Depuis longtemps, Swoole interdit aux développeurs d'utiliser `exit`, mais vous pouvez utiliser une manière non conventionnelle comme lever une exception, et capturer it dans un `catch` au niveau supérieur pour réaliser la même logique de sortie que `exit`.

!> À partir de la version v4.2.2, il est autorisé aux scripts (sans création d'un `http_server`) à sortir avec `exit` uniquement dans la présence de la co-routine actuelle

Swoole **v4.1.0** et versions supérieures prennent directement en charge l'utilisation de `exit` dans les `co-routines` et les événements de service du cycle d'exécution PHP, à ce moment-là, une exception `Swoole\ExitException` capturable est automatiquement levée en dessous. Les développeurs peuvent capturer et réaliser la même logique de sortie que le PHP native à des endroits nécessaires.

### Swoole\ExitException

`Swoole\ExitException` hérite de `Exception` et ajoute deux méthodes supplémentaires : `getStatus` et `getFlags`:

```php
namespace Swoole;

class ExitException extends \Exception
{
	public function getStatus(): mixed
	public function getFlags(): int
}
```

#### getStatus()

Obtenir le paramètre `status` passé à la sortie `exit($status)`, qui prend n'importe quel type de variable en charge.

```php
public function getStatus(): mixed
```

#### getFlags()

Obtenir l'information sur l'environnement où la sortie s'est produite.

```php
public function getFlags(): int
```

Actuellement, il y a les masques suivants :

| Constante | Description |
| -- | -- |
| SWOOLE_EXIT_IN_COROUTINE | Sortie en co-routine |
| SWOOLE_EXIT_IN_SERVER | Sortie en server |


### Méthodes d'utilisation

#### Utilisation de base

```php
use Swoole\Coroutine;
use function Swoole\Coroutine\run;

function route()
{
    controller();
}

function controller()
{
    your_code();
}

function your_code()
{
    Coroutine::sleep(.001);
    exit(1);
}

run(function () {
    try {
        route();
    } catch (\Swoole\ExitException $e) {
        var_dump($e->getMessage());
        var_dump($e->getStatus() === 1);
        var_dump($e->getFlags() === SWOOLE_EXIT_IN_COROUTINE);
    }
});
```

#### Sortie avec statut

```php
use function Swoole\Coroutine\run;

$exit_status = 0;
run(function () {
    try {
        exit(123);
    } catch (\Swoole\ExitException $e) {
        global $exit_status;
        $exit_status = $e->getStatus();
    }
});
var_dump($exit_status);
```


## Traitement des exceptions

Dans la programmation asynchrone, vous pouvez utiliser directement `try/catch` pour gérer les exceptions. **Mais vous devez les capturer à l'intérieur de la co-routine, vous ne pouvez pas les capturer à travers les co-routines.**

!> Non seulement les `Exception` lancées par l'application, mais certaines erreurs de bas niveau peuvent également être capturées, telles que l'absence de `function`, de `class` ou de `method`


### Exemple d'erreur

Dans le code ci-dessous, `try/catch` et `throw` sont dans des co-routines différentes, et il est impossible de capturer cette exception à l'intérieur de la co-routine. Lorsque la co-routine se termine, une exception non capturée est trouvée, ce qui entraînera une erreur fatale.

```bash
PHP Fatal error:  Uncaught RuntimeException
```

```php
try {
	Swoole\Coroutine::create(function () {
		throw new \RuntimeException(__FILE__, __LINE__);
	});
}
catch (\Throwable $e) {
	echo $e;
}
```


### Exemple correct

Capturer l'exception à l'intérieur de la co-routine.

```php
function test() {
	throw new \RuntimeException(__FILE__, __LINE__);
}

Swoole\Coroutine::create(function () {
	try {
		test();
	}
	catch (\Throwable $e) {
		echo $e;
	}
});
```


## Ne pas générer de changement de co-routine dans les méthodes magiques `__get` / `__set`

raison : [Référence analyse du noyau PHP7](https://github.com/pangudashu/php7-internal/blob/40645cfe087b373c80738881911ae3b178818f11/3/zend_object.md)

> **Note:** Si une classe a une méthode `__get()`, alors lors de l'allocation de mémoire pour les propriétés de l'objet (c'est-à-dire : table des propriétés), une zval supplémentaire sera allouée, de type HashTable, chaque fois que la méthode `__get($var)` est appelée, le nom de la variable `$var` entrante sera stocké dans cette table hash. Le but est d'empêcher les appels en boucle, par exemple :
> 
> ***public function __get($var) { return $this->$var; }***
>
> Dans ce cas, c'est l'appel à `__get()` qui a accédé à une propriété inexistante, ce qui entraînera un appel en boucle dans la méthode `__get()`. Si l'on ne vérifie pas si `$var` est déjà dans `__get()`, cela continuera à se répéter indéfiniment. Par conséquent, avant d'appeler `__get()`, on vérifie d'abord si `$var` est déjà dans `__get()`, si c'est le cas, on ne appellera pas à nouveau `__get()`, sinon on insère `$var` comme clé dans cette table hash et on définit la valeur de hachage comme : *guard |= IN_ISSET, après l'appel à `__get()`, on définit la valeur de hachage comme : *guard &= ~IN_ISSET.
>
> Cette table hash n'est pas seulement utilisée pour `__get()`, mais aussi pour d'autres méthodes magiques, donc son type de hachage est zend_long, et différentes méthodes magiques occupent différents bits ; de plus, tous les objets n'allouent pas cette table hash en plus, lors de la création de l'objet, il est déterminé si une table hash est allouée en fonction de ***zend_class_entry.ce_flags*** contenant ***ZEND_ACC_USE_GUARDS***, et lors de la compilation de la classe, si des méthodes telles que `__get()`, `__set()`, `__unset()`, `__isset()` sont définies, alors le flag ce_flags sera marqué avec cet masque.

Après le changement de co-routine, la prochaine invocation sera jugée comme un appel en boucle, ce problème est dû à une caractéristique du PHP **, après communication avec l'équipe de développement PHP, il n'y a toujours pas de solution temporaire.

Note : Bien qu'il n'y ait pas de code dans les méthodes magiques qui peut entraîner un changement de co-routine, l'activation de la planification preemptive des co-routines peut toujours entraîner un changement de co-routine forcé dans les méthodes magiques.

Proposition : Mettez en œuvre vous-même les méthodes `get`/`set` pour une invocation explicite
### Utiliser des variables statiques de classe/variables globales pour conserver le contexte

Plusieurs coroutines sont exécutées en parallèle, donc il n'est pas possible d'utiliser des variables statiques de classe/variables globales pour conserver le contenu du contexte de la coroutine. L'utilisation de variables locales est sûre, car la valeur de la variable locale est automatiquement conservée dans l'étagère de la coroutine, et d'autres coroutines ne peuvent pas accéder aux variables locales de la coroutine.

#### Exemple d'erreur

```php
$server = new Swoole\Http\Server('127.0.0.1', 9501);

$_array = [];
$server->on('request', function ($request, $response) {
    global $_array;
    // Demande /a (coroutine 1)
    if ($request->server['request_uri'] == '/a') {
        $_array['name'] = 'a';
        co::sleep(1.0);
        echo $_array['name'];
        $response->end($_array['name']);
    }
    // Demande /b (coroutine 2)
    else {
        $_array['name'] = 'b';
        $response->end();
    }
});
$server->start();
```

Lancer 2 demandes en parallèle.

```shell
curl http://127.0.0.1:9501/a
curl http://127.0.0.1:9501/b
```

* Dans la coroutine 1, la variable globale `$_array['name']` est définie avec la valeur 'a'
* La coroutine 1 appelle `co::sleep` pour suspendre
* La coroutine 2 s'exécute, définissant la valeur de `$_array['name']` à 'b', et la coroutine 2 se termine
* À ce moment, le timer retourne, et le contexte de la coroutine 1 est restauré en bas. Cependant, il y a une dépendance de contexte dans la logique de la coroutine 1. Lorsque `$_array['name']` est à nouveau imprimé, la valeur attendue par le programme est 'a', mais cette valeur a été modifiée par la coroutine 2, et le résultat réel est 'b', ce qui entraîne une erreur logique.
* De même, l'utilisation de variables statiques de classe `Class::$array`, des propriétés d'objets globaux `$object->array`, d'autres variables superglobales `$GLOBALS`, etc., pour conserver le contexte dans un programme de coroutines est très dangereux. Il peut se produire des comportements inattendus.

![](../_images/coroutine/notice-1.png)

#### Exemple correct : Utiliser la gestion du contexte avec Context

Il est possible d'utiliser une classe `Context` pour gérer le contexte des coroutines. Dans la classe `Context`, utilisez `Coroutine::getuid` pour obtenir l'ID de la coroutine, puis isolez les variables globales entre différentes coroutines, et nettoyez les données de contexte lorsque la coroutine se termine.

```php
use Swoole\Coroutine;

class Context
{
    protected static $pool = [];

    static function get($key)
    {
        $cid = Coroutine::getuid();
        if ($cid < 0)
        {
            return null;
        }
        if(isset(self::$pool[$cid][$key])){
            return self::$pool[$cid][$key];
        }
        return null;
    }

    static function put($key, $item)
    {
        $cid = Coroutine::getuid();
        if ($cid > 0)
        {
            self::$pool[$cid][$key] = $item;
        }

    }

    static function delete($key = null)
    {
        $cid = Coroutine::getuid();
        if ($cid > 0)
        {
            if($key){
                unset(self::$pool[$cid][$key]);
            }else{
                unset(self::$pool[$cid]);
            }
        }
    }
}
```

Utilisation :

```php
use Swoole\Coroutine\Context;

$server = new Swoole\Http\Server('127.0.0.1', 9501);

$server->on('request', function ($request, $response) {
    if ($request->server['request_uri'] == '/a') {
        Context::put('name', 'a');
        co::sleep(1.0);
        echo Context::get('name');
        $response->end(Context::get('name'));
        // Nettoyer lorsque la coroutine se termine
        Context::delete('name');
    } else {
        Context::put('name', 'b');
        $response->end();
        // Nettoyer lorsque la coroutine se termine
        Context::delete();
    }
});
$server->start();
```
