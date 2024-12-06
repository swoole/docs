# Types de données
Voici les types de données qui peuvent être transmis et partagés entre les threads.

## Types de base
Les variables de types `null/bool/int/float`, dont la taille de mémoire est inférieure à `16 Bytes`, sont transmises comme des valeurs.

## Strings
Les chaînes sont **copiées en mémoire** et stockées dans `ArrayList`, `Queue`, `Map`.

## Ressources Socket

### Liste des types pris en charge

- `Co\Socket`

- `PHP Stream`
- `PHP Socket(ext-sockets)`, nécessitant l'activation de la compilation avec `--enable-sockets`

### Types non pris en charge

- `Swoole\Client`

- `Swoole\Server`

- `Swoole\Coroutine\Client`

- `Swoole\Coroutine\Http\Client`

- Connexions `pdo`

- Connexions `redis`
- Autres types de ressources `Socket` spéciaux

### Duplication des ressources

- Lors de l'écriture, une opération `dup(fd)` est effectuée pour séparer la ressource originale de la nouvelle, sans affecter l'une ou l'autre. L'opération `close` sur la ressource originale n'affectera pas la nouvelle ressource.
- Lors de la lecture, une opération `dup(fd)` est effectuée pour construire une nouvelle ressource `Socket` dans le thread enfant `VM`.
- Lors de la suppression, une opération `close(fd)` est effectuée pour libérer le handle de fichier.

Cela signifie que la ressource `Socket` présentera trois comptages de références :

- Le thread où la ressource `Socket` a été initialement créée

- Les conteneurs `ArrayList`, `Queue`, `Map`

- Le thread enfant qui lit les conteneurs `ArrayList`, `Queue`, `Map`

La ressource `Socket` n'est vraiment libérée que lorsque le nombre de références est réduit à zéro, c'est-à-dire lorsque aucun thread ou conteneur ne détient la ressource. Si le nombre de références n'est pas nul, même l'exécution de l'opération `close` ne fermera pas la connexion et n'affectera pas les autres threads ou conteneurs détenant la ressource `Socket`.

Si vous souhaitez ignorer le comptage de références et fermer directement la `Socket`, vous pouvez utiliser la méthode `shutdown()`, par exemple :

- `stream_socket_shutdown()`

- `Socket::shutdown()`
- `socket_shutdown()`

> L'opération `shutdown` affectera toutes les ressources `Socket` détenues par les threads et ne sera plus utilisable après l'exécution, il ne sera plus possible d'effectuer des opérations `read/write`.

## Arrays
Utilisez `array_is_list()` pour déterminer le type de l'array. Si c'est un array à index numérique, il sera transformé en `ArrayList`, et un array à index associatif en `Map`.

- Il parcourra tout l'array et insérera les éléments dans `ArrayList` ou `Map`
- Il prend en charge les arrays multidimensionnels et parcourt de manière récursive les arrays multidimensionnels pour les transformer en structures `ArrayList` ou `Map` en嵌套.

Exemple :
```php
$array = [
    'a' => random_int(1, 999999999999999999),
    'b' => random_bytes(128),
    'c' => uniqid(),
    'd' => time(),
    'e' => [
        'key' => 'value',
        'hello' => 'world',
    ];
];

$map = new Map($array);

// $map['e'] est un nouvel objet Map contenant deux éléments, key et hello, avec des valeurs de 'value' et 'world'
var_dump($map['e']);
```

## Objets

### Objets de ressources thread

Les objets de ressources thread tels que `Thread\Lock`, `Thread\Atomic`, `Thread\ArrayList`, `Thread\Map`, etc., peuvent être directement stockés dans `ArrayList`, `Queue`, `Map`.
Cette opération consiste simplement à stocker une référence à l'objet dans le conteneur, sans faire de copie de l'objet.

Lors de l'écriture de l'objet dans `ArrayList` ou `Map`, une référence à la ressource thread est ajoutée une fois, sans copie. Lorsque le nombre de références à l'objet atteint zéro, il est libéré.

Exemple :

```php
$map = new Thread\Map;
$lock = new Thread\Lock; // Le nombre actuel de références est 1
$map['lock'] = $lock; // Le nombre actuel de références est 2
unset($map['lock']); // Le nombre actuel de références est 1
unset($lock); // Le nombre actuel de références est 0, l'objet Lock est libéré
```

Les listes prises en charge incluent :

- `Thread\Lock`

- `Thread\Atomic`

- `Thread\Atomic\Long`

- `Thread\Barrier`

- `Thread\ArrayList`

- `Thread\Map`
- `Thread\Queue`

Veuillez noter que les objets thread `Thread` ne peuvent pas être sérialisés ni transmis, ils sont uniquement disponibles dans le thread parent.

### Objets PHP ordinaires
Ils seront sérialisés automatiquement lors de l'écriture et désérialisés lors de la lecture. Veuillez noter que si l'objet contient des types non sérialisables, une exception sera levée.
