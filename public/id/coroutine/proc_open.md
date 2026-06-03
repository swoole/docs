# Manajemen Proses Coroutine

Karena melakukan `fork` proses di dalam ruang coroutine akan membawa konteks coroutine lain bersamanya, infrastruktur melarang penggunaan modul `Process` di dalam `Coroutine`. Bisa menggunakan:

* `System::exec()` atau `Runtime Hook` + `shell_exec` untuk menjalankan program eksternal
* `Runtime Hook` + `proc_open` untuk komunikasi interaktif antara proses induk-anak

## Contoh Penggunaan

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
