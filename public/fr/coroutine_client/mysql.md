# Coroutine\MySQL

Client MySQL pour coroutines.

!> Ce client n'est plus recommandé pour utilisation, il est préférable d'utiliser la méthode `Swoole\Runtime::enableCoroutine` combinée avec `pdo_mysql` ou `mysqli`, c'est-à-dire [la coroutineisation en une touche](/runtime) du client MySQL原生.  
!> Après Swoole 6.0, ce client MySQL coroutine a été supprimé


## Exemple d'utilisation

```php
use Swoole\Coroutine\MySQL;
use function Swoole\Coroutine\run;

run(function () {
    $swoole_mysql = new MySQL();
    $swoole_mysql->connect([
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'user'     => 'user',
        'password' => 'pass',
        'database' => 'test',
    ]);
    $res = $swoole_mysql->query('select sleep(1)');
    var_dump($res);
});
```


## Caractéristique defer

Veuillez consulter la section [Client Concurrency](/coroutine/multi_call).


## Procédures stockées

À partir de la version `4.0.0`, le support pour les procédures stockées et l'obtention de plusieurs ensembles de résultats est ajouté.


## MySQL8.0

Swoole-4.0.1 ou une version supérieure prend en charge toutes les capacités de vérification de sécurité de MySQL8, vous pouvez utiliser directement le client sans avoir à revenir sur la configuration de mot de passe.


### Versions inférieures à 4.0.1

Par défaut, MySQL8 utilise le plugin `caching_sha2_password` qui est plus sécurisant. Si vous avez été migré de 5.x, vous pouvez utiliser toutes les fonctionnalités de MySQL, mais si vous avez créé un nouveau MySQL, vous devez entrer dans la ligne de commande MySQL pour effectuer les opérations suivantes pour être compatible :

```SQL
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'password';
flush privileges;
```

Remplacez `'root'@'localhost'` par l'utilisateur que vous utilisez et `password` par son mot de passe.

Si vous ne pouvez toujours pas utiliser le client, vous devez configurer my.cnf avec `default_authentication_plugin = mysql_native_password`.


## Propriétés


### serverInfo

Informations de connexion, conservent l'array passé à la fonction de connexion.


### sock

Déscription du fichier de descriptor utilisé pour la connexion.


### connected

Indique si une connexion au serveur MySQL est établie.

!> Référence [Propriété connected et état de connexion incohérents](/question/use?id=connected%C3%A9tat%C3%A9%C3%A9s%C3%B4s%C3%A9s%C3%B4s)


### connect_error

Message d'erreur survenant lors de l'exécution de la connexion `connect` avec le serveur.


### connect_errno

Code d'erreur survenant lors de l'exécution de la connexion `connect` avec le serveur, de type entier.


### error

Message d'erreur renvoyé par le serveur lors de l'exécution d'une commande MySQL.


### errno

Code d'erreur renvoyé par le serveur lors de l'exécution d'une commande MySQL, de type entier.


### affected_rows

Nombre de lignes affectées.


### insert_id

ID de la dernière ligne insérée.


## Méthodes


### connect()

Établir une connexion à MySQL.

```php
Swoole\Coroutine\MySQL->connect(array $serverInfo): bool
```

!> `$serverInfo` : Paramètres transmis sous forme d'array

```php
[
    'host'        => 'Adresse IP du MySQL', // Si c'est un UNIX Socket local, il faut l'écrire sous la forme `unix://tmp/your_file.sock`
    'user'        => 'Utilisateur de données',
    'password'    => 'Mot de passe du database',
    'database'    => 'Nom du database',
    'port'        => 'Port du MySQL par défaut 3306 Optionnel',
    'timeout'     => 'Temps de connexion', // Ne concerne que le temps de connexion, pas les méthodes query et execute, voir [Règles de timeout du client](/coroutine_client/init?id=règles%C3%A9ch)
    'charset'     => 'Chaîne de caractères',
    'strict_type' => false, // Activer le mode strict, les données renvoyées par la méthode query seront également converties en type fort
    'fetch_mode'  => true,  // Activer le mode fetch, peut être utilisé comme pdo pour fetch/fetchAll ligne par ligne ou obtenir tout le jeu de résultats (version 4.0 et plus)
]
```


### query()

Exécuter une commande SQL.

```php
Swoole\Coroutine\MySQL->query(string $sql, float $timeout = 0): array|false
```

  * **Paramètres** 

    * **`string $sql`**
      * **Fonction** : Commande SQL
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

    * **`float $timeout`**
      * **Fonction** : Temps de temps limité 【Si le serveur MySQL ne renvoie pas de données dans le délai spécifié, le niveau inférieur retournera `false`, avec un code d'erreur de `110` et coupera la connexion】
      * **Unité de valeur** : Seconde, la précision minimale est de millisecondes (`.001` seconde)
      * **Valeur par défaut** : `0`
      * **Autres valeurs** : None
      * **Référence [Règles de timeout du client](/coroutine_client/init?id=règles%C3%A9ch)**


  * **Valeur de retour**

    * Si le temps est écoulé/il y a une erreur, il retourne `false`, sinon `array` sous forme de résultat de la requête

  * **Récupération différée**

  !> Après avoir défini `defer`, l'appel à `query` retournera directement `true`. L'appel à `recv` entrera dans le wait de coroutine pour recevoir et retourner les résultats de la requête.

  * **Exemple**

```php
use Swoole\Coroutine\MySQL;
use function Swoole\Coroutine\run;

