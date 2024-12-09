# Einführung in Coroutinen

Bevor Sie diesen Artikel lesen, sollten Sie zuerst [Coroutine](/coroutine) betrachten, um sich die grundlegenden Konzepte der Coroutinen zu vermitteln.

Swoole4 verwendet einen brandneuen Coroutine-Kernel-Motor und hat nun ein ganzes Team von Entwicklern, die sich voll und ganz auf Swoole konzentrieren. Dies führt in eine Ära, die es in der Geschichte von PHP noch nie gab, und bietet einzigartige Möglichkeiten für eine hohe Leistungssteigerung.

Swoole4 oder höher haben integrierte Coroutinen mit hoher Verfügbarkeit, die es ermöglichen, asynchrones IO mit völlig synchronem Code zu realisieren. PHP-Code benötigt keine zusätzlichen Schlüsselwörter, und die Basisplattform会自动 die Coroutine-Planung durchführen.

### Wie viele Dinge können Sie in einer Sekunde mit Coroutinen tun?

Schlafen 10.000 Mal, lesen, schreiben, überprüfen und löschen Sie Dateien 10.000 Mal, kommunizieren Sie 10.000 Mal mit einer MySQLi- und PDO-Datenbank, erstellen Sie einen TCP-Server und kommunizieren Sie mit mehreren Clients 10.000 Mal, erstellen Sie einen UDP-Server und kommunizieren Sie mit mehreren Clients 10.000 Mal... alles wird perfekt innerhalb eines Prozesses abgeschlossen!

```php
use Swoole\Runtime;
use Swoole\Coroutine;
use function Swoole\Coroutine\run;

// Nach dieser Zeile werden Dateiverfahren, sleep, MySQLi, PDO, streams usw. zu asynchronem IO, siehe Abschnitt 'Schnellstart mit Coroutinen'.
Runtime::enableCoroutine();
$s = microtime(true);

// Swoole\Coroutine\run() siehe Abschnitt 'Coroutine-Container'.
run(function() {
    // Ich möchte nur schlafen...
    for ($c = 100; $c--;) {
        Coroutine::create(function () {
            for ($n = 100; $n--;) {
                usleep(1000);
            }
        });
    }

    // 10k Datei-Lesen und Schreiben
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

    // 10k PDO und MySQLi-Lesen
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

    // PHP_stream TCP-Server & Client mit 12.8k Anforderungen in einem Prozess
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

    // UDP-Server & Client mit 12.8k Anforderungen in einem Prozess
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
echo 'Verbrauchte Zeit: ' . (microtime(true) - $s) . ' Sekunden';
```
