# Compteur sans verrouillage entre processus/threads Atomic

`Atomic` est une classe d'opérations de comptage atomiques fournie en底层 par `Swoole`, qui permet une augmentation et une diminution atomiques des entiers sans verrouillage.

* Utilise la mémoire partagée, permettant l'opération du compteur entre différents processus
* Basé sur les instructions atomiques CPU fournies par `gcc/clang`, sans besoin de verrouillage
* Doit être créé avant `Server->start` dans un programme de serveur pour pouvoir être utilisé dans les processus Worker
* Par défaut utilise le type unsigned 32 bits, si une整数 signé 64 bits est nécessaire, utilisez `Swoole\Atomic\Long`
* Dans un mode multithread, utilisez `Swoole\Thread\Atomic` et `Swoole\Thread\Atomic\Long`, à part le nom de namespace, leur interface est entièrement identique à celle de `Swoole\Atomic` et `Swoole\Atomic\Long`.

!> Veuillez ne pas créer de compteur dans des fonctions de rappel telles que [onReceive](/server/events?id=onreceive), sinon la mémoire continuera de croître, entraînant une fuite des mémoire.

!> Prend en charge le comptage atomique de l'entier signé 64 bits, nécessitant la création avec `new Swoole\Atomic\Long`. `Atomic\Long` ne prend pas en charge les méthodes `wait` et `wakeup`.


## Exemple complet

```php
$atomic = new Swoole\Atomic();

$serv = new Swoole\Server('127.0.0.1', '9501');
$serv->set([
    'worker_num' => 1,
    'log_file' => '/dev/null'
]);
$serv->on("start", function ($serv) use ($atomic) {
    if ($atomic->add() == 2) {
        $serv->shutdown();
    }
});
$serv->on("ManagerStart", function ($serv) use ($atomic) {
    if ($atomic->add() == 2) {
        $serv->shutdown();
    }
});
$serv->on("ManagerStop", function ($serv) {
    echo "shutdown\n";
});
$serv->on("Receive", function () {
    
});
$serv->start();
```


## Méthodes


### __construct()

Constructeur. Crée un objet de comptage atomique.

```php
Swoole\Atomic::__construct(int $init_value = 0);
```

  * **Paramètres** 

    * **`int $init_value`**
      * **Fonction** : Spécifier la valeur initialisée
      * **Valeur par défaut** : `0`
      * **Autres valeurs** : None


!> -`Atomic` ne peut manipuler que des entiers non signés 32 bits, avec une capacité maximale de `4,2 milliards`, et ne prend pas en charge les nombres négatifs ;  

- Pour utiliser un compteur atomique dans un `Server`, il doit être créé avant `Server->start` ;  
- Pour utiliser un compteur atomique dans un [Process](/process/process), il doit être créé avant `Process->start`.


### add()

Augmente le compteur.

```php
Swoole\Atomic->add(int $add_value = 1): int
```

  * **Paramètres** 

    * **`int $add_value`**
      * **Fonction** : Valeur à ajouter【doit être un entier positif】
      * **Valeur par défaut** : `1`
      * **Autres valeurs** : None

  * **Valeurs de retour**

    * La méthode `add` retourne la valeur résultante de l'opération réussie

!> Si la somme avec la valeur originale dépasse `4,2 milliards`, cela provoquera une overflow, et les bits de haut niveau seront abandonnés.


### sub()

Diminue le compteur.

```php
Swoole\Atomic->sub(int $sub_value = 1): int
```

  * **Paramètres** 

    * **`int $sub_value`**
      * **Fonction** : Valeur à diminuer【doit être un entier positif】
      * **Valeur par défaut** : `1`
      * **Autres valeurs** : None

  * **Valeurs de retour**

    * La méthode `sub` retourne la valeur résultante de l'opération réussie

!> Si la différence avec la valeur originale tombe en dessous de `0`, cela provoquera une overflow, et les bits de haut niveau seront abandonnés.


### get()

Obtenir la valeur actuelle du compteur.

```php
Swoole\Atomic->get(): int
```

  * **Valeurs de retour**

    * Retourne la valeur actuelle


### set()

Set la valeur actuelle à la valeur spécifiée.

