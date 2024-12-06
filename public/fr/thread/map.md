# Conteneur de concurrency sécurisé Map

Créez une structure `Map` concurrentielle qui peut être passée en paramètre à un thread enfant. La lecture et l'écriture sont visibles par d'autres threads.




## Caractéristiques
- La `Map`, `ArrayList` et `Queue` allouent automatiquement de la mémoire, il n'est pas nécessaire de les allouer de manière fixe comme pour une `Table`.


- Le niveau sous-jacent est automatiquement verrouillé, ce qui le rend thread-sécurité.


- Les types de variables pouvant être transmis sont décrits dans la [documentation des types de données](thread/transfer.md).


- Ne prend pas en charge l'itération, utilisez plutôt `keys()`, `values()`, `toArray()` pour y faire face.


- Les objets `Map`, `ArrayList`, `Queue` doivent être transmis en tant que paramètres de thread à l'enfant thread avant sa création.


- `Thread\Map` implémente les interfaces `ArrayAccess` et `Countable`, permettant une manipulation directe comme avec un tableau.


## Exemple
```php
use Swoole\Thread;
use Swoole\Thread\Map;

$args = Thread::getArguments();
if (empty($args)) {
    $map = new Map;
    $thread = new Thread(__FILE__, $i, $map);
    sleep(1);
    $map['test'] = unique();
    $thread->join();
} else {
    $map = $args[1];
    sleep(2);
    var_dump($map['test']);
}
```



- Ajout ou modification : `$map[$key] = $value`

- Suppression : `unset($map[$key])`

- Lecture : `$value = $map[$key]`
- Obtenir la longueur : `count($map)`


## Méthodes


### __construct()
Constructeur du conteneur de concurrency sécurisé `Map`

```php
Swoole\Thread\Map->__construct(?array $values = null)
```


- `$values` optionnel, parcourir l'array pour ajouter les valeurs à la `Map`


### add()
Écrire des données dans la `Map`

```php
Swoole\Thread\Map->add(mixed $key, mixed $value) : bool
```
  * **Paramètres**
      * `mixed $key`
          * Fonction : La clé à ajouter.
          * Valeur par défaut : None.
          * Autres valeurs : None.
  
      * `mixed $value`
          * Fonction : La valeur à ajouter.
          * Valeur par défaut : None.
          * Autres valeurs : None.
  
  * **Valeur de retour**
      * Si `$key` existe déjà, retourne `false`, sinon retourne `true` indiquant un ajout réussi.


### update()
Mettre à jour les données dans la `Map`

```php
Swoole\Thread\Map->update(mixed $key, mixed $value) : bool
```

  * **Paramètres**
      * `mixed $key`
          * Fonction : La clé à mettre à jour.
          * Valeur par défaut : None.
          * Autres valeurs : None.
  
      * `mixed $value`
          * Fonction : La valeur à mettre à jour.
          * Valeur par défaut : None.
          * Autres valeurs : None.
  
  * **Valeur de retour**
      * Si `$key` n'existe pas, retourne `false`, sinon retourne `true` indiquant une mise à jour réussie


### incr()
Augmenter de manière sûre la valeur d'une `Map`, supportant les types flottants ou entiers. Si une autre type est utilisé pour l'augmentation, il sera automatiquement converti en entier, initialisé à `0`, puis augmenté.

```php
Swoole\Thread\Map->incr(mixed $key, mixed $value = 1) : int | float
```
* **Paramètres**
    * `mixed $key`
        * Fonction : La clé à augmenter. Si elle n'existe pas, elle est automatiquement créée et initialisée à `0`.
        * Valeur par défaut : None.
        * Autres valeurs : None.

    * `mixed $value`
        * Fonction : La valeur d'augmentation.
        * Valeur par défaut : 1.
        * Autres valeurs : None.

* **Valeur de retour**
    * Retourne la valeur après l'augmentation.


### decr()
Diminuer de manière sûre la valeur d'une `Map`, supportant les types flottants ou entiers. Si une autre type est utilisé pour la diminution, il sera automatiquement converti en entier, initialisé à `0`, puis diminué.

```php
Swoole\Thread\Map->decr(mixed $key, mixed $value = 1) : int | float
```
* **Paramètres**
    * `mixed $key`
        * Fonction : La clé à diminuer. Si elle n'existe pas, elle est automatiquement créée et initialisée à `0`.
        * Valeur par défaut : None.
        * Autres valeurs : None.

    * `mixed $value`
        * Fonction : La valeur de diminution.
        * Valeur par défaut : 1.
        * Autres valeurs : None.

* **Valeur de retour**
    * Retourne la valeur après la diminution.


### count()
Obtenir le nombre d'éléments

```php
Swoole\Thread\Map()->count(): int
```

  * **Valeur de retour**
      * Retourne le nombre d'éléments dans la Map.


### keys()
Rendre toutes les `clés`

```php
Swoole\Thread\Map()->keys(): array
```

  * **Valeur de retour**
    * Retourne toutes les `clés` de la `Map`


### values()
Rendre toutes les `valeurs`

```php
Swoole\Thread\Map()->values(): array
```

* **Valeur de retour**
    * Retourne toutes les `valeurs` de la `Map`


### toArray()
Convertir la `Map` en un tableau

```php
Swoole\Thread\Map()->toArray(): array
```

### clean()
Vider tous les éléments

```php
