# Conteneur de concurrency sûre List

Créez une structure `List` concurrentielle qui peut être passée en paramètre à un thread subordonné. Les opérations de lecture et d'écriture sont visibles par d'autres threads.

## Caractéristiques
- `Map`, `ArrayList`, `Queue` allouent automatiquement de la mémoire, il n'est pas nécessaire de les allouer de manière fixe comme pour `Table`.

- Le niveau sous-jacent est automatiquement verrouillé, ce qui le rend thread-sûr.

- Les types de variables pouvant être transmis sont décrits dans la [documentation des types de données](thread/transfer.md).

- Ne prend pas en charge l'itération, utilisez `toArray()` comme alternative.

- Les objets `Map`, `ArrayList`, `Queue` doivent être passés en tant que paramètres de thread aux threads subordonnés avant la création du thread.

- `Thread\ArrayList` implémente les interfaces `ArrayAccess` et `Countable`, permettant une manipulation directe comme pour un tableau.

- `Thread\ArrayList` ne prend en charge que les opérations d'indexation numérique, les autres index seront convertis par force en numériques.

## Exemple
```php
use Swoole\Thread;
use Swoole\Thread\ArrayList;

$args = Thread::getArguments();
if (empty($args)) {
    $list = new ArrayList;
    $thread = new Thread(__FILE__, $i, $list);
    sleep(1);
    $list[] = unique();
    $thread->join();
} else {
    $list = $args[1];
    sleep(2);
    var_dump($list[0]);
}
```

- Ajout ou modification : `$list[$index] = $value`

- Suppression : `unset($list[$index])`

- Lecture : `$value = $list[$index]`
- Obtention de la longueur : `count($list)`

## Suppression
Veuillez noter que l'opération de suppression peut entraîner un déplacement massif des éléments de la `List`. Par exemple, si une `List` contient `1000` éléments et que `unset($list[4])` est appelée, il sera nécessaire de déplacer massivement les éléments de `$list[5]` à `$list[999]` pour remplir l'espace laissé par la suppression de `$list[4]`. Cependant, il n'y aura pas de copie profonde des éléments, seuls leurs pointeurs seront déplacés.

> Lorsqu'une `List` est grande, la suppression d'un élément en tête peut consommer beaucoup de ressources CPU.

## Méthodes

### __construct()
Constructeur du conteneur de concurrency sûre `ArrayList`

```php
Swoole\Thread\ArrayList->__construct(?array $values = null)
```

- `$values` est optionnel, il parcourt l'array et ajoute ses valeurs à la `ArrayList`.

- Prend uniquement en charge des arrays de type `list`, pas d'arrays associatifs, sinon une exception sera lancée.
- Pour les arrays associatifs, utilisez `array_values` pour les transformer en arrays de type `list`.

### incr()
Augmente de manière sûre la valeur d'un élément dans la `ArrayList`, prend en charge les types flottants ou entiers. Si une autre type est utilisé pour l'augmentation, il sera automatiquement converti en entier, initialisé à `0`, puis augmenté.

```php
Swoole\Thread\ArrayList->incr(int $index, mixed $value = 1) : int | float
```

* **Paramètres**
    * `int $index`
        * Fonction : numéro d'index, doit être une adresse d'index valide, sinon une exception sera lancée.
        * Valeur par défaut : none.
        * Autres valeurs : none.

    * `mixed $value`
        * Fonction : valeur à augmenter.
        * Valeur par défaut : 1.
        * Autres valeurs : none.

* **Valeur de retour**
    * Retourne la valeur après augmentation.

### decr()
Diminue de manière sûre la valeur d'un élément dans la `ArrayList`, prend en charge les types flottants ou entiers. Si une autre type est utilisé pour la diminution, il sera automatiquement converti en entier, initialisé à `0`, puis diminué.

```php
Swoole\Thread\ArrayList->(int $index, $value = 1) : int | float
```

* **Paramètres**
    * `int $index`
        * Fonction : numéro d'index, doit être une adresse d'index valide, sinon une exception sera lancée.
        * Valeur par défaut : none.
        * Autres valeurs : none.

    * `mixed $value`
        * Fonction : valeur à diminuer.
        * Valeur par défaut : 1.
        * Autres valeurs : none.

* **Valeur de retour**
    * Retourne la valeur après diminution.

### count()
Obtient le nombre d'éléments dans la `ArrayList`

```php
Swoole\Thread\ArrayList()->count(): int
```

* **Valeur de retour**
    * Retourne le nombre d'éléments dans la List.

### toArray()
Convertit la `ArrayList` en un tableau

```php
Swoole\Thread\ArrayList()->toArray(): array
```

* **Valeur de retour**
    * Type d'array, retourne tous les éléments de la `ArrayList`.

### clean()
Vide tous les éléments

```php
Swoole\Thread\ArrayList()->clean(): void
```
