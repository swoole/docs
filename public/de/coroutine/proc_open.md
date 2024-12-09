# Prozessmanagement von Coroutinen

Da das `fork`-Prozess in einem Coroutine-Kontext andere Coroutine-Umwelt mit sich bringt, wird im unteren Level das Verwenden des `Process`-Moduls in `Coroutine` untersagt. Es können verwendet werden:

* `System::exec()` oder `Runtime Hook`+`shell_exec` um externe Programme auszuführen
* `Runtime Hook`+`proc_open` um eine Kommunikation zwischen Eltern- und Kindprozess zu ermöglichen


## Verwendungszwecke


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
