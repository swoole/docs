# 코루틴 프로세스 관리

코루틴 공간에서 `fork` 프로세스를 할 때 다른 코루틴의 컨텍스트를 함께 가져오기 때문에, 기본적으로 `Coroutine`에서 `Process` 모듈을 사용할 수 없습니다. 다음과 같은 방법으로 외부 프로그램을 실행하거나 부모-자식 프로세스 간의 상호 통신을 구현할 수 있습니다.

* `System::exec()` 또는 `Runtime Hook`+`shell_exec`을 사용하여 외부 프로그램 실행
* `Runtime Hook`+`proc_open`을 사용하여 부모-자식 프로세스 간의 상호 통신

## 사용 예제

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
