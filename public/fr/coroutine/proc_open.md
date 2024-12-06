# Gestion de processus coopératifs

Étant donné que le `fork` de processus dans l'espace des coopératives emporte le contexte des autres coopératives, le niveau inférieur interdit l'utilisation du module `Process` dans `Coroutine`. Il est possible d'utiliser

* `System::exec()` ou `Runtime Hook` + `shell_exec` pour exécuter des programmes en dehors
* `Runtime Hook` + `proc_open` pour réaliser la communication interactive entre les processus père et fils

## Exemple d'utilisation


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
