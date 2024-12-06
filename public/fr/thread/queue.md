# Conteneur de concurrency sécurisé Queue

Créez une structure `Queue` concurrentielle qui peut être passée en paramètre de thread aux sous-threads. La lecture et l'écriture sont visibles par d'autres threads.

## Caractéristiques
- `Thread\Queue` est une structure de données FIFO (First In First Out).

- `Map`, `ArrayList`, `Queue` allouent automatiquement de la mémoire, il n'est pas nécessaire de fixer l'allocation comme pour `Table`.

- Le niveau inférieur s'assure automatiquement de la sécurisation par verrouillage, ce qui en fait un conteneur thread-safe.

- Les types de variables pouvant être transmis sont décrits dans [la transmission des paramètres de thread](thread/transfer.md).

- Ne prend pas en charge l'itération, le niveau inférieur utilise `C++ std::queue`, ne supportant que des opérations FIFO.

- Les objets `Map`, `ArrayList`, `Queue` doivent être passés en tant que paramètres de thread aux sous-threads avant la création du thread.

- `Thread\Queue` ne peut que pousser et popper des éléments, sans pouvoir accéder aux éléments de manière aléatoire.

- `Thread\Queue` intègre une variable de condition thread, permettant de réveiller ou d'attendre d'autres threads lors des opérations `push/pop`.

## Exemple

```php
use Swoole\Thread;
use Swoole\Thread\Queue;

$args = Thread::getArguments();
$c = 4;
$n = 128;

if (empty($args)) {
    $threads = [];
    $queue = new Queue;
    for ($i = 0; $i < $c; $i++) {
        $threads[] = new Thread(__FILE__, $i, $queue);
    }
    while ($n--) {
        $queue->push(base64_encode(random_bytes(16)), Queue::NOTIFY_ONE);
        usleep(random_int(10000, 100000));
    }
    $n = 4;
    while ($n--) {
        $queue->push('', Queue::NOTIFY_ONE);
    }
    for ($i = 0; $i < $c; $i++) {
        $threads[$i]->join();
    }
    var_dump($queue->count());
} else {
    $queue = $args[1];
    while (1) {
        $job = $queue->pop(-1);
        if (!$job) {
            break;
        }
        var_dump($job);
    }
}
```

## Constantes

Nom | Effet
---|---
`Queue::NOTIFY_ONE` | Réveille un thread
`Queue::NOTIFY_ALL` | Réveille tous les threads

## Liste des méthodes

### __construct()
Constructeur du conteneur de concurrency sécurisé `Queue`

```php
Swoole\Thread\Queue->__construct()
```

### push()
Écrire des données à la fin de la file d'attente

```php
Swoole\Thread\Queue()->push(mixed $value, int $notify_which = 0): void
```

  * **Paramètres**
      * `mixed $value`
          * Fonction : Contenu des données écrites.
          * Valeur par défaut : Aucun.
          * Autres valeurs : Aucun.

      !> Afin d'éviter toute ambiguïté, veuillez ne pas écrire `null` ou `false` dans le canal
  
      * `int $notify`
          * Fonction : Indique si les threads en attente de lecture doivent être notifiés.
          * Valeur par défaut : `0`, ne réveille aucun thread
          * Autres valeurs : `Swoole\Thread\Queue::NOTIFY_ONE` réveille un thread, `Swoole\Thread\Queue::NOTIFY_ALL` réveille tous les threads.



### pop()
Récupérer des données de l'extrémité avant de la file d'attente

```php
Swoole\Thread\Queue()->pop(float $timeout = 0): mixed
```

* **Paramètres**
    * `float $wait`
        * Fonction : Temps d'attente.
        * Valeur par défaut : `0`, signifie ne pas attendre.
        * Autres valeurs : Si ce n'est pas `0`, signifie attendre que le producteur `push()` des données pendant `$timeout` secondes, si négatif, signifie ne jamais timeout.

* **Valeurs de retour**
    * Retourne les données de l'extrémité avant de la file d'attente, si la file est vide, retourne directement `NULL`.

> Lorsqu'il est utilisé avec `Queue::NOTIFY_ALL` pour réveiller tous les threads, un seul thread peut obtenir les données écrites par l'opération `push()`.


### count()
Obtenir le nombre d'éléments dans la file d'attente

```php
Swoole\Thread\Queue()->count(): int
```

* **Valeurs de retour**
    * Retourne le nombre d'éléments dans la file d'attente.

### clean()
Vider tous les éléments

```php
Swoole\Thread\Queue()->clean(): void
```
