# Ein Fehlerbericht einreichen

## Zu beachten

Wenn Sie glauben, einen BUG im Swoole-Kernel entdeckt zu haben, geben Sie bitte einen Bericht ein. Die Entwickler des Swoole-Kernels wissen vielleicht nicht einmal von der Existenz des Problems, und es könnte sehr schwierig sein, ihn zu entdecken und zu reparieren, wenn Sie ihn nicht selbst melden. Sie können Fehlerberichte (d.h. den grünen `New issue` Knopf in der oberen rechten Ecke) in der [GitHub Issue Tracker](https://github.com/swoole/swoole-src/issues) einreichen. Hier werden Fehlerberichte bevorzugt gelöst.

Bitte senden Sie keine Fehlerberichte per E-Mail-Liste oder in privaten Briefen, das GitHub Issue Tracker ist ebenfalls ein Ort, an dem Sie jegliche Anforderungen und Vorschläge zu Swoole stellen können.

Bevor Sie einen Fehlerbericht einreichen, lesen Sie bitte die folgenden **Anleitungen zum Einreichen eines Fehlerberichts** durch.

## Neues Issue erstellen

Beim Erstellen eines Issues wird zunächst ein Template bereitgestellt, das Sie sorgfältig ausfüllen sollten, sonst könnte das Issue aufgrund mangelnder Informationen ignoriert werden:

```markdown

Bitte beantworten Sie diese Fragen, bevor Sie Ihr Issue einreichen. Vielen Dank!
> Bitte beantworten Sie diese Fragen vor dem Einreichen eines Issues:
	
1. Was haben Sie getan? Wenn möglich, bieten Sie ein einfaches Script zur Reproduktion des Fehlers.
> Bitte beschreiben Sie den Prozess der Fehlermeldung im Detail, geben Sie relevante Code an und bieten Sie am besten ein einfaches Script, das den Fehler stabil reproduziert.

2. Was hätten Sie erwartet zu sehen?
> Was war Ihr erwartetes Ergebnis?

3. Was haben Sie tatsächlich gesehen?
> Was war das tatsächliche Ergebnis bei der Ausführung?

4. Welche Version von Swoole verwenden Sie (`php --ri swoole`)?
> Welche Version verwenden Sie? Geben Sie den Inhalt an, der durch `php --ri swoole` gedruckt wird	

5. Welche机器umgebung verwenden Sie (einschließlich der Version des Kernels, PHP und GCC)?
> Welche机器umgebung verwenden Sie (einschließlich der Version des Kernels, PHP und GCC Compilerversion)?	
> Verwenden Sie die Befehle `uname -a`, `php -v`, `gcc -v` zum Drucken

```

Am wichtigsten ist es, ein **einfaches Script zur stabilen Reproduktion des Fehlers** bereitzustellen, sonst müssen Sie so viele andere Informationen wie möglich bereitstellen, um den Entwicklern zu helfen, die Ursache des Fehlers zu bestimmen.

## Speicheranalyse (empfohlen)

In vielen Fällen kann Valgrind Fehler im Speicher besser entdecken als gdb. Führen Sie Ihr Programm mit den folgenden Befehlen aus, bis der BUG auftritt

```shell
USE_ZEND_ALLOC=0 valgrind --log-file=/tmp/valgrind.log php your_file.php
```

* Wenn das Programm einen Fehler hat, können Sie durch Drücken von `ctrl+c` den Betrieb beenden und dann den `/tmp/valgrind.log`-Datei hochladen, um dem Entwicklerteam zu helfen, den BUG zu lokalisieren.

## Über Kernabstürze (Kernel Dump)

In besonderen Fällen können Sie Debugging-Tools verwenden, um den Entwicklern zu helfen, das Problem zu lokalisieren

```shell
WARNING	swManager_check_exit_status: worker#1 abnormal exit, status=0, signal=11
```

Wenn ein solcher Hinweis im Swoole-Log erscheint (signal11), bedeutet dies, dass das Programm einen `Kernel Dump` erzeugt hat. Sie müssen ein追踪调试工具 verwenden, um den Ort des Fehlers zu bestimmen.

> Um `gdb` zur追踪`swoole` zu verwenden, müssen Sie beim编译en den `--enable-debug`-Parameter hinzufügen, um mehr Informationen zu erhalten

内核转储datei aktivieren
```shell
ulimit -c unlimited
```

Führen Sie den BUG aus, und die Kernabstürzdumpdatei wird im Programmverzeichnis oder im Systemrootverzeichnis oder im `/cores` Verzeichnis generiert (abhängig von Ihrer Systemkonfiguration)

Geben Sie den folgenden Befehl ein, um das Programm in gdb zu debuggen

```
gdb php core
gdb php /tmp/core.1234
```

Geben Sie dann `bt` und drücken Sie Enter, um den Aufrufl Stack mit dem Problem zu sehen
```
(gdb) bt
```

Sie können durch Drücken von `f Zahl` den spezifischen Aufrufl Stack Frame betrachten
```
(gdb) f 1
(gdb) f 0
```

Legten Sie alle oben genannten Informationen in das Issue ein.
