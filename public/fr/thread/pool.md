# B pool de threads

Un pool de threads peut maintenir la mise en œuvre de plusieurs threads de travail, créant automatiquement, redémarrant et fermant des sous-threads.

## Méthodes


### __construct()

Constructeur.

```php
Swoole\Thread\Pool::__construct(string $workerThreadClass, int $num);
```

* **Paramètres** 
  * `string $workerThreadClass` : La classe des threads de travail qui s'exécutent
  * `int $worker_num` : Le nombre spécifié de threads de travail


### withArguments()

Définir les arguments des threads de travail, ces arguments peuvent être obtenus dans la méthode `run($args)`.

```php
Swoole\Thread\Pool::withArguments(...$args): static;
```


### withAutoloader()

Charger le fichier `autoload`

```php
Swoole\Thread\Pool::withAutoloader(string $autoloader): static;
```
* **Paramètres** 
  * `string $autoloader` : Le chemin du fichier `PHP` de l'autoloader


> Si vous utilisez `Composer`, il peut être déduit automatiquement au niveau des processus de travail et le fichier `vendor/autoload.php` sera chargé, il n'est donc pas nécessaire de le specifier manuellement.


### withClassDefinitionFile()

Définir le fichier de définition de la classe du thread de travail, **ce fichier doit contenir uniquement des déclarations de `namespace`**, `use`, **et de classe**, et ne doit pas contenir de morceaux de code exécutables.

La classe du thread de travail doit hériter de la classe de base `Swoole\Thread\Runnable` et mettre en œuvre la méthode `run(array $args)`.

```php
Swoole\Thread\Pool::withClassDefinitionFile(string $classFile): static;
```
* **Paramètres** 
  * `string $classFile` : Le chemin du fichier `PHP` de la classe du thread de travail

Si la classe du thread de travail est dans le chemin de l'autoloader, elle n'est pas nécessaire d'être spécifiée.


### start()

Démarrer tous les threads de travail

```php
Swoole\Thread\Pool::start(): void;
```


### shutdown()

Fermer le pool de threads

```php
Swoole\Thread\Pool::shutdown(): void;
```


## Exemple
```php
$map = new Swoole\Thread\Map();

(new Pool(TestThread::class, 4))
    ->withAutoloader(__DIR__ . '/vendor/autoload.php')
    ->withClassDefinitionFile(__DIR__ . '/TestThread.php')
    ->withArguments(uniqid(), $map)
    ->start();
```


## Thread\Runnable

La classe du thread de travail doit hériter de cette classe.


### run(array $args)

Il faut redéfinir cette méthode, `$args` sont les arguments passés par l'objet pool de threads à l'aide de la méthode `withArguments()`.


### shutdown()

Fermer le pool de threads


### $id 
Le numéro du thread actuel, qui va de `0` à `(total de threads - 1)`. Lorsque le thread est redémarré, le nouveau thread successeur a le même numéro que l'ancien thread.


### Exemple

```php
use Swoole\Thread\Runnable;

class TestThread extends Runnable
{
    public function run($uuid, $map): void
    {
        $map->incr('thread', 1);

        for ($i = 0; $i < 5; $i++) {
            usleep(10000);
            $map->incr('sleep');
        }

        if ($map['sleep'] > 50) {
            $this->shutdown();
        }
    }
}
```
