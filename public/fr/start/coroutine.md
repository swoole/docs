# Introduction à la coroutines

!> Il est recommandé de consulter [Coroutine](/coroutine) pour comprendre les concepts de base des coroutines avant de lire cet article.

Swoole4 utilise un tout nouveau moteur de noyau de coroutines, et maintenant Swoole dispose d'une équipe de développement à plein temps, entrant ainsi dans une période sans précédent dans l'histoire de PHP, offrant des possibilités uniques pour une augmentation rapide des performances.

Swoole4 ou versions supérieures possèdent des coroutines intégrées à haute disponibilité, permettant d'implémenter l'[IO asynchrone](/learn?id=同步io异步io) avec du code entièrement synchrone, sans aucun mot-clé supplémentaire dans le code PHP, et le système d'exploitation effectue automatiquement le dispatching des coroutines en底层.

### Combien de choses pouvez-vous faire en une seconde avec les coroutines ?

Dormir 10 000 fois, lire, écrire, vérifier et supprimer des fichiers 10 000 fois, communiquer avec une base de données MySQLi et PDO 10 000 fois, créer un serveur TCP et communiquer avec plusieurs clients 10 000 fois, créer un serveur UDP et communiquer avec plusieurs clients 10 000 fois... Tout est完美ly terminé dans un seul processus !

```php
use Swoole\Runtime;
use Swoole\Coroutine;
use function Swoole\Coroutine\run;

// Cette ligne de code transforme les opérations de fichiers, sleep, MySQLi, PDO, streams, etc., en IO asynchrone, voir la section 'Coroutine simplifiée'.
Runtime::enableCoroutine();
$s = microtime(true);

// run() de Swoole\Coroutine est décrit dans la section 'Conteneur de coroutines'.
run(function() {
    // Je veux juste dormir...
    for ($c = 100; $c--;) {
        Coroutine::create(function () {
            for ($n = 100; $n--;) {
                usleep(1000);
            }
        });
    }

    // 10k lectures et écritures de fichiers
    for ($c = 100; $c--;) {
        Coroutine::create(function () use ($c) {
            $tmp_filename = "/tmp/test-{$c}.php";
            for ($n = 100; $n--;) {
                $self = file_get_contents(__FILE__);
                file_put_contents($tmp_filename, $self);
                assert(file_get_contents($tmp_filename) === $self);
            }
            unlink($tmp_filename);
        });
    }

    // 10k lectures avec PDO et MySQLi
    for ($c = 50; $c--;) {
        Coroutine::create(function () {
            $pdo = new PDO('mysql:host=127.0.0.1;dbname=test;charset=utf8', 'root', 'root');
            $statement = $pdo->prepare('SELECT * FROM `user`');
            for ($n = 100; $n--;) {
                $statement->execute();
                assert(count($statement->fetchAll()) > 0);
            }
        });
    }
    for ($c = 50; $c--;) {
        Coroutine::create(function () {
            $mysqli = new Mysqli('127.0.0.1', 'root', 'root', 'test');
            $statement = $mysqli->prepare('SELECT `id` FROM `user`');
            for ($n = 100; $n--;) {
                $statement->bind_result($id);
                $statement->execute();
                $statement->fetch();
                assert($id > 0);
            }
        });
    }

    // Serveur et client TCP avec des demandes de 12.8k dans un seul processus
    function tcp_pack(string $data): string
    {
        return pack('n', strlen($data)) . $data;
    }

    function tcp_length(string $head): int
    {
        return unpack('n', $head)[1];
    }

    Coroutine::create(function () {
        $ctx = stream_context_create(['socket' => ['so_reuseaddr' => true, 'backlog' => 128]]);
        $socket = stream_socket_server(
            'tcp://0.0.0.0:9502',
            $errno, $errstr, STREAM_SERVER_BIND | STREAM_SERVER_LISTEN, $ctx
        );
        if (!$socket) {
            echo "{$errstr} ({$errno})\n";
        } else {
            $i = 0;
            while ($conn = stream_socket_accept($socket, 1)) {
                stream_set_timeout($conn, 5);
                for ($n = 100; $n--;) {
                    $data = fread($conn, tcp_length(fread($conn, 2)));
                    assert($data === "Hello Swoole Server #{$n}!");
                    fwrite($conn, tcp_pack("Hello Swoole Client #{$n}!"));
                }
                if (++$i === 128) {
                    fclose($socket);
                    break;
                }
            }
        }
    });
    for ($c = 128; $c--;) {
        Coroutine::create(function () {
            $fp = stream_socket_client('tcp://127.0.0.1:9502', $errno, $errstr, 1);
            if (!$fp) {
                echo "{$errstr} ({$errno})\n";
            } else {
                stream_set_timeout($fp, 5);
                for ($n = 100; $n--;) {
                    fwrite($fp, tcp_pack("Hello Swoole Server #{$n}!"));
                    $data = fread($fp, tcp_length(fread($fp, 2)));
                    assert($data === "Hello Swoole Client #{$n}!");
                }
                fclose($fp);
            }
        });
    }

    // Serveur et client UDP avec des demandes de 12.8k dans un seul processus
    Coroutine::create(function () {
        $socket = new Swoole\Coroutine\Socket(AF_INET, SOCK_DGRAM, 0);
        $socket->bind('127.0.0.1', 9503);
        $client_map = [];
        for ($c = 128; $c--;) {
            for ($n = 0; $n < 100; $n++) {
                $recv = $socket->recvfrom($peer);
                $client_uid = "{$peer['address']}:{$peer['port']}";
                $id = $client_map[$client_uid] = ($client_map[$client_uid] ?? -1) + 1;
                assert($recv === "Client: Hello #{$id}!");
                $socket->sendto($peer['address'], $peer['port'], "Server: Hello #{$id}!");
            }
        }
        $socket->close();
    });
    for ($c = 128; $c--;) {
        Coroutine::create(function () {
            $fp = stream_socket_client('udp://127.0.0.1:9503', $errno, $errstr, 1);
            if (!$fp) {
                echo "$errstr ($errno)\n";
            } else {
                for ($n = 0; $n < 100; $n++) {
                    fwrite($fp, "Client: Hello #{$n}!");
                    $recv = fread($fp, 1024);
                    list($address, $port) = explode(':', (stream_socket_get_name($fp, true)));
                    assert($address === '127.0.0.1' && (int)$port === 9503);
                    assert($recv === "Server: Hello #{$n}!");
                }
                fclose($fp);
            }
        });
    }
});
echo 'use ' . (microtime(true) - $s) . ' s';
```
