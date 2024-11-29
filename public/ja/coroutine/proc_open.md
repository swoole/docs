## コロニアルプロセス管理

コリニュート空間内で `fork`プロセスを行うと、他のコリニュートのコンテキストを持ち込むため、基盤レベルで `Coroutine` 内での `Process` モジュールの使用が禁止されています。以下の方法を使用することができます。

* `System::exec()` または `Runtime Hook`+`shell_exec` を使用して外部プログラムを実行する
* `Runtime Hook`+`proc_open` を使用して親子プロセスの対話通信を実現する

## 使用例

### main.php

```php
use Swoole\Runtime;
use function Swoole\Coroutine\run;

Runtime::enableCoroutine(SWOOLE_HOOK_ALL);
run(function () {
    $descriptorspec = array(
        0 => array("pipe", "r"),
        1 => array("pipe", "w"),
        2 => array("file", "/tmp/error-output.txt", "a")
    );

    $process = proc_open('php ' . __DIR__ . '/read_stdin.php', $descriptorspec, $pipes);

    $n = 10;
    while ($n--) {
        fwrite($pipes[0], "hello #$n \n");
        echo fread($pipes[1], 8192);
    }

    fclose($pipes[0]);
    proc_close($process);
});
```

### read_stdin.php

```php
while(true) {
    $line = fgets(STDIN);
    if ($line) {
        echo $line;
    } else {
        break;
    }
}
```
