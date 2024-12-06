# Chronomètre Timer

Chronomètre à précision milliseconde. Il est basé sur `epoll_wait` et `setitimer` pour sa mise en œuvre, et utilise une structure de données en tas minimale, ce qui permet d'implémenter un grand nombre de chronomètres.

* Utilisé dans les processus d'I/O synchrone avec `setitimer` et les signaux, comme dans les processus `Manager` et `TaskWorker`
* Utilisé dans les processus d'I/O asynchrone avec `epoll_wait`/`kevent`/`poll`/`select` pour la mise en œuvre du délai


## Performance

La mise en œuvre sous-jacente utilise une structure de données en tas minimale pour les chronomètres, l'ajout et la suppression de chronomètres sont entièrement des opérations en mémoire, ce qui confère une performance très élevée.

> Dans le script de test de référence officiel [timer.php](https://github.com/swoole/benchmark/blob/master/timer.php), l'ajout ou la suppression de `100 000` chronomètres au hasard prend environ `0.08s`.

```shell
~/workspace/swoole/benchmark$ php timer.php
add 100000 timer :0.091133117675781s
del 100000 timer :0.084658145904541s
```

!> Les chronomètres sont des opérations en mémoire, sans consommation d'I/O


## Différences

`Timer` est différent du `pcntl_alarm` fourni par `PHP` lui-même. `pcntl_alarm` est basé sur la fonction `clock_signal + tick` et présente certaines défauts :

  * Il ne prend en charge que la seconde, tandis que `Timer` peut atteindre le niveau de la milliseconde
  * Il ne prend pas en charge l'établissement de plusieurs chronomètres en même temps
  * `pcntl_alarm` dépend de `declare(ticks = 1)`, ce qui est très inefficace


## Chronomètre à zéro milliseconde

Le sous-système ne prend pas en charge les chronomètres avec un paramètre de temps de `0`. Contrairement à certains langages de programmation comme `Node.js`. Dans `Swoole`, vous pouvez utiliser [Swoole\Event::defer](/event?id=defer) pour une fonction similaire.

```php
Swoole\Event::defer(function () {
  echo "hello\n";
});
```

!> Le code ci-dessus a exactement le même effet que `setTimeout(func, 0)` dans `JS`.


## Synonymes

`tick()`, `after()`, `clear()` ont tous des synonymes au style de fonction


Méthodes statiques de la classe | Synonymes au style de fonction
---|---
`Swoole\Timer::tick()` | `swoole_timer_tick()`
`Swoole\Timer::after()` | `swoole_timer_after()`
`Swoole\Timer::clear()` | `swoole_timer_clear()`


## Méthodes


### tick()

Configure un chronomètre à temps intermédiaire.

Contrairement au chronomètre `after`, le chronomètre `tick` se déclenche de manière continue et continuera jusqu'à ce que [Timer::clear](/timer?id=clear) soit appelé pour l'éliminer.

```php
Swoole\Timer::tick(int $msec, callable $callback_function, ...$params): int
```

!> 1. Le chronomètre est uniquement valide dans l'espace de processus actuel  
   2. Le chronomètre est purement asynchrone et ne peut pas être utilisé avec des fonctions d'I/O synchrone, sinon le temps d'exécution du chronomètre peut être désynchronisé  
   3. Il peut y avoir certaines erreurs d'exécution dans le processus du chronomètre

  * **Paramètres** 

    * **`int $msec`**
      * **Fonction** : Specifier le temps
      * **Unité de valeur** : Millisecondes [par exemple, `1000` représente `1` seconde, la version `v4.2.10` et inférieure a une limite maximale de `86400000`]
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

    * **`callable $callback_function`**
      * **Fonction** : La fonction à exécuter après le temps écoulé, qui doit être appelable
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

    * **`...$params`**
      * **Fonction** : Passer des données à la fonction d'exécution [cet argument est également optionnel]
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun
      
      !> Vous pouvez utiliser la syntaxe `use` d'une fonction anonyme pour passer des paramètres à la fonction de rappel

  * **Fonction de rappel $callback_function** 

    ```php
    callbackFunction(int $timer_id, ...$params);
    ```

      * **`int $timer_id`**
        * **Fonction** : L'ID du chronomètre [peut être utilisé avec [Timer::clear](/timer?id=clear) pour éliminer ce chronomètre]
        * **Valeur par défaut** : Aucun
        * **Autres valeurs** : Aucun

      * **`...$params`**
        * **Fonction** : Le troisième argument passé par `Timer::tick` `$param`
        * **Valeur par défaut** : Aucun
        * **Autres valeurs** : Aucun

  * **Extensiones**

    * **Corrections de chronomètres**

      Le temps d'exécution de la fonction de rappel du chronomètre n'affecte pas le prochain déclenchement du chronomètre. Exemple : un chronomètre `tick` de `10ms` est établi en `0.002s`, la première exécution du rappel se fait en `0.012s`, si la fonction de rappel s'exécute pendant `5ms`, le prochain déclenchement du chronomètre se fera toujours en `0.022s`, et non en `0.027s`.
      
      Cependant, si l'exécution du temps de la fonction de rappel du chronomètre est trop longue, elle peut même couvrir le prochain déclenchement du chronomètre. Le sous-système effectuera une correction temporelle, abandonnant l'action échue et déclenchant le rappel au prochain moment. Comme dans l'exemple ci-dessus, si la fonction de rappel en `0.012s` s'exécute pendant `15ms`, un rappel de chronomètre devrait se produire en `0.022s`. En réalité, le chronomètre ne retourne que en `0.027s`, lorsque le rappel du chronomètre est déjà passé. Le sous-système déclenchera à nouveau le rappel du chronomètre en `0.032s`.
    
    * **Mode de coroutines**

      Dans un environnement de coroutines, le rappel de `Timer::tick` créera automatiquement une coroutine, vous pouvez utiliser directement les API liées aux coroutines sans avoir à appeler `go` pour créer une coroutine.
      
      !> Vous pouvez configurer [enable_coroutine](/timer?id=close-timer-co) pour désactiver la création automatique de coroutines

  * **Exemples d'utilisation**

    ```php
    Swoole\Timer::tick(1000, function(){
        echo "timeout\n";
    });
    ```

    * **Exemple correct**

    ```php
    Swoole\Timer::tick(3000, function (int $timer_id, $param1, $param2) {
        echo "timer_id #$timer_id, après 3000ms.\n";
        echo "param1 est $param1, param2 est $param2.\n";

        Swoole\Timer::tick(14000, function ($timer_id) {
            echo "timer_id #$timer_id, après 14000ms.\n";
        });
    }, "A", "B");
    ```

    * **Exemple erroné**

    ```php
    Swoole\Timer::tick(3000, function () {
        echo "après 3000ms.\n";
        sleep(14);
        echo "après 14000ms.\n";
    });
    ```


### after()

Exécute une fonction après un délai spécifié. La fonction `Swoole\Timer::after` est un chronomètre à usage unique qui sera détruit après son exécution.

Contrairement à la fonction `sleep` fournie par la bibliothèque standard PHP, `after` est non bloquant. L'appel à `sleep` fera entrer le processus actuel en mode bloquant, rendant impossible le traitement de nouvelles demandes.

```php
Swoole\Timer::after(int $msec, callable $callback_function, ...$params): int
```

  * **Paramètres** 

    * **`int $msec`**
      * **Fonction** : Specifier le temps
      * **Unité de valeur** : Millisecondes [par exemple, `1000` représente `1` seconde, la version `v4.2.10` et inférieure a une limite maximale de `86400000`]
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

    * **`callable $callback_function`**
      * **Fonction** : La fonction à exécuter après le temps écoulé, qui doit être appelable.
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

    * **`...$params`**
      * **Fonction** : Passer des données à la fonction d'exécution [cet argument est également optionnel]
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun
      
      !> Vous pouvez utiliser la syntaxe `use` d'une fonction anonyme pour passer des paramètres à la fonction de rappel

  * **Valeur de retour**

    * Un ID de chronomètre est retourné en cas d'exécution réussie, si vous annulez le chronomètre, vous pouvez appeler [Swoole\Timer::clear](/timer?id=clear)

  * **Extensiones**

    * **Mode de coroutines**

      Dans un environnement de coroutines, le rappel de [Swoole\Timer::after](/timer?id=after) créera automatiquement une coroutine, vous pouvez utiliser directement les API liées aux coroutines sans avoir à appeler `go` pour créer une coroutine.
      
      !> Vous pouvez configurer [enable_coroutine](/timer?id=close-timer-co) pour désactiver la création automatique de coroutines

  * **Exemples d'utilisation**

```php
$str = "Swoole";
Swoole\Timer::after(1000, function() use ($str) {
    echo "Bonjour, $str\n";
});
```
### clear()

Utilisez l'ID du timer pour supprimer le timer.

```php
Swoole\Timer::clear(int $timer_id): bool
```

  * **Paramètres**

    * **`int $timer_id`**
      * **Fonction** : ID du timer 【Appelez [Timer::tick](/timer?id=tick), [Timer::after](/timer?id=after) pour obtenir un ID entier】
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

!> `Swoole\Timer::clear` ne peut pas être utilisé pour effacer les timers d'autres processus, il s'agit uniquement des timers du processus actuel

  * **Exemple d'utilisation**

```php
$timer = Swoole\Timer::after(1000, function () {
    echo "timeout\n";
});

var_dump(Swoole\Timer::clear($timer));
var_dump($timer);

// Sortie : bool(true) int(1)
// Ne sort pas : timeout
```


### clearAll()

Efface tous les timers dans le processus Worker actuel.

!> Disponible à partir de la version Swoole `v4.4.0`

```php
Swoole\Timer::clearAll(): bool
```


### info()

Retourne les informations sur le `timer`.

!> Disponible à partir de la version Swoole `v4.4.0`

```php
Swoole\Timer::info(int $timer_id): array
```

  * **Valeur de retour**

```php
array(5) {
  ["exec_msec"]=>
  int(6000)
  ["exec_count"]=> // v4.8.0 ajouté
  int(5)
  ["interval"]=>
  int(1000)
  ["round"]=>
  int(0)
  ["removed"]=>
  bool(false)
}
```


### list()

Retourne un itérateur de timers, permettant de parcourir tous les IDs de timers du processus Worker actuel avec `foreach`

!> Disponible à partir de la version Swoole `v4.4.0`

```php
Swoole\Timer::list(): Swoole\Timer\Iterator
```

  * **Exemple d'utilisation**

```php
foreach (Swoole\Timer::list() as $timer_id) {
    var_dump(Swoole\Timer::info($timer_id));
}
```


### stats()

Affiche l'état des timers.

!> Disponible à partir de la version Swoole `v4.4.0`

```php
Swoole\Timer::stats(): array
```

  * **Valeur de retour**

```php
array(3) {
  ["initialized"]=>
  bool(true)
  ["num"]=>
  int(1000)
  ["round"]=>
  int(1)
}
```


### set()

Configure les paramètres liés au timer.

```php
Swoole\Timer::set(array $array): void
```

!> Cette méthode est déconseillée à partir de la version `v4.6.0`.

## Fermer les coroutines :id=close-timer-co

Par défaut, les timers créent automatiquement des coroutines lorsqu'ils exécutent leur fonction de rappel, mais vous pouvez configurer séparément la fermeture des coroutines pour les timers.

```php
swoole_async_set([
  'enable_coroutine' => false,
]);
```