run(function () {
    $swoole_mysql = new MySQL();
    $swoole_mysql->connect([
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'user'     => 'user',
        'password' => 'pass',
        'database' => 'test',
    ]);
    $res = $swoole_mysql->query('show tables');
    if ($res === false) {
        return;
    }
    var_dump($res);
});
```


### prepare()

Envoyer une demande de préparation SQL au serveur MySQL.

!> `prepare` doit être utilisé en combinaison avec `execute`. Après un succès de la demande de préparation, appeler la méthode `execute` pour envoyer les paramètres de données au serveur MySQL.

```php
Swoole\Coroutine\MySQL->prepare(string $sql, float $timeout): Swoole\Coroutine\MySQL\Statement|false;
```

  * **Paramètres** 

    * **`string $sql`**
      * **Fonction** : Commande préparée 【Utilise `?` comme placeholders pour les paramètres】
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

    * **`float $timeout`**
      * **Fonction** : Temps de temps limité 
      * **Unité de valeur** : Seconde, la précision minimale est de millisecondes (`.001` seconde)
      * **Valeur par défaut** : `0`
      * **Autres valeurs** : None
      * **Référence [Règles de timeout du client](/coroutine_client/init?id=règles%C3%A9ch)**


  * **Valeur de retour**

    * Échec, retourne `false`, vous pouvez vérifier `$db->error` et `$db->errno` pour déterminer la raison de l'erreur
    * Succès, retourne un objet `Swoole\Coroutine\MySQL\Statement`, vous pouvez appeler la méthode [execute](/coroutine_client/mysql?id=statement-gtexecute) de l'objet pour envoyer les paramètres

  * **Exemple**

```php
use Swoole\Coroutine\MySQL;
use function Swoole\Coroutine\run;

run(function () {
    $db = new MySQL();
    $ret1 = $db->connect([
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'user'     => 'root',
        'password' => 'root',
        'database' => 'test',
    ]);
    $stmt = $db->prepare('SELECT * FROM userinfo WHERE id=?');
    if ($stmt == false) {
        var_dump($db->errno, $db->error);
    } else {
        $ret2 = $stmt->execute(array(10));
        var_dump($ret2);
    }
});
```


### escape()

Échapper les caractères spéciaux dans une commande SQL pour éviter les attaques par injection SQL. La mise en œuvre de base repose sur les fonctions fournies par `mysqlnd`, nécessitant la dépendance de l'extension `mysqlnd` PHP.

!> Lors de la compilation, il est nécessaire d'ajouter [--enable-mysqlnd](/environment?id=compilation_options) pour activer.

```php
Swoole\Coroutine\MySQL->escape(string $str): string
```

  * **Paramètres** 

    * **`string $str`**
      * **Fonction** : Caractères à échapper
      * **Valeur par défaut** : None
      * **Autres valeurs** : None

  * **Exemple d'utilisation**

```php
use Swoole\Coroutine\MySQL;
use function Swoole\Coroutine\run;

