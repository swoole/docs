# Temps d'exécution

Par rapport à `Swoole1.x`, `Swoole4+` offre l'arme secrète des coroutines, où tout le code métier est synchrone, mais le niveau d'I/O sous-jacent est asynchrone, garantissant la concurrency tout en évitant la logique de code disjointe et l'enlisement dans plusieurs niveaux de callback typiques des callbacks asynchrones. Pour obtenir cet effet, il est nécessaire que toutes les demandes d'I/O soient [asynchrones](/learn?id=syncioasyncio), et bien que les clients tels que `MySQL` et `Redis` fournis par l'ère `Swoole1.x` soient asynchrones, ils utilisent une approche de programmation de callbacks asynchrones, et non celle des coroutines. Par conséquent, ces clients ont été retirés dans l'ère `Swoole4`.

Pour résoudre le problème du soutien aux coroutines pour ces clients, l'équipe de développement Swoole a fait un travail considérable :

- Au début, un client coroutine a été développé pour chaque type de client, comme détaillé dans [Clients Coroutine](/coroutine_client/init), mais cela posait trois problèmes :

  * La mise en œuvre était complexe, chaque client avait des protocoles complexes et perfectionner le soutien pour tous était une tâche énorme.
  * Les utilisateurs devaient modifier beaucoup de code, par exemple, si la connexion à `MySQL` était autrefois effectuée using la méthode native PHP PDO, alors maintenant il fallait utiliser la méthode [Swoole\Coroutine\MySQL](/coroutine_client/mysql).
  * Il était difficile de couvrir toutes les opérations, par exemple, les fonctions `proc_open()`, `sleep()`, etc., qui pouvaient également bloquer et rendre le programme synchrone et bloquant.


- Face à ces problèmes, l'équipe de développement Swoole a changé de stratégie de mise en œuvre, en utilisant la méthode `Hook` des fonctions PHP natives pour réaliser des clients coroutine. En une ligne de code, il est possible de transformer le code I/O synchrone existant en [I/O asynchrone](/learn?id=syncioasyncio) qui peut être [调度协程](/coroutine?id=协程调度), c'est-à-dire une "simplification en une touche de coroutine".

!> Cette caractéristique est stable à partir de la version `v4.3`, et de plus en plus de fonctions peuvent être "coroutineisée", donc certains clients coroutine précédemment écrits ne sont plus recommandés pour utilisation. Pour plus de détails, veuillez consulter [Clients Coroutine](/coroutine_client/init). Par exemple, depuis la version `v4.3+`, les opérations de fichier (comme `file_get_contents`, `fread`, etc.) peuvent être "coroutineisées". Si vous utilisez une version `v4.3+`, vous pouvez directement utiliser la "coroutineisation" plutôt que de recourir aux opérations de fichier coroutine fournies par Swoole [Coroutine File Operations](/coroutine/system).

## Types de fonctions

Définissez la portée des fonctions à "coroutineiser" en utilisant les `flags`

```php
Co::set(['hook_flags'=> SWOOLE_HOOK_ALL]); // Utilisez cette méthode pour les versions v4.4+.
// Ou
Swoole\Runtime::enableCoroutine($flags = SWOOLE_HOOK_ALL);
```

Pour activer plusieurs `flags` simultanément, utilisez l'opération `|`

```php
Co::set(['hook_flags'=> SWOOLE_HOOK_TCP | SWOOLE_HOOK_SLEEP]);
```

!> Les fonctions "Hookées" doivent être utilisées dans le [conteneur de coroutines](/coroutine/scheduler)

#### Questions courantes :id=runtime-qa

!> **Utiliser `Swoole\Runtime::enableCoroutine()` ou `Co::set(['hook_flags'])`**
  