```php
Swoole\Atomic->set(int $value): void
```

  * **Paramètres** 

    * **`int $value`**
      * **Fonction** : Specifier la valeur cible à set
      * **Valeur par défaut** : None
      * **Autres valeurs** : None


### cmpset()

Si la valeur actuelle est égale à la valeur de paramètre `1`, alors set la valeur actuelle à la valeur de paramètre `2`.   

```php
Swoole\Atomic->cmpset(int $cmp_value, int $set_value): bool
```

  * **Paramètres** 

    * **`int $cmp_value`**
      * **Fonction** : Si la valeur actuelle est égale à `$cmp_value`, retourne `true`, et set la valeur actuelle à `$set_value`, sinon retourne `false`【doit être un entier inférieur à `4,2 milliards`】
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

    * **`int $set_value`**
      * **Fonction** : Si la valeur actuelle est égale à `$cmp_value`, retourne `true`, et set la valeur actuelle à `$set_value`, sinon retourne `false`【doit être un entier inférieur à `4,2 milliards`】
      * **Valeur par défaut** : None
      * **Autres valeurs** : None


### wait()

Set en état d'attente.

!> Lorsque la valeur du compteur atomique est `0`, le programme entre dans un état d'attente. Un autre processus appelant `wakeup` peut à nouveau réveiller le programme. La base utilise la mise en œuvre `Linux Futex`, en utilisant cette caractéristique, il est possible d'implémenter une fonction de attente, de notification et de verrouillage avec seulement `4` octets de mémoire. Sur des plateformes qui ne prennent pas en charge `Futex`, la base utilisera une boucle `usleep(1000)` pour simuler l'implémentation.

```php
Swoole\Atomic->wait(float $timeout = 1.0): bool
```

  * **Paramètres** 

    * **`float $timeout`**
      * **Fonction** : Specifier le temps de timeout【set à `-1` signifie ne jamais timeout, attendre continuellement jusqu'à ce qu'un autre processus réveille】
      * **Unité de valeur** : seconde【prendre en charge les nombres flottants, comme `1.5` signifie `1s`+`500ms`】
      * **Valeur par défaut** : `1`
      * **Autres valeurs** : None

  * **Valeurs de retour** 

    * Retourne `false` en cas de timeout, avec l'erreur code `EAGAIN`, utilisable avec la fonction `swoole_errno` pour obtenir
    * Retourne `true` en cas de succès, indiquant qu'un autre processus a réveillé avec succès le verrou actuel avec `wakeup`

  * **environnement coroutine**

  `wait` bloque tout le processus plutôt que les coroutines, donc veuillez ne pas utiliser `Atomic->wait()` dans un environnement coroutine pour éviter qu'un processus ne s'arrête.


!> - Lorsqu'une caractéristique `wait/wakeup` est utilisée, la valeur du compteur atomique ne peut être que `0` ou `1`, sinon cela entraînera un utilisation incorrecte ;  
- Bien sûr, lorsque la valeur du compteur atomique est `1`, cela signifie qu'il n'y a pas de processus en attente, et la fonction `wait` retournera immédiatement `true`.

  * **Exemple d'utilisation**

    ```php
    $n = new Swoole\Atomic;
    if (pcntl_fork() > 0) {
        echo "master start\n";
        $n->wait(1.5);
        echo "master end\n";
    } else {
        echo "child start\n";
        sleep(1);
        $n->wakeup();
        echo "child end\n";
    }
    ```

### wakeup()

Réveille d'autres processus en attente.

```php
Swoole\Atomic->wakeup(int $n = 1): bool
```

  * **Paramètres** 

    * **`int $n`**
      * **Fonction** : Nombre de processus à réveiller
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

* Si la valeur actuelle du compteur atomique est `0`, cela signifie qu'aucun processus n'est en attente, et `wakeup` retournera immédiatement `true` ;
* Si la valeur actuelle du compteur atomique est `1`, cela signifie qu'il y a un processus en attente, et `wakeup` réveillera le processus en attente et retournera `true` ;
* Après que le processus réveillé est retourné, il mettra la valeur du compteur atomique à `0`, ce qui permet de rappeler `wakeup` pour réveiller d'autres processus en attente.