run(function () {
    $db = new MySQL();
    $db->connect([
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'user'     => 'root',
        'password' => 'root',
        'database' => 'test',
    ]);
    $data = $db->escape("abc'efg\r\n");
});
```
### begin()

Déclencher une transaction. Utilisé conjointement avec `commit` et `rollback` pour gérer les transactions MySQL.

```php
Swoole\Coroutine\MySQL->begin(): bool
```

!> Commencer une transaction MySQL, retourne `true` en cas de succès, `false` en cas d'échec, veuillez vérifier `=$db->errno` pour obtenir le code d'erreur.
  
!> Sur la même connexion MySQL, seule une transaction peut être démarrée à la fois ;  
Il faut attendre que la précédente transaction soit `commit` ou `rollback` avant de pouvoir lancer une nouvelle transaction ;  
Sinon, une exception `Swoole\MySQL\Exception` sera lancée en dessous avec un code d'exception de `21`.

  * **Exemple**

    ```php
    $db->begin();
    $db->query("update userinfo set level = 22 where id = 1");
    $db->commit();
    ```


### commit()

Valider une transaction. 

!> Doit être utilisé en combinaison avec `begin`.

```php
Swoole\Coroutine\MySQL->commit(): bool
```

!> Retourne `true` en cas de succès, `false` en cas d'échec, veuillez vérifier `=$db->errno` pour obtenir le code d'erreur.


### rollback()

Annuler une transaction.

!> Doit être utilisé en combinaison avec `begin`.

```php
Swoole\Coroutine\MySQL->rollback(): bool
```

!> Retourne `true` en cas de succès, `false` en cas d'échec, veuillez vérifier `=$db->errno` pour obtenir le code d'erreur.


### Statement->execute()

Envoyer des paramètres de données préparées au serveur MySQL.

!> L'exécution doit être utilisée en combinaison avec la préparation, avant d'appeler `execute`, il faut d'abord appeler `prepare` pour lancer une demande de préparation.

!> La méthode `execute` peut être appelée à plusieurs reprises.

```php
Swoole\Coroutine\MySQL\Statement->execute(array $params, float $timeout = -1): array|bool
```

  * **Paramètres** 

    * **`array $params`**
      * **Fonction** : Paramètres de données préparées 【doit avoir le même nombre de paramètres que la déclaration `prepare`. `$params` doit être un tableau avec des indices numériques, et l'ordre des paramètres doit être le même que dans la déclaration `prepare`】
      * **Valeur par défaut** : Aucun
      * **Autres valeurs** : Aucun

    * **`float $timeout`**
      * **Fonction** : Temps de dépassement 【Si le serveur MySQL ne répond pas dans le délai spécifié, le niveau inférieur retournera `false`, avec un code d'erreur de `110`, et coupera la connexion】
      * **Unité de valeur** : seconde, la précision minimale est de millisecondes (0.001 seconde)
      * **Valeur par défaut** : `-1`
      * **Autres valeurs** : Aucun
      * **Référence[Règles de dépassement du client](/coroutine_client/init?id=Règles de dépassement)**

  * **Valeur de retour** 

    * Retourne `true` en cas de succès, si la valeur de `fetch_mode` de `connect` est définie à `true`
    * Retourne un tableau de données `array` en cas de succès, sinon,
    * Retourne `false` en cas d'échec, veuillez vérifier `$db->error` et `$db->errno` pour déterminer la cause de l'erreur

  * **Exemple d'utilisation** 

```php
use Swoole\Coroutine\MySQL;
use function Swoole\Coroutine\run;

run(function () {
    $db = new MySQL();
    $ret1 = $db->connect([
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'user'     => 'root',
        'password' => 'root',
        'database' => 'test',
    ]);
    $stmt = $db->prepare('SELECT * FROM userinfo WHERE id=? and name=?');
    if ($stmt == false) {
        var_dump($db->errno, $db->error);
    } else {
        $ret2 = $stmt->execute(array(10, 'rango'));
        var_dump($ret2);

        $ret3 = $stmt->execute(array(13, 'alvin'));
        var_dump($ret3);
    }
});
```


### Statement->fetch()

Récupérer la ligne suivante de la résultat.

```php
Swoole\Coroutine\MySQL\Statement->fetch(): ?array
```

!> À partir de la version Swoole `4.0-rc1`, il est nécessaire d'ajouter l'option `fetch_mode => true` lors de la connexion

  * **Exemple** 

```php
$stmt = $db->prepare('SELECT * FROM ckl LIMIT 1');
$stmt->execute();
while ($ret = $stmt->fetch()) {
    var_dump($ret);
}
```

!> À partir de la nouvelle version du moteur MySQL `v4.4.0`, `fetch` doit être utilisé avec l'exemple de code pour lire jusqu'à `NULL`, sinon il sera impossible de lancer de nouvelles demandes (en raison du mécanisme de lecture sur demande en dessous, cela peut économiser de la mémoire)


### Statement->fetchAll()

Retourner un tableau contenant toutes les lignes du résultat.

```php
Swoole\Coroutine\MySQL\Statement->fetchAll():? array
```

!> À partir de la version Swoole `4.0-rc1`, il est nécessaire d'ajouter l'option `fetch_mode => true` lors de la connexion

  * **Exemple** 

```php
$stmt = $db->prepare('SELECT * FROM ckl LIMIT 1');
$stmt->execute();
$stmt->fetchAll();
```

### Statement->nextResult()

Avancer à la prochaine résultat de réponse dans un gestionnaire de résultat à plusieurs réponses (comme pour les processus stockés avec plusieurs retours).

```php
Swoole\Coroutine\MySQL\Statement->nextResult():? bool
```

  * **Valeurs de retour**

    * Retourne `TRUE` en cas de succès
    * Retourne `FALSE` en cas d'échec
    * Retourne `NULL` s'il n'y a pas de résultat suivant

  * **Exemples** 

    * **Mode non fetch**

    ```php
    $stmt = $db->prepare('CALL reply(?)');
    $res  = $stmt->execute(['hello mysql!']);
    do {
      var_dump($res);
    } while ($res = $stmt->nextResult());
    var_dump($stmt->affected_rows);
    ```

    * **Mode fetch**

    ```php
    $stmt = $db->prepare('CALL reply(?)');
    $stmt->execute(['hello mysql!']);
    do {
      $res = $stmt->fetchAll();
      var_dump($res);
    } while ($stmt->nextResult());
    var_dump($stmt->affected_rows);
    ```

!> À partir de la nouvelle version du moteur MySQL `v4.4.0`, `fetch` doit être utilisé avec l'exemple de code pour lire jusqu'à `NULL`, sinon il sera impossible de lancer de nouvelles demandes (en raison du mécanisme de lecture sur demande en dessous, cela peut économiser de la mémoire)
