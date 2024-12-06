# Méthodes et propriétés


## Méthodes


### __construct()
Constructeur multithread

```php
Swoole\Thread->__construct(string $script_file, mixed ...$args)
```
* **Paramètres**
    * `string $script_file`
        * Fonction : Fichier à exécuter après le lancement de la thread.
        * Valeur par défaut : Aucun.
        * Autres valeurs : Aucun.

    * `mixed $args`
        * Fonction : Données partagées passées par la thread principale à la thread secondaire, accessibles dans la thread secondaire avec `Swoole\Thread::getArguments()`.
        * Valeur par défaut : Aucun.
        * Autres valeurs : Aucun.

!> L'échec de la création de la thread lancera une `Swoole\Exception`, qui peut être capturée avec un `try catch`.


### join()
La thread principale attend la sortie de la thread secondaire. Si la thread secondaire est toujours en cours d'exécution, `join()` blockera jusqu'à ce que la thread secondaire se ferme.

```php
Swoole\Thread->join(): bool
```
* **Valeur de retour**
    * Retourne `true` si l'opération est réussie, `false` sinon.


### joinable()
Vérifie si la thread secondaire s'est déjà fermée.

```php
Swoole\Thread->joinable(): bool
```


#### Valeur de retour

- `true` signifie que la thread secondaire s'est fermée, et que l'appel à `join()` ne provoquera pas un blocage
- `false` signifie qu'elle n'est pas fermée


### detach()
Laisse la thread secondaire se détacher de la contrôle de la thread principale, sans avoir besoin de `join()` pour attendre la sortie de la thread.

```php
Swoole\Thread->detach(): bool
```
* **Valeur de retour**
    * Retourne `true` si l'opération est réussie, `false` sinon.


### getId()
Méthode statique, pour obtenir l'ID de la thread actuelle.

```php
Swoole\Thread::getId(): int
```
* **Valeur de retour**
    * Retourne un entier de type int, représentant l'ID de la thread actuelle.


### getArguments()
Méthode statique, pour obtenir les données partagées passées par la thread principale lors de la création d'une nouvelle instance de `Swoole\Thread()` dans la thread secondaire.

```php
Swoole\Thread::getArguments(): ?array
```

* **Valeur de retour**
    * Retourne les données partagées passées par le processus parent à la thread secondaire.

?> La thread principale n'aura pas de paramètres de thread, on peut distinguer les threads parents des threads enfants en vérifiant si les paramètres de thread sont vides, et leur faire exécuter différentes logiques
```php
use Swoole\Thread;

$args = Thread::getArguments(); // Si c'est la thread principale, $args est vide, si c'est une thread enfant, $args n'est pas vide
if (empty($args)) {
    # Thread principale
    new Thread(__FILE__, 'thread enfant'); // Passer des paramètres de thread
    echo "thread principale\n";
} else {
    # Thread enfant
    var_dump($args); // Sortie: ['thread enfant']
}
```


### getInfo()
Méthode statique, pour obtenir des informations sur l'environnement multithread actuel.

```php
Swoole\Thread::getInfo(): array
```
Les informations renvoyées par l'array sont les suivantes :



- `is_main_thread` : La thread actuelle est-elle la thread principale ?

- `is_shutdown` : La thread est-elle déjà fermée ?
- `thread_num` : Nombre de threads actifs actuels


### getPriority()
Méthode statique, pour obtenir des informations sur la planification de la thread actuelle

```php
Swoole\Thread->getPriority(): array
```
Les informations renvoyées par l'array sont les suivantes :



- `policy` : Stratégie de planification de la thread
- `priority` : Priorité de planification de la thread


### setPriority()
Méthode statique, pour établir la priorité et la stratégie de planification de la thread actuelle

?> Seuls les utilisateurs `root` peuvent ajuster, les utilisateurs non `root` seront refusés

```php
Swoole\Thread->setPriority(int $priority, int $policy = -1): bool
```

* **Paramètres**
    * `int $priority`
        * Fonction : Établir la priorité de planification de la thread
        * Valeur par défaut : Aucun.
        * Autres valeurs : Aucun.

    * `mixed $policy`
        * Fonction : Établir la stratégie de planification de la thread
        * Valeur par défaut : `-1`, signifie ne pas ajuster la stratégie de planification.
        * Autres valeurs : Constantes `Thread::SCHED_*` relatives.

* **Valeur de retour**
    * Retourne `true` en cas de succès
    * Retourne `false` en cas d'échec, utilisez `swoole_last_error()` pour obtenir des informations sur l'erreur

