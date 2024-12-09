# Asynchrone Dateioperationen

Die [One-Click-Coroutine-Enablement](/runtime) von `Swoole` kann die Synchron-Blockierung von PHP-Dateioperationen leicht in asynchrones Ausführen umwandeln. `Swoole` bietet insgesamt zwei verschiedene asynchrone Strategien für Dateien.

## Threadpool

* Der `Threadpool` ist die Standardmethode für asynchrone Fileoperationen in `Swoole`. Wenn ein Benutzer eine Fileoperation durchführt, wird diese Operation direkt an den `Threadpool` übergeben, wo ein Subthread die Fileoperation erledigt und nach Abschluss wieder zum Coroutine zurückkehrt.
* Alle PHP-Dateifunktionen können über den `Threadpool` asynchron umgesetzt werden, wie zum Beispiel `file_get_contents`, `fopen`, usw.
* Keine Abhängigkeiten erforderlich, hohe Kompatibilität, direkt einsatzbar.

## io_uring

* `io_uring` ist eine seit `Swoole v6.0` integrierte Strategie, die asynchron basiert auf `io_uring` und `epoll`.
* Hohe Durchsatzrate, kann eine große Anzahl von asynchronen Fileoperationen verarbeiten.
* Erfordert Linux-Versionen und hängt von der `liburing`-Bibliothek ab, einige Betriebssysteme können diese Funktion nicht nutzen.
* Da er auf Dateideskriptoren basiert, werden nur wenige PHP-Dateifunktionen unterstützt.
* Erfordert eine höhere Linux-Kernel-Version.

!> kann nur verwendet werden, wenn die `liburing`-Bibliothek installiert ist und `Swoole` mit `--enable-iouring` compiled wurde.

!> Nach dem Aktivieren von `io_uring` wird der `Threadpool` nicht ersetzt, einige Funktionen von `io_uring`, die nicht asynchronisiert werden können, werden immer noch vom `Threadpool` verarbeitet.

!> `io_uring` unterstützt nur die Funktionen `file_get_contents`, `file_put_contents`, `fopen`, `fclose`, `fread`, `fwrite`, `mkdir`, `unlink`, `fsync`, `fdatasync`, `rename`, `fstat`, `lstat`, `filesize`.
