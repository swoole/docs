# Co-routine\Channel

> Il est préférable de consulter d'abord l'aperçu [Vue d'ensemble](/coroutine) pour comprendre certains concepts de base sur les co-runtines avant de lire cette section.

Les canaux sont utilisés pour la communication entre les co-runtines, et prennent en charge les co-runtines producteurs multiples et les co-runtines consommateurs multiples. La gestion automatique des changements et du calendrier des co-runtines est implémentée en dessous.

## Principe de réalisation

  * Le canal est similaire à un `Array` en `PHP`, ne nécessitant que de la mémoire et n'ayant pas d'autres ressources allouées, toutes les opérations sont des opérations de mémoire, sans consommation d'`IO`.
  * La gestion en dessous utilise le comptage des références en `PHP`, sans duplication de mémoire. Même le transfert de chaînes ou d'arrays énormes ne génère pas de consommation supplémentaire de performance.
  * Le canal est basé sur le comptage des références et est un transfert zero-copy.

## Exemple d'utilisation

```php
use Swoole\Coroutine;
use Swoole\Coroutine\Channel;
use function Swoole\Coroutine\run;

run(function(){
    $channel = new Channel(1);
    Coroutine::create(function () use ($channel) {
        for($i = 0; $i < 10; $i++) {
            Coroutine::sleep(1.0);
            $channel->push(['rand' => rand(1000, 9999), 'index' => $i]);
            echo "{$i}\n";
        }
    });
    Coroutine::create(function () use ($channel) {
        while(1) {
            $data = $channel->pop(2.0);
            if ($data) {
                var_dump($data);
            } else {
                assert($channel->errCode === SWOOLE_CHANNEL_TIMEOUT);
                break;
            }
        }
    });
});
```

## Méthodes


### __construct()

Constructeur du canal.

```php
Swoole\Coroutine\Channel::__construct(int $capacity = 1)
```

  * **Paramètres** 

    * **`int $capacity`**
      * **Fonction** : Établir la capacité 【doit être un entier supérieur ou égal à `1`】
      * **Valeur par défaut** : `1`
      * **Autres valeurs** : Aucun

!> La gestion en dessous utilise le comptage des références PHP pour conserver les variables, l'espace de stockage ne nécessite que `$capacity * sizeof(zval)` octets de mémoire, sous la version PHP7, `zval` est de `16` octets, donc si `$capacity = 1024`, le `Channel` occupera au maximum `16K` de mémoire

!> Lors de son utilisation dans un `Server`, il est nécessaire de créer après [onWorkerStart](/server/events?id=onworkerstart)


### push()

Écrire des données dans le canal.

```php
Swoole\Coroutine\Channel->push(mixed $data, float $timeout = -1): bool
```

  * **Paramètres** 

    * **`mixed $data`**
      * **Fonction** : push des données 【peut être n'importe quel type de variable PHP, y compris des fonctions anonymes et des ressources】
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

      !> Afin d'éviter toute ambiguïté, veuillez ne pas écrire `null` ni `false` dans le canal

    * **`float $timeout`**
      * **Fonction** : Établir le temps d'attente
      * **Unité de valeur** : seconde 【précise avec des浮点数, comme `1.5` signifie `1s`+`500ms`】
      * **Valeur par défaut** : `-1`
      * **Autres valeurs** : Aucun
      * **Impact de la version** : Version Swoole >= v4.2.12

      !> Si le canal est plein, `push` suspendra la co-routine actuelle, et si aucun consommateur ne consomme les données pendant le temps convenu, une timeout se produira, la gestion en dessous reprendra la co-routine actuelle, et l'appel à `push` retournera immédiatement `false`, l'écriture échouera

  * **Valeur de retour**

    * Retourne `true` en cas d'exécution réussie
    * Retourne `false` en cas d'échec de l'exécution lorsque le canal est fermé, et utilisez `$channel->errCode` pour obtenir l'code d'erreur

  * **Extensiones**

    * **Canal plein**

      * Suspend automatiquement la co-routine actuelle, et après que d'autres consommateurs aient consommé les données avec `pop`, le canal peut être écrit, et la co-routine actuelle sera redémarrée
      * Lorsque plusieurs producteurs écrivent simultanément dans le canal, la gestion en dessous effectue automatiquement une file d'attente, et redémarre ces producteurs dans l'ordre

    * **Canal vide**

      * Réveille automatiquement une des co-runtines consommatrices
      * Lorsque plusieurs consommateurs consomment simultanément, la gestion en dessous effectue automatiquement une file d'attente, et redémarre ces consommateurs dans l'ordre

!> `Coroutine\Channel` utilise la mémoire locale, et la mémoire est isolée entre différents processus. Les opérations `push` et `pop` ne peuvent être effectuées que entre différentes co-runtines du même processus 


### pop()

Lire des données du canal.

```php
Swoole\Coroutine\Channel->pop(float $timeout = -1): mixed
```

  * **Paramètres** 

    * **`float $timeout`**
      * **Fonction** : Établir le temps d'attente
      * **Unité de valeur** : seconde 【précise avec des浮点数, comme `1.5` signifie `1s`+`500ms`】
      * **Valeur par défaut** : `-1`【signifie jamais timeout】
      * **Autres valeurs** : Aucun
      * **Impact de la version** : Version Swoole >= v4.0.3

  * **Valeur de retour**

    * Retourne une variable de type quelconque PHP, y compris des fonctions anonymes et des ressources
    * Retourne `false` en cas d'échec de l'exécution lorsque le canal est fermé

  * **Extensiones**

    * **Canal plein**

      * Après avoir consommé les données avec `pop`, redémarre automatiquement un des producteurs pour écrire de nouvelles données
      * Lorsque plusieurs producteurs écrivent simultanément dans le canal, la gestion en dessous effectue automatiquement une file d'attente, et redémarre ces producteurs dans l'ordre

    * **Canal vide**

      * Suspend automatiquement la co-routine actuelle, et après que d'autres producteurs aient écrit des données avec `push`, le canal peut être lu, et la co-routine actuelle sera redémarrée
      * Lorsque plusieurs consommateurs consomment simultanément, la gestion en dessous effectue automatiquement une file d'attente, et redémarre ces consommateurs dans l'ordre


### stats()

Obtenir l'état du canal.

```php
Swoole\Coroutine\Channel->stats(): array
```

  * **Valeur de retour**

    Retourne un tableau, le canal de tampon inclura `4` éléments d'information, et le canal sans tampon retournera `2` éléments d'information
    
    - `consumer_num` : Nombre de consommateurs, indiquant que le canal est vide et qu'il y a `N` co-runtines en attente que d'autres co-runtines appellent la méthode `push` pour produire des données
    - `producer_num` : Nombre de producteurs, indiquant que le canal est plein et qu'il y a `N` co-runtines en attente que d'autres co-runtines appellent la méthode `pop` pour consommer des données
    - `queue_num` : Nombre d'éléments dans le canal

```php
array(
  "consumer_num" => 0,
  "producer_num" => 1,
  "queue_num" => 10
);
```


### close()

Fermer le canal. Réveiller toutes les co-runtines en attente de lecture et d'écriture.

```php
Swoole\Coroutine\Channel->close(): bool
```

!> Réveille tous les producteurs, la méthode `push` retourne `false` ; réveille tous les consommateurs, la méthode `pop` retourne `false`


### length()

Obtenir le nombre d'éléments dans le canal.

```php
Swoole\Coroutine\Channel->length(): int
```


### isEmpty()

Déterminer si le canal est actuellement vide.

```php
Swoole\Coroutine\Channel->isEmpty(): bool
```


### isFull()

Déterminer si le canal est actuellement plein.

```php
Swoole\Coroutine\Channel->isFull(): bool
```


## Propriétés


### capacity

Capacité de la mémoire tampon du canal.

La capacité définie dans le [constructeur](/coroutine/channel?id=__construct) sera conservée dans cette propriété, mais **si la capacité définie est inférieure à 1**, cette propriété sera égale à `1`

```php
Swoole\Coroutine\Channel->capacity: int
```
### errCode

Obtenir le code d'erreur.

```php
Swoole\Coroutine\Channel->errCode : int
```

  * **Valeurs de retour**


Valeur | Constante correspondante | Effet
---|---|---

0 | SWOOLE_CHANNEL_OK | Par défaut, succès
-1 | SWOOLE_CHANNEL_TIMEOUT | Échec du pop en cas de dépassement de temps (délai)
-2 | SWOOLE_CHANNEL_CLOSED | Le canal est fermé, continuer à utiliser le canal
