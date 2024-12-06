# Lock entre processus/threads

* Dans le code PHP, il est très pratique de créer un lock `Swoole\Lock` pour réaliser la synchronisation des données. La classe `Lock` prend en charge 5 types de locks.
* Pour le mode multithread, il faut utiliser `Swoole\Thread\Lock`, dont l'interface est entièrement identique à celle de `Swoole\Lock`, à l'exception du namespace.


Type de lock | Description
---|---
SWOOLE_MUTEX | Lock mutuel
SWOOLE_RWLOCK | Lock lecture-écriture
SWOOLE_SPINLOCK | Lock spin
SWOOLE_FILELOCK | Lock de fichier (obsolète)
SWOOLE_SEM | Semaphore (obsolète)

!> Veuillez ne pas créer de lock dans des fonctions de rappel telles que [onReceive](/server/events?id=onreceive), sinon la mémoire continuera de croître, entraînant une fuite des memoriaux.


## Exemple d'utilisation

```php
$lock = new Swoole\Lock(SWOOLE_MUTEX);
echo "[Maître]créer lock\n";
$lock->lock();
if (pcntl_fork() > 0)
{
  sleep(1);
  $lock->unlock();
} 
else
{
  echo "[Enfant] Attendre lock\n";
  $lock->lock();
  echo "[Enfant] Obtenir lock\n";
  $lock->unlock();
  exit("[Enfant] quitter\n");
}
echo "[Maître]libérer lock\n";
unset($lock);
sleep(1);
echo "[Maître]quitter\n";
```


## Avertissement

!> Les locks ne peuvent pas être utilisés dans les coroutines, veuillez les utiliser avec prudence et n'utilisez pas d'API qui pourraient provoquer un changement de coroutine entre les opérations `lock` et `unlock`.


### Exemple d'erreur

!> Ce code est complètement bloqué à 100% en mode coroutine.

```php
$lock = new Swoole\Lock();
$c = 2;

while ($c--) {
  go(function () use ($lock) {
      $lock->lock();
      Co::sleep(1);
      $lock->unlock();
  });
}
```


## Méthodes


### __construct()

Constructeur.

```php
Swoole\Lock::__construct(int $type = SWOOLE_MUTEX, string $lockfile = '');
```

!> Veuillez ne pas créer/destruire des objets lock en boucle, sinon une fuite des memoriaux se produira.

  * **Paramètres** 

    * **`int $type`**
      * **Fonction** : Type de lock
      * **Valeur par défaut** : `SWOOLE_MUTEX`【lock mutuel】
      * **Autres valeurs** : None

    * **`string $lockfile`**
      * **Fonction** : Chemin du lock de fichier (doit être fourni lorsque le type est `SWOOLE_FILELOCK`)
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

!> Chaque type de lock prend en charge différentes méthodes. Par exemple, les locks lecture-écriture et de fichier peuvent prendre en charge la méthode `$lock->lock_read()`. De plus, à l'exception du lock de fichier, tous les autres types de lock doivent être créés dans le processus parent, afin que les processus enfants créés par `fork` puissent se disputer le lock.


### lock()

Opération de verrouillage. Si un autre processus détient le lock, l'appel à cette méthode entrera en attente jusqu'à ce que le processus détenant le lock libère le lock avec `unlock()`.

```php
Swoole\Lock->lock(): bool
```


### trylock()

Opération de verrouillage. Contrairement à la méthode `lock()`, `trylock()` ne bloque pas et retourne immédiatement.

```php
Swoole\Lock->trylock(): bool
```

  * **Valeurs de retour**

    * Le verrouillage réussit et retourne `true`, à ce moment-là, vous pouvez modifier les variables partagées
    * Le verrouillage échoue et retourne `false`, indiquant qu'un autre processus détient le lock

!> Le semaphore `SWOOLE_SEM` n'a pas de méthode `trylock`


### unlock()

Libération du lock.

```php
Swoole\Lock->unlock(): bool
```


### lock_read()

Verrouillage en lecture seule.

```php
Swoole\Lock->lock_read(): bool
```

* Pendant qu'un lock en lecture seule est détenu, d'autres processus peuvent encore obtenir un lock en lecture seule et continuer à effectuer des opérations de lecture ;
* Cependant, vous ne pouvez pas utiliser `$lock->lock()` ou `$lock->trylock()`, ces deux méthodes obtiennent un lock exclusif, et lorsque un lock exclusif est pris, d'autres processus ne peuvent plus effectuer d'aucune opération de verrouillage, y compris en lecture seule ;
* Lorsque un autre processus obtient un lock exclusif (en appelant `$lock->lock()`/`$lock->trylock()`), `$lock->lock_read()` entrera en attente jusqu'à ce que le processus détenant le lock exclusif libère le lock.

!> Seuls les locks de type `SWOOLE_RWLOCK` et `SWOOLE_FILELOCK` prennent en charge le verrouillage en lecture seule


### trylock_read()

Verrouillage. Cette méthode est identique à `$lock->lock_read()`, mais elle est non bloquante.

```php
Swoole\Lock->trylock_read(): bool
```

!> L'appel retourne immédiatement, veuillez vérifier la valeur de retour pour déterminer si le lock a été obtenu.

### lockwait()

Opération de verrouillage. Son comportement est identique à celui de la méthode `lock()`, mais `lockwait()` peut prendre en charge un délai d'attente.

```php
Swoole\Lock->lockwait(float $timeout = 1.0): bool
```

  * **Paramètres** 

    * **`float $timeout`**
      * **Fonction** : Délai d'attente spécifié
      * **Unité de valeur** : seconde【Prend en charge les nombres à virgule flottante, comme `1.5` signifie `1s`+`500ms`】
      * **Valeur par défaut** : `1`
      * **Autres valeurs** : None

  * **Valeurs de retour**

    * Si le lock n'est pas obtenu dans le délai spécifié, retourne `false`
    * Le verrouillage réussit et retourne `true`

!> Seuls les locks de type `Mutex` prennent en charge la méthode `lockwait`
