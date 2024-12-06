# Barrière de synchronisation des threads

La `Thread\Barrier` est un mécanisme de synchronisation des threads. Il permet à plusieurs threads de se synchroniser à un point spécifique, assurant que tous les threads ont terminé leur tâche avant un certain point critique (la barrière). Ce n'est que lorsque tous les threads participants ont atteint cette barrière qu'ils peuvent continuer à exécuter le code suivant.

Par exemple, si nous créons `4` threads, nous espérons qu'ils s'exécuteront tous ensemble après être prêts, comme lors d'une course où le coup de pistolet est tiré pour signifier le départ, permettant à tous les coureurs de partir simultanément. Cela peut être réalisé avec une `Thread\Barrier`.

## Exemple
```php
use Swoole\Thread;
use Swoole\Thread\Barrier;

const N = 4;
$args = Thread::getArguments();

if (empty($args)) {
    $barrier = new Barrier(N);
    $n = N;
    $threads = [];
    while($n--) {
        $threads[] = new Thread(__FILE__, $barrier, $n);
    }
} else {
    $barrier = $args[0];
    $n = $args[1];
    // Attendre que tous les threads soient prêts
    $barrier->wait();
    echo "thread $n est en cours\n";
}
```

## Méthodes

### __construct()
Constructeur

```php
Thread\Barrier()->__construct(int $count): void
```

  * **Paramètres**
      * `int $count`
          * Fonction : Nombre de threads, doit être supérieur à `1`.
          * Valeur par défaut : Aucun.
          * Autres valeurs : Aucun.
  
Le nombre de threads qui exécutent l'opération `wait` doit correspondre au nombre défini, sinon tous les threads seront bloqués.

### wait()

Bloquer et attendre que les autres threads soient en attente, jusqu'à ce que tous les threads soient en état `wait`, puis réveiller simultanément tous les threads en attente pour continuer à exécuter le code ci-dessous.

```php
Thread\Barrier()->wait(): void
```