> `SCHED_BATCH/SCHED_ISO/SCHED_IDLE/SCHED_DEADLINE` sont uniquement disponibles sous le système `Linux`  

> Les threads de stratégie `SCHED_FIFO/SCHED_RR` sont généralement des threads à temps réel, avec une priorité supérieure aux threads ordinaires et peuvent obtenir plus de temps CPU.


### getAffinity()
Méthode statique, pour obtenir l'affinité CPU de la thread actuelle

```php
Swoole\Thread->getAffinity(): array
```
La valeur renvoyée est un tableau, dont les éléments sont le nombre de cœurs CPU, par exemple : `[0, 1, 3, 4]` signifie que cette thread sera planifiée pour fonctionner sur les cœurs CPU `0/1/3/4`.


### setAffinity()
Méthode statique, pour établir l'affinité CPU de la thread actuelle

```php
Swoole\Thread->setAffinity(array $cpu_set): bool
```

* **Paramètres**
    * `array $cpu_set`
        * Fonction : Liste des cœurs CPU, par exemple `[0, 1, 3, 4]`
        * Valeur par défaut : Aucun.
        * Autres valeurs : Aucun.

* **Valeur de retour**
    * Retourne `true` en cas de succès
    * Retourne `false` en cas d'échec, utilisez `swoole_last_error()` pour obtenir des informations sur l'erreur


### setName()
Méthode statique, pour établir le nom de la thread actuelle. Lors de l'utilisation d'outils tels que `ps` et `gdb` pour l'inspection et le débogage, cela fournit une représentation plus amicale.

```php
Swoole\Thread->setName(string $name): bool
```

* **Paramètres**
    * `string $name`
        * Fonction : Nom de la thread
        * Valeur par défaut : Aucun.
        * Autres valeurs : Aucun.

* **Valeur de retour**
    * Retourne `true` en cas de succès
    * Retourne `false` en cas d'échec, utilisez `swoole_last_error()` pour obtenir des informations sur l'erreur

```shell
$ ps aux | grep -v grep | grep pool.php
swoole   2226813  0.1  0.1 423860 49024 pts/6    Sl+  17:38   0:00 php pool.php

$ ps -T -p 2226813
    PID    SPID TTY          TIME CMD
2226813 2226813 pts/6     00:00:00 Master Thread
2226813 2226814 pts/6     00:00:00 Worker Thread 0
2226813 2226815 pts/6     00:00:00 Worker Thread 1
2226813 2226816 pts/6     00:00:00 Worker Thread 2
2226813 2226817 pts/6     00:00:00 Worker Thread 3
```


### getNativeId()
Obtenir l'ID système de la thread, qui renverra un entier, similaire à l'ID du processus (PID).

```php
Swoole\Thread->getNativeId(): int
```

Cette fonction appelle la fonction système `gettid()` sous `Linux`, pour obtenir un ID similaire à l'ID de thread du système d'exploitation, qui est un petit entier. Lorsque la thread du processus est détruite, elle peut être réutilisée par le système d'exploitation.

Cet ID peut être utilisé pour le débogage avec `gdb` et `strace`, par exemple `gdb -p $tid`. De plus, on peut lire `/proc/{PID}/task/{ThreadNativeId}` pour obtenir des informations sur l'exécution de la thread.


## Propriétés


### id

Obtenir l'ID de la thread secondaire via cette propriété de l'objet, qui est de type `int`.

> Cette propriété est uniquement utilisée dans la thread principale, les threads secondaires ne peuvent pas obtenir l'objet `$thread`, ils doivent utiliser la méthode statique `Thread::getId()` pour obtenir l'ID de la thread.

```php
$thread = new Swoole\Thread(__FILE__, $i);
var_dump($thread->id);
```


## Constantes

Nom | Effet
---|---
`Thread::HARDWARE_CONCURRENCY` | Nombre de threads à la fois matériels, généralement égal au nombre de cœurs CPU
`Thread::API_NAME` | Nom de l'API de la thread, par exemple `POSIX Threads`
`Thread::SCHED_OTHER` | Stratégie de planification de la thread `SCHED_OTHER`
`Thread::SCHED_FIFO` | Stratégie de planification de la thread `SCHED_FIFO`
`Thread::SCHED_RR` | Stratégie de planification de la thread `SCHED_RR`
`Thread::SCHED_BATCH` | Stratégie de planification de la thread `SCHED_BATCH`
`Thread::SCHED_ISO` | Stratégie de planification de la thread `SCHED_ISO`
`Thread::SCHED_IDLE` | Stratégie de planification de la thread `SCHED_IDLE`
`Thread::SCHED_DEADLINE` | Stratégie de planification de la thread `SCHED_DEADLINE`
