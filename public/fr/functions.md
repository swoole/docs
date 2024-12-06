# Liste des fonctions

En plus des fonctions liées à la communication réseau, Swoole offre également certaines fonctions pour obtenir des informations système utilisables dans les programmes PHP.


## swoole_set_process_name()

Utilisé pour affecter le nom du processus. Après avoir modifié le nom du processus, ce qui sera visible via la commande `ps` n'est plus `php your_file.php`, mais le texte spécifié.

Cette fonction accepte un argument de type chaîne.

Cette fonction est identique à la fonction [cli_set_process_title](https://www.php.net/manual/zh/function.cli-set-process-title.php) fournie par PHP5.5. Cependant, `swoole_set_process_name` peut être utilisé dans toutes les versions de PHP à partir de PHP5.2. La compatibilité avec `swoole_set_process_name` est inférieure à celle de `cli_set_process_title`, et si la fonction `cli_set_process_title` existe, elle est préférée.

```php
function swoole_set_process_name(string $name): void
```

Exemple d'utilisation :

```php
swoole_set_process_name("serveur Swoole");
```


### Comment renommer les différents processus du serveur Swoole <!-- {docsify-ignore} -->

* Modifiez le nom du processus principal lors du appel de [onStart](/server/events?id=onstart)
* Modifiez le nom du processus de gestion (`manager`) lors du appel de [onManagerStart](/server/events?id=onmanagerstart)
* Modifiez le nom du processus worker lors du appel de [onWorkerStart](/server/events?id=onworkerstart)
 
!> Les noyaux Linux de faible version et Mac OSX ne prennent pas en charge la renommage des processus  


## swoole_strerror()

Convertir un code d'erreur en message d'erreur.

Signature de la fonction :

```php
function swoole_strerror(int $errno, int $error_type = 1): string
```

Types d'erreurs :

* `1` : État d'erreur `Unix` standard, généré par les erreurs des appels système, telles que `EAGAIN`, `ETIMEDOUT`, etc.
* `2` : Codes d'erreur `getaddrinfo`, générés par les opérations DNS
* `9` : Codes d'erreur Swoole sous-jacents, obtenus avec `swoole_last_error()`

Exemple d'utilisation :

```php
var_dump(swoole_strerror(swoole_last_error(), 9));
```


## swoole_version()

Obtenir le numéro de version de l'extension Swoole, par exemple `1.6.10`

```php
function swoole_version(): string
```

Exemple d'utilisation :

```php
var_dump(SWOOLE_VERSION); // La variable globale SWOOLE_VERSION représente également la version de l'extension Swoole
var_dump(swoole_version());
/**
Return value:
string(6) "1.9.23"
string(6) "1.9.23"
**/
```


## swoole_errno()

Obtenir le dernier code d'erreur d'appel système, similaire à la variable `errno` en C/C++.

```php
function swoole_errno(): int
```

Les valeurs des codes d'erreur sont liées à l'opération système. Vous pouvez utiliser `swoole_strerror` pour convertir l'erreur en message d'erreur.


## swoole_get_local_ip()

Cette fonction est utilisée pour obtenir les adresses IP de toutes les interfaces réseau locales.

```php
function swoole_get_local_ip(): array
```

Exemple d'utilisation :

```php
// Obtenir les adresses IP de toutes les interfaces réseau locales
$list = swoole_get_local_ip();
print_r($list);
/**
Return value
Array
(
      [eno1] => 10.10.28.228
      [br-1e72ecd47449] => 172.20.0.1
      [docker0] => 172.17.0.1
)
**/
```

!> Remarque
* Actuellement, seules les adresses IP IPv4 sont retournées, et l'adresse locale loop 127.0.0.1 est filtrée.
* Le résultat est un tableau associatif dont les clés sont les noms des interfaces. Par exemple `array("eth0" => "192.168.1.100")`
* Cette fonction appelle activement l'appel système `ioctl` pour obtenir les informations de l'interface, sans缓存 en dessous.


## swoole_clear_dns_cache()

Vider le cache DNS intégré de Swoole, efficace pour `swoole_client` et `swoole_async_dns_lookup`.

```php
function swoole_clear_dns_cache()
```


## swoole_get_local_mac()

Obtenir l'adresse MAC de la carte réseau locale.

```php
function swoole_get_local_mac(): array
```

* Si la fonction est appelée avec succès, elle retourne les adresses MAC de toutes les cartes réseau.

```php
array(4) {
  ["lo"]=>
  string(17) "00:00:00:00:00:00"
  ["eno1"]=>
  string(17) "64:00:6A:65:51:32"
  ["docker0"]=>
  string(17) "02:42:21:9B:12:05"
  ["vboxnet0"]=>
  string(17) "0A:00:27:00:00:00"
}
```


## swoole_cpu_num()

Obtenir le nombre de cœurs CPU locaux.

```php
function swoole_cpu_num(): int
```

* Si la fonction est appelée avec succès, elle retourne le nombre de cœurs CPU, par exemple :

```shell
php -r "echo swoole_cpu_num();"
```


## swoole_last_error()

Obtenir le dernier code d'erreur de Swoole sous-jacent.

```php
function swoole_last_error(): int
```

Vous pouvez utiliser `swoole_strerror(swoole_last_error(), 9)` pour convertir l'erreur en message d'erreur, voir la liste complète des codes d'erreur Swoole [ici](/other/errno?id=swoole).


## swoole_mime_type_add()

Ajouter un nouveau type MIME à la table de types MIME intégrée.

```php
function swoole_mime_type_add(string $suffix, string $mime_type): bool
```


## swoole_mime_type_set()

Modifier un type MIME, retourne `false` en cas d'échec (s'il n'existe pas).

```php
function swoole_mime_type_set(string $suffix, string $mime_type): bool
```


## swoole_mime_type_delete()

Supprimer un type MIME, retourne `false` en cas d'échec (s'il n'existe pas).

```php
function swoole_mime_type_delete(string $suffix): bool
```


## swoole_mime_type_get()

Obtenir le type MIME correspondant au nom de fichier.

```php
function swoole_mime_type_get(string $filename): string
```


## swoole_mime_type_exists()

Vérifier si un type MIME correspondant à un suffixe existe.

```php
function swoole_mime_type_exists(string $suffix): bool
```


## swoole_substr_json_decode()

Déserialisation JSON zero-copy, à l'exception de `$offset` et `$length`, les autres paramètres sont identiques à ceux de [json_decode](https://www.php.net/manual/en/function.json-decode.php).

!> Disponible pour les versions Swoole >= `v4.5.6`, et à partir de la version `v4.5.7`, il est nécessaire de compiler avec l'option [--enable-swoole-json](/environment?id=通用参数) pour l'activer. Pour les scénarios d'utilisation, voir [Swoole 4.5.6 soutient la déserialisation JSON zero-copy ou PHP](https://wenda.swoole.com/detail/107587).

```php
function swoole_substr_json_decode(string $packet, int $offset, int $length, bool $assoc = false, int $depth = 512, int $options = 0)
```

  * **Exemple**

```php
$val = json_encode(['hello' => 'swoole']);
$str = pack('N', strlen($val)) . $val . "\r\n";
$l = strlen($str) - 6;
var_dump(json_decode(substr($str, 4, $l), true));
var_dump(swoole_substr_json_decode($str, 4, $l, true));
```


## swoole_substr_unserialize()

Déserialisation PHP zero-copy, à l'exception de `$offset` et `$length`, les autres paramètres sont identiques à ceux de [unserialize](https://www.php.net/manual/en/function.unserialize.php).

!> Disponible pour les versions Swoole >= `v4.5.6`. Pour les scénarios d'utilisation, voir [Swoole 4.5.6 soutient la déserialisation JSON zero-copy ou PHP](https://wenda.swoole.com/detail/107587).

```php
function swoole_substr_unserialize(string $packet, int $offset, int $length, array $options= [])
```

  * **Exemple**

```php
$val = serialize('hello');
$str = pack('N', strlen($val)) . $val . "\r\n";
$l = strlen($str) - 6;
var_dump(unserialize(substr($str, 4, $l)));
var_dump(swoole_substr_unserialize($str, 4, $l));
```


## swoole_error_log()

Écrire des informations d'erreur dans le journal. `$level` est un niveau de journal [voir les niveaux de journal](/consts?id=日志等级).

!> Disponible pour les versions Swoole >= `v4.5.8`

```php
function swoole_error_log(int $level, string $msg)
```
## swoole_clear_error()

Effacer les erreurs du socket ou le dernier code d'erreur sur le code d'erreur.

!> Disponible pour les versions Swoole >= `v4.6.0`

```php
function swoole_clear_error()
```


## swoole_coroutine_socketpair()

Version coroutine de la fonction [socket_create_pair](https://www.php.net/manual/en/function.socket-create-pair.php).

!> Disponible pour les versions Swoole >= `v4.6.0`

```php
function swoole_coroutine_socketpair(int $domain , int $type , int $protocol): array|bool
```


## swoole_async_set

Cette fonction permet de configurer les options liées à l'异步`IO`.

```php
function swoole_async_set(array $settings)
```



- enable_signalfd Activer ou désactiver l'utilisation de la caractéristique `signalfd`

- enable_coroutine Activer ou désactiver les coroutines intégrées, [voir la documentation](/server/setting?id=enable_coroutine)

- aio_core_worker_num Régler le nombre minimum de processus AIO
- aio_worker_num Régler le nombre maximum de processus AIO


## swoole_error_log_ex()

Écrire un journal avec un niveau spécifié et un code d'erreur.

```php
function swoole_error_log_ex(int $level, int $error, string $msg)
```

!> Disponible pour les versions Swoole >= `v4.8.1`

## swoole_ignore_error()

Ignorer les erreurs du journal pour les codes d'erreur spécifiés.

```php
function swoole_ignore_error(int $error)
```

!> Disponible pour les versions Swoole >= `v4.8.1`
