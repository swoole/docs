# 코루틴初探

!> 먼저 [코루틴](/coroutine)를 확인하여 코루틴의 기본 개념을 이해한 후에 이 글을 읽어보시기 바랍니다.

Swoole4는 전혀 새로운 코루틴 커널 엔진을 사용하고 있으며, 이제 Swoole에는 전임 개발 팀이 하나 있다. 그래서 PHP 역사상 전례 없는 시기에 접어들고 있으며, 성능의 고속 향상을 위한 독특한 가능성을 제공하고 있습니다.

Swoole4 이상 버전은 고가용성의 내장 코루틴을 갖추고 있어, 완전히 동기화된 코드로 [비동기 I/O](/learn?id=同步io异步io)를 실현할 수 있습니다. PHP 코드는 추가적인 키워드가 없으며, 하단부에서 자동으로 코루틴 스케줄링을 진행합니다.

### 코루틴을 사용하면 1초 안에 얼마나 많은 일을 할 수 있을까요?

1만 번 잠자기, 1만 번 파일 읽기/쓰기, 1만 번 검사 및 삭제, PDO와 MySQLi를 사용한 데이터베이스 통신 1만 번, TCP 서버와 여러 클라이언트 간의 통신 1만 번, UDP 서버와 여러 클라이언트 간의 통신 1만 번......모든 것이 단일 프로세스에서 완벽하게 완성됩니다! 

```php
use Swoole\Runtime;
use Swoole\Coroutine;
use function Swoole\Coroutine\run;

// 이 줄의 코드 이후, 파일 조작, sleep, Mysqli, PDO, streams 등이 모두 비동기 I/O로 변경됩니다. '하이브리드 코루틴' 장에서 확인하세요.
Runtime::enableCoroutine();
$s = microtime(true);

// Swoole\Coroutine\run()은 '코루틴 컨테이너' 장에서 확인하세요.
run(function() {
    // 나는 그저 잠들고 싶은데...
    for ($c = 100; $c--;) {
        Coroutine::create(function () {
            for ($n = 100; $n--;) {
                usleep(1000);
            }
        });
    }

    // 10k 파일 읽기 및 쓰기
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

    // 10k pdo 및 mysqli 읽기
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

    // php_stream tcp 서버 & 클라이언트 12.8k 요청을 단일 프로세스에서 처리합니다.
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

    // udp 서버 & 클라이언트 12.8k 요청을 단일 프로세스에서 처리합니다.
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