* `Swoole\Runtime::enableCoroutine()`, qui peut être appelé dynamiquement après le démarrage du service (à l'exécution), permet de设置了flags de manière globale et effective dans le processus actuel. Il devrait être appelé au début de votre projet pour obtenir un effet de couverture de 100% ;
* `Co::set()` peut être considéré comme l'équivalent de `ini_set()` en PHP, et doit être appelé avant [Server->start()](/server/methods?id=start) ou [Co\run()](/coroutine/scheduler), sinon les `hook_flags` définis ne prendront pas effet. Pour les versions v4.4+, utilisez cette méthode pour设置了flags ;
* Que ce soit `Co::set(['hook_flags'])` ou `Swoole\Runtime::enableCoroutine()`, il ne faut appeler qu'une seule fois, car des appels répétés seront remplacés.


## Options

Les options prises en charge par `flags` incluent :


### SWOOLE_HOOK_ALL

Ouvre tous les types de flags mentionnés ci-dessous (à l'exception de CURL)

!> À partir de la version v4.5.4, `SWOOLE_HOOK_ALL` comprend également `SWOOLE_HOOK_CURL`

```php
Co::set(['hook_flags' => SWOOLE_HOOK_ALL]); // Ne comprend pas CURL
Co::set(['hook_flags' => SWOOLE_HOOK_ALL | SWOOLE_HOOK_CURL]); // Realise la coroutineisation de tous les types, y compris CURL
```


### SWOOLE_HOOK_TCP

Soutenu à partir de la version `v4.1`, ce type de stream pour les sockets TCP comprend les plus courants `Redis`, `PDO`, `Mysqli` ainsi que les opérations utilisant la série de fonctions PHP [streams](https://www.php.net/streams) pour manipuler des connexions TCP. Ces opérations peuvent être "Hookées". Exemple :

```php
Co::set(['hook_flags' => SWOOLE_HOOK_TCP]);

Co\run(function() {
    for ($c = 100; $c--;) {
        go(function () {//Crée 100 coroutines
            $redis = new Redis();
            $redis->connect('127.0.0.1', 6379);//Ici se produit un décalage de coroutines, le CPU passe à la prochaine coroutine, sans bloquer le processus
            $redis->get('key');//Ici se produit un décalage de coroutines, le CPU passe à la prochaine coroutine, sans bloquer le processus
        });
    }
});
```

Le code ci-dessus utilise la classe native `Redis`, mais elle est en fait devenue asynchrone. `Co\run()` crée un [conteneur de coroutines](/coroutine/scheduler), et `go()` crée une coroutine. Ces deux opérations sont automatiquement gérées par la classe [Swoole\Server](/server/init) fournie par Swoole, sans avoir besoin de les faire manuellement, en référence à [enable_coroutine](/server/setting?id=enable_coroutine).

Cela signifie que les programmeurs PHP traditionnels peuvent écrire des programmes à haute concurrency et à haute performance en utilisant leur logique de code habituelle, comme suit :

```php
Co::set(['hook_flags' => SWOOLE_HOOK_TCP]);

$http = new Swoole\Http\Server("0.0.0.0", 9501);
$http->set(['enable_coroutine' => true]);

$http->on('request', function ($request, $response) {
      $redis = new Redis();
      $redis->connect('127.0.0.1', 6379);//Ici se produit un décalage de coroutines, le CPU passe à la prochaine coroutine (à la prochaine demande), sans bloquer le processus
      $redis->get('key');//Ici se produit un décalage de coroutines, le CPU passe à la prochaine coroutine (à la prochaine demande), sans bloquer le processus
});

$http->start();
```


### SWOOLE_HOOK_UNIX

Soutenu à partir de la version `v4.2`. Ce type de stream pour les sockets Unix, exemple :

```php
Co::set(['hook_flags' => SWOOLE_HOOK_UNIX]);

Co\run(function () {
    $socket = stream_socket_server(
        'unix://swoole.sock',
        $errno,
        $errstr,
        STREAM_SERVER_BIND | STREAM_SERVER_LISTEN
    );
    if (!$socket) {
        echo "$errstr ($errno)" . PHP_EOL;
        exit(1);
    }
    while (stream_socket_accept($socket)) {
    }
});
```


### SWOOLE_HOOK_UDP

Soutenu à partir de la version `v4.2`. Ce type de stream pour les sockets UDP, exemple :

```php
Co::set(['hook_flags' => SWOOLE_HOOK_UDP]);

Co\run(function () {
    $socket = stream_socket_server(
        'udp://0.0.0.0:6666',
        $errno,
        $errstr,
        STREAM_SERVER_BIND
    );
    if (!$socket) {
        echo "$errstr ($errno)" . PHP_EOL;
        exit(1);
    }
    while (stream_socket_recvfrom($socket, 1, 0)) {
    }
});
```
### SWOOLE_HOOK_UDG

À partir de la version `v4.2`, le support est disponible. Stream de type socket datagramme Unix, exemple :

```php
Co::set(['hook_flags' => SWOOLE_HOOK_UDG]);

Co\run(function () {
    $socket = stream_socket_server(
        'udg://swoole.sock',
        $errno,
        $errstr,
        STREAM_SERVER_BIND
    );
    if (!$socket) {
        echo "$errstr ($errno)" . PHP_EOL;
        exit(1);
    }
    while (stream_socket_recvfrom($socket, 1, 0)) {
    }
});
```

### SWOOLE_HOOK_SSL

À partir de la version `v4.2`, le support est disponible. Stream de type socket SSL, exemple :

```php
Co::set(['hook_flags' => SWOOLE_HOOK_SSL]);

Co\run(function () {
    $host = 'host.domain.tld';
    $port = 1234;
    $timeout = 10;
    $cert = '/path/to/your/certchain/certchain.pem';
    $context = stream_context_create(
        array(
            'ssl' => array(
                'local_cert' => $cert,
            )
        )
    );
    if ($fp = stream_socket_client(
        'ssl://' . $host . ':' . $port,
        $errno,
        $errstr,
        30,
        STREAM_CLIENT_CONNECT,
        $context
    )) {
        echo "connected\n";
    } else {
        echo "ERROR: $errno - $errstr \n";
    }
});
```

### SWOOLE_HOOK_TLS

À partir de la version `v4.2`, le support est disponible. Stream de type socket TLS, [voir référence](https://www.php.net/manual/en/context.ssl.php).

Exemple :

```php
Co::set(['hook_flags' => SWOOLE_HOOK_TLS]);
```

### SWOOLE_HOOK_SLEEP

À partir de la version `v4.2`, le support est disponible. `Hook` pour la fonction `sleep`, qui comprend `sleep`, `usleep`, `time_nanosleep`, `time_sleep_until`. Étant donné que la granularité minimale du timer sous-jacent est de `1ms`, lorsque des fonctions de sommeil à haute précision telles que `usleep` sont utilisées avec un temps inférieur à `1ms`, le système d'appel `sleep` sera directement utilisé. Cela peut entraîner un blocage de sommeil très bref. Exemple :

```php
Co::set(['hook_flags' => SWOOLE_HOOK_SLEEP]);

Co\run(function () {
    go(function () {
        sleep(1);
        echo '1' . PHP_EOL;
    });
    go(function () {
        echo '2' . PHP_EOL;
    });
});
// Sortie
2
1
```

### SWOOLE_HOOK_FILE

À partir de la version `v4.3`, le support est disponible.

* **Gestion asynchrone des opérations de fichiers, les fonctions prises en charge incluent :**

    * `fopen`
    * `fread`/`fgets`
    * `fwrite`/`fputs`
    * `file_get_contents`, `file_put_contents`
    * `unlink`
    * `mkdir`
    * `rmdir`

Exemple :

```php
Co::set(['hook_flags' => SWOOLE_HOOK_FILE]);

Co\run(function () {
    $fp = fopen("test.log", "a+");
    fwrite($fp, str_repeat('A', 2048));
    fwrite($fp, str_repeat('B', 2048));
});
```

### SWOOLE_HOOK_STREAM_FUNCTION

À partir de la version `v4.4`, le support est disponible. `Hook` pour la fonction `stream_select()`, exemple :

```php
Co::set(['hook_flags' => SWOOLE_HOOK_STREAM_FUNCTION]);

Co\run(function () {
    $fp1 = stream_socket_client("tcp://www.baidu.com:80", $errno, $errstr, 30);
    $fp2 = stream_socket_client("tcp://www.qq.com:80", $errno, $errstr, 30);
    if (!$fp1) {
        echo "$errstr ($errno) \n";
    } else {
        fwrite($fp1, "GET / HTTP/1.0\r\nHost: www.baidu.com\r\nUser-Agent: curl/7.58.0\r\nAccept: */*\r\n\r\n");
        $r_array = [$fp1, $fp2];
        $w_array = $e_array = null;
        $n = stream_select($r_array, $w_array, $e_array, 10);
        $html = '';
        while (!feof($fp1)) {
            $html .= fgets($fp1, 1024);
        }
        fclose($fp1);
    }
});
```

### SWOOLE_HOOK_BLOCKING_FUNCTION

À partir de la version `v4.4`, le support est disponible. Les fonctions `blocking function` incluent : `gethostbyname`, `exec`, `shell_exec`, exemple :

```php
Co::set(['hook_flags' => SWOOLE_HOOK_BLOCKING_FUNCTION]);

Co\run(function () {
    echo shell_exec('ls');
});
```

### SWOOLE_HOOK_PROC

À partir de la version `v4.4`, le support est disponible. Functions `proc*` asynchronisées, y compris : `proc_open`, `proc_close`, `proc_get_status`, `proc_terminate`.

Exemple :

```php
Co::set(['hook_flags' => SWOOLE_HOOK_PROC]);

Co\run(function () {
    $descriptorspec = array(
        0 => array("pipe", "r"),  // stdin, child process read from it
        1 => array("pipe", "w"),  // stdout, child process write to it
    );
    $process = proc_open('php', $descriptorspec, $pipes);
    if (is_resource($process)) {
        fwrite($pipes[0], 'I am process');
        fclose($pipes[0]);

        while (true) {
            echo fread($pipes[1], 1024);
        }

        fclose($pipes[1]);
        $return_value = proc_close($process);
        echo "command returned $return_value" . PHP_EOL;
    }
});
```

### SWOOLE_HOOK_CURL

Après la version [v4.4LTS](https://github.com/swoole/swoole-src/tree/v4.4.x) ou à partir de la version `v4.5`, le support est officiellement disponible.

* **HOOK pour cURL, les fonctions prises en charge incluent :**

     * curl_init
     * curl_setopt
     * curl_exec
     * curl_multi_getcontent
     * curl_setopt_array
     * curl_error
     * curl_getinfo
     * curl_errno
     * curl_close
     * curl_reset

Exemple :

```php
Co::set(['hook_flags' => SWOOLE_HOOK_CURL]);

Co\run(function () {
    $ch = curl_init();  
    curl_setopt($ch, CURLOPT_URL, "http://www.xinhuanet.com/");  
    curl_setopt($ch, CURLOPT_HEADER, false);  
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);  
    curl_close($ch);
    var_dump($result);
});
```

### SWOOLE_HOOK_NATIVE_CURL

Gestion asynchrone de cURL native.

!> Disponible à partir de la version Swoole `v4.6.0`

!> Avant d'utiliser, assurez-vous d'avoir activé l'option [--enable-swoole-curl](/environment?id=通用参数) lors de la compilation ;  
L'activation de cette option将自动 configurer `SWOOLE_HOOK_NATIVE_CURL`, et désactiver [SWOOLE_HOOK_CURL](/runtime?id=swoole_hook_all) ;  
En même temps, `SWOOLE_HOOK_ALL` comprend `SWOOLE_HOOK_NATIVE_CURL`

```php
Co::set(['hook_flags' => SWOOLE_HOOK_NATIVE_CURL]);

Co::set(['hook_flags' => SWOOLE_HOOK_ALL | SWOOLE_HOOK_NATIVE_CURL]);
```

Exemple :

```php
Co::set(['hook_flags' => SWOOLE_HOOK_ALL]);

Co\run(function () {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://httpbin.org/get");
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);
    curl_close($ch);
    var_dump($result);
});
```

### SWOOLE_HOOK_SOCKETS

Gestion asynchrone des extensions de sockets.

!> Disponible à partir de la version Swoole `v4.6.0`

```php
Co::set(['hook_flags' => SWOOLE_HOOK_SOCKETS]);
```

### SWOOLE_HOOK_STDIO

Gestion asynchrone de STDIO.

!> Disponible à partir de la version Swoole `v4.6.2`

```php
use Swoole\Process;
Co::set(['socket_read_timeout' => -1, 'hook_flags' => SWOOLE_HOOK_STDIO]);
$proc = new Process(function ($p) {
    Co\run(function () use($p) {
        $p->write('start'.PHP_EOL);
        go(function() {
            co::sleep(0.05);
            echo "sleep\n";
        });
        echo fread(STDIN, 1024);
    });
}, true, SOCK_STREAM);
$proc->start();
echo $proc->read();
usleep(100000);
$proc->write('hello world'.PHP_EOL);
echo $proc->read();
echo $proc->read();
Process::wait();
```
### SWOOLE_HOOK_PDO_PGSQL

La `gestion asynchrone` de `pdo_pgsql`.

!> La version Swoole doit être >= `v5.1.0` pour être disponible

```php
Co::set(['hook_flags' => SWOOLE_HOOK_PDO_PGSQL]);
```

Exemple :
```php
<?php
function test()
{
    $dbname   = "test";
    $username = "test";
    $password = "test";
    try {
        $dbh = new PDO("pgsql:dbname=$dbname;host=127.0.0.1:5432", $username, $password);
        $dbh->exec('create table test (id int)');
        $dbh->exec('insert into test values(1)');
        $dbh->exec('insert into test values(2)');
        $res = $dbh->query("select * from test");
        var_dump($res->fetchAll());
        $dbh = null;
    } catch (PDOException $exception) {
        echo $exception->getMessage();
        exit;
    }
}

Co::set(['trace_flags' => SWOOLE_HOOK_PDO_PGSQL]);

Co\run(function () {
    test();
});
```

### SWOOLE_HOOK_PDO_ODBC

La `gestion asynchrone` de `pdo_odbc`.

!> La version Swoole doit être >= `v5.1.0` pour être disponible

```php
Co::set(['hook_flags' => SWOOLE_HOOK_PDO_ODBC]);
```

Exemple :
```php
<?php
function test()
{
    $username = "test";
    $password = "test";
    try {
        $dbh = new PDO("odbc:mysql-test");
        $res = $dbh->query("select sleep(1) s");
        var_dump($res->fetchAll());
        $dbh = null;
    } catch (PDOException $exception) {
        echo $exception->getMessage();
        exit;
    }
}

Co::set(['trace_flags' => SWOOLE_TRACE_CO_ODBC, 'log_level' => SWOOLE_LOG_DEBUG]);

Co\run(function () {
    test();
});
```

### SWOOLE_HOOK_PDO_ORACLE

La `gestion asynchrone` de `pdo_oci`.

!> La version Swoole doit être >= `v5.1.0` pour être disponible

```php
Co::set(['hook_flags' => SWOOLE_HOOK_PDO_ORACLE]);
```

Exemple :
```php
<?php
function test()
{
	$tsn = 'oci:dbname=127.0.0.1:1521/xe;charset=AL32UTF8';
	$username = "test";
	$password = "test";
    try {
        $dbh = new PDO($tsn, $username, $password);
        $dbh->exec('create table test (id int)');
        $dbh->exec('insert into test values(1)');
        $dbh->exec('insert into test values(2)');
        $res = $dbh->query("select * from test");
        var_dump($res->fetchAll());
        $dbh = null;
    } catch (PDOException $exception) {
        echo $exception->getMessage();
        exit;
    }
}

Co::set(['hook_flags' => SWOOLE_HOOK_PDO_ORACLE]);
Co\run(function () {
    test();
});
```

### SWOOLE_HOOK_PDO_SQLITE
La `gestion asynchrone` de `pdo_sqlite`.

!> La version Swoole doit être >= `v5.1.0` pour être disponible

```php
Co::set(['hook_flags' => SWOOLE_HOOK_PDO_SQLITE]);
```

* **Note**

!> Lorsque `swoole` gère de manière asynchrone les bases de données `sqlite`, il utilise un mode de séquentielisation pour garantir la [sécurité en threads](https://www.sqlite.org/threadsafe.html).  
Si le mode de thread spécifié lors de la compilation de la base de données `sqlite` est en mode single-thread, `swoole` ne peut pas gérer asynchroneusement `sqlite` et émet un avertissement, mais cela n'affecte pas l'utilisation, il n'y aura pas de changement de thread pendant les opérations d'insertion, de mise à jour, de suppression et de recherche. Dans ce cas, il est nécessaire de recompiler `sqlite` en spécifiant un mode de thread de séquentielisation ou multithread, [pour la raison](https://www.sqlite.org/compile.html#threadsafe).     
Les connexions à la base de données `sqlite` créées dans un environnement asynchrone sont toutes en mode séquentielisation, tandis que les connexions à la base de données `sqlite` créées dans un environnement non asynchrone sont par défaut cohérentes avec le mode de thread de `sqlite`.   
Si le mode de thread de `sqlite` est multithread, alors les connexions créées dans un environnement non asynchrone ne peuvent pas être partagées par plusieurs threads asynchrones, car la connexion à la base de données est en mode multithread à ce moment-là, et son utilisation dans un environnement asynchrone ne se transformera pas en mode séquentielisation.   
Le mode de thread par défaut de `sqlite` est séquentielisation, [explication sur la séquentielisation](https://www.sqlite.org/c3ref/c_config_covering_index_scan.html#sqliteconfigserialized), [mode de thread par défaut](https://www.sqlite.org/compile.html#threadsafe).      

Exemple :
```php
<?php
use function Swoole\Coroutine\run;
use function Swoole\Coroutine\go;

Co::set(['hook_flags'=> SWOOLE_HOOK_PDO_SQLITE]);

run(function() {
    for($i = 0; $i <= 5; $i++) {
        go(function() use ($i) {
            $db = new PDO('sqlite::memory:');
            $db->query('select randomblob(99999999)');
            var_dump($i);
        });
    }
});
```

## Méthodes


### setHookFlags()

Définir les fonctions à `Hook` par rapport aux `flags`

!> La version Swoole doit être >= `v4.5.0` pour être disponible

```php
Swoole\Runtime::setHookFlags(int $flags): bool
```


### getHookFlags()

Obtenir les `flags` actuellement `Hook`s, qui peuvent être différents de ceux transmis lors de l'activation du `Hook` (les `flags` qui n'ont pas été `Hook`s correctly seront effacés)

!> La version Swoole doit être >= `v4.4.12` pour être disponible

```php
Swoole\Runtime::getHookFlags(): int
```


## Liste commune des Hooks


### Liste disponible

  * Extension `redis`
  * `pdo_mysql` et `mysqli` utilisant le mode `mysqlnd`, si `mysqlnd` n'est pas activé, la coopération asynchrone n'est pas prise en charge
  * Extension `soap`
  * `file_get_contents`, `fopen`
  * `stream_socket_client` (`predis`, `php-amqplib`)
  * `stream_socket_server`
  * `stream_select` (nécessaire pour les versions >= `4.3.2`)
  * `fsockopen`
  * `proc_open` (nécessaire pour les versions >= `4.4.0`)
  * `curl`


### Liste inaccessible

!> **Non pris en charge pour la coopération asynchrone** signifie que la coopération asynchrone sera rétrogradée en mode bloqué, rendant l'utilisation de la coopération asynchrone inutile dans ce cas

  * `mysql` : utilise en dessous `libmysqlclient`
  * `mongo` : utilise en dessous `mongo-c-client`
  * `pdo_pgsql`, à partir de la version Swoole `v5.1.0`, la gestion asynchrone de `pdo_pgsql` est possible
  * `pdo_oci`, à partir de la version Swoole `v5.1.0`, la gestion asynchrone de `pdo_oci` est possible
  * `pdo_odbc`, à partir de la version Swoole `v5.1.0`, la gestion asynchrone de `pdo_odbc` est possible
  * `pdo_firebird`
  * `php-amqp`


## Changements d'API

Pour les versions `v4.3` et antérieures, l'API `enableCoroutine` nécessite deux paramètres.

```php
Swoole\Runtime::enableCoroutine(bool $enable = true, int $flags = SWOOLE_HOOK_ALL);
```

- `$enable` : active ou désactive la coopération asynchrone.
- `$flags` : choisissez le type à `cooperer`, vous pouvez en choisir plusieurs, le défaut est la sélection de tous. Seulement valide si `$enable = true`.

!> `Runtime::enableCoroutine(false)` désactive toutes les options de `Hook` de coopération asynchrone précédemment définies.
