# Управление процессами с использованием корутин

Поскольку `fork` процесс в пространстве корутин несет с собой контекст других корутин, низкоуровневое禁止ение использования `Process` модуля в `Coroutine`. Можно использовать

* `System::exec()` или `Runtime Hook`+`shell_exec` для выполнения внешних программ
* `Runtime Hook`+`proc_open` для реализации взаимодействия и коммуникации между родительским и детским процессами

## Примеры использования


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
