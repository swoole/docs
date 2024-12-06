# Table de mémoire partagée à haute performance

Étant donné que la langue PHP ne prend pas en charge les threads, Swoole utilise un mode de processus multiples. Dans ce mode, il existe une isolation de mémoire entre les processus. Les modifications des variables globales et des super-variables globales à l'intérieur des processus de travail sont inefficaces dans d'autres processus.

> Lorsque vous configurez `worker_num=1`, il n'y a pas d'isolation de processus et vous pouvez utiliser des variables globales pour stocker les données

```php
$fds = array();
$server->on('connect', function ($server, $fd){
    echo "connection open: {$fd}\n";
    global $fds;
    $fds[] = $fd;
    var_dump($fds);
});
```

Bien que `$fds` soit une variable globale, elle est seulement valide dans le processus actuel. Le serveur Swoole sous-jacent créera plusieurs processus Worker, et la valeur affichée par `var_dump($fds)` ne comprend que les `fd` des connexions partiellement établies.

La solution correspondante consiste à utiliser un service de stockage externe :

* Base de données, comme : MySQL, MongoDB
* Serveur de cache, comme : Redis, Memcache
* Fichiers sur disque, qui nécessitent des verrous lors de l'écriture et de la lecture en parallèle par plusieurs processus

Les opérations ordinaires de base de données et de fichiers sur disque comportent de nombreuses attentes IO. Par conséquent, il est recommandé d'utiliser :

* La base de données en mémoire Redis, qui a des vitesses d'écriture et de lecture très rapides, mais qui pose des problèmes de connexion TCP, et dont les performances ne sont pas les plus élevées.
* Le système de fichiers en mémoire `/dev/shm`, où les opérations d'écriture et de lecture sont toutes effectuées en mémoire, sans consommation IO, offrant des performances extrêmement élevées, mais les données ne sont pas formatées et il y a des problèmes de synchronisation des données.

?> En plus des utilisations de stockage mentionnées ci-dessus, il est recommandé d'utiliser la mémoire partagée pour stocker les données. `Swoole\Table` est une structure de données à haute performance et à concurrence élevée basée sur la mémoire partagée et les verrous. Elle est utilisée pour résoudre les problèmes de partage et de synchronisation des verrous des données dans les environnements multi-processus/multi-threads. La capacité de mémoire de `Table` n'est pas contrôlée par le `memory_limit` PHP.

!> Ne utilisez pas la méthode d'écriture et de lecture par array pour `Table`, assurez-vous d'utiliser les API fournies dans la documentation pour effectuer des opérations ;  
 Les objets `Table\Row` obtenus par la méthode d'extraction par array sont des objets à usage unique, veuillez donc ne pas compter sur eux pour trop d'opérations.
À partir de la version `v4.7.0`, il n'est plus possible d'écrire et de lire `Table` par array, et l'objet `Table\Row` a été supprimé.

* **Avantages**

  * Performance impressionnante, capable de lire et d'écrire 2 millions de fois par seconde en un seul thread ;
  * Code d'application sans verrouillage, `Table` intègre des verrous de spin pour les lignes, toutes les opérations sont sécurisées en multi-threads/multi-processus. Il n'est pas nécessaire de se soucier des problèmes de synchronisation des données au niveau de l'utilisateur ;
  * Support pour multi-processus, `Table` peut être utilisée pour partager des données entre plusieurs processus ;
  * Utilisation de verrous de ligne plutôt que de verrous globaux, seuls les verrous se déclenchent lorsqu'au moins deux processus lisent la même donnée en même temps sur le même CPU.

* **Itération**

!> Veuillez ne pas effectuer d'opérations de suppression pendant l'itération (vous pouvez supprimer toutes les `key` après les avoir extraites)

La classe `Table` implémente l'interface `Iterator` et `Countable`, elle peut être itérée avec `foreach` et le nombre de lignes actuelles peut être calculé avec `count`.

```php
foreach($table as $row)
{
  var_dump($row);
}
echo count($table);
```


## Propriétés


### size

Obtenir le nombre maximal de lignes de la table.

```php
Swoole\Table->size;
```


### memorySize

Obtenir la taille réelle occupée en mémoire, en octets.

```php
Swoole\Table->memorySize;
```


## Méthodes


### __construct()

Créer une table en mémoire.

