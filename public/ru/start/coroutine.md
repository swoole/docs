# Введение в协程

!> Рекомендуем сначала посмотреть [Coroutine](/coroutine), чтобы понять основные концепции协程, прежде чем читать этот текст.

Swoole4 использует совершенно новый ядро для协程, и теперь у Swoole есть целая команда разработчиков, полностью посвященных этому. Это открывает беспрецедентные возможности для улучшения производительности в истории PHP.

В Swoole4 или более поздних версиях есть встроенные协程 с высокой доступностью, которые позволяют использовать полностью синхронный код для реализации [асинхронного ввода/вывода](/learn?id=同步io异步io). В PHP-коде не требуется никаких дополнительных ключевых слов, и система автоматически управляет диспетчеризацией协程.

### Сколько дел вы можете выполнить за секунду с использованием协程?

Засыпать 10 тысяч раз, читать, писать, проверять и удалять файлы 10 тысяч раз, общаться с базой данных MySQLi и PDO 10 тысяч раз, создавать TCP-сервер и общаться с несколькими клиентами 10 тысяч раз, создавать UDP-сервер и общаться с несколькими клиентами 10 тысяч раз... Все это идеально завершается в одном процессе!

```php
use Swoole\Runtime;
use Swoole\Coroutine;
use function Swoole\Coroutine\run;

// После этой строки операции с файлами, засыпание, MySQLi, PDO, потоки становятся асинхронными IO, см. раздел 'Один клик для协程изации'.
Runtime::enableCoroutine();
$s = microtime(true);

// Swoole\Coroutine\run() см. в разделе 'Конектор для协程'.
run(function() {
    // Я просто хочу поспать...
    for ($c = 100; $c--;) {
        Coroutine::create(function () {
            for ($n = 100; $n--;) {
                usleep(1000);
            }
        });
    }

    // 10k чтение и письмо файлов
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

    // 10k чтения с PDO и MySQLi
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

    // Сerver и client tcp с php_stream и 12.8k запросов в одном процессе
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

    // Сerver и client udp с 12.8k запросами в одном процессе
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
echo 'Использовано ' . (microtime(true) - $s) . ' с';
```