```php
Swoole\Table::__construct(int $size, float $conflict_proportion = 0.2);
```

  * **Paramètres** 

    * **`int $size`**
      * **Fonction** : Specifier le nombre maximal de lignes de la table
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

      !> Comme la table est basée sur la mémoire partagée, il est impossible de l'agrandir dynamiquement. Par conséquent, `$size` doit être calculé et établi avant sa création. Le nombre maximal de lignes que `Table` peut stocker est directement lié à `$size`, mais n'est pas entièrement identique. Par exemple, si `$size` est de `1024`, le nombre réel de lignes qui peuvent être stockées est **inférieur** à `1024`. Si `$size` est trop grand, et que la mémoire de l'ordinateur est insuffisante, la création de `Table` échouera.  

    * **`float $conflict_proportion`**
      * **Fonction** : Proportion maximale de collisions de hachage
      * **Valeur par défaut** : `0.2` (c'est-à-dire `20%`)
      * **Autres valeurs** : Minimale de `0.2`, maximale de `1`

  * **Calcul de capacité**

      * Si `$size` n'est pas une puissance de `2`, comme `1024`, `8192`, `65536`, etc., le niveau inférieur l'ajustera automatiquement à un nombre proche. Si elle est inférieure à `1024`, elle est par défaut de `1024`, c'est-à-dire que `1024` est la valeur minimale. À partir de la version `v4.4.6`, la valeur minimale est de `64`.
      * La taille totale de la mémoire occupée par `Table` est la somme de (`taille de la structure HashTable` + `taille des clés de 64 octets` + `$valeur de $size`) * (`1 + `$valeur de $conflict_proportion` comme collisions de hachage`) * (`taille des colonnes`).
      * Si votre données `Clé` et le taux de collision de hachage dépassent `20%`, la capacité de blocs de mémoire réservés pour les collisions est insuffisante, et l'appel à `set` pour de nouvelles données échouera avec l'erreur `Unable to allocate memory` et retournera `false`, l'échec du stockage. Dans ce cas, il est nécessaire d'augmenter la valeur de `$size` et de redémarrer le service.
      * Dans la mesure où la mémoire est suffisante, il est préférable de fixer cette valeur aussi élevée que possible.


### column()

Ajouter une colonne à la table en mémoire.

```php
Swoole\Table->column(string $name, int $type, int $size = 0);
```

  * **Paramètres** 

    * **`string $name`**
      * **Fonction** : Specifier le nom de la colonne
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

    * **`int $type`**
      * **Fonction** : Specifier le type de la colonne
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : `Table::TYPE_INT`, `Table::TYPE_FLOAT`, `Table::TYPE_STRING`

    * **`int $size`**
      * **Fonction** : Specifier la longueur maximale des champs de type chaîne [Les champs de type chaîne doivent avoir une `$size` spécifiée]
      * **Unité de valeur** : Octets
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

  * **Explication des types `$type`**


Type | Explication
---|---
Table::TYPE_INT | Par défaut de 8 octets
Table::TYPE_STRING | Après l'établissement, la chaîne spécifiée ne peut pas dépasser la longueur maximale `$size`
Table::TYPE_FLOAT | Occupe 8 octets de mémoire


### create()

Créer une table en mémoire. Après avoir défini la structure de la table, exécutez `create` pour demander de la mémoire à l'opération système et créer la table.

```php
Swoole\Table->create(): bool
```

Après avoir utilisé la méthode `create` pour créer la table, vous pouvez obtenir la taille réelle occupée en mémoire en utilisant la propriété [memorySize](/memory/table?id=memorysize)

  * **Avis** 

    * Avant d'appeler `create`, vous ne pouvez pas utiliser les méthodes d'écriture et de lecture de données telles que `set`, `get`, etc.
    * Après avoir appelé `create`, vous ne pouvez plus utiliser la méthode `column` pour ajouter de nouvelles colonnes
    * Si la mémoire système est insuffisante et que l'allocation échoue, `create` retourne `false`
    * Si l'allocation de mémoire réussit, `create` retourne `true`

    !> Pour utiliser `Table`, assurez-vous d'exécuter `Table->create()` avant de créer des processus fils ;  
    Dans `Server` utilisant `Table`, `Table->create()` doit être exécuté avant `Server->start()`.

  * **Exemple d'utilisation**

```php
$table = new Swoole\Table(1024);
$table->column('id', Swoole\Table::TYPE_INT);
$table->column('name', Swoole\Table::TYPE_STRING, 64);
$table->column('num', Swoole\Table::TYPE_FLOAT);
$table->create();

$worker = new Swoole\Process(function () {}, false, false);
$worker->start();

//$serv = new Swoole\Server('127.0.0.1', 9501);
//$serv->start();
```
### set()

Configure les données d'une ligne. La `Table` utilise une approche `clé-valeur` pour accéder aux données.

```php
Swoole\Table->set(string $key, array $value): bool
```

  * **Paramètres** 

    * **`string $key`**
      * **Fonction** : La `clé` des données
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

      > La même `$key` correspond à la même ligne de données. Si `set` est appelé avec la même `key`, cela écrasera les données précédentes. La longueur maximale de la `key` ne doit pas dépasser 63 octets.

    * **`array $value`**
      * **Fonction** : La `valeur` des données
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

      > Doit être un tableau, et doit correspondre entièrement à la définition de `$name` du champ.

  * **Valeur de retour**

    * True si l'opération de mise est réussie
    * False en cas d'échec, peut-être dû à trop de conflits de hachage entraînant une incapacité à allouer de la mémoire dynamique, il est possible d'augmenter le deuxième argument du constructeur

> - `Table->set()` peut configurer la valeur de tous les champs ou ne modifier que certains champs ;  
   - Avant `Table->set()`, tous les champs de la ligne de données sont vides ;  
   - `set`/`get`/`del` sont avec verrouillage de ligne intégré, donc il n'est pas nécessaire d'appeler `lock` pour verrouiller ;  
   - **La clé n'est pas sûre en binaire, elle doit être de type chaîne et ne doit pas contenir de données binaires.**
    
  * **Exemple d'utilisation**

```php
$table->set('1', ['id' => 1, 'name' => 'test1', 'age' => 20]);
$table->set('2', ['id' => 2, 'name' => 'test2', 'age' => 21]);
$table->set('3', ['id' => 3, 'name' => 'test3', 'age' => 19]);
```

  * **Enseignement de chaînes dépassant la longueur maximale**
    
    Si une chaîne passée dépasse la taille maximale définie lors de la définition de la colonne, le niveau inférieur troncera automatiquement.
    
    ```php
    $table->column('str_value', Swoole\Table::TYPE_STRING, 5);
    $table->set('hello', array('str_value' => 'world 123456789'));
    var_dump($table->get('hello'));
    ```

    * La colonne `str_value` a une taille maximale de 5 octets, mais `set` a établi une chaîne dépassant les `5` octets
    * Le niveau inférieur troncera automatiquement les données sur 5 octets, et la valeur finale de `str_value` sera `world`

!> À partir de la version `v4.3`, le niveau inférieur a géré l'alignement de la mémoire. La longueur de la chaîne doit être un multiple de 8, par exemple, une longueur de 5 est automatiquement alignée sur 8 octets, donc la valeur de `str_value` est `world 12`


### incr()

Opération d'augmentation atomique.

```php
Swoole\Table->incr(string $key, string $column, mixed $incrby = 1): int
```

  * **Paramètres** 

    * **`string $key`**
      * **Fonction** : La `clé` des données【Si la ligne correspondant à `$key` n'existe pas, la valeur par défaut de la colonne est `0`】
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

    * **`string $column`**
      * **Fonction** : Nom de la colonne spécifiée【Seulement pour les champs de type flottant et entier】
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

    * **`string $incrby`**
      * **Fonction** : Increment 【Si la colonne est de type `int`, `$incrby` doit être de type `int`; si la colonne est de type `float`, `$incrby` doit être de type `float`】
      * **Valeur par défaut** : `1`
      * **Autres valeurs** : None

  * **Valeur de retour**

    Retourne la valeur finale du résultat numérique


### decr()

Opération de déduction atomique.

```php
Swoole\Table->decr(string $key, string $column, mixed $decrby = 1): int
```

  * **Paramètres** 

    * **`string $key`**
      * **Fonction** : La `clé` des données【Si la ligne correspondant à `$key` n'existe pas, la valeur par défaut de la colonne est `0`】
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

    * **`string $column`**
      * **Fonction** : Nom de la colonne spécifiée【Seulement pour les champs de type flottant et entier】
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

    * **`string $decrby`**
      * **Fonction** : Increment 【Si la colonne est de type `int`, `$decrby` doit être de type `int`; si la colonne est de type `float`, `$decrby` doit être de type `float`】
      * **Valeur par défaut** : `1`
      * **Autres valeurs** : None

  * **Valeur de retour**

    Retourne la valeur finale du résultat numérique

    !> Si la valeur est `0`, la déduction deviendra négative


### get()

Récupère une ligne de données.

```php
Swoole\Table->get(string $key, string $field = null): array|false
```

  * **Paramètres** 

    * **`string $key`**
      * **Fonction** : La `clé` des données【Doit être de type chaîne】
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

    * **`string $field`**
      * **Fonction** : Lorsque `$field` est spécifié, ne renvoie que la valeur de ce champ, et non toute la enregistrement
      * **Valeur par défaut** : None
      * **Autres valeurs** : None
      
  * **Valeur de retour**

    * Si `$key` n'existe pas, retournera `false`
    * Retourne un tableau de résultats en cas de succès
    * Lorsque `$field` est spécifié, ne renvoie que la valeur de ce champ, et non toute la enregistrement


### exist()

Vérifie si une clé existe dans la table.

```php
Swoole\Table->exist(string $key): bool
```

  * **Paramètres** 

    * **`string $key`**
      * **Fonction** : La `clé` des données【Doit être de type chaîne】
      * **Valeur par défaut** : None
      * **Autres valeurs** : None


### count()

Retourne le nombre d'entrées dans la table.

```php
Swoole\Table->count(): int
```


### del()

Supprime des données.

!> `Clé` non sûr en binaire, doit être de type chaîne et ne doit pas contenir de données binaires ; **N'effacez pas pendant la itération.**

```php
Swoole\Table->del(string $key): bool
```

  * **Valeur de retour**

    * Si les données correspondant à `$key` n'existent pas, retournera `false`
    * Retourne `true` en cas d'échec de la suppression


### stats()

Obtient l'état de la `Swoole\Table`.

```php
Swoole\Table->stats(): array
```

!> Disponible à partir de la version Swoole `v4.8.0`


## Function helper :id=swoole_table

Permet aux utilisateurs de créer rapidement une `Swoole\Table`.

```php
function swoole_table(int $size, string $fields): Swoole\Table
```

!> Disponible à partir de la version Swoole `v4.6.0`. Format de `$fields` : `foo:i/foo:s:num/foo:f`

| Abreviation | Nom complet   | Type               |
| ------------ | ---------------- | ------------------ |
| i            | int              | Table::TYPE_INT    |
| s            | string           | Table::TYPE_STRING |
| f            | float            | Table::TYPE_FLOAT  |

Exemple :

```php
$table = swoole_table(1024, 'fd:int, reactor_id:i, data:s:64');
var_dump($table);

$table = new Swoole\Table(1024, 0.25);
$table->column('fd', Swoole\Table::TYPE_INT);
$table->column('reactor_id', Swoole\Table::TYPE_INT);
$table->column('data', Swoole\Table::TYPE_STRING, 64);
$table->create();
var_dump($table);
```

## Exemple complet

```php
<?php
$table = new Swoole\Table(1024);
$table->column('fd', Swoole\Table::TYPE_INT);
$table->column('reactor_id', Swoole\Table::TYPE_INT);
$table->column('data', Swoole\Table::TYPE_STRING, 64);
$table->create();

$serv = new Swoole\Server('127.0.0.1', 9501);
$serv->set(['dispatch_mode' => 1]);
$serv->table = $table;

$serv->on('receive', function ($serv, $fd, $reactor_id, $data) {

	$cmd = explode(" ", trim($data));

	//get
	if ($cmd[0] == 'get')
	{
		//get self
		if (count($cmd) < 2)
		{
			$cmd[1] = $fd;
		}
		$get_fd = intval($cmd[1]);
		$info = $serv->table->get($get_fd);
		$serv->send($fd, var_export($info, true)."\n");
	}
	//set
	elseif ($cmd[0] == 'set')
	{
		$ret = $serv->table->set($fd, array('reactor_id' => $data, 'fd' => $fd, 'data' => $cmd[1]));
		if ($ret === false)
		{
			$serv->send($fd, "ERROR\n");
		}
		else
		{
			$serv->send($fd, "OK\n");
		}
	}
	else
	{
		$serv->send($fd, "command error.\n");
	}
});

$serv->start();
```
